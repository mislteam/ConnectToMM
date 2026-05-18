<?php

namespace App\Console\Commands;

use App\Models\RoamApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RenewOrderCommandTest extends Command
{
    protected $signature = 'roam:renew-order';

    protected $description = 'Test renewCardByApiCode API';

    public function handle()
    {
        $roam = RoamApi::first();

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Replace with REAL values
        |
        */

        $payload = [

            /*
            |--------------------------------------------------------------------------
            | AUTH
            |--------------------------------------------------------------------------
            */

            'token' => '82605-EF8CD50DEC9C9CCE57430052102E2FA3',

            /*
            |--------------------------------------------------------------------------
            | RENEW TARGET
            |--------------------------------------------------------------------------
            |
            | ICCID OR ORDER NUMBER
            |
            */

            'iccid' => '8948010010034947860',

            /*
            |--------------------------------------------------------------------------
            | PACKAGE IDENTIFICATION
            |--------------------------------------------------------------------------
            */

            'apiCode' => '99915-0-?-500-M-D',

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL
            |--------------------------------------------------------------------------
            */

            'daypassDays' => 1,
        ];

        /*
        |--------------------------------------------------------------------------
        | GENERATE SIGN
        |--------------------------------------------------------------------------
        */

        $payload['sign'] = $this->createSign(
            $payload,
            $roam->client_key
        );

        $this->line('');
        $this->info('========== RENEW ORDER ==========');

        dump([
            'url' => rtrim($roam->api_url, '/')
                . '/api_esim/renewCardByApiCode',

            'payload' => $payload,
        ]);

        /*
        |--------------------------------------------------------------------------
        | API REQUEST
        |--------------------------------------------------------------------------
        */

        $response = Http::asForm()->post(
            rtrim($roam->api_url, '/')
                . '/api_esim/renewCardByApiCode',

            $payload
        );

        $data = $response->json();

        /*
        |--------------------------------------------------------------------------
        | LOGGING
        |--------------------------------------------------------------------------
        */

        Log::info('ROAM_RENEW_ORDER', [
            'payload' => $payload,
            'response' => $data,
        ]);

        dump($data);

        /*
        |--------------------------------------------------------------------------
        | ERROR HANDLING
        |--------------------------------------------------------------------------
        */

        if (($data['code'] ?? null) != 0) {

            $this->error('Renew order failed');

            return self::FAILURE;
        }

        $this->info('Renew order success');

        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */

        $renewData = $data['data'] ?? [];

        $this->line('');
        $this->info('========== RENEW RESULT ==========');

        dump($renewData);

        return self::SUCCESS;
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

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        */

        ksort($data);

        $plainText = '';

        foreach ($data as $key => $value) {

            if (is_array($value)) {

                $value = implode(',', $value);
            }

            $plainText .= $key . '=' . trim((string)$value);
        }

        /*
        |--------------------------------------------------------------------------
        | APPEND CLIENT KEY
        |--------------------------------------------------------------------------
        */

        $plainText .= $clientKey;

        /*
        |--------------------------------------------------------------------------
        | MD5
        |--------------------------------------------------------------------------
        */

        $sign = md5($plainText);

        /*
        |--------------------------------------------------------------------------
        | DEBUG
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
