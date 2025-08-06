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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClickController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('click', 'payment_config');
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
        $additionalData['click_order_reference'] = $uniqueId;
        $payment_data->additional_data = json_encode($additionalData);
        $payment_data->save();

        $serviceId = $this->config_values->service_id;
        $merchantId = $this->config_values->merchant_id;
        $amount = round($payment_data->payment_amount);
        $transactionParam = $uniqueId;
        $returnUrl = 'https://gogomarket.uz';

        $click_url = "https://my.click.uz/services/pay?" . http_build_query([
                'service_id' => $serviceId,
                'merchant_id' => $merchantId,
                'amount' => $amount,
                'transaction_param' => $transactionParam,
                'return_url' => $returnUrl,
            ]);

        return redirect()->away($click_url);
    }

    public function prepare(Request $request): JsonResponse
    {

        $data = $request->all();
        Log::warning('CLICK Prepare Request:', $data);
        if (!$this->isValidSignature($data)) {
            Log::warning("SIGN CHECK FAILED!");
            return $this->clickError(-1, 'SIGN CHECK FAILED!');
        }

        $orderId = $request->get('merchant_trans_id');
        $amount = $request->get('amount');

        $order = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->click_order_reference', $orderId)
            ->first();

        if (!$order) {
            Log::warning('Incorrect order');
            return $this->clickError(-5, 'Order does not exist');
        }

        if (intval($amount) !== intval($order->payment_amount)) {
            Log::warning('Incorrect amount');
            return $this->clickError(-2, 'Incorrect parameter amount');
        }

        $additionalData = json_decode($order->additional_data, true);
        $additionalData['click_trans_id'] = $request->get('click_trans_id');
        $additionalData['click_paydoc_id'] = $request->get('click_paydoc_id');
        $order->additional_data = json_encode($additionalData);
        $order->save();


        return response()->json([
            'click_trans_id' => $request->get('click_trans_id'),
            'merchant_trans_id' => $orderId,
            'merchant_prepare_id' => $order->id,
            'error' => 0,
            'error_note' => 'Success'
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        Log::warning('CLICK Complete Request:', $request->all());

        $orderId = $request->get('merchant_trans_id');
        $clickTransId = $request->get('click_trans_id');
        $merchantPrepareId = $request->get('merchant_prepare_id');
        $amount = $request->get('amount');
        $status = $request->get('error') == 0;

        $order = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->click_order_reference', $orderId)
            ->first();

        Log::warning($order);


        if (!$order) {
            return $this->clickError(-5, 'Order does not exist');
        }

        if (intval($amount) !== intval($order->payment_amount)) {
            Log::warning('Incorrect amount');
            return $this->clickError(-2, 'Incorrect parameter amount');
        }

        Order::where('order_group_id', $orderId)
            ->update([
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

        return response()->json([
            'click_trans_id' => $clickTransId,
            'merchant_trans_id' => $orderId,
            'merchant_confirm_id' => uniqid(),
            'error' => 0,
            'error_note' => 'Success'
        ]);
    }

    private function clickError(int $code, string $message): JsonResponse
    {
        return response()->json([
            'error' => $code,
            'error_note' => $message,
            'merchant_trans_id' => request()->get('merchant_trans_id'),
            'merchant_prepare_id' => null,
        ]);
    }

    protected function generateSignString(
        string $clickTransId,
        string $serviceId,
        string $merchantTransId,
        string $amount,
        string $action,
        string $signTime
    ): string
    {
        $secretKey = $this->config_values->merchant_key;

        $data = [
            $clickTransId,
            $serviceId,
            $secretKey,
            $merchantTransId,
            $amount,
            $action,
            $signTime,
        ];

        return md5(implode('', $data));
    }

    protected function isValidSignature(array $data): bool
    {
        $generated = $this->generateSignString(
            $data['click_trans_id'],
            $data['service_id'],
            $data['merchant_trans_id'],
            $data['amount'],
            $data['action'],
            $data['sign_time']
        );
        Log::warning($generated . " - " . $data['sign_string']);

        return $generated == $data['sign_string'];
    }
}
