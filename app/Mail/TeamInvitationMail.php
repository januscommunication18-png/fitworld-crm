<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TeamInvitationMail extends Mailable
{

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TeamInvitation $invitation,
        public string $studioName,
        public string $inviterName
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->studioName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-invitation',
            with: [
                'invitation' => $this->invitation,
                'studioName' => $this->studioName,
                'inviterName' => $this->inviterName,
                'acceptUrl' => url("/invite/accept/{$this->invitation->token}"),
                'role' => ucfirst($this->invitation->role),
                'expiresAt' => $this->invitation->expires_at->format('F j, Y'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
