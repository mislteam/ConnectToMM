<?php

namespace App\Payment\Providers\Uab\Contracts;

use App\Payment\Providers\Uab\DTO\LoginRequestData;
use App\Payment\Providers\Uab\DTO\LoginResponseData;

interface AuthenticationInterface
{
    public function login(LoginRequestData $data): LoginResponseData;
}
