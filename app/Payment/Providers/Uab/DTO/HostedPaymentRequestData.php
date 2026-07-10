<?php

namespace App\Payment\Providers\Uab\DTO;

use App\Payment\Providers\Uab\Enums\Currency;
use App\Payment\Providers\Uab\Enums\PaymentMethod;

final class HostedPaymentRequestData
{
    public function __construct(
        public readonly string $requestId,
        public readonly string $merchantReference,
        public readonly string $invoiceNo,
        public readonly string $orderNo,
        public readonly string $amount,
        public readonly Currency $currency,
        public readonly PaymentMethod $paymentMethod,
        public readonly ?string $gatewayPaymentMethods,
        public readonly string $billToAddressLine1,
        public readonly string $billToAddressLine2,
        public readonly string $billToAddressCity,
        public readonly string $billToAddressPostalCode,
        public readonly string $billToAddressState,
        public readonly string $billToAddressCountry,
        public readonly string $billToForename,
        public readonly string $billToSurname,
        public readonly string $billToPhone,
        public readonly string $billToEmail,
        public readonly int $expiredInSeconds = 300,
        public readonly ?string $remark = null,
        public readonly ?string $userDefined1 = null,
        public readonly ?string $userDefined2 = null,
        public readonly ?string $userDefined3 = null,
        public readonly ?string $userDefined4 = null,
        public readonly ?string $userDefined5 = null,
    ) {
    }
}
