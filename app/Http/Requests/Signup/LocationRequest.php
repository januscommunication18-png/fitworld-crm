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
            'rooms' => ['integer', 'min:1', 'max:20'],
            'default_capacity' => ['integer', 'min:1', 'max:200'],
            'amenities' => ['array'],
            'amenities.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
