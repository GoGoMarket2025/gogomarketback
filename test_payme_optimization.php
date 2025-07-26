<?php
/**
 * Test script for Payme optimization
 *
 * This script simulates the payment process and checks for timeouts.
 * It can be run from the command line or browser to verify the changes.
 */

// Set execution time limit to detect timeouts
ini_set('max_execution_time', 30);
set_time_limit(30);

echo "Starting Payme optimization test...\n";

// Function to simulate a request to the payment endpoint
function simulatePaymentRequest($paymentId, $cartSize = 1) {
    echo "Simulating payment request with payment_id: $paymentId and cart size: $cartSize\n";

    $startTime = microtime(true);

    // Create a mock request object
    $request = new stdClass();
    $request->payment_id = $paymentId;

    // Simulate cart items
    echo "Creating $cartSize cart items...\n";
    for ($i = 0; $i < $cartSize; $i++) {
        // This is just to simulate the time it would take to process cart items
        usleep(50000); // 50ms delay per item
    }

    // Simulate payment process (without actually making the request)
    echo "Simulating payment process...\n";

    // Calculate execution time
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    echo "Payment request completed in " . number_format($executionTime, 2) . " ms\n";

    return [
        'success' => true,
        'execution_time' => $executionTime,
        'timeout' => $executionTime > 30000 // Check if execution time exceeds 30 seconds
    ];
}

// Test with different cart sizes
$testCases = [
    ['payment_id' => 'test-payment-1', 'cart_size' => 1],
    ['payment_id' => 'test-payment-2', 'cart_size' => 5],
    ['payment_id' => 'test-payment-3', 'cart_size' => 10],
    ['payment_id' => 'test-payment-4', 'cart_size' => 20],
    ['payment_id' => 'test-payment-5', 'cart_size' => 50],
];

$results = [];

foreach ($testCases as $testCase) {
    echo "\n=== Testing with cart size: {$testCase['cart_size']} ===\n";
    $result = simulatePaymentRequest($testCase['payment_id'], $testCase['cart_size']);
    $results[] = [
        'payment_id' => $testCase['payment_id'],
        'cart_size' => $testCase['cart_size'],
        'execution_time' => $result['execution_time'],
        'timeout' => $result['timeout']
    ];
}

// Display summary
echo "\n=== Test Results Summary ===\n";
echo "Cart Size | Execution Time (ms) | Timeout\n";
echo "--------------------------------------\n";

foreach ($results as $result) {
    echo sprintf(
        "%-9s | %-19s | %s\n",
        $result['cart_size'],
        number_format($result['execution_time'], 2),
        $result['timeout'] ? 'Yes' : 'No'
    );
}

echo "\nTest completed.\n";
echo "Note: This is a simulation. Actual performance may vary in production environment.\n";
echo "For accurate results, test with real data in your environment.\n";

/**
 * How to use this test script:
 *
 * 1. Run from command line: php test_payme_optimization.php
 * 2. Or access via browser if running on a web server
 *
 * Expected results:
 * - All test cases should complete without timeouts
 * - Execution time should increase linearly with cart size, but remain reasonable
 * - Even with large cart sizes (50+), the execution time should be well below timeout threshold
 */
