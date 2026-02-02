<?php
require_once 'vendor/autoload.php';

use Mormat\FormulaInterpreter\Compiler;

$compiler = new Compiler();

// Test case: 38160 - 1750 - 100 - 954
$formula = 'basic_pay - sss - hdmfpag_ibig - philhealth';
$executable = $compiler->compile($formula);

$result = $executable->run([
    'basic_pay' => 38160,
    'sss' => 1750,
    'hdmfpag_ibig' => 100,
    'philhealth' => 954,
]);

echo "Formula: $formula\n";
echo "Result: " . number_format($result, 2) . "\n";
echo "Expected (left-to-right): 35,356.00\n";
echo "Actual: " . number_format($result, 2) . "\n";
echo "Match: " . ($result == 35356 ? "✓ PASS" : "✗ FAIL") . "\n";
?>
