<?php

namespace App\Payment\Providers\Uab\Services\Transaction;

use App\Payment\Providers\Uab\Repositories\PaymentTransactionRepository;

class TransactionService
{
    public function __construct(
        private readonly PaymentTransactionRepository $paymentTransactionRepository,
    ) {
    }

    public function updateByRequestId(string $requestId, array $attributes): bool
    {
        return $this->paymentTransactionRepository->updateByRequestId($requestId, $attributes);
    }

    public function findByRequestId(string $requestId): ?\App\Models\UabPaymentTransaction
    {
        return $this->paymentTransactionRepository->findByRequestId($requestId);
    }
}
