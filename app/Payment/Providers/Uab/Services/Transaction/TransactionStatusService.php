<?php

namespace App\Payment\Providers\Uab\Services\Transaction;

use App\Payment\Providers\Uab\Contracts\AuthenticationInterface;
use App\Payment\Providers\Uab\Contracts\SignatureInterface;
use App\Payment\Providers\Uab\Contracts\TransactionInterface;
use App\Payment\Providers\Uab\DTO\LoginRequestData;
use App\Payment\Providers\Uab\DTO\TransactionStatusRequestData;
use App\Payment\Providers\Uab\DTO\TransactionStatusResponseData;
use App\Payment\Providers\Uab\Enums\TransactionStatus;
use App\Payment\Providers\Uab\Exceptions\UabAuthenticationException;
use App\Payment\Providers\Uab\Exceptions\UabInvalidResponseException;
use App\Payment\Providers\Uab\Exceptions\UabTimeoutException;
use App\Payment\Providers\Uab\Repositories\PaymentTransactionRepository;
use App\Payment\Providers\Uab\Services\Logging\ApiLogService;
use App\Payment\Providers\Uab\Services\UabCredentialService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TransactionStatusService implements TransactionInterface
{
    public function __construct(
        private readonly AuthenticationInterface $authenticationService,
        private readonly SignatureInterface $signatureService,
        private readonly PaymentTransactionRepository $paymentTransactionRepository,
        private readonly ApiLogService $apiLogService,
        private readonly UabCredentialService $uabCredentialService,
    ) {
    }

    public function getStatus(TransactionStatusRequestData $data): TransactionStatusResponseData
    {
        $transaction = $this->paymentTransactionRepository->findByRequestId($data->requestId);

        if ($transaction === null) {
            throw new HttpException(404, 'Transaction not found.');
        }

        $credentials = $this->uabCredentialService->getActiveCredential();

        $loginResponse = $this->authenticationService->login(
            new LoginRequestData(
                clientId: $credentials->clientId,
                clientSecret: $credentials->clientSecret,
                insId: $credentials->insId,
                baseUrl: $credentials->baseUrl,
                version: $credentials->version,
                timeoutSeconds: $credentials->timeoutSeconds,
                tokenBufferSeconds: $credentials->tokenBufferSeconds,
            )
        );

        $payload = $this->buildPayload($data, $credentials->version, $credentials->insId, $credentials->merchantId);
        $timestamp = now()->format('Y-m-d\TH:i:sP');
        $nonce = (string) data_get($payload, 'MsgInfo.MsgID');
        $endpoint = rtrim($credentials->baseUrl, '/') . '/api/transaction/status';
        $headers = [
            'Authorization' => 'Bearer ' . $loginResponse->accessToken,
            'X-Auth-AccessKey' => $credentials->accessKey,
            'X-Auth-Timestamp' => $timestamp,
            'X-Auth-Nonce' => $nonce,
            'X-Auth-Signature' => $this->signatureService->generate($payload, [
                'method' => 'POST',
                'uri' => 'api/transaction/status',
                'timestamp' => $timestamp,
                'nonce' => $nonce,
            ]),
        ];

        $startedAt = microtime(true);

        try {
            $response = Http::timeout($credentials->timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->withHeaders($headers)
                ->post($endpoint, $payload);
        } catch (ConnectionException $exception) {
            $this->apiLogService->log([
                'payment_transaction_id' => $transaction->id,
                'endpoint' => '/api/transaction/status',
                'http_method' => 'POST',
                'request_payload' => [
                    'headers' => $headers,
                    'body' => $payload,
                ],
                'response_payload' => ['message' => $exception->getMessage()],
                'status_code' => null,
                'execution_time' => $this->calculateExecutionTime($startedAt),
            ]);

            throw new UabTimeoutException('UAB transaction status request timed out.');
        }

        $responsePayload = $response->json();
        $responseHeaders = [
            'X-Resp-Timestamp' => $response->header('X-Resp-Timestamp'),
            'X-Resp-Nonce' => $response->header('X-Resp-Nonce'),
            'X-Resp-Signature' => $response->header('X-Resp-Signature'),
            'X-Auth-AccessKey' => $response->header('X-Auth-AccessKey'),
        ];

        $this->apiLogService->log([
            'payment_transaction_id' => $transaction->id,
            'endpoint' => '/api/transaction/status',
            'http_method' => 'POST',
            'request_payload' => [
                'headers' => $headers,
                'body' => $payload,
            ],
            'response_payload' => [
                'headers' => $responseHeaders,
                'body' => is_array($responsePayload) ? $responsePayload : ['raw' => $response->body()],
            ],
            'status_code' => $response->status(),
            'execution_time' => $this->calculateExecutionTime($startedAt),
        ]);

        if (!$response->successful()) {
            throw new UabAuthenticationException('UAB transaction status request failed.', max(400, $response->status()));
        }

        if (!is_array($responsePayload)) {
            throw new UabInvalidResponseException();
        }

        $responseCode = (string) data_get($responsePayload, 'MsgResponse.ResponseCode', '');
        $responseMessage = (string) data_get($responsePayload, 'MsgResponse.ResponseMsg', 'UAB transaction status request failed.');

        if ($responseCode !== '000') {
            throw new UabAuthenticationException($responseMessage, 400);
        }

        if (!$this->verifyResponseSignature($responsePayload, $responseHeaders, $responseCode)) {
            throw new UabInvalidResponseException('UAB transaction status response signature verification failed.');
        }

        $status = $this->mapGatewayStatus((string) data_get($responsePayload, 'MsgData.Status', ''));
        $transactionId = data_get($responsePayload, 'MsgData.TransactionID');

        $providerResponse = array_merge(
            (array) ($transaction->provider_response ?? []),
            ['status_query' => $responsePayload]
        );

        $this->paymentTransactionRepository->updateByRequestId($data->requestId, [
            'transaction_id' => is_string($transactionId) && $transactionId !== '' ? $transactionId : $transaction->transaction_id,
            'status' => $status->value,
            'provider_response' => $providerResponse,
        ]);

        return new TransactionStatusResponseData(
            requestId: $data->requestId,
            transactionId: is_string($transactionId) && $transactionId !== '' ? $transactionId : $transaction->transaction_id,
            status: $status,
            providerResponse: $responsePayload,
        );
    }

    private function buildPayload(
        TransactionStatusRequestData $data,
        string $version,
        string $insId,
        string $merchantId,
    ): array
    {
        $timestamp = now()->format('YmdHis');

        return [
            'MsgInfo' => [
                'VersionNo' => $version,
                'MsgID' => $this->generateMessageId($insId, $timestamp),
                'TimeStamp' => $timestamp,
                'MsgType' => 'GET_TRANSACTION_STATUS',
                'InsID' => $insId,
            ],
            'MsgData' => [
                'RequestID' => $data->requestId,
                'MerchantUserID' => $merchantId,
            ],
        ];
    }

    private function mapGatewayStatus(string $status): TransactionStatus
    {
        return match (strtoupper($status)) {
            'SUCCESS' => TransactionStatus::SUCCESS,
            'CANCELLED' => TransactionStatus::CANCELLED,
            'EXPIRED' => TransactionStatus::EXPIRED,
            'DECLINED', 'ERROR', 'FAILED' => TransactionStatus::FAILED,
            'PENDING' => TransactionStatus::PENDING,
            default => TransactionStatus::PROCESSING,
        };
    }

    private function verifyResponseSignature(array $payload, array $headers, string $responseCode): bool
    {
        $signature = (string) ($headers['X-Resp-Signature'] ?? '');
        $timestamp = (string) ($headers['X-Resp-Timestamp'] ?? '');
        $nonce = (string) ($headers['X-Resp-Nonce'] ?? '');

        if ($signature === '' || $timestamp === '' || $nonce === '') {
            return false;
        }

        foreach (['/api/transaction/status', 'api/transaction/status'] as $uri) {
            if ($this->signatureService->verify($payload, $signature, [
                'method' => 'POST',
                'uri_exact' => $uri,
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'response_code' => $responseCode,
            ])) {
                return true;
            }
        }

        return false;
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
