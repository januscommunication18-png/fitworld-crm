<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'interval' => $this->interval,
            'price' => $this->price !== null ? (float) $this->price : null,
            'credits_per_cycle' => $this->credits_per_cycle,
            'status' => $this->status,
            'color' => $this->color,
            'visibility_public' => (bool) $this->visibility_public,
            'sort_order' => $this->sort_order,
            'class_plans_count' => $this->when(isset($this->class_plans_count), (int) $this->class_plans_count),
        ];
    }
}
