<?php

namespace App\Http\Requests\Onboarding;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_members' => ['nullable', 'array', 'max:10'],
            'staff_members.*.first_name' => ['required', 'string', 'max:100'],
            'staff_members.*.last_name' => ['nullable', 'string', 'max:100'],
            'staff_members.*.email' => [
                'required',
                'email',
                'max:255',
            ],
            'staff_members.*.role' => [
                'required',
                Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_INSTRUCTOR]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_members.*.first_name.required' => 'Staff member name is required.',
            'staff_members.*.email.required' => 'Staff member email is required.',
            'staff_members.*.email.email' => 'Please enter a valid email address.',
            'staff_members.*.role.required' => 'Please select a role for the staff member.',
        ];
    }
}
