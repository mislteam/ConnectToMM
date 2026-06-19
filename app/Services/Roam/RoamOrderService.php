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
    private const LOG_CONTEXT = 'roam_api';

    /**
     * Call Roam API (2.24) for an existing local draft order and sync the upstream response into that same record.
     * This supports the "create order -> payment -> provision" flow.
     */
    public function placeOrderForExistingDraft(RoamOrder $order): RoamOrder
    {
        $apiCode = $this->resolveApiCode([
            'api_code' => $order->api_code,
            'price_id' => $order->price_id,
        ]);

        $endpoint = ($order->order_type ?? 'new') === 'recharge'
            ? '/api_esim/renewCardByApiCode'
            : '/api_esim/addEsimOrderByApiCode';

        $draftIccids = (array) data_get($order->raw_response, 'iccid_numbers', []);
        $iccids = array_values(array_filter(array_map('trim', $draftIccids), static fn($v) => $v !== ''));
        $primaryIccid = $order->source_iccid ?: ($iccids[0] ?? null);

        $requestPayload = [
            'apiCode' => $apiCode,
            'count' => (int) $order->quantity,
            'daypassDays' => $order->daypass_days,
            'beginDate' => $this->formatRequestDate($order->start_date),
            'remark' => $order->remark,
            'otherOrderId' => $order->outer_order_id,
            // Use our temporary roam_order_num as external line item id so we can trace in provider logs.
            'otherItemId' => (string) $order->roam_order_num,
            'backInfo' => 1,
            'dpId' => data_get($order->raw_response, 'dp_id'),
            'iccids' => !empty($iccids) ? implode(',', $iccids) : null,
            'customerEmail' => data_get($order->raw_response, 'customer_email', $order->customer?->email),
            'isSendEmail' => 0,
            'pdfLanguage' => $order->pdf_language ?? null,
        ];

        if ($endpoint !== '/api_esim/addEsimOrderByApiCode') {
            $requestPayload['orderNum'] = $order->main_order_num ?? null;
            $requestPayload['iccid'] = $primaryIccid;
            unset($requestPayload['count'], $requestPayload['beginDate'], $requestPayload['iccids']);
        }

        $apiRequestPayload = null;
        $response = $this->requestWithToken($endpoint, $requestPayload, $apiRequestPayload);

        return $this->syncExistingOrder($order, $response, [
            'api_request' => $apiRequestPayload,
            'api_code' => $apiCode,
            'service_type' => $order->service_type,
            'order_type' => $order->order_type,
            'quantity' => $order->quantity,
            'unit_price' => $order->unit_price,
            'total_price' => $order->billable_total_price,
            'daypass_days' => $order->daypass_days,
            'outer_order_id' => $order->outer_order_id,
        ]);
    }

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
            'backInfo' => $payload['back_info'] ?? 1,
            'dpId' => $payload['dp_id'] ?? null,
            'iccids' => $payload['iccids'] ?? null,
            'customerEmail' => $payload['customer_email'] ?? null,
            'isSendEmail' => (int) ($payload['is_send_email'] ?? 1),
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

    public function refundOrder(RoamOrder $order, ?string $remark = null): RoamOrder
    {
        $order->loadMissing(['customer', 'items']);

        $orderNum = $order->roam_order_num ?: $order->main_order_num;
        if (!$orderNum) {
            throw new RuntimeException('Roam order number is required to refund the order.');
        }

        $iccids = $order->items->pluck('iccid')->filter()->values()->implode(',');
        if ($iccids === '') {
            $iccids = $order->source_iccid ?: null;
        }

        $requestPayload = [
            'orderNum' => $orderNum,
            'iccids' => $iccids,
            'remark' => $remark ?? 'Admin refund request',
        ];

        $apiRequestPayload = null;
        $response = $this->requestWithToken('/api_esim/refundOrder', $requestPayload, $apiRequestPayload);
        $refundAmount = $this->extractRefundAmount($response);
        $remoteStatus = data_get($response, 'data.status');
        $rawResponse = (array) ($order->raw_response ?? []);
        $refund = (array) data_get($rawResponse, 'refund', []);
        $refund['roam_api'] = [
            'method' => RoamOrder::REFUND_METHOD_ROAM_API,
            'amount' => $refundAmount,
            'request' => $apiRequestPayload,
            'response' => $response,
        ];
        $refund['method'] = RoamOrder::REFUND_METHOD_ROAM_API;
        $refund['amount'] = $refundAmount;
        $rawResponse['refund'] = $refund;

        $order->fill([
            'roam_status' => is_numeric($remoteStatus) ? (int) $remoteStatus : $order->roam_status,
            'raw_response' => $rawResponse,
        ]);
        $order->save();

        return $order->refresh()->load(['customer', 'items']);
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

        $rawResponse = [
            'request' => $requestPayload['api_request'] ?? $requestPayload,
            'local_request' => $requestPayload,
            'response' => $response,
        ];
        if (data_get($existingOrder?->raw_response, 'payment')) {
            $rawResponse['payment'] = data_get($existingOrder->raw_response, 'payment');
        }
        if (data_get($existingOrder?->raw_response, 'refund')) {
            $rawResponse['refund'] = data_get($existingOrder->raw_response, 'refund');
        }

        $quantity = (int) ($remoteOrder['count'] ?? $requestPayload['quantity'] ?? $existingOrder?->quantity ?? 1);
        $unitPrice = $requestPayload['unit_price'] ?? $existingOrder?->unit_price;
        $unitPrice = $unitPrice !== null ? (int) round($unitPrice) : null;
        $totalPrice = $unitPrice !== null
            ? $unitPrice * max(1, $quantity)
            : (int) round((float) ($requestPayload['total_price'] ?? $existingOrder?->total_price ?? 0));

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
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice ?? 0,
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
                'raw_response' => $rawResponse,
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

    /**
     * Sync upstream response into an existing local order row, replacing the temporary roam_order_num if needed.
     */
    public function syncExistingOrder(RoamOrder $order, array $response, array $requestPayload = []): RoamOrder
    {
        $apiData = $response['data'] ?? [];
        $remoteOrder = is_array($apiData) ? $apiData : ['orderNum' => (string) $apiData];

        $orderNum = $remoteOrder['orderNum'] ?? null;
        if (!$orderNum) {
            throw new RuntimeException('Roam order number was not returned by the API.');
        }

        // If the API returned a different order number than our temp one, replace it.
        if ((string) $order->roam_order_num !== (string) $orderNum) {
            $exists = RoamOrder::query()
                ->where('roam_order_num', $orderNum)
                ->where('id', '!=', $order->id)
                ->exists();

            if ($exists) {
                throw new RuntimeException('Roam order number already exists locally: ' . $orderNum);
            }

            $order->roam_order_num = (string) $orderNum;
        }

        $remoteStatus = $remoteOrder['status'] ?? null;
        $currentOurStatus = (int) ($order->our_status ?? RoamOrder::OUR_STATUS_ORDER_START);

        // Auto-advance to API_SUCCESS when upstream says paid/normal.
        if ((string) $remoteStatus === (string) RoamOrder::ROAM_STATUS_NORMAL) {
            if (in_array($currentOurStatus, [
                RoamOrder::OUR_STATUS_ON_HOLD,
                RoamOrder::OUR_STATUS_PAID,
                RoamOrder::OUR_STATUS_API_PROCESSING,
            ], true)) {
                $currentOurStatus = RoamOrder::OUR_STATUS_API_SUCCESS;
            }
        }

        $rawResponse = [
            'request' => $requestPayload['api_request'] ?? $requestPayload,
            'local_request' => $requestPayload,
            'response' => $response,
        ];
        if (data_get($order->raw_response, 'payment')) {
            $rawResponse['payment'] = data_get($order->raw_response, 'payment');
        }
        if (data_get($order->raw_response, 'refund')) {
            $rawResponse['refund'] = data_get($order->raw_response, 'refund');
        }

        $quantity = (int) ($remoteOrder['count'] ?? $requestPayload['quantity'] ?? $order->quantity ?? 1);
        $unitPrice = $requestPayload['unit_price'] ?? $order->unit_price;
        $unitPrice = $unitPrice !== null ? (int) round((float) $unitPrice) : null;
        $totalPrice = $unitPrice !== null
            ? $unitPrice * max(1, $quantity)
            : (int) round((float) ($requestPayload['total_price'] ?? $order->total_price ?? 0));

        $order->fill([
            'outer_order_id' => $remoteOrder['outerOrderid'] ?? ($requestPayload['outer_order_id'] ?? $order->outer_order_id),
            'sku_id' => (string) ($requestPayload['sku_id'] ?? ($remoteOrder['skuId'] ?? $order->sku_id ?? '')),
            'price_id' => $remoteOrder['priceId'] ?? ($requestPayload['price_id'] ?? $order->price_id),
            'api_code' => $requestPayload['api_code'] ?? $order->api_code,
            'service_type' => $requestPayload['service_type'] ?? $order->service_type,
            'order_type' => $requestPayload['order_type'] ?? $order->order_type,
            'source_iccid' => $requestPayload['source_iccid'] ?? $order->source_iccid,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice ?? 0,
            'daypass_days' => $requestPayload['daypass_days'] ?? $order->daypass_days,
            'start_date' => $this->parseApiDate($remoteOrder['startDate'] ?? ($requestPayload['start_date'] ?? null)),
            'end_date' => $this->parseApiDate($remoteOrder['endDate'] ?? ($requestPayload['end_date'] ?? null)),
            'our_status' => $currentOurStatus,
            'roam_status' => $remoteOrder['status'] ?? ($requestPayload['roam_status'] ?? $order->roam_status),
            'main_order_num' => $remoteOrder['mainOrderNum'] ?? ($requestPayload['main_order_num'] ?? $order->main_order_num),
            'remark' => $requestPayload['remark'] ?? $order->remark,
            'is_send_email' => (bool) ($requestPayload['is_send_email'] ?? $order->is_send_email ?? 0),
            'pdf_language' => $requestPayload['pdf_language'] ?? $order->pdf_language,
            'purchase_date' => $this->parseApiDate($remoteOrder['purchaseDate'] ?? ($requestPayload['purchase_date'] ?? null)),
            'raw_response' => $rawResponse,
        ]);

        $order->save();

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
                        'sm_dp_address' => $card['sm_dp_address'] ?? ($card['sm_dp_address'] ?? null),
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
            'isSendEmail' => $payload['isSendEmail'] ?? true,
            'pdfLanguage' => $payload['pdfLanguage'] ?? null,
            'orderNum' => $payload['orderNum'] ?? null,
            'iccid' => $payload['iccid'] ?? null,
            'otherItemId' => $payload['otherItemId'] ?? null,
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

        Log::debug('Roam API request', [
            'context' => self::LOG_CONTEXT,
            'endpoint' => $endpoint,
            'url' => rtrim($api->api_url, '/') . $endpoint,
            'payload' => $this->sanitizePayloadForLog($requestPayload),
        ]);

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->asForm()
            ->post(rtrim($api->api_url, '/') . $endpoint, $requestPayload);

        if (!$response->successful()) {
            Log::warning('Roam API request failed', [
                'context' => self::LOG_CONTEXT,
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
            Log::warning('Roam API returned error code', [
                'context' => self::LOG_CONTEXT,
                'endpoint' => $endpoint,
                'code' => $code,
                'message' => $message,
                'response' => $decoded,
            ]);

            throw new RuntimeException("Roam API error (code {$code}): {$message}");
        }

        Log::debug('Roam API response ok', [
            'context' => self::LOG_CONTEXT,
            'endpoint' => $endpoint,
            'code' => $code,
        ]);

        return $decoded;
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

    private function extractRefundAmount(array $response): ?float
    {
        $candidates = [
            'data.refundPrice',
            'data.refund_price',
            'data.refundAmount',
            'data.refund_amount',
            'data.price',
            'data.amount',
            'refundPrice',
            'refund_price',
            'refundAmount',
            'refund_amount',
            'price',
            'amount',
        ];

        foreach ($candidates as $path) {
            $value = data_get($response, $path);
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
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

        Log::debug('Roam API login request', [
            'context' => self::LOG_CONTEXT,
            'url' => rtrim($api->api_url, '/') . '/api_order/login',
            'payload' => $this->sanitizePayloadForLog($loginPayload),
        ]);

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->asForm()
            ->post(rtrim($api->api_url, '/') . '/api_order/login', $loginPayload);

        if (!$response->successful()) {
            Log::warning('Roam API login failed', [
                'context' => self::LOG_CONTEXT,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Roam API login failed with HTTP status ' . $response->status());
        }

        $decoded = $response->json();
        $token = data_get($decoded, 'data.token');

        if (!$token) {
            $message = $decoded['message'] ?? 'Roam API login failed.';
            Log::warning('Roam API login returned no token', [
                'context' => self::LOG_CONTEXT,
                'response' => $decoded,
            ]);
            throw new RuntimeException($message);
        }

        Log::debug('Roam API login ok', [
            'context' => self::LOG_CONTEXT,
        ]);

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
