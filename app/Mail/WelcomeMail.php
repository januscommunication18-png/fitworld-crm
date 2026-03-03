<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Client $client
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $studioName = $this->client->host?->studio_name ?? 'Our Studio';

        return new Envelope(
            subject: "Welcome to {$studioName}!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $host = $this->client->host;

        return new Content(
            markdown: 'emails.welcome',
            with: [
                'client' => $this->client,
                'studioName' => $host?->studio_name ?? 'Our Studio',
                'studioEmail' => $host?->contact_email ?? null,
                'studioPhone' => $host?->contact_phone ?? null,
                'bookingUrl' => $host?->subdomain
                    ? "https://{$host->subdomain}.fitnearyou.com/book"
                    : null,
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
