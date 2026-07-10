<?php

namespace App\Payment\Providers\Uab\DTO;

final class TransactionStatusRequestData
{
    public function __construct(
        public readonly string $requestId,
    ) {
    }
}
