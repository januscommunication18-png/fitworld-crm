@extends('layouts.dashboard')

@section('title', ($trans['btn.edit'] ?? 'Edit') . ' ' . $client->full_name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">{{ $trans['nav.clients'] ?? 'Clients' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.show', $client) }}">{{ $client->full_name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['btn.edit'] ?? 'Edit' }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <span class="icon-[tabler--user-edit] size-7"></span>
                {{ $trans['clients.edit_client'] ?? 'Edit Client' }}
            </h1>
            <p class="text-base-content/60 text-sm mt-1">{{ $trans['clients.update_information'] ?? 'Update' }} {{ $client->full_name }}{{ $trans['clients.information_suffix'] ?? "'s information" }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--user] size-5"></span>
                    {{ $trans['clients.basic_information'] ?? 'Basic Information' }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="first_name">{{ $trans['field.first_name'] ?? 'First Name' }} <span class="text-error">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $client->first_name) }}"
                               class="input input-bordered w-full @error('first_name') input-error @enderror" required>
                        @error('first_name')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="last_name">{{ $trans['field.last_name'] ?? 'Last Name' }} <span class="text-error">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $client->last_name) }}"
                               class="input input-bordered w-full @error('last_name') input-error @enderror" required>
                        @error('last_name')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="email">{{ $trans['field.email'] ?? 'Email' }} <span class="text-error">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $client->email) }}"
                               class="input input-bordered w-full @error('email') input-error @enderror" required>
                        @error('email')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $client->phone) }}"
                               class="input input-bordered w-full @error('phone') input-error @enderror">
                        @error('phone')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="secondary_phone">{{ $trans['field.secondary_phone'] ?? 'Secondary Phone' }}</label>
                        <input type="tel" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone', $client->secondary_phone) }}"
                               class="input input-bordered w-full @error('secondary_phone') input-error @enderror">
                        @error('secondary_phone')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="date_of_birth">{{ $trans['field.date_of_birth'] ?? 'Date of Birth' }}</label>
                        <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $client->date_of_birth?->format('Y-m-d')) }}"
                               class="input input-bordered w-full flatpickr-date @error('date_of_birth') input-error @enderror" placeholder="Select date...">
                        @error('date_of_birth')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="gender">{{ $trans['field.gender'] ?? 'Gender' }}</label>
                        <select id="gender" name="gender" class="select select-bordered w-full @error('gender') select-error @enderror">
                            <option value="">Select gender...</option>
                            @foreach($genders as $key => $label)
                                <option value="{{ $key }}" {{ old('gender', $client->gender) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Status & Source --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--tag] size-5"></span>
                    {{ $trans['clients.status_source'] ?? 'Status & Source' }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="status">{{ $trans['common.status'] ?? 'Status' }} <span class="text-error">*</span></label>
                        <select id="status" name="status" class="select select-bordered w-full @error('status') select-error @enderror" required>
                            <option value="">Select status...</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $client->status) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="membership_status">{{ $trans['clients.membership_status'] ?? 'Membership Status' }}</label>
                        <select id="membership_status" name="membership_status" class="select select-bordered w-full @error('membership_status') select-error @enderror">
                            @foreach($membershipStatuses as $key => $label)
                                <option value="{{ $key }}" {{ old('membership_status', $client->membership_status) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('membership_status')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="lead_source">{{ $trans['field.source'] ?? 'Source' }} <span class="text-error">*</span></label>
                        <select id="lead_source" name="lead_source" class="select select-bordered w-full @error('lead_source') select-error @enderror" required>
                            <option value="">Select source...</option>
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}" {{ old('lead_source', $client->lead_source) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_source')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="referral_source">{{ $trans['field.referral_source'] ?? 'Referral Source' }}</label>
                        <input type="text" id="referral_source" name="referral_source" value="{{ old('referral_source', $client->referral_source) }}"
                               class="input input-bordered w-full @error('referral_source') input-error @enderror"
                               placeholder="{{ $trans['clients.who_referred'] ?? 'Who referred them?' }}">
                        @error('referral_source')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="membership_start_date">{{ $trans['clients.membership_start_date'] ?? 'Membership Start Date' }}</label>
                        <input type="text" id="membership_start_date" name="membership_start_date" value="{{ old('membership_start_date', $client->membership_start_date?->format('Y-m-d')) }}"
                               class="input input-bordered w-full flatpickr-date @error('membership_start_date') input-error @enderror" placeholder="Select date...">
                        @error('membership_start_date')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="membership_end_date">{{ $trans['clients.membership_end_date'] ?? 'Membership End Date' }}</label>
                        <input type="text" id="membership_end_date" name="membership_end_date" value="{{ old('membership_end_date', $client->membership_end_date?->format('Y-m-d')) }}"
                               class="input input-bordered w-full flatpickr-date @error('membership_end_date') input-error @enderror" placeholder="Select date...">
                        @error('membership_end_date')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Details (Collapsible) --}}
        <details class="card bg-base-100 group">
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--map-pin] size-5"></span>
                        {{ $trans['clients.contact_details'] ?? 'Contact Details' }}
                    </h2>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label-text" for="address_line_1">{{ $trans['field.address_line_1'] ?? 'Address Line 1' }}</label>
                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $client->address_line_1) }}"
                               class="input input-bordered w-full @error('address_line_1') input-error @enderror"
                               placeholder="Street address">
                        @error('address_line_1')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-text" for="address_line_2">{{ $trans['field.address_line_2'] ?? 'Address Line 2' }}</label>
                        <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $client->address_line_2) }}"
                               class="input input-bordered w-full @error('address_line_2') input-error @enderror"
                               placeholder="Apartment, suite, etc.">
                        @error('address_line_2')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="city">{{ $trans['field.city'] ?? 'City' }}</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $client->city) }}"
                               class="input input-bordered w-full @error('city') input-error @enderror">
                        @error('city')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="state_province">{{ $trans['field.state_province'] ?? 'State/Province' }}</label>
                        <input type="text" id="state_province" name="state_province" value="{{ old('state_province', $client->state_province) }}"
                               class="input input-bordered w-full @error('state_province') input-error @enderror">
                        @error('state_province')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="postal_code">{{ $trans['field.postal_code'] ?? 'Postal Code' }}</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $client->postal_code) }}"
                               class="input input-bordered w-full @error('postal_code') input-error @enderror">
                        @error('postal_code')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="country">{{ $trans['field.country'] ?? 'Country' }}</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $client->country) }}"
                               class="input input-bordered w-full @error('country') input-error @enderror">
                        @error('country')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </details>

        {{-- Communication Preferences (Collapsible) --}}
        <details class="card bg-base-100 group">
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--mail] size-5"></span>
                        {{ $trans['clients.communication_preferences'] ?? 'Communication Preferences' }}
                    </h2>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="preferred_contact_method">{{ $trans['field.preferred_contact_method'] ?? 'Preferred Contact Method' }}</label>
                        <select id="preferred_contact_method" name="preferred_contact_method" class="select select-bordered w-full @error('preferred_contact_method') select-error @enderror">
                            @foreach($contactMethods as $key => $label)
                                <option value="{{ $key }}" {{ old('preferred_contact_method', $client->preferred_contact_method) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('preferred_contact_method')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex flex-wrap gap-6 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="email_opt_in" value="0">
                                <input type="checkbox" name="email_opt_in" value="1" class="checkbox checkbox-primary checkbox-sm"
                                       {{ old('email_opt_in', $client->email_opt_in) ? 'checked' : '' }}>
                                <span class="label-text">{{ $trans['clients.email_opt_in'] ?? 'Email Opt-in' }}</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="sms_opt_in" value="0">
                                <input type="checkbox" name="sms_opt_in" value="1" class="checkbox checkbox-primary checkbox-sm"
                                       {{ old('sms_opt_in', $client->sms_opt_in) ? 'checked' : '' }}>
                                <span class="label-text">{{ $trans['clients.sms_opt_in'] ?? 'SMS Opt-in' }}</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="marketing_opt_in" value="0">
                                <input type="checkbox" name="marketing_opt_in" value="1" class="checkbox checkbox-primary checkbox-sm"
                                       {{ old('marketing_opt_in', $client->marketing_opt_in) ? 'checked' : '' }}>
                                <span class="label-text">{{ $trans['clients.marketing_opt_in'] ?? 'Marketing Opt-in' }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </details>

        {{-- Emergency Contact (Collapsible) --}}
        <details class="card bg-base-100 group">
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--emergency-bed] size-5"></span>
                        {{ $trans['clients.emergency_contact'] ?? 'Emergency Contact' }}
                    </h2>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="emergency_contact_name">{{ $trans['field.contact_name'] ?? 'Contact Name' }}</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $client->emergency_contact_name) }}"
                               class="input input-bordered w-full @error('emergency_contact_name') input-error @enderror">
                        @error('emergency_contact_name')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_relationship">{{ $trans['field.relationship'] ?? 'Relationship' }}</label>
                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $client->emergency_contact_relationship) }}"
                               class="input input-bordered w-full @error('emergency_contact_relationship') input-error @enderror"
                               placeholder="{{ $trans['clients.relationship_example'] ?? 'e.g., Spouse, Parent, Friend' }}">
                        @error('emergency_contact_relationship')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_phone">{{ $trans['field.contact_phone'] ?? 'Contact Phone' }}</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $client->emergency_contact_phone) }}"
                               class="input input-bordered w-full @error('emergency_contact_phone') input-error @enderror">
                        @error('emergency_contact_phone')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="emergency_contact_email">{{ $trans['field.contact_email'] ?? 'Contact Email' }}</label>
                        <input type="email" id="emergency_contact_email" name="emergency_contact_email" value="{{ old('emergency_contact_email', $client->emergency_contact_email) }}"
                               class="input input-bordered w-full @error('emergency_contact_email') input-error @enderror">
                        @error('emergency_contact_email')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </details>

        {{-- Health & Fitness (Collapsible) --}}
        <details class="card bg-base-100 group">
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--heartbeat] size-5"></span>
                        {{ $trans['clients.health_fitness'] ?? 'Health & Fitness' }}
                    </h2>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="experience_level">{{ $trans['field.experience_level'] ?? 'Experience Level' }}</label>
                        <select id="experience_level" name="experience_level" class="select select-bordered w-full @error('experience_level') select-error @enderror">
                            <option value="">Select level...</option>
                            @foreach($experienceLevels as $key => $label)
                                <option value="{{ $key }}" {{ old('experience_level', $client->experience_level) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('experience_level')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer mt-6">
                            <input type="hidden" name="pregnancy_status" value="0">
                            <input type="checkbox" name="pregnancy_status" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('pregnancy_status', $client->pregnancy_status) ? 'checked' : '' }}>
                            <span class="label-text">{{ $trans['clients.currently_pregnant'] ?? 'Currently Pregnant' }}</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-text" for="fitness_goals">{{ $trans['field.fitness_goals'] ?? 'Fitness Goals' }}</label>
                        <textarea id="fitness_goals" name="fitness_goals" rows="2"
                                  class="textarea textarea-bordered w-full @error('fitness_goals') textarea-error @enderror"
                                  placeholder="{{ $trans['clients.fitness_goals_placeholder'] ?? 'What are your fitness goals?' }}">{{ old('fitness_goals', $client->fitness_goals) }}</textarea>
                        @error('fitness_goals')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-text" for="medical_conditions">{{ $trans['field.medical_conditions'] ?? 'Medical Conditions' }}</label>
                        <textarea id="medical_conditions" name="medical_conditions" rows="2"
                                  class="textarea textarea-bordered w-full @error('medical_conditions') textarea-error @enderror"
                                  placeholder="{{ $trans['clients.medical_conditions_placeholder'] ?? 'Any medical conditions we should know about?' }}">{{ old('medical_conditions', $client->medical_conditions) }}</textarea>
                        @error('medical_conditions')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-text" for="injuries">{{ $trans['field.injuries'] ?? 'Injuries' }}</label>
                        <textarea id="injuries" name="injuries" rows="2"
                                  class="textarea textarea-bordered w-full @error('injuries') textarea-error @enderror"
                                  placeholder="{{ $trans['clients.injuries_placeholder'] ?? 'Any current or past injuries?' }}">{{ old('injuries', $client->injuries) }}</textarea>
                        @error('injuries')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-text" for="limitations">{{ $trans['field.limitations'] ?? 'Limitations' }}</label>
                        <textarea id="limitations" name="limitations" rows="2"
                                  class="textarea textarea-bordered w-full @error('limitations') textarea-error @enderror"
                                  placeholder="{{ $trans['clients.limitations_placeholder'] ?? 'Any physical limitations?' }}">{{ old('limitations', $client->limitations) }}</textarea>
                        @error('limitations')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </details>

        {{-- Marketing Tracking (Collapsible) --}}
        <details class="card bg-base-100 group">
            <summary class="card-body cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--chart-bar] size-5"></span>
                        {{ $trans['clients.marketing_tracking'] ?? 'Marketing Tracking (UTM)' }}
                    </h2>
                    <span class="icon-[tabler--chevron-down] size-5 transition-transform group-open:rotate-180"></span>
                </div>
            </summary>
            <div class="card-body pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label-text" for="source_url">{{ $trans['field.source_url'] ?? 'Source URL' }}</label>
                        <input type="text" id="source_url" name="source_url" value="{{ old('source_url', $client->source_url) }}"
                               class="input input-bordered w-full @error('source_url') input-error @enderror"
                               placeholder="https://...">
                        @error('source_url')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="utm_source">{{ $trans['field.utm_source'] ?? 'UTM Source' }}</label>
                        <input type="text" id="utm_source" name="utm_source" value="{{ old('utm_source', $client->utm_source) }}"
                               class="input input-bordered w-full @error('utm_source') input-error @enderror"
                               placeholder="e.g., google, facebook">
                        @error('utm_source')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="utm_medium">{{ $trans['field.utm_medium'] ?? 'UTM Medium' }}</label>
                        <input type="text" id="utm_medium" name="utm_medium" value="{{ old('utm_medium', $client->utm_medium) }}"
                               class="input input-bordered w-full @error('utm_medium') input-error @enderror"
                               placeholder="e.g., cpc, email">
                        @error('utm_medium')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="utm_campaign">{{ $trans['field.utm_campaign'] ?? 'UTM Campaign' }}</label>
                        <input type="text" id="utm_campaign" name="utm_campaign" value="{{ old('utm_campaign', $client->utm_campaign) }}"
                               class="input input-bordered w-full @error('utm_campaign') input-error @enderror"
                               placeholder="e.g., summer_promo">
                        @error('utm_campaign')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="utm_term">{{ $trans['field.utm_term'] ?? 'UTM Term' }}</label>
                        <input type="text" id="utm_term" name="utm_term" value="{{ old('utm_term', $client->utm_term) }}"
                               class="input input-bordered w-full @error('utm_term') input-error @enderror"
                               placeholder="Keywords">
                        @error('utm_term')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="utm_content">{{ $trans['field.utm_content'] ?? 'UTM Content' }}</label>
                        <input type="text" id="utm_content" name="utm_content" value="{{ old('utm_content', $client->utm_content) }}"
                               class="input input-bordered w-full @error('utm_content') input-error @enderror"
                               placeholder="Ad variant">
                        @error('utm_content')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </details>

        {{-- Tags --}}
        @if($tags->count() > 0)
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--tags] size-5"></span>
                    {{ $trans['field.tags'] ?? 'Tags' }}
                </h2>
                @php
                    $clientTagIds = $client->tags->pluck('id')->toArray();
                @endphp
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   class="peer hidden"
                                   {{ in_array($tag->id, old('tags', $clientTagIds)) ? 'checked' : '' }}>
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
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--forms] size-5"></span>
                    {{ $trans['clients.custom_fields'] ?? 'Custom Fields' }}
                </h2>

                {{-- Unsectioned Fields --}}
                @if($customFields['unsectionedFields']->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--note] size-5"></span>
                    {{ $trans['clients.internal_notes'] ?? 'Internal Notes' }}
                </h2>
                <textarea id="notes" name="notes" rows="3"
                          class="textarea textarea-bordered w-full"
                          placeholder="{{ $trans['clients.internal_notes_placeholder'] ?? 'Add any internal notes about this client...' }}">{{ old('notes', $client->notes) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost">{{ $trans['btn.cancel'] ?? 'Cancel' }}</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $trans['btn.save_changes'] ?? 'Save Changes' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr for date fields
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        allowInput: true
    });
});
</script>
@endpush
