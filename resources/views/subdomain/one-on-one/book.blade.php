@extends('layouts.subdomain')

@section('title', 'Book with ' . $instructor->name . ' — ' . $host->studio_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    /* Flatpickr Custom Theme */
    .flatpickr-calendar {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        width: 100% !important;
        background: transparent !important;
    }
    .flatpickr-calendar.inline {
        top: 0 !important;
    }
    .flatpickr-months {
        padding: 0 0 0.75rem 0;
        border-bottom: 1px solid oklch(var(--bc) / 0.08);
        margin-bottom: 0.5rem;
    }
    .flatpickr-month {
        height: auto !important;
        background: transparent !important;
    }
    .flatpickr-current-month {
        font-size: 1.1rem !important;
        font-weight: 600;
        padding: 0;
        color: oklch(var(--bc)) !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: transparent !important;
        font-weight: 600;
    }
    .flatpickr-prev-month, .flatpickr-next-month {
        padding: 0.5rem !important;
        fill: oklch(var(--bc) / 0.6) !important;
        transition: all 0.15s ease;
    }
    .flatpickr-prev-month:hover, .flatpickr-next-month:hover {
        background: oklch(var(--p) / 0.1) !important;
        border-radius: 0.5rem;
    }
    .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg {
        fill: oklch(var(--p)) !important;
    }
    .flatpickr-weekdays {
        background: transparent !important;
        margin-bottom: 0.25rem;
    }
    .flatpickr-weekday {
        color: oklch(var(--bc) / 0.4) !important;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .flatpickr-days {
        width: 100% !important;
    }
    .dayContainer {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
    .flatpickr-day {
        max-width: none !important;
        height: 42px !important;
        line-height: 42px !important;
        border-radius: 50% !important;
        font-weight: 500;
        font-size: 0.9rem;
        color: oklch(var(--bc));
        border: none !important;
        transition: all 0.15s ease;
    }
    .flatpickr-day:hover:not(.flatpickr-disabled):not(.selected) {
        background: oklch(var(--p) / 0.1) !important;
        color: oklch(var(--p)) !important;
    }
    .flatpickr-day.selected {
        background: oklch(var(--p)) !important;
        color: oklch(var(--pc)) !important;
        font-weight: 600;
    }
    .flatpickr-day.today:not(.selected) {
        background: oklch(var(--bc) / 0.05) !important;
        font-weight: 700;
    }
    .flatpickr-day.flatpickr-disabled {
        color: oklch(var(--bc) / 0.15) !important;
        background: oklch(var(--bc) / 0.03) !important;
        text-decoration: line-through;
        cursor: not-allowed !important;
    }
    .flatpickr-day.flatpickr-disabled:hover {
        background: oklch(var(--bc) / 0.03) !important;
        color: oklch(var(--bc) / 0.15) !important;
    }
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay {
        color: oklch(var(--bc) / 0.25) !important;
    }
    /* Suggested dates from invite */
    .flatpickr-day.suggested-date:not(.flatpickr-disabled) {
        background: oklch(var(--p) / 0.15) !important;
        border: 2px solid oklch(var(--p) / 0.4) !important;
        font-weight: 600;
    }
    .flatpickr-day.suggested-date:not(.flatpickr-disabled):hover {
        background: oklch(var(--p) / 0.25) !important;
    }
    .flatpickr-day.suggested-date.selected {
        background: oklch(var(--p)) !important;
        border-color: oklch(var(--p)) !important;
    }

    /* Time slot button styles */
    .time-slot-btn {
        transition: all 0.15s ease;
    }
    .time-slot-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px oklch(var(--p) / 0.2);
    }
</style>
@endpush

@section('content')
@php
    $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
    $workingDaysJson = json_encode($profile->working_days ?? [1,2,3,4,5]);
    $meetingTypeLabelsJson = json_encode($meetingTypeLabels);
    // Use preselected duration if available, otherwise use first allowed duration
    $firstDuration = $preselectedDuration ?? $allowedDurations[0] ?? 30;
    $firstMeetingType = $meetingTypes[0] ?? 'video';

    // Check if we have multi-slot invite
    $hasMultipleSlots = !empty($preselectedSlots);

    // Format preselected datetime for display (legacy single slot)
    $preselectedDateFormatted = '';
    $preselectedTimeFormatted = '';
    if (($isSpecificTimeInvite ?? false) && !$hasMultipleSlots && ($preselectedDate ?? null) && ($preselectedTime ?? null)) {
        $preselectedDateFormatted = \Carbon\Carbon::parse($preselectedDate)->format('l, F j, Y');
        $preselectedTimeFormatted = \Carbon\Carbon::parse($preselectedTime)->format('g:i A');
    }

    // Format multi-slot dates for display
    $formattedMultiSlots = [];
    if ($hasMultipleSlots) {
        foreach ($preselectedSlots as $date => $times) {
            $dateObj = \Carbon\Carbon::parse($date);
            $formattedTimes = [];
            foreach ($times as $time) {
                $timeObj = \Carbon\Carbon::parse($time);
                $formattedTimes[] = [
                    'time' => $time,
                    'display' => $timeObj->format('g:i A'),
                ];
            }
            $formattedMultiSlots[] = [
                'date' => $date,
                'date_display' => $dateObj->format('l, M j'),
                'date_full' => $dateObj->format('l, F j, Y'),
                'times' => $formattedTimes,
            ];
        }
    }
@endphp

<div class="min-h-screen bg-gradient-to-br from-base-200/50 to-base-100 flex items-center justify-center px-4 py-8">

    {{-- Step 1: Date & Time Selection --}}
    <div id="step-calendar" class="w-[40%]">
        <div class="bg-base-100 rounded-2xl shadow-xl overflow-hidden border border-base-200/50">

            {{-- Back Button --}}
            <div class="px-6 pt-8">
                <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-base-content/50 hover:text-primary transition-colors group">
                    <span class="icon-[tabler--arrow-left] size-4 group-hover:-translate-x-0.5 transition-transform"></span>
                    Back
                </a>
            </div>

            {{-- 3 Column Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-3">

                {{-- Column 1: Instructor Info --}}
                <div class="p-6 border-b lg:border-b-0 lg:border-r border-base-200">
                    {{-- Instructor Avatar & Name --}}
                    <div class="flex items-center gap-4 mb-4">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                 class="w-14 h-14 rounded-full object-cover ring-2 ring-base-100 shadow">
                        @else
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center ring-2 ring-base-100 shadow">
                                <span class="text-lg font-bold text-primary-content">{{ $initials }}</span>
                            </div>
                        @endif
                        <div>
                            <h2 class="font-bold text-base-content">{{ $profile->display_name ?? $instructor->name }}</h2>
                            @if($profile->title ?? $instructor->title)
                                <p class="text-sm text-base-content/50">{{ $profile->title ?? $instructor->title }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Meeting Title --}}
                    <h1 class="text-lg font-bold text-base-content mb-4">1:1 Meeting</h1>

                    {{-- Meeting Info --}}
                    <div class="space-y-2 text-sm text-base-content/70 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--clock] size-4 text-base-content/40"></span>
                            <span id="display-duration">{{ $firstDuration }} min</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($firstMeetingType === 'video')
                                <span class="icon-[tabler--video] size-4 text-base-content/40"></span>
                            @elseif($firstMeetingType === 'phone')
                                <span class="icon-[tabler--phone] size-4 text-base-content/40"></span>
                            @else
                                <span class="icon-[tabler--map-pin] size-4 text-base-content/40"></span>
                            @endif
                            <span id="display-type">{{ $meetingTypeLabels[$firstMeetingType] ?? 'Meeting' }}</span>
                        </div>
                    </div>

                    {{-- Duration Picker --}}
                    @if(count($allowedDurations) > 1)
                    <div class="mb-4">
                        <label class="text-xs font-semibold text-base-content/40 uppercase tracking-wider mb-2 block">Duration</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allowedDurations as $duration)
                                <button type="button"
                                        class="px-3 py-1.5 text-sm rounded-lg border-2 transition-all duration-btn font-medium {{ $duration == $firstDuration ? 'bg-primary text-primary-content border-primary' : 'border-base-300 hover:border-primary text-base-content/70' }}"
                                        data-duration="{{ $duration }}">
                                    {{ $duration }}m
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Type Picker --}}
                    @if(count($meetingTypes) > 1)
                    <div class="mb-4">
                        <label class="text-xs font-semibold text-base-content/40 uppercase tracking-wider mb-2 block">Type</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($meetingTypes as $index => $type)
                                <button type="button"
                                        class="px-3 py-1.5 text-sm rounded-lg border-2 transition-all meeting-type-btn flex items-center gap-1.5 font-medium {{ $index === 0 ? 'bg-primary text-primary-content border-primary' : 'border-base-300 hover:border-primary text-base-content/70' }}"
                                        data-type="{{ $type }}">
                                    @if($type === 'in_person')
                                        <span class="icon-[tabler--map-pin] size-4"></span> In Person
                                    @elseif($type === 'phone')
                                        <span class="icon-[tabler--phone] size-4"></span> Phone
                                    @elseif($type === 'video')
                                        <span class="icon-[tabler--video] size-4"></span> Video
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Timezone --}}
                    <p class="text-xs text-base-content/40 flex items-center gap-1.5 mt-5">
                        <span class="icon-[tabler--world] size-3.5"></span>
                        <span id="user-timezone"></span>
                    </p>
                </div>

                {{-- Columns 2 & 3: Calendar & Slots (hidden when specific time invite) --}}
                <div id="calendar-slots-section" class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-base-200 {{ ($isSpecificTimeInvite ?? false) ? 'hidden' : '' }}">
                    {{-- Calendar --}}
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-base-content/50 uppercase tracking-wider mb-4">Select Date</h3>
                        <div id="calendar-inline"></div>
                    </div>

                    {{-- Time Slots --}}
                    <div class="p-6">
                        <div id="time-slots-container" class="min-h-[320px]">
                            <div class="h-full flex flex-col items-center justify-center text-base-content/30 py-8">
                                <span class="icon-[tabler--calendar-event] size-12 mb-3"></span>
                                <p class="font-medium text-base-content/50 text-sm">Select a date</p>
                                <p class="text-xs mt-1">to view available times</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Multi-slot Selection (shown for multi-slot invites) --}}
                @if($hasMultipleSlots ?? false)
                <div id="multi-slot-section" class="lg:col-span-2 p-6">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-base-content mb-1">Suggested Times</h3>
                        <p class="text-sm text-base-content/50">Select a time that works for you</p>
                    </div>

                    <div class="space-y-4">
                        @foreach($formattedMultiSlots as $slotGroup)
                        <div class="border border-base-200 rounded-xl p-4">
                            <p class="font-medium text-sm text-base-content mb-3 flex items-center gap-2">
                                <span class="icon-[tabler--calendar] size-4 text-primary"></span>
                                {{ $slotGroup['date_display'] }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($slotGroup['times'] as $timeSlot)
                                <button type="button"
                                        class="suggested-slot-btn time-slot-btn py-2.5 px-4 text-sm font-medium bg-primary/5 text-primary border border-primary/20 rounded-lg hover:bg-primary hover:text-primary-content hover:border-primary text-center transition-all"
                                        data-date="{{ $slotGroup['date'] }}"
                                        data-date-display="{{ $slotGroup['date_full'] }}"
                                        data-time="{{ $timeSlot['time'] }}"
                                        data-time-display="{{ $timeSlot['display'] }}">
                                    {{ $timeSlot['display'] }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-base-200 flex items-center justify-between">
                        <p class="text-xs text-base-content/40">
                            <span class="icon-[tabler--info-circle] size-3.5 inline-block mr-1"></span>
                            None of these work?
                        </p>
                        <button type="button" id="view-all-times" class="text-sm text-primary font-medium hover:underline flex items-center gap-1">
                            View all available times
                            <span class="icon-[tabler--arrow-right] size-4"></span>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Form Section (replaces calendar & slots, shown by default for specific time invite) --}}
                <div id="form-section" class="{{ ($isSpecificTimeInvite ?? false) ? '' : 'hidden' }} lg:col-span-2 p-6">
                    {{-- Selected Time Summary --}}
                    <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--calendar-check] size-5 text-primary"></span>
                            <div>
                                <p class="font-medium text-base-content text-sm" id="summary-datetime"></p>
                                <p class="text-xs text-base-content/50" id="summary-details"></p>
                            </div>
                        </div>
                        <button type="button" id="change-time" class="text-xs text-primary hover:underline font-medium">Change</button>
                    </div>

                    {{-- Form --}}
                    <h3 class="text-base font-semibold text-base-content mb-4">Your Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            // Split preselected name into first and last name
                            $nameParts = $preselectedName ? explode(' ', $preselectedName, 2) : ['', ''];
                            $preFirstName = $nameParts[0] ?? '';
                            $preLastName = $nameParts[1] ?? '';
                        @endphp
                        <div>
                            <label class="text-sm text-base-content/60 mb-1.5 block" for="first_name">First Name <span class="text-error">*</span></label>
                            <input type="text" id="first_name" class="input input-bordered w-full" placeholder="John" value="{{ $preFirstName }}" required>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60 mb-1.5 block" for="last_name">Last Name <span class="text-error">*</span></label>
                            <input type="text" id="last_name" class="input input-bordered w-full" placeholder="Doe" value="{{ $preLastName }}" required>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60 mb-1.5 block" for="email">Email <span class="text-error">*</span></label>
                            <input type="email" id="email" class="input input-bordered w-full" placeholder="john@example.com" value="{{ $preselectedEmail ?? '' }}" required>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60 mb-1.5 block" for="phone">Phone <span class="text-base-content/30">(optional)</span></label>
                            <input type="tel" id="phone" class="input input-bordered w-full" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-base-content/60 mb-1.5 block" for="notes">Notes <span class="text-base-content/30">(optional)</span></label>
                            <textarea id="notes" class="textarea textarea-bordered w-full" rows="2" placeholder="Any additional information..."></textarea>
                        </div>
                    </div>

                    <div id="booking-error" class="alert alert-error hidden mt-4">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span id="error-message"></span>
                    </div>

                    <button type="button" id="submit-booking" class="btn btn-primary w-full h-12 text-base font-semibold mt-4">
                        <span class="loading loading-spinner loading-sm hidden"></span>
                        <span class="btn-text">Schedule Meeting</span>
                    </button>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availabilityUrl = '{{ route("subdomain.instructor.availability", ["subdomain" => $host->subdomain, "instructor" => $instructor->id]) }}';
    const bookUrl = '{{ route("subdomain.instructor.book", ["subdomain" => $host->subdomain, "instructor" => $instructor->id]) }}';
    const csrfToken = '{{ csrf_token() }}';
    const minDateStr = '{{ $minDate }}';
    const maxDateStr = '{{ $maxDate }}';
    const workingDays = {!! $workingDaysJson !!};
    const meetingTypeLabels = {!! $meetingTypeLabelsJson !!};

    // Preselected values from invite link
    const preselectedDate = '{{ $preselectedDate ?? '' }}';
    const preselectedTime = '{{ $preselectedTime ?? '' }}';
    const isSpecificTimeInvite = {{ ($isSpecificTimeInvite ?? false) ? 'true' : 'false' }};
    const hasMultipleSlots = {{ ($hasMultipleSlots ?? false) ? 'true' : 'false' }};
    const suggestedDates = {!! json_encode($hasMultipleSlots ? array_keys($preselectedSlots) : []) !!};

    let selectedDuration = {{ $firstDuration }};
    let selectedMeetingType = '{{ $firstMeetingType }}';
    let selectedDate = preselectedDate || null;
    let selectedTime = isSpecificTimeInvite ? preselectedTime : null;
    let selectedTimeDisplay = '';

    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    document.getElementById('user-timezone').textContent = timezone;

    // Initialize Flatpickr
    const calendar = flatpickr('#calendar-inline', {
        inline: true,
        dateFormat: 'Y-m-d',
        minDate: minDateStr,
        maxDate: maxDateStr,
        defaultDate: preselectedDate || null,
        disable: [
            function(date) {
                // If we have suggested dates from invite, only allow those dates
                if (suggestedDates.length > 0) {
                    const dateStr = date.toISOString().split('T')[0];
                    return !suggestedDates.includes(dateStr);
                }
                // Otherwise, just disable non-working days
                return !workingDays.includes(date.getDay());
            }
        ],
        onChange: function(selectedDates, dateStr) {
            selectedDate = dateStr;
            selectedTime = null;
            fetchTimeSlots();
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            // Mark suggested dates from invite
            if (suggestedDates.length > 0) {
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (suggestedDates.includes(dateStr)) {
                    dayElem.classList.add('suggested-date');
                    dayElem.title = 'Suggested time available';
                }
            }
        }
    });

    // If this is a specific time invite (single slot, not multi-slot), set up the summary immediately
    if (isSpecificTimeInvite && !hasMultipleSlots && preselectedDate && preselectedTime) {
        selectedDate = preselectedDate;
        selectedTime = preselectedTime;
        // Convert 24h time to display format
        const [hours, minutes] = preselectedTime.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        selectedTimeDisplay = hour12 + ':' + minutes + ' ' + ampm;

        // Set up the summary
        const dateObj = new Date(preselectedDate + 'T12:00:00');
        const dateDisplay = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        document.getElementById('summary-datetime').textContent = dateDisplay + ' at ' + selectedTimeDisplay;
        document.getElementById('summary-details').textContent = selectedDuration + ' min · ' + (meetingTypeLabels[selectedMeetingType] || selectedMeetingType);
    } else if (preselectedDate && !hasMultipleSlots) {
        // If just date is preselected (not specific time), fetch time slots
        fetchTimeSlots();
    }

    // Multi-slot invite: Handle suggested slot button clicks
    document.querySelectorAll('.suggested-slot-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            selectedDate = this.dataset.date;
            selectedTime = this.dataset.time;
            selectedTimeDisplay = this.dataset.timeDisplay;

            // Show form with selected slot summary
            const dateDisplay = this.dataset.dateDisplay;
            document.getElementById('summary-datetime').textContent = dateDisplay + ' at ' + selectedTimeDisplay;
            document.getElementById('summary-details').textContent = selectedDuration + ' min · ' + (meetingTypeLabels[selectedMeetingType] || selectedMeetingType);

            // Hide multi-slot section, show form
            const multiSlotSection = document.getElementById('multi-slot-section');
            if (multiSlotSection) {
                multiSlotSection.classList.add('hidden');
            }
            document.getElementById('form-section').classList.remove('hidden');
        });
    });

    // Multi-slot invite: "View all times" button
    const viewAllTimesBtn = document.getElementById('view-all-times');
    if (viewAllTimesBtn) {
        viewAllTimesBtn.addEventListener('click', function() {
            // Hide multi-slot section, show calendar
            document.getElementById('multi-slot-section').classList.add('hidden');
            document.getElementById('calendar-slots-section').classList.remove('hidden');
        });
    }

    // Duration buttons
    document.querySelectorAll('.duration-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.duration-btn').forEach(function(b) {
                b.classList.remove('bg-primary', 'text-primary-content', 'border-primary');
                b.classList.add('border-base-300', 'text-base-content/70');
            });
            this.classList.add('bg-primary', 'text-primary-content', 'border-primary');
            this.classList.remove('border-base-300', 'text-base-content/70');
            selectedDuration = parseInt(this.dataset.duration);
            document.getElementById('display-duration').textContent = selectedDuration + ' min';
            if (selectedDate) fetchTimeSlots();
        });
    });

    // Meeting type buttons
    document.querySelectorAll('.meeting-type-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.meeting-type-btn').forEach(function(b) {
                b.classList.remove('bg-primary', 'text-primary-content', 'border-primary');
                b.classList.add('border-base-300', 'text-base-content/70');
            });
            this.classList.add('bg-primary', 'text-primary-content', 'border-primary');
            this.classList.remove('border-base-300', 'text-base-content/70');
            selectedMeetingType = this.dataset.type;
            document.getElementById('display-type').textContent = meetingTypeLabels[selectedMeetingType] || selectedMeetingType;
        });
    });

    let allSlots = [];
    let activeTab = 'all';

    function categorizeSlot(timeStr) {
        const hour = parseInt(timeStr.split(':')[0]);
        if (hour < 12) return 'morning';
        if (hour < 17) return 'afternoon';
        return 'evening';
    }

    function renderSlots() {
        const container = document.getElementById('slots-grid');
        if (!container) return;

        let filteredSlots = allSlots;
        if (activeTab !== 'all') {
            filteredSlots = allSlots.filter(function(slot) {
                return categorizeSlot(slot.time) === activeTab;
            });
        }

        if (filteredSlots.length === 0) {
            container.innerHTML = '<div class="col-span-3 text-center py-6 text-base-content/40 text-sm">No times in this period</div>';
            return;
        }

        let html = '';
        filteredSlots.forEach(function(slot) {
            // Check if this slot matches the preselected time
            const isPreselected = preselectedTime && slot.time === preselectedTime;
            const btnClass = isPreselected
                ? 'time-slot-btn py-2.5 px-2 text-sm font-medium bg-primary text-primary-content border-2 border-primary rounded-lg text-center ring-2 ring-primary/30'
                : 'time-slot-btn py-2.5 px-2 text-sm font-medium bg-primary/5 text-primary border border-primary/20 rounded-lg hover:bg-primary hover:text-primary-content hover:border-primary text-center';
            html += '<button type="button" class="' + btnClass + '" data-time="' + slot.time + '" data-display="' + slot.display + '">' + slot.display + (isPreselected ? ' <span class="icon-[tabler--check] size-3 ml-1"></span>' : '') + '</button>';
        });
        container.innerHTML = html;

        document.querySelectorAll('.time-slot-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                selectedTime = this.dataset.time;
                selectedTimeDisplay = this.dataset.display;
                showForm();
            });
        });
    }

    function fetchTimeSlots() {
        const container = document.getElementById('time-slots-container');
        container.innerHTML = '<div class="h-full flex items-center justify-center py-12"><span class="loading loading-spinner loading-lg text-primary"></span></div>';

        fetch(availabilityUrl + '?date=' + selectedDate + '&duration=' + selectedDuration, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success || !data.slots || data.slots.length === 0) {
                container.innerHTML = '<div class="h-full flex flex-col items-center justify-center text-base-content/30 py-8"><span class="icon-[tabler--calendar-x] size-12 mb-3"></span><p class="font-semibold text-base-content/50">No times available</p><p class="text-sm mt-1">Try another date</p></div>';
                return;
            }

            allSlots = data.slots;

            // If we have a preselected time, switch to the correct tab
            if (preselectedTime) {
                activeTab = categorizeSlot(preselectedTime);
            } else {
                activeTab = 'morning';
            }

            const dateObj = new Date(selectedDate + 'T12:00:00');
            const dateDisplay = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });

            // Count slots by period
            const morningCount = allSlots.filter(s => categorizeSlot(s.time) === 'morning').length;
            const afternoonCount = allSlots.filter(s => categorizeSlot(s.time) === 'afternoon').length;
            const eveningCount = allSlots.filter(s => categorizeSlot(s.time) === 'evening').length;

            let html = '<p class="font-semibold text-base-content mb-1">' + dateDisplay + '</p>';
            html += '<p class="text-sm text-base-content/50 mb-4">' + data.slots.length + ' available</p>';

            // Tabs - highlight the active tab
            html += '<div class="flex gap-1 mb-4 border-b border-base-200 pb-2">';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg ' + (activeTab === 'morning' ? 'bg-primary text-primary-content' : 'text-base-content/60 hover:bg-base-200') + (morningCount === 0 ? ' opacity-40' : '') + '" data-tab="morning">Morning' + (morningCount > 0 ? ' (' + morningCount + ')' : '') + '</button>';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg ' + (activeTab === 'afternoon' ? 'bg-primary text-primary-content' : 'text-base-content/60 hover:bg-base-200') + (afternoonCount === 0 ? ' opacity-40' : '') + '" data-tab="afternoon">Afternoon' + (afternoonCount > 0 ? ' (' + afternoonCount + ')' : '') + '</button>';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg ' + (activeTab === 'evening' ? 'bg-primary text-primary-content' : 'text-base-content/60 hover:bg-base-200') + (eveningCount === 0 ? ' opacity-40' : '') + '" data-tab="evening">Evening' + (eveningCount > 0 ? ' (' + eveningCount + ')' : '') + '</button>';
            html += '</div>';

            html += '<div id="slots-grid" class="grid grid-cols-3 gap-2 max-h-[200px] overflow-y-auto pr-1"></div>';

            container.innerHTML = html;

            // Tab click handlers
            document.querySelectorAll('.slot-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.slot-tab').forEach(function(t) {
                        t.classList.remove('bg-primary', 'text-primary-content');
                        t.classList.add('text-base-content/60');
                    });
                    this.classList.add('bg-primary', 'text-primary-content');
                    this.classList.remove('text-base-content/60');
                    activeTab = this.dataset.tab;
                    renderSlots();
                });
            });

            renderSlots();
        })
        .catch(function(err) {
            console.error(err);
            container.innerHTML = '<div class="h-full flex flex-col items-center justify-center text-error/60 py-8"><span class="icon-[tabler--alert-triangle] size-12 mb-3"></span><p class="font-semibold">Error loading times</p><p class="text-sm mt-1">Please try again</p></div>';
        });
    }

    function showForm() {
        const dateObj = new Date(selectedDate + 'T12:00:00');
        const dateDisplay = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

        document.getElementById('summary-datetime').textContent = dateDisplay + ' at ' + selectedTimeDisplay;
        document.getElementById('summary-details').textContent = selectedDuration + ' min · ' + (meetingTypeLabels[selectedMeetingType] || selectedMeetingType);

        document.getElementById('calendar-slots-section').classList.add('hidden');
        document.getElementById('form-section').classList.remove('hidden');
    }

    document.getElementById('change-time').addEventListener('click', function() {
        document.getElementById('form-section').classList.add('hidden');
        // For multi-slot invites, show the multi-slot section first
        const multiSlotSection = document.getElementById('multi-slot-section');
        if (multiSlotSection && hasMultipleSlots) {
            multiSlotSection.classList.remove('hidden');
        } else {
            document.getElementById('calendar-slots-section').classList.remove('hidden');
        }
    });

    document.getElementById('submit-booking').addEventListener('click', function() {
        const btn = this;
        const spinner = btn.querySelector('.loading');
        const text = btn.querySelector('.btn-text');

        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!firstName || !lastName || !email) {
            document.getElementById('booking-error').classList.remove('hidden');
            document.getElementById('error-message').textContent = 'Please fill in all required fields.';
            return;
        }

        btn.disabled = true;
        spinner.classList.remove('hidden');
        text.textContent = 'Scheduling...';
        document.getElementById('booking-error').classList.add('hidden');

        fetch(bookUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                date: selectedDate,
                time: selectedTime,
                duration: selectedDuration,
                meeting_type: selectedMeetingType,
                first_name: firstName,
                last_name: lastName,
                email: email,
                phone: document.getElementById('phone').value.trim(),
                notes: document.getElementById('notes').value.trim(),
                timezone: timezone
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = data.confirmation_url;
            } else {
                document.getElementById('booking-error').classList.remove('hidden');
                document.getElementById('error-message').textContent = data.message || 'Booking failed. Please try again.';
                btn.disabled = false;
                spinner.classList.add('hidden');
                text.textContent = 'Schedule Meeting';
            }
        })
        .catch(function(err) {
            console.error(err);
            document.getElementById('booking-error').classList.remove('hidden');
            document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
            btn.disabled = false;
            spinner.classList.add('hidden');
            text.textContent = 'Schedule Meeting';
        });
    });
});
</script>
@endpush
