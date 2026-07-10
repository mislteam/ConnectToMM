<?php

namespace App\Payment\Providers\Uab\Services\Authentication;

use App\Payment\Providers\Uab\Contracts\AuthenticationInterface;
use App\Payment\Providers\Uab\DTO\LoginRequestData;
use App\Payment\Providers\Uab\DTO\LoginResponseData;
use App\Payment\Providers\Uab\Exceptions\UabAuthenticationException;
use App\Payment\Providers\Uab\Exceptions\UabInvalidResponseException;
use App\Payment\Providers\Uab\Exceptions\UabTimeoutException;
use App\Payment\Providers\Uab\Repositories\GatewayTokenRepository;
use App\Payment\Providers\Uab\Services\Logging\ApiLogService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AuthenticationService implements AuthenticationInterface
{
    public function __construct(
        private readonly GatewayTokenRepository $gatewayTokenRepository,
        private readonly ApiLogService $apiLogService,
    ) {}

    public function login(LoginRequestData $data): LoginResponseData
    {
        $existingToken = $this->gatewayTokenRepository->latest();
        $bufferSeconds = $data->tokenBufferSeconds;

        if (
            !$data->forceRefresh &&
            $existingToken !== null &&
            $existingToken->expired_at !== null &&
            $existingToken->expired_at->gt(now()->addSeconds($bufferSeconds))
        ) {
            return new LoginResponseData(
                accessToken: (string) $existingToken->access_token,
                expiredAt: CarbonImmutable::parse($existingToken->expired_at),
                expiresIn: max(0, now()->diffInSeconds($existingToken->expired_at, false)),
                cached: true,
            );
        }

        $payload = $this->buildPayload($data);
        $startedAt = microtime(true);

        try {
            $response = Http::timeout($data->timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->post(rtrim($data->baseUrl, '/') . '/api/login', $payload);
        } catch (ConnectionException $exception) {
            $this->apiLogService->log([
                'endpoint' => '/api/login',
                'http_method' => 'POST',
                'request_payload' => $payload,
                'response_payload' => [
                    'message' => $exception->getMessage(),
                ],
                'status_code' => null,
                'execution_time' => $this->calculateExecutionTime($startedAt),
            ]);

            throw new UabTimeoutException();
        }

        $responsePayload = $response->json();

        $this->apiLogService->log([
            'endpoint' => '/api/login',
            'http_method' => 'POST',
            'request_payload' => $payload,
            'response_payload' => is_array($responsePayload) ? $responsePayload : ['raw' => $response->body()],
            'status_code' => $response->status(),
            'execution_time' => $this->calculateExecutionTime($startedAt),
        ]);

        if (!$response->successful()) {
            throw new UabAuthenticationException('UAB authentication failure.', max(401, $response->status()));
        }

        if (!is_array($responsePayload)) {
            throw new UabInvalidResponseException();
        }

        $responseCode = (string) data_get($responsePayload, 'MsgResponse.ResponseCode', '');
        $responseMessage = (string) data_get($responsePayload, 'MsgResponse.ResponseMsg', 'UAB authentication failed.');

        if ($responseCode !== '000') {
            throw new UabAuthenticationException($responseMessage, 401);
        }

        $accessToken = data_get($responsePayload, 'MsgData.AccessToken');
        $expiresIn = data_get($responsePayload, 'MsgData.ExpiresIn');

        if (!is_string($accessToken) || $accessToken === '' || !is_numeric($expiresIn)) {
            throw new UabInvalidResponseException();
        }

        $expiredAt = CarbonImmutable::now()->addSeconds((int) $expiresIn);
        $storedToken = $this->gatewayTokenRepository->store($accessToken, $expiredAt);

        return new LoginResponseData(
            accessToken: (string) $storedToken->access_token,
            expiredAt: CarbonImmutable::parse($storedToken->expired_at),
            expiresIn: (int) $expiresIn,
            cached: false,
            providerResponse: $responsePayload,
        );
    }

    private function buildPayload(LoginRequestData $data): array
    {
        $timestamp = now()->format('YmdHis');

        return [
            'MsgInfo' => [
                'VersionNo' => $data->version,
                'MsgID' => $this->generateMessageId($data->insId, $timestamp),
                'TimeStamp' => $timestamp,
                'MsgType' => 'LOGIN',
                'InsID' => $data->insId,
            ],
            'MsgData' => [
                'ClientID' => $data->clientId,
                'ClientSecret' => $data->clientSecret,
                'GrantType' => 'client_credentials',
            ],
        ];
    }

    private function generateMessageId(string $insId, string $timestamp): string
    {
        $normalizedInsId = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $insId) ?? '');

        if (strlen($normalizedInsId) !== 8) {
            throw new UabAuthenticationException('UAB Ins ID must be exactly 8 alphanumeric characters.');
        }

        return 'M' . $normalizedInsId . $timestamp . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function calculateExecutionTime(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
