<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Models\UabCredential;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTypes = [
            ['type' => 'Direct Bank Transfer', 'status' => 1],
            ['type' => 'UAB Pay', 'status' => 0],
        ];

        foreach ($paymentTypes as $payment) {
            PaymentSetting::firstOrCreate([
                'type' => $payment['type'],
                'status' => $payment['status']
            ]);
        }

        UabCredential::updateOrCreate(
            [
                'payment_setting_id' => 2,
            ],
            [
                'channel' => 'Connect To Myanmar',
                'merchant_user_id' => 'MM123456789',
                'api_url' => 'https://sandbox-api.uabpay.com/payment',
                'access_key' => 'AK_7x9d2f3k8m1n',
                'secret_key' => 'SK_9p4q6r2t8v1w',
                'client_secret' => 'CS_3h7j9k2m5n8p',
            ]
        );
    }
}
