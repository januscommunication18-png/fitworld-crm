<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesCustomTemplate;

    public function __construct(
        public Client $client
    ) {}

    public function build()
    {
        $host = $this->client->host;
        $bookingDomain = config('app.booking_domain', 'fitcrm.biz');

        $variables = [
            'customer_name' => $this->client->full_name,
            'studio_name' => $host?->studio_name ?? 'Our Studio',
            'studio_email' => $host?->studio_email ?? $host?->contact_email ?? '',
            'studio_phone' => $host?->phone ?? $host?->contact_phone ?? '',
            'booking_url' => $host?->subdomain
                ? "https://{$host->subdomain}.{$bookingDomain}"
                : url('/'),
        ];

        // Try custom template first
        if ($host && $this->buildFromCustomTemplate('welcome_email', $host, $variables)) {
            return $this;
        }

        // Fall back to default blade template
        return $this->subject("Welcome to {$variables['studio_name']}!")
            ->markdown('emails.welcome', [
                'client' => $this->client,
                'studioName' => $variables['studio_name'],
                'studioEmail' => $variables['studio_email'],
                'studioPhone' => $variables['studio_phone'],
                'bookingUrl' => $variables['booking_url'],
            ]);
    }
}
