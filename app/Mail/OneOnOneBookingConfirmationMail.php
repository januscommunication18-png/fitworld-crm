<?php

namespace App\Mail;

use App\Models\OneOnOneBooking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOnOneBookingConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public OneOnOneBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $hostName = $this->booking->bookingProfile?->display_name ?? 'Your Host';

        return new Envelope(
            subject: "Meeting Confirmed with {$hostName}",
        );
    }

    public function content(): Content
    {
        $profile = $this->booking->bookingProfile;
        $host = $this->booking->host;
        $subdomain = $host->subdomain;
        $domain = config('app.booking_domain', 'fitcrm.biz');

        // Generate calendar URLs
        $calendarUrls = $this->generateCalendarUrls();

        return new Content(
            markdown: 'emails.one-on-one.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'profile' => $profile,
                'hostName' => $profile?->display_name ?? 'Your Host',
                'meetingDate' => $this->booking->start_time->format('l, F j, Y'),
                'meetingTime' => $this->booking->start_time->format('g:i A') . ' - ' . $this->booking->end_time->format('g:i A'),
                'meetingType' => $this->booking->meeting_type_label,
                'duration' => $this->booking->formatted_duration,
                'studioName' => $host?->studio_name ?? 'Studio',
                'manageUrl' => "https://{$subdomain}.{$domain}/meeting/manage/{$this->booking->manage_token}",
                'supportEmail' => $host?->studio_email ?? $host?->support_email,
                'googleCalendarUrl' => $calendarUrls['google'],
                'outlookCalendarUrl' => $calendarUrls['outlook'],
                'yahooCalendarUrl' => $calendarUrls['yahoo'],
                'icsUrl' => $calendarUrls['ics'],
            ],
        );
    }

    /**
     * Generate calendar URLs for various providers.
     */
    protected function generateCalendarUrls(): array
    {
        $booking = $this->booking;
        $profile = $booking->bookingProfile;
        $host = $booking->host;

        $title = "1:1 Meeting with " . ($profile?->display_name ?? $profile?->instructor?->name ?? 'Host');
        $startTime = $booking->start_time;
        $endTime = $booking->end_time;

        // Build description
        $description = "Meeting with {$profile?->display_name}\n";
        $description .= "Duration: {$booking->formatted_duration}\n";
        $description .= "Type: {$booking->meeting_type_label}\n";

        // Build location based on meeting type
        $location = '';
        if ($booking->meeting_type === 'in_person' && $profile?->in_person_location) {
            $location = $profile->in_person_location;
        } elseif ($booking->meeting_type === 'video' && $profile?->video_link) {
            $location = $profile->video_link;
            $description .= "Video Link: {$profile->video_link}\n";
        } elseif ($booking->meeting_type === 'phone' && $profile?->phone_number) {
            $description .= "Phone: {$profile->phone_number}\n";
        }

        // Google Calendar URL
        $googleUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $startTime->format('Ymd\THis') . '/' . $endTime->format('Ymd\THis'),
            'details' => $description,
            'location' => $location,
            'ctz' => $booking->timezone ?? config('app.timezone'),
        ]);

        // Outlook Calendar URL
        $outlookUrl = 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query([
            'subject' => $title,
            'startdt' => $startTime->toIso8601String(),
            'enddt' => $endTime->toIso8601String(),
            'body' => $description,
            'location' => $location,
        ]);

        // Yahoo Calendar URL
        $yahooUrl = 'https://calendar.yahoo.com/?' . http_build_query([
            'v' => '60',
            'title' => $title,
            'st' => $startTime->format('Ymd\THis'),
            'et' => $endTime->format('Ymd\THis'),
            'desc' => $description,
            'in_loc' => $location,
        ]);

        // ICS download URL
        $subdomain = $host->subdomain;
        $domain = config('app.booking_domain', 'fitcrm.biz');
        $icsUrl = "https://{$subdomain}.{$domain}/meeting/calendar/{$booking->confirmation_token}.ics";

        return [
            'google' => $googleUrl,
            'outlook' => $outlookUrl,
            'yahoo' => $yahooUrl,
            'ics' => $icsUrl,
        ];
    }

    public function attachments(): array
    {
        return [];
    }
}
