@extends('layouts.dashboard')

@section('title', 'Membership Schedule')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.calendar') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Calendar</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Membership Schedule</li>
    </ol>
@endsection

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

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('schedule.calendar') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Membership Schedule</h1>
            <p class="text-base-content/60 mt-1">Create recurring class sessions for membership holders with auto-enrollment.</p>
        </div>
    </div>

    @if($membershipPlans->isEmpty())
        <div class="alert alert-warning">
            <span class="icon-[tabler--alert-triangle] size-5"></span>
            <div>
                <h3 class="font-bold">No Active Membership Plans</h3>
                <p class="text-sm">You need at least one active membership plan to create membership schedules.</p>
            </div>
            <a href="{{ route('membership-plans.create') }}" class="btn btn-sm btn-warning">Create Membership Plan</a>
        </div>
    @else
        <form action="{{ route('scheduled-membership.store') }}" method="POST">
            @csrf
            <input type="hidden" name="is_recurring" value="1">

            <div class="space-y-6">
                {{-- Card 1: Membership Plan Selection --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">1</span>
                            <h3 class="card-title">Membership Plan</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text" for="membership_plan_id">Membership Plan</label>
                            <select id="membership_plan_id" name="membership_plan_id" class="hidden @error('membership_plan_id') input-error @enderror" required
                                data-select='{
                                    "hasSearch": true,
                                    "searchPlaceholder": "Search membership plans...",
                                    "placeholder": "Select a membership plan...",
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }'>
                                <option value="">Select a membership plan...</option>
                                @foreach($membershipPlans as $plan)
                                <option value="{{ $plan->id }}"
                                    data-price="{{ $plan->price ?? 0 }}"
                                    data-interval="{{ $plan->interval }}"
                                    {{ old('membership_plan_id', $selectedMembershipPlanId ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} ({{ $plan->formatted_type }})
                                </option>
                                @endforeach
                            </select>
                            @error('membership_plan_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="title">Session Title</label>
                            <input type="text" id="title" name="title"
                                value="{{ old('title') }}"
                                class="input w-full @error('title') input-error @enderror"
                                placeholder="e.g., Yoga for Members, Member Morning Class">
                            <p class="text-xs text-base-content/60 mt-1">Optional. If left empty, the membership plan name will be used.</p>
                            @error('title')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Recurring Schedule --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                            <h3 class="card-title">Recurring Schedule</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label-text" for="start_date">Start Date</label>
                                <input type="text" id="start_date" name="start_date"
                                    value="{{ old('start_date', $selectedDate ?? now()->format('Y-m-d')) }}"
                                    class="input w-full flatpickr-date @error('start_date') input-error @enderror"
                                    placeholder="Select date..." required>
                                @error('start_date')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="start_time">Start Time</label>
                                <input type="text" id="start_time" name="start_time"
                                    value="{{ old('start_time', '09:00') }}"
                                    class="input w-full flatpickr-time @error('start_time') input-error @enderror"
                                    placeholder="Select time..." required>
                                @error('start_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="end_time">End Time</label>
                                <input type="text" id="end_time" name="end_time"
                                    value="{{ old('end_time', '10:00') }}"
                                    class="input w-full flatpickr-time @error('end_time') input-error @enderror"
                                    placeholder="Select time..." required>
                                @error('end_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Recurring Days --}}
                        <div>
                            <label class="label-text mb-2 block">Repeat On</label>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $days = [
                                        'monday' => 'Mon',
                                        'tuesday' => 'Tue',
                                        'wednesday' => 'Wed',
                                        'thursday' => 'Thu',
                                        'friday' => 'Fri',
                                        'saturday' => 'Sat',
                                        'sunday' => 'Sun',
                                    ];
                                    $oldDays = old('recurrence_days', []);
                                @endphp
                                @foreach($days as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="recurrence_days[]" value="{{ $value }}"
                                        class="checkbox checkbox-primary checkbox-sm peer hidden"
                                        {{ in_array($value, $oldDays) ? 'checked' : '' }}>
                                    <span class="btn btn-sm btn-ghost peer-checked:btn-primary">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('recurrence_days')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Recurrence End --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="recurrence_end_type">Ends</label>
                                <select id="recurrence_end_type" name="recurrence_end_type" class="select w-full">
                                    <option value="after" {{ old('recurrence_end_type', 'after') === 'after' ? 'selected' : '' }}>After number of occurrences</option>
                                    <option value="on" {{ old('recurrence_end_type') === 'on' ? 'selected' : '' }}>On specific date</option>
                                    <option value="never" {{ old('recurrence_end_type') === 'never' ? 'selected' : '' }}>Never (ongoing)</option>
                                </select>
                            </div>
                            <div id="recurrence-count-wrapper">
                                <label class="label-text" for="recurrence_count">Number of Sessions</label>
                                <input type="number" id="recurrence_count" name="recurrence_count"
                                    value="{{ old('recurrence_count', 12) }}"
                                    class="input w-full" min="1" max="52">
                            </div>
                            <div id="recurrence-end-date-wrapper" class="hidden">
                                <label class="label-text" for="recurrence_end_date">End Date</label>
                                <input type="text" id="recurrence_end_date" name="recurrence_end_date"
                                    value="{{ old('recurrence_end_date') }}"
                                    class="input w-full flatpickr-date"
                                    placeholder="Select end date...">
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
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text" for="instructor_ids">Assign Instructors</label>
                            <select id="instructor_ids" name="instructor_ids[]" multiple class="hidden"
                                data-select='{
                                    "hasSearch": true,
                                    "searchPlaceholder": "Search instructors...",
                                    "placeholder": "Select instructors...",
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }'>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}"
                                    {{ in_array($instructor->id, old('instructor_ids', [])) ? 'selected' : '' }}>
                                    {{ $instructor->name }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-base-content/60 mt-1">First selected instructor will be primary. Additional instructors will be backups.</p>
                            @error('instructor_ids')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 4: Location --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">4</span>
                            <h3 class="card-title">Location</h3>
                        </div>
                    </div>
                    <div class="card-body">
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
                                <option value="{{ $location->id }}"
                                    {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 5: Capacity & Pricing --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                            <h3 class="card-title">Capacity & Pricing</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="capacity">Capacity</label>
                                <input type="number" id="capacity" name="capacity"
                                    value="{{ old('capacity', 20) }}"
                                    class="input w-full @error('capacity') input-error @enderror"
                                    min="1" max="500" required>
                                @error('capacity')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text">Membership Price</label>
                                <div id="membership-price-display" class="alert alert-soft alert-info">
                                    <span class="icon-[tabler--currency-dollar] size-5"></span>
                                    <span>$<span id="membership-price">0</span>/<span id="membership-interval">month</span></span>
                                </div>
                                <p class="text-xs text-base-content/60 mt-1">Sessions are included in the membership - no additional charge to members.</p>
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
                            placeholder="Notes for staff only (not visible to clients)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Card 7: Status --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">7</span>
                            <h3 class="card-title">Status</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="draft"
                                    class="radio radio-primary"
                                    {{ old('status', 'draft') === 'draft' ? 'checked' : '' }}>
                                <span class="label-text">Draft</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="published"
                                    class="radio radio-primary"
                                    {{ old('status') === 'published' ? 'checked' : '' }}>
                                <span class="label-text">Published</span>
                                <span class="badge badge-success badge-sm">Live</span>
                            </label>
                        </div>
                        <p class="text-xs text-base-content/60 mt-2">Published sessions are visible to members and open for booking.</p>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('schedule.calendar') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        Create Scheduled Sessions
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recurrence end type toggle
    var recurrenceEndType = document.getElementById('recurrence_end_type');
    var recurrenceCountWrapper = document.getElementById('recurrence-count-wrapper');
    var recurrenceEndDateWrapper = document.getElementById('recurrence-end-date-wrapper');

    function updateRecurrenceFields() {
        var type = recurrenceEndType.value;
        recurrenceCountWrapper.classList.toggle('hidden', type !== 'after');
        recurrenceEndDateWrapper.classList.toggle('hidden', type !== 'on');
    }

    recurrenceEndType.addEventListener('change', updateRecurrenceFields);
    updateRecurrenceFields();

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

    // Membership plan price display
    var membershipPlanSelect = document.getElementById('membership_plan_id');
    var membershipPriceDisplay = document.getElementById('membership-price');
    var membershipIntervalDisplay = document.getElementById('membership-interval');

    function updateMembershipPrice() {
        var selectedOption = membershipPlanSelect.options[membershipPlanSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            var price = selectedOption.dataset.price || '0';
            var interval = selectedOption.dataset.interval || 'monthly';
            membershipPriceDisplay.textContent = parseFloat(price).toFixed(2);
            membershipIntervalDisplay.textContent = interval;
        } else {
            membershipPriceDisplay.textContent = '0';
            membershipIntervalDisplay.textContent = 'month';
        }
    }

    membershipPlanSelect.addEventListener('change', updateMembershipPrice);

    // Watch for HSSelect changes
    var observer = new MutationObserver(updateMembershipPrice);
    observer.observe(membershipPlanSelect, { attributes: true, childList: true, subtree: true });

    setTimeout(updateMembershipPrice, 200);
});
</script>
@endpush
@endsection
