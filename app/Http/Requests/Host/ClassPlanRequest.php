<?php

namespace App\Http\Requests\Host;

use App\Models\ClassPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassPlanRequest extends FormRequest
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
            'category' => ['required', 'string', Rule::in(array_keys(ClassPlan::getCategories()))],
            'type' => ['required', 'string', Rule::in(array_keys(ClassPlan::getTypes()))],
            'default_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'default_capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'min_capacity' => ['nullable', 'integer', 'min:0', 'lte:default_capacity'],
            'default_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'drop_in_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'drop_in_prices' => ['nullable', 'array'],
            'drop_in_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'new_member_prices' => ['nullable', 'array'],
            'new_member_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'new_member_drop_in_prices' => ['nullable', 'array'],
            'new_member_drop_in_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'difficulty_level' => ['required', 'string', Rule::in(array_keys(ClassPlan::getDifficultyLevels()))],
            'equipment_needed' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible_on_booking_page' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'color.regex' => 'The color must be a valid hex color code (e.g., #6366f1).',
            'min_capacity.lte' => 'The minimum capacity must be less than or equal to the maximum capacity.',
        ];
    }
}
