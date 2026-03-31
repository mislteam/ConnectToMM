<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ContactInfo::create([
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500 s,when an unknown printer took a galley of type and scrambled it to make a type specimen book . It has survived not only five centuries,but also the leap into electronic typesetting,remaining essentially unchanged .',
            'email' => 'harry@gmail.com',
            'phone' => '+9591234567, +959 987654321',
            'social_media_links' => [
                [
                    'title' => 'Facebook',
                    'icon'  => 'fa-brands fa-facebook',
                    'link'  => 'https://facebook.com/page',
                ],
                [
                    'title' => 'Twitter',
                    'icon'  => 'fa-brands fa-twitter',
                    'link'  => 'https://twitter.com/handle',
                ],
            ]
        ]);
    }
}
