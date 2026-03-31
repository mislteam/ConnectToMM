<?php

namespace Database\Seeders;

use App\Models\Joytel;
use App\Models\JoyUsageLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JoyUsageLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location_array = [
            'Australia',
            'Canada'
        ];
        foreach ($location_array as $location) {
            JoyUsageLocation::create([
                'location' => $location
            ]);
        }
    }
}
