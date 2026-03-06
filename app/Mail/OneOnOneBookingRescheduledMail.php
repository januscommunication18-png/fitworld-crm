<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingRescheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OneOnOneBooking $newBooking,
        public OneOnOneBooking $oldBooking
    ) {}

    public function envelope(): Envelope
    {
        $hostName = $this->newBooking->bookingProfile?->display_name ?? 'Your Host';

        return new Envelope(
            subject: "Meeting Rescheduled - {$hostName}",
        );
    }

    public function content(): Content
    {
        $profile = $this->newBooking->bookingProfile;
        $host = $this->newBooking->host;
        $subdomain = $host->subdomain;
        $domain = config('app.booking_domain', 'fitcrm.biz');

        return new Content(
            markdown: 'emails.one-on-one.booking-rescheduled',
            with: [
                'newBooking' => $this->newBooking,
                'oldBooking' => $this->oldBooking,
                'profile' => $profile,
                'hostName' => $profile?->display_name ?? 'Your Host',
                'oldDate' => $this->oldBooking->start_time->format('l, F j, Y'),
                'oldTime' => $this->oldBooking->start_time->format('g:i A'),
                'newDate' => $this->newBooking->start_time->format('l, F j, Y'),
                'newTime' => $this->newBooking->start_time->format('g:i A') . ' - ' . $this->newBooking->end_time->format('g:i A'),
                'studioName' => $host?->studio_name ?? 'Studio',
                'manageUrl' => "https://{$subdomain}.{$domain}/meeting/manage/{$this->newBooking->manage_token}",
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
