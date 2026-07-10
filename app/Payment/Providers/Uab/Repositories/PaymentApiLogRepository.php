<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabPaymentApiLog;

class PaymentApiLogRepository
{
    public function __construct(
        private readonly UabPaymentApiLog $model,
    ) {
    }

    public function create(array $attributes): UabPaymentApiLog
    {
        return $this->model->newQuery()->create($attributes);
    }
}
