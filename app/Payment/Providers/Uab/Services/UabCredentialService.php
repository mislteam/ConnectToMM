<?php

namespace App\Payment\Providers\Uab\Services;

use App\Payment\Providers\Uab\DTO\UabCredentialData;
use App\Payment\Providers\Uab\Exceptions\UabAuthenticationException;
use App\Models\UabCredential;
use App\Payment\Providers\Uab\Repositories\UabCredentialRepository;

class UabCredentialService
{
    public function __construct(
        private readonly UabCredentialRepository $uabCredentialRepository,
    ) {}

    public function getActiveCredential(): UabCredentialData
    {
        $credential = $this->uabCredentialRepository->active();

        if (!$credential instanceof UabCredential) {
            throw new UabAuthenticationException('UAB credentials are not configured.', 500);
        }

        $requiredFields = [
            'base_url' => $credential->base_url,
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'access_key' => $credential->access_key,
            'secret_key' => $credential->secret_key,
            'merchant_id' => $credential->merchant_id,
            'channel' => $credential->channel,
            'ins_id' => $credential->ins_id,
        ];

        foreach ($requiredFields as $field => $value) {
            if (!is_string($value) || trim($value) === '') {
                throw new UabAuthenticationException("UAB credential field [{$field}] is missing.", 500);
            }
        }

        return new UabCredentialData(
            baseUrl: (string) $credential->base_url,
            clientId: (string) $credential->client_id,
            clientSecret: (string) $credential->client_secret,
            accessKey: (string) $credential->access_key,
            secretKey: (string) $credential->secret_key,
            merchantId: (string) $credential->merchant_id,
            channel: (string) $credential->channel,
            insId: (string) $credential->ins_id,
            notifyUrl: $credential->notify_url !== null ? (string) $credential->notify_url : null,
            successUrl: $credential->success_url !== null ? (string) $credential->success_url : null,
            cancelUrl: $credential->cancel_url !== null ? (string) $credential->cancel_url : null,
            gatewayPaymentMethods: $credential->payment_methods !== null ? (string) $credential->payment_methods : null,
            billingAddressLine1: $credential->billing_address_line1 !== null ? (string) $credential->billing_address_line1 : null,
            billingAddressLine2: $credential->billing_address_line2 !== null ? (string) $credential->billing_address_line2 : null,
            billingCity: $credential->billing_city !== null ? (string) $credential->billing_city : null,
            billingPostalCode: $credential->billing_postal_code !== null ? (string) $credential->billing_postal_code : null,
            billingState: $credential->billing_state !== null ? (string) $credential->billing_state : null,
            billingCountry: $credential->billing_country !== null ? (string) $credential->billing_country : null,
            version: (string) config('uab.version', '1.0.0'),
            timeoutSeconds: (int) config('uab.timeout', 20),
            tokenBufferSeconds: (int) config('uab.token_buffer_seconds', 30),
        );
    }
}
