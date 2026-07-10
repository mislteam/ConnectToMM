<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
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
            ], [
                'status' => $payment['status'],
            ]);
        }

    }
}
