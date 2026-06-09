<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full detail for a space rental — `GET /space-rentals/{id}`.
 */
class SpaceRentalDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'rental',
            'reference_number' => $this->reference_number,
            'title' => $this->config?->name ?? 'Space Rental',
            'purpose' => $this->purpose,
            'purpose_label' => $this->formatted_purpose,
            'status' => $this->status,
            'status_label' => $this->formatted_status,
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'duration_hours' => $this->hours_booked !== null ? (float) $this->hours_booked : null,
            'location' => $this->location_display,
            'client' => [
                'name' => $this->client_name,
                'email' => $this->client_email,
                'phone' => $this->client_phone,
                'company' => $this->client_company,
                'is_external' => $this->isExternalClient(),
            ],
            'pricing' => [
                'currency' => $this->currency,
                'currency_symbol' => \App\Models\MembershipPlan::getCurrencySymbol($this->currency),
                'hourly_rate' => $this->hourly_rate !== null ? (float) $this->hourly_rate : null,
                'subtotal' => $this->subtotal !== null ? (float) $this->subtotal : null,
                'tax' => $this->tax_amount !== null ? (float) $this->tax_amount : null,
                'total' => $this->total_amount !== null ? (float) $this->total_amount : null,
            ],
            'deposit' => [
                'amount' => $this->deposit_amount !== null ? (float) $this->deposit_amount : null,
                'status' => $this->deposit_status,
                'status_label' => $this->formatted_deposit_status,
                'refund_amount' => $this->deposit_refund_amount !== null ? (float) $this->deposit_refund_amount : null,
                'refund_reason' => $this->deposit_refund_reason,
                'refunded_at' => $this->deposit_refunded_at?->toIso8601String(),
            ],
            'waiver' => [
                'required' => $this->requiresWaiver(),
                'signed' => (bool) $this->waiver_signed,
                'signed_at' => $this->waiver_signed_at?->toIso8601String(),
                'signer_name' => $this->waiver_signer_name,
            ],
            'damage' => [
                'reported' => (bool) $this->damage_reported,
                'notes' => $this->damage_notes,
                'charge' => $this->damage_charge !== null ? (float) $this->damage_charge : null,
            ],
            'purpose_notes' => $this->purpose_notes,
            'internal_notes' => $this->internal_notes,
            'cancellation_reason' => $this->cancellation_reason,

            // Who did what.
            'created_by' => $this->createdBy?->name,
            'confirmed_by' => $this->confirmedBy?->name,
            'completed_by' => $this->completedBy?->name,
            'cancelled_by' => $this->cancelledBy?->name,

            'booked_at' => $this->created_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),

            // Status history (newest first, per the model's default ordering).
            'status_history' => $this->whenLoaded('statusLogs', fn () => $this->statusLogs->map(fn ($l) => [
                'label' => $l->status_change_label,
                'notes' => $l->notes,
                'at' => $l->created_at?->toIso8601String(),
                'by' => $l->updatedByUser?->name,
            ])->values(), []),
        ];
    }
}
