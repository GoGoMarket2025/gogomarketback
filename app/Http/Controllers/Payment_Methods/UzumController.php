<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\Cart;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\PaymentRequest;
use App\Models\ShippingAddress;
use App\Traits\Processor;
use App\Utils\CartManager;
use App\Utils\Helpers;
use App\Utils\OrderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UzumController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('uzum', 'payment_config');
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

        $data = digital_creat_order($payment_data);
        dump($data);
        die();



        $user = Helpers::getCustomerInformation($request);
        dump($user);

        die();

        $checkOutUrl = $this->config_values->checkout_url;
        $terminalId = $this->config_values->terminal_id;
        $apiKey = $this->config_values->api_key;
        $amount = round($payment_data->payment_amount * 100);


        // Optional: define base URI from .env or config
        $baseUri = $checkOutUrl;
        $headers = [
            'Content-Language' => 'ru-RU',
            'X-Terminal-Id' => $terminalId,
            'X-API-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ];

        // Предположим, что $order — это модель заказа, аналогичная $model в Yii
        $data = [
            'amount' => $amount,
            'clientId' => (string)$user->id,
            'currency' => 860,
            'paymentDetails' => "Оплата за заказ № {$getUniqueId}",
            'paymentParams' => [
                'payType' => 'ONE_STEP',
                'force3ds' => false,
                'phoneNumber' => (string)$user->phone,
            ],
            'viewType' => 'IFRAME',
            'sessionTimeoutSecs' => 1800,
            'successUrl' => 'https://gogomarket.uz',
            'failureUrl' => 'https://gogomarket.uz',
            'orderNumber' => $getUniqueId,
        ];
        // Send request with headers and timeout
        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->post("{$baseUri}/api/v1/payment/register", $data);

        // Handle response
        if (!$response->successful()) {
            Log::error('Uzum API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
        $responseData = $response->json();
        // Access the paymentRedirectUrl
        $uzumUrl = $responseData['result']['paymentRedirectUrl'] ?? null;
        $uzumOrderId = $responseData['result']['orderId'] ?? null;

        $additionalData = json_decode($payment_data->additional_data, true);
        $additionalData['uzum_order_id'] = $uzumOrderId;
        $payment_data->additional_data = json_encode($additionalData);
        $payment_data->save();
        return redirect()->away($uzumUrl);
    }

    public function handle(Request $request): JsonResponse
    {

        $data = $request->all();
        Log::warning('Uzum Handle request:', $data);


        return response()->json([
            'data' => true
        ]);
    }

}
