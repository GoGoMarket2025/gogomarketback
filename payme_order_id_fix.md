# Payme Order ID Fix

## Issue Description
The issue was that the Payme payment gateway integration was using the payment ID instead of the order ID in the `ac.order_id` parameter of the payload. According to the requirements, we should use the order ID instead.

## Changes Made

### 1. Modified Payload Construction
In the `payment` method of the `PaymeController`, changed the payload construction to use the order_group_id instead of the payment_id:

```php
// Before
$payload = "m={$this->config_values->merchant_id};ac.order_id={$payment_data->id};amount={$amount}";

// After
$payload = "m={$this->config_values->merchant_id};ac.order_id={$orderData['order_group_id']};amount={$amount}";
```

### 2. Updated Callback Methods
Since the callback methods (`checkPerformTransaction` and `createTransaction`) were expecting the payment ID in the `order_id` parameter, we updated them to handle the new order_id parameter correctly:

#### checkPerformTransaction Method
```php
// Get payment_id from the session using order_group_id
$pendingOrderData = session()->get('payme_pending_order', null);
$paymentId = null;

if ($pendingOrderData && $pendingOrderData['order_group_id'] == $orderId) {
    $paymentId = $pendingOrderData['payment_id'];
}

// If payment_id found in session, use it; otherwise, try using orderId directly
$order = $paymentId ? 
    $this->payment::where(['id' => $paymentId])->where(['is_paid' => 0])->first() :
    $this->payment::where(['id' => $orderId])->where(['is_paid' => 0])->first();
```

#### createTransaction Method
```php
// Get payment_id from the session using order_group_id
$pendingOrderData = session()->get('payme_pending_order', null);
$paymentId = null;

if ($pendingOrderData && $pendingOrderData['order_group_id'] == $orderId) {
    $paymentId = $pendingOrderData['payment_id'];
}

// If payment_id found in session, use it; otherwise, try using orderId directly
$order = $paymentId ? 
    $this->payment::where(['id' => $paymentId])->first() :
    $this->payment::where(['id' => $orderId])->first();
```

## Testing
To test these changes:

1. Add items to your cart
2. Proceed to checkout
3. Select Payme as the payment method
4. Click "Proceed to Payment"
5. Verify that you are redirected to the Payme payment page
6. Complete the payment
7. Verify that you are redirected back to the success page
8. Check that orders are created correctly in the admin panel

## Backward Compatibility
The changes maintain backward compatibility by:
1. Storing both the payment_id and order_group_id in the session
2. Trying to find the payment using the order_group_id first, then falling back to using the order_id directly if not found
3. This ensures that existing integrations will continue to work while new ones will use the correct order_id parameter

## Conclusion
These changes ensure that the Payme payment gateway integration uses the order ID instead of the payment ID in the `ac.order_id` parameter of the payload, as required by the issue description.
