<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinbackMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Client $client,
        public Host $host
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';

        return new Envelope(
            subject: "We miss you at {$studioName}!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.winback',
            with: [
                'client' => $this->client,
                'studioName' => $this->host->studio_name ?? 'Our Studio',
                'studioEmail' => $this->host->contact_email ?? null,
                'bookingUrl' => $this->host->subdomain
                    ? "https://{$this->host->subdomain}.fitnearyou.com/book"
                    : null,
                'lastVisit' => $this->client->last_visit_at?->format('F j, Y'),
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
