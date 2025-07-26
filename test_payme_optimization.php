<?php
/**
 * Test script for PaymeController optimization
 *
 * This script simulates a payment request to test if the optimized PaymeController
 * can handle the request without timeout errors.
 */

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Create a mock payment request
function createMockPaymentRequest() {
    // Generate a unique ID
    $id = Str::uuid();

    // Create a payment request with test data
    $paymentRequest = new PaymentRequest();
    $paymentRequest->id = $id;
    $paymentRequest->payment_amount = 100.00;
    $paymentRequest->currency_code = 'UZS';
    $paymentRequest->is_paid = 0;

    // Create additional data with customer information
    $additionalData = [
        'customer_id' => 1, // Use an existing customer ID
        'is_guest' => 0,
        'order_note' => 'Test order',
        'address_id' => 1, // Use an existing address ID
        'billing_address_id' => 1 // Use an existing billing address ID
    ];

    $paymentRequest->additional_data = json_encode($additionalData);

    // Save the payment request
    $paymentRequest->save();

    return $id;
}

// Test the payment function
function testPaymentFunction($paymentId) {
    echo "Testing PaymeController::payment function with payment_id: $paymentId\n";
    echo "=================================================================\n\n";

    // Create a mock request
    $request = Request::create('/payment', 'POST', [
        'payment_id' => $paymentId
    ]);

    // Create a controller instance
    $controller = new PaymeController(new PaymentRequest());

    // Set a start time to measure execution time
    $startTime = microtime(true);

    try {
        // Call the payment function
        $response = $controller->payment($request);

        // Calculate execution time
        $executionTime = microtime(true) - $startTime;

        // Check if the response is a redirect
        if (method_exists($response, 'getTargetUrl')) {
            echo "Success! Payment function completed in " . round($executionTime, 2) . " seconds.\n";
            echo "Redirect URL: " . $response->getTargetUrl() . "\n\n";
        } else {
            echo "Response received but not a redirect. Execution time: " . round($executionTime, 2) . " seconds.\n";
            echo "Response: " . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n\n";
        }
    } catch (\Exception $e) {
        // Calculate execution time
        $executionTime = microtime(true) - $startTime;

        echo "Error! Payment function failed after " . round($executionTime, 2) . " seconds.\n";
        echo "Exception: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\n";
    }
}

// Main test function
function runTest() {
    echo "PaymeController Optimization Test\n";
    echo "================================\n\n";

    try {
        // Create a mock payment request
        $paymentId = createMockPaymentRequest();
        echo "Created mock payment request with ID: $paymentId\n\n";

        // Test the payment function
        testPaymentFunction($paymentId);

        echo "Test completed.\n";
    } catch (\Exception $e) {
        echo "Test setup failed: " . $e->getMessage() . "\n";
    }
}

// Run the test
runTest();
