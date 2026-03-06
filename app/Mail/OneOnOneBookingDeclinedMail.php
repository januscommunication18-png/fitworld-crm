<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingDeclinedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OneOnOneBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $instructor = $this->booking->bookingProfile->instructor;
        return new Envelope(
            subject: "Booking Request Declined - {$instructor->name}",
        );
    }

    public function content(): Content
    {
        $profile = $this->booking->bookingProfile;
        $instructor = $profile->instructor;
        $host = $this->booking->host;

        return new Content(
            markdown: 'emails.one-on-one.booking-declined',
            with: [
                'booking' => $this->booking,
                'instructorName' => $profile->display_name ?? $instructor->name,
                'meetingDate' => $this->booking->start_time->format('l, F j, Y'),
                'meetingTime' => $this->booking->start_time->format('g:i A') . ' - ' . $this->booking->end_time->format('g:i A'),
                'meetingType' => $this->booking->meeting_type_label,
                'duration' => $this->booking->formatted_duration,
                'declineReason' => $this->booking->decline_reason,
                'studioName' => $host?->studio_name ?? 'Studio',
                'bookingUrl' => route('subdomain.instructor.book-meeting', [
                    'subdomain' => $host->subdomain,
                    'instructor' => $instructor->id,
                ]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
