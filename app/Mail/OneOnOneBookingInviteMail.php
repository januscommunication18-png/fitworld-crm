<?php

namespace App\Mail;

use App\Models\Host;
use App\Models\Instructor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingInviteMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Instructor $instructor,
        public Host $host,
        public ?string $clientName = null,
        public ?string $clientEmail = null,
        public ?int $duration = null,
        public ?\Carbon\Carbon $scheduledAt = null
    ) {}

    public function envelope(): Envelope
    {
        $displayName = $this->instructor->bookingProfile?->display_name ?? $this->instructor->name;
        return new Envelope(
            subject: "Book a 1:1 Meeting with {$displayName} - {$this->host->studio_name}",
        );
    }

    public function content(): Content
    {
        $profile = $this->instructor->bookingProfile;
        $displayName = $profile?->display_name ?? $this->instructor->name;
        $title = $profile?->title ?? $this->instructor->title;

        // Build booking URL with optional parameters
        $bookingParams = [
            'subdomain' => $this->host->subdomain,
            'instructor' => $this->instructor->id,
        ];
        $bookingUrl = route('subdomain.instructor.book-meeting', $bookingParams);

        $queryParams = [];
        if ($this->duration) {
            $queryParams['duration'] = $this->duration;
        }
        if ($this->scheduledAt) {
            $queryParams['date'] = $this->scheduledAt->format('Y-m-d');
            $queryParams['time'] = $this->scheduledAt->format('H:i');
        }
        if ($this->clientName) {
            $queryParams['name'] = $this->clientName;
        }
        if ($this->clientEmail) {
            $queryParams['email'] = $this->clientEmail;
        }
        if (!empty($queryParams)) {
            $bookingUrl .= '?' . http_build_query($queryParams);
        }

        return new Content(
            markdown: 'emails.one-on-one.booking-invite',
            with: [
                'instructor' => $this->instructor,
                'instructorName' => $displayName,
                'instructorTitle' => $title,
                'instructorBio' => $profile?->bio ?? $this->instructor->bio,
                'studioName' => $this->host->studio_name,
                'clientName' => $this->clientName,
                'duration' => $this->duration,
                'scheduledAt' => $this->scheduledAt,
                'bookingUrl' => $bookingUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
