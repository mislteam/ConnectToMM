<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('sections') as $key => $config) {
            $section = Section::where('section_key', $key)->first();
            if (!$section || empty($config['items'])) continue;

            $position = 1;
            foreach ($config['items'] as $itemGroup) {
                for ($i = 1; $i <= $itemGroup['count']; $i++) {
                    Item::updateOrCreate(
                        [
                            'section_id' => $section->id,
                            'position' => $position,
                        ],
                        [
                            'item_type' => $itemGroup['type'],
                            'title' => 'Edit Item Title',
                            'description' => null,
                            'item_image' => null,
                            'button_text' => null,
                            'button_url' => null,
                        ]
                    );
                    $position++;
                }
            }
        }
    }
}
