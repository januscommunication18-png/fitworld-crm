@extends('layouts.dashboard')

@section('title', 'Send 1-1 Meeting Invite')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    .flatpickr-calendar {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-day.selected {
        background: oklch(var(--p)) !important;
        border-color: oklch(var(--p)) !important;
    }
    .slot-btn {
        transition: all 0.15s ease;
    }
    .slot-btn.selected {
        background: oklch(var(--p));
        color: oklch(var(--pc));
        border-color: oklch(var(--p));
    }
</style>
@endpush

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('one-on-one.index') }}">1:1 Meetings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Send 1-1 Meeting Invite</li>
    </ol>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('one-on-one.index') }}" class="inline-flex items-center gap-1.5 text-sm text-base-content/50 hover:text-primary transition-colors mb-4">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to 1:1 Meetings
        </a>
        <h1 class="text-2xl font-bold">Send 1-1 Meeting Invite</h1>
        <p class="text-base-content/60 mt-1">Invite a client to book a 1:1 meeting{{ $isOwner ? '' : ' with you' }}.</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
    <div class="alert alert-soft alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Invite Form --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form id="invite-form" action="{{ route('one-on-one.send-invite') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Staff Member Selection (Owner only) --}}
                @if($isOwner && $instructorsWithBooking->isNotEmpty())
                <div>
                    <label class="label-text mb-2 block text-base font-medium" for="instructor_id">
                        Staff Member <span class="text-error">*</span>
                    </label>
                    <select id="instructor_id" name="instructor_id" class="select select-bordered w-full" required>
                        <option value="">Choose a team member...</option>
                        @foreach($instructorsWithBooking as $inst)
                            <option value="{{ $inst->id }}"
                                data-durations="{{ json_encode($inst->bookingProfile->allowed_durations ?? [30]) }}"
                                data-default-duration="{{ $inst->bookingProfile->default_duration ?? 30 }}"
                                data-working-days="{{ json_encode($inst->bookingProfile->working_days ?? [1,2,3,4,5]) }}"
                                data-start-time="{{ $inst->bookingProfile->default_start_time ?? '09:00' }}"
                                data-end-time="{{ $inst->bookingProfile->default_end_time ?? '17:00' }}">
                                {{ $inst->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @elseif(!$isOwner && $instructor)
                {{-- Instructor (Read-only for non-owners) --}}
                <div>
                    <label class="label-text mb-2 block text-base font-medium">Staff Member</label>
                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-sm font-bold text-primary">{{ strtoupper(substr($instructor->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="font-medium">{{ $instructor->name }}</span>
                            <span class="badge badge-primary badge-sm ml-2">You</span>
                        </div>
                    </div>
                    <input type="hidden" name="instructor_id" value="{{ $instructor->id }}">
                </div>
                @endif

                <div class="divider">Client Details</div>

                {{-- Client Name --}}
                <div>
                    <label class="label-text mb-2 block text-base font-medium" for="client_name">
                        Client Name
                    </label>
                    <input type="text" id="client_name" name="client_name"
                        class="input input-bordered w-full"
                        placeholder="John Doe"
                        value="{{ old('client_name') }}">
                </div>

                {{-- Client Email --}}
                <div>
                    <label class="label-text mb-2 block text-base font-medium" for="email">
                        Client Email Address <span class="text-error">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                        class="input input-bordered w-full"
                        placeholder="client@example.com"
                        value="{{ old('email') }}"
                        required>
                </div>

                <div class="divider">Meeting Details</div>

                {{-- Meeting Duration --}}
                <div>
                    <label class="label-text mb-2 block text-base font-medium" for="duration">
                        Meeting Duration
                    </label>
                    <select id="duration" name="duration" class="select select-bordered w-full">
                        @php
                            $allowedDurations = $profile->allowed_durations ?? [30, 60];
                            $defaultDuration = $profile->default_duration ?? 30;
                            $durationLabels = [15 => '15 minutes', 30 => '30 minutes', 45 => '45 minutes', 60 => '1 hour'];
                        @endphp
                        @foreach($allowedDurations as $dur)
                            <option value="{{ $dur }}" {{ $dur == $defaultDuration ? 'selected' : '' }}>
                                {{ $durationLabels[$dur] ?? $dur . ' minutes' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Availability Display --}}
                <div id="availability-info" class="bg-base-200/50 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                        <div>
                            <span class="text-sm font-medium">Client will choose from available times</span>
                            <p class="text-xs text-base-content/60 mt-0.5" id="availability-text">
                                @php
                                    $workingDays = $profile->working_days ?? [1, 2, 3, 4, 5];
                                    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    $workingDayNames = array_map(fn($d) => $dayNames[$d], $workingDays);
                                    $startTime = $profile->default_start_time ? \Carbon\Carbon::parse($profile->default_start_time)->format('g:i A') : '9:00 AM';
                                    $endTime = $profile->default_end_time ? \Carbon\Carbon::parse($profile->default_end_time)->format('g:i A') : '5:00 PM';
                                @endphp
                                {{ implode(', ', $workingDayNames) }} &bull; {{ $startTime }} - {{ $endTime }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Specific Time Toggle --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="label-text text-base font-medium">Suggest Specific Time Slots</label>
                        <p class="text-xs text-base-content/60">Pre-select dates and times for the client</p>
                    </div>
                    <input type="checkbox" id="specific_time_toggle" class="toggle toggle-primary" onchange="toggleSpecificTime(this.checked)">
                </div>

                {{-- Specific Time Slots Section --}}
                <div id="specific-time-fields" class="hidden space-y-4 border-l-4 border-primary/30 pl-4">
                    {{-- Selected Slots Summary --}}
                    <div id="selected-slots-summary" class="hidden">
                        <label class="label-text mb-2 block text-sm font-medium text-primary">Selected Time Slots</label>
                        <div id="selected-slots-list" class="space-y-2"></div>
                    </div>

                    {{-- Date Picker Section --}}
                    <div id="date-picker-section">
                        <label class="label-text mb-2 block text-sm font-medium">Select a Date</label>
                        <input type="text" id="date-picker" class="input input-bordered w-full" placeholder="Click to select a date" readonly>
                    </div>

                    {{-- Time Slots Section (shown after date selection) --}}
                    <div id="time-slots-section" class="hidden">
                        <div class="flex items-center justify-between mb-2">
                            <label class="label-text text-sm font-medium">
                                Available Slots for <span id="selected-date-display" class="text-primary"></span>
                            </label>
                            <button type="button" id="clear-date-btn" class="btn btn-ghost btn-xs" onclick="clearDateSelection()">
                                <span class="icon-[tabler--x] size-4"></span>
                                Clear
                            </button>
                        </div>
                        <div id="time-slots-container" class="min-h-[100px]">
                            {{-- Slots will be loaded here --}}
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-200">
                            <p class="text-xs text-base-content/50">
                                <span class="icon-[tabler--info-circle] size-3.5 inline-block mr-1"></span>
                                Click slots to select/deselect. Multiple selection allowed.
                            </p>
                            <button type="button" id="add-slots-btn" class="btn btn-primary btn-sm" onclick="addSelectedSlots()" disabled>
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add Slots
                            </button>
                        </div>
                    </div>

                    {{-- Add Another Date Button --}}
                    <button type="button" id="add-another-date-btn" class="hidden btn btn-outline btn-sm w-full" onclick="showDatePicker()">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Another Date
                    </button>

                    {{-- Hidden input to store selected slots as JSON --}}
                    <input type="hidden" name="scheduled_slots" id="scheduled_slots_input" value="">
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-base-200">
                    <a href="{{ route('one-on-one.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span class="loading loading-spinner loading-sm hidden"></span>
                        <span class="icon-[tabler--send] size-5"></span>
                        Send Invite
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
// State
let selectedSlots = {}; // { '2026-03-17': ['09:00', '10:00'], '2026-03-18': ['14:00'] }
let currentDateSlots = []; // Slots selected for current date being edited
let currentDate = null;
let flatpickrInstance = null;

// Get instructor ID (for fetching availability)
function getInstructorId() {
    const select = document.getElementById('instructor_id');
    if (select) {
        return select.value;
    }
    const hidden = document.querySelector('input[name="instructor_id"]');
    return hidden ? hidden.value : null;
}

function getDuration() {
    return document.getElementById('duration').value;
}

function toggleSpecificTime(enabled) {
    const fields = document.getElementById('specific-time-fields');
    const availInfo = document.getElementById('availability-info');

    if (enabled) {
        fields.classList.remove('hidden');
        availInfo.classList.add('hidden');
        initDatePicker();
    } else {
        fields.classList.add('hidden');
        availInfo.classList.remove('hidden');
        // Clear all selections
        selectedSlots = {};
        currentDateSlots = [];
        currentDate = null;
        updateSelectedSlotsDisplay();
        updateHiddenInput();
    }
}

function initDatePicker() {
    if (flatpickrInstance) {
        flatpickrInstance.destroy();
    }

    flatpickrInstance = flatpickr('#date-picker', {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        maxDate: new Date().fp_incr(60), // 60 days ahead
        onChange: function(selectedDates, dateStr) {
            if (dateStr) {
                selectDate(dateStr);
            }
        }
    });
}

function selectDate(dateStr) {
    currentDate = dateStr;
    currentDateSlots = selectedSlots[dateStr] ? [...selectedSlots[dateStr]] : [];

    // Format date for display
    const dateObj = new Date(dateStr + 'T12:00:00');
    const displayDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
    document.getElementById('selected-date-display').textContent = displayDate;

    // Show time slots section
    document.getElementById('date-picker-section').classList.add('hidden');
    document.getElementById('time-slots-section').classList.remove('hidden');

    // Fetch available slots
    fetchTimeSlots(dateStr);
}

function fetchTimeSlots(dateStr) {
    const container = document.getElementById('time-slots-container');
    container.innerHTML = '<div class="flex items-center justify-center py-8"><span class="loading loading-spinner loading-md text-primary"></span></div>';

    const instructorId = getInstructorId();
    const duration = getDuration();

    if (!instructorId) {
        container.innerHTML = '<div class="text-center py-8 text-base-content/50">Please select a staff member first</div>';
        return;
    }

    // Fetch from same-origin availability endpoint
    const url = `/one-on-one/availability/${instructorId}?date=${dateStr}&duration=${duration}`;

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.slots || data.slots.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <span class="icon-[tabler--calendar-x] size-10 text-base-content/20 mx-auto block mb-2"></span>
                    <p class="text-base-content/50 text-sm">No available slots for this date</p>
                </div>
            `;
            return;
        }

        renderTimeSlots(data.slots);
    })
    .catch(error => {
        console.error('Error fetching slots:', error);
        container.innerHTML = `
            <div class="text-center py-8 text-error/60">
                <span class="icon-[tabler--alert-triangle] size-10 mx-auto block mb-2"></span>
                <p class="text-sm">Error loading time slots</p>
            </div>
        `;
    });
}

function renderTimeSlots(slots) {
    const container = document.getElementById('time-slots-container');

    // Group by period
    const morning = slots.filter(s => parseInt(s.time.split(':')[0]) < 12);
    const afternoon = slots.filter(s => {
        const hour = parseInt(s.time.split(':')[0]);
        return hour >= 12 && hour < 17;
    });
    const evening = slots.filter(s => parseInt(s.time.split(':')[0]) >= 17);

    let html = '';

    if (morning.length > 0) {
        html += `<div class="mb-3">
            <p class="text-xs font-medium text-base-content/50 mb-2">Morning</p>
            <div class="flex flex-wrap gap-2">
                ${morning.map(s => renderSlotButton(s)).join('')}
            </div>
        </div>`;
    }

    if (afternoon.length > 0) {
        html += `<div class="mb-3">
            <p class="text-xs font-medium text-base-content/50 mb-2">Afternoon</p>
            <div class="flex flex-wrap gap-2">
                ${afternoon.map(s => renderSlotButton(s)).join('')}
            </div>
        </div>`;
    }

    if (evening.length > 0) {
        html += `<div class="mb-3">
            <p class="text-xs font-medium text-base-content/50 mb-2">Evening</p>
            <div class="flex flex-wrap gap-2">
                ${evening.map(s => renderSlotButton(s)).join('')}
            </div>
        </div>`;
    }

    container.innerHTML = html;

    // Add click handlers
    container.querySelectorAll('.slot-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            toggleSlotSelection(this.dataset.time, this);
        });
    });
}

function renderSlotButton(slot) {
    const isSelected = currentDateSlots.includes(slot.time);
    return `<button type="button" class="slot-btn px-3 py-2 text-sm rounded-lg border-2 ${isSelected ? 'selected border-primary bg-primary text-primary-content' : 'border-base-300 hover:border-primary'}" data-time="${slot.time}" data-display="${slot.display}">
        ${slot.display}
        ${isSelected ? '<span class="icon-[tabler--check] size-4 ml-1"></span>' : ''}
    </button>`;
}

function toggleSlotSelection(time, btn) {
    const index = currentDateSlots.indexOf(time);
    if (index > -1) {
        currentDateSlots.splice(index, 1);
        btn.classList.remove('selected', 'border-primary', 'bg-primary', 'text-primary-content');
        btn.classList.add('border-base-300');
        btn.innerHTML = btn.dataset.display;
    } else {
        currentDateSlots.push(time);
        btn.classList.add('selected', 'border-primary', 'bg-primary', 'text-primary-content');
        btn.classList.remove('border-base-300');
        btn.innerHTML = btn.dataset.display + '<span class="icon-[tabler--check] size-4 ml-1"></span>';
    }

    // Enable/disable add button
    document.getElementById('add-slots-btn').disabled = currentDateSlots.length === 0;
}

function addSelectedSlots() {
    if (currentDateSlots.length === 0 || !currentDate) return;

    // Save slots for current date
    selectedSlots[currentDate] = [...currentDateSlots];

    // Update display
    updateSelectedSlotsDisplay();
    updateHiddenInput();

    // Reset current selection
    currentDate = null;
    currentDateSlots = [];

    // Hide time slots, show add another date button
    document.getElementById('time-slots-section').classList.add('hidden');
    document.getElementById('add-another-date-btn').classList.remove('hidden');
    document.getElementById('date-picker-section').classList.add('hidden');

    // Clear date picker
    if (flatpickrInstance) {
        flatpickrInstance.clear();
    }
}

function showDatePicker() {
    document.getElementById('add-another-date-btn').classList.add('hidden');
    document.getElementById('date-picker-section').classList.remove('hidden');
}

function clearDateSelection() {
    currentDate = null;
    currentDateSlots = [];
    document.getElementById('time-slots-section').classList.add('hidden');
    document.getElementById('date-picker-section').classList.remove('hidden');

    // Show add another date button if we have slots
    if (Object.keys(selectedSlots).length > 0) {
        document.getElementById('add-another-date-btn').classList.remove('hidden');
        document.getElementById('date-picker-section').classList.add('hidden');
    }

    if (flatpickrInstance) {
        flatpickrInstance.clear();
    }
}

function updateSelectedSlotsDisplay() {
    const summary = document.getElementById('selected-slots-summary');
    const list = document.getElementById('selected-slots-list');

    const dates = Object.keys(selectedSlots);
    if (dates.length === 0) {
        summary.classList.add('hidden');
        return;
    }

    summary.classList.remove('hidden');

    // Sort dates
    dates.sort();

    let html = '';
    dates.forEach(dateStr => {
        const slots = selectedSlots[dateStr];
        const dateObj = new Date(dateStr + 'T12:00:00');
        const displayDate = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });

        // Format times
        const formattedTimes = slots.map(time => {
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return hour12 + ':' + m + ' ' + ampm;
        }).join(', ');

        html += `
            <div class="flex items-center justify-between bg-primary/5 border border-primary/20 rounded-lg px-3 py-2">
                <div>
                    <span class="font-medium text-sm">${displayDate}</span>
                    <span class="text-xs text-base-content/60 ml-2">${formattedTimes}</span>
                </div>
                <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="removeDate('${dateStr}')">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;
    });

    list.innerHTML = html;
}

function removeDate(dateStr) {
    delete selectedSlots[dateStr];
    updateSelectedSlotsDisplay();
    updateHiddenInput();

    // If no slots left, show date picker
    if (Object.keys(selectedSlots).length === 0) {
        document.getElementById('add-another-date-btn').classList.add('hidden');
        document.getElementById('date-picker-section').classList.remove('hidden');
    }
}

function updateHiddenInput() {
    document.getElementById('scheduled_slots_input').value = JSON.stringify(selectedSlots);
}

// Handle instructor selection change (for owners)
const instructorSelect = document.getElementById('instructor_id');
if (instructorSelect) {
    instructorSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (!selected.value) return;

        const durations = JSON.parse(selected.dataset.durations || '[30]');
        const defaultDuration = selected.dataset.defaultDuration || 30;
        const workingDays = JSON.parse(selected.dataset.workingDays || '[1,2,3,4,5]');
        const startTime = selected.dataset.startTime || '09:00';
        const endTime = selected.dataset.endTime || '17:00';

        // Update duration dropdown
        const durationSelect = document.getElementById('duration');
        const durationLabels = {15: '15 minutes', 30: '30 minutes', 45: '45 minutes', 60: '1 hour'};
        durationSelect.innerHTML = '';
        durations.forEach(function(dur) {
            const option = document.createElement('option');
            option.value = dur;
            option.textContent = durationLabels[dur] || dur + ' minutes';
            if (dur == defaultDuration) option.selected = true;
            durationSelect.appendChild(option);
        });

        // Update availability text
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const workingDayNames = workingDays.map(d => dayNames[d]);
        const formatTime = (t) => {
            const [h, m] = t.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return hour12 + ':' + m + ' ' + ampm;
        };
        document.getElementById('availability-text').textContent =
            workingDayNames.join(', ') + ' • ' + formatTime(startTime) + ' - ' + formatTime(endTime);

        // Clear any existing slot selections if instructor changes
        selectedSlots = {};
        currentDateSlots = [];
        currentDate = null;
        updateSelectedSlotsDisplay();
        updateHiddenInput();
    });
}

// Duration change handler - refetch slots if viewing
document.getElementById('duration').addEventListener('change', function() {
    if (currentDate) {
        fetchTimeSlots(currentDate);
    }
});

// Form submission
document.getElementById('invite-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('submit-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success and redirect
            if (window.notyf) {
                window.notyf.success(data.message || 'Invite sent successfully!');
            }
            setTimeout(() => {
                window.location.href = '{{ route("one-on-one.index", ["status" => "my-invites"]) }}';
            }, 1000);
        } else {
            if (window.notyf) {
                window.notyf.error(data.message || 'Failed to send invite');
            }
            btn.disabled = false;
            spinner.classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.notyf) {
            window.notyf.error('An error occurred while sending invite');
        }
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
});
</script>
@endpush
@endsection
