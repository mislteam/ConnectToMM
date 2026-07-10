<?php

namespace App\Payment\Providers\Uab\Contracts;

use App\Payment\Providers\Uab\DTO\TransactionStatusRequestData;
use App\Payment\Providers\Uab\DTO\TransactionStatusResponseData;

interface TransactionInterface
{
    public function getStatus(TransactionStatusRequestData $data): TransactionStatusResponseData;
}
