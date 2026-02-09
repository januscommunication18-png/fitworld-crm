<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $password;
    public string $firstName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $password, string $firstName)
    {
        $this->password = $password;
        $this->firstName = $firstName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your FitCRM Admin Password Has Been Reset',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-password-reset',
            with: [
                'password' => $this->password,
                'firstName' => $this->firstName,
                'loginUrl' => route('backoffice.security'),
            ],
        );
    }
}
