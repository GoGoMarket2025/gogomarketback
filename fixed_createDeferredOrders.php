    /**
     * Create orders that were deferred during payment initialization
     */
    private function createDeferredOrders($pendingOrderData, $transactionId)
    {
        $additionalData = $pendingOrderData['additional_data'];
        $generateUniqueId = $pendingOrderData['order_group_id'];
        $orderIds = [];

        try {
            \Illuminate\Support\Facades\Log::info('Starting deferred order creation', [
                'payment_id' => $pendingOrderData['payment_id'],
                'order_group_id' => $generateUniqueId
            ]);

            // Check if orders already exist for this order_group_id to prevent duplicates
            $existingOrders = \App\Models\Order::where('order_group_id', $generateUniqueId)->get();
            if ($existingOrders->count() > 0) {
                \Illuminate\Support\Facades\Log::warning('Orders already exist for this order_group_id', [
                    'order_group_id' => $generateUniqueId,
                    'existing_order_count' => $existingOrders->count(),
                    'existing_order_ids' => $existingOrders->pluck('id')->toArray()
                ]);

                // Return existing order IDs
                return $existingOrders->pluck('id')->toArray();
            }

            // Use cart group IDs stored in the session during payment initiation
            $cartGroupIds = $pendingOrderData['cart_group_ids'] ?? [];

            // Fallback to retrieving cart group IDs if not found in session
            if (empty($cartGroupIds)) {
                \Illuminate\Support\Facades\Log::warning('Cart group IDs not found in session, attempting to retrieve from database');

                $customerConditions = [];
                if (isset($additionalData['customer_id'])) {
                    $customerConditions['customer_id'] = $additionalData['customer_id'];
                    $customerConditions['is_guest'] = isset($additionalData['is_guest']) ? $additionalData['is_guest'] : 0;
                }

                if (!empty($customerConditions)) {
                    $cartGroupIds = \App\Models\Cart::where($customerConditions)
                        ->where('is_checked', 1)
                        ->groupBy('cart_group_id')
                        ->pluck('cart_group_id')
                        ->toArray();
                } else {
                    $cartGroupIds = \App\Utils\CartManager::get_cart_group_ids(type: 'checked');
                }
            }

            if (empty($cartGroupIds)) {
                \Illuminate\Support\Facades\Log::error('No cart group IDs found for order creation', [
                    'payment_id' => $pendingOrderData['payment_id'],
                    'additional_data' => $additionalData
                ]);
                throw new \Exception('No cart group IDs found for order creation');
            }

            $isGuestUserInOrder = $additionalData['is_guest_in_order'] ?? 0;

            // Create order for each cart group - in batches if possible
            foreach ($cartGroupIds as $cartGroupId) {
                // Verify cart exists before attempting to create order
                $cartExists = \App\Models\Cart::where('cart_group_id', $cartGroupId)->exists();
                if (!$cartExists) {
                    \Illuminate\Support\Facades\Log::warning('Cart not found for cart_group_id: ' . $cartGroupId, [
                        'payment_id' => $pendingOrderData['payment_id'],
                        'cart_group_id' => $cartGroupId
                    ]);
                    continue; // Skip this cart group
                }

                $data = [
                    'payment_method' => 'payme',
                    'order_status' => 'confirmed', // Already paid
                    'payment_status' => 'paid',
                    'transaction_ref' => $transactionId,
                    'order_group_id' => $generateUniqueId,
                    'cart_group_id' => $cartGroupId,
                    'request' => [
                        'customer_id' => $additionalData['customer_id'] ?? 0,
                        'is_guest' => $isGuestUserInOrder,
                        'guest_id' => $isGuestUserInOrder ? ($additionalData['customer_id'] ?? 0) : null,
                        'order_note' => $additionalData['order_note'] ?? null,
                        'coupon_code' => $additionalData['coupon_code'] ?? null,
                        'coupon_discount' => $additionalData['coupon_discount'] ?? null,
                        'address_id' => $additionalData['address_id'] ?? null,
                        'billing_address_id' => $additionalData['billing_address_id'] ?? null,
                        'payment_request_from' => $additionalData['payment_request_from'] ?? 'web',
                    ],
                    'new_customer_id' => $additionalData['new_customer_id'] ?? null,
                ];

                try {
                    \Illuminate\Support\Facades\Log::info('Creating deferred order for cart_group_id: ' . $cartGroupId, [
                        'payment_id' => $pendingOrderData['payment_id'],
                        'cart_group_id' => $cartGroupId
                    ]);

                    $orderId = \App\Utils\OrderManager::generate_order($data);
                    $orderIds[] = $orderId;

                    \Illuminate\Support\Facades\Log::info('Successfully created order: ' . $orderId);

                    // Generate referral bonus if applicable
                    \App\Utils\OrderManager::generateReferBonusForFirstOrder(orderId: $orderId);

                    // Ensure notification is sent for the order
                    $order = \App\Models\Order::with('customer', 'seller.shop', 'details')->find($orderId);
                    if ($order) {
                        // Send notification to customer
                        $notification = (object)[
                            'key' => 'confirmed',
                            'type' => 'customer',
                            'order' => $order,
                        ];
                        event(new \App\Events\OrderPlacedEvent(notification: $notification));

                        // Send notification to seller if not admin
                        if ($order->seller_is == 'seller') {
                            $notification = (object)[
                                'key' => 'new_order_message',
                                'type' => 'seller',
                                'order' => $order,
                            ];
                            event(new \App\Events\OrderPlacedEvent(notification: $notification));
                        }

                        \Illuminate\Support\Facades\Log::info('Order notifications sent for order: ' . $orderId);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error creating deferred order: ' . $e->getMessage(), [
                        'payment_id' => $pendingOrderData['payment_id'],
                        'cart_group_id' => $cartGroupId,
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if (empty($orderIds)) {
                \Illuminate\Support\Facades\Log::error('No orders were created', [
                    'payment_id' => $pendingOrderData['payment_id']
                ]);
                throw new \Exception('No orders were created');
            }

            // Store order IDs in session for reference
            session()->put('payme_order_ids', $orderIds);

            return $orderIds;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in deferred order creation process: ' . $e->getMessage(), [
                'payment_id' => $pendingOrderData['payment_id'],
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Return any orders that were created before the error
            if (!empty($orderIds)) {
                session()->put('payme_order_ids', $orderIds);
                return $orderIds;
            }

            return [];
        }
    }
