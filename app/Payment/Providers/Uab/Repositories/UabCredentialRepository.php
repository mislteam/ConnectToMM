<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabCredential;

class UabCredentialRepository
{
    public function __construct(
        private readonly UabCredential $model,
    ) {
    }

    public function active(): ?UabCredential
    {
        $query = $this->model->newQuery()
            ->where('payment_setting_id', 2)
            ->orderByDesc('is_active')
            ->latest('id');

        return $query->first() ?? $this->model->newQuery()->latest('id')->first();
    }
}
