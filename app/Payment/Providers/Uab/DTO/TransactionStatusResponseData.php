<?php

namespace App\Payment\Providers\Uab\DTO;

use App\Payment\Providers\Uab\Enums\TransactionStatus;

final class TransactionStatusResponseData
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $transactionId,
        public readonly TransactionStatus $status,
        public readonly array $providerResponse = [],
    ) {
    }
}
