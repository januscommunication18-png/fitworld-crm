<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Booking;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full booking profile for the mobile detail screen. Mirrors the host web
 * booking "show" page: client, session, payment, intake and the booking
 * timeline (booked / checked-in / cancelled).
 */
class BookingDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = Booking::getStatuses();
        $sources = Booking::getBookingSources();
        $methods = Booking::getPaymentMethods();
        $intakes = Booking::getIntakeStatuses();

        $b = $this->bookable;
        $instructor = $b?->primaryInstructor ?? $b?->instructor ?? null;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,

            'booking_source' => $this->booking_source,
            'source_label' => $sources[$this->booking_source] ?? $this->booking_source,

            'payment_method' => $this->payment_method,
            'payment_label' => $methods[$this->payment_method] ?? $this->payment_method,
            'price_paid' => $this->price_paid !== null ? (float) $this->price_paid : null,

            'intake_status' => $this->intake_status,
            'intake_label' => $intakes[$this->intake_status] ?? $this->intake_status,

            // Timeline
            'booked_at' => $this->booked_at?->toIso8601String(),
            'created_by' => $this->createdBy?->name,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_in_method' => $this->checked_in_method,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'cancellation_notes' => $this->cancellation_notes,
            'cancelled_by' => $this->cancelledBy?->name,

            'client' => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
                'email' => $this->client->email,
                'phone' => $this->client->phone,
                'initials' => $this->client->initials,
            ] : null,

            'bookable' => $b ? [
                'type' => $this->bookable_type === ClassSession::class ? 'class' : 'service',
                'name' => $b->classPlan?->name ?? $b->servicePlan?->name ?? $b->title ?? $b->name ?? 'Booking',
                'start_time' => $b->start_time?->toIso8601String(),
                'end_time' => $b->end_time?->toIso8601String(),
                'instructor' => $instructor?->name,
                'location' => $b->location?->name,
            ] : null,
        ];
    }
}