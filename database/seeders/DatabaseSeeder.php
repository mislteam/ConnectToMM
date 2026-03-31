<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BannerSeeder::class,
            ContactInfoSeeder::class,
            CurrencySeeder::class,
            ImportantLinkSeeder::class,
            SectionSeeder::class,
            ItemSeeder::class,
            JoyUsageLocationSeeder::class,
            LinkSeeder::class,
        ]);
    }
}
