<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = ClassSession::getStatuses();

        return [
            'id' => $this->id,
            'name' => $this->classPlan?->name ?? $this->title ?? 'Class',
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'capacity' => (int) $this->capacity,
            'booked' => $this->when(isset($this->bookings_count), (int) $this->bookings_count),
            'price' => $this->price !== null ? (float) $this->price : null,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'instructor' => $this->primaryInstructor?->name ?? 'TBA',
            'location' => $this->location?->name,
            'room' => $this->room?->name,
        ];
    }
}
