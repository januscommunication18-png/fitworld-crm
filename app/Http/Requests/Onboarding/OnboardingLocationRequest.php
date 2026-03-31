<?php

namespace App\Http\Requests\Onboarding;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location_type' => ['required', Rule::in(Location::getLocationTypes())],
            'address_line_1' => ['required_if:location_type,in_person', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            // Virtual location fields
            'virtual_platform' => ['required_if:location_type,virtual', 'nullable', 'string', 'max:50'],
            'virtual_meeting_link' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'address_line_1.required_if' => 'The address is required for in-person locations.',
            'virtual_platform.required_if' => 'Please select a virtual platform.',
        ];
    }
}
