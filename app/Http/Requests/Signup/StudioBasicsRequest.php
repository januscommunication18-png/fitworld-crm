<?php

namespace App\Http\Requests\Signup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudioBasicsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hostId = $this->user()->host_id;

        return [
            'studio_name' => ['required', 'string', 'max:255'],
            'studio_types' => ['array'],
            'studio_types.*' => ['string'],
            'city' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('hosts', 'subdomain')->ignore($hostId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Subdomain must contain only lowercase letters, numbers, and hyphens.',
            'subdomain.unique' => 'This subdomain is already taken.',
        ];
    }
}
