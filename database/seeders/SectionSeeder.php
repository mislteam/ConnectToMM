<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('sections') as $key => $config) {
            Section::updateOrCreate(
                ['section_key' => $key],
                [
                    'type' => $config['type'],
                    'eyebrow_text' => \Illuminate\Support\Str::title(str_replace('_', ' ', $key)),
                    'title' => 'Edit Section Title',
                    'description' => null,
                    'image' => null,
                    'video' => null
                ]
            );
        }
    }
}
