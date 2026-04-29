<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $code,
        public string $expiresAt,
        public string $purpose = 'email_verification',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->purpose) {
            'login' => 'Your Connect To Myanmar login verification code',
            'reset_password' => 'Your Connect To Myanmar password reset code',
            default => 'Your Connect To Myanmar Registration code',
        };

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.customer-verification-code',
            text: 'emails.customer-verification-code-text',
            with: [
                'customer' => $this->customer,
                'code' => $this->code,
                'expiresAt' => $this->expiresAt,
                'purpose' => $this->purpose,
            ],
        );
    }
}
