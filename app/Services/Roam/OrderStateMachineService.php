<?php

namespace App\Services\Roam;

use App\Models\RoamOrder;
use InvalidArgumentException;

class OrderStateMachineService
{
    public function allowedOurTransitions(int $fromStatus): array
    {
        return RoamOrder::OUR_STATUS_TRANSITIONS[$fromStatus] ?? [];
    }

    public function canTransitionOurStatus(int $fromStatus, int $toStatus): bool
    {
        return in_array($toStatus, $this->allowedOurTransitions($fromStatus), true);
    }

    public function guardOurStatusTransition(int $fromStatus, int $toStatus): void
    {
        if ($this->canTransitionOurStatus($fromStatus, $toStatus)) {
            return;
        }

        $fromLabel = RoamOrder::OUR_STATUS_LABELS[$fromStatus] ?? (string) $fromStatus;
        $toLabel = RoamOrder::OUR_STATUS_LABELS[$toStatus] ?? (string) $toStatus;

        throw new InvalidArgumentException("Invalid order status transition: {$fromLabel} -> {$toLabel}");
    }

    public function transitionRoamOrder(RoamOrder $order, int $toStatus, array $attributes = [], bool $save = true): RoamOrder
    {
        $this->guardOurStatusTransition((int) $order->our_status, $toStatus);

        $order->our_status = $toStatus;
        if (!empty($attributes)) {
            $order->fill($attributes);
        }

        if ($save) {
            $order->save();
        }

        return $order;
    }
}
