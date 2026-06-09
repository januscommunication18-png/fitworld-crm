<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full detail for a class (or membership) session — `GET /class-sessions/{id}`.
 * A membership session is a ClassSession with no class plan.
 *
 * Roster stats are aggregate counts (withCount aliases set by the controller),
 * and `attendees` is only a small preview — the full roster is paginated via
 * `GET /class-sessions/{id}/bookings`. This keeps the detail payload small even
 * when a session has hundreds of bookings.
 */
class ClassSessionDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = ClassSession::getStatuses();
        $isMembership = $this->class_plan_id === null;
        $capacity = $this->capacity !== null ? (int) $this->capacity : null;
        $booked = (int) ($this->booked_count ?? 0);

        return [
            'id' => $this->id,
            'type' => $isMembership ? 'membership' : 'class',
            'name' => $this->classPlan?->name
                ?? $this->membershipPlans->first()?->name
                ?? $this->title
                ?? ($isMembership ? 'Membership Session' : 'Class'),
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'capacity' => $capacity,
            'booked' => $booked,
            'price' => $this->price !== null ? (float) $this->price : null,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,

            // Class plan descriptors.
            'category' => $this->classPlan?->category,
            'difficulty' => $this->classPlan?->difficulty_level,
            'description' => $this->classPlan?->description,

            // Staff.
            'instructor' => $this->primaryInstructor?->name ?? 'TBA',
            'backup_instructors' => $this->backupInstructors->pluck('name')->values(),

            // Location.
            'location' => $this->location?->name,
            'room' => $this->room?->name,
            'room_capacity' => $this->room?->capacity,
            'address' => $this->location?->full_address,
            'location_notes' => $this->location_notes,

            // State.
            'is_recurring' => $this->isRecurring(),
            'has_conflict' => $this->hasUnresolvedConflict(),
            'conflict_notes' => $this->conflict_notes,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'notes' => $this->notes,
            'membership_plans' => $this->membershipPlans->pluck('name')->values(),

            // Roster stats (aggregate counts — see controller withCount).
            'stats' => [
                'booked' => $booked,
                'checked_in' => (int) ($this->checked_in_count ?? 0),
                'intake_completed' => (int) ($this->intake_completed_count ?? 0),
                'intake_pending' => (int) ($this->intake_pending_count ?? 0),
                'cancelled' => (int) ($this->cancelled_count ?? 0),
                'spots_left' => $capacity !== null ? max(0, $capacity - $booked) : null,
            ],

            // Preview + total; full list is paginated separately.
            'attendees_total' => (int) ($this->total_count ?? 0),
            'attendees' => BookingAttendeeResource::collection($this->bookings),
        ];
    }
}
