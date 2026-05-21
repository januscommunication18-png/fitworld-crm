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
                    <x-studio-select name="service_plan_id" id="service_plan_id" placeholder="Select a service..." :required="true" :option-count="count($servicePlans)">
                        <option value="">Select a service...</option>
                        @foreach($servicePlans as $plan)
                        <option value="{{ $plan->id }}"
                            data-duration="{{ $plan->duration_minutes }}"
                            data-price="{{ $plan->price }}"
                            {{ old('service_plan_id', $selectedServicePlanId) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ $plan->formatted_duration }})
                        </option>
                        @endforeach
                    </x-studio-select>
                    @error('service_plan_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Custom Title --}}
                <div>
                    <label class="label-text" for="title">Custom Title</label>
                    <input type="text" id="title" name="title"
                        value="{{ old('title', $serviceSlot?->title) }}"
                        class="input w-full @error('title') input-error @enderror"
                        placeholder="Optional — override the default service name"
                        maxlength="100">
                    <p class="text-xs text-base-content/60 mt-1">Leave empty to use the service plan name.</p>
                    @error('title')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card 2: Instructor --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                    <h3 class="card-title">Assign staff member / Instructor</h3>
                </div>
            </div>
            <div class="card-body">
                <x-studio-member-select
                    name="instructor_id"
                    :selected="$selectedInstructorId"
                    :required="true"
                />
                @error('instructor_id')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
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
                    <div>
                        <label class="label-text" for="slot_end_time_display">End Time</label>
                        <input type="text" id="slot_end_time_display"
                            class="input w-full bg-base-200"
                            placeholder="—" readonly tabindex="-1">
                        <p class="text-xs text-base-content/60 mt-1">Auto-calculated from start time and service duration.</p>
                    </div>
                    <div>
                        <label class="label-text" for="slot_price_display">Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60">$</span>
                            <input type="text" id="slot_price_display"
                                class="input w-full bg-base-200 pl-7"
                                placeholder="—" readonly tabindex="-1">
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Set by the selected service plan.</p>
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

        {{-- Card 5: Notes --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                    <h3 class="card-title">Notes</h3>
                </div>
            </div>
            <div class="card-body space-y-4">
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
                @php
                    $isAvailable = old('status', $serviceSlot?->status ?? 'draft') === 'available';
                @endphp
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Open for Booking</span>
                        <p class="text-xs text-base-content/60">Available slots are visible to clients and open for booking.</p>
                    </div>
                    <input type="hidden" name="status" id="slot_status_value" value="{{ $isAvailable ? 'available' : 'draft' }}">
                    <label class="switch switch-primary">
                        <input type="checkbox" id="slot_status_toggle"
                            {{ $isAvailable ? 'checked' : '' }}
                            onchange="document.getElementById('slot_status_value').value = this.checked ? 'available' : 'draft'" />
                        <span class="switch-indicator"></span>
                    </label>
                </div>
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
    // Location -> Room auto-load
    var locationRooms = @json($locations->mapWithKeys(fn($loc) => [$loc->id => $loc->rooms->map(fn($r) => ['id' => $r->id, 'name' => $r->name])]));
    var locationSelect = document.getElementById('location_id');
    var roomSelect = document.getElementById('room_id');
    var selectedRoomId = {{ old('room_id', $serviceSlot->room_id ?? 'null') }};

    function updateRooms() {
        var locationId = locationSelect.value;
        var rooms = locationRooms[locationId] || [];

        roomSelect.innerHTML = '<option value="">Select a room...</option>';
        rooms.forEach(function(room) {
            var opt = document.createElement('option');
            opt.value = room.id;
            opt.textContent = room.name;
            if (room.id == selectedRoomId) opt.selected = true;
            roomSelect.appendChild(opt);
        });

        // Show/hide room field based on whether rooms exist
        roomSelect.closest('div').style.display = rooms.length > 0 ? '' : 'none';
    }

    locationSelect.addEventListener('change', function() {
        selectedRoomId = null; // Reset on manual change
        updateRooms();
    });

    // Also observe for HSSelect (advanced select) changes
    var observer = new MutationObserver(function() {
        updateRooms();
    });
    observer.observe(locationSelect, { attributes: true, childList: true });

    // Initial load
    updateRooms();

    var servicePlanSelect = document.getElementById('service_plan_id');
    var dateInput = document.getElementById('slot_date');
    var timeInput = document.getElementById('slot_time');
    var startTimeInput = document.getElementById('start_time');
    var endTimeDisplay = document.getElementById('slot_end_time_display');
    var priceDisplay = document.getElementById('slot_price_display');

    function getSelectedDuration() {
        var selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) return null;
        var d = parseInt(selectedOption.dataset.duration, 10);
        return isNaN(d) ? null : d;
    }

    function formatTime12h(hours, minutes) {
        var period = hours >= 12 ? 'PM' : 'AM';
        var h12 = hours % 12;
        if (h12 === 0) h12 = 12;
        var mm = minutes < 10 ? '0' + minutes : '' + minutes;
        return h12 + ':' + mm + ' ' + period;
    }

    function updateEndTime() {
        var time = timeInput.value;
        var duration = getSelectedDuration();
        if (!time || duration === null) {
            endTimeDisplay.value = '';
            return;
        }
        var parts = time.split(':');
        var h = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        if (isNaN(h) || isNaN(m)) {
            endTimeDisplay.value = '';
            return;
        }
        var total = h * 60 + m + duration;
        var endH = Math.floor(total / 60) % 24;
        var endM = total % 60;
        endTimeDisplay.value = formatTime12h(endH, endM);
    }

    function updateStartTime() {
        var date = dateInput.value;
        var time = timeInput.value;
        if (date && time) {
            startTimeInput.value = date + 'T' + time;
        }
        updateEndTime();
    }

    function updateServiceInfo() {
        var selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            var price = selectedOption.dataset.price;
            priceDisplay.value = parseFloat(price || 0).toFixed(2);
        } else {
            priceDisplay.value = '';
        }
        updateEndTime();
    }

    // Service plan change handler
    servicePlanSelect.addEventListener('change', updateServiceInfo);

    // Watch for HSSelect changes
    var observer = new MutationObserver(function() {
        updateServiceInfo();
    });
    observer.observe(servicePlanSelect, { attributes: true, childList: true, subtree: true });

    // Initialize flatpickr with onChange hooks to update start_time
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false,
        onChange: function() { updateStartTime(); }
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
        static: false,
        onChange: function() { updateStartTime(); }
    });

    dateInput.addEventListener('change', updateStartTime);
    timeInput.addEventListener('change', updateStartTime);

    // Recurring toggle
    var recurringCheckbox = document.getElementById('is_recurring');
    var recurringOptions = document.getElementById('recurring-options');
    if (recurringCheckbox && recurringOptions) {
        recurringCheckbox.addEventListener('change', function() {
            recurringOptions.classList.toggle('hidden', !this.checked);
        });
    }

    // Ensure start_time is set before form submission
    var form = startTimeInput.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            updateStartTime();
        });
    }

    // Initialize
    updateStartTime();
    setTimeout(updateServiceInfo, 200);
});
</script>
@endpush
