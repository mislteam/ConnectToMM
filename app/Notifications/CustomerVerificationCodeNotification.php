<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $code,
        protected string $expiresAt
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Connect To Myanmar verification code')
            ->greeting('Hello ' . ($notifiable->name ?? 'there'))
            ->line('Your email verification code is: ' . $this->code)
            ->line('This code will expire at ' . $this->expiresAt . '.')
            ->line('If you did not request this code, you can safely ignore this email.');
    }
}
