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
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['clients.add_new_client'] ?? 'Add New Client' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['clients.create_description'] ?? 'Create a new client profile for your studio' }}</p>
        </div>
        <a href="{{ route('clients.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            {{ $trans['btn.back'] ?? 'Back' }}
        </a>
    </div>

    <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
        @csrf

        {{-- Card 1: Basic Information --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">1</span>
                    <h3 class="card-title">{{ $trans['clients.basic_information'] ?? 'Basic Information' }}</h3>
                </div>
            </div>
            <div class="card-body">
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
                        <x-phone-input name="phone" :value="old('phone')" label="{{ $trans['field.phone'] ?? 'Phone' }}" id-suffix="client-create" />
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

        {{-- Card 2: Status & Source --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                    <h3 class="card-title">{{ $trans['clients.status_source'] ?? 'Status & Source' }}</h3>
                </div>
            </div>
            <div class="card-body">
                {{-- Status Selection --}}
                <div class="mb-4">
                    <label class="label-text" for="status">{{ $trans['clients.client_status'] ?? 'Client Status' }} <span class="text-error">*</span></label>
                    <x-studio-select
                        name="status"
                        :options="$statuses"
                        :selected="old('status', \App\Models\Client::STATUS_ACTIVE)"
                        placeholder="Select status..."
                        :required="true"
                        id="status"
                    />
                    <p class="text-xs text-base-content/60 mt-1">{{ $trans['clients.status_helper'] ?? 'Track the current engagement level of this client with your studio.' }}</p>
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

        {{-- Card 3: Contact Details --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">3</span>
                    <h3 class="card-title">{{ $trans['clients.contact_details'] ?? 'Contact Details' }}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div>
                        <x-phone-input name="secondary_phone" :value="old('secondary_phone')" label="{{ $trans['field.secondary_phone'] ?? 'Secondary Phone' }}" id-suffix="client-create-secondary" />
                    </div>

                    {{-- Quick Address Search & Validate --}}
                    <div class="relative" id="client-address-search-wrapper">
                        <label class="label-text font-medium" for="client-address-search">
                            <span class="icon-[tabler--search] size-4 mr-1"></span>
                            {{ $trans['common.quick_address_search'] ?? 'Quick Address Search' }}
                        </label>
                        <div class="flex gap-2 mt-1">
                            <div class="relative flex-1">
                                <input type="text" id="client-address-search" class="input w-full pr-10" placeholder="{{ $trans['common.address_search_placeholder'] ?? 'Search address, city, or zip code...' }}" autocomplete="off" />
                                <span id="client-search-loading" class="loading loading-spinner loading-xs absolute top-1/2 right-3 -translate-y-1/2 text-primary hidden"></span>
                            </div>
                            <button type="button" id="client-validate-btn" class="btn btn-outline btn-primary shrink-0" onclick="validateClientAddress()">
                                <span class="icon-[tabler--check] size-4"></span> {{ $trans['btn.validate'] ?? 'Validate' }}
                            </button>
                        </div>
                        <div id="client-address-suggestions" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-y-auto hidden"></div>
                        <div id="client-validation-msg" class="mt-2 hidden"></div>
                    </div>

                    <div>
                        <label class="label-text" for="address_line_1">{{ $trans['field.address_line_1'] ?? 'Address Line 1' }}</label>
                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}"
                               class="input w-full" placeholder="{{ $trans['clients.street_address'] ?? 'Street address' }}">
                    </div>

                    <div>
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
        </div>

        {{-- Card 4: Communication Preferences --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">4</span>
                    <h3 class="card-title">{{ $trans['clients.communication_preferences'] ?? 'Communication Preferences' }}</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
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
        </div>

        {{-- Card 5: Emergency Contact --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                    <h3 class="card-title">{{ $trans['clients.emergency_contact'] ?? 'Emergency Contact' }}</h3>
                </div>
            </div>
            <div class="card-body">
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
                        <x-phone-input name="emergency_contact_phone" :value="old('emergency_contact_phone')" label="{{ $trans['field.phone'] ?? 'Phone' }}" id-suffix="client-create-emergency" />
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                        <input type="email" id="emergency_contact_email" name="emergency_contact_email" value="{{ old('emergency_contact_email') }}"
                               class="input w-full" placeholder="email@example.com">
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 6: Health & Fitness --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">6</span>
                    <h3 class="card-title">{{ $trans['clients.health_fitness'] ?? 'Health & Fitness' }}</h3>
                </div>
            </div>
            <div class="card-body">
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
        </div>

        {{-- Card 7: Marketing Tracking (UTM) — temporarily hidden --}}
        @if(false)
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">7</span>
                    <h3 class="card-title">{{ $trans['clients.marketing_tracking'] ?? 'Marketing Tracking (UTM)' }}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div>
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
        </div>
        @endif

        {{-- Card 8: Tags --}}
        @if($tags->count() > 0)
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center justify-between gap-3 w-full">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">8</span>
                        <h3 class="card-title">{{ $trans['field.tags'] ?? 'Tags' }}</h3>
                    </div>
                    <p class="text-sm text-base-content/60 text-right">{{ $trans['clients.tags_multi_helper'] ?? 'You can add multiple tags — click each one to apply.' }}</p>
                </div>
            </div>
            <div class="card-body">
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

        {{-- Card 9: Additional Information (Custom Fields) --}}
        @if($customFields['sections']->count() > 0 || $customFields['unsectionedFields']->count() > 0)
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">9</span>
                    <h3 class="card-title">{{ $trans['clients.additional_information'] ?? 'Additional Information' }}</h3>
                </div>
            </div>
            <div class="card-body">
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

        {{-- Card 10: Internal Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">10</span>
                    <h3 class="card-title">{{ $trans['clients.internal_notes'] ?? 'Internal Notes' }}</h3>
                </div>
            </div>
            <div class="card-body">
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

    // ---------- Quick Address Search (autocomplete) ----------
    var addrSearchInput = document.getElementById('client-address-search');
    var addrSuggestions = document.getElementById('client-address-suggestions');
    var addrLoading = document.getElementById('client-search-loading');
    var addrSearchTimer;

    if (addrSearchInput) {
        addrSearchInput.addEventListener('input', function() {
            clearTimeout(addrSearchTimer);
            var query = this.value.trim();

            if (query.length < 3) {
                addrSuggestions.classList.add('hidden');
                return;
            }

            addrLoading.classList.remove('hidden');

            addrSearchTimer = setTimeout(function() {
                fetch('/api/v1/address/autocomplete?q=' + encodeURIComponent(query))
                    .then(function(r) { return r.json(); })
                    .then(function(results) {
                        addrLoading.classList.add('hidden');

                        if (!results || results.length === 0) {
                            addrSuggestions.innerHTML = '<div class="px-4 py-3 text-base-content/50 text-sm">No addresses found.</div>';
                            addrSuggestions.classList.remove('hidden');
                            return;
                        }

                        addrSuggestions.innerHTML = results.map(function(r, i) {
                            return '<div class="client-addr-sug px-4 py-3 hover:bg-base-200 cursor-pointer border-b border-base-200 last:border-b-0" data-idx="' + i + '">' +
                                '<div class="font-medium text-sm">' + (r.label || r.street_line || '') + '</div>' +
                                (r.street_line ? '<div class="text-xs text-base-content/60">' + (r.city || '') + ', ' + (r.state || '') + ' ' + (r.zipcode || '') + '</div>' : '') +
                            '</div>';
                        }).join('');
                        addrSuggestions.classList.remove('hidden');

                        addrSuggestions.querySelectorAll('.client-addr-sug').forEach(function(item) {
                            item.addEventListener('click', function() {
                                var idx = parseInt(this.dataset.idx);
                                applyClientAddress(results[idx]);
                                addrSearchInput.value = '';
                                addrSuggestions.classList.add('hidden');
                            });
                        });
                    })
                    .catch(function() {
                        addrLoading.classList.add('hidden');
                        addrSuggestions.classList.add('hidden');
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!addrSearchInput.contains(e.target) && !addrSuggestions.contains(e.target)) {
                addrSuggestions.classList.add('hidden');
            }
        });
    }
});

// Apply a chosen address suggestion into the client form fields.
function applyClientAddress(result) {
    if (!result) return;
    if (result.street_line) document.getElementById('address_line_1').value = result.street_line;
    if (result.city) document.getElementById('city').value = result.city;
    if (result.state_name) document.getElementById('state_province').value = result.state_name;
    else if (result.state) document.getElementById('state_province').value = result.state;
    if (result.zipcode) document.getElementById('postal_code').value = result.zipcode;
    if (result.country) document.getElementById('country').value = result.country;
}

// Validate the typed-in address via the server.
function validateClientAddress() {
    var street = (document.getElementById('address_line_1')?.value || '').trim();
    var city = (document.getElementById('city')?.value || '').trim();
    var state = (document.getElementById('state_province')?.value || '').trim();
    var zipcode = (document.getElementById('postal_code')?.value || '').trim();
    var msgDiv = document.getElementById('client-validation-msg');
    var btn = document.getElementById('client-validate-btn');

    if (!street && !city && !zipcode) {
        msgDiv.innerHTML = '<div class="alert alert-warning alert-sm"><span class="icon-[tabler--alert-triangle] size-4"></span><span class="text-sm">Enter an address first</span></div>';
        msgDiv.classList.remove('hidden');
        setTimeout(function() { msgDiv.classList.add('hidden'); }, 3000);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Validating...';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

    fetch('/api/v1/address/validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ street: street, city: city, state: state, zipcode: zipcode })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.valid) {
            if (result.street) document.getElementById('address_line_1').value = result.street;
            if (result.city) document.getElementById('city').value = result.city;
            if (result.state_name) document.getElementById('state_province').value = result.state_name;
            else if (result.state) document.getElementById('state_province').value = result.state;
            if (result.zipcode) document.getElementById('postal_code').value = result.zipcode;

            msgDiv.innerHTML = '<div class="alert alert-success alert-sm"><span class="icon-[tabler--check] size-4"></span><span class="text-sm">Address validated and updated</span></div>';
        } else {
            msgDiv.innerHTML = '<div class="alert alert-error alert-sm"><span class="icon-[tabler--x] size-4"></span><span class="text-sm">Could not validate this address</span></div>';
        }
        msgDiv.classList.remove('hidden');
        setTimeout(function() { msgDiv.classList.add('hidden'); }, 5000);
    })
    .catch(function() {
        msgDiv.innerHTML = '<div class="alert alert-error alert-sm"><span class="icon-[tabler--x] size-4"></span><span class="text-sm">Validation failed</span></div>';
        msgDiv.classList.remove('hidden');
        setTimeout(function() { msgDiv.classList.add('hidden'); }, 5000);
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Validate';
    });
}
</script>
@endpush
