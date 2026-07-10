<?php

namespace App\Payment\Providers\Uab\DTO;

final class UabCredentialData
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $accessKey,
        public readonly string $secretKey,
        public readonly string $merchantId,
        public readonly string $channel,
        public readonly string $insId,
        public readonly ?string $notifyUrl,
        public readonly ?string $successUrl,
        public readonly ?string $cancelUrl,
        public readonly ?string $gatewayPaymentMethods = null,
        public readonly ?string $billingAddressLine1 = null,
        public readonly ?string $billingAddressLine2 = null,
        public readonly ?string $billingCity = null,
        public readonly ?string $billingPostalCode = null,
        public readonly ?string $billingState = null,
        public readonly ?string $billingCountry = null,
        public readonly string $version = '1.0.0',
        public readonly int $timeoutSeconds = 20,
        public readonly int $tokenBufferSeconds = 30,
    ) {
    }
}
