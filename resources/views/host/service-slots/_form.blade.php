@php
    $serviceSlot = $serviceSlot ?? null;
    $selectedServicePlanId = $selectedServicePlanId ?? $serviceSlot?->service_plan_id;
    $selectedInstructorId = $selectedInstructorId ?? $serviceSlot?->instructor_id;
    $selectedDate = $selectedDate ?? $serviceSlot?->start_time?->format('Y-m-d') ?? now()->format('Y-m-d');
    $selectedLocationId = old('location_id', $serviceSlot?->location_id);
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
    <div class="space-y-6">
        {{-- Card 1: Service Selection --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">1</span>
                    <h3 class="card-title">Service Selection</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="service_plan_id">Service</label>
                    <select id="service_plan_id" name="service_plan_id" class="hidden @error('service_plan_id') input-error @enderror" required
                        data-select='{
                            "hasSearch": true,
                            "searchPlaceholder": "Search services...",
                            "placeholder": "Select a service...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">Select a service...</option>
                        @foreach($servicePlans as $plan)
                        <option value="{{ $plan->id }}"
                            data-duration="{{ $plan->duration_minutes }}"
                            data-price="{{ $plan->price }}"
                            {{ old('service_plan_id', $selectedServicePlanId) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ $plan->formatted_duration }})
                        </option>
                        @endforeach
                    </select>
                    @error('service_plan_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Service Info Display --}}
                <div id="service-info" class="hidden">
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div>
                            <p class="text-sm">Duration: <span id="service-duration" class="font-semibold">-</span> min | Price: $<span id="service-price" class="font-semibold">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Instructor --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                    <h3 class="card-title">Instructor</h3>
                </div>
            </div>
            <div class="card-body">
                <div>
                    <label class="label-text" for="instructor_id">Assign Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="hidden @error('instructor_id') input-error @enderror" required
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
                        <option value="{{ $instructor->id }}" {{ old('instructor_id', $selectedInstructorId) == $instructor->id ? 'selected' : '' }}>
                            {{ $instructor->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card 3: Date & Time --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">3</span>
                    <h3 class="card-title">Date & Time</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="slot_date">Date</label>
                        <input type="text" id="slot_date" name="slot_date"
                            value="{{ old('slot_date', $selectedDate) }}"
                            class="input w-full flatpickr-date @error('slot_date') input-error @enderror"
                            placeholder="Select date..." required>
                        @error('slot_date')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="slot_time">Start Time</label>
                        <input type="text" id="slot_time" name="slot_time"
                            value="{{ old('slot_time', $serviceSlot?->start_time?->format('H:i') ?? '09:00') }}"
                            class="input w-full flatpickr-time @error('slot_time') input-error @enderror"
                            placeholder="Select time..." required>
                        @error('slot_time')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="start_time" id="start_time">

                {{-- Recurring Toggle --}}
                @if(!$serviceSlot)
                <div class="pt-2 border-t border-base-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                            class="checkbox checkbox-primary"
                            {{ old('is_recurring') ? 'checked' : '' }}>
                        <div>
                            <span class="label-text font-medium">Recurring Slot</span>
                            <p class="text-xs text-base-content/50">Create multiple slots on selected days of the week</p>
                        </div>
                    </label>
                </div>

                {{-- Recurring Options --}}
                <div id="recurring-options" class="space-y-4 {{ old('is_recurring') ? '' : 'hidden' }}">
                    <div>
                        <label class="label-text mb-2 block">Days of Week</label>
                        <div class="flex flex-wrap gap-2" id="days-of-week-selector">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $day)
                            <label class="day-checkbox flex items-center justify-center w-14 h-10 rounded-lg border-2 border-base-300 cursor-pointer hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10 transition-all" for="day-{{ $index }}">
                                <input type="checkbox" id="day-{{ $index }}" name="recurrence_days[]" value="{{ $index }}" class="hidden"
                                    {{ is_array(old('recurrence_days')) && in_array($index, old('recurrence_days')) ? 'checked' : '' }}>
                                <span class="text-sm font-medium">{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="label-text" for="recurrence_end_date">End Date</label>
                        <input type="text" id="recurrence_end_date" name="recurrence_end_date"
                            value="{{ old('recurrence_end_date') }}"
                            class="input w-full flatpickr-date"
                            placeholder="Select end date...">
                        <p class="text-xs text-base-content/50 mt-1">Leave empty to create slots for up to 12 weeks</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

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
                    <div>
                        <label class="label-text" for="location_id">Location</label>
                        <select id="location_id" name="location_id" class="hidden"
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
                            <option value="">Select a location...</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $selectedLocationId) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="room_id">Room</label>
                        <select id="room_id" name="room_id" class="select w-full @error('room_id') input-error @enderror">
                            <option value="">Select a room...</option>
                        </select>
                        @error('room_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: Pricing & Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                    <h3 class="card-title">Pricing & Notes</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="price">Custom Price ($)</label>
                    <input type="number" id="price" name="price"
                        value="{{ old('price', $serviceSlot?->price) }}"
                        class="input w-full @error('price') input-error @enderror"
                        min="0" max="9999.99" step="0.01"
                        placeholder="Leave empty to use service price">
                    <p class="text-xs text-base-content/60 mt-1">Override the default service price for this slot.</p>
                    @error('price')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="notes">Internal Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                        class="textarea w-full @error('notes') input-error @enderror"
                        placeholder="Notes for staff only (not visible to clients)">{{ old('notes', $serviceSlot?->notes) }}</textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card 6: Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">6</span>
                    <h3 class="card-title">Status</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-3">
                    @php
                        $availableStatuses = [
                            'draft' => 'Draft',
                            'available' => 'Available',
                        ];
                        if ($serviceSlot) {
                            $availableStatuses = \App\Models\ServiceSlot::getStatuses();
                        }
                    @endphp
                    @foreach($availableStatuses as $value => $label)
                        @if($value !== 'booked' && $value !== 'cancelled')
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="{{ $value }}"
                                class="radio radio-primary"
                                {{ old('status', $serviceSlot?->status ?? 'draft') === $value ? 'checked' : '' }}>
                            <span class="label-text">{{ $label }}</span>
                            @if($value === 'available')
                                <span class="badge badge-success badge-sm">Open for Booking</span>
                            @endif
                        </label>
                        @endif
                    @endforeach
                </div>
                <p class="text-xs text-base-content/60 mt-2">Available slots are visible to clients and open for booking.</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-5"></span>
                {{ $serviceSlot ? 'Update Slot' : 'Create Slot' }}
            </button>
            <a href="{{ route('service-slots.index') }}" class="btn btn-ghost">
                Cancel
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var servicePlanSelect = document.getElementById('service_plan_id');
    var dateInput = document.getElementById('slot_date');
    var timeInput = document.getElementById('slot_time');
    var startTimeInput = document.getElementById('start_time');
    var serviceInfo = document.getElementById('service-info');
    var serviceDuration = document.getElementById('service-duration');
    var servicePrice = document.getElementById('service-price');

    function updateStartTime() {
        var date = dateInput.value;
        var time = timeInput.value;
        if (date && time) {
            startTimeInput.value = date + 'T' + time;
        }
    }

    function updateServiceInfo() {
        var selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            var duration = selectedOption.dataset.duration;
            var price = selectedOption.dataset.price;
            serviceDuration.textContent = duration || '-';
            servicePrice.textContent = price || '0';
            serviceInfo.classList.remove('hidden');
        } else {
            serviceInfo.classList.add('hidden');
        }
    }

    // Service plan change handler
    servicePlanSelect.addEventListener('change', updateServiceInfo);

    // Watch for HSSelect changes
    var observer = new MutationObserver(function() {
        updateServiceInfo();
    });
    observer.observe(servicePlanSelect, { attributes: true, childList: true, subtree: true });

    dateInput.addEventListener('change', updateStartTime);
    timeInput.addEventListener('change', updateStartTime);

    // Initialize flatpickr
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false
    });

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
        static: false
    });

    // Recurring toggle
    var recurringCheckbox = document.getElementById('is_recurring');
    var recurringOptions = document.getElementById('recurring-options');
    if (recurringCheckbox && recurringOptions) {
        recurringCheckbox.addEventListener('change', function() {
            recurringOptions.classList.toggle('hidden', !this.checked);
        });
    }

    // Initialize
    updateStartTime();
    setTimeout(updateServiceInfo, 200);
});
</script>
@endpush
