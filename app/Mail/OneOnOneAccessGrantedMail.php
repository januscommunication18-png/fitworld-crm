<?php

namespace App\Mail;

use App\Models\BookingProfile;
use App\Models\Instructor;
use App\Models\TeamInvitation;
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
        public BookingProfile $profile,
        public bool $hasUserAccount = true,
        public ?TeamInvitation $invitation = null
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

        // Determine the appropriate URL based on user account status
        if ($this->hasUserAccount) {
            $actionUrl = route('one-on-one.index');
            $actionText = 'Set Up My Booking Profile';
        } else {
            // No user account - use invitation signup URL
            $actionUrl = $this->invitation
                ? route('invitation.show', ['token' => $this->invitation->token])
                : route('login');
            $actionText = 'Create Account & Set Up Profile';
        }

        return new Content(
            markdown: 'emails.one-on-one.access-granted',
            with: [
                'instructor' => $this->instructor,
                'profile' => $this->profile,
                'studioName' => $host?->studio_name ?? 'Your Studio',
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
                'hasUserAccount' => $this->hasUserAccount,
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
