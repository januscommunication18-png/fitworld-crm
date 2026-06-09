<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Per-host role / is_primary come from the `host_user` pivot when the host
     * was loaded through `$user->hosts()` (it may be absent for legacy
     * single-host users loaded via `$user->host`).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'studio_name' => $this->studio_name,
            'subdomain' => $this->subdomain,
            'logo_url' => $this->logo_url,
            'city' => $this->city,
            'state' => $this->state,
            'timezone' => $this->timezone,
            'default_currency' => $this->default_currency,
            'status' => $this->status,
            'role' => $this->pivot->role ?? null,
            'is_primary' => (bool) ($this->pivot->is_primary ?? false),
            // Effective permission keys this user has for this studio. Owners
            // get ['*'] (wildcard = all). Drives what the mobile app shows.
            'permissions' => $request->user()?->getEffectivePermissions($this->resource) ?? [],
        ];
    }
}
