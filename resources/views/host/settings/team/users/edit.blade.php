@extends('layouts.settings')

@section('title', 'Edit Team Member — Settings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <style>
        .flatpickr-calendar { z-index: 9999 !important; }
        .flatpickr-calendar.hasTime.noCalendar { width: auto !important; min-width: 200px; }
        .flatpickr-time { display: flex !important; align-items: center !important; justify-content: center !important; gap: 4px; max-height: none !important; height: auto !important; padding: 10px !important; }
        .flatpickr-time .numInputWrapper { width: 50px !important; height: 40px !important; }
        .flatpickr-time .numInputWrapper input { font-size: 1.25rem !important; }
        .flatpickr-time .flatpickr-time-separator { font-size: 1.25rem !important; line-height: 40px !important; }
        .flatpickr-time .flatpickr-am-pm { width: 50px !important; height: 40px !important; line-height: 40px !important; font-size: 0.875rem !important; }
    </style>
@endpush

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">Users & Roles</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $user->full_name }}</li>
    </ol>
@endsection

@section('settings-content')
    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
            <div class="alert alert-soft alert-error">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.team.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Step Tab Navigation --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-0">
                    <nav class="flex overflow-x-auto border-b border-base-content/10" id="step-tabs">
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-primary text-primary" data-step="1" onclick="goToStep(1)">
                            <span class="icon-[tabler--user] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">1.</span> Profile
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="2" onclick="goToStep(2)">
                            <span class="icon-[tabler--briefcase] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">2.</span> Employment
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="3" onclick="goToStep(3)">
                            <span class="icon-[tabler--chart-bar] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">3.</span> Workload
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="4" onclick="goToStep(4)">
                            <span class="icon-[tabler--calendar-week] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">4.</span> Days
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="5" onclick="goToStep(5)">
                            <span class="icon-[tabler--clock] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">5.</span> Hours
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="6" onclick="goToStep(6)">
                            <span class="icon-[tabler--shield-cog] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">6.</span> Permissions
                        </button>
                        <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="7" onclick="goToStep(7)">
                            <span class="icon-[tabler--certificate] size-5 mr-2 inline-block align-middle"></span>
                            <span class="hidden sm:inline">7.</span> Certifications
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Step 1: Profile --}}
            <div id="step-1" class="step-content mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span class="icon-[tabler--user] size-5 text-primary"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Profile Information</h3>
                                <p class="text-base-content/60 text-sm">Basic details about the team member</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body space-y-5">
                        {{-- User Display (read-only) --}}
                        <div class="flex items-center gap-4 p-4 bg-base-200/50 rounded-lg">
                            <div class="avatar placeholder">
                                @php
                                    $bgColor = match($user->role) {
                                        'owner' => 'bg-primary text-primary-content',
                                        'admin' => 'bg-secondary text-secondary-content',
                                        'staff' => 'bg-info text-info-content',
                                        'instructor' => 'bg-accent text-accent-content',
                                        default => 'bg-base-300 text-base-content'
                                    };
                                @endphp
                                <div class="{{ $bgColor }} w-12 rounded-full">
                                    <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-medium text-lg">{{ $user->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label" for="phone">
                                <span class="label-text font-medium">Phone Number</span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--phone] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="tel" id="phone" name="phone" class="input input-bordered w-full pl-10 @error('phone') input-error @enderror"
                                       value="{{ old('phone', $user->phone ?? $instructor?->phone) }}" placeholder="+1 (555) 123-4567"
                                       oninput="this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '')" />
                            </div>
                        </div>

                        <div class="divider text-base-content/40 text-xs">ROLE</div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                            </label>
                            <div class="flex flex-col gap-3">
                                {{-- Admin --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-secondary has-[:checked]:bg-secondary/5 hover:border-secondary/30 transition-all">
                                    <input type="radio" name="role" value="admin" class="radio radio-secondary mt-1" {{ old('role', $user->role) == 'admin' ? 'checked' : '' }} />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--shield] size-5 text-secondary"></span>
                                            <span class="font-semibold">Admin</span>
                                        </div>
                                        <p class="text-sm text-base-content/60 mt-1">Full access to manage scheduling, bookings, students, instructors, and team settings.</p>
                                    </div>
                                </label>

                                {{-- Staff --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-info has-[:checked]:bg-info/5 hover:border-info/30 transition-all">
                                    <input type="radio" name="role" value="staff" class="radio radio-info mt-1" {{ old('role', $user->role) == 'staff' ? 'checked' : '' }} />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--user] size-5 text-info"></span>
                                            <span class="font-semibold">Staff</span>
                                        </div>
                                        <p class="text-sm text-base-content/60 mt-1">Can manage daily operations like bookings, check-ins, and student management.</p>
                                    </div>
                                </label>

                                {{-- Instructor --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-accent has-[:checked]:bg-accent/5 hover:border-accent/30 transition-all">
                                    <input type="radio" name="role" value="instructor" class="radio radio-accent mt-1" {{ old('role', $user->role) == 'instructor' ? 'checked' : '' }} />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--yoga] size-5 text-accent"></span>
                                            <span class="font-semibold">Instructor</span>
                                        </div>
                                        <p class="text-sm text-base-content/60 mt-1">Can view their own schedule and mark attendance for classes they teach.</p>
                                    </div>
                                </label>
                            </div>
                            @error('role')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="bio">
                                <span class="label-text font-medium">Bio</span>
                                <span class="label-text-alt text-base-content/50">Optional</span>
                            </label>
                            <textarea id="bio" name="bio" class="textarea textarea-bordered w-full @error('bio') input-error @enderror" rows="3"
                                      placeholder="A brief introduction about this team member...">{{ old('bio', $user->bio ?? $instructor?->bio) }}</textarea>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Specialties</span>
                            </label>
                            <div class="flex flex-col gap-2">
                                @php
                                    $selectedSpecialties = old('specialties', $instructor?->specialties ?? []);
                                @endphp
                                @foreach($specialties as $specialty)
                                    <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-lg border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all hover:border-primary/30">
                                        <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="checkbox checkbox-sm checkbox-primary"
                                            {{ in_array($specialty, $selectedSpecialties ?? []) ? 'checked' : '' }} />
                                        <span class="flex-1">{{ $specialty }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label" for="certifications_text">
                                <span class="label-text font-medium">Certifications (Text)</span>
                            </label>
                            <textarea id="certifications_text" name="certifications_text" class="textarea textarea-bordered w-full" rows="2"
                                      placeholder="RYT-200, ACE Certified, etc.">{{ old('certifications_text', $instructor?->certifications) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Employment --}}
            <div id="step-2" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                                <span class="icon-[tabler--briefcase] size-5 text-secondary"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Employment Details</h3>
                                <p class="text-base-content/60 text-sm">Compensation and employment type</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body space-y-5">
                        <div class="form-control">
                            <label class="label" for="employment_type">
                                <span class="label-text font-medium">Employment Type</span>
                            </label>
                            <select id="employment_type" name="employment_type" class="hidden"
                                    data-select='{
                                "placeholder": "Select employment type...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                                <option value="">Select employment type...</option>
                                @foreach($employmentTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('employment_type', $instructor?->employment_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label" for="rate_type">
                                <span class="label-text font-medium">Rate Type</span>
                            </label>
                            <select id="rate_type" name="rate_type" class="hidden"
                                    data-select='{
                                "placeholder": "Select how they are paid...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                                <option value="">Select how they're paid...</option>
                                @foreach($rateTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('rate_type', $instructor?->rate_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="rate-amount-wrapper" class="form-control {{ old('rate_type', $instructor?->rate_type) ? '' : 'hidden' }}">
                            <label class="label" for="rate_amount">
                                <span class="label-text font-medium">Rate Amount</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60 font-medium">$</span>
                                <input type="number" id="rate_amount" name="rate_amount" class="input input-bordered w-full pl-8 @error('rate_amount') input-error @enderror"
                                       step="0.01" min="0" placeholder="0.00" value="{{ old('rate_amount', $instructor?->rate_amount) }}" />
                            </div>
                            @error('rate_amount')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="compensation_notes">
                                <span class="label-text font-medium">Compensation Notes</span>
                                <span class="label-text-alt text-base-content/50">Optional</span>
                            </label>
                            <textarea id="compensation_notes" name="compensation_notes" class="textarea textarea-bordered w-full" rows="2"
                                      placeholder="e.g., Bonus for classes over 20 students, Travel reimbursement included...">{{ old('compensation_notes', $instructor?->compensation_notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Workload --}}
            <div id="step-3" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                                <span class="icon-[tabler--chart-bar] size-5 text-accent"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Workload Limits</h3>
                                <p class="text-base-content/60 text-sm">Prevent over-scheduling</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body space-y-5">
                        <div class="form-control">
                            <label class="label" for="hours_per_week">
                                <span class="label-text font-medium">Hours per Week</span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--clock-hour-4] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="number" id="hours_per_week" name="hours_per_week" class="input input-bordered w-full pl-10"
                                       step="0.5" min="0" max="168" placeholder="e.g., 20" value="{{ old('hours_per_week', $instructor?->hours_per_week) }}" />
                            </div>
                            <label class="label"><span class="label-text-alt text-base-content/50">Used for scheduling warnings</span></label>
                        </div>

                        <div class="form-control">
                            <label class="label" for="max_classes_per_week">
                                <span class="label-text font-medium">Max Classes per Week</span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--calendar-event] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="number" id="max_classes_per_week" name="max_classes_per_week" class="input input-bordered w-full pl-10"
                                       min="0" max="100" placeholder="e.g., 15" value="{{ old('max_classes_per_week', $instructor?->max_classes_per_week) }}" />
                            </div>
                            <label class="label"><span class="label-text-alt text-base-content/50">Soft limit for scheduling</span></label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Working Days --}}
            <div id="step-4" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                                <span class="icon-[tabler--calendar-week] size-5 text-warning"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Working Days</h3>
                                <p class="text-base-content/60 text-sm">Select which days this team member is available</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $selectedDays = old('working_days', $instructor?->working_days ?? []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                            @foreach($dayOptions as $value => $label)
                                <label class="cursor-pointer flex flex-col items-center p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/10 hover:border-primary/30 transition-all">
                                    <input type="checkbox" name="working_days[]" value="{{ $value }}" class="hidden"
                                        {{ in_array($value, $selectedDays ?? []) ? 'checked' : '' }} />
                                    <span class="text-2xl mb-1">{{ ['Sun' => '☀️', 'Mon' => '🌙', 'Tue' => '🔥', 'Wed' => '💧', 'Thu' => '⚡', 'Fri' => '🐟', 'Sat' => '⭐'][substr($label, 0, 3)] }}</span>
                                    <span class="font-medium text-sm">{{ substr($label, 0, 3) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-base-content/50 mt-4 text-center">
                            <span class="icon-[tabler--info-circle] size-4 inline-block align-text-bottom mr-1"></span>
                            Select the days this team member typically works
                        </p>
                    </div>
                </div>
            </div>

            {{-- Step 5: Availability Hours --}}
            <div id="step-5" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                                <span class="icon-[tabler--clock] size-5 text-success"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Availability Hours</h3>
                                <p class="text-base-content/60 text-sm">Set working hours for scheduling</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body space-y-6">
                        <div class="form-control">
                            <label class="label" for="availability_default_from">
                                <span class="label-text font-medium">Start Time</span>
                            </label>
                            <input type="text" id="availability_default_from" name="availability_default_from"
                                   class="input input-bordered w-full flatpickr-time" placeholder="Select start time..."
                                   value="{{ old('availability_default_from', $instructor?->availability_default_from) }}" />
                        </div>

                        <div class="form-control">
                            <label class="label" for="availability_default_to">
                                <span class="label-text font-medium">End Time</span>
                            </label>
                            <input type="text" id="availability_default_to" name="availability_default_to"
                                   class="input input-bordered w-full flatpickr-time" placeholder="Select end time..."
                                   value="{{ old('availability_default_to', $instructor?->availability_default_to) }}" />
                        </div>

                        @php
                            $hasOverrides = !empty(old('availability_by_day', $instructor?->availability_by_day));
                        @endphp
                        <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-lg border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all hover:border-primary/30">
                            <input type="checkbox" id="enable-day-overrides" class="checkbox checkbox-sm checkbox-primary" {{ $hasOverrides ? 'checked' : '' }} />
                            <div class="flex-1">
                                <span class="font-medium">Day-Specific Overrides</span>
                                <span class="text-xs text-base-content/60 block">Set different hours for specific days</span>
                            </div>
                            <span class="icon-[tabler--adjustments-horizontal] size-5 text-base-content/40"></span>
                        </label>

                        <div id="day-overrides-container" class="space-y-3 {{ $hasOverrides ? '' : 'hidden' }}">
                            @php
                                $overrides = old('availability_by_day', $instructor?->availability_by_day ?? []);
                            @endphp
                            @foreach($dayOptions as $value => $label)
                                <div class="flex flex-col gap-2 p-4 bg-base-200/30 rounded-lg">
                                    <span class="font-medium text-sm">{{ $label }}</span>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="text" name="availability_by_day[{{ $value }}][from]"
                                               class="input input-sm input-bordered w-full flatpickr-time-override" placeholder="Start time"
                                               value="{{ $overrides[$value]['from'] ?? '' }}" />
                                        <input type="text" name="availability_by_day[{{ $value }}][to]"
                                               class="input input-sm input-bordered w-full flatpickr-time-override" placeholder="End time"
                                               value="{{ $overrides[$value]['to'] ?? '' }}" />
                                    </div>
                                </div>
                            @endforeach
                            <p class="text-xs text-base-content/50">Leave blank to use default hours for that day.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 6: Permissions --}}
            <div id="step-6" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                                <span class="icon-[tabler--shield-cog] size-5 text-secondary"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Permissions</h3>
                                <p class="text-base-content/60 text-sm">Customize what this user can access</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $categoryIcons = [
                                'schedule' => 'icon-[tabler--calendar]',
                                'bookings' => 'icon-[tabler--clipboard-list]',
                                'students' => 'icon-[tabler--users]',
                                'offers' => 'icon-[tabler--tag]',
                                'insights' => 'icon-[tabler--chart-bar]',
                                'payments' => 'icon-[tabler--credit-card]',
                                'studio' => 'icon-[tabler--building-store]',
                                'team' => 'icon-[tabler--users-group]',
                                'billing' => 'icon-[tabler--receipt]',
                            ];
                            $categoryColors = [
                                'schedule' => 'text-primary bg-primary/10',
                                'bookings' => 'text-secondary bg-secondary/10',
                                'students' => 'text-info bg-info/10',
                                'offers' => 'text-warning bg-warning/10',
                                'insights' => 'text-accent bg-accent/10',
                                'payments' => 'text-success bg-success/10',
                                'studio' => 'text-primary bg-primary/10',
                                'team' => 'text-secondary bg-secondary/10',
                                'billing' => 'text-info bg-info/10',
                            ];
                            $userPermissions = old('permissions', $user->permissions ?? []);
                        @endphp

                        <div class="space-y-2">
                            @foreach($groupedPermissions as $category => $permissions)
                                <details class="group border border-base-content/10 rounded-lg overflow-hidden" id="perm-section-{{ $category }}">
                                    <summary class="flex items-center gap-3 p-3 cursor-pointer hover:bg-base-200/50 transition-colors list-none">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $categoryColors[$category] ?? 'text-base-content bg-base-200' }}">
                                            <span class="{{ $categoryIcons[$category] ?? 'icon-[tabler--settings]' }} size-4"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm">{{ ucfirst($category) }}</h4>
                                            <p class="text-xs text-base-content/50" id="perm-count-{{ $category }}">0 of {{ count($permissions) }} enabled</p>
                                        </div>
                                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                                    </summary>
                                    <div class="border-t border-base-content/10 bg-base-200/30 p-3 space-y-1">
                                        <div class="flex justify-end mb-2">
                                            <button type="button" class="text-xs text-primary hover:underline" onclick="toggleCategory('{{ $category }}')">
                                                Toggle all
                                            </button>
                                        </div>
                                        @foreach($permissions as $permission => $label)
                                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-100 cursor-pointer transition-colors">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permission }}"
                                                       class="checkbox checkbox-primary checkbox-sm permission-checkbox"
                                                       data-permission="{{ $permission }}"
                                                       data-category="{{ $category }}"
                                                       onchange="updateCategoryCount('{{ $category }}')"
                                                    {{ in_array($permission, $userPermissions ?? []) ? 'checked' : '' }} />
                                                <span class="text-sm">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" class="btn btn-ghost btn-sm gap-2" onclick="resetToRoleDefaults()">
                                <span class="icon-[tabler--refresh] size-4"></span>
                                Reset to Role Defaults
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 7: Certifications --}}
            <div id="step-7" class="step-content hidden mt-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                                    <span class="icon-[tabler--certificate] size-5 text-warning"></span>
                                </div>
                                <div>
                                    <h3 class="card-title text-lg">Certifications & Credentials</h3>
                                    <p class="text-base-content/60 text-sm">Track certifications, licenses, and credentials</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openUserCertDrawer()">
                                <span class="icon-[tabler--plus] size-4"></span> Add
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="user-certifications-list">
                            @if($certifications->isEmpty())
                                <div class="text-center py-8" id="no-user-certs-message">
                                    <span class="icon-[tabler--certificate] size-12 text-base-content/20 mx-auto block"></span>
                                    <p class="text-base-content/50 mt-2">No certifications added yet</p>
                                    <button type="button" class="btn btn-primary btn-sm mt-4" onclick="openUserCertDrawer()">
                                        <span class="icon-[tabler--plus] size-4"></span> Add Certification
                                    </button>
                                </div>
                            @else
                                <div class="space-y-3" id="user-certs-container">
                                    @foreach($certifications as $cert)
                                    <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="{{ $cert->id }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                                <span class="icon-[tabler--certificate] size-5 text-primary"></span>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $cert->name }}</div>
                                                @if($cert->certification_name)
                                                    <div class="text-xs text-base-content/60">{{ $cert->certification_name }}</div>
                                                @endif
                                                @if($cert->expire_date)
                                                    <div class="text-xs mt-1">
                                                        <span class="badge {{ $cert->status_badge_class }} badge-xs">
                                                            @if($cert->isExpired())
                                                                Expired {{ $cert->expire_date->format('M j, Y') }}
                                                            @else
                                                                Expires {{ $cert->expire_date->format('M j, Y') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            @if($cert->file_path)
                                            <a href="{{ $cert->file_url }}" target="_blank" class="btn btn-ghost btn-sm btn-square" title="View File">
                                                <span class="icon-[tabler--file-download] size-4"></span>
                                            </a>
                                            @endif
                                            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editUserCert({{ $cert->id }})" title="Edit">
                                                <span class="icon-[tabler--pencil] size-4"></span>
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteUserCert({{ $cert->id }})" title="Delete">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="flex items-center justify-between pt-6">
                <button type="button" id="prev-step-btn" class="btn btn-ghost gap-2 hidden" onclick="prevStep()">
                    <span class="icon-[tabler--chevron-left] size-5"></span>
                    Previous
                </button>
                <div class="flex gap-3 ml-auto">
                    <a href="{{ route('settings.team.users') }}" class="btn btn-ghost">Cancel</a>
                    <button type="button" id="next-step-btn" class="btn btn-primary gap-2" onclick="nextStep()">
                        Next Step
                        <span class="icon-[tabler--chevron-right] size-5"></span>
                    </button>
                    <button type="submit" id="save-btn" class="btn btn-primary gap-2 hidden">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

{{-- User Certification Drawer --}}
<div id="user-cert-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold" id="user-cert-drawer-title">Add Certification</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeUserCertDrawer()">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="user-cert-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <input type="hidden" id="user-cert-id" value="" />
        <input type="hidden" id="user-cert-remove-file" value="" />
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text font-medium" for="user_cert_name">Name <span class="text-error">*</span></label>
                    <input type="text" id="user_cert_name" name="name" class="input w-full" placeholder="e.g., First Aid, CPR, Management Training" required />
                </div>

                <div>
                    <label class="label-text font-medium" for="user_cert_certification_name">Certification / Credential Name</label>
                    <input type="text" id="user_cert_certification_name" name="certification_name" class="input w-full" placeholder="e.g., Red Cross Certified, License #12345" />
                </div>

                <div>
                    <label class="label-text font-medium" for="user_cert_expire_date">Expiration Date</label>
                    <input type="date" id="user_cert_expire_date" name="expire_date" class="input w-full" />
                    <p class="text-xs text-base-content/50 mt-1">Leave blank if no expiration</p>
                </div>

                <div>
                    <label class="label-text font-medium" for="user_cert_reminder_days">Reminder</label>
                    <select id="user_cert_reminder_days" name="reminder_days" class="select w-full">
                        <option value="">No reminder</option>
                        <option value="7">7 days before expiry</option>
                        <option value="14">14 days before expiry</option>
                        <option value="30">30 days before expiry</option>
                        <option value="60">60 days before expiry</option>
                        <option value="90">90 days before expiry</option>
                    </select>
                </div>

                <div>
                    <label class="label-text font-medium">Upload Document</label>
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-6 hover:border-primary transition-colors cursor-pointer" id="user-cert-drop-zone">
                        <input type="file" id="user_cert_file" name="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                        <div id="user-cert-upload-placeholder">
                            <span class="icon-[tabler--cloud-upload] size-8 text-base-content/30 mb-2 block mx-auto"></span>
                            <p class="text-sm text-base-content/60 text-center">Drag and drop file here, or</p>
                            <button type="button" class="btn btn-soft btn-sm mt-2 mx-auto block" id="user-cert-browse-btn">Browse Files</button>
                        </div>
                        <div id="user-cert-upload-preview" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-success mb-2 block mx-auto"></span>
                            <p id="user-cert-preview-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="user-cert-remove-preview-btn">
                                <span class="icon-[tabler--x] size-4"></span> Remove
                            </button>
                        </div>
                        <div id="user-cert-existing-file" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-primary mb-2 block mx-auto"></span>
                            <p id="user-cert-existing-file-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="user-cert-remove-existing-btn">
                                <span class="icon-[tabler--x] size-4"></span> Remove
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/50 text-center mt-2">PDF, JPG, PNG, WebP. Max 10MB</p>
                </div>

                <div>
                    <label class="label-text font-medium" for="user_cert_notes">Notes</label>
                    <textarea id="user_cert_notes" name="notes" class="textarea w-full" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-user-cert-btn">
                <span class="loading loading-spinner loading-xs hidden" id="user-cert-spinner"></span>
                Save
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeUserCertDrawer()">Cancel</button>
        </div>
    </form>
</div>

{{-- User Cert Delete Modal --}}
<dialog id="delete-user-cert-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Certification</h3>
        <p class="py-4">Are you sure you want to delete this certification? This action cannot be undone.</p>
        <input type="hidden" id="delete-user-cert-id" value="" />
        <div class="modal-action">
            <button type="button" class="btn btn-error" id="confirm-delete-user-cert-btn">Delete</button>
            <button type="button" class="btn" onclick="document.getElementById('delete-user-cert-modal').close()">Cancel</button>
        </div>
    </div>
</dialog>

{{-- Drawer Backdrop --}}
<div id="user-cert-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeUserCertDrawer()"></div>

@push('scripts')
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        const totalSteps = 7;
        let currentStep = 1;

        // Role default permissions
        const roleDefaults = {
            admin: @json(\App\Models\User::getDefaultPermissionsForRole('admin')),
            staff: @json(\App\Models\User::getDefaultPermissionsForRole('staff')),
            instructor: @json(\App\Models\User::getDefaultPermissionsForRole('instructor'))
        };

        function showStep(step) {
            // Hide all steps
            for (let i = 1; i <= totalSteps; i++) {
                const stepEl = document.getElementById('step-' + i);
                if (stepEl) stepEl.classList.add('hidden');
            }
            // Show current step
            const currentStepEl = document.getElementById('step-' + step);
            if (currentStepEl) currentStepEl.classList.remove('hidden');

            // Update tab indicators
            document.querySelectorAll('.step-tab').forEach((tab) => {
                const tabStep = parseInt(tab.dataset.step);
                if (tabStep === step) {
                    tab.classList.add('border-primary', 'text-primary');
                    tab.classList.remove('border-transparent', 'text-base-content/60');
                } else {
                    tab.classList.remove('border-primary', 'text-primary');
                    tab.classList.add('border-transparent', 'text-base-content/60');
                }
            });

            // Update navigation buttons
            document.getElementById('prev-step-btn').classList.toggle('hidden', step === 1);
            document.getElementById('next-step-btn').classList.toggle('hidden', step === totalSteps);
            document.getElementById('save-btn').classList.toggle('hidden', step !== totalSteps);

            currentStep = step;
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function goToStep(step) {
            showStep(step);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetToRoleDefaults() {
            var role = document.querySelector('input[name="role"]:checked')?.value || 'staff';
            var defaults = roleDefaults[role] || [];

            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.checked = defaults.includes(checkbox.dataset.permission);
            });

            updateAllCategoryCounts();
        }

        function toggleCategory(category) {
            var checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
            var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });

            updateCategoryCount(category);
        }

        function updateCategoryCount(category) {
            var checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
            var total = checkboxes.length;
            var checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; }).length;
            var countEl = document.getElementById('perm-count-' + category);
            if (countEl) {
                countEl.textContent = checked + ' of ' + total + ' enabled';
            }
        }

        function updateAllCategoryCounts() {
            var categories = new Set();
            document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
                categories.add(cb.dataset.category);
            });
            categories.forEach(function(category) {
                updateCategoryCount(category);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Rate type toggle
            var rateTypeSelect = document.getElementById('rate_type');
            function updateRateAmountVisibility() {
                document.getElementById('rate-amount-wrapper').classList.toggle('hidden', !rateTypeSelect.value);
            }
            rateTypeSelect.addEventListener('change', updateRateAmountVisibility);
            // Also observe for HSSelect mutations
            var rateTypeObserver = new MutationObserver(updateRateAmountVisibility);
            rateTypeObserver.observe(rateTypeSelect, { attributes: true, childList: true, subtree: true });
            // Initial check
            updateRateAmountVisibility();

            // Day overrides toggle
            document.getElementById('enable-day-overrides').addEventListener('change', function() {
                document.getElementById('day-overrides-container').classList.toggle('hidden', !this.checked);
            });

            // Initialize Flatpickr
            var timePickerConfig = {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: false,
                minuteIncrement: 15,
                altInput: true,
                altFormat: 'h:i K',
                altInputClass: 'input input-bordered w-full',
                appendTo: document.body,
                static: false
            };

            var timePickerSmConfig = {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: false,
                minuteIncrement: 15,
                altInput: true,
                altFormat: 'h:i K',
                altInputClass: 'input input-sm input-bordered w-full',
                appendTo: document.body,
                static: false
            };

            flatpickr('.flatpickr-time', timePickerConfig);
            flatpickr('.flatpickr-time-override', timePickerSmConfig);

            // Set initial category counts
            updateAllCategoryCounts();
        });

        // ============================================
        // User Certifications Management
        // ============================================
        var editingUserCertId = null;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showToast(message, type) {
            type = type || 'success';
            var toast = document.createElement('div');
            toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
            toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
            document.body.appendChild(toast);
            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(function() { toast.remove(); }, 300);
            }, 3000);
        }

        function openUserCertDrawer() {
            resetUserCertForm();
            var drawer = document.getElementById('user-cert-drawer');
            var backdrop = document.getElementById('user-cert-backdrop');
            if (drawer && backdrop) {
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                drawer.classList.remove('translate-x-full');
                drawer.classList.add('translate-x-0');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeUserCertDrawer() {
            var drawer = document.getElementById('user-cert-drawer');
            var backdrop = document.getElementById('user-cert-backdrop');
            if (drawer && backdrop) {
                drawer.classList.remove('translate-x-0');
                drawer.classList.add('translate-x-full');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            }
        }

        function resetUserCertForm() {
            editingUserCertId = null;
            document.getElementById('user-cert-drawer-title').textContent = 'Add Certification';
            document.getElementById('user-cert-id').value = '';
            document.getElementById('user_cert_name').value = '';
            document.getElementById('user_cert_certification_name').value = '';
            document.getElementById('user_cert_expire_date').value = '';
            document.getElementById('user_cert_reminder_days').value = '';
            document.getElementById('user_cert_notes').value = '';
            document.getElementById('user_cert_file').value = '';
            document.getElementById('user-cert-remove-file').value = '';

            var placeholder = document.getElementById('user-cert-upload-placeholder');
            var preview = document.getElementById('user-cert-upload-preview');
            var existingFile = document.getElementById('user-cert-existing-file');
            if (placeholder) placeholder.classList.remove('hidden');
            if (preview) preview.classList.add('hidden');
            if (existingFile) existingFile.classList.add('hidden');
        }

        function editUserCert(id) {
            editingUserCertId = id;
            document.getElementById('user-cert-drawer-title').textContent = 'Edit Certification';

            var spinner = document.getElementById('user-cert-spinner');
            spinner.classList.remove('hidden');

            fetch('{{ url("settings/team/users") }}/{{ $user->id }}/certifications/' + id, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    var cert = result.certification;
                    document.getElementById('user-cert-id').value = cert.id;
                    document.getElementById('user_cert_name').value = cert.name || '';
                    document.getElementById('user_cert_certification_name').value = cert.certification_name || '';
                    document.getElementById('user_cert_expire_date').value = cert.expire_date || '';
                    document.getElementById('user_cert_reminder_days').value = cert.reminder_days || '';
                    document.getElementById('user_cert_notes').value = cert.notes || '';

                    var placeholder = document.getElementById('user-cert-upload-placeholder');
                    var preview = document.getElementById('user-cert-upload-preview');
                    var existingFile = document.getElementById('user-cert-existing-file');
                    var existingFileName = document.getElementById('user-cert-existing-file-name');

                    if (cert.file_name) {
                        if (existingFile && existingFileName) {
                            existingFileName.textContent = cert.file_name;
                            existingFile.classList.remove('hidden');
                            if (placeholder) placeholder.classList.add('hidden');
                        }
                    } else {
                        if (existingFile) existingFile.classList.add('hidden');
                        if (placeholder) placeholder.classList.remove('hidden');
                    }
                    if (preview) preview.classList.add('hidden');

                    openUserCertDrawer();
                } else {
                    showToast(result.message || 'Failed to load certification', 'error');
                }
            })
            .catch(function() { showToast('An error occurred', 'error'); })
            .finally(function() { spinner.classList.add('hidden'); });
        }

        function deleteUserCert(id) {
            document.getElementById('delete-user-cert-id').value = id;
            document.getElementById('delete-user-cert-modal').showModal();
        }

        // Confirm delete
        document.getElementById('confirm-delete-user-cert-btn').addEventListener('click', function() {
            var btn = this;
            var id = document.getElementById('delete-user-cert-id').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Deleting...';

            fetch('{{ url("settings/team/users") }}/{{ $user->id }}/certifications/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    var item = document.querySelector('[data-cert-id="' + id + '"]');
                    if (item) item.remove();

                    var container = document.getElementById('user-certs-container');
                    if (container && container.querySelectorAll('[data-cert-id]').length === 0) {
                        document.getElementById('user-certifications-list').innerHTML =
                            '<div class="text-center py-8" id="no-user-certs-message">' +
                            '<span class="icon-[tabler--certificate] size-12 text-base-content/20 mx-auto block"></span>' +
                            '<p class="text-base-content/50 mt-2">No certifications added yet</p>' +
                            '<button type="button" class="btn btn-primary btn-sm mt-4" onclick="openUserCertDrawer()">' +
                            '<span class="icon-[tabler--plus] size-4"></span> Add Certification</button></div>';
                    }

                    document.getElementById('delete-user-cert-modal').close();
                    showToast('Certification deleted!');
                } else {
                    showToast(result.message || 'Failed to delete', 'error');
                }
            })
            .catch(function() { showToast('An error occurred', 'error'); })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = 'Delete';
            });
        });

        // File input handling
        (function() {
            var fileInput = document.getElementById('user_cert_file');
            var browseBtn = document.getElementById('user-cert-browse-btn');
            var dropZone = document.getElementById('user-cert-drop-zone');
            var placeholder = document.getElementById('user-cert-upload-placeholder');
            var preview = document.getElementById('user-cert-upload-preview');
            var previewName = document.getElementById('user-cert-preview-name');
            var removeBtn = document.getElementById('user-cert-remove-preview-btn');
            var existingFile = document.getElementById('user-cert-existing-file');

            if (!fileInput) return;

            if (browseBtn) {
                browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); fileInput.click(); });
            }

            if (dropZone) {
                dropZone.addEventListener('click', function(e) { if (!e.target.closest('button')) fileInput.click(); });
                dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
                dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('border-primary', 'bg-primary/5');
                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        handleUserCertFile(e.dataTransfer.files[0]);
                    }
                });
            }

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) handleUserCertFile(this.files[0]);
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.value = '';
                    if (preview) preview.classList.add('hidden');
                    if (placeholder) placeholder.classList.remove('hidden');
                });
            }

            function handleUserCertFile(file) {
                var validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showToast('Please upload PDF, JPG, PNG, or WebP', 'error');
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    showToast('File must be under 10MB', 'error');
                    return;
                }

                if (previewName) previewName.textContent = file.name;
                if (placeholder) placeholder.classList.add('hidden');
                if (existingFile) existingFile.classList.add('hidden');
                if (preview) preview.classList.remove('hidden');
            }

            var removeExistingBtn = document.getElementById('user-cert-remove-existing-btn');
            if (removeExistingBtn) {
                removeExistingBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (existingFile) existingFile.classList.add('hidden');
                    if (placeholder) placeholder.classList.remove('hidden');
                    document.getElementById('user-cert-remove-file').value = '1';
                });
            }
        })();

        // Form submit
        document.getElementById('user-cert-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('save-user-cert-btn');
            var spinner = document.getElementById('user-cert-spinner');
            btn.disabled = true;
            spinner.classList.remove('hidden');

            var formData = new FormData();
            formData.append('name', document.getElementById('user_cert_name').value);
            formData.append('certification_name', document.getElementById('user_cert_certification_name').value);
            formData.append('expire_date', document.getElementById('user_cert_expire_date').value);
            formData.append('reminder_days', document.getElementById('user_cert_reminder_days').value);
            formData.append('notes', document.getElementById('user_cert_notes').value);

            var fileInput = document.getElementById('user_cert_file');
            if (fileInput.files.length > 0) {
                formData.append('file', fileInput.files[0]);
            }

            var removeFile = document.getElementById('user-cert-remove-file').value;
            if (removeFile === '1') {
                formData.append('remove_file', '1');
            }

            var certId = document.getElementById('user-cert-id').value;
            var isEdit = certId && certId !== '';
            if (isEdit) {
                formData.append('id', certId);
            }
            var url = '{{ url("settings/team/users") }}/{{ $user->id }}/certifications';

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    var cert = result.certification;
                    var list = document.getElementById('user-certifications-list');

                    var itemHtml = '<div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="' + cert.id + '">' +
                        '<div class="flex items-center gap-3">' +
                        '<div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">' +
                        '<span class="icon-[tabler--certificate] size-5 text-primary"></span></div>' +
                        '<div><div class="font-medium">' + escapeHtml(cert.name) + '</div>';

                    if (cert.certification_name) {
                        itemHtml += '<div class="text-xs text-base-content/60">' + escapeHtml(cert.certification_name) + '</div>';
                    }

                    if (cert.expire_date_formatted) {
                        itemHtml += '<div class="text-xs mt-1"><span class="badge ' + cert.status_badge_class + ' badge-xs">' +
                            (cert.days_until_expiry < 0 ? 'Expired ' : 'Expires ') + cert.expire_date_formatted + '</span></div>';
                    }

                    itemHtml += '</div></div><div class="flex items-center gap-1">';

                    if (cert.file_url) {
                        itemHtml += '<a href="' + cert.file_url + '" target="_blank" class="btn btn-ghost btn-sm btn-square" title="View File">' +
                            '<span class="icon-[tabler--file-download] size-4"></span></a>';
                    }

                    itemHtml += '<button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editUserCert(' + cert.id + ')" title="Edit">' +
                        '<span class="icon-[tabler--pencil] size-4"></span></button>' +
                        '<button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteUserCert(' + cert.id + ')" title="Delete">' +
                        '<span class="icon-[tabler--trash] size-4"></span></button></div></div>';

                    if (isEdit) {
                        var existingItem = document.querySelector('[data-cert-id="' + cert.id + '"]');
                        if (existingItem) {
                            existingItem.outerHTML = itemHtml;
                        }
                    } else {
                        var emptyState = document.getElementById('no-user-certs-message');
                        if (emptyState) {
                            list.innerHTML = '<div class="space-y-3" id="user-certs-container">' + itemHtml + '</div>';
                        } else {
                            var container = document.getElementById('user-certs-container');
                            if (container) {
                                container.insertAdjacentHTML('beforeend', itemHtml);
                            } else {
                                list.innerHTML = '<div class="space-y-3" id="user-certs-container">' + itemHtml + '</div>';
                            }
                        }
                    }

                    resetUserCertForm();
                    closeUserCertDrawer();
                    setTimeout(function() { showToast(isEdit ? 'Certification updated!' : 'Certification added!'); }, 350);
                } else {
                    showToast(result.message || 'Failed to save', 'error');
                }
            })
            .catch(function() { showToast('An error occurred', 'error'); })
            .finally(function() {
                btn.disabled = false;
                spinner.classList.add('hidden');
            });
        });

        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endpush
@endsection
