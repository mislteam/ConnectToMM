<?php

namespace App\Payment\Providers\Uab\Services;

use App\Payment\Providers\Uab\Contracts\SignatureInterface;
use InvalidArgumentException;

class SignatureService implements SignatureInterface
{
    public function __construct(
        private readonly UabCredentialService $uabCredentialService,
    )
    {
    }

    public function generate(array $payload, array $context = []): string
    {
        $rawString = $this->buildRawString($payload, $context);
        $secretKey = (string) ($context['secret_key'] ?? $this->uabCredentialService->getActiveCredential()->secretKey);

        if ($secretKey === '') {
            throw new InvalidArgumentException('UAB secret key is required for signature generation.');
        }

        return base64_encode(hash_hmac('sha256', $rawString, $secretKey, true));
    }

    public function verify(array $payload, string $signature, array $context = []): bool
    {
        return hash_equals($signature, $this->generate($payload, $context));
    }

    private function buildRawString(array $payload, array $context): string
    {
        $method = strtoupper((string) ($context['method'] ?? 'POST'));
        $uri = array_key_exists('uri_exact', $context)
            ? (string) $context['uri_exact']
            : ltrim((string) ($context['uri'] ?? '/'), '/');
        $requestId = (string) ($context['request_id'] ?? data_get($payload, 'RequestID', ''));

        if (isset($context['query_params']) && is_array($context['query_params'])) {
            $queryString = $this->buildFormBodyString(
                $payload,
                $context['query_params'],
                (string) ($context['query_separator'] ?? ',')
            );

            return implode('|', [
                $method,
                $uri,
                $requestId,
                $queryString,
            ]);
        }

        $timestamp = (string) ($context['timestamp'] ?? '');
        $nonce = (string) ($context['nonce'] ?? $requestId);

        if (array_key_exists('response_code', $context)) {
            return implode('|', [
                $method,
                $uri,
                $timestamp,
                $nonce,
                (string) $context['response_code'],
                (string) json_encode($payload, JSON_UNESCAPED_SLASHES),
            ]);
        }

        if (isset($context['signed_fields']) && is_array($context['signed_fields'])) {
            $formBodyString = $this->buildFormBodyString(
                $payload,
                $context['signed_fields'],
                (string) ($context['field_separator'] ?? ',')
            );

            return implode('|', [
                $method,
                $uri,
                $timestamp,
                $nonce,
                $formBodyString,
            ]);
        }

        $payloadJson = (string) json_encode($payload, JSON_UNESCAPED_SLASHES);

        return implode('|', [
            $method,
            $uri,
            $timestamp,
            $nonce,
            $payloadJson,
        ]);
    }

    private function buildFormBodyString(array $payload, array $signedFields, string $separator = ','): string
    {
        $segments = [];

        foreach ($signedFields as $field) {
            $segments[] = $field . '=' . (string) ($payload[$field] ?? '');
        }

        return implode($separator, $segments);
    }
}
