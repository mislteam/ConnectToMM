<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportantLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['text' => 'Home', 'link' => '/contact'],
            ['text' => 'About Us', 'link' => '/faq'],
            ['text' => 'E-SIM', 'link' => '/contact'],
            ['text' => 'Physical SIM', 'link' => '/contact'],
            ['text' => 'Contact Us', 'link' => '/contact'],
        ];
        foreach ($data as $d) {
            \App\Models\Link::create([
                'text' => $d['text'],
                'link' => $d['link'],
                'type' => 'important'
            ]);
        }
    }
}
