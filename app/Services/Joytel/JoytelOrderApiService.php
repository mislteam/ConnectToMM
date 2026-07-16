<?php

namespace App\Services\Joytel;

use App\Models\JoytelApi;
use App\Models\JoytelOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class JoytelOrderApiService
{
    public function createOrder(JoytelOrder $order): array
    {
        if ($this->isRechargeOrder($order)) {
            return $this->createRechargeOrder($order);
        }

        $api = JoytelApi::query()->first();

        if (!$api) {
            throw new RuntimeException('No active Joytel API credential found.');
        }

        $customerCode = (string) $api->customer_code;
        $customerAuth = (string) $api->customer_auth;
        $warehouse = (string) data_get($order->raw_response, 'request_payload.warehouse', '');
        $type = (int) (data_get($order->raw_response, 'request_payload.type') ?: 3);
        $orderTid = $this->resolveOrderTid($order, $customerCode);
        $receiveName = $this->receiveName($order);
        $phone = $this->phone($order);
        $timestamp = (int) (microtime(true) * 1000);
        $itemList = $this->itemList($order);

        $payload = [
            'customerCode' => $customerCode,
            'orderTid' => $orderTid,
            'type' => $type,
            'warehouse' => $warehouse,
            'receiveName' => $receiveName,
            'phone' => $phone,
            'timestamp' => $timestamp,
            'email' => $this->email($order),
            'replyType' => 1,
            'remark' => (string) ($order->remark ?? ''),
            'itemList' => $itemList,
        ];

        $payload['autoGraph'] = $this->makeAutoGraph(
            $customerCode,
            $customerAuth,
            $warehouse,
            $type,
            $orderTid,
            $receiveName,
            $phone,
            $timestamp,
            $itemList
        );

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->createOrderUrl($api), $payload);

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel API returned an invalid response.');
        }

        if (($data['code'] ?? null) !== 0) {
            $message = $data['message'] ?? $data['msg'] ?? 'Joytel order submit failed.';
            throw new RuntimeException((string) $message);
        }

        if (!data_get($data, 'data.orderCode')) {
            throw new RuntimeException('Joytel API success response did not include orderCode.');
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'api_type' => 'esim_order',
            'order_tid' => data_get($data, 'data.orderTid') ?: $orderTid,
            'order_code' => data_get($data, 'data.orderCode'),
            'joytel_status' => data_get($data, 'data.status'),
        ];
    }

    public function queryOrder(string $orderTid = '', string $orderCode = ''): array
    {
        $api = JoytelApi::query()->first();

        if (!$api) {
            throw new RuntimeException('No active Joytel API credential found.');
        }

        if ($orderTid === '' && $orderCode === '') {
            throw new RuntimeException('Joytel orderTid or orderCode is required for query.');
        }

        $customerCode = (string) $api->customer_code;
        $customerAuth = (string) $api->customer_auth;
        $timestamp = (int) (microtime(true) * 1000);

        $payload = [
            'customerCode' => $customerCode,
            'timestamp' => $timestamp,
            'orderCode' => $orderCode,
            'orderTid' => $orderTid,
        ];

        $payload['autoGraph'] = sha1($customerCode . $customerAuth . $orderCode . $orderTid . $timestamp);

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->queryOrderUrl($api), $payload);

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel query API returned an invalid response.');
        }

        if (($data['code'] ?? null) !== 0) {
            $message = $data['message'] ?? $data['msg'] ?? 'Joytel order query failed.';
            throw new RuntimeException((string) $message);
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'api_type' => 'esim_order',
            'order_tid' => data_get($data, 'data.orderTid') ?: $orderTid,
            'order_code' => data_get($data, 'data.orderCode') ?: $orderCode,
            'joytel_status' => data_get($data, 'data.status'),
            'item_list' => data_get($data, 'data.itemList', []),
        ];
    }

    public function queryRechargeOrder(string $orderTid = '', string $rechargeCode = ''): array
    {
        $api = JoytelApi::query()->first();

        if (!$api) {
            throw new RuntimeException('No active Joytel API credential found.');
        }

        if ($orderTid === '' && $rechargeCode === '') {
            throw new RuntimeException('Joytel orderTid or rechargeCode is required for recharge query.');
        }

        $customerCode = (string) $api->customer_code;
        $customerAuth = (string) $api->customer_auth;
        $timestamp = (int) (microtime(true) * 1000);

        $payload = [
            'customerCode' => $customerCode,
            'orderTid' => $orderTid,
            'rechargeCode' => $rechargeCode,
            'timestamp' => $timestamp,
        ];

        $payload['autoGraph'] = sha1($customerCode . $customerAuth . $timestamp . $rechargeCode . $orderTid);

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->rechargeQueryUrl($api), $payload);

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel recharge query API returned an invalid response.');
        }

        if (($data['code'] ?? null) !== 0) {
            $message = $data['message'] ?? $data['msg'] ?? 'Joytel recharge query failed.';
            throw new RuntimeException((string) $message);
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'api_type' => 'recharge_order',
            'order_tid' => data_get($data, 'data.orderTid') ?: $orderTid,
            'order_code' => data_get($data, 'data.rechargeCode') ?: $rechargeCode,
            'joytel_status' => data_get($data, 'data.status') ?? data_get($data, 'data.itemList.0.status'),
            'item_list' => data_get($data, 'data.itemList', []),
        ];
    }

    public function redeemCoupon(string $coupon, int $qrcodeType = 1): array
    {
        if (trim($coupon) === '') {
            throw new RuntimeException('Joytel coupon is required for redemption.');
        }

        $api = $this->rspApi();
        $transId = (string) Str::uuid();
        $timestamp = (int) (microtime(true) * 1000);
        $payload = [
            'coupon' => $coupon,
            'qrcodeType' => $qrcodeType,
        ];

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders($this->rspHeaders($api, $transId, $timestamp))
            ->post($this->rspUrl($api, '/openapi/coupon/redeem'), $payload);
        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel coupon redeem API returned an invalid response.');
        }

        if ((string) ($data['code'] ?? '') !== '000') {
            throw new RuntimeException((string) ($data['mesg'] ?? $data['message'] ?? 'Joytel coupon redeem failed.'));
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'transaction_id' => $transId,
        ];
    }

    public function getTransactionStatus(string $transactionId): array
    {
        if (trim($transactionId) === '') {
            throw new RuntimeException('Joytel transaction ID is required.');
        }

        $api = $this->rspApi();
        $requestTransId = (string) Str::uuid();
        $timestamp = (int) (microtime(true) * 1000);
        $payload = ['qTransId' => $transactionId];

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders($this->rspHeaders($api, $requestTransId, $timestamp))
            ->post($this->rspUrl($api, '/openapi/getTransactionStatus'), $payload);
        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel transaction status API returned an invalid response.');
        }

        if ((string) ($data['resultCode'] ?? '') !== '000') {
            throw new RuntimeException((string) ($data['resultMesg'] ?? 'Joytel eSIM provisioning is not ready.'));
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'transaction_id' => $transactionId,
            'data' => (array) data_get($data, 'data', []),
        ];
    }

    public function queryEsimUsage(string $coupon): array
    {
        if (trim($coupon) === '') {
            throw new RuntimeException('Joytel eSIM SN PIN is required.');
        }

        $api = $this->rspApi();
        $transId = (string) Str::uuid();
        $timestamp = (int) (microtime(true) * 1000);
        $payload = ['coupon' => $coupon];

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders($this->rspHeaders($api, $transId, $timestamp))
            ->post($this->rspUrl($api, '/openapi/esim/usage/query'), $payload);
        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel eSIM usage API returned an invalid response.');
        }

        if ((string) ($data['code'] ?? '') !== '000') {
            throw new RuntimeException((string) ($data['mesg'] ?? $data['message'] ?? 'Joytel eSIM usage query failed.'));
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'data' => (array) data_get($data, 'data', []),
        ];
    }

    public function querySimUsage(string $cid, string $rspOrderId, string $imsi = ''): array
    {
        if (trim($cid) === '') {
            throw new RuntimeException('Joytel SIM SN Code / CID is required.');
        }

        if (trim($rspOrderId) === '') {
            throw new RuntimeException('Joytel RSP Order ID is required.');
        }

        $api = $this->rspApi();
        $transId = (string) Str::uuid();
        $timestamp = (int) (microtime(true) * 1000);
        $payload = [
            'cid' => $cid,
            'orderId' => $rspOrderId,
        ];

        if (trim($imsi) !== '') {
            $payload['imsi'] = trim($imsi);
        }

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders($this->rspHeaders($api, $transId, $timestamp))
            ->post($this->rspUrl($api, '/openapi/sim/usage/query'), $payload);
        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel SIM usage API returned an invalid response.');
        }

        if ((string) ($data['code'] ?? '') !== '000') {
            throw new RuntimeException((string) ($data['mesg'] ?? $data['message'] ?? 'Joytel SIM usage query failed.'));
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'data' => (array) data_get($data, 'data', []),
        ];
    }

    public function generateOrderTid(string $customerCode): string
    {
        return $customerCode . now()->format('YmdHis') . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function createRechargeOrder(JoytelOrder $order): array
    {
        $api = JoytelApi::query()->first();

        if (!$api) {
            throw new RuntimeException('No active Joytel API credential found.');
        }

        $customerCode = (string) $api->customer_code;
        $customerAuth = (string) $api->customer_auth;
        $orderTid = $this->resolveOrderTid($order, $customerCode);
        $timestamp = (int) (microtime(true) * 1000);
        $itemList = $this->rechargeItemList($order);

        $payload = [
            'customerCode' => $customerCode,
            'orderTid' => $orderTid,
            'timestamp' => $timestamp,
            'itemList' => $itemList,
        ];

        $itemString = '';
        foreach ($itemList as $item) {
            $itemString .= $item['productCode'] . $item['snCode'] . $item['days'];
        }

        $payload['autoGraph'] = sha1($customerCode . $customerAuth . $timestamp . $itemString . $orderTid);

        $response = Http::timeout(60)
            ->connectTimeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->rechargeOrderUrl($api), $payload);

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Joytel recharge API returned an invalid response.');
        }

        if (($data['code'] ?? null) !== 0) {
            $message = $data['message'] ?? $data['msg'] ?? 'Joytel recharge submit failed.';
            throw new RuntimeException((string) $message);
        }

        if (!data_get($data, 'data.rechargeCode')) {
            throw new RuntimeException('Joytel recharge success response did not include rechargeCode.');
        }

        return [
            'payload' => $payload,
            'response' => $data,
            'api_type' => 'recharge_order',
            'order_tid' => data_get($data, 'data.orderTid') ?: $orderTid,
            'order_code' => data_get($data, 'data.rechargeCode'),
            'joytel_status' => data_get($data, 'data.status'),
        ];
    }

    private function rechargeItemList(JoytelOrder $order): array
    {
        $productCode = $order->items()->whereNotNull('product_code')->value('product_code')
            ?: data_get($order->raw_response, 'request_payload.productCode')
            ?: data_get($order->raw_response, 'cart_item.product_code');
        $snCode = trim((string) ($order->source_sn_code ?: data_get($order->raw_response, 'request_payload.snCode') ?: data_get($order->raw_response, 'cart_item.source_sn_code')));
        $days = (int) ($order->validity_days ?: data_get($order->raw_response, 'request_payload.days') ?: 1);

        if (!$productCode) {
            throw new RuntimeException('Joytel recharge product code is missing.');
        }

        if ($snCode === '') {
            throw new RuntimeException('Joytel recharge SN code is missing.');
        }

        return [[
            'productCode' => (string) $productCode,
            'snCode' => $snCode,
            'days' => max(1, $days),
        ]];
    }

    private function resolveOrderTid(JoytelOrder $order, string $customerCode): string
    {
        $orderTid = trim((string) ($order->outer_order_id ?: data_get($order->raw_response, 'request_payload.orderTid')));

        return $orderTid !== '' ? $orderTid : $this->generateOrderTid($customerCode);
    }

    private function itemList(JoytelOrder $order): array
    {
        $productCode = $order->items()->whereNotNull('product_code')->value('product_code')
            ?: data_get($order->raw_response, 'request_payload.productCode')
            ?: data_get($order->raw_response, 'cart_item.product_code');

        if (!$productCode) {
            throw new RuntimeException('Joytel product code is missing.');
        }

        return [[
            'productCode' => (string) $productCode,
            'quantity' => max(1, (int) $order->quantity),
        ]];
    }

    private function makeAutoGraph(
        string $customerCode,
        string $customerAuth,
        string $warehouse,
        int $type,
        string $orderTid,
        string $receiveName,
        string $phone,
        int $timestamp,
        array $itemList
    ): string {
        $itemString = '';

        foreach ($itemList as $item) {
            $itemString .= $item['productCode'] . $item['quantity'];
        }

        return sha1(
            $customerCode .
            $customerAuth .
            $warehouse .
            $type .
            $orderTid .
            $receiveName .
            $phone .
            $timestamp .
            $itemString
        );
    }

    private function createOrderUrl(JoytelApi $api): string
    {
        $baseUrl = trim((string) $api->api_url);

        // if ($baseUrl === '') {
        //     return 'https://api.joytelshop.com/customerApi/customerOrder';
        // }

        if (str_ends_with($baseUrl, '/customerApi/customerOrder')) {
            return $baseUrl;
        }

        return rtrim($baseUrl, '/') . '/customerApi/customerOrder';
    }

    private function queryOrderUrl(JoytelApi $api): string
    {
        $baseUrl = trim((string) $api->api_url);

        // if ($baseUrl === '') {
        //     return 'https://api.joytelshop.com/customerApi/customerOrder/query';
        // }

        if (str_ends_with($baseUrl, '/customerApi/customerOrder/query')) {
            return $baseUrl;
        }

        if (str_ends_with($baseUrl, '/customerApi/customerOrder')) {
            return $baseUrl . '/query';
        }

        return rtrim($baseUrl, '/') . '/customerApi/customerOrder/query';
    }

    private function rechargeOrderUrl(JoytelApi $api): string
    {
        $baseUrl = trim((string) $api->api_url);

        // if ($baseUrl === '') {
        //     return 'https://api.joytelshop.com/joyRechargeApi/rechargeOrder';
        // }

        if (str_ends_with($baseUrl, '/joyRechargeApi/rechargeOrder')) {
            return $baseUrl;
        }

        return rtrim($baseUrl, '/') . '/joyRechargeApi/rechargeOrder';
    }

    private function rechargeQueryUrl(JoytelApi $api): string
    {
        $baseUrl = trim((string) $api->api_url);

        // if ($baseUrl === '') {
        //     return 'https://api.joytelshop.com/joyRechargeApi/rechargeOrder/query';
        // }

        if (str_ends_with($baseUrl, '/joyRechargeApi/rechargeOrder/query')) {
            return $baseUrl;
        }

        if (str_ends_with($baseUrl, '/joyRechargeApi/rechargeOrder')) {
            return $baseUrl . '/query';
        }

        return rtrim($baseUrl, '/') . '/joyRechargeApi/rechargeOrder/query';
    }

    private function isRechargeOrder(JoytelOrder $order): bool
    {
        return strtolower((string) $order->service_type) === 'physical'
            && strtolower((string) $order->order_type) === 'recharge';
    }

    private function rspApi(): JoytelApi
    {
        $api = JoytelApi::query()->first();

        if (!$api || trim((string) $api->rsp_appid) === '' || trim((string) $api->rsp_secret) === '') {
            throw new RuntimeException('Joytel RSP API credentials are missing.');
        }

        return $api;
    }

    private function rspHeaders(JoytelApi $api, string $transId, int $timestamp): array
    {
        return [
            'AppId' => (string) $api->rsp_appid,
            'TransId' => $transId,
            'Timestamp' => $timestamp,
            'Ciphertext' => md5($api->rsp_appid . $transId . $timestamp . $api->rsp_secret),
            'Content-Type' => 'application/json',
        ];
    }

    private function rspUrl(JoytelApi $api, string $path): string
    {
        $baseUrl = rtrim(trim((string) $api->rsp_baseurl), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Joytel RSP base URL is missing.');
        }

        return $baseUrl . $path;
    }

    private function receiveName(JoytelOrder $order): string
    {
        return (string) (
            data_get($order->raw_response, 'billing.receive_name')
            ?: data_get($order->raw_response, 'billing.name')
            ?: $order->customer?->name
            ?: 'Customer'
        );
    }

    private function phone(JoytelOrder $order): string
    {
        return (string) (
            data_get($order->raw_response, 'billing.phone')
            ?: $order->customer?->phone
            ?: ''
        );
    }

    private function email(JoytelOrder $order): string
    {
        return (string) (
            data_get($order->raw_response, 'billing.email')
            ?: $order->customer?->email
            ?: ''
        );
    }
}
