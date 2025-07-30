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

        // Get cart group IDs
        $cartGroupIds = [];
        if (isset($additionalData['customer_id']) && isset($additionalData['is_guest'])) {
            $cartGroupIds = Cart::where(['customer_id' => $additionalData['customer_id'], 'is_guest' => '0', 'is_checked' => 1])
                ->groupBy('cart_group_id')->pluck('cart_group_id')->toArray();
        } else {
            $cartGroupIds = CartManager::get_cart_group_ids(type: 'checked');
        }

        if (empty($cartGroupIds)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, ['message' => 'No items in cart']), 400);
        }

        // Create orders for each cart group
        $newCustomerRegister = isset($additionalData['new_customer_info']) ? session('newRegisterCustomerInfo') : null;
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $currencyCode = $request->current_currency_code ?? Currency::find(getWebConfig(name: 'system_default_currency'))->code;
        } else {
            $currencyCode = Currency::find(getWebConfig(name: 'system_default_currency'))->code;
        }

        $getUniqueId = OrderManager::generateUniqueOrderID();

        $orderIds = [];
        foreach ($cartGroupIds as $groupId) {
            $data = [
                'payment_method' => 'cash_on_delivery',
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'transaction_ref' => '',
                'order_group_id' => $getUniqueId,
                'cart_group_id' => $groupId,
                'request' => $request,
                'newCustomerRegister' => $newCustomerRegister,
                'bring_change_amount' => $request['bring_change_amount'] ?? 0,
                'bring_change_amount_currency' => $currencyCode,
            ];

            $orderId = OrderManager::generate_order($data);

            $order = Order::find($orderId);
            $order->billing_address = ($request['billing_address_id'] != null) ? $request['billing_address_id'] : $order['billing_address'];
            $order->billing_address_data = ($request['billing_address_id'] != null) ? ShippingAddress::find($request['billing_address_id']) : $order['billing_address_data'];
            $order->order_note = ($request['order_note'] != null) ? $request['order_note'] : $order['order_note'];
            $order->save();

            $orderIds[] = $orderId;
        }

//        CartManager::cart_clean($request);

        // Update payment data with order information
        $additionalData['click_order_reference'] = $getUniqueId;
        $additionalData['order_ids'] = $orderIds;
        $payment_data->additional_data = json_encode($additionalData);
        $payment_data->save();

        $serviceId = $this->config_values->service_id;
        $merchantId = $this->config_values->merchant_id;
        $amount = round($payment_data->payment_amount);
        $transactionParam = $getUniqueId;
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

    public function handle(Request $request)
    {
        $data = $request->all();
        Log::warning('CLICK warning Handle Request:', $data);
        Log::debug('CLICK debug Handle Request:', $data);
        Log::info('CLICK info Handle Request:', $data);

        return response()->json([
            'message' => 'here'
        ]);
    }
    public function prepare(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::warning('CLICK Prepare Request:', $data);
        if (!$this->isValidSignature($data)) {
            return $this->clickError(-1, 'SIGN CHECK FAILED!');
        }

        $orderId = $request->get('merchant_trans_id');
        $amount = $request->get('amount');

        $order = $this->payment::where('is_paid', 0)
            ->whereJsonContains('additional_data->click_order_reference', $orderId)
            ->first();

        if (!$order) {
            return $this->clickError(-5, 'User does not exist');
        }

        if ((int)$amount !== (int)($order->order_amount)) {
            return $this->clickError(-2, 'Incorrect parameter amount');
        }

        return response()->json([
            'click_trans_id' => $request->get('click_trans_id'),
            'merchant_trans_id' => $orderId,
            'merchant_prepare_id' => uniqid(),
            'error' => 0,
            'error_note' => 'Success'
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $orderId = $request->get('merchant_trans_id');
        $clickTransId = $request->get('click_trans_id');
        $merchantPrepareId = $request->get('merchant_prepare_id');
        $amount = $request->get('amount');
        $status = $request->get('error') == 0;

        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            return $this->clickError(-5, 'User does not exist');
        }

        if ((int)$amount !== (int)($order->order_amount * 100)) {
            return $this->clickError(-2, 'Incorrect parameter amount');
        }

        $transaction = OrderTransaction::firstOrCreate(
            ['transaction_id' => $clickTransId],
            [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'seller_id' => $order->seller_id,
                'seller_is' => $order->seller_is,
                'payment_method' => 'click',
                'order_amount' => $order->order_amount,
                'status' => $status ? 'success' : 'canceled'
            ]
        );

        if ($status) {
            $order->payment_status = 'paid';
            $order->save();
        } else {
            $order->payment_status = 'unpaid';
            $order->save();
        }

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
        $secretKey = $this->config_values->secret_key;

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

        return $generated === $data['sign_string'];
    }
}
