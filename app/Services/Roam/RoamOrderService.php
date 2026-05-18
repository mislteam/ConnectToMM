<?php

namespace App\Services\Roam;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\RoamApi;
use App\Models\RoamOrder;
use App\Models\RoamOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RoamOrderService
{
    public function placeOrder(array $payload, ?Customer $customer = null): RoamOrder
    {
        $outerOrderId = $payload['outer_order_id'] ?? $this->generateOuterOrderId();
        $apiCode = $this->resolveApiCode($payload);
        $payload['api_code'] = $apiCode;

        $endpoint = ($payload['order_type'] ?? 'new') === 'recharge'
            ? '/api_esim/renewCardByApiCode'
            : '/api_esim/addEsimOrderByApiCode';

        $requestPayload = [
            'apiCode' => $apiCode,
            'count' => $payload['quantity'] ?? null,
            'daypassDays' => $payload['daypass_days'] ?? null,
            'beginDate' => $this->formatRequestDate($payload['start_date'] ?? null),
            'remark' => $payload['remark'] ?? null,
            'otherOrderId' => $outerOrderId,
            'otherItemId' => $payload['other_item_id'] ?? null,
            'otherPrice' => $payload['other_price'] ?? null,
            'backInfo' => $payload['back_info'] ?? 1,
            'dpId' => $payload['dp_id'] ?? null,
            'iccids' => $payload['iccids'] ?? null,
            'customerEmail' => $payload['customer_email'] ?? null,
            'isSendEmail' => (int) ($payload['is_send_email'] ?? 0),
            'pdfLanguage' => $payload['pdf_language'] ?? null,
        ];

        if ($endpoint !== '/api_esim/addEsimOrderByApiCode') {
            $requestPayload['orderNum'] = $payload['order_num'] ?? $payload['main_order_num'] ?? null;
            $requestPayload['iccid'] = $payload['source_iccid'] ?? $payload['iccid'] ?? null;
            unset($requestPayload['count'], $requestPayload['daypassDays'], $requestPayload['beginDate']);
        }

        $apiRequestPayload = null;
        $response = $this->requestWithToken($endpoint, $requestPayload, $apiRequestPayload);

        $payload['outer_order_id'] = $outerOrderId;
        $payload['api_code'] = $apiCode;
        $payload['main_order_num'] = $payload['main_order_num'] ?? $payload['order_num'] ?? null;
        $payload['source_iccid'] = $payload['source_iccid'] ?? $payload['iccid'] ?? null;
        $payload['api_request'] = $apiRequestPayload;

        return $this->syncLocalOrder($response, $payload, $customer);
    }

    public function getOrderInfo(string $orderNum): array
    {
        return $this->requestWithToken('/api_esim/getOrderInfo', [
            'orderNum' => $orderNum,
        ]);
    }

    public function queryOrders(array $payload = []): array
    {
        return $this->requestWithToken('/api_esim/queryOrder', [
            'startDate' => $payload['start_date'] ?? null,
            'endDate' => $payload['end_date'] ?? null,
            'outerOrderId' => $payload['outer_order_id'] ?? null,
            'pageSize' => $payload['page_size'] ?? null,
        ]);
    }

    public function sendPdfEmail(
        string $orderNum,
        string $email,
        ?string $iccids = null,
        bool $saveEmail = false,
        string $type = 'email'
    ): array {
        return $this->requestWithToken('/api_esim/handlerEsimPdf', [
            'type' => $type,
            'orderId' => $orderNum,
            'iccids' => $iccids,
            'email' => $email,
            'saveType' => $saveEmail ? 1 : 0,
        ]);
    }

    public function syncByOrderNum(string $orderNum): RoamOrder
    {
        $response = $this->getOrderInfo($orderNum);

        return $this->syncLocalOrder($response, [
            'roam_order_num' => $orderNum,
            'auto_advance_our_status' => true,
        ]);
    }

    public function syncLocalOrder(array $response, array $requestPayload = [], ?Customer $customer = null): RoamOrder
    {
        $apiData = $response['data'] ?? [];
        $remoteOrder = is_array($apiData) ? $apiData : ['orderNum' => (string) $apiData];

        $orderNum = $remoteOrder['orderNum'] ?? $requestPayload['roam_order_num'] ?? null;
        if (!$orderNum) {
            throw new RuntimeException('Roam order number was not returned by the API.');
        }

        $existingOrder = RoamOrder::where('roam_order_num', $orderNum)->first();
        $customerId = $customer?->id
            ?? ($requestPayload['customer_id'] ?? $existingOrder?->customer_id);

        if (!$customerId) {
            throw new RuntimeException('Roam order customer could not be determined.');
        }

        $currentOurStatus = (int) ($requestPayload['our_status'] ?? ($existingOrder?->our_status ?? RoamOrder::OUR_STATUS_ORDER_START));
        $remoteStatus = $remoteOrder['status'] ?? null;
        $shouldAutoAdvance = (bool) ($requestPayload['auto_advance_our_status'] ?? false);

        if ($shouldAutoAdvance && (string) $remoteStatus === (string) RoamOrder::ROAM_STATUS_NORMAL) {
            if (in_array($currentOurStatus, [
                RoamOrder::OUR_STATUS_ON_HOLD,
                RoamOrder::OUR_STATUS_PAID,
                RoamOrder::OUR_STATUS_API_PROCESSING,
            ], true)) {
                $currentOurStatus = RoamOrder::OUR_STATUS_API_SUCCESS;
            }
        }

        $order = RoamOrder::updateOrCreate(
            ['roam_order_num' => $orderNum],
            [
                'customer_id' => $customerId,
                'outer_order_id' => $remoteOrder['outerOrderid'] ?? ($requestPayload['outer_order_id'] ?? $existingOrder?->outer_order_id),
                'sku_id' => (string) ($requestPayload['sku_id'] ?? ($remoteOrder['skuId'] ?? $existingOrder?->sku_id ?? '')),
                'price_id' => $remoteOrder['priceId'] ?? ($requestPayload['price_id'] ?? $existingOrder?->price_id),
                'api_code' => $requestPayload['api_code'] ?? $existingOrder?->api_code,
                'service_type' => $requestPayload['service_type'] ?? ($existingOrder?->service_type ?? 'esim'),
                'order_type' => $requestPayload['order_type'] ?? ($existingOrder?->order_type ?? 'new'),
                'source_iccid' => $requestPayload['source_iccid'] ?? null,
                'quantity' => (int) ($remoteOrder['count'] ?? $requestPayload['quantity'] ?? $existingOrder?->quantity ?? 1),
                'unit_price' => $requestPayload['unit_price'] ?? $existingOrder?->unit_price,
                'total_price' => $requestPayload['total_price'] ?? ($remoteOrder['price'] ?? $existingOrder?->total_price ?? 0),
                'daypass_days' => $requestPayload['daypass_days'] ?? $existingOrder?->daypass_days,
                'start_date' => $this->parseApiDate($remoteOrder['startDate'] ?? ($requestPayload['start_date'] ?? null)),
                'end_date' => $this->parseApiDate($remoteOrder['endDate'] ?? ($requestPayload['end_date'] ?? null)),
                'our_status' => $currentOurStatus,
                'roam_status' => $remoteOrder['status'] ?? ($requestPayload['roam_status'] ?? $existingOrder?->roam_status),
                'main_order_num' => $remoteOrder['mainOrderNum'] ?? ($requestPayload['main_order_num'] ?? $existingOrder?->main_order_num),
                'remark' => $requestPayload['remark'] ?? $existingOrder?->remark,
                'is_send_email' => (bool) ($requestPayload['is_send_email'] ?? $existingOrder?->is_send_email ?? 0),
                'pdf_language' => $requestPayload['pdf_language'] ?? $existingOrder?->pdf_language,
                'purchase_date' => $this->parseApiDate($remoteOrder['purchaseDate'] ?? ($requestPayload['purchase_date'] ?? null)),
                'raw_response' => [
                    'request' => $requestPayload['api_request'] ?? $requestPayload,
                    'local_request' => $requestPayload,
                    'response' => $response,
                ],
            ]
        );

        if (is_array($remoteOrder['cardApiDtoList'] ?? null)) {
            foreach ($remoteOrder['cardApiDtoList'] as $card) {
                if (!is_array($card)) {
                    continue;
                }

                $iccid = $card['iccid'] ?? null;
                if (!$iccid) {
                    continue;
                }

                RoamOrderItem::updateOrCreate(
                    [
                        'roam_order_id' => $order->id,
                        'iccid' => $iccid,
                    ],
                    [
                        'data' => $card['data'] ?? null,
                        'mobile_number' => $card['mobileNumber'] ?? null,
                        'activation_code' => $card['activationCode'] ?? null,
                        'sm_dp_address' => $card['sm_dp_address'] ?? null,
                        'apn' => $card['apn'] ?? null,
                        'dp_id' => $card['dpId'] ?? null,
                        'validity' => $card['validity'] ?? null,
                        'used_mb' => $card['usedMB'] ?? null,
                        'activate_before' => $this->parseApiDate($card['activateBefore'] ?? null),
                        'start_date' => $this->parseApiDate($card['startDate'] ?? null),
                        'end_date' => $this->parseApiDate($card['endDate'] ?? null),
                        'pdf_url' => $card['pdfUrl'] ?? null,
                        'raw_card_data' => $card,
                    ]
                );
            }
        }

        return $order->load(['customer', 'items']);
    }

    private function requestWithToken(string $endpoint, array $payload, ?array &$requestPayload = null): array
    {
        $auth = $this->authenticate();
        $api = $auth['api'];
        $token = $auth['token'];

        $requestPayload = array_filter([
            'token' => $token,
            'apiCode' => $payload['apiCode'] ?? null,
            'count' => $payload['count'] ?? null,
            'daypassDays' => $payload['daypassDays'] ?? null,
            'beginDate' => $payload['beginDate'] ?? null,
            'remark' => $payload['remark'] ?? null,
            'otherOrderId' => $payload['otherOrderId'] ?? null,
            'backInfo' => $payload['backInfo'] ?? null,
            'dpId' => $payload['dpId'] ?? null,
            'iccids' => $payload['iccids'] ?? null,
            'customerEmail' => $payload['customerEmail'] ?? null,
            'isSendEmail' => $payload['isSendEmail'] ?? null,
            'pdfLanguage' => $payload['pdfLanguage'] ?? null,
            'orderNum' => $payload['orderNum'] ?? null,
            'iccid' => $payload['iccid'] ?? null,
            'otherItemId' => $payload['otherItemId'] ?? null,
            'otherPrice' => $payload['otherPrice'] ?? null,
            'type' => $payload['type'] ?? null,
            'orderId' => $payload['orderId'] ?? null,
            'email' => $payload['email'] ?? null,
            'saveType' => $payload['saveType'] ?? null,
            'startDate' => $payload['startDate'] ?? null,
            'endDate' => $payload['endDate'] ?? null,
            'outerOrderId' => $payload['outerOrderId'] ?? null,
            'pageSize' => $payload['pageSize'] ?? null,
        ], static fn($value) => !is_null($value) && $value !== '');

        $requestPayload['sign'] = $this->createSign($requestPayload, $api->client_key);

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->asForm()
            ->post(rtrim($api->api_url, '/') . $endpoint, $requestPayload);

        if (!$response->successful()) {
            Log::warning('Roam API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Roam API request failed with HTTP status ' . $response->status());
        }

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new RuntimeException('Roam API returned an invalid JSON response.');
        }

        $code = (string) ($decoded['code'] ?? '');
        if ($code !== '0') {
            $message = $decoded['message'] ?? 'Roam API error.';
            throw new RuntimeException($message);
        }

        return $decoded;
    }

    private function resolveApiCode(array $payload): ?string
    {
        if (!empty($payload['api_code'])) {
            return (string) $payload['api_code'];
        }

        $priceId = $payload['price_id'] ?? null;
        if ($priceId !== null && $priceId !== '') {
            $apiCode = PriceList::query()->find($priceId)?->product_code;

            if (!empty($apiCode)) {
                return (string) $apiCode;
            }
        }

        throw new RuntimeException('Roam api_code could not be determined from the order payload.');
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
            Log::warning('Roam API login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Roam API login failed with HTTP status ' . $response->status());
        }

        $decoded = $response->json();
        $token = data_get($decoded, 'data.token');

        if (!$token) {
            $message = $decoded['message'] ?? 'Roam API login failed.';
            throw new RuntimeException($message);
        }

        return [
            'api' => $api,
            'token' => $token,
        ];
    }

    private function createSign(array $data, string $clientKey): string
    {
        unset($data['sign']); // remove existing sign if any
        $data = array_filter($data, function ($value) {

            return $value !== null
                && $value !== '';
        });
        ksort($data);

        $plainText = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {

                $value = implode(',', $value);
            }

            $plainText .= $key . '=' . trim((string)$value);
        }

        $plainText .= $clientKey;

        $sign = md5($plainText);

        return $sign;
    }

    private function parseApiDate(mixed $value): ?Carbon
    {
        if (!$value || !is_string($value)) {
            return null;
        }

        $normalized = preg_replace('/\\s*\\(GMT[^)]*\\)$/', '', trim($value));

        try {
            return Carbon::parse($normalized);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatRequestDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('m/d/Y');
        }

        try {
            return Carbon::parse((string) $value)->format('m/d/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function generateOuterOrderId(): string
    {
        // Generates: O-20260508-14:53:39-
        $datePart = now()->format('Ymd');
        $timePart = now()->format('H:i:s');
        $prefix = "O-{$datePart}-{$timePart}-";

        $latest = RoamOrder::query()
            ->where('outer_order_id', 'like', $prefix . '%')
            ->orderByDesc('outer_order_id')
            ->value('outer_order_id');

        $sequence = 1;

        // Updated Regex to handle the colons and the date format
        // Matches: O - 8 digits - dash - 2 digits : 2 digits : 2 digits - dash - 6 digits
        if (is_string($latest) && preg_match('/^O-\d{8}-\d{2}:\d{2}:\d{2}-(\d{6})$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
