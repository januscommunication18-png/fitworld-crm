<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $className = $this->booking->bookable?->display_title
            ?? $this->booking->bookable?->title
            ?? 'Your Class';

        return new Envelope(
            subject: "Reminder: {$className} - Tomorrow",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $bookable = $this->booking->bookable;
        $host = $this->booking->host;

        return new Content(
            markdown: 'emails.class-reminder',
            with: [
                'booking' => $this->booking,
                'client' => $this->booking->client,
                'className' => $bookable?->display_title ?? $bookable?->title ?? 'Class Session',
                'sessionDate' => $bookable?->start_time?->format('l, F j, Y') ?? '-',
                'sessionTime' => $bookable?->start_time && $bookable?->end_time
                    ? $bookable->start_time->format('g:i A') . ' - ' . $bookable->end_time->format('g:i A')
                    : '-',
                'instructorName' => $bookable instanceof \App\Models\ServiceSlot
                    ? ($bookable?->instructor?->name ?? null)
                    : ($bookable?->primaryInstructor?->name ?? null),
                'locationName' => $bookable?->location?->name ?? null,
                'studioName' => $host?->studio_name ?? 'Our Studio',
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
