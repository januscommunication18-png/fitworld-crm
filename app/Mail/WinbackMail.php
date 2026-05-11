<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
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
    use Queueable, SerializesModels, UsesCustomTemplate;

    public function __construct(
        public Client $client,
        public Host $host
    ) {}

    public function build()
    {
        $bookingDomain = config('app.booking_domain', 'fitcrm.biz');

        $variables = [
            'customer_name' => $this->client->full_name,
            'last_visit_date' => $this->client->last_visit_at?->format('F j, Y') ?? 'a while ago',
            'studio_name' => $this->host->studio_name ?? 'Our Studio',
            'booking_url' => $this->host->subdomain
                ? "https://{$this->host->subdomain}.{$bookingDomain}"
                : url('/'),
        ];

        // Try custom template first
        if ($this->buildFromCustomTemplate('winback_campaign', $this->host, $variables)) {
            return $this;
        }

        // Fall back to default blade template
        return $this->subject("We miss you at {$variables['studio_name']}!")
            ->markdown('emails.winback', [
                'client' => $this->client,
                'studioName' => $variables['studio_name'],
                'studioEmail' => $this->host->contact_email ?? null,
                'bookingUrl' => $variables['booking_url'],
                'lastVisit' => $this->client->last_visit_at?->format('F j, Y'),
            ]);
    }
}
