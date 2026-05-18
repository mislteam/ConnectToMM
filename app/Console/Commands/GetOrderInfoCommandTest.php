<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetOrderInfoCommandTest extends Command
{
    protected $signature = 'roam:order-info';

    protected $description = 'Test getOrderInfo API';

    public function handle()
    {
        $roam = RoamApi::first();

        $token = '82605-65EAEF6E9FA160F45D24DBF7B9B0FE12';
        $orderNum = 'EP20260513006406';

        $payload = [
            'token' => $token,
            'orderNum' => $orderNum,
        ];

        $payload['sign'] = $this->createSign($payload, $roam->client_key);

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
            return self::FAILURE;
        }

        $this->info('Get order info success');

        dump($data['data']);

        return self::SUCCESS;
    }

    private function createSign(array $data, string $clientKey): string
    {
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
