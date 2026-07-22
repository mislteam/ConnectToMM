<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GeneralServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $settings = GeneralSetting::whereIn('name', [
                'logo',
                'title',
                'joytel_title',
                'roam_title',
                'joytel_logo',
                'roam_logo',
                'joytel_order_types',
                'roam_order_types',
                'joytel_sim_types',
                'roam_sim_types'
            ])->get()->keyBy('name');

            $view->with('settings', $settings);
        });
    }
}
