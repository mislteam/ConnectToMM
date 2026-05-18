<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestRoamOrderAPI extends Command
{
    protected $signature = 'roam:test-order-apis';

    protected $description = 'Test addEsimOrderByApiCode, renewCardByApiCode, and getOrderInfo APIs';

    public function handle()
    {
        $this->info('==============================');
        $this->info('ROAM ORDER API TEST');
        $this->info('==============================');

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Use REAL values from your successful previous API calls.
        |
        */

        $token = '82605-019311AA9E9632E69F0630C3D2A0E683';

        $apiCode = 15172;

        $orderNum = 'EP20260507004309';

        /*
        |--------------------------------------------------------------------------
        | STEP 1: CREATE ORDER
        |--------------------------------------------------------------------------
        */

        $createdOrder = $this->createOrder(
            $token,
            $apiCode
        );

        if (!$createdOrder) {

            $this->error('addEsimOrder failed');

            return self::FAILURE;
        }

        $renewed = $this->renewOrder(
            $token,
            $apiCode,
            $createdOrder['orderNum'] ?? $orderNum
        );

        if (!$renewed) {

            $this->error('renewCardByApiCode failed');

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Check ORDER INFO
        |--------------------------------------------------------------------------
        */

        $checked = $this->getOrderInfo(
            $token,
            $createdOrder['orderNum'] ?? $orderNum
        );

        if (!$checked) {

            $this->error('getOrderInfo failed');

            return self::FAILURE;
        }

        $this->info('==============================');
        $this->info('ALL TESTS SUCCESS');
        $this->info('==============================');

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    private function createOrder(
        string $token,
        int|string $apiCode
    ): ?array {

        $roam = RoamApi::first();

        $payload = [
            'token' => $token,
            'apiCode' => $apiCode,
            'count' => 1,

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            */

            'customerEmail' => 'yushwesin7@gmail.com',

            // 0 = don't send email
            'isSendEmail' => 1,

            // FOr daypack support package
            'daypassDays' => 1,

            // 1 = return full order info
            'backInfo' => 1,

            'otherOrderId' => 'ORDER-' . time(),

        ];

        $payload['sign'] = $this->createSign(
            $payload,
            $roam->client_key
        );

        $this->line('');
        $this->info('========== CREATE ORDER ==========');

        dump([
            'url' => rtrim($roam->api_url, '/') . '/api_esim/addEsimOrderByApiCode',
            'payload' => $payload,
        ]);

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/') . '/api_esim/addEsimOrderByApiCode',
            $payload
        );

        $data = $response->json();

        dump($data);

        Log::info('ROAM_CREATE_ORDER', [
            'payload' => $payload,
            'response' => $data,
        ]);

        if (($data['code'] ?? null) != 0) {

            $this->error('Create order API failed');

            return false;
        }

        $this->info('Order created successfully');

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT RESPONSE DATA
        |--------------------------------------------------------------------------
        */

        $order = $data['data'];

        $this->line('');
        $this->info('========== ORDER RESULT ==========');

        dump([
            'orderNum' => $order['orderNum'] ?? null,
            'cards' => $order['cardApiDtoList'] ?? [],
        ]);

        return $order;
    }

    /*
    |--------------------------------------------------------------------------
    | RENEW ORDER
    |--------------------------------------------------------------------------
    */
    private function renewOrder(
        string $token,
        int|string $apiCode,
        string $orderNum
    ): bool {
        $roam = RoamApi::first();

        $payload = [
            'token' => $token,
            'orderNum' => $orderNum,
            'apiCode' => $apiCode,
            'backInfo' => 1,
            'otherOrderId' => 'RENEW-' . time(),
        ];

        $payload['sign'] = $this->createSign(
            $payload,
            $roam->client_key
        );

        $this->line('');
        $this->info('========== RENEW ORDER ==========');

        dump([
            'url' => rtrim($roam->api_url, '/') . '/api_esim/renewCardByApiCode',
            'payload' => $payload,
        ]);

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/') . '/api_esim/renewCardByApiCode',
            $payload
        );

        $data = $response->json();

        dump($data);

        Log::info('ROAM_RENEW_ORDER', [
            'payload' => $payload,
            'response' => $data,
        ]);

        if (($data['code'] ?? null) != 0) {

            $this->error('Renew order API failed');

            return false;
        }

        $this->info('Renew order created successfully');

        $order = $data['data'];

        $this->line('');
        $this->info('========== RENEW RESULT ==========');

        dump([
            'orderNum' => $order['orderNum'] ?? null,
            'cards' => $order['cardApiDtoList'] ?? [],
        ]);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ORDER INFO
    |--------------------------------------------------------------------------
    */
    private function getOrderInfo(
        string $token,
        string $orderNum
    ): ?array {

        $roam = RoamApi::first();
        $payload = [
            'token' => $token,
            'orderNum' => $orderNum,
        ];

        $payload['sign'] = $this->createSign(
            $payload,
            $roam->client_key
        );

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/') . '/api_esim/getOrderInfo',
            $payload
        );

        $data = $response->json();
        dump($data);

        Log::info('Order_info', [
            'payload' => $payload,
            'response' => $data,
        ]);

        if (($data['code'] ?? null) != 0) {
            $this->error('Get order info failed');

            dump($data);

            return null;
        }

        $this->info('Get order info success');

        dump($data['data']);

        return $data['data'];
    }

    /*
    |--------------------------------------------------------------------------
    | SIGN GENERATOR
    |--------------------------------------------------------------------------
    */

    private function createSign(
        array $data,
        string $clientKey
    ): string {

        unset($data['sign']);

        $data = array_filter($data, function ($value) {

            return $value !== null
                && $value !== '';
        });

        ksort($data);

        $plainText = '';

        foreach ($data as $key => $value) {

            if (is_array($value)) {

                $value = implode(',', $value);
            }

            $plainText .= $key . '=' . trim((string)$value);
        }

        $plainText .= $clientKey;

        $sign = md5($plainText);

        /*
        |--------------------------------------------------------------------------
        | DEBUG SIGN
        |--------------------------------------------------------------------------
        */

        $this->line('');
        $this->info('========== SIGN DEBUG ==========');

        dump([
            'plain_text' => $plainText,
            'sign' => $sign,
        ]);

        return $sign;
    }
}
