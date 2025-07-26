<?php

// This script tests the PaymeController payment method
// It simulates a payment request and verifies that an order is created

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\PaymentRequest;
use App\Models\Order;

// Create a mock payment request
$paymentId = \Illuminate\Support\Str::uuid()->toString();

// Create a payment request in the database
$paymentRequest = new PaymentRequest();
$paymentRequest->id = $paymentId;
$paymentRequest->payment_amount = 100.00;
$paymentRequest->is_paid = 0;

// Create additional data with cart_group_id
$cartGroupId = 'test_cart_group_' . time();
$additionalData = [
    'cart_group_id' => $cartGroupId,
    'customer_id' => 1, // Assuming customer ID 1 exists
    'payment_method' => 'payme'
];
$paymentRequest->additional_data = json_encode($additionalData);
$paymentRequest->save();

echo "Created payment request with ID: {$paymentId}\n";

// Create a request object
$request = Request::create('/payment', 'POST', [
    'payment_id' => $paymentId
]);

// Create a PaymeController instance
$controller = app()->make(PaymeController::class);

// Call the payment method
echo "Calling payment method...\n";
$response = $controller->payment($request);

// Check if an order was created
$order = Order::where('cart_group_id', $cartGroupId)->first();

if ($order) {
    echo "Success! Order created with ID: {$order->id}\n";
    echo "Order group ID: {$order->order_group_id}\n";
    echo "Payment status: {$order->payment_status}\n";
    echo "Order status: {$order->order_status}\n";
} else {
    echo "Error: Order was not created\n";
}

// Clean up
$paymentRequest->delete();
if ($order) {
    $order->delete();
}

echo "Test completed\n";
