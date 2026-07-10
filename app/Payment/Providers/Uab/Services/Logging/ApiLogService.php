<?php

namespace App\Payment\Providers\Uab\Services\Logging;

use App\Payment\Providers\Uab\Repositories\PaymentApiLogRepository;

class ApiLogService
{
    public function __construct(
        private readonly PaymentApiLogRepository $paymentApiLogRepository,
    ) {
    }

    public function log(array $attributes): void
    {
        $this->paymentApiLogRepository->create([
            'payment_transaction_id' => $attributes['payment_transaction_id'] ?? null,
            'endpoint' => $attributes['endpoint'],
            'http_method' => strtoupper((string) $attributes['http_method']),
            'request_payload' => $this->maskPayload($attributes['request_payload'] ?? null),
            'response_payload' => $this->maskPayload($attributes['response_payload'] ?? null),
            'status_code' => $attributes['status_code'] ?? null,
            'execution_time' => $attributes['execution_time'] ?? null,
        ]);
    }

    public function maskPayload(mixed $payload): mixed
    {
        if (!is_array($payload)) {
            return $payload;
        }

        $masked = [];

        foreach ($payload as $key => $value) {
            if ($this->shouldMask((string) $key)) {
                $masked[$key] = $this->maskValue($value);
                continue;
            }

            $masked[$key] = is_array($value)
                ? $this->maskPayload($value)
                : $value;
        }

        return $masked;
    }

    private function shouldMask(string $key): bool
    {
        return in_array(strtolower($key), [
            'accesskey',
            'clientsecret',
            'access_token',
            'accesstoken',
            'authorization',
            'secret',
            'secretkey',
            'x-auth-signature',
            'signature',
        ], true);
    }

    private function maskValue(mixed $value): string
    {
        $stringValue = (string) $value;
        $length = strlen($stringValue);

        if ($length <= 8) {
            return str_repeat('*', max(4, $length));
        }

        return substr($stringValue, 0, 4)
            . str_repeat('*', $length - 8)
            . substr($stringValue, -4);
    }
}
