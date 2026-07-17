<?php

namespace App\Services;

use App\Models\JoytelOrder;
use App\Models\RoamOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrderNotificationService
{
    public function orderCreated(Model $order): void
    {
        if (!$this->canNotify($order)) {
            return;
        }

        $reference = $this->reference($order);
        if ($reference === '') {
            return;
        }

        $provider = $this->provider($order);
        $customerName = $order->customer?->name ?? 'Customer';

        $this->notifyAdmins([
            'dedupe_key' => "{$provider}:admin:order-created:{$reference}",
            'title' => Str::headline($provider) . ' order placed',
            'message' => "{$customerName} placed order {$reference}.",
            'icon' => 'shopping-cart',
            'tone' => 'warning',
            'provider' => $provider,
            'reference' => $reference,
            'url' => $this->adminUrl($provider, $reference),
        ]);
    }

    public function paymentSlipUploaded(Model $order): void
    {
        if (!$this->canNotify($order)) {
            return;
        }

        $reference = $this->reference($order);
        if ($reference === '') {
            return;
        }

        $provider = $this->provider($order);
        $customerName = $order->customer?->name ?? 'Customer';

        $this->notifyAdmins([
            'dedupe_key' => "{$provider}:admin:slip-uploaded:{$reference}",
            'title' => Str::headline($provider) . ' payment slip uploaded',
            'message' => "{$customerName} uploaded a payment slip for order {$reference}.",
            'icon' => 'receipt',
            'tone' => 'warning',
            'provider' => $provider,
            'reference' => $reference,
            'url' => $this->adminUrl($provider, $reference),
        ]);
    }

    public function paymentApproved(Model $order): void
    {
        if (!$this->canNotify($order)) {
            return;
        }

        $reference = $this->reference($order);
        if ($reference === '') {
            return;
        }

        $provider = $this->provider($order);
        $title = 'Payment approved';
        $message = "Your order {$reference} payment has been approved.";
        $shouldSendMail = false;
        $joytelOrderNum = null;
        $dedupeReference = $reference;

        if ($order instanceof JoytelOrder) {
            $order->loadMissing('items');
            $shouldSendMail = true;
            $joytelOrderNum = (string) ($order->joytel_order_num ?? '');
            $isTempJoytelOrder = str_starts_with($joytelOrderNum, 'JTMP-');
            $serviceLabel = Str::lower((string) $order->service_type) === 'physical'
                ? 'physical SIM recharge'
                : 'eSIM';

            $title = 'Joytel order completed';
            $message = "Your Joytel {$serviceLabel} order {$reference} has been approved and submitted successfully.";

            if ($joytelOrderNum !== '' && !$isTempJoytelOrder) {
                $message .= " Joytel reference: {$joytelOrderNum}.";
                $dedupeReference = $reference . ':' . $joytelOrderNum;
            }
        }

        $this->notifyCustomer($order, [
            'dedupe_key' => "{$provider}:customer:payment-approved:{$dedupeReference}",
            'title' => $title,
            'message' => $message,
            'mail' => $shouldSendMail,
            'mail_subject' => $order instanceof JoytelOrder && Str::lower((string) $order->service_type) === 'esim'
                ? '[Order Success Notification] eSIM Redemption'
                : $title,
            'mail_action_text' => 'View Order',
            'icon' => 'check-circle',
            'tone' => 'success',
            'provider' => $provider,
            'reference' => $reference,
            'joytel_order_num' => $joytelOrderNum,
            'customer_email' => $order->customer?->email,
            'order_created_at' => $order->created_at?->format('Y/m/d H:i:s'),
            'service_type' => $order instanceof JoytelOrder ? (string) $order->service_type : null,
            'joytel_items' => $order instanceof JoytelOrder
                ? $order->items->map(function ($item) {
                    return [
                        'product_code' => $item->product_code,
                        'sn_code' => $item->sn_code,
                        'cid' => $item->cid,
                        'qrcode' => $item->qrcode,
                        'pin1' => $item->pin1,
                        'pin2' => $item->pin2,
                        'puk1' => $item->puk1,
                        'puk2' => $item->puk2,
                        'sale_plan_name' => $item->sale_plan_name,
                        'sale_plan_days' => $item->sale_plan_days,
                        'plain_qr_path' => data_get($item->raw_callback_data, 'plain_qr_path'),
                    ];
                })->all()
                : [],
            'url' => $this->customerUrl($provider, $reference),
        ]);
    }

    public function adminCancelled(Model $order): void
    {
        if (!$this->canNotify($order)) {
            return;
        }

        $reference = $this->reference($order);
        if ($reference === '') {
            return;
        }

        $provider = $this->provider($order);

        $this->notifyCustomer($order, [
            'dedupe_key' => "{$provider}:customer:admin-cancelled:{$reference}",
            'title' => 'Order cancelled',
            'message' => "Your order {$reference} was cancelled by admin.",
            'icon' => 'x-circle',
            'tone' => 'danger',
            'provider' => $provider,
            'reference' => $reference,
            'url' => $this->customerUrl($provider, $reference),
        ]);
    }

    public function orderStatusChanged(Model $order, int $fromStatus, int $toStatus): void
    {
        if (!$this->canNotify($order)) {
            return;
        }

        $reference = $this->reference($order);
        if ($reference === '') {
            return;
        }

        $provider = $this->provider($order);
        $statusKey = $this->statusKey($order, $toStatus);
        if ($statusKey !== 'refunded') {
            return;
        }

        $statusLabel = Str::headline($statusKey);

        $this->notifyCustomer($order, [
            'dedupe_key' => "{$provider}:customer:{$statusKey}:{$reference}",
            'title' => "Order {$statusLabel}",
            'message' => "Your order {$reference} is {$statusLabel}.",
            'icon' => 'rotate-ccw',
            'tone' => 'info',
            'provider' => $provider,
            'reference' => $reference,
            'status' => $statusKey,
            'url' => $this->customerUrl($provider, $reference),
        ]);
    }

    private function canNotify(Model $order): bool
    {
        return Schema::hasTable('notifications');
    }

    private function notifyAdmins(array $payload): void
    {
        $admins = User::query()->where('status', 0)->get();

        foreach ($admins as $admin) {
            $this->notifyOnce($admin, $payload);
        }
    }

    private function notifyCustomer(Model $order, array $payload): void
    {
        $customer = $order->customer()->first();
        if ($customer) {
            $this->notifyOnce($customer, $payload);
        }
    }

    private function notifyOnce(object $notifiable, array $payload): void
    {
        $exists = $notifiable->notifications()
            ->where('type', OrderNotification::class)
            ->where('data->dedupe_key', $payload['dedupe_key'])
            ->exists();

        if (!$exists) {
            try {
                Notification::send($notifiable, new OrderNotification(array_merge($payload, [
                    'mail' => false,
                    'database' => true,
                ])));
            } catch (\Throwable $e) {
                Log::warning('ORDER_NOTIFICATION_SEND_FAILED', [
                    'dedupe_key' => $payload['dedupe_key'] ?? null,
                    'channel' => 'database',
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->id ?? null,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($payload['mail'])) {
            try {
                Notification::send($notifiable, new OrderNotification(array_merge($payload, [
                    'database' => false,
                ])));
            } catch (\Throwable $e) {
                Log::warning('ORDER_NOTIFICATION_SEND_FAILED', [
                    'dedupe_key' => $payload['dedupe_key'] ?? null,
                    'channel' => 'mail',
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->id ?? null,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function provider(Model $order): string
    {
        return $order instanceof JoytelOrder ? 'joytel' : 'roam';
    }

    private function reference(Model $order): string
    {
        if ($order instanceof JoytelOrder) {
            return (string) ($order->outer_order_id ?: $order->joytel_order_num);
        }

        return (string) ($order->outer_order_id ?: $order->roam_order_num);
    }

    private function statusKey(Model $order, int $status): string
    {
        if ($status === RoamOrder::OUR_STATUS_PENDING_PAYMENT) {
            return 'pending_payment';
        }

        if ($status === RoamOrder::OUR_STATUS_COMPLETED) {
            return 'completed';
        }

        if ($status === RoamOrder::OUR_STATUS_REFUNDED) {
            return 'refunded';
        }

        return 'processing';
    }

    private function paymentLabel(string $paymentMethod): string
    {
        return match (Str::lower($paymentMethod)) {
            'uab_pay', 'uab pay', 'uab-payment' => 'UAB Pay',
            'direct_bank_transfer', 'bank_transfer', 'direct bank transfer' => 'Direct Bank Transfer',
            default => $paymentMethod !== '' ? Str::headline($paymentMethod) : 'Direct Bank Transfer',
        };
    }

    private function adminUrl(string $provider, string $reference): string
    {
        return $provider === 'joytel'
            ? route('order.joytel.show', ['reference' => $reference])
            : route('order.show', ['reference' => $reference]);
    }

    private function customerUrl(string $provider, string $reference): string
    {
        return $provider === 'joytel'
            ? route('customer.order.detail', ['outerOrderId' => $reference])
            : route('customer.roam.order.detail', ['outerOrderId' => $reference]);
    }
}
