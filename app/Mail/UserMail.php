<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
        public string $email,
        public string $temporaryPassword,
        public string $setupUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your NU-Secure account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-mail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
