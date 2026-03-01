<?php

namespace App\Mail;

use App\Models\ProspectWaitlist;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WaitlistThankYouMail extends Mailable
{

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ProspectWaitlist $waitlistEntry
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're on the FitCRM Waitlist!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.waitlist-thank-you',
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
