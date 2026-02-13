@extends('layouts.settings')

@section('title', 'Add Team Member — Settings')

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
        <li aria-current="page">Add Team Member</li>
    </ol>
@endsection

@section('settings-content')
    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="alert alert-soft alert-error">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.team.invite') }}" method="POST">
            @csrf

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
                        <div class="form-control">
                            <label class="label" for="first_name">
                                <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--user] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="text" id="first_name" name="first_name" class="input input-bordered w-full pl-10 @error('first_name') input-error @enderror"
                                       required placeholder="John" value="{{ old('first_name') }}"
                                       pattern="[A-Za-z\s\-']+"
                                       oninput="this.value = this.value.replace(/[0-9]/g, '')" />
                            </div>
                            @error('first_name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="last_name">
                                <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--user] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="text" id="last_name" name="last_name" class="input input-bordered w-full pl-10 @error('last_name') input-error @enderror"
                                       required placeholder="Doe" value="{{ old('last_name') }}"
                                       pattern="[A-Za-z\s\-']+"
                                       oninput="this.value = this.value.replace(/[0-9]/g, '')" />
                            </div>
                            @error('last_name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="email">
                                <span class="label-text font-medium">Email Address</span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--mail] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="email" id="email" name="email" class="input input-bordered w-full pl-10 @error('email') input-error @enderror"
                                       placeholder="colleague@example.com" value="{{ old('email') }}" />
                            </div>
                            @error('email')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                            <label class="label"><span class="label-text-alt text-base-content/50">Required if granting login access</span></label>
                        </div>

                        <div class="form-control">
                            <label class="label" for="phone">
                                <span class="label-text font-medium">Phone Number</span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--phone] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="tel" id="phone" name="phone" class="input input-bordered w-full pl-10 @error('phone') input-error @enderror"
                                       value="{{ old('phone') }}" placeholder="+1 (555) 123-4567"
                                       oninput="this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '')" />
                            </div>
                        </div>

                        {{-- Login Access Option --}}
                        <label id="send-invite-option" class="cursor-pointer flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-base-content/10 hover:border-primary/30 hover:bg-primary/5 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="checkbox" name="send_invite" value="1" id="send_invite" class="checkbox checkbox-primary" {{ old('send_invite', true) ? 'checked' : '' }} />
                            <div class="flex-1">
                            <span class="font-medium flex items-center gap-2">
                                <span class="icon-[tabler--mail-forward] size-5 text-primary"></span>
                                Grant Login Access
                            </span>
                                <span class="text-sm text-base-content/60 block mt-0.5">Team member will receive an email invitation to create their account</span>
                            </div>
                        </label>
                        <p class="text-xs text-base-content/50 ml-1">
                            <span class="icon-[tabler--info-circle] size-3.5 inline-block align-text-bottom mr-0.5"></span>
                            If unchecked, the team member will be added without login access
                        </p>

                        <div class="divider text-base-content/40 text-xs">ROLE</div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                            </label>
                            <div class="flex flex-col gap-3">
                                {{-- Admin --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-secondary has-[:checked]:bg-secondary/5 hover:border-secondary/30 transition-all">
                                    <input type="radio" name="role" value="admin" class="radio radio-secondary mt-1" {{ old('role') == 'admin' ? 'checked' : '' }} />
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
                                    <input type="radio" name="role" value="staff" class="radio radio-info mt-1" {{ old('role', 'staff') == 'staff' ? 'checked' : '' }} />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--user] size-5 text-info"></span>
                                            <span class="font-semibold">Staff</span>
                                            <span class="badge badge-info badge-soft badge-xs">Default</span>
                                        </div>
                                        <p class="text-sm text-base-content/60 mt-1">Can manage daily operations like bookings, check-ins, and student management.</p>
                                    </div>
                                </label>

                                {{-- Instructor --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-accent has-[:checked]:bg-accent/5 hover:border-accent/30 transition-all">
                                    <input type="radio" name="role" value="instructor" class="radio radio-accent mt-1" {{ old('role') == 'instructor' ? 'checked' : '' }} />
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
                                      placeholder="A brief introduction about this team member...">{{ old('bio') }}</textarea>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Specialties</span>
                            </label>
                            <div class="flex flex-col gap-2">
                                @php
                                    $selectedSpecialties = old('specialties', []);
                                @endphp
                                @foreach($specialties ?? \App\Models\Instructor::getCommonSpecialties() as $specialty)
                                    <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-lg border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all hover:border-primary/30">
                                        <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="checkbox checkbox-sm checkbox-primary"
                                            {{ in_array($specialty, $selectedSpecialties) ? 'checked' : '' }} />
                                        <span class="flex-1">{{ $specialty }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label" for="certifications">
                                <span class="label-text font-medium">Certifications</span>
                            </label>
                            <textarea id="certifications" name="certifications" class="textarea textarea-bordered w-full" rows="2"
                                      placeholder="RYT-200, ACE Certified, etc.">{{ old('certifications') }}</textarea>
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
                                @foreach($employmentTypes ?? \App\Models\Instructor::getEmploymentTypes() as $value => $label)
                                    <option value="{{ $value }}" {{ old('employment_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                                @foreach($rateTypes ?? \App\Models\Instructor::getRateTypes() as $value => $label)
                                    <option value="{{ $value }}" {{ old('rate_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="rate-amount-wrapper" class="form-control {{ old('rate_type') ? '' : 'hidden' }}">
                            <label class="label" for="rate_amount">
                                <span class="label-text font-medium">Rate Amount</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60 font-medium">$</span>
                                <input type="number" id="rate_amount" name="rate_amount" class="input input-bordered w-full pl-8 @error('rate_amount') input-error @enderror"
                                       step="0.01" min="0" placeholder="0.00" value="{{ old('rate_amount') }}" />
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
                                      placeholder="e.g., Bonus for classes over 20 students, Travel reimbursement included...">{{ old('compensation_notes') }}</textarea>
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
                                       step="0.5" min="0" max="168" placeholder="e.g., 20" value="{{ old('hours_per_week') }}" />
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
                                       min="0" max="100" placeholder="e.g., 15" value="{{ old('max_classes_per_week') }}" />
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
                            $selectedDays = old('working_days', []);
                            $dayOptions = $dayOptions ?? \App\Models\Instructor::getDayOptions();
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                            @foreach($dayOptions as $value => $label)
                                <label class="cursor-pointer flex flex-col items-center p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/10 hover:border-primary/30 transition-all">
                                    <input type="checkbox" name="working_days[]" value="{{ $value }}" class="hidden"
                                        {{ in_array($value, $selectedDays) ? 'checked' : '' }} />
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
                                   value="{{ old('availability_default_from') }}" />
                        </div>

                        <div class="form-control">
                            <label class="label" for="availability_default_to">
                                <span class="label-text font-medium">End Time</span>
                            </label>
                            <input type="text" id="availability_default_to" name="availability_default_to"
                                   class="input input-bordered w-full flatpickr-time" placeholder="Select end time..."
                                   value="{{ old('availability_default_to') }}" />
                        </div>

                        @php
                            $hasOverrides = !empty(old('availability_by_day'));
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
                                $overrides = old('availability_by_day', []);
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
                                                    {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }} />
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
                        <span id="save-btn-icon" class="icon-[tabler--user-plus] size-5"></span>
                        <span id="save-btn-text">Add Team Member</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script>
            const totalSteps = 6;
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
                // Validate required fields before proceeding
                if (currentStep === 1) {
                    const firstNameInput = document.getElementById('first_name');
                    const lastNameInput = document.getElementById('last_name');
                    const emailInput = document.getElementById('email');
                    const sendInviteCheckbox = document.getElementById('send_invite');

                    if (!firstNameInput.value) {
                        firstNameInput.focus();
                        firstNameInput.reportValidity();
                        return;
                    }
                    if (!lastNameInput.value) {
                        lastNameInput.focus();
                        lastNameInput.reportValidity();
                        return;
                    }
                    // Email is required only if granting login access
                    if (sendInviteCheckbox.checked && (!emailInput.value || !emailInput.checkValidity())) {
                        emailInput.focus();
                        emailInput.setCustomValidity('Email is required when granting login access');
                        emailInput.reportValidity();
                        emailInput.setCustomValidity('');
                        return;
                    }
                }
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

            // Update permissions based on role selection
            function updatePermissionsForRole() {
                const role = document.querySelector('input[name="role"]:checked')?.value || 'staff';
                const defaults = roleDefaults[role] || [];

                document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                    checkbox.checked = defaults.includes(checkbox.dataset.permission);
                });

                updateAllCategoryCounts();
            }

            function resetToRoleDefaults() {
                updatePermissionsForRole();
            }

            function toggleCategory(category) {
                const checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
                const allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });

                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = !allChecked;
                });

                updateCategoryCount(category);
            }

            function updateCategoryCount(category) {
                const checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
                const total = checkboxes.length;
                const checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; }).length;
                const countEl = document.getElementById('perm-count-' + category);
                if (countEl) {
                    countEl.textContent = checked + ' of ' + total + ' enabled';
                }
            }

            function updateAllCategoryCounts() {
                const categories = new Set();
                document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
                    categories.add(cb.dataset.category);
                });
                categories.forEach(function(category) {
                    updateCategoryCount(category);
                });
            }

            function updateSaveButton() {
                const sendInvite = document.getElementById('send_invite').checked;
                const btnIcon = document.getElementById('save-btn-icon');
                const btnText = document.getElementById('save-btn-text');

                if (sendInvite) {
                    btnIcon.className = 'icon-[tabler--send] size-5';
                    btnText.textContent = 'Send Invitation';
                } else {
                    btnIcon.className = 'icon-[tabler--user-plus] size-5';
                    btnText.textContent = 'Add Team Member';
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Role selection change
                const roleRadios = document.querySelectorAll('input[name="role"]');
                roleRadios.forEach(function(radio) {
                    radio.addEventListener('change', updatePermissionsForRole);
                });

                // Send invite checkbox change
                const sendInviteCheckbox = document.getElementById('send_invite');
                sendInviteCheckbox.addEventListener('change', updateSaveButton);

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

                // Set initial permissions based on role
                updatePermissionsForRole();
                updateSaveButton();
            });
        </script>
    @endpush
@endsection
