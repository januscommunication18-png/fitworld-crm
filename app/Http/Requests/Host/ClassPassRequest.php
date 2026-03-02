<?php

namespace App\Http\Requests\Host;

use App\Models\ClassPass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassPassRequest extends FormRequest
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
            // Basic Details
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'class_count' => ['required', 'integer', 'min:1', 'max:999'],

            // Pricing (Multi-currency)
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'prices.' . $defaultCurrency => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'new_member_prices' => ['nullable', 'array'],
            'new_member_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],

            // Validity & Activation
            'validity_type' => ['required', 'string', Rule::in(array_keys(ClassPass::getValidityTypes()))],
            'validity_value' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
                Rule::requiredIf(fn() => in_array($this->validity_type, ['days', 'months'])),
            ],
            'activation_type' => ['required', 'string', Rule::in(array_keys(ClassPass::getActivationTypes()))],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],

            // Eligibility Rules
            'eligibility_type' => ['required', 'string', Rule::in(array_keys(ClassPass::getEligibilityTypes()))],
            'eligible_class_plan_ids' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => $this->eligibility_type === 'class_plans'),
            ],
            'eligible_class_plan_ids.*' => ['integer', 'exists:class_plans,id'],
            'eligible_categories' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => $this->eligibility_type === 'categories'),
            ],
            'eligible_categories.*' => ['string', 'max:100'],
            'eligible_instructor_ids' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => $this->eligibility_type === 'instructors'),
            ],
            'eligible_instructor_ids.*' => ['integer', 'exists:instructors,id'],
            'eligible_location_ids' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => $this->eligibility_type === 'locations'),
            ],
            'eligible_location_ids.*' => ['integer', 'exists:locations,id'],
            'excluded_class_types' => ['nullable', 'array'],
            'excluded_class_types.*' => ['string', Rule::in(array_keys(ClassPass::getClassTypes()))],

            // Credit Consumption Rules
            'default_credits_per_class' => ['required', 'integer', 'min:1', 'max:10'],
            'credit_rules' => ['nullable', 'array'],
            'credit_rules.*.class_plan_id' => ['nullable', 'integer', 'exists:class_plans,id'],
            'credit_rules.*.category' => ['nullable', 'string', 'max:100'],
            'credit_rules.*.type' => ['nullable', 'string', 'max:100'],
            'credit_rules.*.instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'credit_rules.*.credits' => ['required_with:credit_rules.*', 'integer', 'min:1', 'max:10'],

            // Peak Time Settings
            'peak_time_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'peak_time_days' => ['nullable', 'array'],
            'peak_time_days.*' => ['integer', 'min:0', 'max:6'],
            'peak_time_start' => ['nullable', 'date_format:H:i'],
            'peak_time_end' => ['nullable', 'date_format:H:i', 'after:peak_time_start'],

            // Expiry & Extension Rules
            'allow_admin_extension' => ['nullable', 'boolean'],
            'allow_freeze' => ['nullable', 'boolean'],
            'max_freeze_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'reactivation_fee' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'reactivation_fee_prices' => ['nullable', 'array'],
            'reactivation_fee_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],

            // Sharing & Transfer
            'allow_transfer' => ['nullable', 'boolean'],
            'allow_family_sharing' => ['nullable', 'boolean'],
            'allow_gifting' => ['nullable', 'boolean'],
            'max_family_members' => ['nullable', 'integer', 'min:0', 'max:10'],

            // Auto-Renewal (Hybrid Model)
            'is_recurring' => ['nullable', 'boolean'],
            'renewal_interval' => [
                'nullable',
                'string',
                Rule::in(array_keys(ClassPass::getRenewalIntervals())),
                Rule::requiredIf(fn() => $this->boolean('is_recurring')),
            ],
            'rollover_enabled' => ['nullable', 'boolean'],
            'max_rollover_credits' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_rollover_periods' => ['nullable', 'integer', 'min:0', 'max:12'],

            // Appearance & Display
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],

            // Status
            'status' => ['required', 'string', Rule::in(array_keys(ClassPass::getStatuses()))],
            'visibility_public' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name for this class pass.',
            'class_count.required' => 'Please specify how many classes are included in this pass.',
            'class_count.min' => 'The pass must include at least 1 class.',
            'prices.*.numeric' => 'Price must be a valid number.',
            'prices.' . ($this->host?->default_currency ?? 'USD') . '.required' => 'Please enter a price for the default currency.',
            'validity_value.required' => 'Please specify the validity period.',
            'activation_type.required' => 'Please select when the pass should start.',
            'eligibility_type.required' => 'Please select which classes this pass covers.',
            'eligible_class_plan_ids.required' => 'Please select at least one class plan.',
            'eligible_categories.required' => 'Please select at least one category.',
            'eligible_instructor_ids.required' => 'Please select at least one instructor.',
            'eligible_location_ids.required' => 'Please select at least one location.',
            'default_credits_per_class.required' => 'Please specify how many credits are used per class.',
            'default_credits_per_class.min' => 'Credits per class must be at least 1.',
            'peak_time_end.after' => 'Peak time end must be after peak time start.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #10b981).',
            'image.max' => 'The image must be less than 2MB.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, or WebP file.',
            'renewal_interval.required' => 'Please select a renewal interval for recurring passes.',
        ];
    }

    public function attributes(): array
    {
        return [
            'class_count' => 'number of classes',
            'validity_type' => 'validity type',
            'validity_value' => 'validity period',
            'activation_type' => 'activation timing',
            'eligibility_type' => 'eligibility scope',
            'eligible_class_plan_ids' => 'class plans',
            'eligible_categories' => 'categories',
            'eligible_instructor_ids' => 'instructors',
            'eligible_location_ids' => 'locations',
            'excluded_class_types' => 'excluded class types',
            'default_credits_per_class' => 'credits per class',
            'peak_time_multiplier' => 'peak time multiplier',
            'peak_time_days' => 'peak days',
            'peak_time_start' => 'peak time start',
            'peak_time_end' => 'peak time end',
            'grace_period_days' => 'grace period',
            'allow_admin_extension' => 'admin extension',
            'allow_freeze' => 'freeze option',
            'max_freeze_days' => 'maximum freeze days',
            'reactivation_fee' => 'reactivation fee',
            'allow_transfer' => 'transfer option',
            'allow_family_sharing' => 'family sharing',
            'allow_gifting' => 'gifting option',
            'max_family_members' => 'maximum family members',
            'is_recurring' => 'recurring pass',
            'renewal_interval' => 'renewal interval',
            'rollover_enabled' => 'credit rollover',
            'max_rollover_credits' => 'maximum rollover credits',
            'max_rollover_periods' => 'maximum rollover periods',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to booleans
        $this->merge([
            'visibility_public' => $this->boolean('visibility_public'),
            'allow_admin_extension' => $this->boolean('allow_admin_extension', true),
            'allow_freeze' => $this->boolean('allow_freeze'),
            'allow_transfer' => $this->boolean('allow_transfer'),
            'allow_family_sharing' => $this->boolean('allow_family_sharing'),
            'allow_gifting' => $this->boolean('allow_gifting'),
            'is_recurring' => $this->boolean('is_recurring'),
            'rollover_enabled' => $this->boolean('rollover_enabled'),
            'remove_image' => $this->boolean('remove_image'),
        ]);

        // Set defaults for optional fields
        if (!$this->has('default_credits_per_class')) {
            $this->merge(['default_credits_per_class' => 1]);
        }

        if (!$this->has('grace_period_days')) {
            $this->merge(['grace_period_days' => 0]);
        }

        if (!$this->has('max_freeze_days')) {
            $this->merge(['max_freeze_days' => 30]);
        }
    }
}
