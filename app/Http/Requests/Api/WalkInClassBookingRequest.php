<?php

namespace App\Http\Requests\Api;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalkInClassBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated and have walk-in permissions
        $user = $this->user();
        return $user && in_array($user->role, ['owner', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],

            // Payment
            'payment_method' => ['required', Rule::in(array_keys(Booking::getPaymentMethods()))],
            'manual_method' => ['required_if:payment_method,manual', 'nullable', Rule::in(array_keys(Payment::getManualMethods()))],
            'price_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_notes' => ['nullable', 'string', 'max:500'],

            // Credit-based payment references
            'customer_membership_id' => ['nullable', 'integer', 'exists:customer_memberships,id'],
            'class_pack_purchase_id' => ['nullable', 'integer', 'exists:class_pack_purchases,id'],

            // Intake
            'intake_status' => ['nullable', Rule::in(array_keys(Booking::getIntakeStatuses()))],
            'intake_waived' => ['nullable', 'boolean'],
            'intake_waived_reason' => ['required_if:intake_waived,true', 'nullable', 'string', 'max:255'],

            // Capacity override
            'capacity_override' => ['nullable', 'boolean'],
            'capacity_override_reason' => ['required_if:capacity_override,true', 'nullable', 'string', 'max:255'],

            // Options
            'check_in_now' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client does not exist.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'manual_method.required_if' => 'Please specify the manual payment type.',
            'intake_waived_reason.required_if' => 'Please provide a reason for waiving intake.',
            'capacity_override_reason.required_if' => 'Please provide a reason for the capacity override.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'payment_method' => 'payment method',
            'manual_method' => 'manual payment type',
            'price_paid' => 'amount',
            'customer_membership_id' => 'membership',
            'class_pack_purchase_id' => 'class pack',
        ];
    }
}
