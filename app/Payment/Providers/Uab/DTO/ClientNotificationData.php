<?php

namespace App\Payment\Providers\Uab\DTO;

use App\Payment\Providers\Uab\Enums\TransactionStatus;

final class ClientNotificationData
{
    public function __construct(
        public readonly string $requestId,
        public readonly TransactionStatus $status,
        public readonly array $payload = [],
    ) {
    }
}
