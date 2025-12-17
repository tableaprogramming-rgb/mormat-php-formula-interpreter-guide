<?php

/**
 * Advanced Payroll Examples - Real-World Scenarios
 *
 * Comprehensive payroll system implementations covering complete payroll
 * calculations, bonuses, taxes, and HRMS-related computations.
 *
 * Run with: php examples/advanced_payroll_examples.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FormulaParser\Functions\IfFunction;
use Mormat\FormulaInterpreter\Compiler;

echo "=================================================\n";
echo "Advanced Payroll Examples - Complete Systems\n";
echo "=================================================\n\n";

$compiler = new Compiler();
$compiler->registerCustomFunction(new IfFunction());

// ==================================================
// SCENARIO 1: Complete Monthly Payroll
// ==================================================
echo "SCENARIO 1: Complete Monthly Payroll Calculation\n";
echo "================================================\n\n";

$payroll_formulas = [
    'daily_rate' => 'monthly_salary / 22',
    'hourly_rate' => 'daily_rate / 8',
    'basic_pay' => 'daily_rate * days_present',
    'overtime_pay' => 'IF(overtime_hours > 0, (hourly_rate * overtime_hours * 1.25), 0)',
    'absence_deduction' => 'IF(number_of_absences > 0, daily_rate * number_of_absences, 0)',
    'late_deduction' => 'IF(minutes_late > 0, (hourly_rate / 60) * minutes_late, 0)',
    'gross_pay' => 'basic_pay + overtime_pay',
    'sss_contribution' => 'gross_pay * 0.045',
    'philhealth_contribution' => 'gross_pay * 0.01',
    'pagibig_contribution' => 'gross_pay * 0.02',
    'withholding_tax' => 'gross_pay * 0.08',
    'total_deductions' => 'absence_deduction + late_deduction + sss_contribution + philhealth_contribution + pagibig_contribution + withholding_tax',
    'net_pay' => 'gross_pay - total_deductions',
];

$payroll_executables = [];
foreach ($payroll_formulas as $name => $formula) {
    $payroll_executables[$name] = $compiler->compile($formula);
}

$employee = [
    'employee_name' => 'Maria Santos',
    'employee_id' => 'EMP001',
    'monthly_salary' => 30000,
    'days_present' => 20,
    'overtime_hours' => 5,
    'number_of_absences' => 1,
    'minutes_late' => 30,
];

// Calculate each component
$daily_rate = $payroll_executables['daily_rate']->run($employee);
$hourly_rate = $payroll_executables['hourly_rate']->run(['daily_rate' => $daily_rate]);
$employee['daily_rate'] = $daily_rate;
$employee['hourly_rate'] = $hourly_rate;

$basic_pay = $payroll_executables['basic_pay']->run($employee);
$overtime_pay = $payroll_executables['overtime_pay']->run($employee);
$absence_deduction = $payroll_executables['absence_deduction']->run($employee);
$late_deduction = $payroll_executables['late_deduction']->run($employee);
$gross_pay = $payroll_executables['gross_pay']->run($employee);

$employee['basic_pay'] = $basic_pay;
$employee['overtime_pay'] = $overtime_pay;
$employee['absence_deduction'] = $absence_deduction;
$employee['late_deduction'] = $late_deduction;
$employee['gross_pay'] = $gross_pay;

$sss = $payroll_executables['sss_contribution']->run(['gross_pay' => $gross_pay]);
$philhealth = $payroll_executables['philhealth_contribution']->run(['gross_pay' => $gross_pay]);
$pagibig = $payroll_executables['pagibig_contribution']->run(['gross_pay' => $gross_pay]);
$withholding_tax = $payroll_executables['withholding_tax']->run(['gross_pay' => $gross_pay]);

$employee['sss_contribution'] = $sss;
$employee['philhealth_contribution'] = $philhealth;
$employee['pagibig_contribution'] = $pagibig;
$employee['withholding_tax'] = $withholding_tax;

$total_deductions = $payroll_executables['total_deductions']->run($employee);
$net_pay = $payroll_executables['net_pay']->run($employee);

echo "PAYSLIP\n";
echo "-------\n";
echo "Employee: {$employee['employee_name']} ({$employee['employee_id']})\n";
echo "Period: January 2025\n\n";
echo "EARNINGS:\n";
echo "  Daily Rate: " . number_format($daily_rate, 2) . "\n";
echo "  Hourly Rate: " . number_format($hourly_rate, 2) . "\n";
echo "  Basic Pay (20 days): " . number_format($basic_pay, 2) . "\n";
echo "  Overtime (5 hrs @ 1.25x): " . number_format($overtime_pay, 2) . "\n";
echo "  Gross Pay: " . number_format($gross_pay, 2) . "\n\n";
echo "DEDUCTIONS:\n";
echo "  Absences (1 day): -" . number_format($absence_deduction, 2) . "\n";
echo "  Tardiness (30 mins): -" . number_format($late_deduction, 2) . "\n";
echo "  SSS (4.5%): -" . number_format($sss, 2) . "\n";
echo "  PhilHealth (1%): -" . number_format($philhealth, 2) . "\n";
echo "  Pag-IBIG (2%): -" . number_format($pagibig, 2) . "\n";
echo "  Withholding Tax (8%): -" . number_format($withholding_tax, 2) . "\n";
echo "  Total Deductions: " . number_format($total_deductions, 2) . "\n\n";
echo "NET PAY: " . number_format($net_pay, 2) . "\n\n";

// ==================================================
// SCENARIO 2: Performance Bonus System
// ==================================================
echo "\nSCENARIO 2: Performance Bonus Calculation\n";
echo "==========================================\n\n";

$bonus_formula = "IF(performance_rating = 'excellent', gross_pay * 0.15, " .
                "IF(performance_rating = 'good', gross_pay * 0.10, " .
                "IF(performance_rating = 'satisfactory', gross_pay * 0.05, 0)))";

$bonus_executable = $compiler->compile($bonus_formula);

$bonus_employees = [
    ['name' => 'Maria Santos', 'gross_pay' => 27272.73, 'performance_rating' => 'excellent'],
    ['name' => 'Juan Cruz', 'gross_pay' => 28000, 'performance_rating' => 'good'],
    ['name' => 'Rosa Garcia', 'gross_pay' => 26500, 'performance_rating' => 'satisfactory'],
    ['name' => 'Carlos Lopez', 'gross_pay' => 25000, 'performance_rating' => 'poor'],
];

echo "PERFORMANCE BONUSES:\n";
printf("%-20s %-15s %-15s\n", "Employee", "Rating", "Bonus");
echo str_repeat("-", 50) . "\n";

foreach ($bonus_employees as $emp) {
    $bonus = $bonus_executable->run($emp);
    printf("%-20s %-15s %-15s\n", $emp['name'], $emp['performance_rating'], number_format($bonus, 2));
}

echo "\n";

// ==================================================
// SCENARIO 3: 13th Month Pay
// ==================================================
echo "\nSCENARIO 3: 13th Month Pay (Year-End Bonus)\n";
echo "==========================================\n\n";

$thirteenth_month_formula = "IF(years_of_service >= 1, (total_annual_earnings / 12), 0)";
$thirteenth_month_executable = $compiler->compile($thirteenth_month_formula);

$employees_13th = [
    ['name' => 'New Hire', 'years_of_service' => 0, 'total_annual_earnings' => 360000],
    ['name' => '1 Year Employee', 'years_of_service' => 1, 'total_annual_earnings' => 360000],
    ['name' => '5 Year Employee', 'years_of_service' => 5, 'total_annual_earnings' => 500000],
];

echo "13TH MONTH PAY ENTITLEMENTS:\n";
printf("%-20s %-15s %-15s %-15s\n", "Employee", "Years", "Annual Earnings", "13th Month");
echo str_repeat("-", 65) . "\n";

foreach ($employees_13th as $emp) {
    $thirteenth_month = $thirteenth_month_executable->run($emp);
    printf("%-20s %-15d %-15s %-15s\n",
        $emp['name'],
        $emp['years_of_service'],
        number_format($emp['total_annual_earnings'], 2),
        number_format($thirteenth_month, 2)
    );
}

echo "\n";

// ==================================================
// SCENARIO 4: Progressive Tax Brackets
// ==================================================
echo "\nSCENARIO 4: Progressive Tax Calculation\n";
echo "=====================================\n\n";

$tax_formula = "IF(taxable_income > 80000, (taxable_income * 0.30), " .
              "IF(taxable_income > 50000, (taxable_income * 0.20), " .
              "IF(taxable_income > 30000, (taxable_income * 0.15), " .
              "IF(taxable_income > 10000, (taxable_income * 0.10), 0))))";

$tax_executable = $compiler->compile($tax_formula);

$taxable_incomes = [
    ['description' => 'Below minimum', 'taxable_income' => 5000],
    ['description' => 'Lower bracket', 'taxable_income' => 20000],
    ['description' => 'Middle bracket', 'taxable_income' => 40000],
    ['description' => 'Upper bracket', 'taxable_income' => 60000],
    ['description' => 'Top bracket', 'taxable_income' => 100000],
];

echo "PROGRESSIVE TAX RATES:\n";
printf("%-20s %-15s %-15s\n", "Income Level", "Taxable Income", "Tax Amount");
echo str_repeat("-", 50) . "\n";

foreach ($taxable_incomes as $data) {
    $tax = $tax_executable->run($data);
    printf("%-20s %-15s %-15s\n",
        $data['description'],
        number_format($data['taxable_income'], 2),
        number_format($tax, 2)
    );
}

echo "\n";

// ==================================================
// SCENARIO 5: Leave Conversion to Cash
// ==================================================
echo "\nSCENARIO 5: Leave Credit Conversion\n";
echo "==================================\n\n";

$leave_conversion_formula = "IF(leave_credits > 0, (daily_rate * leave_credits), 0)";
$leave_conversion_executable = $compiler->compile($leave_conversion_formula);

$daily_rate_conv = 1363.64;
$leave_data = [
    ['employee' => 'Maria Santos', 'leave_credits' => 5, 'daily_rate' => $daily_rate_conv],
    ['employee' => 'Juan Cruz', 'leave_credits' => 3, 'daily_rate' => $daily_rate_conv],
    ['employee' => 'Rosa Garcia', 'leave_credits' => 0, 'daily_rate' => $daily_rate_conv],
];

echo "LEAVE CONVERSION TO CASH:\n";
printf("%-20s %-15s %-15s %-15s\n", "Employee", "Leave Credits", "Daily Rate", "Cash Value");
echo str_repeat("-", 65) . "\n";

foreach ($leave_data as $emp) {
    $conversion = $leave_conversion_executable->run($emp);
    printf("%-20s %-15d %-15s %-15s\n",
        $emp['employee'],
        $emp['leave_credits'],
        number_format($emp['daily_rate'], 2),
        number_format($conversion, 2)
    );
}

echo "\n";

// ==================================================
// SCENARIO 6: Attendance Incentive Program
// ==================================================
echo "\nSCENARIO 6: Perfect Attendance Incentive\n";
echo "======================================\n\n";

$attendance_formula = "IF(number_of_absences = 0, gross_pay * 0.10, 0)";
$attendance_executable = $compiler->compile($attendance_formula);

$gross_pay_ref = 25000;
$attendance_data = [
    ['employee' => 'Maria Santos', 'number_of_absences' => 0, 'gross_pay' => $gross_pay_ref],
    ['employee' => 'Juan Cruz', 'number_of_absences' => 1, 'gross_pay' => $gross_pay_ref],
    ['employee' => 'Rosa Garcia', 'number_of_absences' => 0, 'gross_pay' => $gross_pay_ref],
];

echo "PERFECT ATTENDANCE BONUS (10% of gross pay):\n";
printf("%-20s %-15s %-15s %-15s\n", "Employee", "Absences", "Gross Pay", "Bonus");
echo str_repeat("-", 65) . "\n";

foreach ($attendance_data as $emp) {
    $bonus = $attendance_executable->run($emp);
    printf("%-20s %-15d %-15s %-15s\n",
        $emp['employee'],
        $emp['number_of_absences'],
        number_format($emp['gross_pay'], 2),
        number_format($bonus, 2)
    );
}

echo "\n";

// ==================================================
// SCENARIO 7: Multi-Employee Batch Processing
// ==================================================
echo "\nSCENARIO 7: Batch Payroll Processing (10 Employees)\n";
echo "==================================================\n\n";

$batch_formula = "IF(employment_type = 'regular', basic_salary, daily_rate * days_worked)";
$batch_executable = $compiler->compile($batch_formula);

$batch_employees = [
    ['id' => 'E001', 'name' => 'Alice Johnson', 'employment_type' => 'regular', 'basic_salary' => 30000, 'daily_rate' => 0, 'days_worked' => 0],
    ['id' => 'E002', 'name' => 'Bob Smith', 'employment_type' => 'contractual', 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 20],
    ['id' => 'E003', 'name' => 'Carol Davis', 'employment_type' => 'regular', 'basic_salary' => 28000, 'daily_rate' => 0, 'days_worked' => 0],
    ['id' => 'E004', 'name' => 'David Wilson', 'employment_type' => 'contractual', 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 18],
    ['id' => 'E005', 'name' => 'Eve Martinez', 'employment_type' => 'regular', 'basic_salary' => 32000, 'daily_rate' => 0, 'days_worked' => 0],
];

echo "BATCH PAYROLL PROCESSING:\n";
printf("%-8s %-20s %-15s %-15s\n", "ID", "Name", "Type", "Gross Pay");
echo str_repeat("-", 60) . "\n";

$total_payroll = 0;
foreach ($batch_employees as $emp) {
    $gross = $batch_executable->run($emp);
    $total_payroll += $gross;
    printf("%-8s %-20s %-15s %-15s\n",
        $emp['id'],
        $emp['name'],
        $emp['employment_type'],
        number_format($gross, 2)
    );
}

echo str_repeat("-", 60) . "\n";
printf("%-8s %-20s %-15s %-15s\n", "", "TOTAL PAYROLL", "", number_format($total_payroll, 2));

echo "\n";
echo "=================================================\n";
echo "All advanced payroll examples completed!\n";
echo "=================================================\n";
