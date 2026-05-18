<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefundOrderCommandTest extends Command
{
    protected $signature = 'roam:refund-order';

    protected $description = 'Test refundOrder API';

    public function handle()
    {
        $roam = RoamApi::first();

        $token = '82605-65EAEF6E9FA160F45D24DBF7B9B0FE12';
        $orderNum = 'EP20260513006406';
        $iccid = '8948010010036501236';

        $payload = [
            'token' => $token,
            'orderNum' => $orderNum,
            'iccid' => $iccid,
            'remark' => 'Test for Ecommerce website',
        ];

        $payload['sign'] = $this->createSign($payload, $roam->client_key);

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/') . '/api_esim/refundOrder',
            $payload
        );

        $data = $response->json();
        dump($data);

        Log::info('Refund_order', [
            'payload' => $payload,
            'response' => $data,
        ]);

        if (($data['code'] ?? null) != 0) {
            $this->error('Refund order failed');
            dump($data);
            return self::FAILURE;
        }

        $this->info('Refund order success');

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
