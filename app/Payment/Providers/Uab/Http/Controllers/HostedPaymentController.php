<?php

namespace App\Payment\Providers\Uab\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Payment\Providers\Uab\Contracts\HostedPaymentInterface;
use App\Payment\Providers\Uab\DTO\HostedPaymentRequestData;
use App\Payment\Providers\Uab\Enums\Currency;
use App\Payment\Providers\Uab\Enums\PaymentMethod;
use App\Payment\Providers\Uab\Http\Requests\HostedPaymentCheckoutRequest;
use App\Payment\Providers\Uab\Http\Resources\HostedPaymentResponseResource;
use App\Payment\Providers\Uab\Services\HostedPayment\HostedPaymentService;

class HostedPaymentController extends Controller
{
    public function __construct(
        private readonly HostedPaymentInterface $hostedPaymentService,
    ) {}

    public function store(HostedPaymentCheckoutRequest $request): HostedPaymentResponseResource
    {
        $validated = $request->validated();
        $requestId = $request->input('request_id');

        if (!$requestId && $this->hostedPaymentService instanceof HostedPaymentService) {
            $requestId = $this->hostedPaymentService->generateRequestId();
        }

        return new HostedPaymentResponseResource(
            $this->hostedPaymentService->createHostedCheckout(
                new HostedPaymentRequestData(
                    requestId: (string) $requestId,
                    merchantReference: (string) ($validated['merchant_reference'] ?? $validated['order_no']),
                    invoiceNo: (string) $validated['invoice_no'],
                    orderNo: (string) $validated['order_no'],
                    amount: number_format((float) $validated['amount'], 2, '.', ''),
                    currency: Currency::from((string) $validated['currency']),
                    paymentMethod: PaymentMethod::from((string) $validated['payment_method']),
                    gatewayPaymentMethods: $validated['payment_methods'] ?? null,
                    billToAddressLine1: (string) $validated['bill_to_address_line1'],
                    billToAddressLine2: (string) $validated['bill_to_address_line2'],
                    billToAddressCity: (string) $validated['bill_to_address_city'],
                    billToAddressPostalCode: (string) $validated['bill_to_address_postal_code'],
                    billToAddressState: (string) $validated['bill_to_address_state'],
                    billToAddressCountry: (string) $validated['bill_to_address_country'],
                    billToForename: (string) $validated['bill_to_forename'],
                    billToSurname: (string) $validated['bill_to_surname'],
                    billToPhone: (string) $validated['bill_to_phone'],
                    billToEmail: (string) $validated['bill_to_email'],
                    expiredInSeconds: (int) ($validated['expired_in_seconds'] ?? 300),
                    remark: $validated['remark'] ?? null,
                    userDefined1: $validated['user_defined_1'] ?? null,
                    userDefined2: $validated['user_defined_2'] ?? null,
                    userDefined3: $validated['user_defined_3'] ?? null,
                    userDefined4: $validated['user_defined_4'] ?? null,
                    userDefined5: $validated['user_defined_5'] ?? null,
                )
            )
        );
    }
}
