<?php

namespace App\Payment\Providers\Uab\DTO;

final class CallbackData
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $transactionId,
        public readonly ?string $transactionReferenceNumber,
        public readonly ?string $status,
        public readonly ?string $signature,
        public readonly array $payload = [],
        public readonly array $headers = [],
        public readonly string $eventType = 'notify',
        public readonly string $uri = '',
        public readonly string $url = '',
    ) {
    }
}
