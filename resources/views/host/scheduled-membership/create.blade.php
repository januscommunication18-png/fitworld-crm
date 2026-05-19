@extends('layouts.dashboard')

@section('title', (($editMode ?? false) ? 'Edit' : 'Create') . ' Membership Schedule')

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
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    .flatpickr-calendar { font-family: inherit; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid var(--fallback-bc,oklch(var(--bc)/0.2)); z-index: 9999 !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: oklch(var(--p)) !important; border-color: oklch(var(--p)) !important; }
    .flatpickr-day:hover { background: oklch(var(--p)/0.1) !important; border-color: oklch(var(--p)/0.1) !important; }
    .flatpickr-day.today { border-color: oklch(var(--p)) !important; }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-weekdays, span.flatpickr-weekday { background: oklch(var(--b1)); }
    .flatpickr-calendar.hasTime.noCalendar { width: auto !important; min-width: 200px; }
    .flatpickr-time { display: flex !important; align-items: center !important; justify-content: center !important; gap: 4px; max-height: none !important; height: auto !important; padding: 10px !important; }
    .flatpickr-time .numInputWrapper { width: 50px !important; height: 40px !important; }
    .flatpickr-time .numInputWrapper input { font-size: 1.25rem !important; }
    .flatpickr-time .flatpickr-time-separator { font-size: 1.25rem !important; line-height: 40px !important; }
    .flatpickr-time .flatpickr-am-pm { width: 50px !important; height: 40px !important; line-height: 40px !important; font-size: 0.875rem !important; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ ($editMode ?? false) ? 'Edit' : 'Create' }} Membership Schedule</h1>
            <p class="text-base-content/60 mt-1">{{ ($editMode ?? false) ? 'Update this scheduled membership session.' : 'Create recurring class sessions for membership holders with auto-enrollment.' }}</p>
        </div>
        <a href="{{ route('schedule.calendar') }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
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
        <x-form-validate action="{{ ($editMode ?? false) ? route('scheduled-membership.update', $session) : route('scheduled-membership.store') }}" method="{{ ($editMode ?? false) ? 'PUT' : 'POST' }}">
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
                            <label class="label-text" for="membership_plan_id">Membership Plan <span class="text-error">*</span></label>
                            @php
                                $membershipOptions = [];
                                foreach($membershipPlans as $plan) {
                                    $membershipOptions[$plan->id] = $plan->name . ' (' . $plan->formatted_type . ')';
                                }
                            @endphp
                            <x-studio-select
                                name="membership_plan_id"
                                :options="$membershipOptions"
                                :selected="old('membership_plan_id', $selectedMembershipPlanId ?? '')"
                                placeholder="Select a membership plan..."
                                :required="true"
                                id="membership_plan_id"
                            />
                            @error('membership_plan_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="title">Session Title</label>
                            <input type="text" id="title" name="title"
                                value="{{ old('title', $sessionTitle ?? '') }}"
                                class="input w-full"
                                placeholder="e.g., Yoga for Members, Member Morning Class">
                            <p class="text-xs text-base-content/60 mt-1">Optional. If left empty, the membership plan name will be used.</p>
                            @error('title')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Schedule Type Toggle --}}
                        <div class="p-4 bg-base-200/30 rounded-xl mt-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium">Schedule Type</span>
                                    <p class="text-xs text-base-content/60">Choose between recurring sessions or open access walk-in check-in.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm {{ old('schedule_type', $scheduleType ?? 'scheduled') !== 'open_access' ? 'font-semibold text-primary' : 'text-base-content/50' }}" id="label-scheduled">Scheduled</span>
                                    <label class="switch switch-primary">
                                        <input type="hidden" name="schedule_type" value="scheduled">
                                        <input type="checkbox" name="schedule_type" value="open_access" id="schedule_type_toggle"
                                            {{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? 'checked' : '' }} />
                                        <span class="switch-indicator"></span>
                                    </label>
                                    <span class="text-sm {{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? 'font-semibold text-primary' : 'text-base-content/50' }}" id="label-open-access">Open Access</span>
                                </div>
                            </div>
                            <div id="open-access-info" class="mt-3 p-3 bg-info/5 border border-info/20 rounded-lg {{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? '' : 'hidden' }}">
                                <div class="flex items-start gap-2">
                                    <span class="icon-[tabler--info-circle] size-4 text-info mt-0.5"></span>
                                    <p class="text-xs text-base-content/70">Members can walk in anytime — no session booking needed. Staff will check them in from the membership check-in screen.</p>
                                </div>
                            </div>
                        </div>

                        {{-- QR Check-in --}}
                        @php
                            $qrEnabled = old('qr_checkin_enabled', $qrCheckinEnabled ?? false);
                        @endphp
                        <div class="p-4 bg-base-200/30 rounded-xl mt-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--qrcode] size-5 text-primary"></span>
                                    <div>
                                        <span class="font-medium">QR Code Check-in</span>
                                        <p class="text-xs text-base-content/60">Generate a QR code for daily member self check-in.</p>
                                    </div>
                                </div>
                                <label class="switch switch-primary">
                                    <input type="hidden" name="qr_checkin_enabled" value="0">
                                    <input type="checkbox" name="qr_checkin_enabled" value="1"
                                        {{ $qrEnabled ? 'checked' : '' }} />
                                    <span class="switch-indicator"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cards that only show for Scheduled type --}}
                <div id="scheduled-cards" class="{{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? 'hidden' : '' }}">

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
                                <label class="label-text" for="start_date">Start Date <span class="text-error">*</span></label>
                                <input type="text" id="start_date" name="start_date"
                                    value="{{ old('start_date', $selectedDate ?? now()->format('Y-m-d')) }}"
                                    class="input w-full"
                                    placeholder="Select date..." required>
                                @error('start_date')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="start_time">Start Time <span class="text-error">*</span></label>
                                <input type="text" id="start_time" name="start_time"
                                    value="{{ old('start_time', $startTime ?? '09:00') }}"
                                    class="input w-full"
                                    placeholder="Select time..." required>
                                @error('start_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="end_time">End Time <span class="text-error">*</span></label>
                                <input type="text" id="end_time" name="end_time"
                                    value="{{ old('end_time', $endTime ?? '10:00') }}"
                                    class="input w-full"
                                    placeholder="Select time..." required>
                                @error('end_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

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
                            $oldDays = old('recurrence_days', $recurrenceDays ?? []);
                        @endphp

                        {{-- Recurrence End --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label-text" for="recurrence_end_type">Ends <span class="text-error">*</span></label>
                                @php
                                    $endTypes = [
                                        'after' => 'After number of occurrences',
                                        'on' => 'On specific date',
                                        'never' => 'No end date — repeats indefinitely',
                                    ];
                                @endphp
                                <x-studio-select
                                    name="recurrence_end_type"
                                    :options="$endTypes"
                                    :selected="old('recurrence_end_type', $recurrenceEndType ?? 'after')"
                                    placeholder="Select..."
                                    :required="true"
                                    id="recurrence_end_type"
                                />
                            </div>
                            <div id="recurrence-count-wrapper">
                                <label class="label-text" for="recurrence_count">Number of Sessions <span class="text-error">*</span></label>
                                <input type="number" id="recurrence_count" name="recurrence_count"
                                    value="{{ old('recurrence_count', $recurrenceCount ?? 12) }}"
                                    class="input w-full" min="1" max="52" required>
                            </div>
                            <div id="recurrence-end-date-wrapper" class="hidden">
                                <label class="label-text" for="recurrence_end_date">End Date <span class="text-error">*</span></label>
                                <input type="text" id="recurrence_end_date" name="recurrence_end_date"
                                    value="{{ old('recurrence_end_date', $recurrenceEndDate ?? '') }}"
                                    class="input w-full"
                                    placeholder="Select end date...">
                            </div>
                        </div>

                        {{-- Repeat On (checkbox toggle) --}}
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer mb-3">
                                <input type="checkbox" id="repeat_on_toggle" class="checkbox checkbox-primary checkbox-sm"
                                    {{ !empty($oldDays) ? 'checked' : '' }}>
                                <span class="font-medium text-sm">Repeat on specific days</span>
                            </label>
                            <div id="repeat-days-wrapper" class="{{ !empty($oldDays) ? '' : 'hidden' }}">
                                <div class="flex flex-wrap gap-2" id="day-buttons">
                                    @foreach($days as $value => $label)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="recurrence_days[]" value="{{ $value }}"
                                            class="hidden day-checkbox"
                                            {{ in_array($value, $oldDays) ? 'checked' : '' }}>
                                        <span class="btn btn-sm {{ in_array($value, $oldDays) ? 'btn-primary' : 'btn-ghost' }} day-btn">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @error('recurrence_days')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 3: Assigned Staff & Instructors (optional) --}}
                <x-studio-members
                    :selected-staff="old('staff_member_ids', $assignedStaffMemberIds ?? [])"
                    :selected-instructors="old('instructor_ids', $assignedInstructorIds ?? [])"
                    title="Assigned Staff & Instructors (Optional)"
                />

                </div>{{-- /scheduled-cards --}}

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
                            @php
                                $locationOptions = [];
                                foreach($locations as $location) {
                                    $locationOptions[$location->id] = $location->name;
                                }
                            @endphp
                            <x-studio-select
                                name="location_id"
                                :options="$locationOptions"
                                :selected="old('location_id', $locationId ?? '')"
                                placeholder="Select a location..."
                                id="location_id"
                            />
                            @error('location_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 4: Capacity & Pricing --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">5</span>
                            <h3 class="card-title">Capacity & Pricing</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        <div id="capacity-wrapper" class="{{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? 'hidden' : '' }}">
                            <label class="label-text" for="capacity">Capacity <span class="text-error">*</span></label>
                            <input type="number" id="capacity" name="capacity"
                                value="{{ old('capacity', $capacity ?? 20) }}"
                                class="input w-full md:w-1/3"
                                min="1" max="500">
                            @error('capacity')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pricing accordion (readonly, from selected membership plan) --}}
                        <div id="pricing-section-wrapper" class="hidden">
                            <p class="text-xs text-base-content/60 mb-3">Sessions are included in the membership — no additional charge to members.</p>

                            <div class="accordion space-y-[5px]" id="pricing-accordion">
                                {{-- Pricing --}}
                                <div class="accordion-item bg-base-200/30 rounded-lg" id="pricing-accordion-item">
                                    <button type="button" class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="pricing-accordion-content" aria-expanded="false">
                                        <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold">Pricing</span>
                                            <span class="text-base-content/60 text-sm block font-normal">Billing period pricing across all currencies</span>
                                        </div>
                                        <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
                                    </button>
                                    <div id="pricing-accordion-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                                        <div class="px-4 pb-4">
                                            @php
                                                $billingPeriods = ['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
                                            @endphp

                                            {{-- Multi-currency table --}}
                                            @if(count($hostCurrencies) > 1)
                                            <div class="overflow-x-auto">
                                                <table class="table table-zebra table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th class="w-48">Period</th>
                                                            @foreach($hostCurrencies as $currency)
                                                                <th class="text-center">
                                                                    {{ $currency }}
                                                                    @if($currency === $defaultCurrency)
                                                                        <span class="badge badge-primary badge-xs ms-1">Default</span>
                                                                    @endif
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody id="pricing-table-body">
                                                        @foreach($billingPeriods as $months => $label)
                                                            <tr class="{{ $months === '1' ? 'bg-primary/5' : '' }}">
                                                                <td class="font-medium">
                                                                    {{ $label }}
                                                                    @if($months === '1')
                                                                        <span class="badge badge-primary badge-xs ms-1">Base</span>
                                                                    @endif
                                                                </td>
                                                                @foreach($hostCurrencies as $currency)
                                                                    <td class="text-center" id="billing-{{ $months }}-{{ $currency }}">
                                                                        <span class="text-base-content/30">—</span>
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @endif

                                            {{-- Visual pricing cards --}}
                                            <div class="{{ count($hostCurrencies) > 1 ? 'mt-4' : '' }}">
                                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3" id="pricing-cards"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Fees & Cancellation --}}
                                <div class="accordion-item bg-base-200/30 rounded-lg" id="fees-accordion-item">
                                    <button type="button" class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="fees-accordion-content" aria-expanded="false">
                                        <span class="icon-[tabler--receipt] size-5 text-primary"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold">Fees & Cancellation</span>
                                            <span class="text-base-content/60 text-sm block font-normal">Registration fees, cancellation fees, and grace period</span>
                                        </div>
                                        <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
                                    </button>
                                    <div id="fees-accordion-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                                        <div class="px-4 pb-4">
                                            <div class="overflow-x-auto">
                                                <table class="table table-zebra table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Fee Type</th>
                                                            @foreach($hostCurrencies as $currency)
                                                                <th class="text-center">{{ $currency }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-base-content/70">Registration Fee</td>
                                                            @foreach($hostCurrencies as $currency)
                                                                <td class="text-center font-medium" id="fee-registration-{{ $currency }}">
                                                                    <span class="text-base-content/40">-</span>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                        <tr>
                                                            <td class="text-base-content/70">Cancellation Fee</td>
                                                            @foreach($hostCurrencies as $currency)
                                                                <td class="text-center font-medium" id="fee-cancellation-{{ $currency }}">
                                                                    <span class="text-base-content/40">-</span>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                <label class="text-sm text-base-content/60">Grace Period</label>
                                                <p class="font-medium" id="fee-grace-period">48 hours</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- No plan selected message --}}
                        <div id="no-plan-selected" class="alert alert-soft alert-warning">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span class="text-sm">Select a membership plan above to view pricing.</span>
                        </div>
                    </div>
                </div>

                {{-- Card 5: Notes --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">6</span>
                            <h3 class="card-title">Internal Notes</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <textarea id="notes" name="notes" rows="3"
                            class="textarea w-full"
                            placeholder="Notes for staff only (not visible to clients)"
                            maxlength="1000">{{ old('notes', $notes ?? '') }}</textarea>
                        @error('notes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Card 6: Status (only for scheduled) --}}
                <div class="card bg-base-100 {{ old('schedule_type', $scheduleType ?? 'scheduled') === 'open_access' ? 'hidden' : '' }}" id="status-card">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">7</span>
                            <h3 class="card-title">Status</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium" id="status-label">Publish Sessions</span>
                                <p class="text-xs text-base-content/60" id="status-description">Published sessions are visible to members and open for booking.</p>
                            </div>
                            <input type="hidden" name="status" id="status_value" value="{{ in_array(old('status', $status ?? 'draft'), ['published', 'active']) ? 'published' : 'draft' }}">
                            <label class="switch switch-primary">
                                <input type="checkbox" id="status_toggle"
                                    {{ in_array(old('status', $status ?? 'draft'), ['published', 'active']) ? 'checked' : '' }}
                                    onchange="document.getElementById('status_value').value = this.checked ? 'published' : 'draft'" />
                                <span class="switch-indicator"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="card bg-base-100">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn btn-primary w-full" id="submit-btn">
                            <span class="icon-[tabler--{{ ($editMode ?? false) ? 'check' : 'calendar-plus' }}] size-5" id="submit-icon"></span>
                            <span id="submit-text">{{ ($editMode ?? false) ? 'Update Schedule' : 'Create Scheduled Sessions' }}</span>
                        </button>
                        <a href="{{ route('schedule.calendar') }}" class="btn btn-ghost w-full">Cancel</a>
                    </div>
                </div>
            </div>
        </x-form-validate>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Schedule type toggle
    var scheduleTypeToggle = document.getElementById('schedule_type_toggle');
    var scheduledCards = document.getElementById('scheduled-cards');
    var openAccessInfo = document.getElementById('open-access-info');
    var labelScheduled = document.getElementById('label-scheduled');
    var labelOpenAccess = document.getElementById('label-open-access');
    var submitText = document.getElementById('submit-text');
    var submitIcon = document.getElementById('submit-icon');
    var statusLabel = document.getElementById('status-label');
    var statusDescription = document.getElementById('status-description');
    var capacityWrapper = document.getElementById('capacity-wrapper');
    var statusCard = document.getElementById('status-card');

    function toggleScheduleType() {
        var isOpenAccess = scheduleTypeToggle.checked;
        scheduledCards.classList.toggle('hidden', isOpenAccess);
        openAccessInfo.classList.toggle('hidden', !isOpenAccess);
        capacityWrapper.classList.toggle('hidden', isOpenAccess);
        statusCard.classList.toggle('hidden', isOpenAccess);

        labelScheduled.classList.toggle('font-semibold', !isOpenAccess);
        labelScheduled.classList.toggle('text-primary', !isOpenAccess);
        labelScheduled.classList.toggle('text-base-content/50', isOpenAccess);
        labelOpenAccess.classList.toggle('font-semibold', isOpenAccess);
        labelOpenAccess.classList.toggle('text-primary', isOpenAccess);
        labelOpenAccess.classList.toggle('text-base-content/50', !isOpenAccess);

        // Update status label based on schedule type
        if (isOpenAccess) {
            statusLabel.textContent = 'Activate Open Access';
            statusDescription.textContent = 'When active, members can walk in and be checked in anytime.';
        } else {
            statusLabel.textContent = 'Publish Sessions';
            statusDescription.textContent = 'Published sessions are visible to members and open for booking.';
        }

        @if(!($editMode ?? false))
        if (isOpenAccess) {
            submitText.textContent = 'Save Open Access Plan';
            submitIcon.className = 'icon-[tabler--door-enter] size-5';
        } else {
            submitText.textContent = 'Create Scheduled Sessions';
            submitIcon.className = 'icon-[tabler--calendar-plus] size-5';
        }
        @endif
    }

    scheduleTypeToggle.addEventListener('change', toggleScheduleType);
    toggleScheduleType(); // Set initial state

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

    // Repeat on days toggle
    var repeatToggle = document.getElementById('repeat_on_toggle');
    var repeatDaysWrapper = document.getElementById('repeat-days-wrapper');

    repeatToggle.addEventListener('change', function() {
        if (this.checked) {
            repeatDaysWrapper.classList.remove('hidden');
        } else {
            repeatDaysWrapper.classList.add('hidden');
            repeatDaysWrapper.querySelectorAll('.day-checkbox').forEach(function(cb) {
                cb.checked = false;
                cb.nextElementSibling.classList.remove('btn-primary');
                cb.nextElementSibling.classList.add('btn-ghost');
            });
        }
    });

    // Day button toggle styling
    document.querySelectorAll('.day-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var btn = this.nextElementSibling;
            if (this.checked) {
                btn.classList.remove('btn-ghost');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-ghost');
            }
        });
    });

    // Flatpickr
    flatpickr('#start_date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false
    });

    flatpickr('#recurrence_end_date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        altInputClass: 'input w-full',
        appendTo: document.body,
        static: false
    });

    flatpickr('#start_time', {
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

    flatpickr('#end_time', {
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

    // Membership plan pricing
    var membershipPlanSelect = document.getElementById('membership_plan_id');
    var pricingSectionWrapper = document.getElementById('pricing-section-wrapper');
    var noPlanSelected = document.getElementById('no-plan-selected');

    @php
        $billingPeriodsJs = ['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
        $planFullData = [];
        foreach($membershipPlans as $plan) {
            $billing = [];
            foreach($billingPeriodsJs as $months => $label) {
                $periodPrices = $plan->billing_discounts[$months] ?? [];
                if (!is_array($periodPrices)) $periodPrices = [];
                $perCurrency = [];
                foreach($hostCurrencies as $currency) {
                    $val = !empty($periodPrices[$currency]) ? (float)$periodPrices[$currency] : 0;
                    $perCurrency[$currency] = $val;
                }
                $billing[$months] = $perCurrency;
            }
            $regFees = [];
            $cancelFees = [];
            foreach($hostCurrencies as $currency) {
                $regFees[$currency] = !empty($plan->registration_fees[$currency]) ? (float)$plan->registration_fees[$currency] : 0;
                $cancelFees[$currency] = !empty($plan->cancellation_fees[$currency]) ? (float)$plan->cancellation_fees[$currency] : 0;
            }
            $planFullData[$plan->id] = [
                'billing' => $billing,
                'registration_fees' => $regFees,
                'cancellation_fees' => $cancelFees,
                'grace_hours' => $plan->cancellation_grace_hours ?? 48,
                'base_price' => $plan->prices[$defaultCurrency] ?? 0,
            ];
        }
    @endphp
    var planFullData = @json($planFullData);
    var hostCurrencies = @json($hostCurrencies);
    var defaultCurrency = @json($defaultCurrency);
    var currencySymbols = @json($currencySymbols);
    var billingPeriods = @json($billingPeriodsJs);

    function fmt(amount, currency) {
        var sym = currencySymbols[currency] || currency;
        return sym + parseFloat(amount).toFixed(2);
    }

    function updatePricingDisplay() {
        var selectedId = membershipPlanSelect.value;
        if (selectedId && planFullData[selectedId]) {
            var data = planFullData[selectedId];
            pricingSectionWrapper.classList.remove('hidden');
            noPlanSelected.classList.add('hidden');

            // Multi-currency table
            Object.keys(billingPeriods).forEach(function(months) {
                hostCurrencies.forEach(function(currency) {
                    var el = document.getElementById('billing-' + months + '-' + currency);
                    if (!el) return;
                    var val = data.billing[months] && data.billing[months][currency] ? data.billing[months][currency] : 0;
                    if (val > 0) {
                        el.innerHTML = '<span class="text-success font-medium">' + fmt(val, currency) + '</span>';
                    } else {
                        el.innerHTML = '<span class="text-base-content/30">—</span>';
                    }
                });
            });

            // Visual pricing cards
            var cardsEl = document.getElementById('pricing-cards');
            cardsEl.innerHTML = '';
            var basePrice = parseFloat(data.base_price) || 0;

            Object.keys(billingPeriods).forEach(function(months) {
                var m = parseInt(months);
                var total = data.billing[months] && data.billing[months][defaultCurrency] ? parseFloat(data.billing[months][defaultCurrency]) : 0;
                var hasValue = total > 0;
                var monthlyRate = m > 0 ? total / m : 0;
                var totalWithout = basePrice * m;
                var savings = hasValue && m > 1 ? totalWithout - total : 0;
                var sym = currencySymbols[defaultCurrency] || '$';

                var bgClass = hasValue ? (months === '1' ? 'bg-primary/10 ring-1 ring-primary/20' : 'bg-success/10') : 'bg-base-200/50';
                var priceClass = hasValue ? (months === '1' ? 'text-primary' : 'text-success') : 'text-base-content/30';

                var html = '<div class="text-center p-3 rounded-lg ' + bgClass + '">' +
                    '<div class="text-sm text-base-content/60">' + billingPeriods[months] + '</div>';

                if (hasValue) {
                    html += '<div class="text-xl font-bold ' + priceClass + '">' + sym + total.toFixed(2) + '</div>';
                    if (m > 1) {
                        html += '<div class="text-xs text-base-content/50">' + sym + monthlyRate.toFixed(2) + '/mo</div>';
                    } else {
                        html += '<div class="text-xs text-base-content/50">Base price</div>';
                    }
                    if (savings > 0) {
                        html += '<div class="text-xs text-success mt-0.5">Save ' + sym + savings.toFixed(2) + '</div>';
                    }
                } else {
                    html += '<div class="text-xl font-bold text-base-content/30">—</div>' +
                        '<div class="text-xs text-base-content/40">Not set</div>';
                }
                html += '</div>';
                cardsEl.innerHTML += html;
            });

            // Fees
            hostCurrencies.forEach(function(currency) {
                var regEl = document.getElementById('fee-registration-' + currency);
                var cancelEl = document.getElementById('fee-cancellation-' + currency);
                if (regEl) {
                    var regVal = data.registration_fees[currency] || 0;
                    regEl.innerHTML = regVal > 0 ? fmt(regVal, currency) : '<span class="text-base-content/40">-</span>';
                }
                if (cancelEl) {
                    var cancelVal = data.cancellation_fees[currency] || 0;
                    cancelEl.innerHTML = cancelVal > 0 ? '<span class="text-error">' + fmt(cancelVal, currency) + '</span>' : '<span class="text-base-content/40">-</span>';
                }
            });

            document.getElementById('fee-grace-period').textContent = data.grace_hours + ' hours';
        } else {
            pricingSectionWrapper.classList.add('hidden');
            noPlanSelected.classList.remove('hidden');
        }
    }

    membershipPlanSelect.addEventListener('change', updatePricingDisplay);

    var observer = new MutationObserver(updatePricingDisplay);
    observer.observe(membershipPlanSelect, { attributes: true, childList: true, subtree: true });

    setTimeout(updatePricingDisplay, 200);
});
</script>
@endpush
