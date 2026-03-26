<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FitNearYouVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $firstName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $firstName)
    {
        $this->code = $code;
        $this->firstName = $firstName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FitNearYou - API Secret Verification Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.fitnearyou-verification-code',
            with: [
                'code' => $this->code,
                'firstName' => $this->firstName,
            ],
        );
    }
}
