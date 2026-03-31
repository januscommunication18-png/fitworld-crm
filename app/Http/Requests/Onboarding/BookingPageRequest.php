<?php

namespace App\Http\Requests\Onboarding;

use App\Models\Host;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_page_status' => [
                'required',
                Rule::in([Host::BOOKING_PAGE_DRAFT, Host::BOOKING_PAGE_PUBLISHED]),
            ],
        ];
    }
}
