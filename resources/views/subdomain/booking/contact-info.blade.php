@extends('layouts.subdomain')

@section('title', 'Complete Your Booking — ' . $host->studio_name)

@section('content')
@php
    $item = $bookingState['selected_item'] ?? [];
    $currencySymbol = $item['currency_symbol'] ?? \App\Models\MembershipPlan::getCurrencySymbol($item['currency'] ?? $host->default_currency ?? 'USD');
@endphp
@push('styles')
<style>
    /* Selected billing period: green background, all inner text white. The
       children carry their own color utilities (text-success / text-base-content)
       so we override them when the parent gets .btn-success applied by JS. */
    .billing-period-btn.btn-success,
    .billing-period-btn.btn-success * {
        color: #ffffff !important;
    }
</style>
@endpush

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="w-full h-full px-4 md:px-6">
            <div class="flex items-center justify-between h-full">
                {{-- Logo --}}
                <div class="flex items-center">
                    @if($host->logo_url)
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                            <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                        </a>
                    @else
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                                <span class="text-base font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                            </div>
                            <span class="font-bold text-base hidden sm:inline">{{ $host->studio_name }}</span>
                        </a>
                    @endif
                </div>

                {{-- Progress Indicator --}}
                <div class="hidden md:flex items-center gap-2 text-sm">
                    <span class="flex items-center gap-1.5 text-primary font-medium">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">1</span>
                        Details
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-base-content/50">
                        <span class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-xs font-bold">2</span>
                        Payment
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-base-content/50">
                        <span class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-xs font-bold">3</span>
                        Done
                    </span>
                </div>

                {{-- Change Selection --}}
                <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    <span class="hidden sm:inline">Change</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-6 md:py-10">
        <div class="container-fixed">
            <div class="max-w-4xl mx-auto">

                {{-- What You're Booking --}}
                <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-primary/20 mb-6">
                    <div class="card-body py-4 md:py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-primary/20 flex items-center justify-center shrink-0">
                                @if(($item['type'] ?? '') === 'class_plan' || ($item['type'] ?? '') === 'class_session')
                                    <span class="icon-[tabler--yoga] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'service_slot' || ($item['type'] ?? '') === 'service_plan')
                                    <span class="icon-[tabler--sparkles] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'membership_plan')
                                    <span class="icon-[tabler--id-badge-2] size-7 text-primary"></span>
                                @else
                                    <span class="icon-[tabler--calendar-check] size-7 text-primary"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 id="booking-item-name" class="text-xl font-bold text-base-content">{{ $item['name'] ?? 'Your Selection' }}</h2>
                                <div id="booking-item-meta" class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-base-content/70">
                                    @if(($item['type'] ?? '') === 'class_plan')
                                        @php $cbt = $item['class_booking_type'] ?? 'single'; @endphp
                                        @if($cbt === 'series')
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--calendar-repeat] size-4"></span> Series Class</span>
                                            @if(!empty($item['billing_period']))
                                                <span class="flex items-center gap-1"><span class="icon-[tabler--refresh] size-4"></span> {{ $item['billing_period'] }}</span>
                                            @endif
                                        @else
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--calendar-event] size-4"></span> Single Class</span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'membership_plan')
                                        <span class="flex items-center gap-1"><span class="icon-[tabler--id-badge-2] size-4"></span> Membership Plan</span>
                                        @if(!empty($item['billing_period']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--refresh] size-4"></span> Billed {{ $item['billing_period'] }}</span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'class_pack')
                                        <span class="flex items-center gap-1"><span class="icon-[tabler--ticket] size-4"></span> Class Pack</span>
                                        @if(!empty($item['class_count']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--check] size-4"></span> {{ $item['class_count'] }} classes</span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'service_plan')
                                        <span class="flex items-center gap-1"><span class="icon-[tabler--sparkles] size-4"></span> Service</span>
                                        @if(!empty($item['duration']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--clock] size-4"></span> {{ $item['duration'] }} minutes</span>
                                        @endif
                                    @else
                                        @if(!empty($item['datetime']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--calendar] size-4"></span> {{ $item['datetime'] }}</span>
                                        @endif
                                        @if(!empty($item['instructor']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--user] size-4"></span> {{ $item['instructor'] }}</span>
                                        @endif
                                        @if(!empty($item['location']))
                                            <span class="flex items-center gap-1"><span class="icon-[tabler--map-pin] size-4"></span> {{ $item['location'] }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <span id="booking-item-price" class="text-2xl font-bold text-primary">{{ $currencySymbol }}{{ number_format($item['price'] ?? 0, 2) }}</span>
                                @if(($item['type'] ?? '') === 'membership_plan')
                                    <div class="text-sm text-base-content/60">{{ $item['billing_period'] ?? 'per month' }}</div>
                                @endif
                                @if(!empty($item['is_waitlist']))
                                    <div class="badge badge-warning badge-sm mt-1">Waitlist</div>
                                @endif
                            </div>
                        </div>

                        {{-- Booking Type Selector (for class plans only) --}}
                        @if(($item['type'] ?? '') === 'class_plan')
                        @php
                            $currentType = $item['class_booking_type'] ?? 'single';
                            $rawBillingDiscounts = $item['billing_discounts'] ?? [];
                            $itemCurrency = $item['currency'] ?? ($host->default_currency ?? 'USD');

                            // Defensive: billing_discounts may already be a flat
                            // [period => number] (from the current controller) OR a
                            // legacy nested [period => [currency => number]] map left
                            // over from older sessions. Normalize to the flat shape
                            // so the template logic below can stay simple.
                            $billingDiscounts = [];
                            foreach ($rawBillingDiscounts as $period => $value) {
                                $billingDiscounts[(string) $period] = is_array($value)
                                    ? (float) ($value[$itemCurrency] ?? 0)
                                    : (float) $value;
                            }
                            $hasSeriesOption = ($item['has_series_option'] ?? false) && collect($billingDiscounts)->filter(fn($v) => $v > 0)->isNotEmpty();
                            $basePrice = $item['original_price'] ?? $item['price'] ?? 0;
                        @endphp
                        <div class="bg-base-100 rounded-xl border border-base-200 mt-4 p-4">
                            <p class="text-sm font-medium text-base-content/70 mb-3">Booking Type</p>
                            <div class="flex flex-wrap gap-2" id="class-booking-type-selector">
                                <button type="button" data-type="single"
                                    class="booking-type-btn btn btn-sm {{ $currentType === 'single' ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                                    <span class="icon-[tabler--calendar-event] size-4"></span> Single Class
                                </button>
                                @if($hasSeriesOption)
                                <button type="button" data-type="series"
                                    class="booking-type-btn btn btn-sm {{ $currentType === 'series' ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                                    <span class="icon-[tabler--calendar-repeat] size-4"></span> Series Class
                                </button>
                                @endif
                            </div>

                            {{-- Series: Billing Period Options --}}
                            @if($hasSeriesOption)
                            <div id="series-period-picker" class="{{ $currentType === 'series' ? '' : 'hidden' }} mt-3">
                                <p class="text-xs text-base-content/60 mb-2">Select billing period</p>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $periods = ['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
                                    @endphp
                                    @foreach($periods as $months => $label)
                                        @php $periodTotal = floatval($billingDiscounts[$months] ?? 0); @endphp
                                        @if($periodTotal > 0)
                                        @php $m = (int) $months; $monthlyRate = $m > 0 ? $periodTotal / $m : 0; @endphp
                                        <button type="button" data-period="{{ $months }}" data-price="{{ $periodTotal }}"
                                            class="billing-period-btn btn btn-sm btn-ghost border border-base-content flex-col h-auto py-2 px-3">
                                            <span class="text-xs text-base-content/60">{{ $label }}</span>
                                            <span class="font-bold text-success">{{ $currencySymbol }}{{ number_format($periodTotal, 0) }}</span>
                                            <span class="text-[10px] text-base-content/50">{{ $currencySymbol }}{{ number_format($monthlyRate, 2) }}/mo</span>
                                        </button>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Series summary — populated by JS after the AJAX response.
                                      Shows actual session count and the first/last session date
                                      within the chosen billing period. --}}
                                @php $initialSummary = $item['series_summary'] ?? null; @endphp
                                <div id="series-summary" class="{{ $initialSummary ? '' : 'hidden' }} mt-3 grid grid-cols-3 gap-2 bg-success/5 border border-success/30 rounded-lg p-3 text-center">
                                    <div>
                                        <div class="text-[10px] uppercase tracking-wide text-base-content/60">Sessions</div>
                                        <div id="series-summary-count" class="font-bold text-success">{{ $initialSummary['session_count'] ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] uppercase tracking-wide text-base-content/60">Starts</div>
                                        <div id="series-summary-start" class="font-semibold text-sm">{{ $initialSummary['start_date'] ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] uppercase tracking-wide text-base-content/60">Ends</div>
                                        <div id="series-summary-end" class="font-semibold text-sm">{{ $initialSummary['end_date'] ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Single Class: pick a specific session.
                                  Shown when class_booking_type is "single", hidden when "series".
                                  When visible, the dropdown is required to proceed. --}}
                            <div id="single-session-picker" class="{{ $currentType === 'single' ? '' : 'hidden' }} mt-4 pt-4 border-t border-base-200">
                                <label class="label-text mb-1 block" for="class_session_id">Pick a session <span class="text-error">*</span></label>
                                @if($sessions->isEmpty())
                                    <div class="alert alert-soft alert-warning text-sm">
                                        <span class="icon-[tabler--alert-circle] size-4"></span>
                                        <span>No upcoming sessions are scheduled for this class right now. Please switch to Series or check back later.</span>
                                    </div>
                                @else
                                    @php $preselectedSession = old('class_session_id', $item['class_session_id'] ?? ''); @endphp
                                    <x-studio-select
                                        name="__inner_class_session_id"
                                        id="class_session_id"
                                        placeholder="Select a date & time..."
                                        :option-count="$sessions->count()">
                                        <option value="">-- Select a date & time --</option>
                                        @foreach($sessions as $s)
                                            @php
                                                $bookedCount = $s->bookings_count ?? $s->bookings()->where('status', 'confirmed')->count();
                                                $cap = $s->capacity ?? $s->classPlan?->default_capacity ?? 0;
                                                $isFull = $cap > 0 && $bookedCount >= $cap;
                                                $instructor = $s->primaryInstructor?->name;
                                                $location = $s->room?->location?->name;
                                                $bits = [
                                                    $s->start_time->format('D, M j · g:i A'),
                                                    $instructor,
                                                    $location,
                                                    $isFull ? '(waitlist)' : null,
                                                ];
                                                $label = implode(' · ', array_filter($bits));
                                            @endphp
                                            <option value="{{ $s->id }}" {{ (string) $preselectedSession === (string) $s->id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </x-studio-select>
                                    @error('class_session_id')
                                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    {{-- Form --}}
                    <div class="lg:col-span-3">
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <span class="icon-[tabler--user-circle] size-5 text-primary"></span>
                                    </div>
                                    <div>
                                        <h1 class="text-lg font-bold">Your Details</h1>
                                        <p class="text-sm text-base-content/60">We'll use this to confirm your booking</p>
                                    </div>
                                </div>

                                @if($errors->any())
                                    <div class="alert alert-error mb-4">
                                        <span class="icon-[tabler--alert-circle] size-5"></span>
                                        <span>{{ $errors->first() }}</span>
                                    </div>
                                @endif

                                <form action="{{ route('booking.contact.save', ['subdomain' => $host->subdomain]) }}" method="POST" id="contact-info-form">
                                    @csrf
                                    {{-- Carries the class session id (synced from the session picker
                                          which lives in the header card, outside this form). --}}
                                    <input type="hidden" name="class_session_id" id="class_session_id_hidden" value="{{ old('class_session_id', $item['class_session_id'] ?? '') }}">

                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="form-control">
                                                <label class="label" for="first_name">
                                                    <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                                                </label>
                                                <input type="text" id="first_name" name="first_name"
                                                       value="{{ old('first_name', $prefillData['first_name'] ?? '') }}"
                                                       required
                                                       placeholder="John"
                                                       @if($isLoggedIn) readonly @endif
                                                       class="input input-bordered w-full focus:input-primary @error('first_name') input-error @enderror @if($isLoggedIn) bg-base-200/60 cursor-not-allowed @endif">
                                            </div>
                                            <div class="form-control">
                                                <label class="label" for="last_name">
                                                    <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                                                </label>
                                                <input type="text" id="last_name" name="last_name"
                                                       value="{{ old('last_name', $prefillData['last_name'] ?? '') }}"
                                                       required
                                                       placeholder="Doe"
                                                       @if($isLoggedIn) readonly @endif
                                                       class="input input-bordered w-full focus:input-primary @error('last_name') input-error @enderror @if($isLoggedIn) bg-base-200/60 cursor-not-allowed @endif">
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <label class="label" for="email">
                                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                                            </label>
                                            <div class="relative">
                                                <span class="icon-[tabler--mail] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                                <input type="email" id="email" name="email"
                                                       value="{{ old('email', $prefillData['email'] ?? '') }}"
                                                       required
                                                       placeholder="john@example.com"
                                                       @if($isLoggedIn) readonly @endif
                                                       class="input input-bordered w-full pl-10 focus:input-primary @error('email') input-error @enderror @if($isLoggedIn) bg-base-200/60 cursor-not-allowed @endif">
                                            </div>
                                            <label class="label">
                                                <span class="label-text-alt text-base-content/50">
                                                    @if($isLoggedIn)
                                                        Email is tied to your account. <a href="{{ route('member.portal.profile', ['subdomain' => $host->subdomain]) }}" class="link link-primary">Manage in your profile</a>.
                                                    @else
                                                        Confirmation & receipt will be sent here
                                                    @endif
                                                </span>
                                            </label>
                                        </div>

                                        <div class="form-control">
                                            <x-phone-input
                                                name="phone"
                                                :value="old('phone', $prefillData['phone'] ?? '')"
                                                label="Phone"
                                                :required="true"
                                                :host="$host"
                                                id-suffix="booking-contact"
                                            />
                                            @error('phone')
                                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-8">
                                        <button type="submit" class="btn btn-primary btn-lg w-full gap-2">
                                            Continue to Payment
                                            <span class="icon-[tabler--arrow-right] size-5"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Side Info --}}
                    <div class="lg:col-span-2 space-y-4">
                        {{-- Studio Info --}}
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <div class="flex items-center gap-3">
                                    @if($host->logo_url)
                                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-12 object-contain rounded-lg">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center">
                                            <span class="text-xl font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-bold">{{ $host->studio_name }}</h3>
                                        @if($host->address)
                                            <p class="text-sm text-base-content/60">{{ $host->address }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Security Badge --}}
                        <div class="card bg-base-100">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                                        <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Secure Booking</h4>
                                        <p class="text-xs text-base-content/60">Your information is protected</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Help --}}
                        <div class="card bg-base-100">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-info/10 flex items-center justify-center">
                                        <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Need Help?</h4>
                                        @if($host->email)
                                            <a href="mailto:{{ $host->email }}" class="text-xs text-primary hover:underline">{{ $host->email }}</a>
                                        @else
                                            <p class="text-xs text-base-content/60">Contact the studio</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(($item['type'] ?? '') === 'class_plan')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var classPlanId = '{{ $item["class_plan_id"] ?? $item["id"] ?? "" }}';
    var csrfToken = '{{ csrf_token() }}';
    var subdomain = '{{ $host->subdomain }}';
    var currencySymbol = '{{ $currencySymbol }}';

    // FlyonUI's HSSelect normally auto-inits on its own, but it's a race with
    // late-imported scripts in some bundles. Force a re-init so the styled
    // session picker always hydrates. No-op for already-initialized selects.
    if (typeof HSSelect !== 'undefined') {
        try { HSSelect.autoInit(); } catch (e) {}
    }

    // Booking type buttons
    document.querySelectorAll('.booking-type-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var type = this.dataset.type;
            var data = { class_booking_type: type, _token: csrfToken };

            // For series, need a billing period — select first available if none selected
            var periodPicker = document.getElementById('series-period-picker');
            var sessionPicker = document.getElementById('single-session-picker');
            if (type === 'series') {
                if (periodPicker) periodPicker.classList.remove('hidden');
                if (sessionPicker) sessionPicker.classList.add('hidden');
                var selectedPeriod = document.querySelector('.billing-period-btn.btn-success');
                if (!selectedPeriod) {
                    var firstPeriod = document.querySelector('.billing-period-btn');
                    if (firstPeriod) {
                        firstPeriod.click();
                        return;
                    }
                }
                data.billing_period = selectedPeriod ? selectedPeriod.dataset.period : null;
            } else {
                if (periodPicker) periodPicker.classList.add('hidden');
                if (sessionPicker) sessionPicker.classList.remove('hidden');
            }

            updateBookingType(data, this);
        });
    });

    // Sync the session picker (header card, outside the form) to the hidden
    // input inside the form; also block submit if Single mode and no session picked.
    var sessionSelect = document.getElementById('class_session_id');
    var sessionHidden = document.getElementById('class_session_id_hidden');
    if (sessionSelect && sessionHidden) {
        sessionSelect.addEventListener('change', function () {
            sessionHidden.value = this.value || '';
        });
        // Initial sync (handles validation round-trip with old() values).
        if (sessionSelect.value && !sessionHidden.value) {
            sessionHidden.value = sessionSelect.value;
        }
    }

    var contactForm = document.getElementById('contact-info-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            var activeBtn = document.querySelector('.booking-type-btn.btn-primary');
            var currentType = activeBtn ? activeBtn.dataset.type : '{{ $currentType }}';
            if (currentType === 'single' && sessionHidden && !sessionHidden.value) {
                e.preventDefault();
                if (sessionSelect) sessionSelect.focus();
                var picker = document.getElementById('single-session-picker');
                if (picker) picker.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Inline message
                var existing = document.getElementById('session-picker-error');
                if (!existing && picker) {
                    var p = document.createElement('p');
                    p.id = 'session-picker-error';
                    p.className = 'text-error text-sm mt-2';
                    p.textContent = 'Please pick a session before continuing.';
                    picker.appendChild(p);
                }
            }
        });
    }

    // Billing period buttons
    document.querySelectorAll('.billing-period-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Highlight
            document.querySelectorAll('.billing-period-btn').forEach(function(b) {
                b.classList.remove('btn-success', 'border-success');
                b.classList.add('btn-ghost', 'border-base-content');
            });
            this.classList.remove('btn-ghost', 'border-base-content');
            this.classList.add('btn-success', 'border-success');

            // Also make sure series is the active type
            var data = {
                class_booking_type: 'series',
                billing_period: this.dataset.period,
                _token: csrfToken
            };

            var seriesBtn = document.querySelector('.booking-type-btn[data-type="series"]');
            updateBookingType(data, seriesBtn);
        });
    });

    function updateBookingType(data, activeBtn) {
        // Update button states
        document.querySelectorAll('.booking-type-btn').forEach(function(b) {
            b.classList.remove('btn-primary');
            b.classList.add('btn-ghost', 'border', 'border-base-300');
        });
        if (activeBtn) {
            activeBtn.classList.remove('btn-ghost', 'border-base-300');
            activeBtn.classList.add('btn-primary');
        }

        // AJAX update session
        fetch('{{ route("booking.process-class-plan-type", ["subdomain" => $host->subdomain, "classPlan" => $item["class_plan_id"] ?? $item["id"] ?? 0]) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success && resp.item) {
                // Update the display
                document.getElementById('booking-item-name').textContent = resp.item.name;
                document.getElementById('booking-item-price').textContent = currencySymbol + parseFloat(resp.item.price).toFixed(2);

                // Update meta
                var meta = document.getElementById('booking-item-meta');
                var type = resp.item.class_booking_type || 'single';
                if (type === 'series') {
                    meta.innerHTML = '<span class="flex items-center gap-1"><span class="icon-[tabler--calendar-repeat] size-4"></span> Series Class</span>' +
                        (resp.item.billing_period ? '<span class="flex items-center gap-1"><span class="icon-[tabler--refresh] size-4"></span> ' + resp.item.billing_period + '</span>' : '');
                } else {
                    meta.innerHTML = '<span class="flex items-center gap-1"><span class="icon-[tabler--calendar-event] size-4"></span> Single Class</span>';
                }

                // Series summary block: populate counts + start/end dates from the
                // server's response, or hide when switching back to Single.
                var summary = document.getElementById('series-summary');
                if (summary) {
                    if (type === 'series' && resp.item.series_summary) {
                        var s = resp.item.series_summary;
                        document.getElementById('series-summary-count').textContent = s.session_count ?? '—';
                        document.getElementById('series-summary-start').textContent = s.start_date || '—';
                        document.getElementById('series-summary-end').textContent = s.end_date || '—';
                        summary.classList.remove('hidden');
                    } else {
                        summary.classList.add('hidden');
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
