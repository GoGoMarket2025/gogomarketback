<?php

// This script tests the refactored PaymeController implementation
// It simulates both web and mobile app payment flows

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\PaymentRequest;

echo "Testing refactored PaymeController implementation\n";
echo "================================================\n\n";

// Create a mock payment request for testing
$paymentId = \Illuminate\Support\Str::uuid()->toString();

// Create a payment request in the database
$paymentRequest = new PaymentRequest();
$paymentRequest->id = $paymentId;
$paymentRequest->payment_amount = 100.00;
$paymentRequest->is_paid = 0;
$paymentRequest->currency_code = 'UZS';

// Create additional data with cart_group_id
$cartGroupId = 'test_cart_group_' . time();
$additionalData = [
    'cart_group_id' => $cartGroupId,
    'customer_id' => 1, // Assuming customer ID 1 exists
    'payment_method' => 'payme',
    'business_name' => 'Test Business',
    'payment_mode' => 'web', // This will be changed for mobile test
];
$paymentRequest->additional_data = json_encode($additionalData);
$paymentRequest->save();

echo "Created payment request with ID: {$paymentId}\n";

// Test 1: Web payment flow
echo "\nTest 1: Web payment flow\n";
echo "----------------------\n";

// Create a request object for web payment
$webRequest = Request::create('/payment', 'POST', [
    'payment_id' => $paymentId
]);

// Create a PaymeController instance
$controller = app()->make(PaymeController::class);

// Call the payment method
echo "Calling payment method for web...\n";
$response = $controller->payment($webRequest);

// Get the updated payment request
$paymentRequest->refresh();
$additionalData = json_decode($paymentRequest->additional_data, true);

// Check if payme_order_reference was added
if (isset($additionalData['payme_order_reference'])) {
    echo "Success! payme_order_reference was added: {$additionalData['payme_order_reference']}\n";
    $orderReference = $additionalData['payme_order_reference'];
} else {
    echo "Error: payme_order_reference was not added\n";
    exit(1);
}

// Test 2: Simulate Payme API calls
echo "\nTest 2: Simulate Payme API calls\n";
echo "-----------------------------\n";

// Simulate CheckPerformTransaction
echo "Simulating CheckPerformTransaction...\n";
$checkData = [
    'method' => 'CheckPerformTransaction',
    'params' => [
        'account' => [
            'order_id' => $orderReference
        ],
        'amount' => 10000 // 100 sum in tiyin
    ]
];

// Create a request object with Authorization header
$checkRequest = Request::create('/handle', 'POST', $checkData);
$checkRequest->headers->set('Authorization', 'Basic ' . base64_encode('test:test')); // Mock auth

// Call the handle method
$checkResponse = $controller->handle($checkRequest);
$checkResult = json_decode($checkResponse->getContent(), true);

echo "CheckPerformTransaction response: " . json_encode($checkResult, JSON_PRETTY_PRINT) . "\n";

// Simulate CreateTransaction
echo "\nSimulating CreateTransaction...\n";
$transactionId = time() . rand(1000, 9999);
$createData = [
    'method' => 'CreateTransaction',
    'params' => [
        'account' => [
            'order_id' => $orderReference
        ],
        'id' => $transactionId,
        'time' => time() * 1000,
        'amount' => 10000 // 100 sum in tiyin
    ]
];

// Create a request object with Authorization header
$createRequest = Request::create('/handle', 'POST', $createData);
$createRequest->headers->set('Authorization', 'Basic ' . base64_encode('test:test')); // Mock auth

// Call the handle method
$createResponse = $controller->handle($createRequest);
$createResult = json_decode($createResponse->getContent(), true);

echo "CreateTransaction response: " . json_encode($createResult, JSON_PRETTY_PRINT) . "\n";

// Check if an order was created
$order = \App\Models\Order::where('order_group_id', $orderReference)->first();

if ($order) {
    echo "\nSuccess! Order was created with ID: {$order->id}\n";
    echo "Order group ID: {$order->order_group_id}\n";
    echo "Payment status: {$order->payment_status}\n";
    echo "Order status: {$order->order_status}\n";
} else {
    echo "\nError: Order was not created\n";
    exit(1);
}

// Test 3: Mobile app scenario
echo "\nTest 3: Mobile app scenario\n";
echo "------------------------\n";

// Create a new payment request for mobile test
$mobilePaymentId = \Illuminate\Support\Str::uuid()->toString();

// Create a payment request in the database
$mobilePaymentRequest = new PaymentRequest();
$mobilePaymentRequest->id = $mobilePaymentId;
$mobilePaymentRequest->payment_amount = 150.00;
$mobilePaymentRequest->is_paid = 0;
$mobilePaymentRequest->currency_code = 'UZS';

// Create additional data with cart_group_id for mobile
$mobileCartGroupId = 'mobile_cart_group_' . time();
$mobileAdditionalData = [
    'cart_group_id' => $mobileCartGroupId,
    'customer_id' => 1,
    'payment_method' => 'payme',
    'business_name' => 'Test Business',
    'payment_mode' => 'app', // This indicates a mobile app request
    'payment_platform' => 'app',
    'payment_request_from' => 'app',
];
$mobilePaymentRequest->additional_data = json_encode($mobileAdditionalData);
$mobilePaymentRequest->save();

echo "Created mobile payment request with ID: {$mobilePaymentId}\n";

// Create a request object for mobile payment
$mobileRequest = Request::create('/payment', 'POST', [
    'payment_id' => $mobilePaymentId
]);

// Call the payment method
echo "Calling payment method for mobile...\n";
$mobileResponse = $controller->payment($mobileRequest);

// Get the updated payment request
$mobilePaymentRequest->refresh();
$mobileAdditionalData = json_decode($mobilePaymentRequest->additional_data, true);

// Check if payme_order_reference was added
if (isset($mobileAdditionalData['payme_order_reference'])) {
    echo "Success! payme_order_reference was added: {$mobileAdditionalData['payme_order_reference']}\n";
    $mobileOrderReference = $mobileAdditionalData['payme_order_reference'];
} else {
    echo "Error: payme_order_reference was not added\n";
    exit(1);
}

// Simulate CreateTransaction for mobile
echo "\nSimulating CreateTransaction for mobile...\n";
$mobileTransactionId = time() . rand(1000, 9999);
$mobileCreateData = [
    'method' => 'CreateTransaction',
    'params' => [
        'account' => [
            'order_id' => $mobileOrderReference
        ],
        'id' => $mobileTransactionId,
        'time' => time() * 1000,
        'amount' => 15000 // 150 sum in tiyin
    ]
];

// Create a request object with Authorization header
$mobileCreateRequest = Request::create('/handle', 'POST', $mobileCreateData);
$mobileCreateRequest->headers->set('Authorization', 'Basic ' . base64_encode('test:test')); // Mock auth

// Call the handle method
$mobileCreateResponse = $controller->handle($mobileCreateRequest);
$mobileCreateResult = json_decode($mobileCreateResponse->getContent(), true);

echo "Mobile CreateTransaction response: " . json_encode($mobileCreateResult, JSON_PRETTY_PRINT) . "\n";

// Check if an order was created for mobile
$mobileOrder = \App\Models\Order::where('order_group_id', $mobileOrderReference)->first();

if ($mobileOrder) {
    echo "\nSuccess! Mobile order was created with ID: {$mobileOrder->id}\n";
    echo "Order group ID: {$mobileOrder->order_group_id}\n";
    echo "Payment status: {$mobileOrder->payment_status}\n";
    echo "Order status: {$mobileOrder->order_status}\n";
} else {
    echo "\nError: Mobile order was not created\n";
    exit(1);
}

// Clean up
echo "\nCleaning up...\n";
if ($order) {
    $order->delete();
}
if ($mobileOrder) {
    $mobileOrder->delete();
}
\App\Models\OrderTransaction::where('transaction_id', $transactionId)->delete();
\App\Models\OrderTransaction::where('transaction_id', $mobileTransactionId)->delete();
$paymentRequest->delete();
$mobilePaymentRequest->delete();

echo "\nTest completed successfully!\n";
