<?php

namespace App\Http\Controllers\Payment_Methods;

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
    public function handle(Request $request)
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

    // Implement the required methods according to Payme documentation
    // ...

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
