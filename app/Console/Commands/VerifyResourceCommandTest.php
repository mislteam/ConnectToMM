<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyResourceCommandTest extends Command
{
    protected $signature = 'roam:verify-resource';

    protected $description = 'Test Roam verifyResource API';

    public function handle()
    {
        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Use REAL values from your successful previous API calls.
        |
        */
        $roam = RoamApi::first();
        $token = '82605-0BE4A282F18D660CD9AA27564D24E349';
        $skuId = 124;
        $priceId = 15172;

        $payload = [
            'token' => $token,
            'skuid' => $skuId,
            'priceid' => $priceId,
            'count' => 1,
        ];

        $payload['sign'] = $this->createSign($payload, $roam->client_key);

        $this->line('');
        $this->info('========== VERIFY RESOURCE ==========');

        dump([
            'url' => rtrim($roam->api_url, '/') . '/api_esim/verifyResource',
            'payload' => $payload,
        ]);

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/') . '/api_esim/verifyResource',
            $payload
        );

        $data = $response->json();

        Log::info('ROAM_VERIFY_RESOURCE', [
            'payload' => $payload,
            'response' => $data,
        ]);

        dump($data);

        if (($data['code'] ?? null) != 0) {
            $this->error('VerifyResource failed');
            return self::FAILURE;
        }

        $this->info('Resource available');
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

            $plainText .= $key . '=' . trim((string) $value);
        }

        $plainText .= $clientKey;

        $sign = md5($plainText);

        $this->line('');
        $this->info('========== SIGN DEBUG ==========');

        dump([
            'plain_text' => $plainText,
            'sign' => $sign,
        ]);

        return $sign;
    }
}
