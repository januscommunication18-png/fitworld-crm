@php
    $serviceSlot = $serviceSlot ?? null;
    $selectedServicePlanId = $selectedServicePlanId ?? $serviceSlot?->service_plan_id;
    $selectedInstructorId = $selectedInstructorId ?? $serviceSlot?->instructor_id;
    $selectedDate = $selectedDate ?? $serviceSlot?->start_time?->format('Y-m-d') ?? now()->format('Y-m-d');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Service & Instructor --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Service & Instructor</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="service_plan_id">Service</label>
                        <select id="service_plan_id" name="service_plan_id" class="select w-full @error('service_plan_id') input-error @enderror" required>
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
                    <div>
                        <label class="label-text" for="instructor_id">Instructor</label>
                        <select id="instructor_id" name="instructor_id" class="select w-full @error('instructor_id') input-error @enderror" required>
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
        </div>

        {{-- Date & Time --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Date & Time</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="slot_date">Date</label>
                        <input type="date" id="slot_date" name="slot_date"
                            value="{{ old('slot_date', $selectedDate) }}"
                            class="input w-full @error('slot_date') input-error @enderror"
                            min="{{ now()->format('Y-m-d') }}"
                            required>
                        @error('slot_date')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="slot_time">Start Time</label>
                        <input type="time" id="slot_time" name="slot_time"
                            value="{{ old('slot_time', $serviceSlot?->start_time?->format('H:i') ?? '09:00') }}"
                            class="input w-full @error('slot_time') input-error @enderror"
                            required>
                        @error('slot_time')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="start_time" id="start_time">

                <div id="duration-info" class="text-sm text-base-content/60 hidden">
                    Duration: <span id="duration-display">-</span> min
                    (End time: <span id="end-time-display">-</span>)
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Location</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="location_id">Location</label>
                        <select id="location_id" name="location_id" class="select w-full @error('location_id') input-error @enderror">
                            <option value="">Select a location...</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $serviceSlot?->location_id) == $location->id ? 'selected' : '' }}>
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
                            {{-- Rooms are loaded dynamically based on location --}}
                        </select>
                        @error('room_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Additional Info</h3>
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

                @if($serviceSlot)
                <div>
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select w-full @error('status') input-error @enderror">
                        @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $serviceSlot->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $serviceSlot ? 'Update Slot' : 'Create Slot' }}
                </button>
                <a href="{{ route('service-slots.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>

        @if($serviceSlot)
        {{-- Slot Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Slot Info</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Created</dt>
                        <dd>{{ $serviceSlot->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Status</dt>
                        <dd><span class="badge {{ $serviceSlot->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $serviceSlot->status }}</span></dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var servicePlanSelect = document.getElementById('service_plan_id');
    var dateInput = document.getElementById('slot_date');
    var timeInput = document.getElementById('slot_time');
    var startTimeInput = document.getElementById('start_time');
    var durationInfo = document.getElementById('duration-info');
    var durationDisplay = document.getElementById('duration-display');
    var endTimeDisplay = document.getElementById('end-time-display');

    function updateStartTime() {
        var date = dateInput.value;
        var time = timeInput.value;
        if (date && time) {
            startTimeInput.value = date + 'T' + time;
        }
    }

    function updateDurationInfo() {
        var selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];
        var duration = selectedOption.dataset.duration;

        if (duration && dateInput.value && timeInput.value) {
            durationDisplay.textContent = duration;

            // Calculate end time
            var startTime = new Date(dateInput.value + 'T' + timeInput.value);
            var endTime = new Date(startTime.getTime() + duration * 60000);
            var endTimeStr = endTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            endTimeDisplay.textContent = endTimeStr;

            durationInfo.classList.remove('hidden');
        } else {
            durationInfo.classList.add('hidden');
        }
    }

    servicePlanSelect.addEventListener('change', updateDurationInfo);
    dateInput.addEventListener('change', function() {
        updateStartTime();
        updateDurationInfo();
    });
    timeInput.addEventListener('change', function() {
        updateStartTime();
        updateDurationInfo();
    });

    // Initialize
    updateStartTime();
    updateDurationInfo();
});
</script>
@endpush
