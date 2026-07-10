<?php

namespace App\Payment\Providers\Uab\Services;

use App\Payment\Providers\Uab\Enums\TransactionStatus;
use App\Models\RoamOrder;
use App\Services\Roam\OrderStateMachineService;
use App\Services\Roam\RoamProvisioningFlowService;
use Illuminate\Support\Collection;

class PaymentLifecycleService
{
    public function __construct(
        private readonly RoamProvisioningFlowService $roamProvisioningFlowService,
        private readonly OrderStateMachineService $orderStateMachineService,
    ) {
    }

    public function syncForPaymentResult(string $outerOrderId, TransactionStatus $status): void
    {
        if ($outerOrderId === '') {
            return;
        }

        $orders = RoamOrder::query()
            ->with(['customer', 'items'])
            ->where('outer_order_id', $outerOrderId)
            ->whereIn('payment_method', ['uabpay', 'UAB Pay'])
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        if ($status === TransactionStatus::SUCCESS) {
            $this->provisionPendingOrdersAfterSuccessfulPayment($orders, $outerOrderId);

            return;
        }

        if (in_array($status, [
            TransactionStatus::FAILED,
            TransactionStatus::CANCELLED,
            TransactionStatus::EXPIRED,
        ], true)) {
            $this->cancelPendingOrders($orders);
        }
    }

    private function provisionPendingOrdersAfterSuccessfulPayment(Collection $orders, string $outerOrderId): void
    {
        if (!$orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return;
        }

        $customer = $orders->first()?->customer;
        if ($customer === null) {
            return;
        }

        $this->roamProvisioningFlowService->provisionAfterPayment($customer, $outerOrderId);
    }

    private function cancelPendingOrders(Collection $orders): void
    {
        $orders->each(function (RoamOrder $order): void {
            if ((int) $order->our_status !== RoamOrder::OUR_STATUS_PENDING_PAYMENT) {
                return;
            }

            $rawResponse = (array) ($order->raw_response ?? []);
            $payment = (array) data_get($rawResponse, 'payment', []);
            $payment['uab']['cancelled_at'] = now()->toDateTimeString();
            $rawResponse['payment'] = $payment;

            $this->orderStateMachineService->transitionRoamOrder($order, RoamOrder::OUR_STATUS_CANCELLED, [
                'raw_response' => $rawResponse,
            ]);
        });
    }
}
