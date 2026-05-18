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
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(array_keys(MembershipPlan::getTypes()))],
            'new_member_prices' => ['nullable', 'array'],
            'new_member_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'billing_discounts_1mo' => ['required', 'array'],
            'billing_discounts_1mo.' . $defaultCurrency => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'billing_discounts_1mo.*' => ['nullable', 'numeric', 'min:0'],
            'billing_discounts_3mo' => ['nullable', 'array'],
            'billing_discounts_3mo.*' => ['nullable', 'numeric', 'min:0'],
            'billing_discounts_6mo' => ['nullable', 'array'],
            'billing_discounts_6mo.*' => ['nullable', 'numeric', 'min:0'],
            'billing_discounts_9mo' => ['nullable', 'array'],
            'billing_discounts_9mo.*' => ['nullable', 'numeric', 'min:0'],
            'billing_discounts_12mo' => ['nullable', 'array'],
            'billing_discounts_12mo.*' => ['nullable', 'numeric', 'min:0'],
            'registration_fees' => ['nullable', 'array'],
            'registration_fees.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'cancellation_fees' => ['nullable', 'array'],
            'cancellation_fees.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'cancellation_grace_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'file_attachments' => ['nullable', 'array'],
            'file_attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        $host = auth()->user()->host;
        $defaultCurrency = $host->default_currency ?? 'USD';

        return [
            'billing_discounts_1mo.' . $defaultCurrency . '.required' => 'The 1 Month base price (' . $defaultCurrency . ') is required.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #10b981).',
            'credits_per_cycle.required_if' => 'Credits per cycle is required for credit-based memberships.',
        ];
    }
}
