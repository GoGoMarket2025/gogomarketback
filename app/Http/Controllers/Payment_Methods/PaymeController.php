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

    // Generate authentication headers for Payme API
    private function getAuthHeaders()
    {
        // According to Payme docs, authentication is done via X-Auth header
        return [
            'X-Auth: ' . $this->config_values->merchant_id . ':' . $this->config_values->merchant_key,
            'Content-Type: application/json',
        ];
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


        // Шаг 1: собрать строку
        $merchant_id = $this->config_values->merchant_id;
        $order_id = 123123;
        $amount = 2500000; // 2500.00 UZS = 250000 тийин

        $payload = "m={$merchant_id};ac.order_id={$order_id};amount={$amount}";
        $encoded = rtrim(base64_encode($payload), '=');
        $payme_url = "https://checkout.paycom.uz/{$encoded}";

        return redirect()->away($payme_url);
    }

    // Handle cancel action
    public function cancel(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->payment_response($data, 'cancel');
    }

    // Handle success action
    public function success(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        // Verify the payment with Payme
        $payment_data = [
            'id' => (string)Str::uuid(),
            'method' => 'receipts.get',
            'params' => [
                'receipt_id' => $request->receipt_id
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://checkout.paycom.uz/api');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getAuthHeaders());

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);

        if (isset($response['result']['receipt']['state']) && $response['result']['receipt']['state'] == 4) {
            // Payment is successful
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'payme',
                'is_paid' => 1,
                'transaction_id' => $response['result']['receipt']['_id'],
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            return $this->payment_response($data, 'success');
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }
}
