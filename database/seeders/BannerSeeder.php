<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'page' => 'About Us',
                'title' => 'About Us',
                'subtitle' => 'About us Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'about_us',
                'status' => 0
            ],
            [
                'page' => 'Faq',
                'title' => 'Faq',
                'subtitle' => 'Faq Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'faq',
                'status' => 0
            ],
            [
                'page' => 'Blog',
                'title' => 'Blog',
                'subtitle' => 'Blog Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'blog',
                'status' => 0
            ],
            [
                'page' => 'Contact Us',
                'title' => 'Contact Us',
                'subtitle' => 'Contact Us Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'contact_us',
                'status' => 0
            ],
            [
                'page' => 'Joytel eSIM',
                'title' => 'Joytel eSIM',
                'subtitle' => 'Joytel eSIM Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'joytel_esim',
                'status' => 0
            ],
            [
                'page' => 'Joytel Physical',
                'title' => 'Joytel Physical',
                'subtitle' => 'Joytel Physical Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'joytel_physical',
                'status' => 0
            ],
            [
                'page' => 'Rom eSIM',
                'title' => 'Rom eSIM',
                'subtitle' => 'Rom eSIM Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'rom_esim',
                'status' => 0
            ],
            [
                'page' => 'Rom Physical',
                'title' => 'Rom Physical',
                'subtitle' => 'Rom Physical Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'rom_physical',
                'status' => 0
            ],
            [
                'page' => 'My Account',
                'title' => 'My Account',
                'subtitle' => 'My Account Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'my_account',
                'status' => 0
            ],
            [
                'page' => 'Order',
                'title' => 'Order',
                'subtitle' => 'Order Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'order',
                'status' => 0
            ],
            [
                'page' => 'Checkout',
                'title' => 'Checkout',
                'subtitle' => 'Checkout Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'checkout',
                'status' => 0
            ],
            [
                'page' => 'Payment',
                'title' => 'Payment',
                'subtitle' => 'Payment Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'payment',
                'status' => 0
            ],
            [
                'page' => 'Order Detail',
                'title' => 'Order Detail',
                'subtitle' => 'Order Detail Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'banner_type' => 'order_detail',
                'status' => 0
            ],
        ];
        foreach ($banners as $banner) {
            \App\Models\Banner::updateOrCreate(
                ['page' => $banner['page']],

                [
                    'title' => $banner['title'],
                    'subtitle' => $banner['subtitle'],
                    'banner_type' => $banner['banner_type'],
                    'status' => $banner['status']
                ]
            );
        }
    }
}
