<?php

namespace App\Http\Requests\Signup;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:500'],
            'rooms' => ['integer', 'min:1', 'max:20'],
            'default_capacity' => ['integer', 'min:1', 'max:200'],
            'amenities' => ['array'],
            'amenities.*' => ['string'],
        ];
    }
}
