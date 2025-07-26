<?php
/**
 * Test script for PaymeController::createTransaction method
 *
 * This script simulates Payme API requests to test the createTransaction method
 * with different scenarios:
 * 1. Order not found
 * 2. Incorrect amount
 * 3. Existing transaction in pending state
 * 4. Any existing transaction
 * 5. Successful transaction creation
 */

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\Payment_Methods\PaymeController;
use App\Models\PaymentRequest;
use App\Models\Order;
use App\Models\OrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Create a mock request
$controller = new PaymeController(new PaymentRequest());

echo "Testing PaymeController::createTransaction method\n";
echo "===============================================\n\n";

// Generate a unique transaction ID for testing
$transactionId = Str::uuid();
$currentTime = time() * 1000; // Current time in milliseconds

// Test case 1: Order not found
echo "Test Case 1: Order not found\n";
echo "----------------------------\n";
$data = [
    'params' => [
        'account' => [
            'order_id' => 'non_existent_order_id'
        ],
        'id' => $transactionId,
        'time' => $currentTime,
        'amount' => 10000 // 100 UZS in tiyin
    ]
];

$response = $controller->createTransaction($data);
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
            'id' => $transactionId,
            'time' => $currentTime,
            'amount' => round($order->order_amount * 100) + 1000 // Add 10 UZS to make it incorrect
        ]
    ];

    $response = $controller->createTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Order amount: " . $order->order_amount . " UZS (" . round($order->order_amount * 100) . " tiyin)\n";
    echo "Request amount: " . ($data['params']['amount'] / 100) . " UZS (" . $data['params']['amount'] . " tiyin)\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No orders found in the database. Skipping test case 2.\n\n";
}

// Test case 3: Existing transaction in pending state
echo "Test Case 3: Existing transaction in pending state\n";
echo "------------------------------------------------\n";

// Create a pending transaction for testing
if ($order) {
    // First, check if a transaction already exists
    $existingTransaction = OrderTransaction::where('order_id', $order->id)
        ->where('transaction_id', $transactionId)
        ->first();

    if (!$existingTransaction) {
        $transaction = new OrderTransaction();
        $transaction->order_id = $order->id;
        $transaction->customer_id = $order->customer_id;
        $transaction->seller_id = $order->seller_id;
        $transaction->seller_is = $order->seller_is;
        $transaction->transaction_id = $transactionId;
        $transaction->order_amount = $order->order_amount;
        $transaction->payment_method = 'payme';
        $transaction->status = 'pending';
        $transaction->save();

        echo "Created a pending transaction for testing.\n";
    } else {
        echo "Transaction already exists, using it for testing.\n";
        $transaction = $existingTransaction;
    }

    // Test with a later time
    $laterTime = $transaction->created_at->timestamp * 1000 + 60000; // 1 minute later

    $data = [
        'params' => [
            'account' => [
                'order_id' => $order->order_group_id
            ],
            'id' => $transactionId,
            'time' => $laterTime,
            'amount' => round($order->order_amount * 100)
        ]
    ];

    $response = $controller->createTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Transaction created at: " . date('Y-m-d H:i:s', $transaction->created_at->timestamp) . "\n";
    echo "Request time: " . date('Y-m-d H:i:s', $laterTime / 1000) . "\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

    // Test with the same time
    $data['params']['time'] = $transaction->created_at->timestamp * 1000;

    $response = $controller->createTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Transaction created at: " . date('Y-m-d H:i:s', $transaction->created_at->timestamp) . "\n";
    echo "Request time: " . date('Y-m-d H:i:s', $data['params']['time'] / 1000) . "\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No orders found in the database. Skipping test case 3.\n\n";
}

// Test case 4: Successful transaction creation
echo "Test Case 4: Successful transaction creation\n";
echo "-------------------------------------------\n";

// Find another order without a transaction
$anotherOrder = Order::whereDoesntHave('orderTransaction')->first();
if ($anotherOrder) {
    $newTransactionId = Str::uuid(); // Generate a new transaction ID

    $data = [
        'params' => [
            'account' => [
                'order_id' => $anotherOrder->order_group_id
            ],
            'id' => $newTransactionId,
            'time' => $currentTime,
            'amount' => round($anotherOrder->order_amount * 100)
        ]
    ];

    $response = $controller->createTransaction($data);
    $responseData = json_decode($response->getContent(), true);
    echo "Order ID: " . $anotherOrder->id . "\n";
    echo "Order Group ID: " . $anotherOrder->order_group_id . "\n";
    echo "Order amount: " . $anotherOrder->order_amount . " UZS (" . round($anotherOrder->order_amount * 100) . " tiyin)\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

    // Verify that the transaction was created
    $createdTransaction = OrderTransaction::where('order_id', $anotherOrder->id)
        ->where('transaction_id', $newTransactionId)
        ->first();

    if ($createdTransaction) {
        echo "Transaction successfully created in the database.\n";
        echo "Transaction ID: " . $createdTransaction->transaction_id . "\n";
        echo "Order ID: " . $createdTransaction->order_id . "\n";
        echo "Status: " . $createdTransaction->status . "\n";
    } else {
        echo "Transaction was not created in the database.\n";
    }
} else {
    echo "No orders without transactions found in the database. Skipping test case 4.\n";
}

echo "\nTest completed.\n";
