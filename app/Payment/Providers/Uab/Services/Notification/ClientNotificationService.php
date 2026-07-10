<?php

namespace App\Payment\Providers\Uab\Services\Notification;

use App\Payment\Providers\Uab\Contracts\NotificationInterface;
use App\Payment\Providers\Uab\DTO\ClientNotificationData;

class ClientNotificationService implements NotificationInterface
{
    public function __construct()
    {
    }

    public function notify(ClientNotificationData $data): void
    {
        // This project is the merchant system itself, so no external forwarding is required.
    }
}
