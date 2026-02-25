<?php

namespace App\Http\Requests\Host;

use App\Models\ServicePlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->host_id !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', Rule::in(array_keys(ServicePlan::getCategories()))],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'deposit_prices' => ['nullable', 'array'],
            'deposit_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'new_member_prices' => ['nullable', 'array'],
            'new_member_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'new_member_deposit_prices' => ['nullable', 'array'],
            'new_member_deposit_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'location_type' => ['required', 'string', Rule::in(array_keys(ServicePlan::getLocationTypes()))],
            'max_participants' => ['required', 'integer', 'min:1', 'max:20'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'booking_notice_hours' => ['nullable', 'integer', 'min:0', 'max:168'], // max 1 week
            'cancellation_hours' => ['nullable', 'integer', 'min:0', 'max:168'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible_on_booking_page' => ['nullable', 'boolean'],
            'instructor_ids' => ['nullable', 'array'],
            'instructor_ids.*' => ['exists:instructors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'color.regex' => 'The color must be a valid hex color code (e.g., #8b5cf6).',
            'deposit_amount.lte' => 'The deposit amount cannot exceed the service price.',
        ];
    }
}
