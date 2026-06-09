<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassPassResource extends JsonResource
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
            'class_count' => $this->class_count,
            'price' => $this->price !== null ? (float) $this->price : null,
            'validity_type' => $this->validity_type,
            'validity_value' => $this->validity_value,
            'activation_type' => $this->activation_type,
            'status' => $this->status,
            'purchases_count' => $this->when(isset($this->purchases_count), (int) $this->purchases_count),
        ];
    }
}
