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
        public Host $host
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

        return new Content(
            markdown: 'emails.one-on-one.booking-invite',
            with: [
                'instructor' => $this->instructor,
                'instructorName' => $displayName,
                'instructorTitle' => $title,
                'instructorBio' => $profile?->bio ?? $this->instructor->bio,
                'studioName' => $this->host->studio_name,
                'bookingUrl' => route('subdomain.instructor.book-meeting', [
                    'subdomain' => $this->host->subdomain,
                    'instructor' => $this->instructor->id,
                ]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
