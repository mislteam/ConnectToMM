<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'logo' => [
                'value' => 'logo.png',
                'type' => 'file',
            ],
            'title' => [
                'value' => 'Connect to Myanmar',
                'type' => 'string',
            ],
            'joytel_title' => [
                'value' => 'Joytel',
                'type' => 'string',
            ],
            'joytel_logo' => [
                'value' => 'joy_logo.png',
                'type' => 'file',
            ],
            'roam_title' => [
                'value' => 'Roam',
                'type' => 'string',
            ],
            'roam_logo' => [
                'value' => 'roam_logo.png',
                'type' => 'file',
            ],
            'roam_order_types' => [
                'value' => json_encode([
                    'esim_new',
                    'esim_recharge',
                    'physical_new',
                    'physical_recharge'
                ]),
                'type' => 'string',
            ],
            'joytel_order_types' => [
                'value' => json_encode([
                    'esim_new',
                    'esim_recharge',
                    'physical_new',
                    'physical_recharge'
                ]),
                'type' => 'string',
            ],
        ];
        foreach ($settings as $name => $data) {
            GeneralSetting::updateOrCreate(
                ['name' => $name],
                [
                    'value' => $data['value'],
                    'type' => $data['type']
                ]
            );
        }
    }
}
