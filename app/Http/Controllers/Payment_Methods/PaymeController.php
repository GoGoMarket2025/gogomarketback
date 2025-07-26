<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\VarDumper;

class PaymeController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('payme', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        $this->payment = $payment;
    }

    // Create a payment
    public function payment(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();

        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        // Store cart information in the session for later order creation
        $additionalData = json_decode($payment_data->additional_data, true);

        // Store minimal required data for order creation after payment
        $orderData = [
            'payment_id' => $request['payment_id'],
            'additional_data' => $additionalData,
            'order_group_id' => \App\Utils\OrderManager::generateUniqueOrderID(),
            'timestamp' => time()
        ];

        // Store order data in session for later processing
        session()->put('payme_pending_order', $orderData);

        // Continue with payment gateway redirection (lightweight operation)
        $amount = round($payment_data->payment_amount * 100);
        $payload = "m={$this->config_values->merchant_id};ac.order_id={$payment_data->id};amount={$amount}";
        $encoded = rtrim(base64_encode($payload), '=');
        $payme_url = "https://checkout.paycom.uz/{$encoded}";

        return redirect()->away($payme_url);
    }

    public function handle(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json([
                'error' => [
                    'code' => -32504,
                    'message' => 'Недостаточно прав',
                ]
            ], 200); // HTTP 200 by Payme spec
        }

        // Check for Basic token
        if (str_starts_with($authHeader, 'Basic ')) {
            $token = substr($authHeader, 6);
            $decodedToken = base64_decode($token);
            $expectedToken = $this->config_values->merchant_key;

            if ($decodedToken !== $expectedToken) {
                return response()->json([
                    'error' => [
                        'code' => -32504,
                        'message' => 'Недостаточно прав',
                    ]
                ], 200);
            }
        }

        // Process the request based on method
        $data = $request->all();
        $method = $data['method'] ?? '';

        switch ($method) {
            case 'CheckPerformTransaction':
                return $this->checkPerformTransaction($data);
            case 'CreateTransaction':
                return $this->createTransaction($data);
            case 'PerformTransaction':
                return $this->performTransaction($data);
            case 'CheckTransaction':
                return $this->checkTransaction($data);
            case 'CancelTransaction':
                return $this->cancelTransaction($data);
            default:
                return $this->error(405, 'Method not found');
        }
    }

    private function checkPerformTransaction($data): JsonResponse
    {
        $orderId = $data['params']['account']['order_id'] ?? null;
        $amount = $data['params']['amount'] ?? null;

        if (!$orderId || !$amount) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid order ID or amount.'
                ]
            ], 200);
        }

        $order = $this->payment::where(['id' => $orderId])->where(['is_paid' => 0])->first();

        if (!$order) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Order not found.'
                ]
            ], 200);
        }

        // Amount in Payme is in tiyin (1/100 of UZS)
        if ((int)($order->payment_amount * 100) !== (int)$amount) {
            return response()->json([
                'error' => [
                    'code' => -31001,
                    'message' => 'Incorrect amount.'
                ]
            ], 200);
        }

        // All checks passed
        return response()->json([
            'result' => ['allow' => true]
        ], 200);
    }

    private function createTransaction($data)
    {
        $orderId = $data['params']['account']['order_id'] ?? null;
        $amount = $data['params']['amount'] ?? null;
        $transactionId = $data['params']['id'] ?? null;

        if (!$orderId || !$amount || !$transactionId) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid parameters.'
                ]
            ], 200);
        }

        $order = $this->payment::where(['id' => $orderId])->first();

        if (!$order) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Order not found.'
                ]
            ], 200);
        }

        // Check if transaction already exists
        if ($order->is_paid == 1 && $order->transaction_id) {
            return response()->json([
                'result' => [
                    'create_time' => time() * 1000,
                    'transaction' => $order->transaction_id,
                    'state' => 2 // Completed
                ]
            ], 200);
        }

        // Create new transaction
        $this->payment::where(['id' => $orderId])->update([
            'payment_method' => 'payme',
            'transaction_id' => $transactionId
        ]);

        return response()->json([
            'result' => [
                'create_time' => time() * 1000,
                'transaction' => $transactionId,
                'state' => 1 // Created
            ]
        ], 200);
    }

    private function performTransaction($data)
    {
        $transactionId = $data['params']['id'] ?? null;

        if (!$transactionId) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid transaction ID.'
                ]
            ], 200);
        }

        $order = $this->payment::where(['transaction_id' => $transactionId])->first();

        if (!$order) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Transaction not found.'
                ]
            ], 200);
        }

        // If already paid, return success
        if ($order->is_paid == 1) {
            return response()->json([
                'result' => [
                    'transaction' => $transactionId,
                    'perform_time' => time() * 1000,
                    'state' => 2 // Completed
                ]
            ], 200);
        }

        // Mark as paid and call success hook
        $this->payment::where(['transaction_id' => $transactionId])->update([
            'is_paid' => 1
        ]);

        $order = $this->payment::where(['transaction_id' => $transactionId])->first();

        if (isset($order) && function_exists($order->success_hook)) {
            call_user_func($order->success_hook, $order);
        }

        return response()->json([
            'result' => [
                'transaction' => $transactionId,
                'perform_time' => time() * 1000,
                'state' => 2 // Completed
            ]
        ], 200);
    }

    private function checkTransaction($data)
    {
        $transactionId = $data['params']['id'] ?? null;

        if (!$transactionId) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid transaction ID.'
                ]
            ], 200);
        }

        $order = $this->payment::where(['transaction_id' => $transactionId])->first();

        if (!$order) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Transaction not found.'
                ]
            ], 200);
        }

        $state = $order->is_paid == 1 ? 2 : 1; // 2 = Completed, 1 = Created

        return response()->json([
            'result' => [
                'create_time' => strtotime($order->created_at) * 1000,
                'perform_time' => $order->is_paid == 1 ? strtotime($order->updated_at) * 1000 : null,
                'cancel_time' => null,
                'transaction' => $transactionId,
                'state' => $state,
                'reason' => null
            ]
        ], 200);
    }

    private function cancelTransaction($data)
    {
        $transactionId = $data['params']['id'] ?? null;

        if (!$transactionId) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid transaction ID.'
                ]
            ], 200);
        }

        $order = $this->payment::where(['transaction_id' => $transactionId])->first();

        if (!$order) {
            return response()->json([
                'error' => [
                    'code' => -31050,
                    'message' => 'Transaction not found.'
                ]
            ], 200);
        }

        // If already paid, can't cancel
        if ($order->is_paid == 1) {
            return response()->json([
                'error' => [
                    'code' => -31007,
                    'message' => 'Cannot cancel completed transaction.'
                ]
            ], 200);
        }

        // Call failure hook
        if (isset($order) && function_exists($order->failure_hook)) {
            call_user_func($order->failure_hook, $order);
        }

        return response()->json([
            'result' => [
                'transaction' => $transactionId,
                'cancel_time' => time() * 1000,
                'state' => 3 // Cancelled
            ]
        ], 200);
    }

    private function error($code, $message)
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ]);
    }

    public function success(Request $request)
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();

        if (!isset($payment_data)) {
            return redirect('/');
        }

        // If payment is not already marked as paid
        if ($payment_data->is_paid == 0) {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'payme',
                'is_paid' => 1,
                'transaction_id' => $request->input('transaction_id', uniqid())
            ]);

            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();

            // Process deferred order creation
            $pendingOrderData = session()->get('payme_pending_order', null);

            if ($pendingOrderData && $pendingOrderData['payment_id'] == $request['payment_id']) {
                // Create orders now that payment is complete
                $this->createDeferredOrders($pendingOrderData, $payment_data->transaction_id);

                // Clear the pending order data
                session()->forget('payme_pending_order');
            } else {
                // Check if we have existing orders from the previous process
                $orderIds = session()->get('payme_order_ids', []);

                if (!empty($orderIds)) {
                    // Update existing orders to mark them as paid
                    foreach ($orderIds as $orderId) {
                        \App\Models\Order::where('id', $orderId)->update([
                            'order_status' => 'confirmed',
                            'payment_status' => 'paid',
                            'transaction_ref' => $payment_data->transaction_id
                        ]);

                        // Generate referral bonus if applicable
                        \App\Utils\OrderManager::generateReferBonusForFirstOrder(orderId: $orderId);
                    }

                    // Clear the session
                    session()->forget('payme_order_ids');
                } else {
                    // If no existing orders found, fall back to the original success hook
                    if (isset($payment_data) && function_exists($payment_data->success_hook)) {
                        call_user_func($payment_data->success_hook, $payment_data);
                    }
                }
            }
        }

        return $this->payment_response($payment_data, 'success');
    }

    /**
     * Create orders that were deferred during payment initialization
     */
    private function createDeferredOrders($pendingOrderData, $transactionId)
    {
        $additionalData = $pendingOrderData['additional_data'];
        $generateUniqueId = $pendingOrderData['order_group_id'];
        $orderIds = [];

        // Get cart group IDs - using a more efficient query approach
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

        $isGuestUserInOrder = $additionalData['is_guest_in_order'] ?? 0;

        // Create order for each cart group - in batches if possible
        foreach ($cartGroupIds as $cartGroupId) {
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
                $orderId = \App\Utils\OrderManager::generate_order($data);
                $orderIds[] = $orderId;

                // Generate referral bonus if applicable
                \App\Utils\OrderManager::generateReferBonusForFirstOrder(orderId: $orderId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error creating deferred order: ' . $e->getMessage());
            }
        }

        // Store order IDs in session for reference
        session()->put('payme_order_ids', $orderIds);
    }

    public function failed(Request $request)
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();

        // Clear any pending order data for this payment
        $pendingOrderData = session()->get('payme_pending_order', null);
        if ($pendingOrderData && $pendingOrderData['payment_id'] == $request['payment_id']) {
            session()->forget('payme_pending_order');
        }

        // Check if we have existing orders from the payment process
        $orderIds = session()->get('payme_order_ids', []);

        if (!empty($orderIds)) {
            // Update existing orders to mark them as failed
            foreach ($orderIds as $orderId) {
                \App\Models\Order::where('id', $orderId)->update([
                    'order_status' => 'failed',
                    'payment_status' => 'unpaid'
                ]);
            }

            // Clear the session
            session()->forget('payme_order_ids');
        } else {
            // If no existing orders found, fall back to the original failure hook
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
        }

        return $this->payment_response($payment_data, 'fail');
    }

    public function canceled(Request $request)
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();

        // Clear any pending order data for this payment
        $pendingOrderData = session()->get('payme_pending_order', null);
        if ($pendingOrderData && $pendingOrderData['payment_id'] == $request['payment_id']) {
            session()->forget('payme_pending_order');
        }

        // Check if we have existing orders from the payment process
        $orderIds = session()->get('payme_order_ids', []);

        if (!empty($orderIds)) {
            // Update existing orders to mark them as canceled
            foreach ($orderIds as $orderId) {
                \App\Models\Order::where('id', $orderId)->update([
                    'order_status' => 'canceled',
                    'payment_status' => 'unpaid'
                ]);
            }

            // Clear the session
            session()->forget('payme_order_ids');
        } else {
            // If no existing orders found, fall back to the original failure hook
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
        }

        return $this->payment_response($payment_data, 'cancel');
    }
}
