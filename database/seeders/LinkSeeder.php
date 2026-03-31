<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['text' => 'Support', 'link' => '/contact'],
            ['text' => 'FAQ', 'link' => '/faq'],
            ['text' => 'Members', 'link' => '/contact'],
        ];
        foreach ($data as $d) {
            \App\Models\Link::create([
                'text' => $d['text'],
                'link' => $d['link'],
                'type' => 'support'
            ]);
        }
    }
}
