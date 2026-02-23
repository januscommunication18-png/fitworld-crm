<?php

namespace App\Http\Requests\Host;

use App\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->host_id !== null;
    }

    public function rules(): array
    {
        $host = auth()->user()->host;
        $defaultCurrency = $host->default_currency ?? 'USD';

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getTypes()))],
            'has_scheduled_class' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'], // Legacy field, kept for compatibility
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'prices.' . $defaultCurrency => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'interval' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getIntervals()))],
            'credits_per_cycle' => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:type,credits'],
            'eligibility_scope' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getEligibilityScopes()))],
            'class_plan_ids' => ['nullable', 'array'],
            'class_plan_ids.*' => ['exists:class_plans,id'],
            'addon_members' => ['nullable', 'integer', 'min:0', 'max:10'],
            'free_amenities' => ['nullable', 'array'],
            'free_amenities.*' => ['string', 'max:255'],
            'free_rental_ids' => ['nullable', 'array'],
            'free_rental_ids.*' => ['integer', 'exists:rental_items,id'],
            'location_scope_type' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getLocationScopes()))],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['exists:locations,id'],
            'visibility_public' => ['nullable', 'boolean'],
            'status' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getStatuses()))],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'color.regex' => 'The color must be a valid hex color code (e.g., #10b981).',
            'credits_per_cycle.required_if' => 'Credits per cycle is required for credit-based memberships.',
        ];
    }
}
