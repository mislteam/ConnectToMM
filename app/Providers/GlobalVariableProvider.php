<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class GlobalVariableProvider extends ServiceProvider
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
            $user_usd_rate = \App\Models\Currency::where('name', 'user_usd_rate')?->value('value');
            $view->with([
                'userUsdRate' => $user_usd_rate
            ]);
        });
    }
}
