<?php

namespace App\Payment\Providers\Uab\Contracts;

use App\Payment\Providers\Uab\DTO\HostedPaymentRequestData;
use App\Payment\Providers\Uab\DTO\HostedPaymentResponseData;

interface HostedPaymentInterface
{
    public function createHostedCheckout(HostedPaymentRequestData $data): HostedPaymentResponseData;
}
