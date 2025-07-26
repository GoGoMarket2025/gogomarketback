<?php

// This script tests the PaymeController transaction methods
// It simulates Payme API calls to the handle method

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\Order;
use App\Models\OrderTransaction;

// Helper function to make API requests
function makePaymeRequest($method, $params = []) {
    // Create a request object with Authorization header
    $request = Request::create('/handle', 'POST', [
        'method' => $method,
        'params' => $params,
        'id' => rand(1000, 9999)
    ]);

    // Get the merchant key from config
    $config = DB::table('addon_settings')
        ->where('key_name', 'payme')
        ->where('settings_type', 'payment_config')
        ->first();

    $configValues = json_decode($config->test_values);
    $merchantKey = $configValues->merchant_key;

    // Add Authorization header
    $request->headers->set('Authorization', 'Basic ' . base64_encode($merchantKey));

    // Create a PaymeController instance
    $controller = app()->make(PaymeController::class);

    // Call the handle method
    $response = $controller->handle($request);

    // Return the response
    return json_decode($response->getContent(), true);
}

// Create a test order
echo "Creating test order...\n";
$orderGroupId = \App\Utils\OrderManager::generateUniqueOrderID();
$order = new Order();
$order->order_group_id = $orderGroupId;
$order->customer_id = 1; // Assuming customer ID 1 exists
$order->payment_status = 'unpaid';
$order->order_status = 'pending';
$order->payment_method = 'payme';
$order->save();

echo "Test order created with ID: {$order->id}\n";
echo "Order group ID: {$orderGroupId}\n";

// Test CheckPerformTransaction
echo "\nTesting CheckPerformTransaction...\n";
$response = makePaymeRequest('CheckPerformTransaction', [
    'account' => [
        'order_id' => $orderGroupId
    ],
    'amount' => 10000 // 100 sum in tiyin
]);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

// Test CreateTransaction
echo "\nTesting CreateTransaction...\n";
$transactionId = time() . rand(1000, 9999);
$response = makePaymeRequest('CreateTransaction', [
    'account' => [
        'order_id' => $orderGroupId
    ],
    'id' => $transactionId,
    'time' => time() * 1000,
    'amount' => 10000 // 100 sum in tiyin
]);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

// Test CheckTransaction
echo "\nTesting CheckTransaction...\n";
$response = makePaymeRequest('CheckTransaction', [
    'id' => $transactionId
]);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

// Test PerformTransaction
echo "\nTesting PerformTransaction...\n";
$response = makePaymeRequest('PerformTransaction', [
    'id' => $transactionId
]);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

// Verify order payment status
$order->refresh();
echo "\nOrder payment status after PerformTransaction: {$order->payment_status}\n";

// Test CancelTransaction
echo "\nTesting CancelTransaction...\n";
$response = makePaymeRequest('CancelTransaction', [
    'id' => $transactionId,
    'reason' => 1
]);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

// Verify order payment status
$order->refresh();
echo "\nOrder payment status after CancelTransaction: {$order->payment_status}\n";

// Clean up
echo "\nCleaning up...\n";
OrderTransaction::where('transaction_id', $transactionId)->delete();
$order->delete();

echo "Test completed\n";
