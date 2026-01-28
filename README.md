# PHP Formula Interpreter for Payroll & HRMS

A comprehensive guide and working examples for using the `mormat/php-formula-interpreter` package for payroll and Human Resource Management System (HRMS) calculations with a custom IF function supporting nested conditional logic.

## Quick Start

### 1. Installation

```bash
composer require mormat/php-formula-interpreter
```

### 2. Basic Payroll Calculation

```php
require_once 'vendor/autoload.php';

use Mormat\FormulaInterpreter\Compiler;

$compiler = new Compiler();

// Calculate gross pay
$executable = $compiler->compile('basic_salary + transportation_allowance + meal_allowance');

$gross_pay = $executable->run([
    'basic_salary' => 25000,
    'transportation_allowance' => 1000,
    'meal_allowance' => 500,
]);

echo "Gross Pay: " . number_format($gross_pay, 2); // Output: 26,500.00
```

### 3. Using IF for Conditional Pay

```php
use FormulaParser\Functions\IfFunction;

$compiler = new Compiler();
$compiler->registerCustomFunction(new IfFunction());

// Different pay structure by employment type
$formula = "IF(employment_type = 'regular', basic_salary, daily_rate * days_worked)";
$executable = $compiler->compile($formula);

$pay = $executable->run([
    'employment_type' => 'contractual',
    'basic_salary' => 30000,
    'daily_rate' => 1363.64,
    'days_worked' => 18,
]);

echo "Pay: " . number_format($pay, 2); // Output: 24,545.52
```

## Project Structure

```
formula-parser-php/
├── composer.json                           # Package dependencies
├── README.md                                # This file
├── USAGE_GUIDE.md                          # Comprehensive documentation
├── src/
│   └── Functions/
│       └── IfFunction.php                  # Custom IF function
└── examples/
    ├── basic_payroll_usage.php             # Basic payroll calculations
    ├── payroll_if_function_demo.php        # IF function examples
    └── advanced_payroll_examples.php       # Complete payroll systems
```

## Documentation

### [USAGE_GUIDE.md](USAGE_GUIDE.md) - Complete Reference

Comprehensive documentation with payroll focus covering:
- Installation & setup
- Basic payroll concepts (2-phase compilation/execution)
- Basic usage examples (salary, deductions, allowances)
- Custom function development
- Custom IF function for conditional payroll logic
- Complete payroll scenarios (payslips, bonuses, 13th month)
- Best practices & troubleshooting

## Examples

### Basic Payroll Examples
```bash
php examples/basic_payroll_usage.php
```

Demonstrates:
- Gross pay calculation (salary + allowances)
- Net pay calculation (after deductions)
- Daily rate and hourly rate calculations
- Absence deductions
- Late deductions

### IF Function Demonstrations
```bash
php examples/payroll_if_function_demo.php
```

Demonstrates:
- Regular vs contractual employees
- Overtime pay (1.25x, 1.3x, 2.6x multipliers)
- Absence deductions (conditional)
- Holiday and rest day pay
- Night differential
- Nested IF for complex pay structures

### Advanced Payroll Scenarios
```bash
php examples/advanced_payroll_examples.php
```

Complete implementations for:
1. **Complete Monthly Payroll** - Full payslip calculation
2. **Performance Bonuses** - Rating-based bonus system
3. **13th Month Pay** - Year-end bonus calculation
4. **Leave Conversion** - Converting leave credits to cash
5. **Attendance Incentives** - Perfect attendance bonuses
6. **Progressive Tax** - Tax bracket calculations

## Variable Naming Convention

All variables use **snake_case** (lowercase with underscores):

```php
✓ basic_salary
✓ number_of_absences
✓ overtime_hours
✓ daily_rate
✓ is_holiday
✓ sss_contribution
✓ withholding_tax
```

## Key Features

### ✓ Safe Formula Evaluation
No use of dangerous `eval()` function. Formulas are parsed and compiled for secure execution.

### ✓ Correct Operator Associativity (Fixed Jan 2026)
All arithmetic and logical operators now correctly evaluate **left-to-right**, ensuring accurate calculations for chained operations like deductions: `gross - tax - sss - insurance - loans`

### ✓ Support for Multiple Data Types
- Numbers (integers and floats)
- Strings (for status, employee types)
- Arrays (for employee lists)
- Booleans (from comparisons)

### ✓ Rich Operator Support
- Arithmetic: `+`, `-`, `*`, `/` (left-associative ✓)
- Logical: `and`, `or`, `not` (left-associative ✓)
- Comparison: `>`, `<`, `>=`, `<=`, `=` (left-associative ✓)
- Membership: `in` (left-associative ✓)

### ✓ Built-in Functions
- Math: `sqrt()`, `pow()`, `modulo()`, `pi()`, `sin()`, `cos()`
- String: `lowercase()`, `uppercase()`, `capitalize()`
- Array: `count()`

### ✓ Custom IF Function
- **Basic IF**: `IF(condition, true_value, false_value)`
- **Nested IF**: `IF(cond1, val1, IF(cond2, val2, val3))`
- **Flexible return types** (numeric, string, mixed)
- **Numeric conditions** (0 = false, non-zero = true)

## Operator Precedence & Associativity

### Operator Precedence (High → Low)
Operators are evaluated in this order:

| Priority | Operators | Type | Associativity |
|----------|-----------|------|----------------|
| **Highest** | `(`, `)` | Parentheses | N/A |
| **5** | `*`, `/` | Multiplication, Division | **Left** ←→ |
| **4** | `+`, `-` | Addition, Subtraction | **Left** ←→ |
| **3** | `<`, `>`, `<=`, `>=`, `=` | Comparison | **Left** ←→ |
| **2** | `and` | Logical AND | **Left** ←→ |
| **1** | `or` | Logical OR | **Left** ←→ |
| **1** | `in` | Array membership | **Left** ←→ |

### Left-Associativity Explained

All operators evaluate **left-to-right**. This means:

```php
// Subtraction example
$formula = 'a - b - c';
// Evaluates as: (a - b) - c  ←→ LEFT-TO-RIGHT
// NOT as: a - (b - c)  (which would be wrong)

// With actual values:
// 100 - 50 - 25 = (100 - 50) - 25 = 50 - 25 = 25 ✓
// NOT: 100 - (50 - 25) = 100 - 25 = 75 ✗
```

This is especially important for payroll deduction chains:

```php
// Taxable Income Calculation (Left-to-Right)
$formula = 'gross_pay - sss - hdmfpag_ibig - philhealth';

// Example with real values:
// 38,160 - 1,750 - 100 - 954
// = ((38,160 - 1,750) - 100) - 954
// = (36,410 - 100) - 954
// = 36,310 - 954
// = 35,356 ✓

// If evaluated wrong (right-to-left):
// 38,160 - (1,750 - (100 - 954))
// = 38,160 - (1,750 - (-854))
// = 38,160 - 2,604
// = 35,556 ✗ (WRONG - $200 difference!)
```

### Using Parentheses for Clarity

Even though operations evaluate left-to-right by default, use parentheses to make your intent clear:

```php
// Good practice - explicit
$formula = '((base_salary + allowances) - deductions)';

// Also correct - but less clear
$formula = 'base_salary + allowances - deductions';
```

## Payroll Formula Examples

### Simple Salary Calculation
```php
$formula = 'basic_salary + transportation_allowance + meal_allowance';
// Example: 25000 + 1000 + 500 = 26,500
```

### Daily Rate Based Pay
```php
$formula = 'daily_rate * days_present';
// Example: 1363.64 * 20 = 27,272.73
```

### Hourly Rate With Overtime
```php
$formula = 'IF(hours_worked > 8, (hourly_rate * 8) + ((hours_worked - 8) * hourly_rate * 1.25), hourly_rate * hours_worked)';
// Example: 170.45 * 8 + (2 * 170.45 * 1.25) = 1,874.95
```

### Absence Deduction
```php
$formula = 'IF(number_of_absences > 0, daily_rate * number_of_absences, 0)';
// Example: 1363.64 * 2 = 2,727.27
```

### Employment Type Pay
```php
$formula = "IF(employment_type = 'regular', basic_salary, IF(employment_type = 'contractual', daily_rate * days_worked, hourly_rate * hours_worked))";
```

### Holiday Pay
```php
$formula = 'IF(is_holiday, daily_rate * 2, daily_rate)';
// Example: Holiday = 1363.64 * 2 = 2,727.27
```

### Night Differential
```php
$formula = 'IF(night_shift_hours > 0, (hourly_rate * night_shift_hours) + (hourly_rate * night_shift_hours * 0.10), 0)';
// Example: (170.45 * 8) + (170.45 * 8 * 0.10) = 1,499.96
```

## Payroll Scenarios Covered

### 1. Basic Salary Calculations
- basic_salary + allowances - deductions
- daily_rate * days_worked
- hourly_rate * hours_worked

### 2. Overtime Calculations
- Regular overtime (1.25x for hours > 8)
- Special overtime (1.3x for rest days)
- Holiday overtime (2.6x for holidays)

### 3. Leave and Deductions
- Absence deductions: daily_rate * number_of_absences
- Late deductions: (hourly_rate / 60) * minutes_late
- Unpaid leave handling

### 4. Allowances and Benefits
- Transportation allowance based on attendance
- Meal allowance by rank
- Night differential (10% premium)
- Hazard pay for risky roles

### 5. Tax Calculations
- Progressive tax brackets
- Tax-exempt computations
- Government contributions (SSS, PhilHealth, Pag-IBIG)

### 6. Employment Type Logic
- Regular vs Contractual vs Part-time
- Different pay structures per type
- Pro-rated benefits

### 7. Performance-Based Pay
- Performance bonuses (excellent: 15%, good: 10%, satisfactory: 5%)
- Commission calculations
- Incentive computations

## Common Patterns

### Pattern 1: Compile Once, Run Many Times
```php
// Efficient - compile formula once
$executable = $compiler->compile('basic_salary + allowances - deductions');

foreach ($employees as $employee) {
    $net_pay = $executable->run($employee);
}

// Inefficient - recompiles every iteration
foreach ($employees as $employee) {
    $executable = $compiler->compile('basic_salary + allowances - deductions');
    $net_pay = $executable->run($employee);
}
```

### Pattern 2: Conditional Payroll
```php
// Use IF for different payment structures
$formula = "IF(employment_type = 'regular', " .
           "basic_salary, " .
           "IF(employment_type = 'contractual', " .
           "daily_rate * days_worked, " .
           "hourly_rate * hours_worked))";
```

### Pattern 3: Store Formulas in Configuration
```php
// config/payroll.php
return [
    'gross_pay' => 'basic_salary + allowances',
    'overtime' => 'IF(overtime_hours > 0, (hourly_rate * overtime_hours * 1.25), 0)',
    'deductions' => 'sss + philhealth + pagibig + tax',
    'net_pay' => 'gross_pay - deductions',
];

// In payroll processor
$formulas = require 'config/payroll.php';
$executables = [];
foreach ($formulas as $name => $formula) {
    $executables[$name] = $compiler->compile($formula);
}
```

## Best Practices

1. **Pre-compile complex formulas** - Store and reuse compiled formulas
2. **Validate variables** - Ensure all required variables are provided
3. **Document formulas** - Add comments explaining complex payroll logic
4. **Test edge cases** - Include unit tests for boundary conditions
5. **Use snake_case** - Maintain consistent variable naming
6. **Handle zero/empty values** - Use IF for optional components

## Performance Tips

1. **Compile once, execute many times** - Compilation is the expensive operation
2. **Cache compiled formulas** - Store in memory or cache layer
3. **Use numeric comparisons** - Faster than string matching
4. **Minimize nesting depth** - Deeply nested IFs are slower

## Running Examples

```bash
# Install dependencies
composer install

# Run basic payroll examples
php examples/basic_payroll_usage.php

# Run IF function demonstrations
php examples/payroll_if_function_demo.php

# Run advanced payroll scenarios
php examples/advanced_payroll_examples.php
```

## Creating Custom Functions

To create custom payroll functions:

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
        return count($params) === 2 &&
               is_numeric($params[0]) &&
               is_numeric($params[1]);
    }

    public function execute(array $params): mixed
    {
        [$hourly_rate, $overtime_hours] = $params;
        return ($hourly_rate * 1.25) * $overtime_hours;
    }
}

// Usage
$compiler->registerCustomFunction(new ComputeOvertimeFunction());
$result = $compiler->compile('regular_pay + COMPUTE_OVERTIME(hourly_rate, overtime_hours)')
    ->run(['regular_pay' => 5000, 'hourly_rate' => 170.45, 'overtime_hours' => 5]);
```

## File Summary

| File | Purpose |
|------|---------|
| `composer.json` | Package dependencies and autoloading |
| `USAGE_GUIDE.md` | Comprehensive documentation with payroll examples |
| `README.md` | Quick start and overview (this file) |
| `src/Functions/IfFunction.php` | Custom IF function implementation |
| `examples/basic_payroll_usage.php` | Basic payroll calculations |
| `examples/payroll_if_function_demo.php` | IF function demonstrations |
| `examples/advanced_payroll_examples.php` | Complete payroll systems |

## Troubleshooting

### IF Function Not Found
```php
// Register the IF function before compiling
$compiler->registerCustomFunction(new IfFunction());
```

### Variable Name Mismatch
```php
// Ensure variable names match exactly (case-sensitive)
// Formula uses: daily_rate * days_present
// Data must have: ['daily_rate' => 1363.64, 'days_present' => 20]
```

### Type Errors
```php
// Ensure variables are correct types
// Numbers should be numeric, not strings
$executable->run(['hours' => 40, 'rate' => 170.45]);  // ✓ Correct
$executable->run(['hours' => '40', 'rate' => '170.45']);  // May have issues
```

## References

- **GitHub**: https://github.com/mormat/php-formula-interpreter
- **Supported Operators**: `+`, `-`, `*`, `/`, `>`, `<`, `>=`, `<=`, `=`, `and`, `or`, `not`, `in`
- **License**: GPL-2.0

---

**Last Updated**: 2026-01-28

## 🔴 Critical Fix - January 2026

**Operator Associativity Fix**: The interpreter now correctly evaluates chained arithmetic operations from **left-to-right** instead of right-to-left. This fixes critical calculation errors in payroll deduction formulas.

- **Impact**: Deduction chains like `gross - tax - sss - insurance` now compute correctly
- **Example Fix**: Formula `38160 - 1750 - 100 - 954` now returns `35,356` (correct) instead of `35,556` (was wrong by $200)
- **For Details**: See [OPERATOR_ASSOCIATIVITY_FIX_REPORT.md](OPERATOR_ASSOCIATIVITY_FIX_REPORT.md)

For comprehensive documentation, see [USAGE_GUIDE.md](USAGE_GUIDE.md)
