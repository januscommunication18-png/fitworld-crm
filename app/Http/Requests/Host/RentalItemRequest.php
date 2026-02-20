<?php

namespace App\Http\Requests\Host;

use App\Models\RentalItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RentalItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', Rule::in(array_keys(RentalItem::getCategories()))],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0'],
            'deposit_prices' => ['nullable', 'array'],
            'deposit_prices.*' => ['nullable', 'numeric', 'min:0'],
            'total_inventory' => ['required', 'integer', 'min:0'],
            'requires_return' => ['nullable', 'boolean'],
            'max_rental_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['string'],
            'class_plan_ids' => ['nullable', 'array'],
            'class_plan_ids.*' => ['exists:class_plans,id'],
            'required_class_plan_ids' => ['nullable', 'array'],
            'required_class_plan_ids.*' => ['exists:class_plans,id'],
            'eligibility_type' => ['nullable', 'string', Rule::in(['all', 'membership', 'class_pack'])],
            'eligible_membership_ids' => ['nullable', 'array'],
            'eligible_membership_ids.*' => ['exists:membership_plans,id'],
            'free_membership_ids' => ['nullable', 'array'],
            'free_membership_ids.*' => ['exists:membership_plans,id'],
            'eligible_class_pack_ids' => ['nullable', 'array'],
            'eligible_class_pack_ids.*' => ['exists:class_packs,id'],
            'free_class_pack_ids' => ['nullable', 'array'],
            'free_class_pack_ids.*' => ['exists:class_packs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name for this rental item.',
            'total_inventory.required' => 'Please enter the total inventory count.',
            'total_inventory.min' => 'Inventory cannot be negative.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.max' => 'Each image must be less than 2MB.',
        ];
    }
}
