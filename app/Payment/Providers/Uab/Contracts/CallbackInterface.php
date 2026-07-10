<?php

namespace App\Payment\Providers\Uab\Contracts;

use App\Payment\Providers\Uab\DTO\CallbackData;

interface CallbackInterface
{
    public function handle(CallbackData $data): array;
}
