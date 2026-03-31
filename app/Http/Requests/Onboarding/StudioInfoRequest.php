<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudioInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $host = auth()->user()->host;

        return [
            'studio_name' => ['required', 'string', 'max:255'],
            'studio_structure' => ['required', Rule::in(['solo', 'team'])],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$/',
                Rule::unique('hosts', 'subdomain')->ignore($host->id),
            ],
            'studio_types' => ['nullable', 'array'],
            'studio_types.*' => ['string'],
            'studio_categories' => ['nullable', 'array'],
            'studio_categories.*' => ['string'],
            'default_language_app' => ['nullable', 'string', 'size:2'],
            'default_currency' => ['nullable', 'string', 'size:3'],
            'cancellation_window_hours' => ['nullable', 'integer', 'min:0', 'max:168'],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'The subdomain can only contain lowercase letters, numbers, and hyphens. It must start and end with a letter or number.',
            'subdomain.unique' => 'This subdomain is already taken. Please choose another.',
        ];
    }
}
