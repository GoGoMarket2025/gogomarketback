<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\Cart;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentRequest;
use App\Models\ShippingAddress;
use App\Traits\Processor;
use App\Utils\CartManager;
use App\Utils\OrderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

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

        // Create order based on payment data
        $additionalData = json_decode($payment_data->additional_data, true);
        if (!$additionalData) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, ['message' => 'Invalid payment data']), 400);
        }

        $data = digital_creat_order($payment_data);
        $uniqueId = $data['uniqueId'];

        // Update payment data with order information
        $additionalData['payme_order_reference'] = $uniqueId;
        $payment_data->additional_data = json_encode($additionalData);
        $payment_data->save();

        // Continue with payment gateway redirection
        $amount = round($payment_data->payment_amount * 100);
        $payload = "m={$this->config_values->merchant_id};ac.order_id={$uniqueId};a={$amount}";
        $encoded = rtrim(base64_encode($payload), '=');
        $payme_url = "https://checkout.paycom.uz/{$encoded}";

        return redirect()->away($payme_url);
    }

    /**
     * Helper method to return error responses in Payme format
     */
    private function error($code, $message, $data = null): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ]
        ], 200); // Payme requires HTTP 200 even for errors
    }

    /**
     * Check if transaction can be performed
     */
    private function checkPerformTransaction($data): JsonResponse
    {
        $params = $data['params'] ?? [];
        $account = $params['account'] ?? [];
        $amount = $params['amount'] ?? 0;

        // Validate order ID
        $orderId = $account['order_id'] ?? null;
        if (!$orderId) {
            return $this->error(-31050, 'Order ID not provided');
        }

        // Find payment request with this reference ID to get the correct payment amount
        $paymentRequest = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->payme_order_reference', $orderId)
            ->first();

        if ($paymentRequest) {
            // Check if the amount matches the payment request amount (converted to tiyin)
            if (round($paymentRequest->payment_amount * 100) != $amount) {
                return $this->error(-31001, 'Incorrect amount.');
            }
        } else {
            return $this->error(-31050, 'Order not found');
        }

        return response()->json([
            'result' => [
                'allow' => true
            ]
        ]);
    }

    /**
     * Create a transaction
     */
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

        // Check if there's a transaction with the same transaction_id
        $existingTransaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)
            ->first();

        if ($existingTransaction) {
            // If a transaction with this ID already exists, return its information
            return response()->json([
                'result' => [
                    'create_time' => (int)$existingTransaction->created_at->timestamp * 1000,
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

    /**
     * Perform a transaction (mark as completed)
     */
    private function performTransaction($data): JsonResponse
    {
        $params = $data['params'] ?? [];
        $transactionId = $params['id'] ?? null;
        $orderGroupId = $params['id'] ?? null;

        if (!$transactionId) {
            return $this->error(-31050, 'Transaction ID not provided');
        }

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
        }

        // Find transaction
        $transaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)->first();
        if (!$transaction) {
            // If no transaction found but payment request was processed, return success
            if ($paymentRequest) {
                return response()->json([
                    'result' => [
                        'transaction' => $transactionId,
                        'perform_time' => time() * 1000,
                        'state' => 2
                    ]
                ]);
            }
            return $this->error(-31050, 'Transaction not found');
        }

        // Update transaction status
        $transaction->status = 'success';
        $transaction->save();

        Order::where('order_group_id', $orderGroupId)
            ->update([
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

        return response()->json([
            'result' => [
                'transaction' => $transactionId,
                'perform_time' => (int)$transaction->updated_at->timestamp * 1000,
                'state' => 2
            ]
        ]);
    }

    /**
     * Check transaction status
     */
    private function checkTransaction($data): JsonResponse
    {
        $params = $data['params'] ?? [];
        $transactionId = $params['id'] ?? null;

        if (!$transactionId) {
            return $this->error(-31050, 'Transaction ID not provided');
        }

        // Find transaction
        $transaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)->first();
        if (!$transaction) {
            return $this->error(-31050, 'Transaction not found');
        }

        $state = 1; // Created
        if ($transaction->status == 'success') {
            $state = 2; // Completed
        } elseif ($transaction->status == 'failed' || $transaction->status == 'canceled') {
            $state = -1; // Canceled
        }

        return response()->json([
            'result' => [
                'create_time' => (int)$transaction->created_at->timestamp * 1000,
                'perform_time' => $state == 2 ? (int)$transaction->updated_at->timestamp * 1000 : 0,
                'cancel_time' => $state == -1 ? (int)$transaction->updated_at->timestamp * 1000 : 0,
                'transaction' => $transactionId,
                'state' => $state,
                'reason' => $state == -1 ? 1 : null
            ]
        ]);
    }

    /**
     * Cancel a transaction
     */
    private function cancelTransaction($data): JsonResponse
    {
        $params = $data['params'] ?? [];
        $transactionId = $params['id'] ?? null;
        $reason = $params['reason'] ?? null;

        if (!$transactionId) {
            return $this->error(-31050, 'Transaction ID not provided');
        }

        // Find transaction
        $transaction = \App\Models\OrderTransaction::where('transaction_id', $transactionId)->first();
        if (!$transaction) {
            return $this->error(-31050, 'Transaction not found');
        }

        // Update transaction status
        $transaction->status = 'canceled';
        $transaction->save();

        // Update order status if it was paid
        $order = \App\Models\Order::find($transaction->order_id);
        if ($order && $order->payment_status == 'paid') {
            $order->payment_status = 'unpaid';
            $order->save();
        }

        return response()->json([
            'result' => [
                'transaction' => $transactionId,
                'cancel_time' => (int)$transaction->updated_at->timestamp * 1000,
                'state' => -1
            ]
        ]);
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

}
