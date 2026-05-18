<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateOrderCommandTest extends Command
{
    protected $signature = 'roam:create-order';

    protected $description = 'Test addEsimOrderByApiCode API';

    public function handle()
    {
        $roam = RoamApi::first();

        $payload = [
            'token' => '82605-0BE4A282F18D660CD9AA27564D24E349',
            'apiCode' => '99915-0-?-500-M-D',
            'count' => 1,
            'customerEmail' => 'yushwesin7@gmail.com',
            'isSendEmail' => 1,
            'daypassDays' => 1,
            'backInfo' => 1,
            'otherOrderId' => 'ORDER-' . time(),
        ];

        $payload['sign'] = $this->createSign($payload, $roam->client_key);

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
        Log::info('ROAM_CREATE_ORDER', [
            'payload' => $payload,
            'response' => $data,
        ]);
        dump($data);

        if (($data['code'] ?? null) != 0) {
            $this->error('Create order failed');
            return self::FAILURE;
        }

        $this->info('Order created successfully');

        $order = $data['data'];

        $this->line('');
        $this->info('========== ORDER RESULT ==========');

        dump([
            'orderNum' => $order['orderNum'] ?? null,
            'cards' => $order['cardApiDtoList'] ?? [],
        ]);

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
