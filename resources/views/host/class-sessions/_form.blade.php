@php
    $classSession = $classSession ?? null;
    $selectedClassPlanId = $selectedClassPlanId ?? $classSession?->class_plan_id;
    $selectedDate = $selectedDate ?? $classSession?->start_time?->format('Y-m-d') ?? now()->format('Y-m-d');
    $selectedLocationId = old('location_id', $classSession?->location_id);
    $selectedLocation = isset($locations) && $selectedLocationId ? $locations->firstWhere('id', $selectedLocationId) : null;
    $selectedClassType = $selectedLocation?->location_types[0] ?? $selectedLocation?->location_type ?? '';

    // Handle room IDs - support both old room_ids[] array and existing single room_id
    $selectedRoomIds = old('room_ids', []);
    if (empty($selectedRoomIds) && $classSession?->room_id) {
        $selectedRoomIds = [$classSession->room_id];
    }
@endphp

@push('styles')
<style>
    .flatpickr-input.input,
    .flatpickr-alt-input {
        height: 2.5rem !important;
        min-height: 2.5rem !important;
    }
    .flatpickr-calendar {
        z-index: 9999 !important;
    }
    /* Time picker layout fixes */
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
    {{-- Main Form --}}
    <div class="space-y-6">
        {{-- Class Selection --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Class Selection</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="class_plan_id">Class Plan</label>
                    <select id="class_plan_id" name="class_plan_id" class="hidden @error('class_plan_id') input-error @enderror" required
                        data-select='{
                            "hasSearch": true,
                            "searchPlaceholder": "Search classes...",
                            "placeholder": "Select a class...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">Select a class...</option>
                        @foreach($classPlans as $plan)
                        <option value="{{ $plan->id }}"
                            data-duration="{{ $plan->default_duration_minutes }}"
                            data-capacity="{{ $plan->default_capacity }}"
                            data-price="{{ $plan->default_price }}"
                            data-color="{{ $plan->color }}"
                            {{ old('class_plan_id', $selectedClassPlanId) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ $plan->formatted_duration }})
                        </option>
                        @endforeach
                    </select>
                    @error('class_plan_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="title">Custom Title (optional)</label>
                    <input type="text" id="title" name="title"
                        value="{{ old('title', $classSession?->title) }}"
                        class="input w-full @error('title') input-error @enderror"
                        placeholder="Leave empty to use class plan name">
                    @error('title')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Instructors --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Instructors</h3>
            </div>
            <div class="card-body space-y-4">
                {{-- Primary Instructor --}}
                <div>
                    <label class="label-text" for="primary_instructor_id">Primary Instructor</label>
                    <select id="primary_instructor_id" name="primary_instructor_id" class="hidden @error('primary_instructor_id') input-error @enderror" required
                        data-select='{
                            "hasSearch": true,
                            "searchPlaceholder": "Search instructors...",
                            "placeholder": "Select an instructor...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">Select an instructor...</option>
                        @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ old('primary_instructor_id', $classSession?->primary_instructor_id) == $instructor->id ? 'selected' : '' }}>
                            {{ $instructor->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('primary_instructor_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Backup Instructors (Multiple) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Backup Instructors (optional)</label>
                        <button type="button" id="add-backup-instructor" class="btn btn-ghost btn-sm btn-circle text-primary" title="Add backup instructor">
                            <span class="icon-[tabler--plus] size-5"></span>
                        </button>
                    </div>
                    <div id="backup-instructors-container" class="space-y-2">
                        @php
                            $backupInstructorIds = old('backup_instructor_ids', $classSession?->backupInstructors?->pluck('id')->toArray() ?? []);
                            if (empty($backupInstructorIds)) {
                                $backupInstructorIds = [null]; // Show one empty row by default
                            }
                        @endphp
                        @foreach($backupInstructorIds as $index => $backupId)
                        <div class="backup-instructor-row flex items-center gap-2" data-index="{{ $index }}">
                            <div class="flex-1">
                                <select name="backup_instructor_ids[]" class="select w-full backup-instructor-select">
                                    <option value="">Select backup instructor...</option>
                                    @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ $backupId == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="remove-backup-instructor btn btn-ghost btn-sm btn-circle text-error flex-shrink-0" title="Remove">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-base-content/60 text-sm mt-2">Add backup instructors in order of priority. First backup will be called first.</p>
                    @error('backup_instructor_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('backup_instructor_ids.*')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Date & Time --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Date & Time</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="session_date">Date</label>
                        <input type="text" id="session_date" name="session_date"
                            value="{{ old('session_date', $selectedDate) }}"
                            class="input w-full flatpickr-date @error('session_date') input-error @enderror"
                            placeholder="Select date..."
                            required>
                        @error('session_date')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="session_time">Start Time</label>
                        <input type="text" id="session_time" name="session_time"
                            value="{{ old('session_time', $classSession?->start_time?->format('H:i') ?? '09:00') }}"
                            class="input w-full flatpickr-time @error('session_time') input-error @enderror"
                            placeholder="Select time..."
                            required>
                        @error('session_time')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="duration_minutes">Duration (minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes"
                            value="{{ old('duration_minutes', $classSession?->duration_minutes ?? 60) }}"
                            class="input w-full @error('duration_minutes') input-error @enderror"
                            min="5" max="480" required>
                        @error('duration_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="time-preview" class="text-sm text-base-content/60 hidden">
                    Session: <span id="preview-start"></span> - <span id="preview-end"></span>
                </div>
            </div>
        </div>

        {{-- Recurrence (only for create) --}}
        @if(!$classSession)
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Recurrence</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="is_recurring" name="is_recurring" value="1" class="checkbox checkbox-primary checkbox-sm" {{ old('is_recurring') ? 'checked' : '' }}>
                    <span class="label-text">Create recurring sessions</span>
                </label>

                <div id="recurrence-options" class="space-y-4 {{ old('is_recurring') ? '' : 'hidden' }}">
                    <div>
                        <label class="label-text">Days of Week</label>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $day)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200">
                                <input type="checkbox" name="recurrence_days[]" value="{{ $index }}" class="checkbox checkbox-sm checkbox-primary"
                                    {{ is_array(old('recurrence_days')) && in_array($index, old('recurrence_days')) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="label-text">End Recurrence</label>
                        <div class="space-y-2 mt-2">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="recurrence_end_type" value="never" class="radio radio-sm radio-primary" {{ old('recurrence_end_type', 'never') === 'never' ? 'checked' : '' }}>
                                <span class="label-text">Never (creates up to 52 sessions)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="recurrence_end_type" value="after" class="radio radio-sm radio-primary" {{ old('recurrence_end_type') === 'after' ? 'checked' : '' }}>
                                <span class="label-text">After</span>
                                <input type="number" name="recurrence_count" value="{{ old('recurrence_count', 10) }}" class="input input-sm w-20" min="2" max="52">
                                <span class="label-text">occurrences</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="recurrence_end_type" value="on" class="radio radio-sm radio-primary" {{ old('recurrence_end_type') === 'on' ? 'checked' : '' }}>
                                <span class="label-text">On date</span>
                                <input type="text" name="recurrence_end_date" id="recurrence_end_date" value="{{ old('recurrence_end_date') }}" class="input input-sm flatpickr-date" placeholder="Select date...">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Location --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Location</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Class/Location Type Filter --}}
                    <div>
                        <label class="label-text" for="class_location_type">Class Type</label>
                        <select id="class_location_type" class="hidden"
                            data-select='{
                                "placeholder": "All Types",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">All Types</option>
                            @foreach(\App\Models\Location::getLocationTypeOptions() as $type => $label)
                            <option value="{{ $type }}" {{ $selectedClassType === $type ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="location_id">Location</label>
                        <select id="location_id" name="location_id" class="hidden @error('location_id') input-error @enderror"
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search locations...",
                                "placeholder": "Select a location...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="" data-type="" data-types="[]">Select a location...</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}"
                                data-type="{{ $location->location_type }}"
                                data-types="{{ json_encode($location->location_types ?? []) }}"
                                data-rooms="{{ $location->rooms->toJson() }}"
                                data-public-notes="{{ $location->public_location_notes }}"
                                data-virtual-platform="{{ $location->virtual_platform_label }}"
                                {{ old('location_id', $classSession?->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Room selection (for In-Person Studio only) --}}
                <div id="room-wrapper" class="hidden">
                    <label class="label-text" for="room_id">Room(s)</label>
                    <select id="room_id" name="room_ids[]" multiple class="hidden @error('room_ids') input-error @enderror"
                        data-select='{
                            "placeholder": "Select rooms...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                    </select>
                    @error('room_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('room_ids.*')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location Notes (for non In-Person types) --}}
                <div id="location-notes-wrapper" class="hidden">
                    <label class="label-text" for="location_notes">Location Notes</label>
                    <textarea id="location_notes" name="location_notes" rows="2"
                        class="textarea w-full @error('location_notes') input-error @enderror"
                        placeholder="Enter meeting point, instructions, or other details for this location...">{{ old('location_notes', $classSession?->location_notes) }}</textarea>
                    @error('location_notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location-specific info display --}}
                <div id="location-info" class="hidden">
                    <div id="public-location-info" class="alert alert-soft alert-info hidden">
                        <span class="icon-[tabler--trees] size-5"></span>
                        <div>
                            <strong>Public Location</strong>
                            <p id="public-notes-display" class="text-sm"></p>
                        </div>
                    </div>
                    <div id="virtual-location-info" class="alert alert-soft alert-info hidden">
                        <span class="icon-[tabler--video] size-5"></span>
                        <div>
                            <strong>Virtual Location</strong>
                            <p id="virtual-platform-display" class="text-sm"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Capacity & Price --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Capacity & Price</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="capacity">Capacity</label>
                        <input type="number" id="capacity" name="capacity"
                            value="{{ old('capacity', $classSession?->capacity ?? 20) }}"
                            class="input w-full @error('capacity') input-error @enderror"
                            min="1" max="500" required>
                        @error('capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="price">Price Override ($)</label>
                        <input type="number" id="price" name="price"
                            value="{{ old('price', $classSession?->price) }}"
                            class="input w-full @error('price') input-error @enderror"
                            min="0" max="9999.99" step="0.01"
                            placeholder="Leave empty to use class plan price">
                        @error('price')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Internal Notes</h3>
            </div>
            <div class="card-body">
                <textarea id="notes" name="notes" rows="3"
                    class="textarea w-full @error('notes') input-error @enderror"
                    placeholder="Notes for staff only (not visible to clients)">{{ old('notes', $classSession?->notes) }}</textarea>
                @error('notes')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if($classSession)
        {{-- Status & Session Info (Edit mode only) --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Session Status</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="status">Status</label>
                        <select id="status" name="status" class="hidden @error('status') input-error @enderror"
                            data-select='{
                                "placeholder": "Select status...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $classSession->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text">Session Info</label>
                        <dl class="mt-2 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Created</dt>
                                <dd>{{ $classSession->created_at->format('M j, Y') }}</dd>
                            </div>
                            @if($classSession->isRecurring())
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Type</dt>
                                <dd><span class="badge badge-soft badge-info badge-sm">Recurring</span></dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $classSession ? 'Update Session' : 'Schedule Session' }}
            </button>
            <a href="{{ route('class-sessions.index') }}" class="btn btn-ghost">
                Cancel
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var classPlanSelect = document.getElementById('class_plan_id');
    var durationInput = document.getElementById('duration_minutes');
    var capacityInput = document.getElementById('capacity');
    var priceInput = document.getElementById('price');
    var dateInput = document.getElementById('session_date');
    var timeInput = document.getElementById('session_time');
    var timePreview = document.getElementById('time-preview');
    var previewStart = document.getElementById('preview-start');
    var previewEnd = document.getElementById('preview-end');
    var locationSelect = document.getElementById('location_id');
    var isRecurringCheckbox = document.getElementById('is_recurring');
    var recurrenceOptions = document.getElementById('recurrence-options');

    // Track if user has manually edited capacity/price
    var isEditMode = @json($classSession !== null);
    var userEditedCapacity = isEditMode; // In edit mode, treat existing values as user-edited
    var userEditedPrice = isEditMode;
    var lastSelectedPlanId = classPlanSelect.value;

    capacityInput.addEventListener('input', function() {
        userEditedCapacity = true;
    });

    priceInput.addEventListener('input', function() {
        userEditedPrice = true;
    });

    // Auto-fill from class plan when selection changes
    function applyClassPlanDefaults(forceUpdate) {
        var selectedOption = classPlanSelect.options[classPlanSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            // Check if class plan actually changed
            var planChanged = lastSelectedPlanId !== selectedOption.value;
            if (planChanged) {
                lastSelectedPlanId = selectedOption.value;
                // Reset flags when class plan changes (user explicitly changed it)
                if (forceUpdate) {
                    userEditedCapacity = false;
                    userEditedPrice = false;
                }
            }

            // Always update duration
            durationInput.value = selectedOption.dataset.duration || 60;

            // Update capacity if user hasn't manually edited it
            if (!userEditedCapacity) {
                capacityInput.value = selectedOption.dataset.capacity || 20;
            }

            // Update price placeholder always, value only if not edited
            var defaultPrice = selectedOption.dataset.price;
            priceInput.placeholder = defaultPrice && defaultPrice !== ''
                ? 'Default: $' + parseFloat(defaultPrice).toFixed(2)
                : 'Leave empty to use class plan price';

            if (!userEditedPrice) {
                priceInput.value = defaultPrice && defaultPrice !== '' ? defaultPrice : '';
            }

            updateTimePreview();
        }
    }

    classPlanSelect.addEventListener('change', function() {
        applyClassPlanDefaults(true);
    });

    // Also observe for HSSelect changes (advance-select component)
    var classPlanObserver = new MutationObserver(function() {
        // Only apply if the value actually changed (HSSelect updates)
        if (classPlanSelect.value !== lastSelectedPlanId) {
            applyClassPlanDefaults(true);
        }
    });
    classPlanObserver.observe(classPlanSelect, { attributes: true, childList: true, subtree: true });

    // Update time preview
    function updateTimePreview() {
        var date = dateInput.value;
        var time = timeInput.value;
        var duration = parseInt(durationInput.value) || 0;

        if (date && time && duration > 0) {
            var start = new Date(date + 'T' + time);
            var end = new Date(start.getTime() + duration * 60000);

            previewStart.textContent = start.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            previewEnd.textContent = end.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            timePreview.classList.remove('hidden');
        } else {
            timePreview.classList.add('hidden');
        }
    }

    durationInput.addEventListener('change', updateTimePreview);
    durationInput.addEventListener('input', updateTimePreview);
    timeInput.addEventListener('change', updateTimePreview);
    dateInput.addEventListener('change', updateTimePreview);
    updateTimePreview();

    // Set initial placeholder for price based on selected class plan
    var selectedPlanOption = classPlanSelect.options[classPlanSelect.selectedIndex];
    if (selectedPlanOption && selectedPlanOption.value) {
        var defaultPrice = selectedPlanOption.dataset.price;
        if (defaultPrice && defaultPrice !== '') {
            priceInput.placeholder = 'Default: $' + parseFloat(defaultPrice).toFixed(2);
        }
    }

    // Room and location type handling
    var currentRoomIds = @json($selectedRoomIds);
    var roomWrapper = document.getElementById('room-wrapper');
    var roomSelect = document.getElementById('room_id');
    var locationNotesWrapper = document.getElementById('location-notes-wrapper');
    var locationInfo = document.getElementById('location-info');
    var publicLocationInfo = document.getElementById('public-location-info');
    var virtualLocationInfo = document.getElementById('virtual-location-info');
    var publicNotesDisplay = document.getElementById('public-notes-display');
    var virtualPlatformDisplay = document.getElementById('virtual-platform-display');
    var classLocationTypeSelect = document.getElementById('class_location_type');
    var hsSelectInstance = null;

    // Filter locations based on selected class type
    function filterLocationsByType() {
        var selectedType = classLocationTypeSelect.value;
        var currentLocationValue = locationSelect.value;
        var hasVisibleSelected = false;

        Array.from(locationSelect.options).forEach(function(option) {
            if (!option.value) {
                // Always show the placeholder
                option.style.display = '';
                return;
            }

            var locationTypes = [];
            try {
                locationTypes = JSON.parse(option.dataset.types || '[]');
            } catch (e) {
                locationTypes = [];
            }

            // Also check the legacy single type
            var legacyType = option.dataset.type || '';
            if (legacyType && !locationTypes.includes(legacyType)) {
                locationTypes.push(legacyType);
            }

            // Show if no type filter selected, or if location has the selected type
            var shouldShow = !selectedType || locationTypes.includes(selectedType);
            option.style.display = shouldShow ? '' : 'none';
            option.disabled = !shouldShow;

            if (shouldShow && option.value === currentLocationValue) {
                hasVisibleSelected = true;
            }
        });

        // Reset selection if current selection is now hidden
        if (!hasVisibleSelected && currentLocationValue) {
            locationSelect.value = '';
        }

        // Update fields based on class type
        updateLocationFields();
    }

    classLocationTypeSelect.addEventListener('change', filterLocationsByType);

    // Also observe for HSSelect changes
    var classTypeObserver = new MutationObserver(filterLocationsByType);
    classTypeObserver.observe(classLocationTypeSelect, { attributes: true, childList: true, subtree: true });

    function updateLocationFields() {
        var selectedClassType = classLocationTypeSelect.value;
        var selectedOption = locationSelect.options[locationSelect.selectedIndex];
        var hasLocation = selectedOption && selectedOption.value;

        // Reset displays
        roomWrapper.classList.add('hidden');
        locationNotesWrapper.classList.add('hidden');
        locationInfo.classList.add('hidden');
        publicLocationInfo.classList.add('hidden');
        virtualLocationInfo.classList.add('hidden');

        if (!hasLocation) {
            return;
        }

        // If no class type is selected, try to get the location's type
        if (!selectedClassType && selectedOption) {
            var locationTypes = [];
            try {
                locationTypes = JSON.parse(selectedOption.dataset.types || '[]');
            } catch (e) {
                locationTypes = [];
            }
            // Also check legacy single type
            var legacyType = selectedOption.dataset.type || '';
            if (locationTypes.length > 0) {
                selectedClassType = locationTypes[0];
            } else if (legacyType) {
                selectedClassType = legacyType;
            }
        }

        // Show room multi-select only for In-Person Studio
        if (selectedClassType === 'in_person') {
            roomWrapper.classList.remove('hidden');

            // Populate rooms
            if (selectedOption.dataset.rooms) {
                var rooms = JSON.parse(selectedOption.dataset.rooms);

                // Clear and rebuild options
                roomSelect.innerHTML = '';
                rooms.forEach(function(room) {
                    var option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = room.name + ' (capacity: ' + room.capacity + ')';
                    option.dataset.capacity = room.capacity;
                    if (currentRoomIds.includes(room.id) || currentRoomIds.includes(String(room.id))) {
                        option.selected = true;
                    }
                    roomSelect.appendChild(option);
                });

                // Reinitialize HSSelect if needed
                if (window.HSSelect) {
                    var existingInstance = HSSelect.getInstance(roomSelect);
                    if (existingInstance) {
                        existingInstance.destroy();
                    }
                    setTimeout(function() {
                        HSSelect.autoInit();
                    }, 50);
                }
            }
        } else {
            // Show location notes for all other types
            locationNotesWrapper.classList.remove('hidden');

            // Also show location-specific info
            var locationType = selectedOption?.dataset?.type || '';
            if (locationType === 'public') {
                locationInfo.classList.remove('hidden');
                publicLocationInfo.classList.remove('hidden');
                publicNotesDisplay.textContent = selectedOption.dataset.publicNotes || 'No instructions provided.';
            } else if (locationType === 'virtual') {
                locationInfo.classList.remove('hidden');
                virtualLocationInfo.classList.remove('hidden');
                virtualPlatformDisplay.textContent = 'Platform: ' + (selectedOption.dataset.virtualPlatform || 'Not specified');
            }
        }
    }

    locationSelect.addEventListener('change', function() {
        currentRoomIds = [];
        updateLocationFields();
    });

    // Also observe for HSSelect changes on location
    var locationObserver = new MutationObserver(function() {
        updateLocationFields();
    });
    locationObserver.observe(locationSelect, { attributes: true, childList: true, subtree: true });

    // Initial location fields setup - delay to allow HSSelect to initialize
    function initializeLocationFields() {
        // Get the actual selected class type from the select (PHP pre-selected)
        var selectedClassTypeOption = classLocationTypeSelect.options[classLocationTypeSelect.selectedIndex];
        if (selectedClassTypeOption && selectedClassTypeOption.value) {
            classLocationTypeSelect.value = selectedClassTypeOption.value;
        }

        filterLocationsByType();
        updateLocationFields();
    }

    // Wait for HSSelect to be fully initialized
    setTimeout(function() {
        initializeLocationFields();

        // Re-run after HSSelect might have updated
        setTimeout(function() {
            // Force the class type select to reflect the actual selected value
            if (classLocationTypeSelect.value) {
                filterLocationsByType();
                updateLocationFields();
            }
        }, 200);
    }, 100);

    // Toggle recurrence options
    if (isRecurringCheckbox) {
        isRecurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurrenceOptions.classList.remove('hidden');
            } else {
                recurrenceOptions.classList.add('hidden');
            }
        });
    }

    // Initialize flatpickr for date inputs
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        minDate: isEditMode ? null : 'today',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false,
        onChange: function() {
            updateTimePreview();
        }
    });

    // Initialize flatpickr for time inputs
    flatpickr('.flatpickr-time', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: false,
        minuteIncrement: 15,
        altInput: true,
        altFormat: 'h:i K',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false,
        onChange: function() {
            updateTimePreview();
        }
    });

    // Backup instructors dynamic add/remove
    var backupContainer = document.getElementById('backup-instructors-container');
    var addBackupBtn = document.getElementById('add-backup-instructor');
    var primaryInstructorSelect = document.getElementById('primary_instructor_id');
    var backupRowIndex = backupContainer.querySelectorAll('.backup-instructor-row').length;

    // Instructor data for building new rows
    var instructorsData = @json($instructors->map(fn($i) => ['id' => $i->id, 'name' => $i->name]));

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Get all selected instructor IDs (primary + all backups)
    function getSelectedInstructorIds() {
        var selected = [];
        // Add primary instructor
        if (primaryInstructorSelect.value) {
            selected.push(primaryInstructorSelect.value);
        }
        // Add all backup instructors
        backupContainer.querySelectorAll('.backup-instructor-select').forEach(function(select) {
            if (select.value) {
                selected.push(select.value);
            }
        });
        return selected;
    }

    // Update all backup dropdowns to hide already-selected instructors
    function updateBackupDropdowns() {
        var selectedIds = getSelectedInstructorIds();

        backupContainer.querySelectorAll('.backup-instructor-select').forEach(function(select) {
            var currentValue = select.value;

            // Update each option's disabled state
            Array.from(select.options).forEach(function(option) {
                if (option.value === '') return; // Skip placeholder

                // Disable if selected elsewhere (but not in this select)
                var isSelectedElsewhere = selectedIds.includes(option.value) && option.value !== currentValue;
                option.disabled = isSelectedElsewhere;
                option.style.display = isSelectedElsewhere ? 'none' : '';
            });
        });
    }

    function buildInstructorOptions(excludeIds) {
        excludeIds = excludeIds || [];
        var options = '<option value="">Select backup instructor...</option>';
        instructorsData.forEach(function(instructor) {
            var isExcluded = excludeIds.includes(String(instructor.id));
            if (!isExcluded) {
                options += '<option value="' + instructor.id + '">' + escapeHtml(instructor.name) + '</option>';
            }
        });
        return options;
    }

    function createBackupRow() {
        var selectedIds = getSelectedInstructorIds();
        var row = document.createElement('div');
        row.className = 'backup-instructor-row flex items-center gap-2';
        row.dataset.index = backupRowIndex++;
        row.innerHTML = '<div class="flex-1">' +
            '<select name="backup_instructor_ids[]" class="select w-full backup-instructor-select">' +
            buildInstructorOptions(selectedIds) +
            '</select>' +
            '</div>' +
            '<button type="button" class="remove-backup-instructor btn btn-ghost btn-sm btn-circle text-error flex-shrink-0" title="Remove">' +
            '<span class="icon-[tabler--trash] size-4"></span>' +
            '</button>';
        return row;
    }

    function updateRemoveButtons() {
        var rows = backupContainer.querySelectorAll('.backup-instructor-row');
        rows.forEach(function(row, index) {
            var removeBtn = row.querySelector('.remove-backup-instructor');
            // Always show remove button, but if there's only one empty row, hide it
            if (rows.length === 1) {
                var select = row.querySelector('select');
                if (!select.value) {
                    removeBtn.classList.add('invisible');
                } else {
                    removeBtn.classList.remove('invisible');
                }
            } else {
                removeBtn.classList.remove('invisible');
            }
        });
    }

    addBackupBtn.addEventListener('click', function() {
        backupContainer.appendChild(createBackupRow());
        updateRemoveButtons();
    });

    backupContainer.addEventListener('click', function(e) {
        var removeBtn = e.target.closest('.remove-backup-instructor');
        if (removeBtn) {
            var row = removeBtn.closest('.backup-instructor-row');
            var rows = backupContainer.querySelectorAll('.backup-instructor-row');
            if (rows.length > 1) {
                row.remove();
            } else {
                // If it's the last row, just clear the selection
                row.querySelector('select').value = '';
            }
            updateRemoveButtons();
            updateBackupDropdowns();
        }
    });

    // Update dropdowns when any backup selection changes
    backupContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('backup-instructor-select')) {
            updateRemoveButtons();
            updateBackupDropdowns();
        }
    });

    // Update backup dropdowns when primary instructor changes
    // Listen on both native change and HSSelect wrapper for advance-select
    primaryInstructorSelect.addEventListener('change', function() {
        updateBackupDropdowns();
    });

    // Also observe for mutations in case advance-select updates the value differently
    var primaryObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'value' || mutation.type === 'childList') {
                updateBackupDropdowns();
            }
        });
    });
    primaryObserver.observe(primaryInstructorSelect, { attributes: true, childList: true, subtree: true });

    // Initial update
    updateRemoveButtons();
    updateBackupDropdowns();
});
</script>
@endpush
