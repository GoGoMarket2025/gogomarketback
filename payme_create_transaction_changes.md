# Payme CreateTransaction Method Changes

## Overview
This document describes the changes made to the `createTransaction` method in the `PaymeController` class to implement the functionality based on the sample code provided in the issue description.

## Problem
The original implementation of the `createTransaction` method in `PaymeController` did not fully match the required functionality. It was missing several key features:

1. It didn't properly check for existing transactions in the created state
2. It didn't create a transaction record in the database
3. It didn't handle the transaction state properly
4. It didn't follow the same error handling pattern as the sample code

## Solution
The modified implementation now:
1. First checks if the order exists by order_group_id
2. If the order doesn't exist, checks if there's a payment request
3. Validates the amount against either the order amount or the payment request amount
4. Checks if there's an existing transaction in the 'pending' state (equivalent to STATE_CREATED in the sample code)
5. Checks if there's any transaction for this order
6. Creates a new OrderTransaction record if we have an order
7. Stores the transaction ID in the payment request's additional_data if we have a payment request
8. Returns appropriate responses based on the transaction state

## Implementation Details

### Original Code
```php
private function createTransaction($data): JsonResponse
{
    $params = $data['params'] ?? [];
    $account = $params['account'] ?? [];
    $orderId = $account['order_id'] ?? null;
    $transactionId = $params['id'] ?? null;
    $time = $params['time'] ?? null;
    $amount = $params['amount'] ?? 0;

    if (!$orderId || !$transactionId || !$time) {
        return $this->error(-31050, 'Missing required parameters');
    }

    // Find payment request with this reference ID
    $paymentRequest = $this->payment::where('is_paid', 0)
        ->whereJsonContains('additional_data->payme_order_reference', $orderId)
        ->first();

    if (!$paymentRequest) {
        // If payment request not found, check if order already exists
        $order = \App\Models\Order::where('order_group_id', $orderId)->first();
        if (!$order) {
            return $this->error(-31050, 'Payment request not found');
        }
    }

    if (round($paymentRequest->payment_amount * 100) != $amount) {
        return $this->error(-31001, 'Incorrect amount.');
    }

    // Check if transaction already exists
    $transaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)->first();
    if ($transaction) {
        return response()->json([
            'result' => [
                'create_time' => (int)$transaction->created_at->timestamp * 1000,
                'transaction' => $transactionId,
                'state' => 1
            ]
        ]);
    }

    // If we have a payment request, store the transaction ID for later use
    if ($paymentRequest) {
        $additionalData = json_decode($paymentRequest->additional_data, true);
        $additionalData['payme_transaction_id'] = $transactionId;
        $paymentRequest->additional_data = json_encode($additionalData);
        $paymentRequest->save();
    }

    return response()->json([
        'result' => [
            'create_time' => time() * 1000,
            'transaction' => $transactionId,
            'state' => 1
        ]
    ]);
}
```

### Modified Code
```php
private function createTransaction($data): JsonResponse
{
    $params = $data['params'] ?? [];
    $account = $params['account'] ?? [];
    $orderId = $account['order_id'] ?? null;
    $transactionId = $params['id'] ?? null;
    $time = $params['time'] ?? null;
    $amount = $params['amount'] ?? 0;

    if (!$orderId || !$transactionId || !$time) {
        return $this->error(-31050, 'Missing required parameters');
    }

    // Find order by order_group_id
    $order = \App\Models\Order::where('order_group_id', $orderId)->first();
    if (!$order) {
        // If order not found, check if there's a payment request
        $paymentRequest = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->payme_order_reference', $orderId)
            ->first();
            
        if (!$paymentRequest) {
            return $this->error(-31050, 'Order not found.');
        }
        
        // Validate amount against payment request
        if (round($paymentRequest->payment_amount * 100) != $amount) {
            return $this->error(-31001, 'Incorrect amount.');
        }
    } else {
        // Validate amount against order amount
        if (round($order->order_amount * 100) != $amount) {
            return $this->error(-31001, 'Incorrect amount.');
        }
    }

    // Check if there's a transaction in created state
    $existingTransaction = \App\Models\OrderTransaction::where('order_id', $order->id ?? null)
        ->where('transaction_id', $transactionId)
        ->where('status', 'pending')
        ->first();
        
    if ($existingTransaction) {
        if ($time > $existingTransaction->created_at->timestamp * 1000) {
            return $this->error(-31050, 'Уже имеется платеж в ожидании для оплаты');
        }
        
        return response()->json([
            'result' => [
                'create_time' => (int)$existingTransaction->created_at->timestamp * 1000,
                'transaction' => $transactionId,
                'state' => 1
            ]
        ]);
    }

    // Check if there's any transaction for this order
    $anyTransaction = \App\Models\OrderTransaction::where('order_id', $order->id ?? null)
        ->where('transaction_id', $transactionId)
        ->first();
        
    if ($anyTransaction) {
        return response()->json([
            'result' => [
                'create_time' => (int)$anyTransaction->created_at->timestamp * 1000,
                'transaction' => $transactionId,
                'state' => 1
            ]
        ]);
    }

    // If we have a payment request, store the transaction ID for later use
    if (isset($paymentRequest)) {
        $additionalData = json_decode($paymentRequest->additional_data, true);
        $additionalData['payme_transaction_id'] = $transactionId;
        $paymentRequest->additional_data = json_encode($additionalData);
        $paymentRequest->save();
    }

    // Create a new transaction if we have an order
    if (isset($order)) {
        $transaction = new \App\Models\OrderTransaction();
        $transaction->order_id = $order->id;
        $transaction->customer_id = $order->customer_id;
        $transaction->seller_id = $order->seller_id;
        $transaction->seller_is = $order->seller_is;
        $transaction->transaction_id = $transactionId;
        $transaction->order_amount = $order->order_amount;
        $transaction->payment_method = 'payme';
        $transaction->status = 'pending'; // Equivalent to STATE_CREATED
        $transaction->save();

        return response()->json([
            'result' => [
                'create_time' => (int)$transaction->created_at->timestamp * 1000,
                'transaction' => $transactionId,
                'state' => 1
            ]
        ]);
    }

    // If we don't have an order yet (only payment request), return a generic response
    return response()->json([
        'result' => [
            'create_time' => $time,
            'transaction' => $transactionId,
            'state' => 1
        ]
    ]);
}
```

## Key Changes

1. **Order Validation**:
   - First checks if the order exists by order_group_id
   - If the order doesn't exist, checks if there's a payment request
   - Returns a standardized error message "Order not found" with code -31050 when neither the order nor payment request exists

2. **Amount Validation**:
   - Validates the amount against either the order amount or the payment request amount
   - Returns an error with code -31001 and message "Incorrect amount." if the amounts don't match

3. **Transaction State Handling**:
   - Checks if there's an existing transaction in the 'pending' state (equivalent to STATE_CREATED in the sample code)
   - Returns an error if the new transaction's time is later than the existing transaction's time
   - Returns the existing transaction's details if it exists

4. **Transaction Creation**:
   - Creates a new OrderTransaction record if we have an order
   - Sets the status to 'pending' (equivalent to STATE_CREATED in the sample code)
   - Returns the transaction details in the response

5. **Payment Request Handling**:
   - Stores the transaction ID in the payment request's additional_data if we have a payment request
   - This allows the performTransaction method to find the payment request later

## Testing

A test script (`test_payme_transactions.php`) has been created to verify the implementation. The script tests four scenarios:

1. **Order not found**: Tests that the method returns an error when the order_id doesn't exist
2. **Incorrect amount**: Tests that the method returns an error when the amount parameter doesn't match the order amount
3. **Existing transaction in pending state**: Tests that the method returns an error when there's an existing transaction in pending state and the new transaction's time is later
4. **Successful transaction creation**: Tests that the method successfully creates a new transaction when all parameters are valid

## Benefits

1. **Improved Validation**: The implementation now properly validates the order existence and payment amount, preventing potential payment discrepancies.
2. **Better Transaction Handling**: The implementation now properly handles transaction states and prevents duplicate transactions.
3. **Database Record Creation**: The implementation now creates a proper transaction record in the database, which can be used by other methods.
4. **Standardized Error Messages**: The error messages now follow the required format, making it easier for clients to understand and handle errors.
5. **Compatibility with Sample Code**: The implementation now follows the same logic as the sample code but is adapted to the current architecture.

## Conclusion

The updated implementation of the createTransaction method now matches the functionality of the sample code provided in the issue description while adapting it to the current architecture of the project. It properly validates orders and amounts, handles transaction states, creates transaction records in the database, and returns appropriate responses based on the transaction state.
