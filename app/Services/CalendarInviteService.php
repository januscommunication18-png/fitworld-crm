<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Host;
use App\Models\ServiceSlot;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CalendarInviteService
{
    /**
     * Generate ICS content from a transaction
     */
    public function generateFromTransaction(Transaction $transaction): ?string
    {
        $purchasable = $transaction->purchasable;

        // Only generate for class sessions and service slots
        if (!($purchasable instanceof ClassSession) && !($purchasable instanceof ServiceSlot)) {
            return null;
        }

        $host = $transaction->host;
        $metadata = $transaction->metadata ?? [];

        $startTime = $purchasable->start_time;
        $endTime = $purchasable->end_time ?? $startTime->copy()->addHour();

        $summary = $metadata['item_name'] ?? $this->getSummary($purchasable, $host);
        $description = $this->getDescription($transaction, $purchasable);
        $location = $this->getLocation($purchasable, $host);
        $uid = $this->generateUid($transaction);

        return $this->buildIcs(
            $uid,
            $summary,
            $description,
            $location,
            $startTime,
            $endTime,
            $host
        );
    }

    /**
     * Generate ICS content from a booking
     */
    public function generateFromBooking(Booking $booking): ?string
    {
        $bookable = $booking->bookable;

        if (!$bookable) {
            return null;
        }

        // Only generate for class sessions and service slots
        if (!($bookable instanceof ClassSession) && !($bookable instanceof ServiceSlot)) {
            return null;
        }

        $host = $booking->host;
        $startTime = $bookable->start_time;
        $endTime = $bookable->end_time ?? $startTime->copy()->addHour();

        $summary = $this->getSummary($bookable, $host);
        $description = $this->getBookingDescription($booking, $bookable);
        $location = $this->getLocation($bookable, $host);
        $uid = 'booking-' . $booking->id . '@' . ($host->subdomain ?? 'fitcrm') . '.biz';

        return $this->buildIcs(
            $uid,
            $summary,
            $description,
            $location,
            $startTime,
            $endTime,
            $host
        );
    }

    /**
     * Build the ICS file content
     */
    protected function buildIcs(
        string $uid,
        string $summary,
        string $description,
        string $location,
        Carbon $startTime,
        Carbon $endTime,
        Host $host
    ): string {
        $now = Carbon::now('UTC');
        $dtStart = $startTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtEnd = $endTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtStamp = $now->format('Ymd\THis\Z');

        // Escape special characters in text fields
        $summary = $this->escapeIcsText($summary);
        $description = $this->escapeIcsText($description);
        $location = $this->escapeIcsText($location);

        $organizer = $host->email ?? 'noreply@fitcrm.biz';
        $organizerName = $this->escapeIcsText($host->studio_name);

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//FitCRM//Booking System//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$dtStamp}\r\n";
        $ics .= "DTSTART:{$dtStart}\r\n";
        $ics .= "DTEND:{$dtEnd}\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= "ORGANIZER;CN={$organizerName}:mailto:{$organizer}\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "SEQUENCE:0\r\n";
        $ics .= "BEGIN:VALARM\r\n";
        $ics .= "TRIGGER:-PT1H\r\n";
        $ics .= "ACTION:DISPLAY\r\n";
        $ics .= "DESCRIPTION:Reminder: {$summary}\r\n";
        $ics .= "END:VALARM\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Get event summary from bookable
     */
    protected function getSummary($bookable, Host $host): string
    {
        $name = '';

        if ($bookable instanceof ClassSession) {
            $name = $bookable->display_title ?? $bookable->classPlan?->name ?? 'Class';
        } elseif ($bookable instanceof ServiceSlot) {
            $name = $bookable->servicePlan?->name ?? 'Appointment';
        }

        return "{$name} at {$host->studio_name}";
    }

    /**
     * Get event description from transaction
     */
    protected function getDescription(Transaction $transaction, $purchasable): string
    {
        $metadata = $transaction->metadata ?? [];
        $lines = [];

        $lines[] = $metadata['item_name'] ?? 'Booking';

        if (!empty($metadata['item_instructor'])) {
            $lines[] = "Instructor: " . $metadata['item_instructor'];
        }

        if ($purchasable instanceof ClassSession && $purchasable->classPlan?->description) {
            $lines[] = "";
            $lines[] = $purchasable->classPlan->description;
        }

        $lines[] = "";
        $lines[] = "Transaction: " . $transaction->transaction_id;

        $host = $transaction->host;
        if ($host->phone) {
            $lines[] = "Contact: " . $host->phone;
        }
        if ($host->email) {
            $lines[] = "Email: " . $host->email;
        }

        return implode("\\n", $lines);
    }

    /**
     * Get event description from booking
     */
    protected function getBookingDescription(Booking $booking, $bookable): string
    {
        $lines = [];

        if ($bookable instanceof ClassSession) {
            $lines[] = $bookable->display_title ?? $bookable->classPlan?->name ?? 'Class';

            if ($bookable->primaryInstructor) {
                $lines[] = "Instructor: " . $bookable->primaryInstructor->name;
            }

            if ($bookable->classPlan?->description) {
                $lines[] = "";
                $lines[] = $bookable->classPlan->description;
            }
        } elseif ($bookable instanceof ServiceSlot) {
            $lines[] = $bookable->servicePlan?->name ?? 'Appointment';

            if ($bookable->instructor) {
                $lines[] = "With: " . $bookable->instructor->name;
            }
        }

        $host = $booking->host;
        $lines[] = "";
        if ($host->phone) {
            $lines[] = "Contact: " . $host->phone;
        }
        if ($host->email) {
            $lines[] = "Email: " . $host->email;
        }

        return implode("\\n", $lines);
    }

    /**
     * Get location string
     */
    protected function getLocation($bookable, Host $host): string
    {
        $location = null;

        if ($bookable instanceof ClassSession) {
            $location = $bookable->room?->location?->name
                ?? $bookable->room?->name
                ?? null;
        } elseif ($bookable instanceof ServiceSlot) {
            $location = $bookable->location?->name ?? null;
        }

        if (!$location) {
            // Fall back to host address
            $parts = array_filter([
                $host->studio_name,
                $host->address,
                $host->city,
                $host->state,
                $host->postal_code,
            ]);
            $location = implode(', ', $parts);
        }

        return $location ?: $host->studio_name;
    }

    /**
     * Generate unique event ID
     */
    protected function generateUid(Transaction $transaction): string
    {
        $host = $transaction->host;
        $domain = $host->subdomain ? "{$host->subdomain}.fitcrm.biz" : 'fitcrm.biz';

        return $transaction->transaction_id . '@' . $domain;
    }

    /**
     * Escape text for ICS format
     */
    protected function escapeIcsText(string $text): string
    {
        // Replace newlines with \n
        $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);
        // Escape commas, semicolons, and backslashes
        $text = str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $text);

        return $text;
    }
}
