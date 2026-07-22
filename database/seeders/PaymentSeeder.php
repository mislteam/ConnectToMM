<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTypes = [
            ['id' => PaymentSetting::DIRECT_BANK_TRANSFER_ID, 'type' => 'Direct Bank Transfer', 'status' => 1],
            ['id' => PaymentSetting::ONLINE_PAYMENT_ID, 'type' => 'Online Payment', 'status' => 1],
            ['id' => PaymentSetting::WALLET_ID, 'type' => 'Wallet', 'status' => 1],
        ];

        foreach ($paymentTypes as $payment) {
            PaymentSetting::firstOrCreate([
                'id' => $payment['id'],
            ], [
                'type' => $payment['type'],
                'status' => $payment['status'],
            ]);
        }

    }
}
