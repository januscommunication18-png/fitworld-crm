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
            'studio_categories' => ['required', 'array', 'min:1'],
            'studio_categories.*' => ['string', 'max:255'],
            'country' => ['required', 'string', 'max:2', 'in:US,CA,GB,DE,AU,IN'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('hosts', 'subdomain')->ignore($hostId),
            ],
            'default_currency' => ['nullable', 'string', 'size:3', 'in:USD,CAD,GBP,EUR,AUD,INR'],
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
