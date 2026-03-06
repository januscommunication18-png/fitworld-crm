@extends('layouts.subdomain')

@section('title', 'Book 1:1 Meeting with ' . $instructor->name . ' — ' . $host->studio_name)

@section('content')
@php
    $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
@endphp

@include('subdomain.partials.navbar')

{{-- Main Content --}}
<div class="min-h-screen bg-gradient-to-br from-base-200/50 to-base-100">
    <div class="container-fixed py-8">
        <div class="max-w-5xl mx-auto">

            {{-- Back Link --}}
            <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
               class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-primary transition-colors mb-6">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to {{ $instructor->name }}'s Profile
            </a>

            {{-- Header Card --}}
            <div class="card bg-gradient-to-r from-primary to-primary/80 text-primary-content mb-6 overflow-hidden">
                <div class="card-body p-6 md:p-8">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        {{-- Instructor Photo --}}
                        <div class="shrink-0">
                            @if($instructor->photo_url)
                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                     class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg">
                            @else
                                <div class="w-24 h-24 rounded-2xl bg-white/20 flex items-center justify-center ring-4 ring-white/20 shadow-lg">
                                    <span class="text-3xl font-bold">{{ $initials }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="text-center md:text-left flex-1">
                            <h1 class="text-2xl md:text-3xl font-bold">Book a Meeting</h1>
                            <p class="text-primary-content/80 mt-1">with {{ $profile->display_name }}</p>
                            @if($profile->title_display)
                                <p class="text-sm text-primary-content/60 mt-1">{{ $profile->title_display }}</p>
                            @endif
                        </div>

                        {{-- Quick Info --}}
                        <div class="flex flex-wrap justify-center md:justify-end gap-3">
                            @foreach($meetingTypes as $type)
                                <div class="flex items-center gap-1.5 bg-white/10 rounded-full px-3 py-1.5 text-sm">
                                    @if($type === 'in_person')
                                        <span class="icon-[tabler--map-pin] size-4"></span>
                                    @elseif($type === 'phone')
                                        <span class="icon-[tabler--phone] size-4"></span>
                                    @elseif($type === 'video')
                                        <span class="icon-[tabler--video] size-4"></span>
                                    @endif
                                    <span>{{ $meetingTypeLabels[$type] ?? ucfirst($type) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Form --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Left: Steps --}}
                <div class="lg:col-span-3 space-y-4">

                    {{-- Step 1: Duration & Type --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm">1</div>
                                <h3 class="font-semibold text-lg">Choose Duration & Type</h3>
                            </div>

                            {{-- Duration --}}
                            <div class="mb-5">
                                <label class="text-sm font-medium text-base-content/70 mb-2 block">Meeting Duration</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="duration-options">
                                    @foreach($allowedDurations as $index => $duration)
                                        <button type="button"
                                                class="btn {{ $index === 0 ? 'btn-primary' : 'btn-outline' }} duration-btn h-auto py-3 flex-col gap-0.5"
                                                data-duration="{{ $duration }}">
                                            <span class="text-lg font-bold">{{ $duration }}</span>
                                            <span class="text-xs opacity-70">minutes</span>
                                        </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="duration" id="selected-duration" value="{{ $allowedDurations[0] ?? 30 }}">
                            </div>

                            {{-- Meeting Type --}}
                            <div>
                                <label class="text-sm font-medium text-base-content/70 mb-2 block">How would you like to meet?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" id="meeting-type-options">
                                    @foreach($meetingTypes as $index => $type)
                                        <button type="button"
                                                class="btn {{ $index === 0 ? 'btn-primary' : 'btn-outline' }} meeting-type-btn h-auto py-4 flex-col gap-1"
                                                data-type="{{ $type }}">
                                            @if($type === 'in_person')
                                                <span class="icon-[tabler--map-pin] size-6"></span>
                                                <span>In Person</span>
                                            @elseif($type === 'phone')
                                                <span class="icon-[tabler--phone] size-6"></span>
                                                <span>Phone Call</span>
                                            @elseif($type === 'video')
                                                <span class="icon-[tabler--video] size-6"></span>
                                                <span>Video Call</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="meeting_type" id="selected-meeting-type" value="{{ $meetingTypes[0] ?? 'in_person' }}">
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Date & Time --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm">2</div>
                                <h3 class="font-semibold text-lg">Select Date & Time</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Date Picker --}}
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-2 block">
                                        <span class="icon-[tabler--calendar] size-4 inline-block mr-1"></span>
                                        Pick a Date
                                    </label>
                                    <input type="date"
                                           id="booking-date"
                                           class="input input-bordered w-full text-lg"
                                           min="{{ $minDate }}"
                                           max="{{ $maxDate }}"
                                           required>
                                    <p class="text-xs text-base-content/50 mt-1.5">
                                        Book between {{ \Carbon\Carbon::parse($minDate)->format('M j') }} - {{ \Carbon\Carbon::parse($maxDate)->format('M j, Y') }}
                                    </p>
                                </div>

                                {{-- Time Slots --}}
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-2 block">
                                        <span class="icon-[tabler--clock] size-4 inline-block mr-1"></span>
                                        Available Times
                                    </label>
                                    <div id="time-slots-container" class="min-h-[120px]">
                                        <div class="flex flex-col items-center justify-center h-[120px] text-base-content/40 border-2 border-dashed border-base-300 rounded-lg">
                                            <span class="icon-[tabler--calendar-event] size-8 mb-2"></span>
                                            <p class="text-sm">Select a date to see available times</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="time" id="selected-time" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Contact Info --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm">3</div>
                                <h3 class="font-semibold text-lg">Your Information</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-1 block" for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name"
                                           class="input input-bordered w-full"
                                           placeholder="John" required>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-1 block" for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name"
                                           class="input input-bordered w-full"
                                           placeholder="Smith" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-1 block" for="email">Email *</label>
                                    <input type="email" id="email" name="email"
                                           class="input input-bordered w-full"
                                           placeholder="john@example.com" required>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-base-content/70 mb-1 block" for="phone">Phone (Optional)</label>
                                    <input type="tel" id="phone" name="phone"
                                           class="input input-bordered w-full"
                                           placeholder="+1 (555) 123-4567">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-medium text-base-content/70 mb-1 block" for="notes">
                                    Additional Notes (Optional)
                                </label>
                                <textarea id="notes" name="notes" rows="2"
                                          class="textarea textarea-bordered w-full"
                                          placeholder="Anything you'd like {{ $profile->display_name }} to know before the meeting?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Summary --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100 shadow-sm sticky top-24">
                        <div class="card-body">
                            <h3 class="font-semibold text-lg mb-4">Booking Summary</h3>

                            {{-- Selected Details --}}
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-base-content/60">Duration</span>
                                    <span class="font-medium" id="summary-duration">{{ $allowedDurations[0] ?? 30 }} minutes</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-base-content/60">Meeting Type</span>
                                    <span class="font-medium" id="summary-type">
                                        @if(($meetingTypes[0] ?? '') === 'in_person')
                                            <span class="icon-[tabler--map-pin] size-4 inline-block mr-1"></span>In Person
                                        @elseif(($meetingTypes[0] ?? '') === 'phone')
                                            <span class="icon-[tabler--phone] size-4 inline-block mr-1"></span>Phone
                                        @elseif(($meetingTypes[0] ?? '') === 'video')
                                            <span class="icon-[tabler--video] size-4 inline-block mr-1"></span>Video
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-base-content/60">Date</span>
                                    <span class="font-medium" id="summary-date">Not selected</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-base-content/60">Time</span>
                                    <span class="font-medium" id="summary-time">Not selected</span>
                                </div>
                            </div>

                            {{-- Price (Free) --}}
                            <div class="mt-4 p-3 bg-success/10 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">Total</span>
                                    <span class="text-lg font-bold text-success">Free</span>
                                </div>
                            </div>

                            {{-- Error Message --}}
                            <div id="booking-error" class="alert alert-error mt-4 hidden">
                                <span class="icon-[tabler--alert-circle] size-5"></span>
                                <span id="error-message"></span>
                            </div>

                            {{-- Submit Button --}}
                            <button type="button" id="submit-booking" class="btn btn-primary w-full mt-4 h-12 text-base" disabled>
                                <span class="loading loading-spinner loading-sm hidden"></span>
                                <span class="icon-[tabler--calendar-check] size-5 btn-icon"></span>
                                <span class="btn-text">Confirm Booking</span>
                            </button>

                            <p class="text-xs text-center text-base-content/50 mt-3">
                                You will receive a confirmation email with meeting details
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availabilityUrl = '{{ route('subdomain.instructor.availability', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}';
    const bookUrl = '{{ route('subdomain.instructor.book', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}';
    const csrfToken = '{{ csrf_token() }}';

    let selectedDuration = {{ $allowedDurations[0] ?? 30 }};
    let selectedMeetingType = '{{ $meetingTypes[0] ?? "in_person" }}';
    let selectedDate = null;
    let selectedTime = null;

    const meetingTypeLabels = {
        'in_person': '<span class="icon-[tabler--map-pin] size-4 inline-block mr-1"></span>In Person',
        'phone': '<span class="icon-[tabler--phone] size-4 inline-block mr-1"></span>Phone',
        'video': '<span class="icon-[tabler--video] size-4 inline-block mr-1"></span>Video'
    };

    // Duration selection
    document.querySelectorAll('.duration-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.duration-btn').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline');
            });
            this.classList.add('btn-primary');
            this.classList.remove('btn-outline');
            selectedDuration = parseInt(this.dataset.duration);
            document.getElementById('selected-duration').value = selectedDuration;
            document.getElementById('summary-duration').textContent = selectedDuration + ' minutes';

            // Refresh time slots if date is selected
            if (selectedDate) {
                fetchTimeSlots();
            }
        });
    });

    // Meeting type selection
    document.querySelectorAll('.meeting-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.meeting-type-btn').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline');
            });
            this.classList.add('btn-primary');
            this.classList.remove('btn-outline');
            selectedMeetingType = this.dataset.type;
            document.getElementById('selected-meeting-type').value = selectedMeetingType;
            document.getElementById('summary-type').innerHTML = meetingTypeLabels[selectedMeetingType] || selectedMeetingType;
            validateForm();
        });
    });

    // Date selection
    document.getElementById('booking-date').addEventListener('change', function() {
        selectedDate = this.value;
        selectedTime = null;
        document.getElementById('selected-time').value = '';
        document.getElementById('summary-time').textContent = 'Not selected';

        // Format date for summary
        if (selectedDate) {
            const dateObj = new Date(selectedDate + 'T12:00:00');
            const formatted = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            document.getElementById('summary-date').textContent = formatted;
        }

        fetchTimeSlots();
    });

    // Fetch time slots
    async function fetchTimeSlots() {
        const container = document.getElementById('time-slots-container');
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-[120px] text-base-content/60">
                <span class="loading loading-spinner loading-md mb-2"></span>
                <p class="text-sm">Loading available times...</p>
            </div>
        `;

        try {
            const response = await fetch(`${availabilityUrl}?date=${selectedDate}&duration=${selectedDuration}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!data.success) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-[120px] text-warning">
                        <span class="icon-[tabler--alert-triangle] size-8 mb-2"></span>
                        <p class="text-sm text-center">${data.message || 'No times available'}</p>
                    </div>
                `;
                validateForm();
                return;
            }

            if (!data.slots || data.slots.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-[120px] text-base-content/50">
                        <span class="icon-[tabler--calendar-off] size-8 mb-2"></span>
                        <p class="text-sm text-center">No available times for this date</p>
                        <p class="text-xs mt-1">Please try another date</p>
                    </div>
                `;
                validateForm();
                return;
            }

            let slotsHtml = '<div class="grid grid-cols-3 gap-2 max-h-[200px] overflow-y-auto pr-1">';
            data.slots.forEach(slot => {
                slotsHtml += `
                    <button type="button"
                            class="btn btn-outline btn-sm time-slot-btn hover:btn-primary"
                            data-time="${slot.time}">
                        ${slot.display}
                    </button>
                `;
            });
            slotsHtml += '</div>';
            container.innerHTML = slotsHtml;

            // Add click handlers
            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot-btn').forEach(b => {
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-outline');
                    });
                    this.classList.add('btn-primary');
                    this.classList.remove('btn-outline');
                    selectedTime = this.dataset.time;
                    document.getElementById('selected-time').value = selectedTime;
                    document.getElementById('summary-time').textContent = this.textContent.trim();
                    validateForm();
                });
            });

            validateForm();
        } catch (error) {
            console.error('Error fetching time slots:', error);
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-[120px] text-error">
                    <span class="icon-[tabler--alert-circle] size-8 mb-2"></span>
                    <p class="text-sm text-center">Failed to load times</p>
                    <button type="button" onclick="location.reload()" class="btn btn-ghost btn-xs mt-2">
                        <span class="icon-[tabler--refresh] size-4"></span> Retry
                    </button>
                </div>
            `;
        }
    }

    // Validate form
    function validateForm() {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();

        const isValid = selectedDuration && selectedMeetingType && selectedDate && selectedTime && firstName && lastName && email;
        document.getElementById('submit-booking').disabled = !isValid;
    }

    // Add input listeners for validation
    ['first_name', 'last_name', 'email'].forEach(id => {
        document.getElementById(id).addEventListener('input', validateForm);
    });

    // Submit booking
    document.getElementById('submit-booking').addEventListener('click', async function() {
        const btn = this;
        const spinner = btn.querySelector('.loading');
        const icon = btn.querySelector('.btn-icon');
        const text = btn.querySelector('.btn-text');

        btn.disabled = true;
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');
        text.textContent = 'Booking...';

        const errorDiv = document.getElementById('booking-error');
        errorDiv.classList.add('hidden');

        try {
            const response = await fetch(bookUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    date: selectedDate,
                    time: selectedTime,
                    duration: selectedDuration,
                    meeting_type: selectedMeetingType,
                    first_name: document.getElementById('first_name').value.trim(),
                    last_name: document.getElementById('last_name').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    phone: document.getElementById('phone').value.trim(),
                    notes: document.getElementById('notes').value.trim(),
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                }),
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = data.confirmation_url;
            } else {
                errorDiv.classList.remove('hidden');
                document.getElementById('error-message').textContent = data.message || 'Failed to book. Please try again.';
                btn.disabled = false;
                spinner.classList.add('hidden');
                icon.classList.remove('hidden');
                text.textContent = 'Confirm Booking';
            }
        } catch (error) {
            console.error('Error booking:', error);
            errorDiv.classList.remove('hidden');
            document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
            btn.disabled = false;
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
            text.textContent = 'Confirm Booking';
        }
    });
});
</script>
@endpush
