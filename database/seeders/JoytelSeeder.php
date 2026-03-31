<?php

namespace Database\Seeders;

use App\Models\Joytel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JoytelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Joytel::factory(2)->create();
    }
}
