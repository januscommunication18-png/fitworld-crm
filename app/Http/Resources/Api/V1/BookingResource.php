<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Booking;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = Booking::getStatuses();
        $sources = Booking::getBookingSources();
        $methods = Booking::getPaymentMethods();
        $bookable = $this->whenLoaded('bookable');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'booking_source' => $this->booking_source,
            'source_label' => $sources[$this->booking_source] ?? $this->booking_source,
            'payment_method' => $this->payment_method,
            'payment_label' => $methods[$this->payment_method] ?? $this->payment_method,
            'intake_status' => $this->intake_status,
            'price_paid' => $this->price_paid !== null ? (float) $this->price_paid : null,
            'booked_at' => $this->booked_at?->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
                'email' => $this->client->email,
            ]),
            'bookable' => $bookable ? [
                'type' => $this->bookable_type === ClassSession::class ? 'class' : 'service',
                'name' => $this->bookableName(),
                'start_time' => $bookable->start_time?->toIso8601String(),
                'end_time' => $bookable->end_time?->toIso8601String(),
            ] : null,

            // Series — a purchase of many sessions. When this row represents a
            // series the controller attaches aggregate counts/dates; otherwise
            // these are null and the row is a single booking.
            'series_id' => $this->series_id,
            'is_series' => $this->series_id !== null,
            'session_count' => $this->getAttribute('series_total'),
            'series_confirmed' => $this->getAttribute('series_confirmed'),
            'series_cancelled' => $this->getAttribute('series_cancelled'),
            'series_total_paid' => $this->getAttribute('series_total_paid') !== null
                ? (float) $this->getAttribute('series_total_paid')
                : null,
            'series_first_at' => $this->seriesIso($this->getAttribute('series_first_at')),
            'series_last_at' => $this->seriesIso($this->getAttribute('series_last_at')),
        ];
    }

    /** Normalise a raw DB datetime string to ISO-8601 (or null). */
    private function seriesIso($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function bookableName(): ?string
    {
        $b = $this->bookable;
        if (! $b) {
            return null;
        }

        return $b->classPlan?->name ?? $b->title ?? $b->name ?? 'Booking';
    }
}
