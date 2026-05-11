<?php

namespace App\Http\Requests\Signup;

use App\Rules\ValidName;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'is_studio_owner' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ];
    }
}
