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
        public ?\Carbon\Carbon $scheduledAt = null,
        public ?array $scheduledSlots = null
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
        // For multi-slot invites, include the slots JSON
        if (!empty($this->scheduledSlots)) {
            $queryParams['slots'] = json_encode($this->scheduledSlots);
        } elseif ($this->scheduledAt) {
            // Legacy single slot
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

        // Format scheduled slots for display in email
        $formattedSlots = $this->formatScheduledSlots();

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
                'scheduledSlots' => $this->scheduledSlots,
                'formattedSlots' => $formattedSlots,
                'bookingUrl' => $bookingUrl,
            ],
        );
    }

    /**
     * Format scheduled slots for display in the email.
     */
    protected function formatScheduledSlots(): array
    {
        $formatted = [];

        if (!empty($this->scheduledSlots)) {
            foreach ($this->scheduledSlots as $date => $times) {
                $dateObj = \Carbon\Carbon::parse($date);
                $formattedTimes = [];

                foreach ($times as $time) {
                    $timeObj = \Carbon\Carbon::parse($time);
                    $formattedTimes[] = $timeObj->format('g:i A');
                }

                $formatted[] = [
                    'date' => $dateObj->format('l, F j, Y'),
                    'date_short' => $dateObj->format('D, M j'),
                    'times' => $formattedTimes,
                ];
            }
        } elseif ($this->scheduledAt) {
            // Legacy single slot
            $formatted[] = [
                'date' => $this->scheduledAt->format('l, F j, Y'),
                'date_short' => $this->scheduledAt->format('D, M j'),
                'times' => [$this->scheduledAt->format('g:i A')],
            ];
        }

        return $formatted;
    }

    public function attachments(): array
    {
        return [];
    }
}
