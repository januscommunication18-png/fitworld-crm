<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OneOnOneBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $hostName = $this->booking->bookingProfile?->display_name ?? 'Your Host';

        return new Envelope(
            subject: "Meeting Confirmed with {$hostName}",
        );
    }

    public function content(): Content
    {
        $profile = $this->booking->bookingProfile;
        $host = $this->booking->host;
        $subdomain = $host->subdomain;
        $domain = config('app.booking_domain', 'fitcrm.biz');

        return new Content(
            markdown: 'emails.one-on-one.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'profile' => $profile,
                'hostName' => $profile?->display_name ?? 'Your Host',
                'meetingDate' => $this->booking->start_time->format('l, F j, Y'),
                'meetingTime' => $this->booking->start_time->format('g:i A') . ' - ' . $this->booking->end_time->format('g:i A'),
                'meetingType' => $this->booking->meeting_type_label,
                'duration' => $this->booking->formatted_duration,
                'studioName' => $host?->studio_name ?? 'Studio',
                'manageUrl' => "https://{$subdomain}.{$domain}/meeting/manage/{$this->booking->manage_token}",
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
