<?php

namespace App\Payment\Providers\Uab\Services\Callback;

use App\Payment\Providers\Uab\Contracts\CallbackInterface;
use App\Payment\Providers\Uab\Contracts\SignatureInterface;
use App\Payment\Providers\Uab\DTO\CallbackData;
use App\Payment\Providers\Uab\DTO\ClientNotificationData;
use App\Payment\Providers\Uab\Enums\TransactionStatus;
use App\Payment\Providers\Uab\Repositories\CallbackLogRepository;
use App\Payment\Providers\Uab\Services\Notification\ClientNotificationService;
use App\Payment\Providers\Uab\Services\PaymentLifecycleService;
use App\Payment\Providers\Uab\Services\Transaction\TransactionService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CallbackService implements CallbackInterface
{
    public function __construct(
        private readonly CallbackLogRepository $callbackLogRepository,
        private readonly SignatureInterface $signatureService,
        private readonly TransactionService $transactionService,
        private readonly ClientNotificationService $clientNotificationService,
        private readonly PaymentLifecycleService $paymentLifecycleService,
    ) {}

    public function handle(CallbackData $data): array
    {
        return $this->process($data);
    }

    public function handleSuccessRedirect(CallbackData $data): array
    {
        return $this->process($data);
    }

    public function handleCancelRedirect(CallbackData $data): array
    {
        return $this->process($data);
    }

    private function process(CallbackData $data): array
    {
        if (!$this->verifySignature($data)) {
            $this->callbackLogRepository->create([
                'payment_transaction_id' => null,
                'request_payload' => $data->payload,
                'response_payload' => ['message' => 'Invalid signature.'],
                'status' => 'INVALID_SIGNATURE',
                'retry_count' => 0,
            ]);

            throw new HttpException(400, 'Invalid signature.');
        }

        $transaction = $this->transactionService->findByRequestId($data->requestId);

        if ($transaction === null) {
            $this->callbackLogRepository->create([
                'payment_transaction_id' => null,
                'request_payload' => $data->payload,
                'response_payload' => ['message' => 'Transaction not found.'],
                'status' => 'TRANSACTION_NOT_FOUND',
                'retry_count' => 0,
            ]);

            throw new HttpException(404, 'Transaction not found.');
        }

        $status = $this->resolveStatus($data);
        $providerResponse = array_merge(
            (array) ($transaction->provider_response ?? []),
            [$data->eventType => $data->payload]
        );

        $updated = $this->transactionService->updateByRequestId($data->requestId, [
            'transaction_id' => $data->transactionId ?? $transaction->transaction_id,
            'status' => $status->value,
            'provider_response' => $providerResponse,
        ]);

        if (!$updated) {
            $this->callbackLogRepository->create([
                'payment_transaction_id' => $transaction->id,
                'request_payload' => $data->payload,
                'response_payload' => ['message' => 'Failed to update transaction.'],
                'status' => 'GATEWAY_FAILURE',
                'retry_count' => 0,
            ]);

            throw new HttpException(502, 'Gateway failure.');
        }

        $responsePayload = [
            'message' => 'Callback processed successfully.',
            'request_id' => $data->requestId,
            'status' => $status->value,
            'outer_order_id' => (string) ($transaction->merchant_reference ?? ''),
        ];

        $this->callbackLogRepository->create([
            'payment_transaction_id' => $transaction->id,
            'request_payload' => $data->payload,
            'response_payload' => $responsePayload,
            'status' => $status->value,
            'retry_count' => 0,
        ]);

        $this->clientNotificationService->notify(new ClientNotificationData(
            requestId: $data->requestId,
            status: $status,
            payload: $data->payload,
        ));

        $this->paymentLifecycleService->syncForPaymentResult(
            (string) ($transaction->merchant_reference ?? ''),
            $status
        );

        return $responsePayload;
    }

    private function verifySignature(CallbackData $data): bool
    {
        if (!is_string($data->signature) || $data->signature === '') {
            return false;
        }

        $signature = str_replace(' ', '+', $data->signature);

        if ($data->eventType === 'notify') {
            return $this->signatureService->verify($data->payload, $signature, [
                'method' => 'POST',
                'uri' => $data->uri,
                'timestamp' => (string) ($data->headers['X-Auth-Timestamp'] ?? ''),
                'nonce' => (string) ($data->headers['X-Auth-Nonce'] ?? ''),
                'request_id' => $data->requestId,
            ]);
        }

        $queryParamSets = $this->redirectSignatureQueryParamSets($data);
        $candidateUris = $this->redirectSignatureUris($data);
        $querySeparators = [',', '&'];

        foreach ($candidateUris as $contextKey => $uris) {
            foreach ($uris as $uri) {
                foreach ($querySeparators as $separator) {
                    foreach ($queryParamSets as $queryParams) {
                        if ($this->signatureService->verify($data->payload, $signature, [
                            'method' => 'GET',
                            $contextKey => $uri,
                            'request_id' => $data->requestId,
                            'query_params' => $queryParams,
                            'query_separator' => $separator,
                        ])) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function redirectSignatureUris(CallbackData $data): array
    {
        $path = '/' . ltrim($data->uri, '/');
        $alias = $data->eventType === 'success' ? 'success_page' : 'cancel_page';
        $exactUris = [$path];

        if ($data->url !== '') {
            $exactUris[] = $data->url;
        }

        return [
            'uri' => array_values(array_unique([
                $alias,
                ltrim($data->uri, '/'),
            ])),
            'uri_exact' => array_values(array_unique($exactUris)),
        ];
    }

    private function redirectSignatureQueryParamSets(CallbackData $data): array
    {
        $baseFields = ['RequestID', 'TransactionReferenceNumber'];
        $fieldSets = [$baseFields];

        if ($data->eventType === 'success' && array_key_exists('TransactionID', $data->payload)) {
            $fieldSets[] = [...$baseFields, 'TransactionID'];
        }

        return array_values(array_unique($fieldSets, SORT_REGULAR));
    }

    private function resolveStatus(CallbackData $data): TransactionStatus
    {
        $description = strtoupper((string) ($data->status ?? ''));

        return match ($description) {
            'SUCCESS' => TransactionStatus::SUCCESS,
            'CANCELLED' => TransactionStatus::CANCELLED,
            'EXPIRED' => TransactionStatus::EXPIRED,
            'DECLINED', 'ERROR' => TransactionStatus::FAILED,
            default => match ($data->eventType) {
                'success' => TransactionStatus::SUCCESS,
                'cancel' => TransactionStatus::CANCELLED,
                default => TransactionStatus::PROCESSING,
            },
        };
    }
}
