<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\QuestionnaireResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingConfirmationMail extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param array<QuestionnaireResponse> $questionnaireResponses
     */
    public function __construct(
        public Booking $booking,
        public array $questionnaireResponses = []
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $className = $this->booking->bookable?->display_title
            ?? $this->booking->bookable?->title
            ?? 'Class';

        return new Envelope(
            subject: "Booking Confirmation - {$className}",
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
            markdown: 'emails.booking-confirmation',
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
                'questionnaireResponses' => $this->questionnaireResponses,
                'hasQuestionnaires' => count($this->questionnaireResponses) > 0,
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
