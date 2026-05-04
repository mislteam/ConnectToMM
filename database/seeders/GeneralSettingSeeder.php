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
            'logo' => 'logo.png',
            'title' => 'Connect to Myanmar',
            'joytel_title' => 'Joytel',
            'joytel_logo' => 'joy_logo.png',
            'roam_title' => 'Roam',
            'roam_logo' => 'roam_logo.png',
        ];
        foreach ($settings as $name => $value) {
            GeneralSetting::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        }
    }
}
