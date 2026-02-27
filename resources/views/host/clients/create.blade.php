@extends('layouts.dashboard')

@section('title', $trans['clients.add_client'] ?? 'Add Client')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">{{ $trans['nav.clients'] ?? 'Clients' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['clients.add_client'] ?? 'Add Client' }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('clients.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['clients.add_new_client'] ?? 'Add New Client' }}</h1>
            <p class="text-base-content/60 text-sm mt-1">{{ $trans['clients.create_description'] ?? 'Create a new client profile for your studio' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
        @csrf

        {{-- Basic Information --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="icon-[tabler--user] size-5 mr-1"></span>
                    {{ $trans['clients.basic_information'] ?? 'Basic Information' }}
                </h5>

                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="first_name">{{ $trans['field.first_name'] ?? 'First Name' }} <span class="text-error">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                               class="input w-full" placeholder="John" required>
                        @error('first_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="last_name">{{ $trans['field.last_name'] ?? 'Last Name' }} <span class="text-error">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                               class="input w-full" placeholder="Doe" required>
                        @error('last_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="email">{{ $trans['field.email'] ?? 'Email' }} <span class="text-error">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="input w-full" placeholder="john@example.com" required>
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                               class="input w-full" placeholder="+1 (555) 000-0000">
                        @error('phone')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text">{{ $trans['field.date_of_birth'] ?? 'Date of Birth' }}</label>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <select id="dob_day" name="dob_day" class="hidden"
                                    data-select='{
                                        "placeholder": "Day",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }'>
                                    <option value="">Day</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('dob_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <select id="dob_month" name="dob_month" class="hidden"
                                    data-select='{
                                        "placeholder": "Month",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }'>
                                    <option value="">Month</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                        <option value="{{ $index + 1 }}" {{ old('dob_month') == ($index + 1) ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select id="dob_year" name="dob_year" class="hidden"
                                    data-select='{
                                        "placeholder": "Year",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }'>
                                    <option value="">Year</option>
                                    @for($i = date('Y'); $i >= 1920; $i--)
                                        <option value="{{ $i }}" {{ old('dob_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                    </div>

                    <div>
                        <label class="label-text" for="gender">{{ $trans['field.gender'] ?? 'Gender' }}</label>
                        <select id="gender" name="gender" class="hidden"
                            data-select='{
                                "placeholder": "Select gender...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select gender...</option>
                            @foreach(\App\Models\Client::getGenders() as $key => $label)
                                <option value="{{ $key }}" {{ old('gender') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status & Source --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="icon-[tabler--tag] size-5 mr-1"></span>
                    {{ $trans['clients.status_source'] ?? 'Status & Source' }}
                </h5>

                {{-- Status Selection - Radio Custom Option Cards --}}
                <div class="mb-4">
                    <label class="label-text mb-2 block">{{ $trans['clients.client_status'] ?? 'Client Status' }} <span class="text-error">*</span></label>
                    <div class="flex w-full items-start gap-3 flex-wrap sm:flex-nowrap">
                        @foreach($statuses as $key => $label)
                            @php
                                $icons = [
                                    'lead' => 'icon-[tabler--user-search]',
                                    'client' => 'icon-[tabler--user]',
                                    'member' => 'icon-[tabler--user-check]',
                                    'at_risk' => 'icon-[tabler--alert-triangle]'
                                ];
                                $icon = $icons[$key] ?? 'icon-[tabler--user]';
                            @endphp
                            <label class="custom-option text-center flex sm:w-1/4 flex-col items-center gap-2">
                                <span class="{{ $icon }} size-8"></span>
                                <span class="label-text">
                                    <span class="text-sm font-medium">{{ $label }}</span>
                                </span>
                                <input type="radio" name="status" value="{{ $key }}" class="radio radio-primary"
                                       {{ old('status', 'lead') === $key ? 'checked' : '' }} required>
                            </label>
                        @endforeach
                    </div>
                    @error('status')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="lead_source">{{ $trans['field.lead_source'] ?? 'Lead Source' }} <span class="text-error">*</span></label>
                        <select id="lead_source" name="lead_source" class="hidden" required
                            data-select='{
                                "placeholder": "Select source...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select source...</option>
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}" {{ old('lead_source', 'manual') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_source')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="referral_source">{{ $trans['field.referral_source'] ?? 'Referral Source' }}</label>
                        <input type="text" id="referral_source" name="referral_source" value="{{ old('referral_source') }}"
                               class="input w-full" placeholder="{{ $trans['clients.who_referred'] ?? 'Who referred them?' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Details (Collapsible) --}}
        <details class="card bg-base-100 group" open>
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h5 class="card-title mb-0">
                        <span class="icon-[tabler--map-pin] size-5 mr-1"></span>
                        {{ $trans['clients.contact_details'] ?? 'Contact Details' }}
                    </h5>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="secondary_phone">{{ $trans['field.secondary_phone'] ?? 'Secondary Phone' }}</label>
                        <input type="tel" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone') }}"
                               class="input w-full" placeholder="{{ $trans['clients.alternative_number'] ?? 'Alternative number' }}">
                    </div>

                    <div>
                        <label class="label-text" for="address_line_1">{{ $trans['field.address_line_1'] ?? 'Address Line 1' }}</label>
                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}"
                               class="input w-full" placeholder="{{ $trans['clients.street_address'] ?? 'Street address' }}">
                    </div>

                    <div
                        <label class="label-text" for="address_line_2">{{ $trans['field.address_line_2'] ?? 'Address Line 2' }}</label>
                        <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}"
                               class="input w-full" placeholder="{{ $trans['clients.apt_suite'] ?? 'Apt, suite, unit, etc.' }}">
                    </div>

                    <div>
                        <label class="label-text" for="city">{{ $trans['field.city'] ?? 'City' }}</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}" class="input w-full">
                    </div>

                    <div>
                        <label class="label-text" for="state_province">{{ $trans['field.state_province'] ?? 'State / Province' }}</label>
                        <input type="text" id="state_province" name="state_province" value="{{ old('state_province') }}" class="input w-full">
                    </div>

                    <div>
                        <label class="label-text" for="postal_code">{{ $trans['field.postal_code'] ?? 'Postal Code' }}</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="input w-full">
                    </div>

                    <div>
                        <label class="label-text" for="country">{{ $trans['field.country'] ?? 'Country' }}</label>
                        <input type="text" id="country" name="country" value="{{ old('country') }}" class="input w-full">
                    </div>
                </div>
            </div>
        </details>

        {{-- Communication Preferences (Collapsible) --}}
        <details class="card bg-base-100 group" open>
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h5 class="card-title mb-0">
                        <span class="icon-[tabler--mail] size-5 mr-1"></span>
                        {{ $trans['clients.communication_preferences'] ?? 'Communication Preferences' }}
                    </h5>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0 space-y-4">
                <div>
                    <label class="label-text" for="preferred_contact_method">{{ $trans['field.preferred_contact_method'] ?? 'Preferred Contact Method' }}</label>
                    <select id="preferred_contact_method" name="preferred_contact_method[]" class="hidden" multiple
                        data-select='{
                            "placeholder": "Select methods...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        @foreach(\App\Models\Client::getContactMethods() as $key => $label)
                            <option value="{{ $key }}" {{ in_array($key, old('preferred_contact_method', ['email'])) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="divider text-sm text-base-content/50">{{ $trans['clients.opt_in_preferences'] ?? 'Opt-in Preferences' }}</div>

                <div class="flex w-full flex-wrap items-start gap-3">
                    <label class="custom-option flex flex-row items-start gap-3">
                        <input type="checkbox" name="email_opt_in" value="1" class="checkbox checkbox-primary mt-1"
                               {{ old('email_opt_in', true) ? 'checked' : '' }}>
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">{{ $trans['clients.email_notifications'] ?? 'Email Notifications' }}</span>
                            <span class="text-base-content/80 block text-sm">{{ $trans['clients.email_notifications_desc'] ?? 'Booking confirmations & reminders' }}</span>
                        </span>
                    </label>

                    <label class="custom-option flex flex-row items-start gap-3">
                        <input type="checkbox" name="sms_opt_in" value="1" class="checkbox checkbox-primary mt-1"
                               {{ old('sms_opt_in') ? 'checked' : '' }}>
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">{{ $trans['clients.sms_notifications'] ?? 'SMS Notifications' }}</span>
                            <span class="text-base-content/80 block text-sm">{{ $trans['clients.sms_notifications_desc'] ?? 'Text message reminders' }}</span>
                        </span>
                    </label>

                    <label class="custom-option flex flex-row items-start gap-3">
                        <input type="checkbox" name="marketing_opt_in" value="1" class="checkbox checkbox-primary mt-1"
                               {{ old('marketing_opt_in', true) ? 'checked' : '' }}>
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">{{ $trans['clients.marketing'] ?? 'Marketing' }}</span>
                            <span class="text-base-content/80 block text-sm">{{ $trans['clients.marketing_desc'] ?? 'Promotions & newsletter' }}</span>
                        </span>
                    </label>
                </div>
            </div>
        </details>

        {{-- Emergency Contact (Collapsible) --}}
        <details class="card bg-base-100 group" open>
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h5 class="card-title mb-0">
                        <span class="icon-[tabler--emergency-bed] size-5 mr-1"></span>
                        {{ $trans['clients.emergency_contact'] ?? 'Emergency Contact' }}
                    </h5>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="emergency_contact_name">{{ $trans['field.contact_name'] ?? 'Contact Name' }}</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                               class="input w-full" placeholder="{{ $trans['clients.full_name'] ?? 'Full name' }}">
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_relationship">{{ $trans['field.relationship'] ?? 'Relationship' }}</label>
                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}"
                               class="input w-full" placeholder="{{ $trans['clients.relationship_example'] ?? 'e.g., Spouse, Parent' }}">
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                               class="input w-full" placeholder="+1 (555) 000-0000">
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                        <input type="email" id="emergency_contact_email" name="emergency_contact_email" value="{{ old('emergency_contact_email') }}"
                               class="input w-full" placeholder="email@example.com">
                    </div>
                </div>
            </div>
        </details>

        {{-- Health & Fitness (Collapsible) --}}
        <details class="card bg-base-100 group" open>
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h5 class="card-title mb-0">
                        <span class="icon-[tabler--heartbeat] size-5 mr-1"></span>
                        {{ $trans['clients.health_fitness'] ?? 'Health & Fitness' }}
                    </h5>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="experience_level">{{ $trans['field.experience_level'] ?? 'Experience Level' }}</label>
                        <select id="experience_level" name="experience_level" class="hidden"
                            data-select='{
                                "placeholder": "Select level...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select level...</option>
                            @foreach(\App\Models\Client::getExperienceLevels() as $key => $label)
                                <option value="{{ $key }}" {{ old('experience_level') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="fitness_goals">{{ $trans['field.fitness_goals'] ?? 'Fitness Goals' }}</label>
                        <textarea id="fitness_goals" name="fitness_goals" rows="2" class="textarea textarea-bordered w-full"
                                  placeholder="{{ $trans['clients.fitness_goals_placeholder'] ?? 'What are their fitness goals?' }}">{{ old('fitness_goals') }}</textarea>
                    </div>

                    <div>
                        <label class="label-text" for="medical_conditions">{{ $trans['field.medical_conditions'] ?? 'Medical Conditions' }}</label>
                        <textarea id="medical_conditions" name="medical_conditions" rows="2" class="textarea textarea-bordered w-full"
                                  placeholder="{{ $trans['clients.medical_conditions_placeholder'] ?? 'Any medical conditions to be aware of?' }}">{{ old('medical_conditions') }}</textarea>
                    </div>

                    <div>
                        <label class="label-text" for="injuries">{{ $trans['field.injuries'] ?? 'Injuries' }}</label>
                        <textarea id="injuries" name="injuries" rows="2" class="textarea textarea-bordered w-full"
                                  placeholder="{{ $trans['clients.injuries_placeholder'] ?? 'Past or current injuries' }}">{{ old('injuries') }}</textarea>
                    </div>

                    <div>
                        <label class="label-text" for="limitations">{{ $trans['field.limitations'] ?? 'Limitations' }}</label>
                        <textarea id="limitations" name="limitations" rows="2" class="textarea textarea-bordered w-full"
                                  placeholder="{{ $trans['clients.limitations_placeholder'] ?? 'Physical limitations' }}">{{ old('limitations') }}</textarea>
                    </div>

                    <div>
                        <label class="custom-option flex flex-row items-start gap-3">
                            <input type="checkbox" name="pregnancy_status" value="1" class="checkbox checkbox-warning mt-1"
                                   {{ old('pregnancy_status') ? 'checked' : '' }}>
                            <span class="label-text w-full text-start">
                                <span class="text-base font-medium">{{ $trans['clients.currently_pregnant'] ?? 'Currently Pregnant' }}</span>
                                <span class="text-base-content/80 block text-sm">{{ $trans['clients.pregnancy_considerations'] ?? 'Special considerations apply' }}</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </details>

        {{-- Marketing/UTM (Collapsible) --}}
        <details class="card bg-base-100 group" open>
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h5 class="card-title mb-0">
                        <span class="icon-[tabler--chart-bar] size-5 mr-1"></span>
                        {{ $trans['clients.marketing_tracking'] ?? 'Marketing Tracking (UTM)' }}
                    </h5>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="space-y-4">
                    <div
                        <label class="label-text" for="source_url">{{ $trans['field.source_url'] ?? 'Source URL' }}</label>
                        <input type="url" id="source_url" name="source_url" value="{{ old('source_url') }}"
                               class="input w-full" placeholder="https://...">
                    </div>

                    <div>
                        <label class="label-text" for="utm_source">{{ $trans['field.utm_source'] ?? 'UTM Source' }}</label>
                        <input type="text" id="utm_source" name="utm_source" value="{{ old('utm_source') }}"
                               class="input w-full" placeholder="google, facebook">
                    </div>

                    <div>
                        <label class="label-text" for="utm_medium">{{ $trans['field.utm_medium'] ?? 'UTM Medium' }}</label>
                        <input type="text" id="utm_medium" name="utm_medium" value="{{ old('utm_medium') }}"
                               class="input w-full" placeholder="cpc, email">
                    </div>

                    <div>
                        <label class="label-text" for="utm_campaign">{{ $trans['field.utm_campaign'] ?? 'UTM Campaign' }}</label>
                        <input type="text" id="utm_campaign" name="utm_campaign" value="{{ old('utm_campaign') }}"
                               class="input w-full" placeholder="{{ $trans['clients.campaign_name'] ?? 'Campaign name' }}">
                    </div>

                    <div>
                        <label class="label-text" for="utm_term">{{ $trans['field.utm_term'] ?? 'UTM Term' }}</label>
                        <input type="text" id="utm_term" name="utm_term" value="{{ old('utm_term') }}"
                               class="input w-full" placeholder="{{ $trans['common.keywords'] ?? 'Keywords' }}">
                    </div>

                    <div>
                        <label class="label-text" for="utm_content">{{ $trans['field.utm_content'] ?? 'UTM Content' }}</label>
                        <input type="text" id="utm_content" name="utm_content" value="{{ old('utm_content') }}"
                               class="input w-full" placeholder="{{ $trans['clients.ad_content'] ?? 'Ad content' }}">
                    </div>
                </div>
            </div>
        </details>

        {{-- Tags --}}
        @if($tags->count() > 0)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="icon-[tabler--tags] size-5 mr-1"></span>
                    {{ $trans['field.tags'] ?? 'Tags' }}
                </h5>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   class="peer hidden"
                                   {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                            <span class="badge badge-lg peer-checked:ring-2 peer-checked:ring-primary transition-all"
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40;">
                                {{ $tag->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Custom Fields --}}
        @if($customFields['sections']->count() > 0 || $customFields['unsectionedFields']->count() > 0)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="icon-[tabler--forms] size-5 mr-1"></span>
                    {{ $trans['clients.additional_information'] ?? 'Additional Information' }}
                </h5>

                {{-- Unsectioned Fields --}}
                @if($customFields['unsectionedFields']->count() > 0)
                <div class="space-y-4 mb-6">
                    @foreach($customFields['unsectionedFields'] as $field)
                        @include('host.clients._custom-field-input', ['field' => $field, 'values' => $customFields['values']])
                    @endforeach
                </div>
                @endif

                {{-- Sectioned Fields --}}
                @foreach($customFields['sections'] as $section)
                    @if($section->activeFieldDefinitions->count() > 0)
                    <div class="mb-6 last:mb-0">
                        <h3 class="font-semibold text-sm text-base-content/70 uppercase tracking-wider mb-3">{{ $section->name }}</h3>
                        <div class="space-y-4">
                            @foreach($section->activeFieldDefinitions as $field)
                                @include('host.clients._custom-field-input', ['field' => $field, 'values' => $customFields['values']])
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Internal Notes --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="icon-[tabler--notes] size-5 mr-1"></span>
                    {{ $trans['clients.internal_notes'] ?? 'Internal Notes' }}
                </h5>
                <textarea id="notes" name="notes" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="{{ $trans['clients.internal_notes_placeholder'] ?? 'Add any internal notes about this client...' }}">{{ old('notes') }}</textarea>
                <p class="text-base-content/50 text-sm mt-2">{{ $trans['clients.notes_visibility'] ?? 'These notes are only visible to staff members.' }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('clients.index') }}" class="btn btn-soft btn-secondary">{{ $trans['btn.cancel'] ?? 'Cancel' }}</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $trans['btn.create_client'] ?? 'Create Client' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Combine date of birth dropdowns into hidden field
    const dobDay = document.getElementById('dob_day');
    const dobMonth = document.getElementById('dob_month');
    const dobYear = document.getElementById('dob_year');
    const dobHidden = document.getElementById('date_of_birth');

    function updateDob() {
        if (dobDay.value && dobMonth.value && dobYear.value) {
            const month = dobMonth.value.toString().padStart(2, '0');
            const day = dobDay.value.toString().padStart(2, '0');
            dobHidden.value = `${dobYear.value}-${month}-${day}`;
        } else {
            dobHidden.value = '';
        }
    }

    dobDay.addEventListener('change', updateDob);
    dobMonth.addEventListener('change', updateDob);
    dobYear.addEventListener('change', updateDob);

    // Initialize from existing value if present
    if (dobHidden.value) {
        const parts = dobHidden.value.split('-');
        if (parts.length === 3) {
            dobYear.value = parts[0];
            dobMonth.value = parseInt(parts[1]);
            dobDay.value = parseInt(parts[2]);
        }
    }
});
</script>
@endpush
