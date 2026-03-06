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
        color: oklch(var(--bc) / 0.2) !important;
    }
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay {
        color: oklch(var(--bc) / 0.25) !important;
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
    $firstDuration = $allowedDurations[0] ?? 30;
    $firstMeetingType = $meetingTypes[0] ?? 'video';
@endphp

<div class="min-h-screen bg-gradient-to-br from-base-200/50 to-base-100 flex items-center justify-center px-4 py-8">

    {{-- Step 1: Date & Time Selection --}}
    <div id="step-calendar" class="w-[40%]">
        <div class="bg-base-100 rounded-2xl shadow-xl overflow-hidden border border-base-200/50">

            {{-- Back Button --}}
            <div class="px-6 pt-5">
                <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-base-content/50 hover:text-primary transition-colors group">
                    <span class="icon-[tabler--arrow-left] size-4 group-hover:-translate-x-0.5 transition-transform"></span>
                    Back
                </a>
            </div>

            {{-- 3 Column Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-base-200">

                {{-- Column 1: Instructor Info --}}
                <div class="p-6">
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
                            @foreach($allowedDurations as $index => $duration)
                                <button type="button"
                                        class="px-3 py-1.5 text-sm rounded-lg border-2 transition-all duration-btn font-medium {{ $index === 0 ? 'bg-primary text-primary-content border-primary' : 'border-base-300 hover:border-primary text-base-content/70' }}"
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

                {{-- Column 2: Calendar --}}
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-base-content/50 uppercase tracking-wider mb-4">Select Date</h3>
                    <div id="calendar-inline"></div>
                </div>

                {{-- Column 3: Time Slots --}}
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
        </div>

        {{-- Powered By --}}
        <p class="text-center text-xs text-base-content/30 mt-6">
            Powered by <span class="font-medium">{{ $host->studio_name }}</span>
        </p>
    </div>

    {{-- Step 2: Contact Form --}}
    <div id="step-form" class="hidden w-full max-w-xl">
        <div class="bg-base-100 rounded-2xl shadow-xl border border-base-200/50 p-6 lg:p-8">
            {{-- Back Button --}}
            <button type="button" id="back-to-calendar" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-primary transition-colors mb-5 group">
                <span class="icon-[tabler--arrow-left] size-4 group-hover:-translate-x-0.5 transition-transform"></span>
                Back to calendar
            </button>

            {{-- Booking Summary --}}
            <div class="bg-base-200/50 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-4">
                    @if($instructor->photo_url)
                        <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                             class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center flex-shrink-0">
                            <span class="text-base font-bold text-primary-content">{{ $initials }}</span>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="font-semibold text-base-content">{{ $profile->display_name ?? $instructor->name }}</h3>
                        <p class="text-sm text-base-content/60" id="summary-datetime"></p>
                        <p class="text-xs text-base-content/40" id="summary-details"></p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-base-content">Your Details</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-base-content/60 mb-1.5 block" for="first_name">First Name <span class="text-error">*</span></label>
                        <input type="text" id="first_name" class="input input-bordered w-full" placeholder="John" required>
                    </div>
                    <div>
                        <label class="text-sm text-base-content/60 mb-1.5 block" for="last_name">Last Name <span class="text-error">*</span></label>
                        <input type="text" id="last_name" class="input input-bordered w-full" placeholder="Doe" required>
                    </div>
                </div>

                <div>
                    <label class="text-sm text-base-content/60 mb-1.5 block" for="email">Email <span class="text-error">*</span></label>
                    <input type="email" id="email" class="input input-bordered w-full" placeholder="john@example.com" required>
                </div>

                <div>
                    <label class="text-sm text-base-content/60 mb-1.5 block" for="phone">Phone <span class="text-base-content/30">(optional)</span></label>
                    <input type="tel" id="phone" class="input input-bordered w-full" placeholder="+1 (555) 000-0000">
                </div>

                <div>
                    <label class="text-sm text-base-content/60 mb-1.5 block" for="notes">Notes <span class="text-base-content/30">(optional)</span></label>
                    <textarea id="notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Any additional information..."></textarea>
                </div>

                <div id="booking-error" class="alert alert-error hidden">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span id="error-message"></span>
                </div>

                <button type="button" id="submit-booking" class="btn btn-primary w-full h-12 text-base font-semibold mt-2">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    <span class="btn-text">Schedule Meeting</span>
                </button>
            </div>
        </div>

        {{-- Powered By --}}
        <p class="text-center text-xs text-base-content/30 mt-6">
            Powered by <span class="font-medium">{{ $host->studio_name }}</span>
        </p>
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

    let selectedDuration = {{ $firstDuration }};
    let selectedMeetingType = '{{ $firstMeetingType }}';
    let selectedDate = null;
    let selectedTime = null;
    let selectedTimeDisplay = '';

    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    document.getElementById('user-timezone').textContent = timezone;

    // Initialize Flatpickr
    const calendar = flatpickr('#calendar-inline', {
        inline: true,
        dateFormat: 'Y-m-d',
        minDate: minDateStr,
        maxDate: maxDateStr,
        disable: [
            function(date) {
                return !workingDays.includes(date.getDay());
            }
        ],
        onChange: function(selectedDates, dateStr) {
            selectedDate = dateStr;
            selectedTime = null;
            fetchTimeSlots();
        }
    });

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
            html += '<button type="button" class="time-slot-btn py-2.5 px-2 text-sm font-medium bg-primary/5 text-primary border border-primary/20 rounded-lg hover:bg-primary hover:text-primary-content hover:border-primary text-center" data-time="' + slot.time + '" data-display="' + slot.display + '">' + slot.display + '</button>';
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
            activeTab = 'morning';

            const dateObj = new Date(selectedDate + 'T12:00:00');
            const dateDisplay = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });

            // Count slots by period
            const morningCount = allSlots.filter(s => categorizeSlot(s.time) === 'morning').length;
            const afternoonCount = allSlots.filter(s => categorizeSlot(s.time) === 'afternoon').length;
            const eveningCount = allSlots.filter(s => categorizeSlot(s.time) === 'evening').length;

            let html = '<p class="font-semibold text-base-content mb-1">' + dateDisplay + '</p>';
            html += '<p class="text-sm text-base-content/50 mb-4">' + data.slots.length + ' available</p>';

            // Tabs
            html += '<div class="flex gap-1 mb-4 border-b border-base-200 pb-2">';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg bg-primary text-primary-content' + (morningCount === 0 ? ' opacity-40' : '') + '" data-tab="morning">Morning' + (morningCount > 0 ? ' (' + morningCount + ')' : '') + '</button>';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg text-base-content/60 hover:bg-base-200' + (afternoonCount === 0 ? ' opacity-40' : '') + '" data-tab="afternoon">Afternoon' + (afternoonCount > 0 ? ' (' + afternoonCount + ')' : '') + '</button>';
            html += '<button type="button" class="slot-tab px-3 py-1.5 text-xs font-medium rounded-lg text-base-content/60 hover:bg-base-200' + (eveningCount === 0 ? ' opacity-40' : '') + '" data-tab="evening">Evening' + (eveningCount > 0 ? ' (' + eveningCount + ')' : '') + '</button>';
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

        document.getElementById('step-calendar').classList.add('hidden');
        document.getElementById('step-form').classList.remove('hidden');
    }

    document.getElementById('back-to-calendar').addEventListener('click', function() {
        document.getElementById('step-form').classList.add('hidden');
        document.getElementById('step-calendar').classList.remove('hidden');
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
