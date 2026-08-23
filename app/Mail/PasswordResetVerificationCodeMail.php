<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $verificationCode,
        public int $expiresInMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NU-Secure Password Reset Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-verification-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
