<?php

namespace App\Http\Requests\Signup;

use Illuminate\Foundation\Http\FormRequest;

class ClassSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skip_class_setup' => ['boolean'],
            'class_name' => ['required_if:skip_class_setup,false', 'nullable', 'string', 'max:255'],
            'class_type' => ['nullable', 'string', 'max:255'],
            'class_duration' => ['integer', 'min:15', 'max:180'],
            'class_capacity' => ['integer', 'min:1', 'max:200'],
            'class_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }
}
