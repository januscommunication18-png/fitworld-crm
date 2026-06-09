<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = Client::getStatuses();
        $sources = Client::getLeadSources();

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'initials' => $this->initials,
            'avatar_url' => $this->avatarUrlForRequest($request),
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'membership_status' => $this->membership_status,
            'lead_source' => $this->lead_source,
            'source_label' => $sources[$this->lead_source] ?? $this->lead_source,
            'last_visit_at' => $this->last_visit_at?->toIso8601String(),
            'created_via' => $this->created_via,
            'total_classes_attended' => (int) $this->total_classes_attended,
            'lifetime_value' => $this->lifetime_value !== null ? (float) $this->lifetime_value : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'color' => $t->color,
            ])->values()),
        ];
    }

    /**
     * Absolute avatar URL built from the *request* origin (not APP_URL), so the
     * mobile client receives a URL on the same host it called — e.g.
     * `http://127.0.0.1:8001/storage/...` in local dev.
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
