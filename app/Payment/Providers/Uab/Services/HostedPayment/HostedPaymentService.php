<?php

namespace App\Payment\Providers\Uab\Services\HostedPayment;

use App\Payment\Providers\Uab\Contracts\AuthenticationInterface;
use App\Payment\Providers\Uab\Contracts\HostedPaymentInterface;
use App\Payment\Providers\Uab\Contracts\SignatureInterface;
use App\Payment\Providers\Uab\DTO\HostedPaymentRequestData;
use App\Payment\Providers\Uab\DTO\HostedPaymentResponseData;
use App\Payment\Providers\Uab\DTO\LoginRequestData;
use App\Payment\Providers\Uab\Enums\TransactionStatus;
use App\Payment\Providers\Uab\Exceptions\UabAuthenticationException;
use App\Payment\Providers\Uab\Exceptions\UabInvalidResponseException;
use App\Payment\Providers\Uab\Exceptions\UabTimeoutException;
use App\Payment\Providers\Uab\Repositories\PaymentTransactionRepository;
use App\Payment\Providers\Uab\Services\Logging\ApiLogService;
use App\Payment\Providers\Uab\Services\UabCredentialService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HostedPaymentService implements HostedPaymentInterface
{
    public function __construct(
        private readonly AuthenticationInterface $authenticationService,
        private readonly SignatureInterface $signatureService,
        private readonly PaymentTransactionRepository $paymentTransactionRepository,
        private readonly ApiLogService $apiLogService,
        private readonly UabCredentialService $uabCredentialService,
    ) {
    }

    public function createHostedCheckout(HostedPaymentRequestData $data): HostedPaymentResponseData
    {
        $credentials = $this->uabCredentialService->getActiveCredential();

        $this->authenticationService->login(
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

        $signedDateTime = now()->format('Y-m-d\TH:i:s');
        $signedFields = [
            'MerchantUserID',
            'AccessKey',
            'Channel',
            'RequestID',
            'PaymentMethod',
            'Amount',
            'Currency',
            'InvoiceNo',
            'BillToAddressLine1',
            'BillToAddressLine2',
            'BillToAddressCity',
            'BillToAddressPostalCode',
            'BillToAddressState',
            'BillToAddressCountry',
            'BillToForename',
            'BillToSurname',
            'BillToPhone',
            'BillToEmail',
            'ExpiredInSeconds',
            'Remark',
            'UserDefined1',
            'UserDefined2',
            'UserDefined3',
            'UserDefined4',
            'UserDefined5',
            'SignedDateTime',
        ];

        $payload = [
            'MerchantUserID' => $credentials->merchantId,
            'AccessKey' => $credentials->accessKey,
            'Channel' => $credentials->channel,
            'RequestID' => $data->requestId,
            'PaymentMethod' => $data->gatewayPaymentMethods ?: $data->paymentMethod->value,
            'Amount' => $data->amount,
            'Currency' => $data->currency->value,
            'InvoiceNo' => $data->invoiceNo,
            'BillToAddressLine1' => $data->billToAddressLine1,
            'BillToAddressLine2' => $data->billToAddressLine2,
            'BillToAddressCity' => $data->billToAddressCity,
            'BillToAddressPostalCode' => $data->billToAddressPostalCode,
            'BillToAddressState' => $data->billToAddressState,
            'BillToAddressCountry' => $data->billToAddressCountry,
            'BillToForename' => $data->billToForename,
            'BillToSurname' => $data->billToSurname,
            'BillToPhone' => $data->billToPhone,
            'BillToEmail' => $data->billToEmail,
            'ExpiredInSeconds' => (string) $data->expiredInSeconds,
            'Remark' => (string) ($data->remark ?? ''),
            'UserDefined1' => (string) ($data->userDefined1 ?? ''),
            'UserDefined2' => (string) ($data->userDefined2 ?? ''),
            'UserDefined3' => (string) ($data->userDefined3 ?? ''),
            'UserDefined4' => (string) ($data->userDefined4 ?? ''),
            'UserDefined5' => (string) ($data->userDefined5 ?? ''),
            'SignedDateTime' => $signedDateTime,
            'SignedFields' => implode(',', $signedFields),
        ];

        $payload['Signature'] = $this->signatureService->generate($payload, [
            'method' => 'POST',
            'uri' => '/Payments/Request',
            'timestamp' => $signedDateTime,
            'request_id' => $data->requestId,
            'signed_fields' => $signedFields,
        ]);

        $endpoint = rtrim($credentials->baseUrl, '/') . '/Payments/Request';
        $startedAt = microtime(true);

        try {
            $response = Http::timeout($credentials->timeoutSeconds)
                ->asForm()
                ->withOptions([
                    'allow_redirects' => false,
                    'http_errors' => false,
                ])
                ->post($endpoint, $payload);
        } catch (ConnectionException $exception) {
            $this->apiLogService->log([
                'endpoint' => '/Payments/Request',
                'http_method' => 'POST',
                'request_payload' => $payload,
                'response_payload' => ['message' => $exception->getMessage()],
                'status_code' => null,
                'execution_time' => $this->calculateExecutionTime($startedAt),
            ]);

            throw new UabTimeoutException('UAB hosted payment request timed out.');
        }

        $paymentUrl = $this->extractPaymentUrl($response, $credentials->baseUrl);
        $paymentHtml = $paymentUrl === null
            ? $this->extractPaymentHtml($response->body(), $credentials->baseUrl)
            : null;
        $responsePayload = [
            'headers' => $response->headers(),
            'body' => $response->body(),
            'payment_url' => $paymentUrl,
            'payment_html_returned' => $paymentHtml !== null,
        ];

        $this->apiLogService->log([
            'endpoint' => '/Payments/Request',
            'http_method' => 'POST',
            'request_payload' => $payload,
            'response_payload' => $responsePayload,
            'status_code' => $response->status(),
            'execution_time' => $this->calculateExecutionTime($startedAt),
        ]);

        if (!$response->successful() && !in_array($response->status(), [301, 302, 303], true)) {
            throw new UabAuthenticationException('UAB hosted payment request failed.', max(400, $response->status()));
        }

        if ($paymentUrl === null && $paymentHtml === null) {
            throw new UabInvalidResponseException('UAB hosted payment URL or checkout page was not returned.');
        }

        $transaction = $this->paymentTransactionRepository->create([
            'request_id' => $data->requestId,
            'transaction_id' => null,
            'merchant_reference' => $data->merchantReference,
            'invoice_no' => $data->invoiceNo,
            'order_no' => $data->orderNo,
            'amount' => $data->amount,
            'currency' => $data->currency->value,
            'payment_method' => $data->paymentMethod->value,
            'status' => TransactionStatus::PENDING->value,
            'provider_response' => $responsePayload,
        ]);

        return new HostedPaymentResponseData(
            requestId: $transaction->request_id,
            transactionId: $transaction->transaction_id,
            paymentUrl: $paymentUrl,
            status: TransactionStatus::PENDING,
            providerResponse: $responsePayload,
            paymentHtml: $paymentHtml,
        );
    }

    public function generateRequestId(): string
    {
        return 'REQ' . now()->format('YmdHis') . Str::padLeft((string) random_int(0, 999999), 6, '0');
    }

    private function extractPaymentUrl($response, string $baseUrl): ?string
    {
        $location = $response->header('Location');
        if (is_string($location) && $location !== '') {
            $paymentUrl = $this->normalizeValidPaymentUrl($location, $baseUrl);
            if ($paymentUrl !== null) {
                return $paymentUrl;
            }
        }

        $json = $response->json();
        if (is_array($json)) {
            $paymentUrl = $this->extractPaymentUrlFromPayload($json, $baseUrl);
            if ($paymentUrl !== null) {
                return $paymentUrl;
            }
        }

        return null;
    }

    private function extractPaymentUrlFromPayload(array $payload, string $baseUrl): ?string
    {
        $urlKeys = [
            'payment_url',
            'paymenturl',
            'hostedpaymenturl',
            'hostedcheckouturl',
            'redirect_url',
            'redirecturl',
            'checkout_url',
            'checkouturl',
            'url',
        ];

        foreach ($payload as $key => $value) {
            if (is_string($value) && $value !== '' && in_array(strtolower((string) $key), $urlKeys, true)) {
                $paymentUrl = $this->normalizeValidPaymentUrl($value, $baseUrl);
                if ($paymentUrl !== null) {
                    return $paymentUrl;
                }
            }

            if (is_array($value)) {
                $paymentUrl = $this->extractPaymentUrlFromPayload($value, $baseUrl);
                if ($paymentUrl !== null) {
                    return $paymentUrl;
                }
            }
        }

        return null;
    }

    private function extractPaymentHtml(?string $body, string $baseUrl): ?string
    {
        if (!is_string($body) || trim($body) === '') {
            return null;
        }

        if (stripos($body, '<html') === false || stripos($body, '<form') === false) {
            return null;
        }

        return $this->rewriteGatewayRelativeUrls($body, $baseUrl);
    }

    private function rewriteGatewayRelativeUrls(string $html, string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        $html = preg_replace_callback(
            '/(<form\b[^>]*\saction=)(["\'])(\/(?!\/)[^"\']*)(\2)/i',
            static fn(array $matches) => $matches[1] . $matches[2] . $baseUrl . $matches[3] . $matches[4],
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/(<(?:script|img)\b[^>]*\ssrc=)(["\'])(\/(?!\/)[^"\']*)(\2)/i',
            static fn(array $matches) => $matches[1] . $matches[2] . $baseUrl . $matches[3] . $matches[4],
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/(<link\b[^>]*\shref=)(["\'])(\/(?!\/)[^"\']*)(\2)/i',
            static fn(array $matches) => $matches[1] . $matches[2] . $baseUrl . $matches[3] . $matches[4],
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/(<a\b[^>]*\shref=)(["\'])\/mailto:([^"\']*)(\2)/i',
            static fn(array $matches) => $matches[1] . $matches[2] . 'mailto:' . $matches[3] . $matches[4],
            $html
        ) ?? $html;
    }

    private function normalizePaymentUrl(string $value, string $baseUrl): string
    {
        if (preg_match('/^https?:\/\//i', $value) === 1) {
            return $value;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }

    private function normalizeValidPaymentUrl(string $value, string $baseUrl): ?string
    {
        $paymentUrl = $this->normalizePaymentUrl($value, $baseUrl);

        return $this->isRequestEndpointUrl($paymentUrl) ? null : $paymentUrl;
    }

    private function isRequestEndpointUrl(string $paymentUrl): bool
    {
        $path = parse_url($paymentUrl, PHP_URL_PATH);

        return is_string($path) && strtolower(rtrim($path, '/')) === '/payments/request';
    }

    private function calculateExecutionTime(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
