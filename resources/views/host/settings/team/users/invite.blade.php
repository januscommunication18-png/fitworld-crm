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

        {{-- Mode Selector --}}
        <div class="form-control max-w-xs">
            <label class="label" for="invite-mode-select">
                <span class="label-text font-medium">Setup Mode</span>
            </label>
            <select id="invite-mode-select" class="select select-bordered w-full" onchange="setInviteMode(this.value)">
                <option value="quick">Quick Invite</option>
                <option value="full">Full Setup</option>
            </select>
        </div>

        <form action="{{ route('settings.team.invite') }}" method="POST">
            @csrf
            <input type="hidden" name="invite_mode" id="invite_mode" value="quick" />

            {{-- Quick Invite Section --}}
            <div id="quick-invite-section">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-header border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span class="icon-[tabler--bolt] size-5 text-primary"></span>
                            </div>
                            <div>
                                <h3 class="card-title text-lg">Quick Invite</h3>
                                <p class="text-base-content/60 text-sm">Send an invitation with just an email and role</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label" for="quick_first_name">
                                    <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                                </label>
                                <div class="relative">
                                    <span class="icon-[tabler--user] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    <input type="text" id="quick_first_name" name="quick_first_name" class="input input-bordered w-full pl-10"
                                           placeholder="John" value="{{ old('quick_first_name') }}"
                                           pattern="[A-Za-z\s\-']+"
                                           oninput="this.value = this.value.replace(/[0-9]/g, '')" />
                                </div>
                                @error('quick_first_name')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label" for="quick_last_name">
                                    <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                                </label>
                                <div class="relative">
                                    <span class="icon-[tabler--user] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    <input type="text" id="quick_last_name" name="quick_last_name" class="input input-bordered w-full pl-10"
                                           placeholder="Doe" value="{{ old('quick_last_name') }}"
                                           pattern="[A-Za-z\s\-']+"
                                           oninput="this.value = this.value.replace(/[0-9]/g, '')" />
                                </div>
                                @error('quick_last_name')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label" for="quick_email">
                                <span class="label-text font-medium">Email Address <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                <span class="icon-[tabler--mail] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                <input type="email" id="quick_email" name="quick_email" class="input input-bordered w-full pl-10 @error('quick_email') input-error @enderror"
                                       placeholder="colleague@example.com" value="{{ old('quick_email') }}" />
                            </div>
                            @error('quick_email')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="cursor-pointer flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-secondary has-[:checked]:bg-secondary/5 hover:border-secondary/30 transition-all">
                                    <input type="radio" name="quick_role" value="admin" class="hidden" {{ old('quick_role') == 'admin' ? 'checked' : '' }} />
                                    <span class="icon-[tabler--shield] size-6 text-secondary"></span>
                                    <span class="font-semibold text-sm">Admin</span>
                                </label>
                                <label class="cursor-pointer flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-warning has-[:checked]:bg-warning/5 hover:border-warning/30 transition-all">
                                    <input type="radio" name="quick_role" value="manager" class="hidden" {{ old('quick_role') == 'manager' ? 'checked' : '' }} />
                                    <span class="icon-[tabler--briefcase] size-6 text-warning"></span>
                                    <span class="font-semibold text-sm">Manager</span>
                                </label>
                                <label class="cursor-pointer flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-info has-[:checked]:bg-info/5 hover:border-info/30 transition-all">
                                    <input type="radio" name="quick_role" value="staff" class="hidden" {{ old('quick_role', 'staff') == 'staff' ? 'checked' : '' }} />
                                    <span class="icon-[tabler--user] size-6 text-info"></span>
                                    <span class="font-semibold text-sm">Staff</span>
                                </label>
                                <label class="cursor-pointer flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-accent has-[:checked]:bg-accent/5 hover:border-accent/30 transition-all">
                                    <input type="radio" name="quick_role" value="instructor" class="hidden" {{ old('quick_role') == 'instructor' ? 'checked' : '' }} />
                                    <span class="icon-[tabler--yoga] size-6 text-accent"></span>
                                    <span class="font-semibold text-sm">Instructor</span>
                                </label>
                            </div>
                            @error('quick_role')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <label class="cursor-pointer flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-base-content/10 hover:border-primary/30 hover:bg-primary/5 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="checkbox" name="quick_send_invite" value="1" class="checkbox checkbox-primary" checked />
                            <div class="flex-1">
                                <span class="font-medium flex items-center gap-2">
                                    <span class="icon-[tabler--mail-forward] size-5 text-primary"></span>
                                    Grant Login Access
                                </span>
                                <span class="text-sm text-base-content/60 block mt-0.5">Team member will receive an email invitation to create their account</span>
                            </div>
                        </label>

                        <p class="text-xs text-base-content/50">
                            <span class="icon-[tabler--info-circle] size-3.5 inline-block align-text-bottom mr-0.5"></span>
                            Default permissions will be applied based on the selected role. You can customize details later from their profile.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Full Setup Sections --}}
            <div id="full-setup-section" class="hidden space-y-0">
                <div class="card bg-base-100 shadow-sm overflow-hidden">

                {{-- 1. Profile (open by default) --}}
                <details class="group accordion-section" open>
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--user] size-5 text-primary"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Profile</h3>
                            <p class="text-base-content/60 text-sm">Basic details, role, and contact information</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-5 border-b border-base-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                                {{-- Manager --}}
                                <label class="cursor-pointer flex items-start gap-4 p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-warning has-[:checked]:bg-warning/5 hover:border-warning/30 transition-all">
                                    <input type="radio" name="role" value="manager" class="radio radio-warning mt-1" {{ old('role') == 'manager' ? 'checked' : '' }} />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--briefcase] size-5 text-warning"></span>
                                            <span class="font-semibold">Manager</span>
                                        </div>
                                        <p class="text-sm text-base-content/60 mt-1">Can manage schedule, bookings, students, and view insights.</p>
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
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $selectedSpecialties = old('specialties', []);
                                @endphp
                                @foreach($specialties ?? \App\Models\Instructor::getCommonSpecialties() as $specialty)
                                    <label class="cursor-pointer flex items-center gap-2 px-4 py-2 rounded-lg border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all hover:border-primary/30">
                                        <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="checkbox checkbox-sm checkbox-primary"
                                            {{ in_array($specialty, $selectedSpecialties) ? 'checked' : '' }} />
                                        <span class="text-sm">{{ $specialty }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>

                {{-- 2. Employment (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--briefcase] size-5 text-secondary"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Employment</h3>
                            <p class="text-base-content/60 text-sm">Compensation and employment type</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-5 border-b border-base-200">
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
                </details>

                {{-- 3. Workload (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--chart-bar] size-5 text-warning"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Workload</h3>
                            <p class="text-base-content/60 text-sm">Weekly hours and class limits</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-5 border-b border-base-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </details>

                {{-- 4. Days (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--calendar-week] size-5 text-accent"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Days</h3>
                            <p class="text-base-content/60 text-sm">Working days of the week</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-4 border-b border-base-200">
                        @php
                            $selectedDays = old('working_days', []);
                            $dayOptions = $dayOptions ?? \App\Models\Instructor::getDayOptions();
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                            @foreach($dayOptions as $value => $label)
                                <label class="cursor-pointer flex flex-col items-center p-4 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/10 hover:border-primary/30 transition-all">
                                    <input type="checkbox" name="working_days[]" value="{{ $value }}" class="hidden"
                                        {{ in_array($value, $selectedDays) ? 'checked' : '' }} />
                                    <span class="text-2xl mb-1">{{ ['Sun' => "\u{2600}\u{FE0F}", 'Mon' => "\u{1F319}", 'Tue' => "\u{1F525}", 'Wed' => "\u{1F4A7}", 'Thu' => "\u{26A1}", 'Fri' => "\u{1F41F}", 'Sat' => "\u{2B50}"][substr($label, 0, 3)] }}</span>
                                    <span class="font-medium text-sm">{{ substr($label, 0, 3) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-base-content/50 text-center">
                            <span class="icon-[tabler--info-circle] size-4 inline-block align-text-bottom mr-1"></span>
                            Select the days this team member typically works
                        </p>
                    </div>
                </details>

                {{-- 5. Hours (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--clock] size-5 text-info"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Hours</h3>
                            <p class="text-base-content/60 text-sm">Availability time ranges</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-4 border-b border-base-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        </div>

                        @php
                            $hasOverrides = !empty(old('availability_by_day'));
                        @endphp
                        <label class="cursor-pointer flex items-center gap-3 px-4 py-3 mt-4 rounded-lg border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all hover:border-primary/30">
                            <input type="checkbox" id="enable-day-overrides" class="checkbox checkbox-sm checkbox-primary" {{ $hasOverrides ? 'checked' : '' }} />
                            <div class="flex-1">
                                <span class="font-medium">Day-Specific Overrides</span>
                                <span class="text-xs text-base-content/60 block">Set different hours for specific days</span>
                            </div>
                            <span class="icon-[tabler--adjustments-horizontal] size-5 text-base-content/40"></span>
                        </label>

                        <div id="day-overrides-container" class="space-y-3 mt-4 {{ $hasOverrides ? '' : 'hidden' }}">
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
                </details>

                {{-- 6. Permissions (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                        <div class="w-10 h-10 rounded-lg bg-error/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--shield-cog] size-5 text-error"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Permissions</h3>
                            <p class="text-base-content/60 text-sm">Customize what this user can access</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 border-b border-base-200">
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
                                'pricing' => 'icon-[tabler--currency-dollar]',
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
                                'pricing' => 'text-success bg-success/10',
                            ];
                        @endphp

                        <div class="space-y-2">
                            @foreach($groupedPermissions as $category => $permissions)
                                {{-- Skip pricing category if feature not enabled --}}
                                @if($category === 'pricing' && !$hasPriceOverrideFeature)
                                    @continue
                                @endif
                                <details class="group/perm border border-base-content/10 rounded-lg overflow-hidden" id="perm-section-{{ $category }}">
                                    <summary class="flex items-center gap-3 p-3 cursor-pointer hover:bg-base-200/50 transition-colors list-none">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $categoryColors[$category] ?? 'text-base-content bg-base-200' }}">
                                            <span class="{{ $categoryIcons[$category] ?? 'icon-[tabler--settings]' }} size-4"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm">{{ ucfirst($category) }}</h4>
                                            <p class="text-xs text-base-content/50" id="perm-count-{{ $category }}">0 of {{ count($permissions) }} enabled</p>
                                        </div>
                                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open/perm:rotate-180"></span>
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
                </details>

                {{-- 7. Certifications (collapsed) --}}
                <details class="group accordion-section">
                    <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200 last:border-b-0">
                        <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--certificate] size-5 text-success"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Certifications</h3>
                            <p class="text-base-content/60 text-sm">Professional certifications and qualifications</p>
                        </div>
                        <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                    </summary>
                    <div class="p-5 space-y-4">
                        <div id="certs-container">
                            @php $oldCerts = old('certs', []); @endphp
                            @if(!empty($oldCerts))
                                @foreach($oldCerts as $i => $oldCert)
                                <div class="cert-entry border border-base-content/10 rounded-lg p-4 space-y-3 relative" data-index="{{ $i }}">
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error absolute top-2 right-2" onclick="removeCert(this)" title="Remove">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="icon-[tabler--certificate] size-4 text-success"></span>
                                        <span class="text-sm font-medium text-base-content/70">Certification #<span class="cert-number">{{ $i + 1 }}</span></span>
                                    </div>
                                    <div>
                                        <label class="label-text font-medium">Name <span class="text-error">*</span></label>
                                        <input type="text" name="certs[{{ $i }}][name]" class="input input-bordered w-full" placeholder="e.g., First Aid, CPR" value="{{ $oldCert['name'] ?? '' }}" required />
                                    </div>
                                    <div>
                                        <label class="label-text font-medium">Certification / Credential Name</label>
                                        <input type="text" name="certs[{{ $i }}][certification_name]" class="input input-bordered w-full" placeholder="e.g., Red Cross Certified, License #12345" value="{{ $oldCert['certification_name'] ?? '' }}" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="label-text font-medium">Expiration Date</label>
                                            <input type="date" name="certs[{{ $i }}][expire_date]" class="input input-bordered w-full" value="{{ $oldCert['expire_date'] ?? '' }}" />
                                        </div>
                                        <div>
                                            <label class="label-text font-medium">Reminder</label>
                                            <select name="certs[{{ $i }}][reminder_days]" class="select select-bordered w-full">
                                                <option value="">No reminder</option>
                                                <option value="7" {{ ($oldCert['reminder_days'] ?? '') == '7' ? 'selected' : '' }}>7 days before</option>
                                                <option value="14" {{ ($oldCert['reminder_days'] ?? '') == '14' ? 'selected' : '' }}>14 days before</option>
                                                <option value="30" {{ ($oldCert['reminder_days'] ?? '') == '30' ? 'selected' : '' }}>30 days before</option>
                                                <option value="60" {{ ($oldCert['reminder_days'] ?? '') == '60' ? 'selected' : '' }}>60 days before</option>
                                                <option value="90" {{ ($oldCert['reminder_days'] ?? '') == '90' ? 'selected' : '' }}>90 days before</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="label-text font-medium">Notes</label>
                                        <textarea name="certs[{{ $i }}][notes]" class="textarea textarea-bordered w-full" rows="2" placeholder="Additional notes...">{{ $oldCert['notes'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button" class="btn btn-soft btn-primary btn-sm w-full" onclick="addCert()">
                            <span class="icon-[tabler--plus] size-4"></span> Add Certification
                        </button>
                    </div>
                </details>

                </div>{{-- end single card wrapper --}}
            </div>{{-- end full-setup-section --}}

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-6">
                <a href="{{ route('settings.team.users') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" id="save-btn" class="btn btn-primary gap-2">
                    <span id="save-btn-icon" class="icon-[tabler--send] size-5"></span>
                    <span id="save-btn-text">Send Invitation</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script>
            let currentMode = 'quick';

            function setInviteMode(mode) {
                currentMode = mode;
                document.getElementById('invite_mode').value = mode;
                document.getElementById('invite-mode-select').value = mode;

                const quickSection = document.getElementById('quick-invite-section');
                const fullSection = document.getElementById('full-setup-section');

                if (mode === 'quick') {
                    quickSection.classList.remove('hidden');
                    fullSection.classList.add('hidden');
                    // Toggle required attributes
                    document.getElementById('quick_first_name').required = true;
                    document.getElementById('quick_last_name').required = true;
                    document.getElementById('quick_email').required = true;
                    document.getElementById('first_name').required = false;
                    document.getElementById('last_name').required = false;
                } else {
                    quickSection.classList.add('hidden');
                    fullSection.classList.remove('hidden');
                    // Toggle required attributes
                    document.getElementById('quick_first_name').required = false;
                    document.getElementById('quick_last_name').required = false;
                    document.getElementById('quick_email').required = false;
                    document.getElementById('first_name').required = true;
                    document.getElementById('last_name').required = true;
                }

                updateSaveButton();
            }

            // Role default permissions
            const roleDefaults = {
                admin: @json(\App\Models\User::getDefaultPermissionsForRole('admin')),
                manager: @json(\App\Models\User::getDefaultPermissionsForRole('manager')),
                staff: @json(\App\Models\User::getDefaultPermissionsForRole('staff')),
                instructor: @json(\App\Models\User::getDefaultPermissionsForRole('instructor'))
            };

            const hasPriceOverrideFeature = {{ $hasPriceOverrideFeature ? 'true' : 'false' }};

            // Update permissions based on role selection
            function updatePermissionsForRole() {
                const role = document.querySelector('input[name="role"]:checked')?.value || 'staff';
                let defaults = roleDefaults[role] || [];

                // Filter out pricing permissions if feature is not enabled
                if (!hasPriceOverrideFeature) {
                    defaults = defaults.filter(function(perm) {
                        return !perm.startsWith('pricing.');
                    });
                }

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
                const btnIcon = document.getElementById('save-btn-icon');
                const btnText = document.getElementById('save-btn-text');

                if (currentMode === 'quick') {
                    btnIcon.className = 'icon-[tabler--send] size-5';
                    btnText.textContent = 'Send Invitation';
                } else {
                    const sendInvite = document.getElementById('send_invite').checked;
                    if (sendInvite) {
                        btnIcon.className = 'icon-[tabler--send] size-5';
                        btnText.textContent = 'Send Invitation';
                    } else {
                        btnIcon.className = 'icon-[tabler--user-plus] size-5';
                        btnText.textContent = 'Add Team Member';
                    }
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
                    const container = document.getElementById('day-overrides-container');
                    container.classList.toggle('hidden', !this.checked);

                    // If enabling overrides, populate empty fields with default times
                    if (this.checked) {
                        const defaultFrom = document.getElementById('availability_default_from').value || '09:00';
                        const defaultTo = document.getElementById('availability_default_to').value || '17:00';

                        container.querySelectorAll('.flatpickr-time-override').forEach(function(input) {
                            if (!input.value) {
                                const isFromField = input.name.includes('[from]');
                                const defaultValue = isFromField ? defaultFrom : defaultTo;
                                const fpInstance = input._flatpickr;
                                if (fpInstance) {
                                    fpInstance.setDate(defaultValue, true);
                                } else {
                                    input.value = defaultValue;
                                }
                            }
                        });
                    }
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

                // Accordion: only one section open at a time (first always stays open)
                var allSections = document.querySelectorAll('.accordion-section');
                var firstSection = allSections[0];
                allSections.forEach(function(details) {
                    details.addEventListener('toggle', function() {
                        if (this.open) {
                            allSections.forEach(function(other) {
                                if (other !== details && other !== firstSection && other.open) {
                                    other.removeAttribute('open');
                                }
                            });
                        }
                    });
                });

                // Restore mode on validation error
                var initialMode = '{{ old('invite_mode', 'quick') }}';
                setInviteMode(initialMode);
            });

            // ========== Certifications ==========
            var certIndex = document.querySelectorAll('.cert-entry').length;

            function addCert() {
                var container = document.getElementById('certs-container');
                var i = certIndex++;
                var html = '<div class="cert-entry border border-base-content/10 rounded-lg p-4 space-y-3 relative" data-index="' + i + '">' +
                    '<button type="button" class="btn btn-ghost btn-xs btn-square text-error absolute top-2 right-2" onclick="removeCert(this)" title="Remove">' +
                    '<span class="icon-[tabler--x] size-4"></span></button>' +
                    '<div class="flex items-center gap-2 mb-1">' +
                    '<span class="icon-[tabler--certificate] size-4 text-success"></span>' +
                    '<span class="text-sm font-medium text-base-content/70">Certification #<span class="cert-number"></span></span></div>' +
                    '<div><label class="label-text font-medium">Name <span class="text-error">*</span></label>' +
                    '<input type="text" name="certs[' + i + '][name]" class="input input-bordered w-full" placeholder="e.g., First Aid, CPR" required /></div>' +
                    '<div><label class="label-text font-medium">Certification / Credential Name</label>' +
                    '<input type="text" name="certs[' + i + '][certification_name]" class="input input-bordered w-full" placeholder="e.g., Red Cross Certified, License #12345" /></div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' +
                    '<div><label class="label-text font-medium">Expiration Date</label>' +
                    '<input type="date" name="certs[' + i + '][expire_date]" class="input input-bordered w-full" /></div>' +
                    '<div><label class="label-text font-medium">Reminder</label>' +
                    '<select name="certs[' + i + '][reminder_days]" class="select select-bordered w-full">' +
                    '<option value="">No reminder</option>' +
                    '<option value="7">7 days before</option>' +
                    '<option value="14">14 days before</option>' +
                    '<option value="30">30 days before</option>' +
                    '<option value="60">60 days before</option>' +
                    '<option value="90">90 days before</option></select></div></div>' +
                    '<div><label class="label-text font-medium">Notes</label>' +
                    '<textarea name="certs[' + i + '][notes]" class="textarea textarea-bordered w-full" rows="2" placeholder="Additional notes..."></textarea></div>' +
                    '</div>';
                container.insertAdjacentHTML('beforeend', html);
                renumberCerts();
            }

            function removeCert(btn) {
                btn.closest('.cert-entry').remove();
                renumberCerts();
            }

            function renumberCerts() {
                document.querySelectorAll('.cert-entry .cert-number').forEach(function(el, idx) {
                    el.textContent = idx + 1;
                });
            }

            // Number existing certs on load
            renumberCerts();
        </script>
    @endpush
@endsection
