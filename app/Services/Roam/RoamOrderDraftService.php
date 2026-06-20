<?php

namespace App\Services\Roam;

use App\Models\Customer;
use App\Models\RoamOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoamOrderDraftService
{
    /**
     * Create local Roam orders (one per cart item) BEFORE payment, without calling Roam API.
     *
     * Because `roam_orders.roam_order_num` is required + unique, we generate a temporary order number
     * (TMP-...) and later replace it with the real upstream order number after payment success.
     *
     * @return array{outer_order_id:string, orders:\Illuminate\Support\Collection<int,RoamOrder>, total_amount:float}
     */
    public function createDraftOrdersFromCart(Customer $customer, array $cart, array $iccidNumbersByIndex = []): array
    {
        $cartItems = $this->normalizeCartItems($cart);
        if (empty($cartItems)) {
            throw new RuntimeException('Cart is empty.');
        }

        $outerOrderId = $this->generateOuterOrderId();
        $totalAmount = 0.0;

        $orders = DB::transaction(function () use ($customer, $cartItems, $iccidNumbersByIndex, $outerOrderId, &$totalAmount) {
            $created = collect();

            foreach ($cartItems as $index => $item) {
                $skuId = $item['sku_id'] ?? $item['sku'] ?? null;
                $apiCode = $item['api_code'] ?? null;
                if (is_string($apiCode)) {
                    $apiCode = trim($apiCode);
                }
                if (!$skuId || !$apiCode) {
                    $keys = implode(', ', array_keys($item));
                    $skuIdPreview = is_scalar($skuId) ? (string) $skuId : gettype($skuId);
                    $apiCodePreview = is_scalar($apiCode) ? (string) $apiCode : gettype($apiCode);
                    throw new RuntimeException("Cart item at index {$index} is missing sku_id or api_code. sku_id={$skuIdPreview}, api_code={$apiCodePreview}. Keys: {$keys}");
                }

                $quantity = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
                $unitPrice = (float) ($item['ori_price'] ?? $item['unit_price'] ?? 0);
                if ($unitPrice <= 0) {
                    $submittedTotalPrice = (float) ($item['total_price'] ?? $item['price'] ?? 0);
                    $unitPrice = $quantity > 0 ? round($submittedTotalPrice / $quantity, 2) : $submittedTotalPrice;
                }
                $unitPrice = (int) round($unitPrice);
                $totalPrice = $unitPrice * $quantity;

                $daypassDays = $this->coerceInt($item['service_day'] ?? ($item['daypass_days'] ?? null));

                $iccids = (array) ($iccidNumbersByIndex[$index] ?? []);
                $iccids = array_values(array_filter(array_map('trim', $iccids), static fn($v) => $v !== ''));
                $orderType = (string) ($item['order_type'] ?? 'new');
                $sourceIccid = $item['iccid_no'] ?? ($item['iccid'] ?? null);
                if (($sourceIccid === null || $sourceIccid === '') && $orderType === 'recharge' && !empty($iccids)) {
                    $sourceIccid = $iccids[0];
                }

                $tmpOrderNum = $this->makeTempRoamOrderNum($outerOrderId, (int) $index);

                $order = RoamOrder::create([
                    'customer_id' => $customer->id,
                    'roam_order_num' => $tmpOrderNum,
                    'outer_order_id' => $outerOrderId,
                    'sku_id' => (string) $skuId,
                    'price_id' => $item['price_id'] ?? null,
                    'api_code' => (string) $apiCode,
                    'service_type' => $item['service_type'] ?? 'esim',
                    'order_type' => $orderType,
                    'source_iccid' => $sourceIccid,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'daypass_days' => $daypassDays,
                    'our_status' => RoamOrder::OUR_STATUS_PENDING_PAYMENT,
                    'roam_status' => null,
                    'renewal' => false,
                    'remark' => $this->buildRemark($item),
                    'is_send_email' => false,
                    'raw_response' => [
                        'draft' => true,
                        'iccid_numbers' => $iccids,
                        'cart_item' => $item,
                    ],
                ]);

                $totalAmount += (float) $order->billable_total_price;
                $created->push($order);
            }

            return $created;
        });

        return [
            'outer_order_id' => $outerOrderId,
            'orders' => \App\Models\RoamOrder::query()
                ->with(['customer', 'items'])
                ->whereIn('id', $orders->pluck('id')->all())
                ->orderBy('id')
                ->get(),
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Keeps the same outer_order_id format you already use elsewhere.
     */
    public function generateOuterOrderId(): string
    {
        // R-YYYYMMDD-HH:MM:SS-000001
        $datePart = now()->format('Ymd');
        $timePart = now()->format('H:i:s');
        $prefix = "R-{$datePart}-{$timePart}-";

        $latest = RoamOrder::query()
            ->where('outer_order_id', 'like', $prefix . '%')
            ->orderByDesc('outer_order_id')
            ->value('outer_order_id');

        $sequence = 1;
        if (is_string($latest) && preg_match('/^R-\\d{8}-\\d{2}:\\d{2}:\\d{2}-(\\d{6})$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function normalizeCartItems(array $cart): array
    {
        if (array_key_exists('sku_id', $cart) || array_key_exists('sku', $cart)) {
            return [$cart];
        }

        return array_values(array_filter($cart, static fn($item) => is_array($item)));
    }

    private function makeTempRoamOrderNum(string $outerOrderId, int $index): string
    {
        // Must be <= 100 chars, unique, and clearly temporary.
        $hash = substr(sha1($outerOrderId . '|' . $index), 0, 10);
        return "TMP-{$hash}-{$index}";
    }

    private function buildRemark(array $item): ?string
    {
        $countryName = $item['country_name'] ?? null;
        $serviceData = $item['service_data'] ?? null;

        $remarkParts = array_values(array_filter([
            $countryName ? "Country: {$countryName}" : null,
            $serviceData ? "Plan: {$serviceData}" : null,
        ]));

        return !empty($remarkParts) ? implode(' | ', $remarkParts) : null;
    }

    private function coerceInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $digits = preg_replace('/[^0-9]/', '', $value);
            return $digits === '' ? null : (int) $digits;
        }

        return null;
    }
}
