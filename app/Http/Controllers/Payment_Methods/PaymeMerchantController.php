<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\DB;

class PaymeMerchantController extends Controller
{
    private $config;

    public function __construct()
    {
        // Load configuration
        $config = DB::table('addon_settings')
            ->where('key_name', 'payme')
            ->where('settings_type', 'payment_config')
            ->first();

        $this->config = json_decode($config->live_values);
        if ($config->mode == 'test') {
            $this->config = json_decode($config->test_values);
        }
    }

    public function pay()
    {
        return true;
    }

    public function handle(\Illuminate\Http\Request $request)
    {
        // Verify authentication
        $auth = $request->header('X-Auth');
        if (!$auth) {
            return $this->error(401, 'Unauthorized');
        }

        list($merchant_id, $received_hash) = explode(':', $auth);
        if ($merchant_id != $this->config->merchant_id) {
            return $this->error(401, 'Invalid merchant');
        }

        // Get the request data
        $data = $request->all();

        // Forward the request to the PaymeController
        $paymeController = new PaymeController(new \App\Models\PaymentRequest());
        return $paymeController->handle($request);
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
