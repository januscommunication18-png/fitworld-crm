<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OneOnOneBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $hostName = $this->booking->bookingProfile?->display_name ?? 'Your Host';

        return new Envelope(
            subject: "Meeting Cancelled - {$hostName}",
        );
    }

    public function content(): Content
    {
        $profile = $this->booking->bookingProfile;
        $host = $this->booking->host;
        $instructor = $profile?->instructor;

        return new Content(
            markdown: 'emails.one-on-one.booking-cancelled',
            with: [
                'booking' => $this->booking,
                'profile' => $profile,
                'hostName' => $profile?->display_name ?? 'Your Host',
                'meetingDate' => $this->booking->start_time->format('l, F j, Y'),
                'meetingTime' => $this->booking->start_time->format('g:i A'),
                'cancelledBy' => $this->booking->cancelled_by === 'guest' ? 'you' : 'the host',
                'studioName' => $host?->studio_name ?? 'Studio',
                'rebookUrl' => $profile?->getPublicUrl(),
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
