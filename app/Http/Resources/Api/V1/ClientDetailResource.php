<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full client profile for the mobile detail screen. Mirrors the host web
 * "Overview" tab: contact + personal details, source, membership, lifetime
 * stats and recent bookings.
 *
 * The controller attaches `booking_stats`, `recent_bookings` and
 * `active_membership` to the model before this resource is built.
 */
class ClientDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = Client::getStatuses();
        $sources = Client::getLeadSources();
        $genders = Client::getGenders();

        $membership = $this->active_membership;

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'initials' => $this->initials,
            'avatar_url' => $this->avatarUrlForRequest($request),
            'email' => $this->email,
            'phone' => $this->phone,
            'secondary_phone' => $this->secondary_phone,

            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'membership_status' => $this->membership_status,

            // Lifetime stats
            'last_visit_at' => $this->last_visit_at?->toIso8601String(),
            'next_booking_at' => $this->next_booking_at?->toIso8601String(),
            'created_via' => $this->created_via,
            'total_classes_attended' => (int) $this->total_classes_attended,
            'lifetime_value' => $this->lifetime_value !== null ? (float) $this->lifetime_value : null,
            'created_at' => $this->created_at?->toIso8601String(),

            // Health score (engagement / usage / revenue)
            'client_score' => $this->client_score,

            // Communication preferences
            'preferred_contact_method' => $this->preferred_contact_method,
            'preferred_contact_label' => Client::getContactMethods()[$this->preferred_contact_method] ?? 'Email',
            'email_opt_in' => (bool) $this->email_opt_in,
            'sms_opt_in' => (bool) $this->sms_opt_in,
            'marketing_opt_in' => (bool) $this->marketing_opt_in,

            // Personal details
            'date_of_birth' => $this->date_of_birth?->toIso8601String(),
            'gender' => $this->gender,
            'gender_label' => $this->gender ? ($genders[$this->gender] ?? ucfirst($this->gender)) : null,

            // Contact details
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state_province' => $this->state_province,
            'postal_code' => $this->postal_code,
            'country' => $this->country,

            // Source & lead
            'lead_source' => $this->lead_source,
            'source_label' => $sources[$this->lead_source] ?? $this->lead_source,
            'referral_source' => $this->referral_source,
            'source_url' => $this->source_url,

            // Emergency contact
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_email' => $this->emergency_contact_email,

            // Health & fitness
            'experience_level' => $this->experience_level,
            'experience_level_label' => $this->experience_level
                ? (Client::getExperienceLevels()[$this->experience_level] ?? ucfirst((string) $this->experience_level))
                : null,
            'pregnancy_status' => (bool) $this->pregnancy_status,
            'fitness_goals' => $this->fitness_goals,
            'medical_conditions' => $this->medical_conditions,
            'injuries' => $this->injuries,
            'limitations' => $this->limitations,

            // Active membership
            'membership' => $membership ? [
                'plan_name' => $membership->membershipPlan?->name,
                'status' => $membership->status,
                'status_label' => ucfirst(str_replace('_', ' ', (string) $membership->status)),
                'start_date' => $membership->start_date?->toIso8601String()
                    ?? $this->membership_start_date?->toIso8601String(),
                'end_date' => $membership->end_date?->toIso8601String()
                    ?? $this->membership_end_date?->toIso8601String(),
            ] : null,

            // Free-text internal notes (Overview)
            'internal_notes' => $this->notes,

            // Custom fields (label/value pairs that have a value set)
            'custom_fields' => $this->customFields(),

            // Booking stats { total, confirmed, attended, cancelled, no_show }
            'booking_stats' => $this->booking_stats,

            // Category summary { classes, services, memberships, packages }
            'booking_summary' => $this->booking_summary,

            // Recent bookings (overview) + full list (Bookings/Activity tabs)
            'recent_bookings' => BookingResource::collection($this->recent_bookings),
            'bookings' => BookingResource::collection($this->bookings),

            // Notes tab — timeline of note/call/email/system entries
            'notes' => $this->notesTimeline()->map(fn ($n) => [
                'id' => $n->id,
                'note_type' => $n->note_type,
                'type_label' => ClientNote::getNoteTypes()[$n->note_type] ?? $n->note_type,
                'content' => $n->content,
                'author_name' => $n->author?->name,
                'created_at' => $n->created_at?->toIso8601String(),
            ])->values(),

            // Questionnaires tab
            'questionnaires' => $this->questionnaires->map(fn ($q) => [
                'id' => $q->id,
                'name' => $q->version?->questionnaire?->name ?? 'Questionnaire',
                'status' => $q->status,
                'status_label' => ucfirst(str_replace('_', ' ', (string) $q->status)),
                'booking_name' => $this->questionnaireBookingName($q),
                'created_at' => $q->created_at?->toIso8601String(),
                'completed_at' => $q->completed_at?->toIso8601String(),
            ])->values(),

            // Progress tab — measurements
            'measurements' => $this->measurements->map(fn ($m) => [
                'id' => $m->id,
                'measured_at' => $m->measured_at?->toIso8601String(),
                'weight' => $m->weight !== null ? (float) $m->weight : null,
                'weight_unit' => $m->weight_unit,
                'body_fat' => $m->body_fat !== null ? (float) $m->body_fat : null,
                'chest' => $m->chest !== null ? (float) $m->chest : null,
                'waist' => $m->waist !== null ? (float) $m->waist : null,
                'recorded_by' => $m->recordedBy?->name,
            ])->values(),

            // Progress tab — scored reports
            'progress_reports' => $this->progress_reports->map(fn ($r) => [
                'id' => $r->id,
                'template_name' => $r->template?->name ?? 'Progress report',
                'report_date' => $r->report_date?->toIso8601String(),
                'overall_score' => $r->overall_score !== null ? (float) $r->overall_score : null,
            ])->values(),

            'tags' => $this->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'color' => $t->color,
            ])->values(),
        ];
    }

    /**
     * Flat label/value list of custom field values that are actually set.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function customFields(): array
    {
        if (! $this->relationLoaded('fieldValues')) {
            return [];
        }

        return $this->fieldValues
            ->filter(fn ($v) => $v->fieldDefinition && $v->value !== null && $v->value !== '')
            ->map(fn ($v) => [
                'label' => $v->fieldDefinition->field_label,
                'value' => (string) $v->formatted_value,
            ])
            ->values()
            ->all();
    }

    /** The notes-timeline collection attached by the controller. */
    protected function notesTimeline(): \Illuminate\Support\Collection
    {
        $attached = $this->resource->getAttribute('notes_timeline');

        return $attached instanceof \Illuminate\Support\Collection ? $attached : collect();
    }

    protected function questionnaireBookingName($q): ?string
    {
        $b = $q->booking?->bookable;
        if (! $b) {
            return null;
        }

        return $b->classPlan?->name
            ?? $b->servicePlan?->name
            ?? $b->title
            ?? $b->name
            ?? null;
    }

    /**
     * Absolute avatar URL built from the *request* origin (not APP_URL), so the
     * mobile client receives a URL on the same host it called.
     */
    protected function avatarUrlForRequest(Request $request): ?string
    {
        $photo = $this->profile_photo;
        if (empty($photo)) {
            return null;
        }
        if (str_starts_with($photo, 'http')) {
            return $photo;
        }

        return $request->getSchemeAndHttpHost() . '/storage/' . ltrim($photo, '/');
    }
}