<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\QuestionnaireResponse;
use App\Services\QrImageService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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
                'qrImageSrc' => $this->qrImageSrc(),
                'qrDownloadUrl' => $this->qrDownloadUrl(),
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
        $png = $this->qrPng();

        if (! $png) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $png, 'checkin-qr.png')->withMime('image/png'),
        ];
    }

    /**
     * Raw PNG bytes of the client's check-in QR (null if no client).
     */
    protected function qrPng(): ?string
    {
        $client = $this->booking->client;

        return $client
            ? app(QrImageService::class)->pngForClient($client)
            : null;
    }

    /**
     * Inline image source (base64 data URI) for the client's check-in QR.
     * A data URI renders self-contained — no CID/attachment resolution or
     * external fetch needed — so it shows in previews and most mail clients.
     */
    protected function qrImageSrc(): ?string
    {
        $client = $this->booking->client;

        return $client
            ? app(QrImageService::class)->dataUriForToken($client->getOrCreateQrCode()->qr_token)
            : null;
    }

    /**
     * Public URL to view/download the client's check-in QR.
     */
    protected function qrDownloadUrl(): ?string
    {
        $client = $this->booking->client;

        if (! $client) {
            return null;
        }

        return route('checkin-qr.show', ['token' => $client->getOrCreateQrCode()->qr_token]);
    }
}
