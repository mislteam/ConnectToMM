<?php

namespace App\Services\Roam;

use App\Models\Customer;
use App\Models\RoamOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RoamProvisioningFlowService
{
    public function __construct(
        private readonly OrderStateMachineService $stateMachine,
        private readonly RoamOrderService $roamOrderService,
    ) {}

    /**
     * After payment success, call Roam API to create eSIM/physical SIM orders and save ICCID/QR/activation info.
     *
     * @return Collection<int,RoamOrder>
     */
    public function provisionAfterPayment(Customer $customer, string $outerOrderId): Collection
    {
        $orders = RoamOrder::query()
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            throw new RuntimeException('Orders not found for this payment reference.');
        }

        return $orders->map(function (RoamOrder $order) {
            // Pending Payment -> Paid -> On Hold -> Processing
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_PAID);
            }
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_PAID) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_ON_HOLD);
            }
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_ON_HOLD) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_API_PROCESSING);
            }

            try {
                $order = $this->roamOrderService->placeOrderForExistingDraft($order);
            } catch (\Throwable $e) {
                Log::warning('Roam provisioning failed', [
                    'outer_order_id' => $order->outer_order_id,
                    'roam_order_num' => $order->roam_order_num,
                    'error' => $e->getMessage(),
                ]);

                // Processing -> Failed
                if ((int) $order->our_status === RoamOrder::OUR_STATUS_API_PROCESSING) {
                    $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_API_FAILED);
                }

                return $order->refresh()->load(['customer', 'items']);
            }

            return $this->finalizeProvisionedOrder($order);
        });
    }

    /**
     * Retry only failed Roam API orders for a paid customer order group.
     *
     * @return Collection<int,RoamOrder>
     */
    public function retryFailedAfterPayment(Customer $customer, string $outerOrderId): Collection
    {
        $orders = RoamOrder::query()
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->where('our_status', RoamOrder::OUR_STATUS_API_FAILED)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            throw new RuntimeException('No failed orders were found for this payment reference.');
        }

        return $orders->map(function (RoamOrder $order) {
            if ((int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED) {
                $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_API_PROCESSING);
            }

            try {
                $order = $this->roamOrderService->placeOrderForExistingDraft($order);
            } catch (\Throwable $e) {
                Log::warning('Roam provisioning retry failed', [
                    'outer_order_id' => $order->outer_order_id,
                    'roam_order_num' => $order->roam_order_num,
                    'error' => $e->getMessage(),
                ]);

                if ((int) $order->our_status === RoamOrder::OUR_STATUS_API_PROCESSING) {
                    $order = $this->stateMachine->transitionRoamOrder($order, RoamOrder::OUR_STATUS_API_FAILED);
                }

                return $order->refresh()->load(['customer', 'items']);
            }

            return $this->finalizeProvisionedOrder($order);
        });
    }

    private function finalizeProvisionedOrder(RoamOrder $order): RoamOrder
    {
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
                Log::warning('Roam provisioning sendPdfEmail failed', [
                    'roam_order_num' => $order->roam_order_num,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $order;
    }
}
