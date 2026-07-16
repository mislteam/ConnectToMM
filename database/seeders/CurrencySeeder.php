<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            'cny' => 680,
            'usd' => 4500,
            'user_usd_rate' => 2100,
        ];

        foreach ($currencies as $name => $value) {
            Currency::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        }
    }
}
