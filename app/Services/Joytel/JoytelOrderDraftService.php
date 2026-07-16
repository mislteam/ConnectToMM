<?php

namespace App\Services\Joytel;

use App\Models\Customer;
use App\Models\JoytelApi;
use App\Models\JoytelOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JoytelOrderDraftService
{
    public function createDraftOrdersFromCart(Customer $customer, array $cart, array $billingData = []): array
    {
        $cartItems = $this->normalizeCartItems($cart);
        if (empty($cartItems)) {
            throw new RuntimeException('Cart is empty.');
        }

        $outerOrderId = $this->generateOuterOrderId();
        $totalAmount = 0.0;
        $billingPhone = trim((string) ($billingData['phone'] ?? ''));
        if ($billingPhone === '') {
            $billingPhone = (string) ($customer->phone ?? '');
        }
        $paymentMethod = (string) ($billingData['payment_method'] ?? 'direct_bank_transfer');
        $sourceSnCodes = (array) ($billingData['source_sn_codes'] ?? []);

        $orders = DB::transaction(function () use ($customer, $cartItems, $outerOrderId, $billingPhone, $paymentMethod, $sourceSnCodes, &$totalAmount) {
            $created = collect();

            foreach ($cartItems as $index => $item) {
                $productCode = trim((string) ($item['product_code'] ?? ''));
                if ($productCode === '') {
                    throw new RuntimeException("Cart item at index {$index} is missing product_code.");
                }

                $quantity = max(1, (int) ($item['qty'] ?? 1));
                $totalPrice = (int) round((float) ($item['price'] ?? 0));
                $unitPrice = (float) ($item['ori_price'] ?? ($quantity > 0 ? round($totalPrice / $quantity, 2) : $totalPrice));
                $serviceType = strtolower((string) ($item['joytel_type'] ?? 'esim')) === 'physical' ? 'physical' : 'esim';
                $orderType = $serviceType === 'physical' || str_contains(strtolower((string) ($item['sim_type'] ?? '')), 'recharge')
                    ? 'recharge'
                    : 'new';
                $sourceSnCode = $this->resolveSourceSnCode($item, $sourceSnCodes, (int) $index);
                if ($this->requiresSourceSnCode($serviceType, $orderType) && $sourceSnCode === '') {
                    throw new RuntimeException("SN Code is required for physical SIM recharge at index {$index}.");
                }

                $days = $this->coerceInt($item['service_day'] ?? null);
                $joytelOrderNum = $this->makeTempJoytelOrderNum($outerOrderId, (int) $index);
                $requestPayload = [
                    'orderTid' => $outerOrderId,
                    'productCode' => $productCode,
                    'quantity' => $quantity,
                    'type' => $serviceType === 'esim' ? 3 : null,
                    'replyType' => 1,
                    'receiveName' => $customer->name,
                    'phone' => $billingPhone,
                    'email' => $customer->email,
                    'days' => $days,
                ];

                if ($sourceSnCode !== '') {
                    $requestPayload['snCode'] = $sourceSnCode;
                    $item['source_sn_code'] = $sourceSnCode;
                }

                $order = JoytelOrder::create([
                    'customer_id' => $customer->id,
                    'joytel_order_num' => $joytelOrderNum,
                    'outer_order_id' => $outerOrderId,
                    'product_name' => $item['product_name'] ?? null,
                    'service_type' => $serviceType,
                    'order_type' => $orderType,
                    'source_sn_code' => $sourceSnCode !== '' ? $sourceSnCode : null,
                    'quantity' => $quantity,
                    'unit_price' => (int) round($unitPrice),
                    'total_price' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'validity_days' => $days,
                    'our_status' => JoytelOrder::OUR_STATUS_PENDING_PAYMENT,
                    'renewal' => $orderType === 'recharge',
                    'remark' => $this->buildRemark($item),
                    'is_send_email' => false,
                    'raw_response' => [
                        'draft' => true,
                        'request_payload' => $requestPayload,
                        'cart_item' => $item,
                        'billing' => [
                            'receive_name' => $customer->name,
                            'phone' => $billingPhone,
                            'email' => $customer->email,
                        ],
                        'payment' => [
                            'method' => $paymentMethod,
                        ],
                    ],
                ]);

                $order->items()->create([
                    'product_code' => $productCode,
                    'sn_code' => $sourceSnCode !== '' ? $sourceSnCode : null,
                    'raw_callback_data' => [
                        'draft' => true,
                        'request_payload' => $requestPayload,
                    ],
                ]);

                $totalAmount += (float) $order->billable_total_price;
                $created->push($order);
            }

            return $created;
        });

        return [
            'outer_order_id' => $outerOrderId,
            'orders' => JoytelOrder::query()
                ->with(['customer', 'items'])
                ->whereIn('id', $orders->pluck('id')->all())
                ->orderBy('id')
                ->get(),
            'total_amount' => $totalAmount,
        ];
    }

    public function generateOuterOrderId(): string
    {
        $customerCode = (string) (JoytelApi::query()->value('customer_code') ?: 'JT');
        $prefix = $customerCode . now()->format('YmdHis');

        $latest = JoytelOrder::query()
            ->where('outer_order_id', 'like', $prefix . '%')
            ->orderByDesc('outer_order_id')
            ->value('outer_order_id');

        $sequence = 1;
        if (is_string($latest) && preg_match('/^' . preg_quote($prefix, '/') . '(\d{6})$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function normalizeCartItems(array $cart): array
    {
        if (array_key_exists('product_code', $cart) || array_key_exists('joytel', $cart)) {
            return [$cart];
        }

        return array_values(array_filter($cart, static fn($item) => is_array($item)));
    }

    private function makeTempJoytelOrderNum(string $outerOrderId, int $index): string
    {
        return 'JTMP-' . substr(sha1($outerOrderId . '|' . $index), 0, 12) . '-' . $index;
    }

    private function buildRemark(array $item): ?string
    {
        $parts = array_values(array_filter([
            $item['product_name'] ?? null,
            $item['service_data'] ?? null,
            isset($item['service_day']) ? $item['service_day'] . ' day(s)' : null,
        ]));

        return !empty($parts) ? implode(' | ', $parts) : null;
    }

    private function resolveSourceSnCode(array $item, array $sourceSnCodes, int $index): string
    {
        return trim((string) (
            $sourceSnCodes[$index]
            ?? $sourceSnCodes[(string) $index]
            ?? $item['source_sn_code']
            ?? ''
        ));
    }

    private function requiresSourceSnCode(string $serviceType, string $orderType): bool
    {
        return strtolower($serviceType) === 'physical' && strtolower($orderType) === 'recharge';
    }

    private function coerceInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
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
