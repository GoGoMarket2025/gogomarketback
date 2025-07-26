# Payme Order Creation Fix

## Issue Description

The Payme payment integration was not creating orders in the database. The issue was that the PaymeController was not following the project's architecture for payment processing and order creation.

## Root Cause Analysis

After investigating the issue, we identified the following problems:

1. **Architectural Mismatch**: The PaymeController was trying to create orders directly in the `createTransaction` method, instead of using the standard flow used by other payment methods.

2. **Missing Success Hook**: Other payment methods (like PayPal) mark the payment request as paid and rely on a success hook function (`digital_payment_success`) to create orders. The PaymeController was not marking the payment request as paid, so the success hook was never called.

3. **Incomplete Data Flow**: The PaymeController was storing the order reference ID but not properly handling the transaction ID, which is needed to link the payment request to the transaction.

## Solution

We refactored the PaymeController to follow the project's architecture for payment processing and order creation:

### 1. Updated the payment method

**Before:**
```php
public function payment(Request $request): JsonResponse|RedirectResponse
{
    // ...
    // Generate a unique reference ID for this payment
    $uniqueID = \App\Utils\OrderManager::generateUniqueOrderID();

    // Store the unique ID in the payment data for later use
    $additionalData = json_decode($payment_data->additional_data, true);
    $additionalData['payme_order_reference'] = $uniqueID;
    $payment_data->additional_data = json_encode($additionalData);
    $payment_data->save();

    // Continue with payment gateway redirection
    $amount = round($payment_data->payment_amount * 100);
    $payload = "m={$this->config_values->merchant_id};ac.order_id={$uniqueID};amount={$amount}";
    $encoded = rtrim(base64_encode($payload), '=');
    $payme_url = "https://checkout.paycom.uz/{$encoded}";

    return redirect()->away($payme_url);
}
```

**After:**
```php
public function payment(Request $request): JsonResponse|RedirectResponse
{
    // ...
    // Generate a unique reference ID for this payment
    $uniqueID = \App\Utils\OrderManager::generateUniqueOrderID();

    // Store the unique ID in the payment data for later use
    $additionalData = json_decode($payment_data->additional_data, true);
    $additionalData['payme_order_reference'] = $uniqueID;
    $payment_data->additional_data = json_encode($additionalData);
    $payment_data->save();

    // Continue with payment gateway redirection
    $amount = round($payment_data->payment_amount * 100);
    $payload = "m={$this->config_values->merchant_id};ac.order_id={$uniqueID};amount={$amount}";
    $encoded = rtrim(base64_encode($payload), '=');
    $payme_url = "https://checkout.paycom.uz/{$encoded}";

    return redirect()->away($payme_url);
}
```

The payment method remains largely the same, as it was already correctly storing the order reference ID in the payment request's additional_data.

### 2. Updated the createTransaction method

**Before:**
```php
private function createTransaction($data): JsonResponse
{
    // ...
    // Check if order exists
    $order = \App\Models\Order::where('order_group_id', $orderId)->first();

    // If order doesn't exist, try to create it
    if (!$order) {
        // Find payment request with this reference ID
        $paymentRequest = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->payme_order_reference', $orderId)
            ->first();

        if (!$paymentRequest) {
            return $this->error(-31050, 'Payment request not found');
        }

        $additionalData = json_decode($paymentRequest->additional_data, true);

        // Check if we have cart_group_id to create an order
        if (!isset($additionalData['cart_group_id'])) {
            return $this->error(-31050, 'Cart information not found');
        }

        // Create the order now
        $data = [
            'payment_method' => 'payme',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'transaction_ref' => $paymentRequest->id,
            'order_group_id' => $orderId,
            'cart_group_id' => $additionalData['cart_group_id'],
        ];

        $orderId = \App\Utils\OrderManager::generate_order($data);
        $order = \App\Models\Order::find($orderId);

        if (!$order) {
            return $this->error(-31050, 'Failed to create order');
        }
    }
    // ...
}
```

**After:**
```php
private function createTransaction($data): JsonResponse
{
    // ...
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
    // ...
}
```

The key change is that we removed the direct order creation and instead store the transaction ID in the payment request's additional_data for later use.

### 3. Updated the performTransaction method

**Before:**
```php
private function performTransaction($data): JsonResponse
{
    // ...
    // Find transaction
    $transaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)->first();
    if (!$transaction) {
        return $this->error(-31050, 'Transaction not found');
    }

    // Update transaction status
    $transaction->status = 'success';
    $transaction->save();

    // Update order payment status
    $order = \App\Models\Order::find($transaction->order_id);
    if ($order) {
        $order->payment_status = 'paid';
        $order->save();
    }
    // ...
}
```

**After:**
```php
private function performTransaction($data): JsonResponse
{
    // ...
    // Find payment request with this transaction ID
    $paymentRequest = $this->payment::where('is_paid', 0)
        ->whereJsonContains('additional_data->payme_transaction_id', $transactionId)
        ->first();

    if ($paymentRequest) {
        // Mark payment as paid
        $paymentRequest->is_paid = 1;
        $paymentRequest->payment_method = 'payme';
        $paymentRequest->transaction_id = $transactionId;
        $paymentRequest->save();

        // Call success hook to create orders
        if (function_exists('digital_payment_success')) {
            digital_payment_success($paymentRequest);
        }
    }
    // ...
}
```

The key change is that we now mark the payment request as paid and call the `digital_payment_success` function, which is responsible for creating orders.

### 4. Updated the checkPerformTransaction method

We also improved the checkPerformTransaction method to better validate the payment request and ensure it has all the required data for order creation.

## Benefits of the Fix

1. **Architectural Consistency**: The PaymeController now follows the same architecture as other payment methods in the project.

2. **Proper Order Creation**: Orders are now created through the standard `digital_payment_success` function, ensuring consistency with other payment methods.

3. **Better Error Handling**: The updated code includes better validation and error handling, making it more robust.

4. **Improved Testability**: The code is now easier to test, as demonstrated by the comprehensive test script.

## Testing

A comprehensive test script (`test_payme_fix.php`) was created to verify the fix. The script:

1. Creates a test cart with a unique cart_group_id
2. Creates a payment request with all required fields
3. Calls the payment method to generate and store a unique order reference
4. Simulates the Payme API calls (CheckPerformTransaction, CreateTransaction, PerformTransaction)
5. Verifies that the payment request is marked as paid and orders are created with the correct data

The test confirms that the fix works correctly and orders are now created in the database when a payment is completed through Payme.
