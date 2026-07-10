<?php

namespace App\Payment\Providers\Uab\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Payment\Providers\Uab\Contracts\TransactionInterface;
use App\Payment\Providers\Uab\DTO\TransactionStatusRequestData;
use App\Payment\Providers\Uab\Http\Resources\TransactionStatusResponseResource;

class TransactionStatusController extends Controller
{
    public function __construct(
        private readonly TransactionInterface $transactionStatusService,
    ) {}

    public function show(string $requestId): TransactionStatusResponseResource
    {
        return new TransactionStatusResponseResource(
            $this->transactionStatusService->getStatus(
                new TransactionStatusRequestData($requestId)
            )
        );
    }
}
