<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesCustomTemplate;

    public function __construct(
        public Booking $booking
    ) {}

    public function build()
    {
        $bookable = $this->booking->bookable;
        $host = $this->booking->host;

        $className = $bookable?->display_title ?? $bookable?->title ?? 'Your Class';
        $instructorName = $bookable instanceof \App\Models\ServiceSlot
            ? ($bookable?->instructor?->name ?? '')
            : ($bookable?->primaryInstructor?->name ?? '');

        $variables = [
            'customer_name' => $this->booking->client?->full_name ?? 'there',
            'class_name' => $className,
            'class_date' => $bookable?->start_time?->format('l, F j, Y') ?? '-',
            'class_time' => $bookable?->start_time && $bookable?->end_time
                ? $bookable->start_time->format('g:i A') . ' - ' . $bookable->end_time->format('g:i A')
                : '-',
            'instructor_name' => $instructorName,
            'location' => $bookable?->location?->name ?? '',
            'studio_name' => $host?->studio_name ?? 'Our Studio',
            'studio_email' => $host?->studio_email ?? $host?->contact_email ?? '',
            'studio_phone' => $host?->phone ?? $host?->contact_phone ?? '',
        ];

        // Try custom template first
        if ($host && $this->buildFromCustomTemplate('class_reminder', $host, $variables)) {
            return $this;
        }

        // Fall back to default blade template
        return $this->subject("Reminder: {$className} - Tomorrow")
            ->markdown('emails.class-reminder', [
                'booking' => $this->booking,
                'client' => $this->booking->client,
                'className' => $className,
                'sessionDate' => $variables['class_date'],
                'sessionTime' => $variables['class_time'],
                'instructorName' => $instructorName ?: null,
                'locationName' => $bookable?->location?->name ?? null,
                'studioName' => $variables['studio_name'],
            ]);
    }
}
