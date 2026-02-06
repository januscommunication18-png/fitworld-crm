@extends('layouts.settings')

@section('title', ($isEdit ? 'Edit' : 'Add') . ' Location — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.locations.index') }}">Locations</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }} Location</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $isEdit ? 'Edit Location' : 'Add Location' }}</h1>
            <p class="text-base-content/60 text-sm">{{ $isEdit ? 'Update location details' : 'Add a new studio location' }}</p>
        </div>
        <a href="{{ route('settings.locations.index') }}" class="btn btn-ghost btn-sm">
            Back to Location Listing
        </a>
    </div>

    {{-- Form Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form
                method="POST"
                action="{{ $isEdit ? route('settings.locations.update', $location) : route('settings.locations.store') }}"
            >
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    {{-- Location Name --}}
                    <div>
                        <label class="label-text" for="name">Location Name <span class="text-error">*</span></label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            class="input w-full @error('name') input-error @enderror"
                            placeholder="e.g. Main Studio, Downtown Branch"
                            value="{{ old('name', $location->name ?? '') }}"
                            required
                        />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Line 1 --}}
                    <div>
                        <label class="label-text" for="address_line_1">Address Line 1 <span class="text-error">*</span></label>
                        <input
                            id="address_line_1"
                            name="address_line_1"
                            type="text"
                            class="input w-full @error('address_line_1') input-error @enderror"
                            placeholder="Street address"
                            value="{{ old('address_line_1', $location->address_line_1 ?? '') }}"
                            required
                        />
                        @error('address_line_1')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Line 2 --}}
                    <div>
                        <label class="label-text" for="address_line_2">Address Line 2</label>
                        <input
                            id="address_line_2"
                            name="address_line_2"
                            type="text"
                            class="input w-full"
                            placeholder="Suite, floor, etc."
                            value="{{ old('address_line_2', $location->address_line_2 ?? '') }}"
                        />
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="label-text" for="city">City <span class="text-error">*</span></label>
                        <input
                            id="city"
                            name="city"
                            type="text"
                            class="input w-full @error('city') input-error @enderror"
                            value="{{ old('city', $location->city ?? '') }}"
                            required
                        />
                        @error('city')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- State --}}
                    <div>
                        <label class="label-text" for="state">State/Region</label>
                        <input
                            id="state"
                            name="state"
                            type="text"
                            class="input w-full"
                            placeholder="e.g. TX, CA, ON"
                            value="{{ old('state', $location->state ?? '') }}"
                        />
                    </div>

                    {{-- Postal Code --}}
                    <div>
                        <label class="label-text" for="postal_code">Postal Code <span class="text-error">*</span></label>
                        <input
                            id="postal_code"
                            name="postal_code"
                            type="text"
                            class="input w-full @error('postal_code') input-error @enderror"
                            value="{{ old('postal_code', $location->postal_code ?? '') }}"
                            required
                        />
                        @error('postal_code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="label-text" for="country">Country <span class="text-error">*</span></label>
                        <select
                            id="country"
                            name="country"
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search countries...",
                                "placeholder": "Select country...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle w-full",
                                "dropdownClasses": "advance-select-menu max-h-48 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'
                            class="hidden"
                            required
                        >
                            <option value="">Select country...</option>
                            @foreach($countries as $code => $name)
                            <option value="{{ $code }}" {{ old('country', $location->country ?? 'US') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="divider text-xs text-base-content/50">Contact (Optional)</div>

                    {{-- Phone --}}
                    <div>
                        <label class="label-text" for="phone">Phone</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            class="input w-full"
                            placeholder="(555) 123-4567"
                            value="{{ old('phone', $location->phone ?? '') }}"
                        />
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="label-text" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="input w-full @error('email') input-error @enderror"
                            placeholder="location@studio.com"
                            value="{{ old('email', $location->email ?? '') }}"
                        />
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="label-text" for="notes">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="textarea w-full"
                            rows="3"
                            placeholder="Parking instructions, entry code, etc."
                        >{{ old('notes', $location->notes ?? '') }}</textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-start gap-2 pt-4 border-t border-base-200">
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update Location' : 'Add Location' }}
                        </button>
                        <a href="{{ route('settings.locations.index') }}" class="btn btn-soft btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
