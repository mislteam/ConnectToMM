<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = [];

        if (($this->payload['database'] ?? true) !== false) {
            $channels[] = 'database';
        }

        if (!empty($this->payload['mail'])) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject((string) ($this->payload['mail_subject'] ?? $this->payload['title'] ?? 'Order update'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Customer') . ',')
            ->line((string) ($this->payload['message'] ?? 'Your order has been updated.'));

        if (!empty($this->payload['reference'])) {
            $mail->line('Order Reference: ' . $this->payload['reference']);
        }

        if (!empty($this->payload['joytel_order_num'])) {
            $mail->line('Joytel Reference: ' . $this->payload['joytel_order_num']);
        }

        foreach ((array) ($this->payload['joytel_items'] ?? []) as $index => $item) {
            $mail->line('eSIM ' . ($index + 1) . ': ' . ($item['sale_plan_name'] ?? $item['product_code'] ?? 'Joytel eSIM'));

            foreach ([
                'SN Code' => $item['sn_code'] ?? null,
                'CID' => $item['cid'] ?? null,
                'PIN 1' => $item['pin1'] ?? null,
                'PIN 2' => $item['pin2'] ?? null,
                'PUK 1' => $item['puk1'] ?? null,
                'PUK 2' => $item['puk2'] ?? null,
                'Activation Code' => $item['qrcode'] ?? null,
            ] as $label => $value) {
                if ($value !== null && $value !== '') {
                    $mail->line($label . ': ' . $value);
                }
            }

            $qrPath = $item['plain_qr_path'] ?? null;
            if ($qrPath && is_file(storage_path('app/' . $qrPath))) {
                $mail->attach(storage_path('app/' . $qrPath), [
                    'as' => 'joytel-esim-' . ($index + 1) . '-qr.png',
                    'mime' => 'image/png',
                ]);
            }
        }

        if (!empty($this->payload['url'])) {
            $mail->action((string) ($this->payload['mail_action_text'] ?? 'View Order'), (string) $this->payload['url']);
        }

        return $mail->line('Thank you for choosing Connect To Myanmar.');
    }
}
