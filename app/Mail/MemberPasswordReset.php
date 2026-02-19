<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Host;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MemberPasswordReset extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Client $client,
        public string $token,
        public Host $host
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reset Your Password - {$this->host->studio_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = route('member.reset-password', [
            'subdomain' => $this->host->subdomain,
            'token' => $this->token,
        ]);

        return new Content(
            markdown: 'emails.member.password-reset',
            with: [
                'client' => $this->client,
                'host' => $this->host,
                'studioName' => $this->host->studio_name,
                'resetUrl' => $resetUrl,
                'expiryMinutes' => 60,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
