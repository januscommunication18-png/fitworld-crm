<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Host;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MemberActivationCode extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Client $client,
        public string $code,
        public Host $host
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Verification Code - {$this->host->studio_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $settings = $this->host->member_portal_settings ?? Host::defaultMemberPortalSettings();
        $expiryMinutes = $settings['activation_code_expiry_minutes'] ?? 10;

        return new Content(
            markdown: 'emails.member.activation-code',
            with: [
                'client' => $this->client,
                'code' => $this->code,
                'host' => $this->host,
                'studioName' => $this->host->studio_name,
                'expiryMinutes' => $expiryMinutes,
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
