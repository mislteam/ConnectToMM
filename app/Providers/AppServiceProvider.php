<?php

namespace App\Providers;

use App\Payment\Providers\Uab\Contracts\AuthenticationInterface;
use App\Payment\Providers\Uab\Contracts\CallbackInterface;
use App\Payment\Providers\Uab\Contracts\HostedPaymentInterface;
use App\Payment\Providers\Uab\Contracts\SignatureInterface;
use App\Payment\Providers\Uab\Contracts\TransactionInterface;
use App\Payment\Providers\Uab\Services\Authentication\AuthenticationService;
use App\Payment\Providers\Uab\Services\Callback\CallbackService;
use App\Payment\Providers\Uab\Services\HostedPayment\HostedPaymentService;
use App\Payment\Providers\Uab\Services\SignatureService;
use App\Payment\Providers\Uab\Services\Transaction\TransactionStatusService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthenticationInterface::class, AuthenticationService::class);
        $this->app->bind(CallbackInterface::class, CallbackService::class);
        $this->app->bind(HostedPaymentInterface::class, HostedPaymentService::class);
        $this->app->bind(SignatureInterface::class, SignatureService::class);
        $this->app->bind(TransactionInterface::class, TransactionStatusService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        set_time_limit(300);
    }
}
