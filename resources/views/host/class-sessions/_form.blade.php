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
                    <label class="label-text" for="class_plan_id">Class Plan <span class="text-error">*</span></label>
                    @php
                        $classPlanOptions = [];
                        foreach($classPlans as $plan) {
                            $classPlanOptions[$plan->id] = $plan->name . ' (' . $plan->formatted_duration . ')';
                        }
                    @endphp
                    <x-studio-select
                        name="class_plan_id"
                        :options="$classPlanOptions"
                        :selected="old('class_plan_id', $selectedClassPlanId)"
                        placeholder="Select a class..."
                        :required="true"
                        id="class_plan_id"
                    />
                    {{-- Hidden data attributes for JS --}}
                    @foreach($classPlans as $plan)
                        <input type="hidden" class="class-plan-data" data-plan-id="{{ $plan->id }}"
                            data-duration="{{ $plan->default_duration_minutes }}"
                            data-capacity="{{ $plan->default_capacity }}"
                            data-price="{{ $plan->default_price }}"
                            data-color="{{ $plan->color }}">
                    @endforeach
                    @error('class_plan_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="title">Custom Name <span class="text-error">*</span></label>
                    <input type="text" id="title" name="title"
                        value="{{ old('title', $classSession?->title) }}"
                        class="input w-full @error('title') input-error @enderror"
                        placeholder="Enter a unique session name" required>
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
                                    placeholder="Select date...">
                                @error('session_date')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="session_time">Start Time</label>
                                <input type="text" id="session_time" name="session_time"
                                    value="{{ old('session_time', $classSession?->start_time?->format('H:i') ?? '09:00') }}"
                                    class="input w-full flatpickr-time @error('session_time') input-error @enderror"
                                    placeholder="Select time...">
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
                            @php
                                $instructorOptions = [];
                                foreach($instructors as $inst) {
                                    $instructorOptions[$inst->id] = $inst->name;
                                }
                            @endphp
                            <x-studio-select
                                name="primary_instructor_id"
                                :options="$instructorOptions"
                                :selected="old('primary_instructor_id', $classSession?->primary_instructor_id)"
                                placeholder="Select an instructor..."
                                :required="true"
                                id="primary_instructor_id"
                            />
                            @error('primary_instructor_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Backup Instructors --}}
                        <div>
                            <label class="label-text mb-1">Backup Instructors (optional)</label>
                            @php
                                $backupInstructorIds = old('backup_instructor_ids', $classSession?->backupInstructors?->pluck('id')->toArray() ?? []);
                            @endphp
                            <select id="backup_instructor_ids" name="backup_instructor_ids[]" multiple class="hidden"
                                data-select='{!! json_encode([
                                    "hasSearch" => true,
                                    "searchPlaceholder" => "Search instructors...",
                                    "placeholder" => "Select backup instructors...",
                                    "toggleTag" => "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses" => "advance-select-toggle",
                                    "dropdownClasses" => "advance-select-menu max-h-72 overflow-y-auto",
                                    "optionClasses" => "advance-select-option selected:select-active",
                                    "optionTemplate" => "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup" => "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>",
                                ], JSON_UNESCAPED_SLASHES) !!}'>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}"
                                        {{ in_array($instructor->id, $backupInstructorIds) ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-base-content/60 text-xs mt-2">Select one or more backup instructors in order of priority.</p>
                            @error('backup_instructor_ids')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Right: Availability Panel --}}
                    <div>
                        <div id="instructor-avail-placeholder" class="text-center py-8 border-2 border-dashed border-base-300 rounded-lg">
                            <span class="icon-[tabler--user] size-10 text-base-content/20 mx-auto mb-2"></span>
                            <p class="text-base-content/50 text-sm">Select an instructor and date to see availability</p>
                        </div>

                        <div id="instructor-avail-loading" class="hidden text-center py-8">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="text-base-content/50 mt-3 text-sm">Loading availability...</p>
                        </div>

                        <div id="instructor-avail-panel" class="hidden space-y-3">
                            <div class="flex items-center gap-3 bg-base-200 rounded-lg p-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content size-10 rounded-full">
                                        <span id="instructor-avail-initials" class="text-sm font-bold">?</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold" id="instructor-avail-name">Instructor</div>
                                    <div class="text-sm text-base-content/60" id="instructor-avail-subtitle">Availability</div>
                                </div>
                            </div>

                            <div id="instructor-time-slot" class="hidden">
                                <div class="text-sm font-medium text-base-content/60 mb-1">Available Hours</div>
                                <div class="flex items-center gap-2 p-2 bg-base-200 rounded-lg">
                                    <span class="icon-[tabler--clock] size-4 text-primary"></span>
                                    <span id="instructor-time-range" class="font-medium">9:00 AM - 5:00 PM</span>
                                </div>
                            </div>

                            <div id="available-time-slots-section" class="hidden">
                                <div class="text-sm font-medium text-base-content/60 mb-1">Available Slots</div>
                                <div id="available-time-slots-loading" class="hidden py-3 text-center">
                                    <span class="loading loading-spinner loading-sm text-primary"></span>
                                </div>
                                <div id="available-time-slots-grid" class="grid grid-cols-3 gap-1.5 max-h-36 overflow-y-auto"></div>
                                <div id="available-time-slots-empty" class="hidden py-3 text-center">
                                    <p class="text-sm text-base-content/50">No available slots</p>
                                </div>
                            </div>

                            <input type="hidden" id="override_availability_warnings" name="override_availability_warnings" value="1">

                            <div>
                                <div class="text-sm font-medium text-base-content/60 mb-1">Working Days</div>
                                <div class="flex gap-1.5" id="instructor-avail-working-days">
                                    @foreach(['S','M','T','W','T','F','S'] as $d)
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-base-200">{{ $d }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div id="instructor-days-availability" class="space-y-2"></div>
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
                    <h3 class="card-title">Location & Capacity</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Class/Location Type Filter (multiselect) --}}
                    <div>
                        <label class="label-text" for="class_location_type">Class Type</label>
                        <select id="class_location_type" multiple class="hidden"
                            data-select='{
                                "placeholder": "All Types",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            @foreach(\App\Models\Location::getLocationTypeOptions() as $type => $label)
                            <option value="{{ $type }}" {{ $selectedClassType === $type ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location --}}
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

                    {{-- Room(s) --}}
                    <div id="room-wrapper" class="hidden flex flex-col">
                        <label class="label-text order-first mb-0">Room</label>
                        <select id="room_id" name="room_ids[]" class="hidden @error('room_ids') input-error @enderror"
                            data-select='{
                                "placeholder": "Select room...",
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
                    </div>

                    {{-- Capacity --}}
                    <div>
                        <label class="label-text" for="capacity">Capacity <span class="text-error">*</span></label>
                        <input type="number" id="capacity" name="capacity"
                            value="{{ old('capacity', $classSession?->capacity ?? 20) }}"
                            class="input w-full @error('capacity') input-error @enderror"
                            min="1" max="500" required>
                        @error('capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <input type="hidden" id="price" name="price" value="{{ old('price', $classSession?->price) }}">

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

        {{-- Card 5: Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
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

        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">6</span>
                    <h3 class="card-title">Status</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Publish Session</span>
                        <p class="text-xs text-base-content/60">Published sessions are visible to clients and open for booking.</p>
                    </div>
                    <input type="hidden" name="status" id="session_status_value" value="{{ in_array(old('status', $classSession?->status ?? 'draft'), ['published']) ? 'published' : 'draft' }}">
                    <label class="switch switch-primary">
                        <input type="checkbox" id="session_status_toggle"
                            {{ in_array(old('status', $classSession?->status ?? 'draft'), ['published']) ? 'checked' : '' }}
                            onchange="document.getElementById('session_status_value').value = this.checked ? 'published' : 'draft'" />
                        <span class="switch-indicator"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Conflict Override (hidden by default, shown when conflict detected) --}}
        <div id="conflict-override-card" class="card bg-base-100 hidden">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--alert-triangle] size-5 text-warning mt-0.5"></span>
                        <div>
                            <span class="font-medium">Scheduling Conflict Detected</span>
                            <p class="text-xs text-base-content/60" id="conflict-message">The selected instructor has a conflict on the chosen date/time.</p>
                        </div>
                    </div>
                    <label class="switch switch-warning">
                        <input type="hidden" name="override_conflicts" value="0">
                        <input type="checkbox" id="conflict_override_toggle" name="override_conflicts" value="1"
                            onchange="document.getElementById('submit-btn').disabled = !this.checked && document.getElementById('conflict-override-card').classList.contains('hidden') === false" />
                        <span class="switch-indicator"></span>
                    </label>
                </div>
                <p class="text-xs text-warning mt-2 ml-8">Toggle on to schedule anyway despite the conflict.</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-4">
            <button type="submit" id="submit-btn" class="btn btn-primary">
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

    // Get class plan data from hidden inputs
    function getClassPlanData(planId) {
        var el = document.querySelector('.class-plan-data[data-plan-id="' + planId + '"]');
        if (!el) return null;
        return {
            duration: el.dataset.duration,
            capacity: el.dataset.capacity,
            price: el.dataset.price,
            color: el.dataset.color,
        };
    }

    // Auto-fill from class plan when selection changes
    function applyClassPlanDefaults(forceUpdate) {
        var planId = classPlanSelect.value;
        if (planId) {
            var data = getClassPlanData(planId);
            if (!data) return;

            var planChanged = lastSelectedPlanId !== planId;
            if (planChanged) {
                lastSelectedPlanId = planId;
                if (forceUpdate) {
                    userEditedCapacity = false;
                    userEditedPrice = false;
                }
            }

            durationInput.value = data.duration || 60;

            if (!userEditedCapacity) {
                capacityInput.value = data.capacity || 20;
            }

            if (!userEditedPrice) {
                priceInput.value = data.price && data.price !== '' ? data.price : '';
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

    // Set initial price from selected class plan
    if (classPlanSelect.value) {
        var initData = getClassPlanData(classPlanSelect.value);
        if (initData && initData.price && !priceInput.value) {
            priceInput.value = initData.price;
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

    // Get selected class types (multiselect returns array)
    function getSelectedClassTypes() {
        var selected = [];
        Array.from(classLocationTypeSelect.selectedOptions || classLocationTypeSelect.options).forEach(function(opt) {
            if (opt.selected && opt.value) selected.push(opt.value);
        });
        return selected;
    }

    // Filter locations based on selected class types
    function filterLocationsByType() {
        var selectedTypes = getSelectedClassTypes();
        var currentLocationValue = locationSelect.value;
        var hasVisibleSelected = false;

        Array.from(locationSelect.options).forEach(function(option) {
            if (!option.value) {
                option.style.display = '';
                return;
            }

            var locationTypes = [];
            try {
                locationTypes = JSON.parse(option.dataset.types || '[]');
            } catch (e) {
                locationTypes = [];
            }

            var legacyType = option.dataset.type || '';
            if (legacyType && !locationTypes.includes(legacyType)) {
                locationTypes.push(legacyType);
            }

            // Show if no type filter selected, or if location has ANY of the selected types
            var shouldShow = selectedTypes.length === 0 || selectedTypes.some(function(t) { return locationTypes.includes(t); });
            option.style.display = shouldShow ? '' : 'none';
            option.disabled = !shouldShow;

            if (shouldShow && option.value === currentLocationValue) {
                hasVisibleSelected = true;
            }
        });

        if (!hasVisibleSelected && currentLocationValue) {
            locationSelect.value = '';
        }

        updateLocationFields();
    }

    classLocationTypeSelect.addEventListener('change', filterLocationsByType);

    // Also observe for HSSelect changes
    var classTypeObserver = new MutationObserver(filterLocationsByType);
    classTypeObserver.observe(classLocationTypeSelect, { attributes: true, childList: true, subtree: true });

    function updateLocationFields() {
        var selectedTypes = getSelectedClassTypes();
        var selectedClassType = selectedTypes.length > 0 ? selectedTypes[0] : '';
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
        filterLocationsByType();
        updateLocationFields();
    }

    // Wait for HSSelect to be fully initialized
    setTimeout(function() {
        initializeLocationFields();

        // Re-run after HSSelect might have updated
        setTimeout(function() {
            filterLocationsByType();
            updateLocationFields();
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

    // Instructor availability references
    var primaryInstructorSelect = document.getElementById('primary_instructor_id');

    // Primary instructor change listener for availability
    if (primaryInstructorSelect) {
        primaryInstructorSelect.addEventListener('change', function() {
            loadInstructorAvailability();
        });

        var primaryObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'value' || mutation.type === 'childList') {
                    loadInstructorAvailability();
                }
            });
        });
        primaryObserver.observe(primaryInstructorSelect, { attributes: true, childList: true, subtree: true });
    }

    // Filter backup instructor dropdown to exclude primary instructor
    var backupSelect = document.getElementById('backup_instructor_ids');

    function filterBackupInstructors() {
        if (!backupSelect || !primaryInstructorSelect) return;
        var primaryId = primaryInstructorSelect.value;

        Array.from(backupSelect.options).forEach(function(option) {
            if (!option.value) return;
            if (option.value === primaryId) {
                option.disabled = true;
                option.style.display = 'none';
                // Deselect if was selected
                if (option.selected) option.selected = false;
            } else {
                option.disabled = false;
                option.style.display = '';
            }
        });

        // Also update the rendered FlyonUI dropdown items
        var backupWrapper = backupSelect.closest('div');
        if (backupWrapper) {
            setTimeout(function() {
                var dropdownItems = backupWrapper.querySelectorAll('.advance-select-option');
                dropdownItems.forEach(function(item) {
                    var val = item.getAttribute('data-value');
                    if (val === primaryId) {
                        item.style.display = 'none';
                    } else {
                        item.style.display = '';
                    }
                });
            }, 100);
        }
    }

    if (primaryInstructorSelect) {
        primaryInstructorSelect.addEventListener('change', filterBackupInstructors);
        // Also run on mutation for advance-select
        var backupFilterObserver = new MutationObserver(filterBackupInstructors);
        backupFilterObserver.observe(primaryInstructorSelect, { attributes: true, childList: true, subtree: true });
    }

    // Initial filter
    setTimeout(filterBackupInstructors, 300);

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
    // Conflict warning UI removed — conflicts are auto-allowed and shown in listing
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

        // Check if availability is configured
        if (!data.has_configured_availability) {
            availSubtitle.textContent = 'Availability not configured';
            instructorTimeSlot.classList.remove('hidden');
            instructorTimeRange.innerHTML = '<span class="text-warning text-sm">Not configured</span>';
            availableSlotsSection.classList.add('hidden');

            var dayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
            var unconfiguredHtml = '';
            dayLetters.forEach(function(letter) {
                unconfiguredHtml += '<span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200 text-base-content/40">' + letter + '</span>';
            });
            availWorkingDays.innerHTML = unconfiguredHtml;
            availDaysContainer.innerHTML = '<div class="p-3 bg-warning/10 border border-warning/20 rounded-lg">' +
                '<div class="flex items-start gap-2">' +
                '<span class="icon-[tabler--alert-triangle] size-4 text-warning mt-0.5"></span>' +
                '<p class="text-sm text-base-content/70">No working days or hours configured for this instructor. <a href="/settings/team/users" class="link link-primary text-sm">Set up availability</a></p>' +
                '</div></div>';
            return;
        }

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

        // Build compact availability summary for each selected day
        var availHtml = '<div class="space-y-1">';
        daysToShow.forEach(function(dayIndex) {
            var dayName = dayNamesShort[dayIndex];
            var works = data.working_days[dayIndex];

            if (works) {
                var hoursText = data.availability
                    ? data.availability.from + ' - ' + data.availability.to
                    : 'All day';

                availHtml += '<div class="flex items-center justify-between py-1.5 px-2 rounded bg-success/5">' +
                    '<div class="flex items-center gap-2">' +
                    '<span class="icon-[tabler--check] size-3.5 text-success"></span>' +
                    '<span class="text-xs font-medium">' + dayName + '</span>' +
                    '</div>' +
                    '<span class="text-xs text-base-content/60">' + hoursText + '</span>' +
                    '</div>';
            } else {
                availHtml += '<div class="flex items-center justify-between py-1.5 px-2 rounded bg-error/5">' +
                    '<div class="flex items-center gap-2">' +
                    '<span class="icon-[tabler--x] size-3.5 text-error"></span>' +
                    '<span class="text-xs font-medium text-error">' + dayName + '</span>' +
                    '</div>' +
                    '<span class="text-xs text-base-content/40">Off</span>' +
                    '</div>';
            }
        });
        availHtml += '</div>';

        // Existing sessions on the start date
        if (data.existing_sessions.length > 0 && selectedDays.length === 0) {
            availHtml += '<div class="mt-2">' +
                '<div class="text-xs font-medium text-base-content/60 mb-1">Existing on ' + data.formatted_date + '</div>';
            data.existing_sessions.forEach(function(session) {
                availHtml += '<div class="flex items-center gap-1.5 text-xs bg-base-200 rounded px-2 py-1 mb-0.5">' +
                    '<span class="icon-[tabler--calendar-event] size-3 text-base-content/50"></span>' +
                    '<span class="font-medium">' + session.time + '</span>' +
                    '<span class="text-base-content/60 truncate">- ' + session.title + '</span>' +
                    '</div>';
            });
            availHtml += '</div>';
        }

        availDaysContainer.innerHTML = availHtml;
    }

    // Conflict check — show override toggle when conflict detected
    var conflictCard = document.getElementById('conflict-override-card');
    var conflictMessage = document.getElementById('conflict-message');
    var conflictToggle = document.getElementById('conflict_override_toggle');
    var submitBtn = document.getElementById('submit-btn');

    function checkSchedulingConflict(data) {
        if (!conflictCard) return;

        var hasConflict = false;
        var messages = [];

        // Check if instructor doesn't work on selected day
        var selectedDays = getSelectedDays();
        var daysToCheck = selectedDays.length > 0 ? selectedDays : [data.day_of_week];

        daysToCheck.forEach(function(dayIndex) {
            if (!data.working_days[dayIndex]) {
                hasConflict = true;
                messages.push(dayNames[dayIndex] + ' is a day off');
            }
        });

        // Check for overlapping sessions
        if (data.existing_sessions && data.existing_sessions.length > 0) {
            var sessionTime = timeInput.value;
            var duration = parseInt(durationInput.value) || 60;

            if (sessionTime) {
                var parts = sessionTime.split(':');
                var startMins = parseInt(parts[0]) * 60 + parseInt(parts[1]);
                var endMins = startMins + duration;

                data.existing_sessions.forEach(function(session) {
                    // Parse existing session time range "g:i A - g:i A"
                    var timeParts = session.time.split(' - ');
                    if (timeParts.length === 2) {
                        var existStart = parseTimeToMinutes(timeParts[0]);
                        var existEnd = parseTimeToMinutes(timeParts[1]);

                        if (existStart !== null && existEnd !== null) {
                            if (startMins < existEnd && endMins > existStart) {
                                hasConflict = true;
                                messages.push('Overlaps with "' + session.title + '" (' + session.time + ')');
                            }
                        }
                    }
                });
            }
        }

        if (hasConflict) {
            conflictCard.classList.remove('hidden');
            conflictMessage.textContent = messages.join('. ') + '.';
            // Disable submit until override is toggled
            if (conflictToggle) {
                submitBtn.disabled = !conflictToggle.checked;
            }
        } else {
            conflictCard.classList.add('hidden');
            submitBtn.disabled = false;
            if (conflictToggle) conflictToggle.checked = false;
        }
    }

    function parseTimeToMinutes(timeStr) {
        var match = timeStr.trim().match(/(\d+):(\d+)\s*(AM|PM)/i);
        if (!match) return null;
        var hours = parseInt(match[1]);
        var minutes = parseInt(match[2]);
        var isPM = match[3].toUpperCase() === 'PM';
        if (isPM && hours !== 12) hours += 12;
        if (!isPM && hours === 12) hours = 0;
        return hours * 60 + minutes;
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
