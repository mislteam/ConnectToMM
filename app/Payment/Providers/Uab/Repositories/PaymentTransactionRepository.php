<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabPaymentTransaction;

class PaymentTransactionRepository
{
    public function __construct(
        private readonly UabPaymentTransaction $model,
    ) {
    }

    public function create(array $attributes): UabPaymentTransaction
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function findByRequestId(string $requestId): ?UabPaymentTransaction
    {
        return $this->model->newQuery()
            ->where('request_id', $requestId)
            ->first();
    }

    public function updateByRequestId(string $requestId, array $attributes): bool
    {
        return (bool) $this->model->newQuery()
            ->where('request_id', $requestId)
            ->update($attributes);
    }
}
