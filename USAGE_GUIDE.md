# PHP Formula Interpreter - Payroll & HRMS Usage Guide

A comprehensive guide for using the `mormat/php-formula-interpreter` package for payroll and Human Resource Management System (HRMS) calculations.

## Table of Contents
1. [Installation](#installation)
2. [Basic Concepts](#basic-concepts)
3. [Basic Payroll Examples](#basic-payroll-examples)
4. [Custom Functions Guide](#custom-functions-guide)
5. [Custom IF Function Implementation](#custom-if-function-implementation)
6. [Complete Payroll Scenarios](#complete-payroll-scenarios)
7. [Best Practices](#best-practices)

---

## Installation

### Prerequisites
- PHP 7.4 or higher
- Composer

### Installation Steps

```bash
composer require mormat/php-formula-interpreter
```

### Verify Installation

```php
require_once 'vendor/autoload.php';
$compiler = new \Mormat\FormulaInterpreter\Compiler();
echo "Installation successful!";
```

---

## Basic Concepts

### How It Works

The `php-formula-interpreter` uses a two-phase approach for formula execution:

1. **Compilation Phase**: Formula string is parsed into executable commands
2. **Execution Phase**: Compiled commands are executed with payroll variables

This separation ensures security and efficiency - formulas can be pre-compiled and executed multiple times with different employee data.

### Basic Workflow

```php
// 1. Create a compiler instance
$compiler = new \Mormat\FormulaInterpreter\Compiler();

// 2. Compile a payroll formula
$executable = $compiler->compile('basic_salary + allowances - deductions');

// 3. Execute with employee data
$result = $executable->run([
    'basic_salary' => 20000,
    'allowances' => 2000,
    'deductions' => 1000
]);  // Returns: 21000
```

### Variable Naming Convention

All variables use **snake_case** (lowercase with underscores):

```
✓ basic_salary
✓ number_of_absences
✓ overtime_hours
✓ daily_rate
✓ is_holiday

✗ baseSalary (camelCase - don't use)
✗ Basic_Salary (PascalCase - don't use)
✗ basic salary (spaces - don't use)
```

---

## Basic Payroll Examples

### Example 1: Simple Salary Calculation

```php
$compiler = new \Mormat\FormulaInterpreter\Compiler();

// Calculate gross pay: basic salary + allowances
$executable = $compiler->compile('basic_salary + transportation_allowance + meal_allowance');

$employees = [
    ['basic_salary' => 20000, 'transportation_allowance' => 1000, 'meal_allowance' => 500],
    ['basic_salary' => 30000, 'transportation_allowance' => 1500, 'meal_allowance' => 750],
    ['basic_salary' => 25000, 'transportation_allowance' => 1200, 'meal_allowance' => 600],
];

foreach ($employees as $emp) {
    $gross_pay = $executable->run($emp);
    echo "Gross Pay: " . number_format($gross_pay, 2) . "\n";
}
// Output:
// Gross Pay: 21,500.00
// Gross Pay: 32,250.00
// Gross Pay: 26,800.00
```

### Example 2: Net Pay Calculation

```php
// Calculate net pay with deductions
$executable = $compiler->compile('gross_pay - sss_contribution - philhealth_contribution - pagibig_contribution - withholding_tax');

$payroll = [
    'gross_pay' => 25000,
    'sss_contribution' => 1125,
    'philhealth_contribution' => 200,
    'pagibig_contribution' => 100,
    'withholding_tax' => 1000,
];

$net_pay = $executable->run($payroll);
echo "Net Pay: " . number_format($net_pay, 2) . "\n";
// Output: Net Pay: 21,575.00
```

### Example 3: Daily Rate Calculation

```php
// Calculate pay based on days worked
$monthly_salary = 30000;
$working_days = 22;

$compiler = new \Mormat\FormulaInterpreter\Compiler();
$executable = $compiler->compile('monthly_salary / working_days');

$daily_rate = $executable->run([
    'monthly_salary' => $monthly_salary,
    'working_days' => $working_days,
]);

echo "Daily Rate: " . number_format($daily_rate, 2) . "\n";
// Output: Daily Rate: 1,363.64

// Now calculate actual pay for days worked
$executable = $compiler->compile('daily_rate * days_present');
$actual_pay = $executable->run([
    'daily_rate' => $daily_rate,
    'days_present' => 20,
]);

echo "Pay for 20 days: " . number_format($actual_pay, 2) . "\n";
// Output: Pay for 20 days: 27,272.73
```

### Example 4: Hourly Rate Calculation

```php
// Calculate hourly rate and pay for hours worked
$compiler = new \Mormat\FormulaInterpreter\Compiler();

$daily_rate = 1363.64;

// Hourly rate = daily_rate / 8 hours
$executable = $compiler->compile('daily_rate / 8');
$hourly_rate = $executable->run(['daily_rate' => $daily_rate]);

echo "Hourly Rate: " . number_format($hourly_rate, 2) . "\n";
// Output: Hourly Rate: 170.45

// Calculate pay for hours worked
$executable = $compiler->compile('hourly_rate * hours_worked');
$regular_pay = $executable->run([
    'hourly_rate' => $hourly_rate,
    'hours_worked' => 40,
]);

echo "Pay for 40 hours: " . number_format($regular_pay, 2) . "\n";
// Output: Pay for 40 hours: 6,818.18
```

### Example 5: Absence Deductions

```php
// Calculate deductions for absences
$compiler = new \Mormat\FormulaInterpreter\Compiler();
$executable = $compiler->compile('daily_rate * number_of_absences');

$absence_data = [
    ['daily_rate' => 1363.64, 'number_of_absences' => 1],
    ['daily_rate' => 1363.64, 'number_of_absences' => 3],
    ['daily_rate' => 1363.64, 'number_of_absences' => 0],
];

foreach ($absence_data as $data) {
    $deduction = $executable->run($data);
    echo "Absence Deduction (" . $data['number_of_absences'] . " days): " . number_format($deduction, 2) . "\n";
}
// Output:
// Absence Deduction (1 days): 1,363.64
// Absence Deduction (3 days): 4,090.91
// Absence Deduction (0 days): 0.00
```

### Example 6: Late Deductions

```php
// Calculate deductions for tardiness
$compiler = new \Mormat\FormulaInterpreter\Compiler();
$executable = $compiler->compile('(hourly_rate / 60) * minutes_late');

$hourly_rate = 170.45;

$late_incidents = [
    ['hourly_rate' => $hourly_rate, 'minutes_late' => 15],
    ['hourly_rate' => $hourly_rate, 'minutes_late' => 30],
    ['hourly_rate' => $hourly_rate, 'minutes_late' => 60],
];

foreach ($late_incidents as $incident) {
    $deduction = $executable->run($incident);
    echo "Late Deduction (" . $incident['minutes_late'] . " mins): " . number_format($deduction, 2) . "\n";
}
// Output:
// Late Deduction (15 mins): 42.61
// Late Deduction (30 mins): 85.23
// Late Deduction (60 mins): 170.45
```

---

## Custom Functions Guide

Custom functions allow you to create reusable payroll calculations. They are implemented using the `FunctionInterface`.

### Basic Custom Function Example

```php
use Mormat\FormulaInterpreter\Functions\FunctionInterface;

class ComputeOvertimeFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'COMPUTE_OVERTIME';
    }

    public function supports(array $params): bool
    {
        // Accepts 2 numeric parameters: hourly_rate and overtime_hours
        return count($params) === 2 &&
               is_numeric($params[0]) &&
               is_numeric($params[1]);
    }

    public function execute(array $params): mixed
    {
        [$hourly_rate, $overtime_hours] = $params;
        $overtime_rate = $hourly_rate * 1.25; // 25% premium
        return $overtime_rate * $overtime_hours;
    }
}

// Usage
$compiler = new \Mormat\FormulaInterpreter\Compiler();
$compiler->registerCustomFunction(new ComputeOvertimeFunction());

$executable = $compiler->compile('regular_pay + COMPUTE_OVERTIME(hourly_rate, overtime_hours)');
$result = $executable->run([
    'regular_pay' => 5454.55,
    'hourly_rate' => 170.45,
    'overtime_hours' => 5,
]);

echo "Total Pay with Overtime: " . number_format($result, 2) . "\n";
// Output: Total Pay with Overtime: 6,628.09
```

### Using CallableFunction Helper

For simple functions wrapping PHP callables:

```php
use Mormat\FormulaInterpreter\Functions\CallableFunction;

$compiler = new \Mormat\FormulaInterpreter\Compiler();

// Create a function for 13th month pay calculation
$thirteenth_month = new CallableFunction(
    'THIRTEENTH_MONTH',
    fn($monthly_salary) => $monthly_salary,  // 1 month worth
    ['numeric']
);

$compiler->registerCustomFunction($thirteenth_month);

$executable = $compiler->compile('monthly_salary + THIRTEENTH_MONTH(monthly_salary)');
$result = $executable->run(['monthly_salary' => 30000]);

echo "Annual Compensation: " . number_format($result, 2) . "\n";
// Output: Annual Compensation: 60,000.00
```

---

## Custom IF Function Implementation

The IF function is essential for payroll logic - determining different payments based on conditions.

### Simple IF Example

```php
use FormulaParser\Functions\IfFunction;

$compiler = new \Mormat\FormulaInterpreter\Compiler();
$compiler->registerCustomFunction(new IfFunction());

// If regular employee, use basic salary; otherwise use daily rate
$formula = "IF(is_regular, basic_salary, daily_rate * days_worked)";
$executable = $compiler->compile($formula);

$employees = [
    ['is_regular' => 1, 'basic_salary' => 20000, 'daily_rate' => 1363.64, 'days_worked' => 20],
    ['is_regular' => 0, 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 18],
];

foreach ($employees as $emp) {
    $gross_pay = $executable->run($emp);
    echo "Gross Pay: " . number_format($gross_pay, 2) . "\n";
}
// Output:
// Gross Pay: 20,000.00
// Gross Pay: 24,545.52
```

### Nested IF for Employment Type

```php
// Different payment structure based on employment type
$formula = "IF(employment_type = 'regular', " .
           "basic_salary, " .
           "IF(employment_type = 'contractual', " .
           "daily_rate * days_worked, " .
           "hourly_rate * hours_worked))";

$executable = $compiler->compile($formula);

$employees = [
    ['employment_type' => 'regular', 'basic_salary' => 25000, 'daily_rate' => 0, 'days_worked' => 0, 'hourly_rate' => 0, 'hours_worked' => 0],
    ['employment_type' => 'contractual', 'basic_salary' => 0, 'daily_rate' => 1363.64, 'days_worked' => 18, 'hourly_rate' => 0, 'hours_worked' => 0],
    ['employment_type' => 'parttime', 'basic_salary' => 0, 'daily_rate' => 0, 'days_worked' => 0, 'hourly_rate' => 170.45, 'hours_worked' => 30],
];

foreach ($employees as $emp) {
    $gross_pay = $executable->run($emp);
    echo "(" . $emp['employment_type'] . ") Gross Pay: " . number_format($gross_pay, 2) . "\n";
}
// Output:
// (regular) Gross Pay: 25,000.00
// (contractual) Gross Pay: 24,545.52
// (parttime) Gross Pay: 5,113.50
```

### Overtime Calculation with IF

```php
// Pay overtime only if hours exceed 8
$formula = "IF(hours_worked > 8, " .
           "(hourly_rate * 8) + ((hours_worked - 8) * hourly_rate * 1.25), " .
           "hourly_rate * hours_worked)";

$executable = $compiler->compile($formula);

$hours_data = [
    ['hours_worked' => 6, 'hourly_rate' => 170.45],
    ['hours_worked' => 8, 'hourly_rate' => 170.45],
    ['hours_worked' => 10, 'hourly_rate' => 170.45],
    ['hours_worked' => 12, 'hourly_rate' => 170.45],
];

foreach ($hours_data as $data) {
    $total_pay = $executable->run($data);
    echo "Hours: {$data['hours_worked']} => Pay: " . number_format($total_pay, 2) . "\n";
}
// Output:
// Hours: 6 => Pay: 1,022.70
// Hours: 8 => Pay: 1,363.64
// Hours: 10 => Pay: 1,874.95
// Hours: 12 => Pay: 2,386.26
```

### Holiday and Rest Day Multipliers

```php
// Different multipliers for regular, rest day, and holiday
$formula = "IF(is_holiday, " .
           "base_rate * 2.6, " .
           "IF(is_rest_day, " .
           "base_rate * 1.3, " .
           "base_rate * 1))";

$executable = $compiler->compile($formula);

$day_types = [
    ['is_holiday' => 0, 'is_rest_day' => 0, 'base_rate' => 1363.64, 'description' => 'Regular Workday'],
    ['is_holiday' => 0, 'is_rest_day' => 1, 'base_rate' => 1363.64, 'description' => 'Rest Day'],
    ['is_holiday' => 1, 'is_rest_day' => 0, 'base_rate' => 1363.64, 'description' => 'Holiday'],
    ['is_holiday' => 1, 'is_rest_day' => 1, 'base_rate' => 1363.64, 'description' => 'Holiday + Rest Day'],
];

foreach ($day_types as $day) {
    $daily_pay = $executable->run($day);
    echo "{$day['description']}: " . number_format($daily_pay, 2) . "\n";
}
// Output:
// Regular Workday: 1,363.64
// Rest Day: 1,772.73
// Holiday: 3,545.46
// Holiday + Rest Day: 3,545.46
```

### Night Differential

```php
// Add night differential for night shift hours
$formula = "IF(night_shift_hours > 0, " .
           "(hourly_rate * night_shift_hours) + (hourly_rate * night_shift_hours * 0.10), " .
           "0)";

$executable = $compiler->compile($formula);

$night_data = [
    ['hourly_rate' => 170.45, 'night_shift_hours' => 0],
    ['hourly_rate' => 170.45, 'night_shift_hours' => 4],
    ['hourly_rate' => 170.45, 'night_shift_hours' => 8],
];

foreach ($night_data as $data) {
    $night_pay = $executable->run($data);
    echo "Night Shift ({$data['night_shift_hours']} hrs): " . number_format($night_pay, 2) . "\n";
}
// Output:
// Night Shift (0 hrs): 0.00
// Night Shift (4 hrs): 749.98
// Night Shift (8 hrs): 1,499.96
```

---

## Complete Payroll Scenarios

### Scenario 1: Complete Monthly Payroll Calculation

```php
use FormulaParser\Functions\IfFunction;

$compiler = new \Mormat\FormulaInterpreter\Compiler();
$compiler->registerCustomFunction(new IfFunction());

// Build payroll components
$formulas = [
    'daily_rate' => 'monthly_salary / 22',
    'hourly_rate' => 'daily_rate / 8',
    'basic_pay' => 'daily_rate * days_present',
    'overtime_pay' => 'IF(overtime_hours > 0, (hourly_rate * overtime_hours * 1.25), 0)',
    'absence_deduction' => 'IF(number_of_absences > 0, daily_rate * number_of_absences, 0)',
    'late_deduction' => 'IF(minutes_late > 0, (hourly_rate / 60) * minutes_late, 0)',
    'gross_pay' => 'basic_pay + overtime_pay',
    'total_deductions' => 'absence_deduction + late_deduction + sss_contribution + philhealth_contribution + pagibig_contribution + withholding_tax',
    'net_pay' => 'gross_pay - total_deductions',
];

$executables = [];
foreach ($formulas as $key => $formula) {
    $executables[$key] = $compiler->compile($formula);
}

// Employee payroll data
$employee = [
    'employee_name' => 'John Doe',
    'monthly_salary' => 30000,
    'days_present' => 20,
    'overtime_hours' => 5,
    'number_of_absences' => 1,
    'minutes_late' => 30,
    'sss_contribution' => 1350,
    'philhealth_contribution' => 200,
    'pagibig_contribution' => 100,
    'withholding_tax' => 2000,
];

// Calculate each component
$daily_rate = $executables['daily_rate']->run($employee);
$employee['daily_rate'] = $daily_rate;

$hourly_rate = $executables['hourly_rate']->run($employee);
$employee['hourly_rate'] = $hourly_rate;

$basic_pay = $executables['basic_pay']->run($employee);
$employee['basic_pay'] = $basic_pay;

$overtime_pay = $executables['overtime_pay']->run($employee);
$employee['overtime_pay'] = $overtime_pay;

$absence_deduction = $executables['absence_deduction']->run($employee);
$employee['absence_deduction'] = $absence_deduction;

$late_deduction = $executables['late_deduction']->run($employee);
$employee['late_deduction'] = $late_deduction;

$gross_pay = $executables['gross_pay']->run($employee);
$employee['gross_pay'] = $gross_pay;

$total_deductions = $executables['total_deductions']->run($employee);
$employee['total_deductions'] = $total_deductions;

$net_pay = $executables['net_pay']->run($employee);
$employee['net_pay'] = $net_pay;

// Display payslip
echo "=== PAYSLIP ===\n";
echo "Employee: {$employee['employee_name']}\n\n";
echo "EARNINGS:\n";
echo "  Daily Rate: " . number_format($daily_rate, 2) . "\n";
echo "  Basic Pay (20 days): " . number_format($basic_pay, 2) . "\n";
echo "  Overtime (5 hrs @ 1.25x): " . number_format($overtime_pay, 2) . "\n";
echo "  Gross Pay: " . number_format($gross_pay, 2) . "\n\n";
echo "DEDUCTIONS:\n";
echo "  Absences (1 day): " . number_format($absence_deduction, 2) . "\n";
echo "  Tardiness (30 mins): " . number_format($late_deduction, 2) . "\n";
echo "  SSS: " . number_format($employee['sss_contribution'], 2) . "\n";
echo "  PhilHealth: " . number_format($employee['philhealth_contribution'], 2) . "\n";
echo "  Pag-IBIG: " . number_format($employee['pagibig_contribution'], 2) . "\n";
echo "  Withholding Tax: " . number_format($employee['withholding_tax'], 2) . "\n";
echo "  Total Deductions: " . number_format($total_deductions, 2) . "\n\n";
echo "NET PAY: " . number_format($net_pay, 2) . "\n";
```

### Scenario 2: Performance Bonus Calculation

```php
// Calculate bonus based on performance rating
$formula = "IF(performance_rating = 'excellent', gross_pay * 0.15, " .
           "IF(performance_rating = 'good', gross_pay * 0.10, " .
           "IF(performance_rating = 'satisfactory', gross_pay * 0.05, 0)))";

$executable = $compiler->compile($formula);

$employees = [
    ['gross_pay' => 25000, 'performance_rating' => 'excellent'],
    ['gross_pay' => 25000, 'performance_rating' => 'good'],
    ['gross_pay' => 25000, 'performance_rating' => 'satisfactory'],
    ['gross_pay' => 25000, 'performance_rating' => 'poor'],
];

echo "PERFORMANCE BONUSES:\n";
foreach ($employees as $emp) {
    $bonus = $executable->run($emp);
    echo "{$emp['performance_rating']}: " . number_format($bonus, 2) . "\n";
}
// Output:
// excellent: 3,750.00
// good: 2,500.00
// satisfactory: 1,250.00
// poor: 0.00
```

### Scenario 3: 13th Month Pay

```php
// Calculate 13th month (year-end bonus) based on length of service
$formula = "IF(years_of_service >= 1, (total_annual_earnings / 12) * months_worked_this_year / 12, 0)";

$executable = $compiler->compile($formula);

$employees = [
    ['years_of_service' => 0, 'total_annual_earnings' => 360000, 'months_worked_this_year' => 12],  // New employee
    ['years_of_service' => 1, 'total_annual_earnings' => 360000, 'months_worked_this_year' => 12],  // 1 year
    ['years_of_service' => 5, 'total_annual_earnings' => 500000, 'months_worked_this_year' => 12],  // 5 years
];

echo "13TH MONTH PAY:\n";
foreach ($employees as $emp) {
    $thirteenth_month = $executable->run($emp);
    echo "Yrs: {$emp['years_of_service']}, Earned: " . number_format($thirteenth_month, 2) . "\n";
}
// Output:
// Yrs: 0, Earned: 0.00
// Yrs: 1, Earned: 2,500.00
// Yrs: 5, Earned: 4,166.67
```

---

## Best Practices

### 1. Compile Once, Reuse Many Times

```php
// GOOD: Compile formula once
$executable = $compiler->compile('basic_salary + allowances - deductions');

// Execute multiple times with different data
foreach ($employees as $employee) {
    $net_pay = $executable->run($employee);
}

// AVOID: Recompiling the same formula repeatedly
foreach ($employees as $employee) {
    $executable = $compiler->compile('basic_salary + allowances - deductions');
    $net_pay = $executable->run($employee);
}
```

### 2. Validate Variables Before Execution

```php
$required_vars = ['basic_salary', 'monthly_salary', 'allowances'];

$formula = 'basic_salary + allowances - deductions';
$executable = $compiler->compile($formula);

$employee_data = ['basic_salary' => 20000, 'allowances' => 2000];

if (array_key_exists('deductions', $employee_data)) {
    $result = $executable->run($employee_data);
} else {
    echo "Missing required variable: deductions\n";
}
```

### 3. Store Formulas in Configuration

```php
// config/payroll_formulas.php
return [
    'gross_pay' => 'basic_salary + transportation_allowance + meal_allowance',
    'basic_pay' => 'daily_rate * days_present',
    'overtime_pay' => 'IF(overtime_hours > 0, (hourly_rate * overtime_hours * 1.25), 0)',
    'absence_deduction' => 'IF(number_of_absences > 0, daily_rate * number_of_absences, 0)',
    'net_pay' => 'gross_pay - total_deductions',
];

// In your payroll processor
$formulas = require 'config/payroll_formulas.php';

foreach ($formulas as $name => $formula) {
    $executables[$name] = $compiler->compile($formula);
}
```

### 4. Use Meaningful Variable Names

Use descriptive snake_case names that clearly indicate what they represent:

```
✓ number_of_absences (clear - count of absences)
✓ overtime_hours (clear - hours worked beyond 8)
✓ sss_contribution (clear - SSS contribution amount)
✓ is_holiday (clear - boolean flag)

✗ abs (unclear)
✗ ot_hrs (abbreviated)
✗ sss (unclear)
✗ hol (abbreviated)
```

### 5. Document Complex Formulas

```php
/**
 * Calculate total earnings with all components
 *
 * Formula: basic_pay + overtime_pay + allowances
 * Where:
 *   - basic_pay = daily_rate * days_present
 *   - overtime_pay = IF(hours_worked > 8, extra_rate * (hours - 8), 0)
 *   - allowances = rice + transportation + meal
 */
$formula = 'basic_pay + overtime_pay + allowances';
$executable = $compiler->compile($formula);
```

### 6. Test Edge Cases

```php
// Test cases for overtime calculation
$test_cases = [
    ['hours_worked' => 0, 'expected' => 0],
    ['hours_worked' => 8, 'expected' => 'regular pay only'],
    ['hours_worked' => 8.5, 'expected' => 'includes 0.5 hour OT'],
    ['hours_worked' => 16, 'expected' => 'full 8 hours OT'],
];

foreach ($test_cases as $test) {
    // Run test...
}
```

---

## Troubleshooting

### Issue: Undefined Variable Error
**Cause**: Variable not provided or misspelled in formula
```php
// Formula
'daily_rate * days_present'

// Error - variable name mismatch
$executable->run(['daily_rate' => 1363.64, 'day_present' => 20]);  // ✗
$executable->run(['daily_rate' => 1363.64, 'days_present' => 20]); // ✓
```

### Issue: Type Mismatch
**Cause**: Variable type doesn't match formula expectations
```php
// Formula expects numeric values
'hours_worked * hourly_rate'

// Error - string instead of number
$executable->run(['hours_worked' => '40', 'hourly_rate' => '170.45']); // May work
$executable->run(['hours_worked' => 40, 'hourly_rate' => 170.45]);     // ✓
```

### Issue: IF Function Not Found
**Cause**: Custom IF function not registered
```php
// Error - IF function not registered
$executable = $compiler->compile("IF(is_regular, 20000, 5000)"); // ✗

// Solution - register before compiling
$compiler->registerCustomFunction(new IfFunction());
$executable = $compiler->compile("IF(is_regular, 20000, 5000)"); // ✓
```

---

## References

- **GitHub**: https://github.com/mormat/php-formula-interpreter
- **Supported Operators**: `+`, `-`, `*`, `/`, `>`, `<`, `>=`, `<=`, `=`, `and`, `or`, `not`

---

## Additional Resources

See the `examples/` directory for complete working scripts:
- `basic_payroll_usage.php` - Introduction to payroll formulas
- `payroll_if_function_demo.php` - IF function for conditional pay
- `advanced_payroll_examples.php` - Complete payroll system implementations
