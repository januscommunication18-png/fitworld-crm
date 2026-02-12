@php
    $instructor = $instructor ?? null;
@endphp

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

<div class="space-y-6">
    {{-- Step Tab Navigation --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <nav class="flex overflow-x-auto border-b border-base-content/10" id="step-tabs">
                @if(!$instructor)
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-primary text-primary" data-step="1" onclick="goToStep(1)">
                    <span class="icon-[tabler--sparkles] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">1.</span> Get Started
                </button>
                @endif
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 {{ $instructor ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content' }}" data-step="{{ $instructor ? 1 : 2 }}" onclick="goToStep({{ $instructor ? 1 : 2 }})">
                    <span class="icon-[tabler--user] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">{{ $instructor ? '1' : '2' }}.</span> Profile
                </button>
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="{{ $instructor ? 2 : 3 }}" onclick="goToStep({{ $instructor ? 2 : 3 }})">
                    <span class="icon-[tabler--briefcase] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">{{ $instructor ? '2' : '3' }}.</span> Employment
                </button>
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="{{ $instructor ? 3 : 4 }}" onclick="goToStep({{ $instructor ? 3 : 4 }})">
                    <span class="icon-[tabler--chart-bar] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">{{ $instructor ? '3' : '4' }}.</span> Workload
                </button>
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="{{ $instructor ? 4 : 5 }}" onclick="goToStep({{ $instructor ? 4 : 5 }})">
                    <span class="icon-[tabler--calendar-week] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">{{ $instructor ? '4' : '5' }}.</span> Days
                </button>
                <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="{{ $instructor ? 5 : 6 }}" onclick="goToStep({{ $instructor ? 5 : 6 }})">
                    <span class="icon-[tabler--clock] size-5 mr-2 inline-block align-middle"></span>
                    <span class="hidden sm:inline">{{ $instructor ? '5' : '6' }}.</span> Hours
                </button>
            </nav>
        </div>
    </div>

    @if(!$instructor)
    {{-- Step 1: Get Started (Create mode only) --}}
    <div id="step-1" class="step-content">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-header border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                        <span class="icon-[tabler--sparkles] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Get Started</h3>
                        <p class="text-base-content/60 text-sm">Choose how you want to add the instructor</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @php
                    $hasAvailableUsers = isset($availableUsers) && $availableUsers->isNotEmpty();
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Option 1: Link Existing User --}}
                    <label class="block {{ $hasAvailableUsers ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }} p-6 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 {{ $hasAvailableUsers ? 'hover:border-primary/30' : '' }} transition-all h-full">
                        <input type="radio" name="instructor_type" value="existing" class="hidden" {{ !$hasAvailableUsers ? 'disabled' : '' }} />
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                <span class="icon-[tabler--user-plus] size-8 text-primary"></span>
                            </div>
                            <h4 class="font-semibold text-lg mb-2">Link Existing User</h4>
                            <p class="text-sm text-base-content/60">
                                Select a team member who already has the instructor role to complete their profile
                            </p>
                            @if($hasAvailableUsers)
                            <span class="badge badge-primary badge-soft mt-3">{{ $availableUsers->count() }} available</span>
                            @else
                            <span class="badge badge-neutral badge-soft mt-3">None available</span>
                            @endif
                        </div>
                    </label>

                    {{-- Option 2: Create New --}}
                    <label class="block cursor-pointer p-6 rounded-xl border-2 border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/5 hover:border-primary/30 transition-all h-full">
                        <input type="radio" name="instructor_type" value="new" class="hidden" checked />
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-secondary/10 flex items-center justify-center mb-4">
                                <span class="icon-[tabler--user-star] size-8 text-secondary"></span>
                            </div>
                            <h4 class="font-semibold text-lg mb-2">Create New Instructor</h4>
                            <p class="text-sm text-base-content/60">
                                Add a new instructor profile from scratch with optional login credentials
                            </p>
                            <span class="badge badge-secondary badge-soft mt-3">Recommended</span>
                        </div>
                    </label>
                </div>

                {{-- User Selection Dropdown (shown when "Link Existing User" is selected) --}}
                <div id="user-selection-wrapper" class="mt-6 hidden">
                    <div class="form-control">
                        <label class="label" for="user_id">
                            <span class="label-text font-medium">Select Team Member</span>
                        </label>
                        <select id="user_id" name="user_id" class="hidden"
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search by name or email...",
                                "placeholder": "Choose a team member...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Choose a team member...</option>
                            @if(isset($availableUsers))
                            @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}"
                                data-name="{{ $user->full_name }}"
                                data-email="{{ $user->email }}"
                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }} — {{ $user->email }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">These team members have the instructor role but need their profile completed</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Profile (Step 1 for edit mode) --}}
    <div id="step-{{ $instructor ? 1 : 2 }}" class="step-content {{ $instructor ? '' : 'hidden' }}">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-header border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--user] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Profile Information</h3>
                        <p class="text-base-content/60 text-sm">Basic details about the instructor</p>
                    </div>
                </div>
            </div>
            <div class="card-body space-y-5">
                @if($instructor)
                {{-- Photo Upload (Edit only) --}}
                <div class="flex items-center gap-4 p-4 bg-base-200/50 rounded-xl">
                    <div id="photo-preview" class="avatar {{ $instructor->photo_url ? '' : 'placeholder' }}">
                        @if($instructor->photo_url)
                        <div class="w-20 rounded-full ring ring-primary/20 ring-offset-2 ring-offset-base-100">
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                        </div>
                        @else
                        <div class="bg-gradient-to-br from-primary to-secondary text-primary-content w-20 rounded-full">
                            <span id="photo-initials" class="text-2xl font-bold">{{ $instructor->initials }}</span>
                        </div>
                        @endif
                    </div>
                    <div>
                        <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp" class="hidden" />
                        <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('photo-input').click()">
                            <span class="icon-[tabler--upload] size-4"></span> Upload Photo
                        </button>
                        <button type="button" id="photo-remove" class="btn btn-sm btn-ghost text-error {{ $instructor->photo_url ? '' : 'hidden' }}">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                        <p class="text-xs text-base-content/50 mt-2">JPG, PNG or WebP. Max 2MB.</p>
                    </div>
                </div>
                @endif

                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text font-medium">Full Name <span class="text-error">*</span></span>
                    </label>
                    <div class="relative">
                        <span class="icon-[tabler--user] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        <input type="text" id="name" name="name" class="input input-bordered w-full pl-10 @error('name') input-error @enderror" required
                            value="{{ old('name', $instructor?->name) }}" placeholder="Jane Smith" />
                    </div>
                    @error('name')
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
                            value="{{ old('email', $instructor?->email) }}" placeholder="jane@example.com" />
                    </div>
                    @error('email')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                @if(!$instructor)
                <label class="cursor-pointer flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-base-content/10 hover:border-primary/30 hover:bg-primary/5 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                    <input type="checkbox" name="send_invite" value="1" class="checkbox checkbox-primary" {{ old('send_invite', true) ? 'checked' : '' }} />
                    <div class="flex-1">
                        <span class="font-medium flex items-center gap-2">
                            <span class="icon-[tabler--mail-forward] size-5 text-primary"></span>
                            Send Login Invitation
                        </span>
                        <span class="text-sm text-base-content/60 block mt-0.5">Instructor will receive an email to create their account</span>
                    </div>
                </label>
                @endif

                <div class="form-control">
                    <label class="label" for="phone">
                        <span class="label-text font-medium">Phone Number</span>
                    </label>
                    <div class="relative">
                        <span class="icon-[tabler--phone] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        <input type="text" id="phone" name="phone" class="input input-bordered w-full pl-10 @error('phone') input-error @enderror"
                            value="{{ old('phone', $instructor?->phone) }}" placeholder="+1 (555) 123-4567" />
                    </div>
                </div>

                <div class="form-control">
                    <label class="label" for="bio">
                        <span class="label-text font-medium">Bio</span>
                        <span class="label-text-alt text-base-content/50">Optional</span>
                    </label>
                    <textarea id="bio" name="bio" class="textarea textarea-bordered w-full @error('bio') input-error @enderror" rows="3"
                        placeholder="A brief introduction about this instructor...">{{ old('bio', $instructor?->bio) }}</textarea>
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
                        placeholder="RYT-200, ACE Certified, etc.">{{ old('certifications', $instructor?->certifications) }}</textarea>
                </div>

                <div class="divider text-base-content/40 text-xs">VISIBILITY</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer flex items-center gap-3 p-4 rounded-xl border border-base-content/10 hover:border-primary/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <input type="checkbox" name="is_visible" value="1" class="checkbox checkbox-primary"
                            {{ old('is_visible', $instructor?->is_visible ?? true) ? 'checked' : '' }} />
                        <div>
                            <span class="font-medium block">Public Profile</span>
                            <span class="text-xs text-base-content/60">Visible on booking page</span>
                        </div>
                    </label>
                    @if($instructor)
                    <label class="cursor-pointer flex items-center gap-3 p-4 rounded-xl border border-base-content/10 hover:border-primary/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                            {{ old('is_active', $instructor?->is_active ?? true) ? 'checked' : '' }} />
                        <div>
                            <span class="font-medium block">Active Status</span>
                            <span class="text-xs text-base-content/60">Can be assigned to classes</span>
                        </div>
                    </label>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Step 3: Employment (Step 2 for edit mode) --}}
    <div id="step-{{ $instructor ? 2 : 3 }}" class="step-content hidden">
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
                <div class="alert alert-info alert-soft">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span>Employment details are required to activate this instructor for class assignments.</span>
                </div>

                <div class="form-control">
                    <label class="label" for="employment_type">
                        <span class="label-text font-medium">Employment Type <span class="text-error">*</span></span>
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
                        <span class="label-text font-medium">Rate Type <span class="text-error">*</span></span>
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
                        <span class="label-text font-medium">Rate Amount <span class="text-error">*</span></span>
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

    {{-- Step 4: Workload (Step 3 for edit mode) --}}
    <div id="step-{{ $instructor ? 3 : 4 }}" class="step-content hidden">
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

    {{-- Step 5: Working Days (Step 4 for edit mode) --}}
    <div id="step-{{ $instructor ? 4 : 5 }}" class="step-content hidden">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-header border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-week] size-5 text-warning"></span>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Working Days <span class="text-error">*</span></h3>
                        <p class="text-base-content/60 text-sm">Select which days this instructor is available</p>
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
                            {{ in_array($value, $selectedDays) ? 'checked' : '' }} />
                        <span class="text-2xl mb-1">{{ ['Sun' => '☀️', 'Mon' => '🌙', 'Tue' => '🔥', 'Wed' => '💧', 'Thu' => '⚡', 'Fri' => '🐟', 'Sat' => '⭐'][substr($label, 0, 3)] }}</span>
                        <span class="font-medium text-sm">{{ substr($label, 0, 3) }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="text-sm text-base-content/50 mt-4 text-center">
                    <span class="icon-[tabler--info-circle] size-4 inline-block align-text-bottom mr-1"></span>
                    Select at least one day to activate this instructor
                </p>
            </div>
        </div>
    </div>

    {{-- Step 6: Availability Hours (Step 5 for edit mode) --}}
    <div id="step-{{ $instructor ? 5 : 6 }}" class="step-content hidden">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-header border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--clock] size-5 text-success"></span>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Availability Hours <span class="text-error">*</span></h3>
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

    {{-- Navigation --}}
    <div class="flex items-center justify-between pt-4">
        <button type="button" id="prev-step-btn" class="btn btn-ghost gap-2 hidden" onclick="prevStep()">
            <span class="icon-[tabler--chevron-left] size-5"></span>
            Previous
        </button>
        <div class="flex gap-3 ml-auto">
            <a href="{{ route('instructors.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="button" id="next-step-btn" class="btn btn-primary gap-2" onclick="nextStep()">
                Next Step
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </button>
            <button type="submit" id="save-btn" class="btn btn-primary gap-2 hidden">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $instructor ? 'Save Changes' : 'Create Instructor' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
const isEditMode = {{ $instructor ? 'true' : 'false' }};
const totalSteps = isEditMode ? 5 : 6;
let currentStep = 1;

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
    if (currentStep < totalSteps) showStep(currentStep + 1);
}

function prevStep() {
    if (currentStep > 1) showStep(currentStep - 1);
}

function goToStep(step) {
    showStep(step);
}

document.addEventListener('DOMContentLoaded', function() {
    @if(!$instructor)
    // Instructor type selection (Create mode only)
    const typeRadios = document.querySelectorAll('input[name="instructor_type"]');
    const userSelectionWrapper = document.getElementById('user-selection-wrapper');
    const userSelect = document.getElementById('user_id');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const sendInviteCheckbox = document.querySelector('input[name="send_invite"]');
    const sendInviteWrapper = sendInviteCheckbox?.closest('label');
    let userSelectInitialized = false;

    function initUserSelect() {
        if (userSelectInitialized || !userSelect) return;
        // Initialize the advanced select if HSSelect is available
        if (typeof HSSelect !== 'undefined') {
            HSSelect.autoInit();
        }
        userSelectInitialized = true;
    }

    function updateInstructorTypeUI() {
        const selectedType = document.querySelector('input[name="instructor_type"]:checked')?.value;

        if (selectedType === 'existing') {
            userSelectionWrapper.classList.remove('hidden');
            // Initialize the select after showing the wrapper
            setTimeout(initUserSelect, 50);
        } else {
            userSelectionWrapper.classList.add('hidden');
            // Reset user selection
            if (userSelect) {
                userSelect.value = '';
                // Trigger change to reset the form fields
                userSelect.dispatchEvent(new Event('change'));
            }
            // Clear and enable fields
            if (nameInput) {
                nameInput.value = '';
                nameInput.readOnly = false;
                nameInput.classList.remove('bg-base-200', 'cursor-not-allowed');
            }
            if (emailInput) {
                emailInput.value = '';
                emailInput.readOnly = false;
                emailInput.classList.remove('bg-base-200', 'cursor-not-allowed');
            }
            // Show send invite option
            if (sendInviteWrapper) {
                sendInviteWrapper.classList.remove('hidden');
            }
        }
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', updateInstructorTypeUI);
    });

    // User selection change
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value) {
                // User selected - pre-fill and disable name/email
                nameInput.value = selectedOption.dataset.name || '';
                emailInput.value = selectedOption.dataset.email || '';
                nameInput.readOnly = true;
                emailInput.readOnly = true;
                nameInput.classList.add('bg-base-200', 'cursor-not-allowed');
                emailInput.classList.add('bg-base-200', 'cursor-not-allowed');

                // Hide send invite option (user already has account)
                if (sendInviteWrapper) {
                    sendInviteWrapper.classList.add('hidden');
                    sendInviteCheckbox.checked = false;
                }
            } else {
                // No user selected - enable name/email
                nameInput.value = '';
                emailInput.value = '';
                nameInput.readOnly = false;
                emailInput.readOnly = false;
                nameInput.classList.remove('bg-base-200', 'cursor-not-allowed');
                emailInput.classList.remove('bg-base-200', 'cursor-not-allowed');

                // Show send invite option
                if (sendInviteWrapper) {
                    sendInviteWrapper.classList.remove('hidden');
                    sendInviteCheckbox.checked = true;
                }
            }
        });

        // Trigger on page load if user was pre-selected
        if (userSelect.value) {
            userSelect.dispatchEvent(new Event('change'));
        }
    }

    // Initialize on page load
    updateInstructorTypeUI();
    @endif

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

    @if($instructor)
    // Photo upload handling
    document.getElementById('photo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);

        fetch('{{ route("instructors.photo", $instructor) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const photoPreview = document.getElementById('photo-preview');
                photoPreview.classList.remove('placeholder');
                photoPreview.innerHTML = '<div class="w-20 rounded-full ring ring-primary/20 ring-offset-2 ring-offset-base-100"><img src="' + data.path + '" /></div>';
                document.getElementById('photo-remove').classList.remove('hidden');
            }
        })
        .catch(error => console.error('Upload failed:', error));
    });

    document.getElementById('photo-remove').addEventListener('click', function() {
        fetch('{{ route("instructors.photo.remove", $instructor) }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const name = document.getElementById('name').value || '--';
                const initials = name.substring(0, 2).toUpperCase();
                const photoPreview = document.getElementById('photo-preview');
                photoPreview.classList.add('placeholder');
                photoPreview.innerHTML = '<div class="bg-gradient-to-br from-primary to-secondary text-primary-content w-20 rounded-full"><span class="text-2xl font-bold">' + initials + '</span></div>';
                document.getElementById('photo-remove').classList.add('hidden');
            }
        })
        .catch(error => console.error('Remove failed:', error));
    });
    @endif
});
</script>
@endpush
