<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Models\UabCredential;
use Illuminate\Database\Seeder;

class UabCredentialSeeder extends Seeder
{
    public function run(): void
    {
        $uabPaymentSetting = PaymentSetting::firstOrCreate(
            ['type' => 'UAB Pay'],
            ['status' => 0]
        );

        UabCredential::updateOrCreate(
            [
                'payment_setting_id' => $uabPaymentSetting->id,
            ],
            [
                'channel' => 'Connect To Myanmar',
                'payment_methods' => 'uabpay,visa_master,upi,mpu,mmqr',
                'merchant_user_id' => 'PGW20240122003351452',
                'api_url' => 'https://uatpgw.transactease.com.mm',
                'base_url' => 'https://uatpgw.transactease.com.mm',
                'client_id' => 'PGW20240122003351452',
                'access_key' => 'REPLACE_WITH_UAB_ACCESS_KEY',
                'secret_key' => 'REPLACE_WITH_UAB_SECRET_KEY',
                'client_secret' => 'REPLACE_WITH_UAB_CLIENT_SECRET',
                'merchant_id' => 'PGW20240122003351452',
                'ins_id' => '24070001',
                'notify_url' => 'https://connecttomm.fwsdemopages.com/checkout',
                'success_url' => 'https://connecttomm.fwsdemopages.com/success',
                'cancel_url' => 'https://connecttomm.fwsdemopages.com/cancel',
                'billing_address_line1' => 'Merchant Billing Address Line 1',
                'billing_address_line2' => 'Merchant Billing Address Line 2',
                'billing_city' => 'Yangon',
                'billing_postal_code' => '11061',
                'billing_state' => 'Yangon',
                'billing_country' => 'MM',
                'is_active' => true,
            ]
        );
    }
}
