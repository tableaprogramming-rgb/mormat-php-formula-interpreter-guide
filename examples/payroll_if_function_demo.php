<?php

/**
 * Payroll IF Function Demonstrations
 *
 * Demonstrates conditional payroll logic using the custom IF function
 * for employment types, overtime, leaves, and complex pay structures.
 *
 * Run with: php examples/payroll_if_function_demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FormulaParser\Functions\IfFunction;
use Mormat\FormulaInterpreter\Compiler;

echo "================================================\n";
echo "Payroll IF Function Demonstrations\n";
echo "================================================\n\n";

$compiler = new Compiler();
$compiler->registerCustomFunction(new IfFunction());

// ==================================================
// Example 1: Regular vs Contractual Employee Pay
// ==================================================
echo "1. Employment Type-Based Pay\n";
echo "---------------------------\n";

$formula = "IF(is_regular, basic_salary, daily_rate * days_worked)";
$executable = $compiler->compile($formula);

$employees = [
    ['name' => 'Maria (Regular)', 'is_regular' => 1, 'basic_salary' => 25000, 'daily_rate' => 0, 'days_worked' => 0],
    ['name' => 'Juan (Contractual)', 'is_regular' => 0, 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 18],
    ['name' => 'Rosa (Regular)', 'is_regular' => 1, 'basic_salary' => 30000, 'daily_rate' => 0, 'days_worked' => 0],
];

foreach ($employees as $emp) {
    $gross_pay = $executable->run($emp);
    echo "  {$emp['name']}: " . number_format($gross_pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 2: Overtime Pay Calculation
// ==================================================
echo "2. Overtime Pay (Hours > 8 = 1.25x rate)\n";
echo "---------------------------------------\n";

$formula = "IF(hours_worked > 8, (hourly_rate * 8) + ((hours_worked - 8) * hourly_rate * 1.25), hourly_rate * hours_worked)";
$executable = $compiler->compile($formula);

$hourly_rate = 170.45;
$hours_scenarios = [
    ['hours_worked' => 6, 'description' => 'Undertime (6 hours)'],
    ['hours_worked' => 8, 'description' => 'Regular (8 hours)'],
    ['hours_worked' => 10, 'description' => 'OT (10 hours)'],
    ['hours_worked' => 12, 'description' => 'Heavy OT (12 hours)'],
];

foreach ($hours_scenarios as $scenario) {
    $scenario['hourly_rate'] = $hourly_rate;
    $pay = $executable->run($scenario);
    echo "  {$scenario['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 3: Absence Deductions (Conditional)
// ==================================================
echo "3. Absence Deductions\n";
echo "-------------------\n";

$formula = "IF(number_of_absences > 0, daily_rate * number_of_absences, 0)";
$executable = $compiler->compile($formula);

$daily_rate = 1363.64;
$absences = [
    ['number_of_absences' => 0, 'description' => 'Perfect attendance'],
    ['number_of_absences' => 1, 'description' => '1 absence'],
    ['number_of_absences' => 3, 'description' => '3 absences'],
];

foreach ($absences as $data) {
    $data['daily_rate'] = $daily_rate;
    $deduction = $executable->run($data);
    echo "  {$data['description']}: -" . number_format($deduction, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 4: Holiday Pay
// ==================================================
echo "4. Holiday Pay (2x regular daily rate)\n";
echo "------------------------------------\n";

$formula = "IF(is_holiday, daily_rate * 2, daily_rate)";
$executable = $compiler->compile($formula);

$day_types = [
    ['is_holiday' => 0, 'daily_rate' => $daily_rate, 'description' => 'Regular workday'],
    ['is_holiday' => 1, 'daily_rate' => $daily_rate, 'description' => 'Holiday'],
];

foreach ($day_types as $data) {
    $pay = $executable->run($data);
    echo "  {$data['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 5: Night Shift Differential
// ==================================================
echo "5. Night Shift Differential (10% premium)\n";
echo "--------------------------------------\n";

$formula = "IF(night_shift_hours > 0, (hourly_rate * night_shift_hours) + (hourly_rate * night_shift_hours * 0.10), 0)";
$executable = $compiler->compile($formula);

$night_shifts = [
    ['night_shift_hours' => 0, 'hourly_rate' => $hourly_rate, 'description' => 'Day shift'],
    ['night_shift_hours' => 4, 'hourly_rate' => $hourly_rate, 'description' => '4 night hours'],
    ['night_shift_hours' => 8, 'hourly_rate' => $hourly_rate, 'description' => 'Full night shift'],
];

foreach ($night_shifts as $data) {
    $pay = $executable->run($data);
    echo "  {$data['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 6: Rest Day vs Holiday Overtime
// ==================================================
echo "6. Overtime Rates (Rest Day=1.3x, Holiday=2.6x)\n";
echo "--------------------------------------------\n";

$formula = "IF(is_holiday, base_rate * 2.6, IF(is_rest_day, base_rate * 1.3, base_rate * 1))";
$executable = $compiler->compile($formula);

$base_rate = 1363.64;
$day_categories = [
    ['is_holiday' => 0, 'is_rest_day' => 0, 'base_rate' => $base_rate, 'description' => 'Regular workday'],
    ['is_holiday' => 0, 'is_rest_day' => 1, 'base_rate' => $base_rate, 'description' => 'Rest day'],
    ['is_holiday' => 1, 'is_rest_day' => 0, 'base_rate' => $base_rate, 'description' => 'Holiday'],
    ['is_holiday' => 1, 'is_rest_day' => 1, 'base_rate' => $base_rate, 'description' => 'Holiday on rest day'],
];

foreach ($day_categories as $data) {
    $pay = $executable->run($data);
    echo "  {$data['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 7: Employment Type Nested IF
// ==================================================
echo "7. Nested IF: Different Pay by Employment Type\n";
echo "-------------------------------------------\n";

$formula = "IF(employment_type = 'regular', " .
           "basic_salary, " .
           "IF(employment_type = 'contractual', " .
           "daily_rate * days_worked, " .
           "hourly_rate * hours_worked))";

$executable = $compiler->compile($formula);

$emp_types = [
    ['employment_type' => 'regular', 'basic_salary' => 30000, 'daily_rate' => 0, 'days_worked' => 0, 'hourly_rate' => 0, 'hours_worked' => 0],
    ['employment_type' => 'contractual', 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 20, 'hourly_rate' => 0, 'hours_worked' => 0],
    ['employment_type' => 'parttime', 'basic_salary' => 0, 'daily_rate' => 0, 'days_worked' => 0, 'hourly_rate' => 170.45, 'hours_worked' => 80],
];

foreach ($emp_types as $data) {
    $pay = $executable->run($data);
    echo "  {$data['employment_type']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 8: SSS Contribution Eligibility
// ==================================================
echo "8. SSS Contribution (Only for regular employees)\n";
echo "----------------------------------------------\n";

$formula = "IF(is_regular, gross_pay * 0.045, 0)";
$executable = $compiler->compile($formula);

$sss_data = [
    ['employee' => 'Regular Employee', 'is_regular' => 1, 'gross_pay' => 25000],
    ['employee' => 'Contractual Employee', 'is_regular' => 0, 'gross_pay' => 20000],
];

foreach ($sss_data as $data) {
    $sss = $executable->run($data);
    echo "  {$data['employee']}: " . number_format($sss, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 9: Bonus Based on Performance
// ==================================================
echo "9. Performance Bonus (Conditional)\n";
echo "--------------------------------\n";

$formula = "IF(performance_rating = 'excellent', gross_pay * 0.15, " .
           "IF(performance_rating = 'good', gross_pay * 0.10, " .
           "IF(performance_rating = 'satisfactory', gross_pay * 0.05, 0)))";

$executable = $compiler->compile($formula);

$gross_pay = 25000;
$ratings = [
    ['performance_rating' => 'excellent', 'gross_pay' => $gross_pay],
    ['performance_rating' => 'good', 'gross_pay' => $gross_pay],
    ['performance_rating' => 'satisfactory', 'gross_pay' => $gross_pay],
    ['performance_rating' => 'poor', 'gross_pay' => $gross_pay],
];

foreach ($ratings as $data) {
    $bonus = $executable->run($data);
    echo "  {$data['performance_rating']}: " . number_format($bonus, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 10: Attendance Incentive
// ==================================================
echo "10. Attendance Incentive (Perfect = Bonus)\n";
echo "--------------------------------------\n";

$formula = "IF(number_of_absences = 0, gross_pay * 0.05, 0)";
$executable = $compiler->compile($formula);

$attendance = [
    ['employee' => 'Maria (Perfect)', 'number_of_absences' => 0, 'gross_pay' => 25000],
    ['employee' => 'Juan (1 absence)', 'number_of_absences' => 1, 'gross_pay' => 25000],
];

foreach ($attendance as $data) {
    $bonus = $executable->run($data);
    echo "  {$data['employee']}: " . number_format($bonus, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 11: Leave Credit Eligibility
// ==================================================
echo "11. Vacation Leave Entitlement (Months of Service)\n";
echo "--------------------------------------------\n";

$formula = "IF(months_of_service >= 12, 15, IF(months_of_service >= 6, 10, 5))";
$executable = $compiler->compile($formula);

$leave_data = [
    ['employee' => 'New hire', 'months_of_service' => 2],
    ['employee' => 'Mid-tenure', 'months_of_service' => 8],
    ['employee' => 'One year', 'months_of_service' => 12],
];

foreach ($leave_data as $data) {
    $leave_days = $executable->run($data);
    echo "  {$data['employee']} ({$data['months_of_service']} months): {$leave_days} days\n";
}

echo "\n";

// ==================================================
// Example 12: Dual Condition: Holiday Overtime
// ==================================================
echo "12. Complex: Holiday + Overtime\n";
echo "-----------------------------\n";

$formula = "IF(is_holiday and hours_worked > 8, " .
           "((hourly_rate * hours_worked) * 2.6), " .
           "IF(is_holiday, (hourly_rate * hours_worked * 2), " .
           "IF(hours_worked > 8, (hourly_rate * 8) + ((hours_worked - 8) * hourly_rate * 1.25), hourly_rate * hours_worked)))";

$executable = $compiler->compile($formula);

$complex_scenarios = [
    ['is_holiday' => 0, 'hours_worked' => 8, 'hourly_rate' => $hourly_rate, 'description' => 'Regular (8 hrs)'],
    ['is_holiday' => 0, 'hours_worked' => 10, 'hourly_rate' => $hourly_rate, 'description' => 'Overtime (10 hrs)'],
    ['is_holiday' => 1, 'hours_worked' => 8, 'hourly_rate' => $hourly_rate, 'description' => 'Holiday (8 hrs)'],
    ['is_holiday' => 1, 'hours_worked' => 10, 'hourly_rate' => $hourly_rate, 'description' => 'Holiday OT (10 hrs)'],
];

foreach ($complex_scenarios as $scenario) {
    $pay = $executable->run($scenario);
    echo "  {$scenario['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";
echo "================================================\n";
echo "All payroll IF function examples completed!\n";
echo "================================================\n";
