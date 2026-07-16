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

        View::composer('admin.layouts.header', function ($view) {
            $canLoadContactMessages = Schema::hasTable('contact_us');
            $unreadContactMessages = $canLoadContactMessages
                ? ContactUs::where('status', false)->latest()->limit(10)->get()
                : collect();

            $view->with([
                'unreadContactMessages' => $unreadContactMessages,
                'unreadContactMessageCount' => $canLoadContactMessages
                    ? ContactUs::where('status', false)->count()
                    : 0,
            ]);
        });

        RoamOrder::created(function (RoamOrder $order) {
            app(OrderNotificationService::class)->orderCreated($order);
        });

        RoamOrder::updated(function (RoamOrder $order) {
            if ($order->wasChanged('our_status')) {
                app(OrderNotificationService::class)->orderStatusChanged(
                    $order,
                    (int) $order->getOriginal('our_status'),
                    (int) $order->our_status
                );
            }
        });

        JoytelOrder::created(function (JoytelOrder $order) {
            app(OrderNotificationService::class)->orderCreated($order);
        });

        JoytelOrder::updated(function (JoytelOrder $order) {
            if ($order->wasChanged('our_status')) {
                app(OrderNotificationService::class)->orderStatusChanged(
                    $order,
                    (int) $order->getOriginal('our_status'),
                    (int) $order->our_status
                );
            }
        });
    }
}
