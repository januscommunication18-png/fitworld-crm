<?php

namespace App\Http\Requests\Signup;

use Illuminate\Foundation\Http\FormRequest;

class InstructorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'add_self_as_instructor' => ['boolean'],
            'instructors' => ['array', 'max:15'],
            'instructors.*.name' => ['required_with:instructors', 'string', 'max:255', 'regex:/^[^\d]+$/'],
            'instructors.*.email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
