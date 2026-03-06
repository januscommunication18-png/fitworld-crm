<?php

namespace App\Mail;

use App\Models\BookingProfile;
use App\Models\Instructor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneAccessGrantedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Instructor $instructor,
        public BookingProfile $profile
    ) {}

    public function envelope(): Envelope
    {
        $studioName = $this->instructor->host?->studio_name ?? 'Your Studio';

        return new Envelope(
            subject: "You've been granted 1:1 Booking Access - {$studioName}",
        );
    }

    public function content(): Content
    {
        $host = $this->instructor->host;

        return new Content(
            markdown: 'emails.one-on-one.access-granted',
            with: [
                'instructor' => $this->instructor,
                'profile' => $this->profile,
                'studioName' => $host?->studio_name ?? 'Your Studio',
                'setupUrl' => route('one-on-one-setup.index'),
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
