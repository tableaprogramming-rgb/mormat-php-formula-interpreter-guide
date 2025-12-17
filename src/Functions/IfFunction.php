<?php

namespace FormulaParser\Functions;

use Mormat\FormulaInterpreter\Functions\FunctionInterface;

/**
 * Custom IF function for conditional logic in formulas
 *
 * Supports:
 * - Basic IF: IF(condition, true_value, false_value)
 * - Nested IF: IF(condition1, value1, IF(condition2, value2, value3))
 *
 * The condition parameter can be:
 * - A boolean value (true/false)
 * - A numeric value (0/null = false, anything else = true)
 * - Result of a comparison operator (>, <, =, etc.)
 *
 * Example:
 * IF(score >= 60, 'Pass', 'Fail')
 * IF(level >= 3, 100, IF(level >= 2, 50, 10))
 */
class IfFunction implements FunctionInterface
{
    /**
     * Returns the function name as used in formulas
     *
     * @return string
     */
    public function getName(): string
    {
        return 'IF';
    }

    /**
     * Validates whether the provided parameters are appropriate for the IF function
     *
     * Requirements:
     * - Exactly 3 parameters required
     * - First parameter (condition) must be boolean or numeric
     * - Second and third parameters (true_value, false_value) can be any type
     *
     * @param array $params Array of parameters [condition, true_value, false_value]
     * @return bool True if parameters are valid, false otherwise
     */
    public function supports(array $params): bool
    {
        // IF function must have exactly 3 parameters
        if (count($params) !== 3) {
            return false;
        }

        // First parameter must be condition (boolean or numeric)
        $condition = $params[0];

        // Condition can be boolean or numeric (0/null = false, non-zero = true)
        if (!is_bool($condition) && !is_numeric($condition)) {
            return false;
        }

        // The second and third parameters can be any type
        // This allows for flexibility in return values and nested IFs
        return true;
    }

    /**
     * Executes the IF function with provided parameters
     *
     * Returns true_value if condition is true, false_value otherwise
     *
     * @param array $params [condition, true_value, false_value]
     * @return mixed The true_value or false_value based on condition
     */
    public function execute(array $params): mixed
    {
        [$condition, $trueValue, $falseValue] = $params;

        // Convert numeric condition to boolean
        // 0 and null are considered false, all other values are true
        if (is_numeric($condition)) {
            $condition = $condition != 0 && $condition !== null;
        }

        // Return appropriate value based on condition
        return $condition ? $trueValue : $falseValue;
    }
}
