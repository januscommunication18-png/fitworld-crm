<?php

namespace App\Http\Requests\Host;

use App\Models\ServiceSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->host_id !== null;
    }

    public function rules(): array
    {
        $rules = [
            'service_plan_id' => ['required', 'exists:service_plans,id'],
            'instructor_id' => ['required', 'exists:instructors,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'start_time' => ['required', 'date', 'after_or_equal:now'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(array_keys(ServiceSlot::getStatuses()))],

            // Recurrence fields
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_days' => ['nullable', 'array'],
            'recurrence_days.*' => ['integer', 'between:0,6'],
            'recurrence_end_date' => ['nullable', 'date', 'after:start_time'],
        ];

        // For updates, allow updating past slots
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['start_time'] = ['required', 'date'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'start_time.after_or_equal' => 'The slot cannot be scheduled in the past.',
            'service_plan_id.exists' => 'The selected service plan is invalid.',
            'instructor_id.exists' => 'The selected instructor is invalid.',
        ];
    }
}
