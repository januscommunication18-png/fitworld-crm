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
            'address' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?=.*[a-zA-Z0-9])[\w\s,.\-\'#\/]+$/',
            ],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zipcode' => ['nullable', 'string', 'max:20'],
            'rooms' => ['integer', 'min:1', 'max:20'],
            'default_capacity' => ['integer', 'min:1', 'max:200'],
            'amenities' => ['array'],
            'amenities.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'Studio address is required.',
            'address.max' => 'Address cannot exceed 255 characters.',
            'address.regex' => 'Please enter a valid street address.',
        ];
    }
}
