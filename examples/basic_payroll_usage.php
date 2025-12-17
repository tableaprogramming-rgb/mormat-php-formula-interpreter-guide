<?php

/**
 * Basic Payroll Usage Examples
 *
 * This file demonstrates fundamental payroll and HRMS calculations
 * using the mormat/php-formula-interpreter package.
 *
 * Run with: php examples/basic_payroll_usage.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mormat\FormulaInterpreter\Compiler;

echo "============================================\n";
echo "Payroll Calculation - Basic Examples\n";
echo "============================================\n\n";

$compiler = new Compiler();

// ==================================================
// Example 1: Gross Pay Calculation
// ==================================================
echo "1. Gross Pay Calculation (Salary + Allowances)\n";
echo "---------------------------------------------\n";

$executable = $compiler->compile('basic_salary + transportation_allowance + meal_allowance');

$employees = [
    ['name' => 'Maria Santos', 'basic_salary' => 20000, 'transportation_allowance' => 1000, 'meal_allowance' => 500],
    ['name' => 'Juan Cruz', 'basic_salary' => 30000, 'transportation_allowance' => 1500, 'meal_allowance' => 750],
    ['name' => 'Rosa Garcia', 'basic_salary' => 25000, 'transportation_allowance' => 1200, 'meal_allowance' => 600],
];

foreach ($employees as $emp) {
    $gross_pay = $executable->run($emp);
    echo "  {$emp['name']}: " . number_format($gross_pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 2: Net Pay Calculation
// ==================================================
echo "2. Net Pay Calculation (After Deductions)\n";
echo "-----------------------------------------\n";

$executable = $compiler->compile('gross_pay - sss_contribution - philhealth_contribution - pagibig_contribution - withholding_tax');

$payroll_data = [
    ['employee' => 'Maria Santos', 'gross_pay' => 21500, 'sss_contribution' => 967.50, 'philhealth_contribution' => 200, 'pagibig_contribution' => 100, 'withholding_tax' => 800],
    ['employee' => 'Juan Cruz', 'gross_pay' => 32250, 'sss_contribution' => 1455, 'philhealth_contribution' => 250, 'pagibig_contribution' => 100, 'withholding_tax' => 1200],
];

foreach ($payroll_data as $data) {
    $net_pay = $executable->run($data);
    echo "  {$data['employee']}: " . number_format($net_pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 3: Daily Rate Calculation
// ==================================================
echo "3. Daily Rate Calculation\n";
echo "------------------------\n";

echo "  Monthly Salary: 30,000 / Working Days: 22\n";

$daily_rate_executable = $compiler->compile('monthly_salary / working_days');
$daily_rate = $daily_rate_executable->run([
    'monthly_salary' => 30000,
    'working_days' => 22,
]);

echo "  Daily Rate: " . number_format($daily_rate, 2) . "\n\n";

// Calculate pay for different days present
$pay_executable = $compiler->compile('daily_rate * days_present');

$days_worked_data = [
    ['days_present' => 20, 'description' => '20 days worked'],
    ['days_present' => 18, 'description' => '18 days worked (2 absences)'],
    ['days_present' => 22, 'description' => '22 days worked (full month)'],
];

foreach ($days_worked_data as $data) {
    $data['daily_rate'] = $daily_rate;
    $pay = $pay_executable->run($data);
    echo "  {$data['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 4: Hourly Rate Calculation
// ==================================================
echo "4. Hourly Rate Calculation\n";
echo "-------------------------\n";

$hourly_rate_executable = $compiler->compile('daily_rate / 8');
$hourly_rate = $hourly_rate_executable->run(['daily_rate' => $daily_rate]);

echo "  Daily Rate: " . number_format($daily_rate, 2) . " / 8 hours\n";
echo "  Hourly Rate: " . number_format($hourly_rate, 2) . "\n\n";

// Calculate pay for hours worked
$hourly_pay_executable = $compiler->compile('hourly_rate * hours_worked');

$hours_worked_data = [
    ['hours_worked' => 8, 'description' => 'Full day (8 hours)'],
    ['hours_worked' => 4, 'description' => 'Half day (4 hours)'],
    ['hours_worked' => 2, 'description' => 'Short shift (2 hours)'],
];

foreach ($hours_worked_data as $data) {
    $data['hourly_rate'] = $hourly_rate;
    $pay = $hourly_pay_executable->run($data);
    echo "  {$data['description']}: " . number_format($pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 5: Absence Deductions
// ==================================================
echo "5. Absence Deductions\n";
echo "-------------------\n";

$absence_deduction_executable = $compiler->compile('daily_rate * number_of_absences');

echo "  Daily Rate: " . number_format($daily_rate, 2) . "\n\n";

$absence_data = [
    ['number_of_absences' => 0, 'description' => 'No absences'],
    ['number_of_absences' => 1, 'description' => '1 absence'],
    ['number_of_absences' => 2, 'description' => '2 absences'],
    ['number_of_absences' => 3, 'description' => '3 absences'],
];

foreach ($absence_data as $data) {
    $data['daily_rate'] = $daily_rate;
    $deduction = $absence_deduction_executable->run($data);
    echo "  {$data['description']}: -" . number_format($deduction, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 6: Late Deductions (Hourly)
// ==================================================
echo "6. Late Deductions (Per Minute)\n";
echo "------------------------------\n";

$late_deduction_executable = $compiler->compile('(hourly_rate / 60) * minutes_late');

echo "  Hourly Rate: " . number_format($hourly_rate, 2) . "\n\n";

$late_data = [
    ['minutes_late' => 0],
    ['minutes_late' => 15],
    ['minutes_late' => 30],
    ['minutes_late' => 60],
];

foreach ($late_data as $data) {
    $data['hourly_rate'] = $hourly_rate;
    $deduction = $late_deduction_executable->run($data);
    echo "  Late {$data['minutes_late']} minutes: -" . number_format($deduction, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 7: Total Attendance-Based Pay
// ==================================================
echo "7. Total Attendance-Based Pay\n";
echo "----------------------------\n";

$attendance_executable = $compiler->compile('(daily_rate * days_present) - ((hourly_rate / 60) * minutes_late)');

$attendance_data = [
    [
        'employee' => 'Maria Santos',
        'daily_rate' => $daily_rate,
        'days_present' => 20,
        'hourly_rate' => $hourly_rate,
        'minutes_late' => 30,
    ],
    [
        'employee' => 'Juan Cruz',
        'daily_rate' => $daily_rate,
        'days_present' => 22,
        'hourly_rate' => $hourly_rate,
        'minutes_late' => 0,
    ],
];

foreach ($attendance_data as $data) {
    $emp_name = $data['employee'];
    $total_pay = $attendance_executable->run($data);
    echo "  {$emp_name}: " . number_format($total_pay, 2) . "\n";
}

echo "\n";

// ==================================================
// Example 8: Government Contributions Calculation
// ==================================================
echo "8. Government Contributions\n";
echo "---------------------------\n";

$gross_pay = 25000;

$sss_executable = $compiler->compile('gross_pay * 0.045');
$philhealth_executable = $compiler->compile('gross_pay * 0.01');
$pagibig_executable = $compiler->compile('gross_pay * 0.02');

$sss = $sss_executable->run(['gross_pay' => $gross_pay]);
$philhealth = $philhealth_executable->run(['gross_pay' => $gross_pay]);
$pagibig = $pagibig_executable->run(['gross_pay' => $gross_pay]);

echo "  Gross Pay: " . number_format($gross_pay, 2) . "\n";
echo "  SSS (4.5%): " . number_format($sss, 2) . "\n";
echo "  PhilHealth (1%): " . number_format($philhealth, 2) . "\n";
echo "  Pag-IBIG (2%): " . number_format($pagibig, 2) . "\n";

$total_contributions_executable = $compiler->compile('sss + philhealth + pagibig');
$total_contributions = $total_contributions_executable->run([
    'sss' => $sss,
    'philhealth' => $philhealth,
    'pagibig' => $pagibig,
]);

echo "  Total Contributions: " . number_format($total_contributions, 2) . "\n";

echo "\n";

// ==================================================
// Example 9: Withholding Tax Calculation
// ==================================================
echo "9. Withholding Tax Calculation\n";
echo "-----------------------------\n";

$taxable_income_executable = $compiler->compile('gross_pay - sss - philhealth - pagibig');
$taxable_income = $taxable_income_executable->run([
    'gross_pay' => $gross_pay,
    'sss' => $sss,
    'philhealth' => $philhealth,
    'pagibig' => $pagibig,
]);

echo "  Gross Pay: " . number_format($gross_pay, 2) . "\n";
echo "  Less: Government Contributions: " . number_format($total_contributions, 2) . "\n";
echo "  Taxable Income: " . number_format($taxable_income, 2) . "\n";

// Simple withholding tax: 8% of taxable income
$withholding_tax_executable = $compiler->compile('taxable_income * 0.08');
$withholding_tax = $withholding_tax_executable->run(['taxable_income' => $taxable_income]);

echo "  Withholding Tax (8%): " . number_format($withholding_tax, 2) . "\n";

echo "\n";

// ==================================================
// Example 10: Complete Payroll Summary
// ==================================================
echo "10. Complete Payroll Summary\n";
echo "----------------------------\n";

$employee_payroll = [
    'employee_name' => 'Robert Johnson',
    'monthly_salary' => 30000,
    'working_days' => 22,
    'days_present' => 20,
    'minutes_late' => 15,
    'gross_pay' => 27272.73,
];

$daily_rate_summary = ($employee_payroll['monthly_salary'] / $employee_payroll['working_days']);
$hourly_rate_summary = ($daily_rate_summary / 8);

$basic_pay = $daily_rate_summary * $employee_payroll['days_present'];
$late_deduction = ($hourly_rate_summary / 60) * $employee_payroll['minutes_late'];
$sss_amount = $basic_pay * 0.045;
$philhealth_amount = $basic_pay * 0.01;
$pagibig_amount = $basic_pay * 0.02;
$total_deductions = $late_deduction + $sss_amount + $philhealth_amount + $pagibig_amount;
$net_pay = $basic_pay - $total_deductions;

echo "Employee: {$employee_payroll['employee_name']}\n";
echo "Period: January 2025\n\n";
echo "EARNINGS:\n";
echo "  Daily Rate: " . number_format($daily_rate_summary, 2) . "\n";
echo "  Days Present: {$employee_payroll['days_present']}\n";
echo "  Basic Pay: " . number_format($basic_pay, 2) . "\n\n";
echo "DEDUCTIONS:\n";
echo "  Tardiness (15 mins): " . number_format($late_deduction, 2) . "\n";
echo "  SSS Contribution: " . number_format($sss_amount, 2) . "\n";
echo "  PhilHealth: " . number_format($philhealth_amount, 2) . "\n";
echo "  Pag-IBIG: " . number_format($pagibig_amount, 2) . "\n";
echo "  Total Deductions: " . number_format($total_deductions, 2) . "\n\n";
echo "NET PAY: " . number_format($net_pay, 2) . "\n";

echo "\n";
echo "============================================\n";
echo "All basic payroll examples completed!\n";
echo "============================================\n";
