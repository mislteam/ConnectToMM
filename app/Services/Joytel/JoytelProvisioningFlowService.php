<?php

namespace App\Services\Joytel;

use App\Models\Customer;
use App\Models\JoytelOrder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use App\Services\OrderNotificationService;

class JoytelProvisioningFlowService
{
    public function __construct(
        private readonly JoytelOrderApiService $joytelApi,
        private readonly OrderNotificationService $notifications,
    ) {}

    /**
     * After payment success, submit pending Joytel draft orders to Joytel and store returned item data.
     *
     * @return Collection<int,JoytelOrder>
     */
    public function provisionAfterPayment(Customer $customer, string $outerOrderId): Collection
    {
        $orders = JoytelOrder::query()
            ->with(['customer', 'items'])
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            throw new RuntimeException('Joytel orders not found for this payment reference.');
        }

        return $orders->map(function (JoytelOrder $order) {
            if ((int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT) {
                $order->forceFill(['our_status' => JoytelOrder::OUR_STATUS_PAID])->save();
            }

            if (!in_array((int) $order->our_status, [
                JoytelOrder::OUR_STATUS_PAID,
                JoytelOrder::OUR_STATUS_API_FAILED,
            ], true)) {
                return $order->fresh(['customer', 'items']);
            }

            $order->forceFill(['our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING])->save();

            try {
                $order = $this->submitOrderToJoytel($order);

                if ((int) $order->our_status === JoytelOrder::OUR_STATUS_COMPLETED) {
                    $this->notifications->paymentApproved($order);
                }

                return $order;
            } catch (\Throwable $e) {
                return $this->markJoytelSubmitFailed($order, $e);
            }
        });
    }

    public function cancelPendingPayment(string $outerOrderId): void
    {
        JoytelOrder::query()
            ->where('outer_order_id', $outerOrderId)
            ->whereIn('payment_method', ['uabpay', 'uab_pay', 'UAB Pay'])
            ->where('our_status', JoytelOrder::OUR_STATUS_PENDING_PAYMENT)
            ->orderBy('id')
            ->get()
            ->each(function (JoytelOrder $order): void {
                $rawResponse = (array) ($order->raw_response ?? []);
                $payment = (array) data_get($rawResponse, 'payment', []);
                $payment['uab']['cancelled_at'] = now()->toDateTimeString();
                $rawResponse['payment'] = $payment;

                $order->forceFill([
                    'our_status' => JoytelOrder::OUR_STATUS_CANCELLED,
                    'raw_response' => $rawResponse,
                ])->save();
            });
    }

    private function submitOrderToJoytel(JoytelOrder $order): JoytelOrder
    {
        $result = $this->joytelApi->createOrder($order->fresh(['customer', 'items']));
        $rawResponse = (array) ($order->raw_response ?? []);
        $rawResponse['joytel_create_order'] = [
            'request' => $result['payload'],
            'response' => $result['response'],
            'submitted_at' => now()->toDateTimeString(),
        ];

        $orderCode = (string) ($result['order_code'] ?: $order->joytel_order_num);

        $order->forceFill([
            'joytel_order_num' => $orderCode,
            'joytel_status' => is_numeric($result['joytel_status']) ? (int) $result['joytel_status'] : null,
            'our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING,
            'purchase_date' => now(),
            'is_send_email' => false,
            'raw_response' => $rawResponse,
        ])->save();

        try {
            $queryResult = ($result['api_type'] ?? null) === 'recharge_order'
                ? $this->joytelApi->queryRechargeOrder((string) $result['order_tid'], (string) $result['order_code'])
                : $this->joytelApi->queryOrder((string) $result['order_tid'], (string) $result['order_code']);
            $rawResponse = (array) ($order->raw_response ?? []);
            $rawResponse['joytel_query_order'] = [
                'request' => $queryResult['payload'],
                'response' => $queryResult['response'],
                'queried_at' => now()->toDateTimeString(),
            ];

            $order->forceFill([
                'joytel_status' => is_numeric($queryResult['joytel_status'] ?? null)
                    ? (int) $queryResult['joytel_status']
                    : $order->joytel_status,
                'raw_response' => $rawResponse,
            ])->save();

            $this->storeJoytelOrderItems($order, $result, $queryResult);
            $order = $this->completeProvisioningWhenReady($order, $queryResult);
        } catch (\Throwable $e) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $rawResponse['joytel_query_order_error'] = [
                'message' => $e->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ];
            $order->forceFill(['raw_response' => $rawResponse])->save();

            Log::warning('JOYTEL_QUERY_ORDER_AFTER_CREATE_FAILED', [
                'joytel_order_id' => $order->id,
                'order_code' => $orderCode,
                'message' => $e->getMessage(),
            ]);
        }

        return $order->fresh(['customer', 'items']);
    }

    private function completeProvisioningWhenReady(JoytelOrder $order, array $queryResult): JoytelOrder
    {
        if ($this->isRechargeOrder($order)) {
            $isSuccessful = (int) ($queryResult['joytel_status'] ?? -1) === 1;

            $order->forceFill([
                'our_status' => $isSuccessful
                    ? JoytelOrder::OUR_STATUS_COMPLETED
                    : JoytelOrder::OUR_STATUS_API_PROCESSING,
                'is_send_email' => $isSuccessful,
            ])->save();

            return $order->fresh(['customer', 'items']);
        }

        if ((int) ($queryResult['joytel_status'] ?? -1) !== 4) {
            $order->forceFill([
                'our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING,
                'is_send_email' => false,
            ])->save();

            return $order->fresh(['customer', 'items']);
        }

        foreach ($order->items()->whereNotNull('sn_pin')->get() as $item) {
            if (!$this->hasIncompleteProvisioningDetails($item)) {
                continue;
            }

            $callbackData = (array) ($item->raw_callback_data ?? []);
            $transactionId = (string) data_get($callbackData, 'joytel_coupon_redeem.transaction_id', '');

            if ($transactionId === '') {
                $redeemResult = $this->joytelApi->redeemCoupon((string) $item->sn_pin, 1);
                $transactionId = (string) $redeemResult['transaction_id'];
                $callbackData['joytel_coupon_redeem'] = $redeemResult;
                $item->forceFill(['raw_callback_data' => $callbackData])->save();
            }

            $transactionResult = (array) data_get($callbackData, 'joytel_transaction_status', []);
            $provisioning = (array) (
                data_get($transactionResult, 'data')
                ?: data_get($transactionResult, 'response.data')
                ?: []
            );

            if (empty($provisioning)) {
                $transactionResult = $this->joytelApi->getTransactionStatus($transactionId);
                $provisioning = (array) $transactionResult['data'];
            }

            $qrContent = trim((string) data_get($provisioning, 'qrcode', ''));

            if ($qrContent === '') {
                throw new RuntimeException('Joytel transaction completed without QR activation content.');
            }

            $qrPath = $this->storePlainQrCode($order, $item->id, $qrContent);
            $callbackData = (array) ($item->fresh()->raw_callback_data ?? []);
            $callbackData['joytel_transaction_status'] = $transactionResult;
            $callbackData['plain_qr_path'] = $qrPath;

            $item->forceFill([
                'sn_pin' => $this->firstProvisioningValue($provisioning, ['coupon'], $item->sn_pin),
                'cid' => $this->firstProvisioningValue($provisioning, ['cid'], $item->cid),
                'qrcode_type' => is_numeric(data_get($provisioning, 'qrcodeType'))
                    ? (int) data_get($provisioning, 'qrcodeType')
                    : ($item->qrcode_type ?: 1),
                'qrcode' => $qrContent ?: $item->qrcode,
                'pin1' => $this->firstProvisioningValue($provisioning, ['pin1', 'pin_1'], $item->pin1),
                'pin2' => $this->firstProvisioningValue($provisioning, ['pin2', 'pin_2'], $item->pin2),
                'puk1' => $this->firstProvisioningValue($provisioning, ['puk1', 'puk_1'], $item->puk1),
                'puk2' => $this->firstProvisioningValue($provisioning, ['puk2', 'puk_2'], $item->puk2),
                'sale_plan_name' => $this->firstProvisioningValue($provisioning, ['salePlanName', 'sale_plan_name'], $item->sale_plan_name),
                'sale_plan_days' => $this->firstProvisioningValue($provisioning, ['salePlanDays', 'sale_plan_days'], $item->sale_plan_days),
                'raw_callback_data' => $callbackData,
            ])->save();
        }

        $isReady = $order->items()->exists()
            && !$order->items()->get()->contains(fn($item) => !$item->sn_code || !$item->sn_pin || !$item->qrcode);

        $order->forceFill([
            'our_status' => $isReady
                ? JoytelOrder::OUR_STATUS_COMPLETED
                : JoytelOrder::OUR_STATUS_API_PROCESSING,
            'is_send_email' => $isReady,
        ])->save();

        return $order->fresh(['customer', 'items']);
    }

    private function storeJoytelOrderItems(JoytelOrder $order, array $createResult, array $queryResult): void
    {
        $snRows = collect((array) ($queryResult['item_list'] ?? []))
            ->flatMap(function ($item) {
                $productCode = data_get($item, 'productCode');
                $snList = collect((array) data_get($item, 'snList', []));

                if ($snList->isEmpty() && (data_get($item, 'snCode') || data_get($item, 'rspOrderId'))) {
                    return [[
                        'productCode' => $productCode,
                        'snCode' => data_get($item, 'snCode'),
                        'status' => data_get($item, 'status'),
                        'statusDesc' => data_get($item, 'statusDesc'),
                        'productExpireDate' => data_get($item, 'productExpireDate'),
                        'rspOrderId' => data_get($item, 'rspOrderId'),
                        'rspTid' => data_get($item, 'rspTid'),
                    ]];
                }

                return $snList
                    ->map(function ($sn) use ($productCode) {
                        $sn['productCode'] = data_get($sn, 'productCode') ?: $productCode;

                        return $sn;
                    });
            })
            ->values();

        if ($snRows->isEmpty()) {
            $order->items()->each(function ($item) use ($createResult, $queryResult) {
                $callbackData = (array) ($item->raw_callback_data ?? []);
                $callbackData['joytel_create_order'] = [
                    'orderTid' => $createResult['order_tid'],
                    'orderCode' => $createResult['order_code'],
                    'response' => $createResult['response'],
                ];
                $callbackData['joytel_query_order'] = [
                    'orderTid' => $queryResult['order_tid'],
                    'orderCode' => $queryResult['order_code'],
                    'response' => $queryResult['response'],
                ];

                $item->raw_callback_data = $callbackData;
                $item->save();
            });

            return;
        }

        foreach ($snRows as $index => $sn) {
            $item = $order->items()->skip($index)->first() ?: $order->items()->create([
                'product_code' => data_get($sn, 'productCode') ?: $order->items()->value('product_code'),
            ]);
            $callbackData = (array) ($item->raw_callback_data ?? []);
            $callbackData['joytel_create_order'] = [
                'orderTid' => $createResult['order_tid'],
                'orderCode' => $createResult['order_code'],
                'response' => $createResult['response'],
            ];
            $callbackData['joytel_query_order'] = [
                'orderTid' => $queryResult['order_tid'],
                'orderCode' => $queryResult['order_code'],
                'response' => $queryResult['response'],
                'sn' => $sn,
            ];

            $item->forceFill([
                'product_code' => data_get($sn, 'productCode') ?: $item->product_code,
                'sn_code' => data_get($sn, 'snCode') ?: data_get($sn, 'sn_code') ?: $item->sn_code,
                'sn_pin' => data_get($sn, 'snPin') ?: data_get($sn, 'sn_pin') ?: $item->sn_pin,
                'product_expire_date' => data_get($sn, 'productExpireDate') ?: $item->product_expire_date,
                'esim_status' => is_numeric(data_get($sn, 'status')) ? (int) data_get($sn, 'status') : $item->esim_status,
                'raw_callback_data' => $callbackData,
            ])->save();
        }
    }

    private function storePlainQrCode(JoytelOrder $order, int $itemId, string $content): string
    {
        $qrCode = QrCode::create($content)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->setSize(360)
            ->setMargin(16);
        $png = (new PngWriter())->write($qrCode)->getString();
        $path = 'joytel-qrcodes/' . $order->id . '-' . $itemId . '.png';

        Storage::disk('public')->put($path, $png);

        return $path;
    }

    private function markJoytelSubmitFailed(JoytelOrder $order, \Throwable $e): JoytelOrder
    {
        $rawResponse = (array) ($order->raw_response ?? []);
        $rawResponse['joytel_create_order_error'] = [
            'message' => $e->getMessage(),
            'failed_at' => now()->toDateTimeString(),
        ];

        $order->forceFill([
            'our_status' => JoytelOrder::OUR_STATUS_API_FAILED,
            'raw_response' => $rawResponse,
        ])->save();

        Log::error('JOYTEL_CREATE_ORDER_FAILED', [
            'joytel_order_id' => $order->id,
            'message' => $e->getMessage(),
        ]);

        return $order->fresh(['customer', 'items']);
    }

    private function hasIncompleteProvisioningDetails($item): bool
    {
        return empty($item->sn_code)
            || empty($item->sn_pin)
            || empty($item->cid)
            || empty($item->qrcode)
            || $item->pin1 === null
            || $item->pin2 === null
            || $item->puk1 === null
            || $item->puk2 === null;
    }

    private function firstProvisioningValue(array $provisioning, array $keys, mixed $fallback = null): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($provisioning, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function isRechargeOrder(JoytelOrder $order): bool
    {
        return strtolower((string) $order->order_type) === 'recharge'
            || strtolower((string) $order->service_type) === 'physical'
            || (bool) $order->renewal;
    }
}
