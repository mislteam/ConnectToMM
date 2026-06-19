<?php

namespace App\Services\Roam;

use App\Models\RoamApi;
use App\Models\RoamPhysicalSku;
use App\Models\RoamSku;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RoamIccidSupportService
{
    private const LOG_CONTEXT = 'roam_iccid_support';

    /**
     * @param  array<int,array<string,mixed>>  $cartItems
     * @param  array<int,array<int,string>>  $iccidNumbersByIndex
     * @return array<int,array<string,mixed>>
     */
    public function validateCartSelections(array $cartItems, array $iccidNumbersByIndex): array
    {
        $failures = [];
        $responsesByIccid = [];

        foreach ($cartItems as $index => $item) {
            $orderType = strtolower((string) ($item['order_type'] ?? ''));
            if ($orderType !== 'recharge') {
                continue;
            }

            $iccids = array_values(array_filter(array_map(function ($value) {
                return preg_replace('/\D+/', '', (string) $value);
            }, (array) ($iccidNumbersByIndex[$index] ?? [])), static fn($value) => $value !== ''));

            if (empty($iccids)) {
                continue;
            }

            $selection = $this->resolveSelection($item);

            Log::debug('Roam ICCID support validation started', [
                'context' => self::LOG_CONTEXT,
                'cart_index' => $index,
                'order_type' => $orderType,
                'service_type' => $item['service_type'] ?? null,
                'country_name' => $selection['country_name'],
                'selected_roam_sku_id' => $selection['roam_sku_id'],
                'selected_api_code' => $selection['api_code'],
                'iccids' => $iccids,
            ]);

            foreach ($iccids as $iccid) {
                if (!array_key_exists($iccid, $responsesByIccid)) {
                    $responsesByIccid[$iccid] = $this->getIccidSupportPackages($iccid);
                }

                $supportMatch = $this->matchSupportedSelection(
                    $responsesByIccid[$iccid]['data'] ?? [],
                    $selection['roam_sku_id'],
                    $selection['api_code']
                );

                if ($supportMatch['supported']) {
                    continue;
                }

                Log::warning('Roam ICCID support validation failed', [
                    'context' => self::LOG_CONTEXT,
                    'cart_index' => $index,
                    'iccid' => $iccid,
                    'country_name' => $selection['country_name'],
                    'selected_roam_sku_id' => $selection['roam_sku_id'],
                    'selected_api_code' => $selection['api_code'],
                    'reason' => $supportMatch['reason'],
                    'supported_sku_ids' => $supportMatch['supported_sku_ids'] ?? [],
                    'matched_package_api_codes' => $supportMatch['matched_package_api_codes'] ?? [],
                ]);

                $failures[] = [
                    'index' => $index,
                    'iccid' => $iccid,
                    'country_name' => $selection['country_name'],
                    'reason' => $supportMatch['reason'],
                    'message' => $this->buildFailureMessage($selection, $iccid, $supportMatch['reason']),
                ];
            }
        }

        return $failures;
    }

    public function getIccidSupportPackages(string $iccid): array
    {
        return $this->requestWithToken('/api_esim/getIccidSupportPackageInfo', [
            'iccid' => $iccid,
        ]);
    }

    /**
     * @param  array<string,mixed>  $item
     * @return array{roam_sku_id:string,api_code:?string,country_name:string}
     */
    private function resolveSelection(array $item): array
    {
        $serviceType = strtolower((string) ($item['service_type'] ?? ''));
        $remoteSkuId = $item['roam_sku_id'] ?? $item['remote_sku_id'] ?? null;
        $countryName = (string) ($item['country_name'] ?? 'Selected package');
        $apiCode = isset($item['api_code']) ? trim((string) $item['api_code']) : null;

        if ($remoteSkuId !== null && $remoteSkuId !== '') {
            return [
                'roam_sku_id' => (string) $remoteSkuId,
                'api_code' => $apiCode !== '' ? $apiCode : null,
                'country_name' => $countryName,
            ];
        }

        $cartSkuId = $item['sku_id'] ?? $item['sku'] ?? null;
        if (!$cartSkuId) {
            throw new RuntimeException('Cart item is missing a SKU identifier.');
        }

        if ($serviceType === 'physical') {
            $physicalSku = RoamPhysicalSku::query()->find($cartSkuId);
            if (!$physicalSku) {
                throw new RuntimeException('Selected physical SIM package could not be resolved.');
            }

            return [
                'roam_sku_id' => (string) $physicalSku->sku_id,
                'api_code' => $apiCode !== '' ? $apiCode : null,
                'country_name' => $countryName !== '' ? $countryName : (string) $physicalSku->country_name,
            ];
        }

        $esimSku = RoamSku::query()->find($cartSkuId);
        if (!$esimSku) {
            throw new RuntimeException('Selected eSIM package could not be resolved.');
        }

        return [
            'roam_sku_id' => (string) $esimSku->sku_id,
            'api_code' => $apiCode !== '' ? $apiCode : null,
            'country_name' => $countryName !== '' ? $countryName : (string) $esimSku->country_name,
        ];
    }

    /**
     * @param  mixed  $data
     * @return array{supported:bool,reason:?string,supported_sku_ids?:array<int,string>,matched_package_api_codes?:array<int,string>}
     */
    private function matchSupportedSelection(mixed $data, string $roamSkuId, ?string $apiCode): array
    {
        $supportedSkus = $this->normalizeSupportedSkus($data);
        $supportedSkuIds = collect($supportedSkus)
            ->map(fn (array $sku) => (string) ($sku['skuid'] ?? ''))
            ->filter()
            ->values()
            ->all();

        $matchedSku = collect($supportedSkus)->first(function (array $sku) use ($roamSkuId) {
            return (string) ($sku['skuid'] ?? '') === (string) $roamSkuId;
        });

        if (!$matchedSku) {
            return [
                'supported' => false,
                'reason' => 'sku_missing',
                'supported_sku_ids' => $supportedSkuIds,
            ];
        }

        if ($apiCode !== null && $apiCode !== '') {
            $packageCodes = collect($matchedSku['packages'] ?? [])
                ->map(fn(array $pkg) => $pkg['apiCode'] ?? $pkg['api_code'] ?? null)
                ->filter(fn($value) => is_string($value) && trim($value) !== '')
                ->map(fn(string $value) => trim($value))
                ->values();

            if ($packageCodes->isNotEmpty() && !$packageCodes->contains($apiCode)) {
                return [
                    'supported' => false,
                    'reason' => 'package_missing',
                    'supported_sku_ids' => $supportedSkuIds,
                    'matched_package_api_codes' => $packageCodes->all(),
                ];
            }
        }

        Log::debug('Roam ICCID support validation passed', [
            'context' => self::LOG_CONTEXT,
            'selected_roam_sku_id' => $roamSkuId,
            'selected_api_code' => $apiCode,
            'supported_sku_ids' => $supportedSkuIds,
            'matched_package_api_codes' => isset($packageCodes) ? $packageCodes->all() : [],
        ]);

        return [
            'supported' => true,
            'reason' => null,
            'supported_sku_ids' => $supportedSkuIds,
            'matched_package_api_codes' => isset($packageCodes) ? $packageCodes->all() : [],
        ];
    }

    /**
     * @param  mixed  $data
     * @return array<int,array<string,mixed>>
     */
    private function normalizeSupportedSkus(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        if (array_key_exists('skuid', $data)) {
            return [$this->normalizeSupportedSkuEntry($data)];
        }

        return array_values(array_filter(array_map(function ($item) {
            return is_array($item) ? $this->normalizeSupportedSkuEntry($item) : null;
        }, $data)));
    }

    /**
     * @param  array<string,mixed>  $sku
     * @return array<string,mixed>
     */
    private function normalizeSupportedSkuEntry(array $sku): array
    {
        $packages = $sku['esimPackageVoList'] ?? $sku['esimPackageDtoList'] ?? [];

        return [
            'skuid' => $sku['skuid'] ?? null,
            'packages' => is_array($packages) ? array_values(array_filter($packages, 'is_array')) : [],
        ];
    }

    /**
     * @param  array{roam_sku_id:string,api_code:?string,country_name:string}  $selection
     */
    private function buildFailureMessage(array $selection, string $iccid, ?string $reason): string
    {
        $countryName = $selection['country_name'] !== '' ? $selection['country_name'] : 'Selected package';

        if ($reason === 'package_missing') {
            return "ICCID {$iccid} cannot buy the selected {$countryName} package.";
        }

        return "ICCID {$iccid} does not support the selected {$countryName} SKU.";
    }

    private function requestWithToken(string $endpoint, array $payload): array
    {
        $auth = $this->authenticate();
        $api = $auth['api'];

        $requestPayload = array_filter([
            'token' => $auth['token'],
            'iccid' => $payload['iccid'] ?? null,
        ], static fn($value) => $value !== null && $value !== '');

        $requestPayload['sign'] = $this->createSign($requestPayload, $api->client_key);

        Log::debug('Roam ICCID support request', [
            'context' => self::LOG_CONTEXT,
            'endpoint' => $endpoint,
            'payload' => $this->sanitizePayloadForLog($requestPayload),
        ]);

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->asForm()
            ->post(rtrim($api->api_url, '/') . $endpoint, $requestPayload);

        if (!$response->successful()) {
            Log::warning('Roam ICCID support HTTP failure', [
                'context' => self::LOG_CONTEXT,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Roam ICCID support lookup failed with HTTP status ' . $response->status() . '.');
        }

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new RuntimeException('Roam ICCID support lookup returned invalid JSON.');
        }

        Log::debug('Roam ICCID support response', [
            'context' => self::LOG_CONTEXT,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'code' => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'response_summary' => $this->summarizeSupportResponse($decoded['data'] ?? null),
        ]);

        $code = (string) ($decoded['code'] ?? '');
        if ($code !== '0') {
            $message = (string) ($decoded['message'] ?? 'Roam ICCID support lookup failed.');
            Log::warning('Roam ICCID support API error', [
                'context' => self::LOG_CONTEXT,
                'endpoint' => $endpoint,
                'code' => $code,
                'message' => $message,
                'response' => $decoded,
            ]);

            throw new RuntimeException("Roam ICCID support lookup failed: {$message}");
        }

        return $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function summarizeSupportResponse(mixed $data): array
    {
        $supportedSkus = $this->normalizeSupportedSkus($data);

        return [
            'sku_count' => count($supportedSkus),
            'sku_ids' => collect($supportedSkus)
                ->map(fn (array $sku) => (string) ($sku['skuid'] ?? ''))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function authenticate(): array
    {
        $api = RoamApi::first();
        if (!$api) {
            throw new RuntimeException('Roam API credentials are not configured.');
        }

        $loginPayload = [
            'phonenumber' => $api->client_id,
            'password' => $api->secret_key,
        ];
        $loginPayload['sign'] = $this->createSign($loginPayload, $api->client_key);

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->asForm()
            ->post(rtrim($api->api_url, '/') . '/api_order/login', $loginPayload);

        if (!$response->successful()) {
            throw new RuntimeException('Roam API login failed with HTTP status ' . $response->status() . '.');
        }

        $decoded = $response->json();
        $token = data_get($decoded, 'data.token');
        if (!$token) {
            $message = (string) ($decoded['message'] ?? 'Roam API login failed.');
            throw new RuntimeException($message);
        }

        return [
            'api' => $api,
            'token' => $token,
        ];
    }

    private function sanitizePayloadForLog(array $payload): array
    {
        $masked = $payload;
        foreach (['password', 'secret_key', 'client_key', 'sign', 'token'] as $key) {
            if (array_key_exists($key, $masked)) {
                $masked[$key] = '***';
            }
        }

        return $masked;
    }

    private function createSign(array $data, string $clientKey): string
    {
        unset($data['sign']);

        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        ksort($data);

        $plainText = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $plainText .= $key . '=' . trim((string) $value);
        }

        return md5($plainText . $clientKey);
    }
}
