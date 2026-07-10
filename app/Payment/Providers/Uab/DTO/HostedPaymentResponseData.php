<?php

namespace App\Payment\Providers\Uab\DTO;

use App\Payment\Providers\Uab\Enums\TransactionStatus;

final class HostedPaymentResponseData
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $transactionId,
        public readonly string $paymentUrl,
        public readonly TransactionStatus $status,
        public readonly array $providerResponse = [],
    ) {
    }
}
