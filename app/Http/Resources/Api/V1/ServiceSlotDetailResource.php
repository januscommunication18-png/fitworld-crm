<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ServiceSlot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full detail for a service slot — `GET /service-slots/{id}`.
 *
 * Roster stats are aggregate counts (withCount aliases set by the controller),
 * and `attendees` is only a small preview — the full roster is paginated via
 * `GET /service-slots/{id}/bookings`.
 */
class ServiceSlotDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = ServiceSlot::getStatuses();
        $price = $this->getEffectivePrice();
        $booked = (int) ($this->booked_count ?? 0);

        return [
            'id' => $this->id,
            'type' => 'service',
            'name' => $this->servicePlan?->name ?? $this->title ?? 'Service',
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'duration_minutes' => ($this->start_time && $this->end_time)
                ? (int) $this->start_time->diffInMinutes($this->end_time)
                : null,
            'price' => $price !== null ? (float) $price : null,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,

            // Service plan descriptors.
            'category' => $this->servicePlan?->category,
            'location_type' => $this->servicePlan?->location_type,
            'max_participants' => $this->servicePlan?->max_participants,

            // Staff.
            'instructor' => $this->instructor?->name ?? 'TBA',
            'backup_instructors' => [],

            // Location.
            'location' => $this->location?->name,
            'room' => $this->room?->name,
            'room_capacity' => $this->room?->capacity,
            'address' => $this->location?->full_address,

            // State.
            'is_recurring' => $this->recurrence_rule !== null || $this->recurrence_parent_id !== null,
            'has_conflict' => false,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'notes' => $this->notes,

            'stats' => [
                'booked' => $booked,
                'checked_in' => (int) ($this->checked_in_count ?? 0),
                'intake_completed' => (int) ($this->intake_completed_count ?? 0),
                'intake_pending' => (int) ($this->intake_pending_count ?? 0),
                'cancelled' => (int) ($this->cancelled_count ?? 0),
                'spots_left' => null,
            ],

            'attendees_total' => (int) ($this->total_count ?? 0),
            'attendees' => BookingAttendeeResource::collection($this->bookings),
        ];
    }
}
