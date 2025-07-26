<?php
/**
 * Test script for PaymeController::checkPerformTransaction method
 *
 * This script simulates Payme API requests to test the checkPerformTransaction method
 * with different scenarios:
 * 1. Order not found
 * 2. Incorrect amount
 * 3. Successful validation
 */

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\PaymentRequest;
use App\Models\Order;
use Illuminate\Http\Request;

// Create a mock request
$controller = new PaymeController(new PaymentRequest());

echo "Testing PaymeController::checkPerformTransaction method\n";
echo "======================================================\n\n";

// Test case 1: Order not found
echo "Test Case 1: Order not found\n";
echo "----------------------------\n";
$data = [
    'params' => [
        'account' => [
            'order_id' => 'non_existent_order_id'
        ],
        'amount' => 10000 // 100 UZS in tiyin
    ]
];

$response = $controller->checkPerformTransaction($data);
$responseData = json_decode($response->getContent(), true);
echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

// Test case 2: Incorrect amount
echo "Test Case 2: Incorrect amount\n";
echo "----------------------------\n";

// First, find an existing order
$order = Order::first();
if ($order) {
    $data = [
        'params' => [
            'account' => [
                'order_id' => $order->order_group_id
            ],
            'amount' => round($order->order_amount * 100) + 1000 // Add 10 UZS to make it incorrect
        ]
    ];

    $response = $controller->checkPerformTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Order amount: " . $order->order_amount . " UZS (" . round($order->order_amount * 100) . " tiyin)\n";
    echo "Request amount: " . ($data['params']['amount'] / 100) . " UZS (" . $data['params']['amount'] . " tiyin)\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No orders found in the database. Skipping test case 2.\n\n";
}

// Test case 3: Successful validation
echo "Test Case 3: Successful validation\n";
echo "--------------------------------\n";

// Use the same order but with correct amount
if ($order) {
    $data = [
        'params' => [
            'account' => [
                'order_id' => $order->order_group_id
            ],
            'amount' => round($order->order_amount * 100) // Correct amount in tiyin
        ]
    ];

    $response = $controller->checkPerformTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Order amount: " . $order->order_amount . " UZS (" . round($order->order_amount * 100) . " tiyin)\n";
    echo "Request amount: " . ($data['params']['amount'] / 100) . " UZS (" . $data['params']['amount'] . " tiyin)\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No orders found in the database. Skipping test case 3.\n\n";
}

echo "Test completed.\n";
