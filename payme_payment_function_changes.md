# Payme Payment Function Changes

## Overview
This document describes the changes made to the `payment` function in the `PaymeController` class to implement order creation based on the payment_id parameter.

## Problem
The original implementation of the `payment` function in `PaymeController` did not create orders based on the payment_id. It simply retrieved the payment data and redirected to the Payme payment gateway with a hardcoded order_id (132).

## Solution
The modified implementation now:
1. Retrieves the payment data based on the payment_id
2. Extracts necessary information from the payment data's additional_data field
3. Creates orders for each cart group using OrderManager::generate_order
4. Updates the payment data with the order information
5. Uses the actual order ID in the Payme payload

## Implementation Details

### Added Imports
```php
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Utils\CartManager;
use App\Utils\OrderManager;
```

### Modified Payment Function
The payment function now:
1. Retrieves and parses the additional_data from the payment request
2. Gets cart group IDs based on customer information
3. Generates a unique order ID
4. Creates orders for each cart group using OrderManager::generate_order
5. Updates the payment data with order information
6. Uses the actual order ID in the Payme payload

### Key Code Sections

#### Retrieving Additional Data
```php
$additionalData = json_decode($payment_data->additional_data, true);
if (!$additionalData) {
    return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, ['message' => 'Invalid payment data']), 400);
}
```

#### Getting Cart Group IDs
```php
$cartGroupIds = [];
if (isset($additionalData['customer_id']) && isset($additionalData['is_guest'])) {
    if ($additionalData['is_guest']) {
        $cartGroupIds = Cart::where(['customer_id' => $additionalData['customer_id'], 'is_guest' => 1, 'is_checked' => 1])
            ->groupBy('cart_group_id')->pluck('cart_group_id')->toArray();
    } else {
        $cartGroupIds = Cart::where(['customer_id' => $additionalData['customer_id'], 'is_guest' => '0', 'is_checked' => 1])
            ->groupBy('cart_group_id')->pluck('cart_group_id')->toArray();
    }
} else {
    $cartGroupIds = CartManager::get_cart_group_ids(type: 'checked');
}
```

#### Creating Orders
```php
$getUniqueId = OrderManager::generateUniqueOrderID();
$orderIds = [];
$newCustomerRegister = isset($additionalData['new_customer_info']) ? session('newRegisterCustomerInfo') : null;
$currencyCode = $payment_data->currency_code ?? 'UZS';

foreach ($cartGroupIds as $groupId) {
    $data = [
        'payment_method' => 'payme',
        'order_status' => 'pending',
        'payment_status' => 'unpaid',
        'transaction_ref' => '',
        'order_group_id' => $getUniqueId,
        'cart_group_id' => $groupId,
        'request' => [
            'customer_id' => $additionalData['customer_id'] ?? null,
            'is_guest' => $additionalData['is_guest'] ?? 0,
            'guest_id' => isset($additionalData['is_guest']) && $additionalData['is_guest'] ? $additionalData['customer_id'] : null,
            'order_note' => $additionalData['order_note'] ?? null,
            'coupon_code' => $additionalData['coupon_code'] ?? null,
            'address_id' => $additionalData['address_id'] ?? null,
            'billing_address_id' => $additionalData['billing_address_id'] ?? null,
        ],
        'newCustomerRegister' => $newCustomerRegister,
    ];

    $orderId = OrderManager::generate_order($data);
    $orderIds[] = $orderId;
}
```

#### Updating Payment Data
```php
$additionalData['payme_order_reference'] = $getUniqueId;
$additionalData['order_ids'] = $orderIds;
$payment_data->additional_data = json_encode($additionalData);
$payment_data->save();
```

#### Using Actual Order ID in Payme Payload
```php
$amount = round($payment_data->payment_amount * 100);
$payload = "m={$this->config_values->merchant_id};ac.order_id={$getUniqueId};amount={$amount}";
$encoded = rtrim(base64_encode($payload), '=');
$payme_url = "https://checkout.paycom.uz/{$encoded}";
```

## Benefits
1. Orders are now created before redirecting to the payment gateway
2. The actual order ID is used in the Payme payload instead of a hardcoded value
3. The payment data is updated with the order information for better tracking
4. The implementation follows the same pattern as the digital_payment_success function for consistency
