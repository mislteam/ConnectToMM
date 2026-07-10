<?php

namespace App\Payment\Providers\Uab\Contracts;

use App\Payment\Providers\Uab\DTO\ClientNotificationData;

interface NotificationInterface
{
    public function notify(ClientNotificationData $data): void;
}
