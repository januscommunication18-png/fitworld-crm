@php
    $instructor = $instructor ?? null;
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        z-index: 9999 !important;
    }
    .flatpickr-calendar.hasTime.noCalendar {
        width: auto !important;
        min-width: 200px;
    }
    .flatpickr-time {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px;
        max-height: none !important;
        height: auto !important;
        padding: 10px !important;
    }
    .flatpickr-time .numInputWrapper {
        width: 50px !important;
        height: 40px !important;
    }
    .flatpickr-time .numInputWrapper input {
        font-size: 1.25rem !important;
    }
    .flatpickr-time .flatpickr-time-separator {
        font-size: 1.25rem !important;
        line-height: 40px !important;
    }
    .flatpickr-time .flatpickr-am-pm {
        width: 50px !important;
        height: 40px !important;
        line-height: 40px !important;
        font-size: 0.875rem !important;
    }
</style>
@endpush

<div class="space-y-6 max-w-4xl mx-auto">
    {{-- Step Navigation --}}
    <div class="card bg-base-100">
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
            </nav>
        </div>
    </div>

    {{-- Step 1: Profile --}}
    <div id="step-1" class="step-content">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body space-y-4">
                @if($instructor)
                {{-- Photo Upload (Edit only) --}}
                <div class="flex items-center gap-4">
                    <div id="photo-preview" class="avatar {{ $instructor->photo_url ? '' : 'placeholder' }}">
                        @if($instructor->photo_url)
                        <div class="w-16 rounded-full">
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                        </div>
                        @else
                        <div class="bg-primary text-primary-content w-16 rounded-full">
                            <span id="photo-initials" class="text-xl">{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
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
                        <p class="text-xs text-base-content/60 mt-1">JPG, PNG or WebP. Max 2MB.</p>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Name <span class="text-error">*</span></label>
                        <input type="text" id="name" name="name" class="input w-full @error('name') input-error @enderror" required
                            value="{{ old('name', $instructor?->name) }}" placeholder="Jane Smith" />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="email">Email @if(!$instructor)<span class="text-error">*</span>@endif</label>
                        <input type="email" id="email" name="email" class="input w-full @error('email') input-error @enderror"
                            {{ !$instructor ? 'required' : '' }}
                            value="{{ old('email', $instructor?->email) }}" placeholder="jane@example.com" />
                        @if(!$instructor)
                        <p class="text-xs text-base-content/60 mt-1">Login invite will be sent automatically</p>
                        @endif
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="input w-full @error('phone') input-error @enderror"
                        value="{{ old('phone', $instructor?->phone) }}" placeholder="+1 (555) 123-4567" />
                    @error('phone')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="textarea w-full @error('bio') input-error @enderror" rows="3"
                        placeholder="A brief introduction about this instructor...">{{ old('bio', $instructor?->bio) }}</textarea>
                    @error('bio')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text mb-2 block">Specialties</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $selectedSpecialties = old('specialties', $instructor?->specialties ?? []);
                        @endphp
                        @foreach($specialties as $specialty)
                        <label class="cursor-pointer flex items-center gap-2 px-3 py-1.5 rounded-lg border border-base-content/10 hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                            <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="checkbox checkbox-primary checkbox-sm"
                                {{ in_array($specialty, $selectedSpecialties) ? 'checked' : '' }} />
                            <span class="text-sm">{{ $specialty }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label-text" for="certifications">Certifications</label>
                    <textarea id="certifications" name="certifications" class="textarea w-full @error('certifications') input-error @enderror" rows="2"
                        placeholder="RYT-200, ACE Certified, etc.">{{ old('certifications', $instructor?->certifications) }}</textarea>
                    @error('certifications')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-lg border border-base-content/10 hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="checkbox" name="is_visible" value="1" class="checkbox checkbox-primary"
                            {{ old('is_visible', $instructor?->is_visible ?? true) ? 'checked' : '' }} />
                        <span>
                            <span class="block font-medium">Visible on Booking Page</span>
                            <span class="text-xs text-base-content/60">Show on public booking page</span>
                        </span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-lg border border-base-content/10 hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                            {{ old('is_active', $instructor?->is_active ?? true) ? 'checked' : '' }} />
                        <span>
                            <span class="block font-medium">Active</span>
                            <span class="text-xs text-base-content/60">Can be assigned to classes</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Employment --}}
    <div id="step-2" class="step-content hidden">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Employment Details</h3>
                <p class="text-base-content/60 text-sm">Define how this instructor is employed and compensated.</p>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type" class="hidden"
                            data-select='{
                                "placeholder": "Not specified",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Not specified</option>
                            @foreach($employmentTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('employment_type', $instructor?->employment_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-text" for="rate_type">Rate Type</label>
                        <select id="rate_type" name="rate_type" class="hidden"
                            data-select='{
                                "placeholder": "Not specified",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Not specified</option>
                            @foreach($rateTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('rate_type', $instructor?->rate_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="rate-amount-wrapper" class="{{ old('rate_type', $instructor?->rate_type) ? '' : 'hidden' }}">
                    <label class="label-text" for="rate_amount">Rate Amount ($)</label>
                    <input type="number" id="rate_amount" name="rate_amount" class="input w-full @error('rate_amount') input-error @enderror"
                        step="0.01" min="0" placeholder="0.00" value="{{ old('rate_amount', $instructor?->rate_amount) }}" />
                    @error('rate_amount')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="compensation_notes">Compensation Notes</label>
                    <textarea id="compensation_notes" name="compensation_notes" class="textarea w-full" rows="2"
                        placeholder="e.g., Paid per attended class, Flat monthly retainer...">{{ old('compensation_notes', $instructor?->compensation_notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 3: Workload --}}
    <div id="step-3" class="step-content hidden">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Workload Limits</h3>
                <p class="text-base-content/60 text-sm">Set workload limits to help prevent over-scheduling.</p>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="hours_per_week">Hours Allocated per Week</label>
                        <input type="number" id="hours_per_week" name="hours_per_week" class="input w-full @error('hours_per_week') input-error @enderror"
                            step="0.5" min="0" max="168" placeholder="e.g., 20" value="{{ old('hours_per_week', $instructor?->hours_per_week) }}" />
                        <p class="text-xs text-base-content/60 mt-1">Used for over-scheduling warnings</p>
                        @error('hours_per_week')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="max_classes_per_week">Max Classes per Week</label>
                        <input type="number" id="max_classes_per_week" name="max_classes_per_week" class="input w-full @error('max_classes_per_week') input-error @enderror"
                            min="0" max="100" placeholder="e.g., 15" value="{{ old('max_classes_per_week', $instructor?->max_classes_per_week) }}" />
                        <p class="text-xs text-base-content/60 mt-1">Optional soft limit</p>
                        @error('max_classes_per_week')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 4: Working Days --}}
    <div id="step-4" class="step-content hidden">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Working Days</h3>
                <p class="text-base-content/60 text-sm">Select the days this instructor is available to work. Leave all unchecked to allow scheduling on any day.</p>
            </div>
            <div class="card-body">
                @php
                    $selectedDays = old('working_days', $instructor?->working_days ?? []);
                @endphp
                <div class="flex flex-wrap gap-3">
                    @foreach($dayOptions as $value => $label)
                    <label class="cursor-pointer flex items-center gap-2 px-5 py-3 rounded-lg border border-base-content/10 hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="checkbox" name="working_days[]" value="{{ $value }}" class="checkbox checkbox-primary"
                            {{ in_array($value, $selectedDays) ? 'checked' : '' }} />
                        <span class="font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Step 5: Availability Hours --}}
    <div id="step-5" class="step-content hidden">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Availability Hours</h3>
                <p class="text-base-content/60 text-sm">Set default working hours. You can optionally customize per day.</p>
            </div>
            <div class="card-body space-y-6">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <h4 class="font-medium mb-3">Default Working Hours</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="availability_default_from">From</label>
                            <input type="text" id="availability_default_from" name="availability_default_from"
                                class="input w-full flatpickr-time" placeholder="Select time..."
                                value="{{ old('availability_default_from', $instructor?->availability_default_from) }}" />
                        </div>
                        <div>
                            <label class="label-text" for="availability_default_to">To</label>
                            <input type="text" id="availability_default_to" name="availability_default_to"
                                class="input w-full flatpickr-time" placeholder="Select time..."
                                value="{{ old('availability_default_to', $instructor?->availability_default_to) }}" />
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium">Day-Specific Overrides</h4>
                        @php
                            $hasOverrides = !empty(old('availability_by_day', $instructor?->availability_by_day));
                        @endphp
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="enable-day-overrides" class="checkbox checkbox-sm" {{ $hasOverrides ? 'checked' : '' }} />
                            <span class="text-sm">Enable</span>
                        </label>
                    </div>
                    <div id="day-overrides-container" class="space-y-3 {{ $hasOverrides ? '' : 'hidden' }}">
                        @php
                            $overrides = old('availability_by_day', $instructor?->availability_by_day ?? []);
                        @endphp
                        @foreach($dayOptions as $value => $label)
                        <div class="grid grid-cols-5 gap-2 items-center">
                            <div class="col-span-1">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="availability_by_day[{{ $value }}][from]"
                                    class="input input-sm w-full flatpickr-time-override" placeholder="From"
                                    value="{{ $overrides[$value]['from'] ?? '' }}" />
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="availability_by_day[{{ $value }}][to]"
                                    class="input input-sm w-full flatpickr-time-override" placeholder="To"
                                    value="{{ $overrides[$value]['to'] ?? '' }}" />
                            </div>
                        </div>
                        @endforeach
                        <p class="text-xs text-base-content/50">Leave blank to use default hours for that day.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex items-center justify-between">
        <button type="button" id="prev-step-btn" class="btn btn-ghost hidden" onclick="prevStep()">
            <span class="icon-[tabler--chevron-left] size-5"></span> Previous
        </button>
        <div class="flex gap-3 ml-auto">
            <a href="{{ route('settings.team.instructors') }}" class="btn btn-ghost">Cancel</a>
            <button type="button" id="next-step-btn" class="btn btn-primary" onclick="nextStep()">
                Next <span class="icon-[tabler--chevron-right] size-5"></span>
            </button>
            <button type="submit" id="save-btn" class="btn btn-primary hidden">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $instructor ? 'Save Changes' : 'Add Instructor' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Multi-step form navigation
let currentStep = 1;
const totalSteps = 5;

function showStep(step) {
    // Hide all steps
    for (let i = 1; i <= totalSteps; i++) {
        document.getElementById('step-' + i).classList.add('hidden');
    }
    // Show current step
    document.getElementById('step-' + step).classList.remove('hidden');

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

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Rate type toggle - observe for HSSelect changes
    var rateTypeSelect = document.getElementById('rate_type');

    function updateRateAmountVisibility() {
        document.getElementById('rate-amount-wrapper').classList.toggle('hidden', !rateTypeSelect.value);
    }

    // Listen for native change event
    rateTypeSelect.addEventListener('change', updateRateAmountVisibility);

    // Also observe for HSSelect mutations
    var rateTypeObserver = new MutationObserver(updateRateAmountVisibility);
    rateTypeObserver.observe(rateTypeSelect, { attributes: true, childList: true, subtree: true });

    // Day overrides toggle
    document.getElementById('enable-day-overrides').addEventListener('change', function() {
        document.getElementById('day-overrides-container').classList.toggle('hidden', !this.checked);
    });

    // Initialize Flatpickr for time inputs
    var timePickerConfig = {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: false,
        minuteIncrement: 15,
        altInput: true,
        altFormat: 'h:i K',
        altInputClass: 'input w-full',
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
        altInputClass: 'input input-sm w-full',
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

        fetch('{{ route("settings.team.instructors.photo", $instructor) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const photoPreview = document.getElementById('photo-preview');
                photoPreview.classList.remove('placeholder');
                photoPreview.innerHTML = '<div class="w-16 rounded-full"><img src="' + data.path + '" /></div>';
                document.getElementById('photo-remove').classList.remove('hidden');
            }
        })
        .catch(error => console.error('Upload failed:', error));
    });

    // Photo remove handling
    document.getElementById('photo-remove').addEventListener('click', function() {
        fetch('{{ route("settings.team.instructors.photo.remove", $instructor) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const name = document.getElementById('name').value || '--';
                const initials = name.substring(0, 2).toUpperCase();
                const photoPreview = document.getElementById('photo-preview');
                photoPreview.classList.add('placeholder');
                photoPreview.innerHTML = '<div class="bg-primary text-primary-content w-16 rounded-full"><span class="text-xl">' + initials + '</span></div>';
                document.getElementById('photo-remove').classList.add('hidden');
            }
        })
        .catch(error => console.error('Remove failed:', error));
    });
    @endif
});
</script>
@endpush
