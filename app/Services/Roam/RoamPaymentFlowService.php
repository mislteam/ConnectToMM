<?php

namespace App\Services\Roam;

use App\Models\Customer;
use App\Models\RoamOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RoamPaymentFlowService
{
    public function __construct(
        private readonly OrderStateMachineService $stateMachine,
        private readonly RoamOrderService $roamOrderService,
    ) {}

    /**
     * @return Collection<int,RoamOrder>
     */
    public function markPaidAndFinalize(Customer $customer, string $outerOrderId): Collection
    {
        $orders = RoamOrder::query()
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->get();

        if ($orders->isEmpty()) {
            throw new RuntimeException('Orders not found for this payment reference.');
        }

        return $orders->map(function (RoamOrder $order) {
            // Pending Payment -> Paid
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_PAID);
            }

            // Paid -> On Hold -> API Processing (internal progression)
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_PAID) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_ON_HOLD);
            }
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_ON_HOLD) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_API_PROCESSING);
            }

            // Sync upstream; if upstream is normal/paid it will auto-advance our status to API_SUCCESS.
            try {
                $order = $this->roamOrderService->syncByOrderNum($order->roam_order_num);
            } catch (\Throwable $e) {
                Log::warning('Roam payment finalize sync failed', [
                    'roam_order_num' => $order->roam_order_num,
                    'error' => $e->getMessage(),
                ]);
            }

            if ((int) $order->our_status === RoamOrder::OUR_STATUS_API_SUCCESS) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_COMPLETED);
            }

            $order = $order->refresh()->load(['customer', 'items']);

            if ((int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED && !$order->is_send_email) {
                try {
                    $iccids = $order->items->pluck('iccid')->filter()->implode(',');
                    $this->roamOrderService->sendPdfEmail(
                        $order->roam_order_num,
                        (string) $order->customer?->email,
                        $iccids !== '' ? $iccids : null,
                        true,
                        'email'
                    );

                    $order->is_send_email = true;
                    $order->save();
                } catch (\Throwable $e) {
                    Log::warning('Roam payment finalize sendPdfEmail failed', [
                        'roam_order_num' => $order->roam_order_num,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $order;
        });
    }
}
