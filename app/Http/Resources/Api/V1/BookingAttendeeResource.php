<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One attendee row for a session/slot roster. Used both for the small preview
 * embedded in the detail payload and for the paginated roster endpoint, so the
 * shape stays identical.
 */
class BookingAttendeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = Booking::getStatuses();
        $methods = Booking::getPaymentMethods();
        $intakes = Booking::getIntakeStatuses();

        return [
            'id' => $this->id,
            'client_name' => $this->client?->full_name ?? 'Unknown',
            'client_email' => $this->client?->email,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'checked_in' => $this->checked_in_at !== null,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'price_paid' => $this->price_paid !== null ? (float) $this->price_paid : null,
            'payment_label' => $methods[$this->payment_method] ?? $this->payment_method,
            'intake_status' => $this->intake_status,
            'intake_label' => $intakes[$this->intake_status] ?? $this->intake_status,
            'booked_at' => ($this->booked_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
