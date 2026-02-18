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

<div class="space-y-6">
    {{-- Main Form --}}
    <div class="space-y-6">
        {{-- Card 1: Class Selection --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">1</span>
                    <h3 class="card-title">Class Selection</h3>
                </div>
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

        {{-- Card 2: Date & Time --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                    <h3 class="card-title">Date & Time</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left: Date/Time Fields --}}
                    <div class="space-y-4">
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

                        {{-- Recurring Class Toggle --}}
                        @if(!$classSession)
                        <div class="border-t border-base-200 pt-4">
                            <label class="flex items-center gap-3 cursor-pointer" for="is_recurring">
                                <input type="checkbox" id="is_recurring" name="is_recurring" value="1" class="checkbox checkbox-primary"
                                    {{ old('is_recurring') ? 'checked' : '' }}>
                                <div>
                                    <span class="label-text font-medium">Recurring Class</span>
                                    <p class="text-xs text-base-content/50">Create multiple sessions on selected days of the week</p>
                                </div>
                            </label>
                        </div>

                        {{-- Recurring Options (hidden by default) --}}
                        <div id="recurring-options" class="space-y-4 {{ old('is_recurring') ? '' : 'hidden' }}">
                            {{-- Days of Week Selection --}}
                            <div>
                                <label class="label-text mb-2 block">Days of Week</label>
                                <div class="flex flex-wrap gap-2" id="days-of-week-selector">
                                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $day)
                                    <label class="day-checkbox flex items-center justify-center w-14 h-10 rounded-lg border-2 border-base-300 cursor-pointer hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10 transition-all" for="day-{{ $index }}">
                                        <input type="checkbox" id="day-{{ $index }}" name="recurrence_days[]" value="{{ $index }}" class="hidden session-day-checkbox"
                                            {{ is_array(old('recurrence_days')) && in_array($index, old('recurrence_days')) ? 'checked' : '' }}>
                                        <span class="text-sm font-medium">{{ $day }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- End Date --}}
                            <div>
                                <label class="label-text" for="recurrence_end_date">End Date (optional)</label>
                                <input type="text" id="recurrence_end_date" name="recurrence_end_date"
                                    value="{{ old('recurrence_end_date') }}"
                                    class="input w-full flatpickr-date"
                                    placeholder="Select end date...">
                                <p class="text-xs text-base-content/50 mt-1">Leave empty to create sessions for up to 1 year</p>
                            </div>

                            {{-- Hidden field for recurrence_end_type --}}
                            <input type="hidden" name="recurrence_end_type" id="recurrence_end_type" value="never">
                        </div>
                        @endif
                    </div>

                    {{-- Right: Sessions Preview Panel --}}
                    <div>
                        {{-- Placeholder (shown when not recurring or no days selected) --}}
                        <div id="sessions-preview-placeholder" class="text-center py-12 border-2 border-dashed border-base-300 rounded-lg">
                            <span class="icon-[tabler--calendar-event] size-12 text-base-content/20 mx-auto mb-3"></span>
                            <p class="text-base-content/50">Enable recurring to see session preview</p>
                        </div>

                        {{-- Sessions Preview Panel --}}
                        <div id="sessions-preview-panel" class="hidden">
                            {{-- Header --}}
                            <div class="flex items-center gap-4 bg-base-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-primary text-primary-content">
                                    <span class="icon-[tabler--calendar-repeat] size-6"></span>
                                </div>
                                <div>
                                    <div class="font-semibold text-lg">Sessions to be Created</div>
                                    <div class="text-sm text-base-content/60" id="sessions-count">0 sessions</div>
                                </div>
                            </div>

                            {{-- Sessions List --}}
                            <div id="sessions-list" class="space-y-2 max-h-72 overflow-y-auto">
                                {{-- Sessions will be populated here --}}
                            </div>

                            {{-- Show More Toggle --}}
                            <div id="sessions-show-more" class="hidden mt-3 text-center">
                                <button type="button" id="toggle-sessions-btn" class="btn btn-ghost btn-sm text-primary">
                                    <span class="icon-[tabler--chevron-down] size-4"></span>
                                    Show all sessions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Instructors --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">3</span>
                    <h3 class="card-title">Instructors</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left: Instructor Selection --}}
                    <div class="space-y-4">
                        {{-- Primary Instructor --}}
                        <div>
                            <label class="label-text" for="primary_instructor_id">Primary Instructor <span class="text-error">*</span></label>
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
                            <p class="text-base-content/60 text-xs mt-2">Add backup instructors in order of priority.</p>
                            @error('backup_instructor_ids')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Right: Instructor Availability Panel --}}
                    <div>
                        {{-- Placeholder (shown when no instructor selected) --}}
                        <div id="instructor-avail-placeholder" class="text-center py-12 border-2 border-dashed border-base-300 rounded-lg">
                            <span class="icon-[tabler--user] size-12 text-base-content/20 mx-auto mb-3"></span>
                            <p class="text-base-content/50">Select an instructor and date to see their availability</p>
                        </div>

                        {{-- Loading State --}}
                        <div id="instructor-avail-loading" class="hidden text-center py-12">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="text-base-content/50 mt-3">Loading availability...</p>
                        </div>

                        {{-- Availability Panel (shows for all selected days) --}}
                        <div id="instructor-avail-panel" class="hidden space-y-4">
                            {{-- Instructor Info --}}
                            <div class="flex items-center gap-4 bg-base-200 rounded-lg p-4">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content size-12 rounded-full">
                                        <span id="instructor-avail-initials" class="text-lg font-bold">JS</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-lg" id="instructor-avail-name">Instructor Name</div>
                                    <div class="text-sm text-base-content/60" id="instructor-avail-subtitle">Availability</div>
                                </div>
                            </div>

                            {{-- Time Slot Display --}}
                            <div id="instructor-time-slot" class="hidden">
                                <div class="text-sm font-medium text-base-content/60 mb-2">Available Hours</div>
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <span class="icon-[tabler--clock] size-5 text-primary"></span>
                                    <span id="instructor-time-range" class="font-semibold text-lg">9:00 AM - 5:00 PM</span>
                                </div>
                            </div>

                            {{-- Available Time Slots (clickable) --}}
                            <div id="available-time-slots-section" class="hidden">
                                <div class="text-sm font-medium text-base-content/60 mb-2">Available Time Slots</div>
                                <div id="available-time-slots-loading" class="hidden py-4 text-center">
                                    <span class="loading loading-spinner loading-sm text-primary"></span>
                                    <span class="text-sm text-base-content/50 ml-2">Loading slots...</span>
                                </div>
                                <div id="available-time-slots-grid" class="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                                    {{-- Time slots will be populated here --}}
                                </div>
                                <div id="available-time-slots-empty" class="hidden py-4 text-center">
                                    <span class="icon-[tabler--calendar-off] size-8 text-base-content/20"></span>
                                    <p class="text-sm text-base-content/50 mt-2">No available slots for this date</p>
                                </div>
                            </div>

                            {{-- Real-time Scheduling Warning --}}
                            <div id="realtime-scheduling-warning" class="hidden">
                                <div class="alert alert-soft alert-warning">
                                    <span class="icon-[tabler--alert-triangle] size-5 shrink-0"></span>
                                    <div class="flex-1">
                                        <h4 class="font-semibold">Scheduling Warning</h4>
                                        <p id="realtime-warning-message" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Working Days with Selection Indicator --}}
                            <div>
                                <div class="text-sm font-medium text-base-content/60 mb-2">Working Days</div>
                                <div class="flex gap-2" id="instructor-avail-working-days">
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">S</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">M</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">T</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">W</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">T</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">F</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">S</span>
                                </div>
                                <p class="text-xs text-base-content/50 mt-2">
                                    <span class="inline-block w-3 h-3 rounded bg-success/20 border-2 border-success mr-1 align-middle"></span> Works this day
                                    <span class="inline-block w-3 h-3 rounded bg-primary ring-2 ring-primary ring-offset-1 ml-3 mr-1 align-middle"></span> Selected day
                                </p>
                            </div>

                            {{-- Days Availability Summary --}}
                            <div id="instructor-days-availability" class="space-y-2">
                                {{-- Will be populated dynamically --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Availability Warnings --}}
        @if(session('availability_warnings'))
        <div class="alert alert-soft alert-warning">
            <span class="icon-[tabler--alert-triangle] size-5 shrink-0"></span>
            <div class="flex-1">
                <h4 class="font-semibold">Scheduling Warning</h4>
                <ul class="list-disc list-inside mt-1 text-sm">
                    @foreach(session('availability_warnings') as $warning)
                    <li>{{ $warning['message'] }}</li>
                    @endforeach
                </ul>
                <div class="mt-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="override_availability_warnings" value="1" class="checkbox checkbox-sm checkbox-warning" required>
                        <span class="text-sm">I understand and want to proceed anyway</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        {{-- Card 4: Location --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">4</span>
                    <h3 class="card-title">Location</h3>
                </div>
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

        {{-- Card 5: Capacity & Price --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                    <h3 class="card-title">Capacity & Price</h3>
                </div>
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

        {{-- Card 6: Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">6</span>
                    <h3 class="card-title">Internal Notes</h3>
                </div>
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

    // Initialize flatpickr for time inputs (store instance for later updates)
    var timePickerInstance = flatpickr('.flatpickr-time', {
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
            // Re-check conflicts when time changes
            if (cachedInstructorData) {
                checkSchedulingConflict(cachedInstructorData);
            }
        }
    });

    // Function to update time picker based on instructor availability
    function updateTimePickerFromAvailability(data) {
        if (!data || !data.availability || !timePickerInstance) return;

        // Parse instructor availability times
        function parseTime12hToMinutes(timeStr) {
            var match = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
            if (!match) return null;
            var hours = parseInt(match[1]);
            var minutes = parseInt(match[2]);
            var isPM = match[3].toUpperCase() === 'PM';
            if (isPM && hours !== 12) hours += 12;
            if (!isPM && hours === 12) hours = 0;
            return hours * 60 + minutes;
        }

        function minutesToTime24(totalMinutes) {
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;
            return (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;
        }

        var availFromMinutes = parseTime12hToMinutes(data.availability.from);
        var availToMinutes = parseTime12hToMinutes(data.availability.to);

        if (availFromMinutes === null || availToMinutes === null) return;

        // Get current time value
        var currentTime = timeInput.value;
        var currentMinutes = 0;
        if (currentTime) {
            var parts = currentTime.split(':');
            currentMinutes = parseInt(parts[0]) * 60 + parseInt(parts[1]);
        }

        // Check if current time is outside availability
        if (currentMinutes < availFromMinutes || currentMinutes >= availToMinutes) {
            // Set time to instructor's start time
            var newTime = minutesToTime24(availFromMinutes);
            timePickerInstance.setDate(newTime, true);
            timeInput.value = newTime;
            updateTimePreview();
        }

        // Update flatpickr min/max times
        var minTimeStr = minutesToTime24(availFromMinutes);
        var maxTimeStr = minutesToTime24(availToMinutes - 15); // Subtract 15 min so session can fit

        timePickerInstance.set('minTime', minTimeStr);
        timePickerInstance.set('maxTime', maxTimeStr);
    }

    // Function to fetch and display available time slots
    function loadAvailableTimeSlots() {
        var instructorId = primaryInstructorSelect.value;
        var selectedDate = dateInput.value;
        var duration = parseInt(durationInput.value) || 60;

        // Hide slots section if no instructor or date
        if (!instructorId || !selectedDate) {
            availableSlotsSection.classList.add('hidden');
            return;
        }

        // Show loading
        availableSlotsSection.classList.remove('hidden');
        availableSlotsLoading.classList.remove('hidden');
        availableSlotsGrid.classList.add('hidden');
        availableSlotsEmpty.classList.add('hidden');

        // Fetch available slots
        fetch('/walk-in/available-slots?instructor_id=' + instructorId + '&date=' + selectedDate + '&duration=' + duration)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                availableSlotsLoading.classList.add('hidden');

                if (data.slots && data.slots.length > 0) {
                    availableSlotsGrid.classList.remove('hidden');
                    renderTimeSlots(data.slots);
                } else {
                    availableSlotsEmpty.classList.remove('hidden');
                }
            })
            .catch(function(err) {
                console.error('Error fetching slots:', err);
                availableSlotsLoading.classList.add('hidden');
                availableSlotsEmpty.classList.remove('hidden');
            });
    }

    // Render clickable time slots
    function renderTimeSlots(slots) {
        var currentTime = timeInput.value;
        var html = '';

        slots.forEach(function(slot) {
            var isSelected = slot.time === currentTime;
            var btnClass = isSelected
                ? 'btn btn-sm btn-primary'
                : 'btn btn-sm btn-outline btn-primary';

            html += '<button type="button" class="time-slot-btn ' + btnClass + '" data-time="' + slot.time + '">' +
                slot.display +
                '</button>';
        });

        availableSlotsGrid.innerHTML = html;

        // Add click handlers
        availableSlotsGrid.querySelectorAll('.time-slot-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var selectedTime = this.dataset.time;

                // Update time input
                timePickerInstance.setDate(selectedTime, true);
                timeInput.value = selectedTime;
                updateTimePreview();

                // Update button styles
                availableSlotsGrid.querySelectorAll('.time-slot-btn').forEach(function(b) {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline', 'btn-primary');
                });
                this.classList.remove('btn-outline');
                this.classList.add('btn-primary');

                // Re-check conflicts
                if (cachedInstructorData) {
                    checkSchedulingConflict(cachedInstructorData);
                }
            });
        });
    }

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
                loadInstructorAvailability();
            }
        });
    });
    primaryObserver.observe(primaryInstructorSelect, { attributes: true, childList: true, subtree: true });

    // Initial update
    updateRemoveButtons();
    updateBackupDropdowns();

    // =====================================================
    // Recurring Class Toggle & Days of Week Selection
    // =====================================================
    var isRecurringCheckbox = document.getElementById('is_recurring');
    var recurringOptions = document.getElementById('recurring-options');
    var dayCheckboxes = document.querySelectorAll('.session-day-checkbox');
    var sessionsPreviewPlaceholder = document.getElementById('sessions-preview-placeholder');
    var sessionsPreviewPanel = document.getElementById('sessions-preview-panel');
    var sessionsList = document.getElementById('sessions-list');
    var sessionsCount = document.getElementById('sessions-count');
    var sessionsShowMore = document.getElementById('sessions-show-more');
    var toggleSessionsBtn = document.getElementById('toggle-sessions-btn');
    var endDateInput = document.getElementById('recurrence_end_date');
    var recurrenceEndTypeInput = document.getElementById('recurrence_end_type');
    var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var showAllSessions = false;

    // Toggle recurring options visibility
    if (isRecurringCheckbox) {
        isRecurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurringOptions.classList.remove('hidden');
                // Uncheck all day checkboxes when turning off recurring
            } else {
                recurringOptions.classList.add('hidden');
                dayCheckboxes.forEach(function(cb) {
                    cb.checked = false;
                });
            }
            updateSessionsPreview();
            loadInstructorAvailability();
        });
    }

    // Toggle show all sessions
    if (toggleSessionsBtn) {
        toggleSessionsBtn.addEventListener('click', function() {
            showAllSessions = !showAllSessions;
            updateSessionsPreview();
        });
    }

    function getSelectedDays() {
        var selected = [];
        // Only count selected days if recurring is checked
        if (isRecurringCheckbox && !isRecurringCheckbox.checked) {
            return selected;
        }
        dayCheckboxes.forEach(function(cb) {
            if (cb.checked) {
                selected.push(parseInt(cb.value));
            }
        });
        return selected;
    }

    function calculateSessionDates() {
        var startDateStr = dateInput.value;

        if (!startDateStr) {
            return [];
        }

        var startDate = new Date(startDateStr + 'T00:00:00');
        var isRecurring = isRecurringCheckbox && isRecurringCheckbox.checked;

        // If not recurring, return just the single session date
        if (!isRecurring) {
            return [startDate];
        }

        var selectedDays = getSelectedDays();

        // If recurring but no days selected, return empty
        if (selectedDays.length === 0) {
            return [];
        }

        var endDate;

        // Get end date (default to 1 year from start, max 52 occurrences)
        if (endDateInput && endDateInput.value) {
            endDate = new Date(endDateInput.value + 'T23:59:59');
        } else {
            endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + 1); // 1 year
        }

        var sessions = [];
        var currentDate = new Date(startDate);

        // Generate all session dates (max 52 to match server limit)
        while (currentDate <= endDate && sessions.length < 52) {
            var dayOfWeek = currentDate.getDay();
            if (selectedDays.includes(dayOfWeek)) {
                sessions.push(new Date(currentDate));
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        return sessions;
    }

    function formatSessionDate(date) {
        return dayNamesShort[date.getDay()] + ', ' + monthNames[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
    }

    function updateSessionsPreview() {
        var sessions = calculateSessionDates();
        var hasSessions = sessions.length > 0;
        var isRecurring = isRecurringCheckbox && isRecurringCheckbox.checked;

        // Show/hide placeholder and panel based on state
        if (hasSessions && sessionsPreviewPanel) {
            sessionsPreviewPlaceholder.classList.add('hidden');
            sessionsPreviewPanel.classList.remove('hidden');

            // Update count and header text based on single vs recurring
            if (isRecurring) {
                sessionsCount.textContent = sessions.length + ' session' + (sessions.length !== 1 ? 's' : '');
            } else {
                sessionsCount.textContent = '1 session';
            }

            // Build sessions list
            var displayLimit = showAllSessions ? sessions.length : 8;
            var sessionsHtml = '';

            sessions.slice(0, displayLimit).forEach(function(date, index) {
                var isFirst = index === 0;
                var isLast = index === sessions.length - 1 && sessions.length > 1;
                var iconClass = isRecurring ? (isFirst ? 'text-success' : (isLast ? 'text-warning' : 'text-primary')) : 'text-primary';
                var label = isRecurring ? (isFirst ? 'First' : (isLast ? 'Last' : '')) : '';

                sessionsHtml += '<div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-base-200/50">' +
                    '<span class="icon-[tabler--calendar-event] size-5 ' + iconClass + '"></span>' +
                    '<span class="flex-1 text-sm">' + formatSessionDate(date) + '</span>' +
                    (label ? '<span class="badge badge-sm badge-soft ' + (isFirst ? 'badge-success' : 'badge-warning') + '">' + label + '</span>' : '') +
                    '</div>';
            });

            sessionsList.innerHTML = sessionsHtml;

            // Show/hide "show more" button
            if (sessions.length > 8) {
                sessionsShowMore.classList.remove('hidden');
                toggleSessionsBtn.innerHTML = showAllSessions
                    ? '<span class="icon-[tabler--chevron-up] size-4"></span> Show less'
                    : '<span class="icon-[tabler--chevron-down] size-4"></span> Show all ' + sessions.length + ' sessions';
            } else {
                sessionsShowMore.classList.add('hidden');
            }

        } else if (sessionsPreviewPanel) {
            sessionsPreviewPanel.classList.add('hidden');
            // Show placeholder when recurring is checked but no days selected yet
            if (isRecurring && sessionsPreviewPlaceholder) {
                sessionsPreviewPlaceholder.classList.remove('hidden');
                sessionsPreviewPlaceholder.innerHTML = '<span class="icon-[tabler--calendar-event] size-12 text-base-content/20 mx-auto mb-3"></span>' +
                    '<p class="text-base-content/50">Select days of the week to see session preview</p>';
            } else if (sessionsPreviewPlaceholder) {
                // No date selected
                sessionsPreviewPlaceholder.classList.remove('hidden');
                sessionsPreviewPlaceholder.innerHTML = '<span class="icon-[tabler--calendar-event] size-12 text-base-content/20 mx-auto mb-3"></span>' +
                    '<p class="text-base-content/50">Select a date to see session preview</p>';
            }
        }

        // Reload availability when sessions change
        loadInstructorAvailability();
    }

    dayCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateSessionsPreview);
    });

    // Listen for date changes
    dateInput.addEventListener('change', updateSessionsPreview);
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            // Update recurrence_end_type based on whether end date is provided
            if (recurrenceEndTypeInput) {
                recurrenceEndTypeInput.value = this.value ? 'on' : 'never';
            }
            updateSessionsPreview();
        });
    }

    // =====================================================
    // Instructor Availability Panel (Multi-Day)
    // =====================================================
    var availPlaceholder = document.getElementById('instructor-avail-placeholder');
    var availLoading = document.getElementById('instructor-avail-loading');
    var availPanel = document.getElementById('instructor-avail-panel');
    var availInitials = document.getElementById('instructor-avail-initials');
    var availName = document.getElementById('instructor-avail-name');
    var availSubtitle = document.getElementById('instructor-avail-subtitle');
    var availWorkingDays = document.getElementById('instructor-avail-working-days');
    var availDaysContainer = document.getElementById('instructor-days-availability');
    var instructorTimeSlot = document.getElementById('instructor-time-slot');
    var instructorTimeRange = document.getElementById('instructor-time-range');
    var realtimeWarning = document.getElementById('realtime-scheduling-warning');
    var realtimeWarningMessage = document.getElementById('realtime-warning-message');
    var availableSlotsSection = document.getElementById('available-time-slots-section');
    var availableSlotsLoading = document.getElementById('available-time-slots-loading');
    var availableSlotsGrid = document.getElementById('available-time-slots-grid');
    var availableSlotsEmpty = document.getElementById('available-time-slots-empty');

    var cachedInstructorData = null;

    function loadInstructorAvailability() {
        var instructorId = primaryInstructorSelect.value;
        var startDate = dateInput.value;
        var selectedDays = getSelectedDays();

        // Reset to placeholder if no instructor or date
        if (!instructorId || !startDate) {
            availPlaceholder.classList.remove('hidden');
            availLoading.classList.add('hidden');
            availPanel.classList.add('hidden');
            return;
        }

        // Show loading
        availPlaceholder.classList.add('hidden');
        availLoading.classList.remove('hidden');
        availPanel.classList.add('hidden');

        // Fetch availability for the start date first
        fetch('/walk-in/instructor-availability?instructor_id=' + instructorId + '&date=' + startDate)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                cachedInstructorData = data;
                availLoading.classList.add('hidden');
                availPanel.classList.remove('hidden');
                displayInstructorAvailability(data, selectedDays);
            })
            .catch(function(err) {
                console.error('Error fetching availability:', err);
                availLoading.classList.add('hidden');
                availPlaceholder.classList.remove('hidden');
            });
    }

    function displayInstructorAvailability(data, selectedDays) {
        // Instructor info
        availInitials.textContent = data.instructor.initials;
        availName.textContent = data.instructor.name;

        // Determine which days to show
        var daysToShow = selectedDays.length > 0 ? selectedDays : [data.day_of_week];
        var subtitle = selectedDays.length > 0
            ? 'Availability for ' + selectedDays.length + ' selected day' + (selectedDays.length > 1 ? 's' : '')
            : data.formatted_date;
        availSubtitle.textContent = subtitle;

        // Show time slot if available
        if (data.availability && data.availability.from && data.availability.to) {
            instructorTimeSlot.classList.remove('hidden');
            instructorTimeRange.textContent = data.availability.from + ' - ' + data.availability.to;

            // Update time picker to match instructor's availability
            updateTimePickerFromAvailability(data);

            // Load available time slots
            loadAvailableTimeSlots();
        } else if (data.works_today) {
            instructorTimeSlot.classList.remove('hidden');
            instructorTimeRange.textContent = 'All day';
            // Reset time picker constraints for "all day" availability
            if (timePickerInstance) {
                timePickerInstance.set('minTime', '06:00');
                timePickerInstance.set('maxTime', '22:00');
            }
            // Load available time slots
            loadAvailableTimeSlots();
        } else {
            instructorTimeSlot.classList.add('hidden');
            availableSlotsSection.classList.add('hidden');
        }

        // Check for scheduling conflicts (real-time warning)
        checkSchedulingConflict(data);

        // Working days with selected days highlighted
        var dayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        var workingDaysHtml = '';
        data.working_days.forEach(function(works, index) {
            var isSelected = daysToShow.includes(index);
            var baseClass = 'size-9 rounded text-sm font-medium flex items-center justify-center transition-all';
            var colorClass;

            if (isSelected) {
                // Selected day
                if (works) {
                    colorClass = 'bg-primary text-primary-content ring-2 ring-primary ring-offset-1';
                } else {
                    colorClass = 'bg-error text-error-content ring-2 ring-error ring-offset-1';
                }
            } else {
                // Not selected
                colorClass = works ? 'bg-success/20 text-success' : 'bg-base-200 text-base-content/40';
            }

            workingDaysHtml += '<span class="' + baseClass + ' ' + colorClass + '">' + dayLetters[index] + '</span>';
        });
        availWorkingDays.innerHTML = workingDaysHtml;

        // Build availability summary for each selected day
        var availHtml = '';
        daysToShow.forEach(function(dayIndex) {
            var dayName = dayNames[dayIndex];
            var works = data.working_days[dayIndex];

            if (works) {
                var hoursText = data.availability
                    ? data.availability.from + ' - ' + data.availability.to
                    : 'All day';

                availHtml += '<div class="flex items-center gap-3 p-3 bg-success/10 border border-success/20 rounded-lg">' +
                    '<span class="icon-[tabler--check] size-5 text-success"></span>' +
                    '<div class="flex-1">' +
                    '<div class="font-medium text-success">' + dayName + '</div>' +
                    '<div class="text-sm text-base-content/60">Available ' + hoursText + '</div>' +
                    '</div>' +
                    '</div>';
            } else {
                availHtml += '<div class="flex items-center gap-3 p-3 bg-error/10 border border-error/20 rounded-lg">' +
                    '<span class="icon-[tabler--x] size-5 text-error"></span>' +
                    '<div class="flex-1">' +
                    '<div class="font-medium text-error">' + dayName + '</div>' +
                    '<div class="text-sm text-base-content/60">Does not work this day</div>' +
                    '</div>' +
                    '</div>';
            }
        });

        // If there are existing sessions on the start date, show them
        if (data.existing_sessions.length > 0 && selectedDays.length === 0) {
            availHtml += '<div class="mt-3">' +
                '<div class="text-sm font-medium text-base-content/60 mb-2">Existing appointments on ' + data.formatted_date + '</div>';
            data.existing_sessions.forEach(function(session) {
                availHtml += '<div class="flex items-center gap-2 text-sm bg-base-200 rounded px-3 py-2 mb-1">' +
                    '<span class="icon-[tabler--calendar-event] size-4 text-base-content/50"></span>' +
                    '<span class="font-medium">' + session.time + '</span>' +
                    '<span class="text-base-content/60 truncate">- ' + session.title + '</span>' +
                    '</div>';
            });
            availHtml += '</div>';
        }

        availDaysContainer.innerHTML = availHtml;
    }

    // Check if session time conflicts with instructor availability
    function checkSchedulingConflict(data) {
        var sessionTime = timeInput.value;
        var duration = parseInt(durationInput.value) || 0;

        // Hide warning by default
        realtimeWarning.classList.add('hidden');

        if (!sessionTime || !duration || !data.availability) {
            return;
        }

        // Parse session start and end times
        var sessionStartParts = sessionTime.split(':');
        var sessionStartMinutes = parseInt(sessionStartParts[0]) * 60 + parseInt(sessionStartParts[1]);
        var sessionEndMinutes = sessionStartMinutes + duration;

        // Parse instructor availability (convert from 12h to 24h format)
        function parseTime12h(timeStr) {
            var match = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
            if (!match) return null;
            var hours = parseInt(match[1]);
            var minutes = parseInt(match[2]);
            var isPM = match[3].toUpperCase() === 'PM';
            if (isPM && hours !== 12) hours += 12;
            if (!isPM && hours === 12) hours = 0;
            return hours * 60 + minutes;
        }

        var availFromMinutes = parseTime12h(data.availability.from);
        var availToMinutes = parseTime12h(data.availability.to);

        if (availFromMinutes === null || availToMinutes === null) {
            return;
        }

        // Check if session time is outside availability
        var warnings = [];

        if (sessionStartMinutes < availFromMinutes || sessionEndMinutes > availToMinutes) {
            // Format session time for display
            var sessionStartFormatted = formatTimeForDisplay(sessionStartMinutes);
            var sessionEndFormatted = formatTimeForDisplay(sessionEndMinutes);

            warnings.push(data.instructor.name + "'s availability is " + data.availability.from + " - " + data.availability.to + ". This session is " + sessionStartFormatted + " - " + sessionEndFormatted + ".");
        }

        // Check if instructor doesn't work on selected day
        var selectedDays = getSelectedDays();
        var daysToCheck = selectedDays.length > 0 ? selectedDays : [data.day_of_week];

        daysToCheck.forEach(function(dayIndex) {
            if (!data.working_days[dayIndex]) {
                warnings.push(data.instructor.name + " does not work on " + dayNames[dayIndex] + "s.");
            }
        });

        if (warnings.length > 0) {
            realtimeWarning.classList.remove('hidden');
            realtimeWarningMessage.innerHTML = warnings.map(function(w) { return '<span class="block">' + w + '</span>'; }).join('');
        }
    }

    function formatTimeForDisplay(totalMinutes) {
        var hours = Math.floor(totalMinutes / 60);
        var minutes = totalMinutes % 60;
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        if (hours === 0) hours = 12;
        return hours + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + ampm;
    }

    // Re-check conflicts when time or duration changes
    timeInput.addEventListener('change', function() {
        if (cachedInstructorData) {
            checkSchedulingConflict(cachedInstructorData);
        }
        // Update selected slot highlight
        var currentTime = timeInput.value;
        availableSlotsGrid.querySelectorAll('.time-slot-btn').forEach(function(btn) {
            if (btn.dataset.time === currentTime) {
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline', 'btn-primary');
            }
        });
    });
    durationInput.addEventListener('change', function() {
        // Reload available slots when duration changes (different duration = different slot availability)
        loadAvailableTimeSlots();
        if (cachedInstructorData) {
            checkSchedulingConflict(cachedInstructorData);
        }
    });

    // Listen for date changes
    dateInput.addEventListener('change', loadInstructorAvailability);

    // Listen for primary instructor changes
    primaryInstructorSelect.addEventListener('change', loadInstructorAvailability);

    // Initial load
    setTimeout(function() {
        updateSessionsPreview();
        loadInstructorAvailability();
    }, 300);
});
</script>
@endpush
