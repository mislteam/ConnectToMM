<?php

namespace App\Payment\Providers\Uab\DTO;

final class LoginRequestData
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $insId,
        public readonly string $baseUrl,
        public readonly bool $forceRefresh = false,
        public readonly string $version = '1.0.0',
        public readonly int $timeoutSeconds = 20,
        public readonly int $tokenBufferSeconds = 30,
    ) {
    }
}
