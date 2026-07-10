<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabCallbackLog;

class CallbackLogRepository
{
    public function __construct(
        private readonly UabCallbackLog $model,
    ) {
    }

    public function create(array $attributes): UabCallbackLog
    {
        return $this->model->newQuery()->create($attributes);
    }
}
