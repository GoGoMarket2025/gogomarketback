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

    private function checkPerformTransaction($data)
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
        if ((int)($order->order_price * 100) !== (int)$amount) {
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


    private function error($code, $message)
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ]);
    }
}
