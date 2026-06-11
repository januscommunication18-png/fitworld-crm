<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends a client their Client ID for the studio's branded mobile app,
 * with sign-in instructions. Triggered manually from the client profile.
 */
class ClientAppInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public string $clientCode,
    ) {}

    public function envelope(): Envelope
    {
        $host = $this->client->host;
        $studioName = $host?->studio_name ?? config('app.name');

        return new Envelope(
            from: $host?->email
                ? new \Illuminate\Mail\Mailables\Address($host->email, $studioName)
                : null,
            subject: "Your {$studioName} App Client ID",
        );
    }

    public function content(): Content
    {
        $host = $this->client->host;

        return new Content(
            view: 'emails.client-app-invite',
            with: [
                'client' => $this->client,
                'clientCode' => $this->clientCode,
                'studioName' => $host?->studio_name ?? config('app.name'),
                'appName' => $host?->getClientAppSetting('app_display_name')
                    ?: ($host?->studio_name ?? 'our app'),
            ],
        );
    }
}
