# Payme CheckPerformTransaction Method Changes

## Overview
This document describes the changes made to the `checkPerformTransaction` method in the `PaymeController` class to implement additional validation checks for order existence and amount matching.

## Problem
The original implementation of the `checkPerformTransaction` method in `PaymeController` did not properly validate the payment amount against the order price. It also used a different error message for order not found scenarios.

## Solution
The modified implementation now:
1. Returns a standardized error message "Order not found" with code -31050 when the order doesn't exist
2. Adds a check to compare the order price (multiplied by 100 to convert to tiyin) with the amount parameter
3. Returns an error with code -31001 and message "Incorrect amount." if the amounts don't match

## Implementation Details

### Original Code
```php
// Check if order exists
$order = \App\Models\Order::where('order_group_id', $orderId)->first();

// If order doesn't exist, check if there's a valid payment request
if (!$order) {
    // Find payment request with this reference ID
    $paymentRequest = $this->payment::where('is_paid', 0)
        ->whereJsonContains('additional_data->payme_order_reference', $orderId)
        ->first();

    if (!$paymentRequest) {
        return $this->error(-31050, 'Payment request not found');
    }
    
    // ... rest of the code
}

// Check if order is already paid
if ($order->payment_status == 'paid') {
    return $this->error(-31050, 'Order already paid');
}

return response()->json([
    'result' => [
        'allow' => true
    ]
]);
```

### Modified Code
```php
// Check if order exists
$order = \App\Models\Order::where('order_group_id', $orderId)->first();

// If order doesn't exist, check if there's a valid payment request
if (!$order) {
    // Find payment request with this reference ID
    $paymentRequest = $this->payment::where('is_paid', 0)
        ->whereJsonContains('additional_data->payme_order_reference', $orderId)
        ->first();

    if (!$paymentRequest) {
        return $this->error(-31050, 'Order not found');
    }
    
    // ... rest of the code
}

// Check if order is already paid
if ($order->payment_status == 'paid') {
    return $this->error(-31050, 'Order already paid');
}

// Check if the amount matches the order price (converted to tiyin)
if (round($order->order_amount * 100) != $amount) {
    return $this->error(-31001, 'Incorrect amount.');
}

return response()->json([
    'result' => [
        'allow' => true
    ]
]);
```

## Key Changes

1. **Error Message Standardization**:
   - Changed error message from "Payment request not found" to "Order not found" to match the required error message format.

2. **Amount Validation**:
   - Added a check to compare the order's `order_amount` (multiplied by 100 to convert to tiyin) with the amount parameter.
   - Used `round()` function to handle potential floating-point precision issues.
   - Return an error with code -31001 and message "Incorrect amount." if the amounts don't match.

## Testing

A test script (`test_payme_check_perform_transaction.php`) has been created to verify the implementation. The script tests three scenarios:

1. **Order not found**: Tests that the method returns an error with code -31050 and message "Order not found" when the order_id doesn't exist.
2. **Incorrect amount**: Tests that the method returns an error with code -31001 and message "Incorrect amount." when the amount parameter doesn't match the order price.
3. **Successful validation**: Tests that the method returns a success response when the order exists and the amount matches.

## Benefits

1. **Improved Validation**: The implementation now properly validates the payment amount against the order price, preventing potential payment discrepancies.
2. **Standardized Error Messages**: The error messages now follow the required format, making it easier for clients to understand and handle errors.
3. **Better Security**: By validating the payment amount, the implementation helps prevent potential fraud or payment manipulation attempts.
