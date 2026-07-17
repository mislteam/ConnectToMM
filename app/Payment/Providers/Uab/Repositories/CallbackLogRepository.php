<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabCallbackLog;
use Illuminate\Support\Collection;

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

    public function findMethodPayloadsByRequestId(string $requestId): Collection
    {
        return $this->model->newQuery()
            ->where('request_payload->RequestID', $requestId)
            ->whereNotNull('request_payload->PaymentMethod')
            ->latest('id')
            ->get()
            ->pluck('request_payload')
            ->filter(fn($payload) => is_array($payload))
            ->values();
    }
}
