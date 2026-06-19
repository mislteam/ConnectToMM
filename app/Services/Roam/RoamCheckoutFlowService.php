<?php

namespace App\Services\Roam;

use App\Models\Customer;
use App\Models\RoamOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RoamCheckoutFlowService
{
    public function __construct(
        private readonly RoamOrderService $roamOrderService,
        private readonly OrderStateMachineService $stateMachine,
    ) {}

    /**
     * Creates one or more Roam orders (one per cart item) and returns the shared outer_order_id.
     *
     * Note: This keeps existing DB schema and creates upstream orders immediately, then marks them
     * as Pending Payment locally so payment can happen next.
     *
     * @return array{outer_order_id:string, orders:\Illuminate\Support\Collection<int,RoamOrder>, total_amount:float}
     */
    public function placeOrdersFromCart(Customer $customer, array $cart, array $iccidNumbersByIndex = []): array
    {
        $cartItems = $this->normalizeCartItems($cart);
        if (empty($cartItems)) {
            throw new RuntimeException('Cart is empty.');
        }

        $outerOrderId = $this->roamOrderService->generateOuterOrderId();
        $totalAmount = 0.0;

        $orders = DB::transaction(function () use ($cartItems, $customer, $iccidNumbersByIndex, $outerOrderId, &$totalAmount) {
            $created = collect();

            foreach ($cartItems as $index => $item) {
                $payload = $this->buildOrderPayload($customer, $item, $outerOrderId, (array) ($iccidNumbersByIndex[$index] ?? []));

                try {
                    $order = $this->roamOrderService->placeOrder($payload, $customer);
                } catch (\Throwable $e) {
                    Log::warning('Roam checkout placeOrder failed', [
                        'outer_order_id' => $outerOrderId,
                        'customer_id' => $customer->id,
                        'cart_index' => $index,
                        'payload' => Arr::except($payload, ['api_request']),
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                // Ensure we start local lifecycle at Pending Payment.
                if ((int) $order->our_status !== RoamOrder::OUR_STATUS_PENDING_PAYMENT) {
                    // Allow both Order Start -> Pending Payment, or keep as-is if already moved.
                    if ((int) $order->our_status === RoamOrder::OUR_STATUS_ORDER_START) {
                        $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_PENDING_PAYMENT);
                    } else {
                        $order->our_status = RoamOrder::OUR_STATUS_PENDING_PAYMENT;
                        $order->save();
                    }
                }

                $totalAmount += (float) $order->billable_total_price;
                $created->push($order);
            }

            return $created;
        });

        return [
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total_amount' => $totalAmount,
        ];
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

    /**
     * @param array<string,mixed> $item
     * @param array<int,string> $iccids
     * @return array<string,mixed>
     */
    private function buildOrderPayload(Customer $customer, array $item, string $outerOrderId, array $iccids): array
    {
        $skuId = $item['sku_id'] ?? $item['sku'] ?? null;
        if (!$skuId) {
            throw new RuntimeException('Cart item sku_id is missing.');
        }

        $quantity = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
        $unitPrice = (float) ($item['ori_price'] ?? $item['unit_price'] ?? 0);
        if ($unitPrice <= 0) {
            $submittedTotalPrice = (float) ($item['total_price'] ?? $item['price'] ?? 0);
            $unitPrice = $quantity > 0 ? round($submittedTotalPrice / $quantity, 2) : $submittedTotalPrice;
        }
        $unitPrice = (int) round($unitPrice);
        $totalPrice = $unitPrice * $quantity;

        $serviceType = (string) ($item['service_type'] ?? 'esim');
        $orderType = (string) ($item['order_type'] ?? 'new');

        $daypassDays = $this->coerceInt($item['service_day'] ?? ($item['daypass_days'] ?? null));
        $serviceData = $item['service_data'] ?? null;
        $countryName = $item['country_name'] ?? null;

        $remarkParts = array_values(array_filter([
            $countryName ? "Country: {$countryName}" : null,
            $serviceData ? "Plan: {$serviceData}" : null,
        ]));

        $payload = [
            'customer_id' => $customer->id,
            'outer_order_id' => $outerOrderId,
            'sku_id' => (string) $skuId,
            'price_id' => $item['price_id'] ?? null,
            'api_code' => $item['api_code'] ?? null,
            'service_type' => $serviceType,
            'order_type' => $orderType,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'daypass_days' => $daypassDays,
            'remark' => !empty($remarkParts) ? implode(' | ', $remarkParts) : null,
            'our_status' => RoamOrder::OUR_STATUS_PENDING_PAYMENT,
            'is_send_email' => true,
            'customer_email' => $customer->email,
        ];

        $iccids = array_values(array_filter(array_map('trim', $iccids), static fn($v) => $v !== ''));
        if (!empty($iccids)) {
            $payload['iccids'] = implode(',', $iccids);
        }

        if (!empty($item['iccid_no'])) {
            $payload['iccid'] = (string) $item['iccid_no'];
            $payload['source_iccid'] = (string) $item['iccid_no'];
        }

        return $payload;
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
