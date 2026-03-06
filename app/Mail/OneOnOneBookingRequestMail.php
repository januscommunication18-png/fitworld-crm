<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OneOnOneBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Booking Request from {$this->booking->guest_full_name} - Action Required",
        );
    }

    public function content(): Content
    {
        $profile = $this->booking->bookingProfile;
        $host = $this->booking->host;

        return new Content(
            markdown: 'emails.one-on-one.booking-request',
            with: [
                'booking' => $this->booking,
                'guestName' => $this->booking->guest_full_name,
                'guestEmail' => $this->booking->guest_email,
                'guestPhone' => $this->booking->guest_phone,
                'guestNotes' => $this->booking->guest_notes,
                'meetingDate' => $this->booking->start_time->format('l, F j, Y'),
                'meetingTime' => $this->booking->start_time->format('g:i A') . ' - ' . $this->booking->end_time->format('g:i A'),
                'meetingType' => $this->booking->meeting_type_label,
                'duration' => $this->booking->formatted_duration,
                'studioName' => $host?->studio_name ?? 'Studio',
                'dashboardUrl' => route('one-on-one.index'),
                'acceptUrl' => route('one-on-one.accept', $this->booking),
                'declineUrl' => route('one-on-one.decline', $this->booking),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
