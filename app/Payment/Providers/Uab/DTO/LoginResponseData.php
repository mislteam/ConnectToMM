<?php

namespace App\Payment\Providers\Uab\DTO;

use Carbon\CarbonImmutable;

final class LoginResponseData
{
    public function __construct(
        public readonly string $accessToken,
        public readonly CarbonImmutable $expiredAt,
        public readonly int $expiresIn,
        public readonly bool $cached = false,
        public readonly array $providerResponse = [],
    ) {
    }
}
