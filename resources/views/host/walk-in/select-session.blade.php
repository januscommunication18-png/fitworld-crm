@extends('layouts.dashboard')

@section('title', 'New Booking')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('bookings.index') }}"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">New Booking</li>
    </ol>
@endsection

@push('styles')
<style>
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
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Booking</h1>
            <p class="text-base-content/60">Book a client for a class</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <div>
            <div class="font-medium">Please fix the following errors:</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Session Error --}}
    @if (session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Dynamic Error Container (for JavaScript errors) --}}
    <div id="form-error" class="alert alert-error mb-6 hidden">
        <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
        <span id="form-error-message"></span>
        <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideFormError()">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>

    <form id="booking-form" action="" method="POST">
        @csrf

        {{-- Step 1: Client, Date & Class Selection --}}
        <div id="step-1" class="space-y-6">
            {{-- Client Selection --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--user] size-5"></span>
                        Select Client
                    </h2>

                    {{-- Client Type Selection --}}
                    <div id="client-type-selection" class="grid grid-cols-2 gap-3 mb-6">
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="radio" name="client_type" value="existing" class="radio radio-primary" checked>
                            <span class="icon-[tabler--users] size-6 text-primary"></span>
                            <div>
                                <span class="font-semibold">Existing Client</span>
                                <span class="text-xs text-base-content/60 block">Search client list</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                            <input type="radio" name="client_type" value="new" class="radio radio-success">
                            <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                            <div>
                                <span class="font-semibold">New Client</span>
                                <span class="text-xs text-base-content/60 block">Create new profile</span>
                            </div>
                        </label>
                    </div>

                    {{-- Existing Client Search --}}
                    {{-- Existing Client Search --}}
                    <div id="existing-client-section">
                        <div class="form-control mb-4">
                            <div class="relative">
                                <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                <input type="text"
                                       id="client-search"
                                       class="input input-bordered w-full pl-10"
                                       placeholder="Search by name, email, or phone..."
                                       autocomplete="off">
                                <div id="search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                    <span class="loading loading-spinner loading-sm"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Search Results --}}
                        <div id="search-results" class="hidden mb-4 max-h-64 overflow-y-auto">
                            <div id="search-results-list" class="space-y-2"></div>
                        </div>
                    </div>

                    {{-- Selected Client Display (shown after client selected) --}}
                    <div id="selected-client" class="hidden">
                        <div id="selected-client-alert" class="alert alert-success">
                            <div class="flex items-center gap-3 w-full">
                                <div id="selected-avatar-container">
                                    <div id="selected-avatar-img" class="avatar hidden">
                                        <div class="size-12 rounded-full">
                                            <img id="selected-avatar-src" src="" alt="">
                                        </div>
                                    </div>
                                    <div id="selected-avatar-initials" class="avatar placeholder">
                                        <div id="selected-avatar-circle" class="bg-success-content text-success size-12 rounded-full">
                                            <span id="selected-initials" class="text-lg font-bold">JD</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-lg" id="selected-name">John Doe</span>
                                        <span id="selected-new-badge" class="badge badge-primary badge-sm hidden">New</span>
                                    </div>
                                    <div class="text-sm opacity-80" id="selected-contact">john@example.com</div>
                                </div>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="clearClient()">
                                    <span class="icon-[tabler--x] size-5"></span>
                                    Change
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="client_id" id="client-id" value="">
                    </div>

                    {{-- New Client Form --}}
                    <div id="new-client-section" class="hidden">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label" for="new-first-name"><span class="label-text">First Name <span class="text-error">*</span></span></label>
                                <input type="text" id="new-first-name" name="first_name" class="input input-bordered" placeholder="John">
                            </div>
                            <div class="form-control">
                                <label class="label" for="new-last-name"><span class="label-text">Last Name <span class="text-error">*</span></span></label>
                                <input type="text" id="new-last-name" name="last_name" class="input input-bordered" placeholder="Doe">
                            </div>
                        </div>
                        <div class="form-control mb-4">
                            <label class="label" for="new-email"><span class="label-text">Email</span></label>
                            <input type="email" id="new-email" name="email" class="input input-bordered" placeholder="john@example.com">
                        </div>
                        <div class="form-control mb-4">
                            <label class="label" for="new-phone"><span class="label-text">Phone</span></label>
                            <input type="tel" id="new-phone" name="phone" class="input input-bordered" placeholder="(555) 123-4567">
                        </div>
                        <button type="button" id="add-new-client-btn" class="btn btn-primary w-full" onclick="confirmNewClient()" disabled>
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            Add Client
                        </button>
                    </div>
                </div>
            </div>

            {{-- Class Plan, Date & Session Selection --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        Select Class & Date
                    </h2>

                    {{-- Class Plan Selection --}}
                    <div class="form-control mb-6">
                        <label class="label" for="class-plan-select">
                            <span class="label-text font-medium">Class Type</span>
                        </label>
                        <select id="class-plan-select" name="class_plan_id" class="hidden"
                            data-select='{
                                "placeholder": "Select a class type...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select a class type...</option>
                            @foreach($classPlans as $plan)
                            <option value="{{ $plan->id }}" data-name="{{ $plan->name }}" data-color="{{ $plan->color }}" data-price="{{ $plan->default_price }}">
                                {{ $plan->name }} - ${{ number_format($plan->default_price, 2) }}
                            </option>
                            @endforeach
                        </select>
                        @if($classPlans->isEmpty())
                        <div class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg mt-2">
                            <span class="icon-[tabler--yoga] size-8 text-base-content/30 mx-auto mb-2"></span>
                            <p class="text-base-content/60">No active class plans in catalog</p>
                        </div>
                        @endif
                    </div>

                    {{-- Booking Type Selection (shown after class plan selected) --}}
                    <div class="form-control mb-6 hidden" id="booking-type-selection">
                        <label class="label">
                            <span class="label-text font-medium">Booking Type</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3" id="booking-type-options">
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-primary/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="booking_type" value="single" class="radio radio-primary" checked onchange="selectBookingType('single')">
                                <div>
                                    <div class="font-medium">Single Class</div>
                                    <div class="text-xs text-base-content/60">Book one session</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-success/30 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="booking_type" value="period" class="radio radio-success" onchange="selectBookingType('period')">
                                <div>
                                    <div class="font-medium">Series Class</div>
                                    <div class="text-xs text-base-content/60">Prepay for a billing period</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-warning/30 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all">
                                <input type="radio" name="booking_type" value="trial" class="radio radio-warning" onchange="selectBookingType('trial')">
                                <div>
                                    <div class="font-medium">Trial Class</div>
                                    <div class="text-xs text-base-content/60">Free or discounted first session</div>
                                </div>
                            </label>
                        </div>

                        {{-- Period Selection (shown when "Avail Discount" selected) --}}
                        <div id="period-selection" class="hidden mt-4">
                            <label class="label"><span class="label-text font-medium">Select Period</span></label>
                            <div id="period-options" class="flex gap-2">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Date Selection (shown after class plan selected for single booking) --}}
                    <div class="form-control mb-6 hidden" id="date-selection">
                        <label class="label" for="booking-date">
                            <span class="label-text font-medium">Date</span>
                        </label>
                        <input type="text"
                               id="booking-date"
                               name="booking_date"
                               class="input input-bordered w-full"
                               value="{{ $selectedDate }}"
                               placeholder="Select date...">
                    </div>

                    {{-- Duration (read-only, preloaded from class plan) --}}
                    <div class="form-control mb-6 hidden" id="duration-selection">
                        <label class="label">
                            <span class="label-text font-medium">Duration</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--clock] size-5 text-base-content/50"></span>
                            <span class="font-semibold text-lg" id="duration-display">60</span>
                            <span class="text-base-content/60">minutes</span>
                        </div>
                        <input type="hidden" id="session-duration" value="60">
                    </div>

                    {{-- Schedule Picker (shown for series class with multiple schedules) --}}
                    <div class="form-control hidden mb-6" id="schedule-picker">
                        <label class="label">
                            <span class="label-text font-medium">Select Schedule</span>
                        </label>
                        <div id="schedule-picker-list" class="space-y-2"></div>
                    </div>

                    {{-- Session Selection (shown after date selected) --}}
                    <div class="form-control hidden" id="session-selection">
                        <label class="label">
                            <span class="label-text font-medium">Available Sessions</span>
                        </label>
                        <div id="sessions-loading" class="hidden">
                            <div class="flex items-center justify-center py-8">
                                <span class="loading loading-spinner loading-md"></span>
                                <span class="ml-2 text-base-content/60">Loading sessions...</span>
                            </div>
                        </div>
                        <div id="sessions-empty" class="hidden">
                            <div class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--calendar-off] size-8 text-base-content/30 mx-auto mb-2"></span>
                                <p class="text-base-content/60 mb-4">No sessions for this class on selected date</p>

                                {{-- Next Available --}}
                                <div id="next-available" class="hidden">
                                    <div class="divider text-xs text-base-content/40">UPCOMING DATES</div>
                                    <div id="next-available-dates" class="flex flex-wrap justify-center gap-2"></div>
                                </div>
                            </div>
                        </div>
                        <div id="sessions-list" class="space-y-3 max-h-96 overflow-y-auto">
                            {{-- Sessions loaded via AJAX --}}
                        </div>
                        <input type="hidden" name="session_id" id="session-id" value="">
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="button" class="btn btn-primary" id="step1-next" disabled>
                            Next: Payment
                            <span class="icon-[tabler--arrow-right] size-5"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Period Sessions Modal --}}
        <div id="period-sessions-modal" class="fixed inset-0 z-50 hidden">
            <div class="fixed inset-0 bg-black/50" onclick="closePeriodSessionsModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="bg-base-100 rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] flex flex-col pointer-events-auto relative">
                    <div class="flex items-center justify-between p-4 border-b border-base-200">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                            <span id="period-modal-title">Sessions</span>
                        </h3>
                        <button type="button" class="btn btn-sm btn-circle btn-ghost" onclick="closePeriodSessionsModal()">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        <div id="period-modal-sessions" class="space-y-2"></div>
                    </div>
                    <div class="p-4 border-t border-base-200">
                        <button type="button" class="btn btn-ghost w-full" onclick="closePeriodSessionsModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Payment & Booking --}}
        <div id="step-2" class="card bg-base-100 border border-base-200 hidden">
            <div class="card-body">
                {{-- Booking Summary --}}
                <div class="bg-base-200/50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--user] size-4 text-base-content/50"></span>
                                <span class="font-medium" id="summary-client">--</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--yoga] size-4 text-base-content/50"></span>
                                <span id="summary-class">--</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--calendar] size-4 text-base-content/50"></span>
                                <span class="text-sm text-base-content/70" id="summary-datetime">--</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-primary" id="display-price">$0.00</div>
                            <span id="original-price-display" class="text-sm text-base-content/50 line-through hidden">$0.00</span>
                            <div id="discount-badge" class="hidden mt-1">
                                <span class="badge badge-success badge-sm gap-1">
                                    <span class="icon-[tabler--discount-check] size-3"></span>
                                    <span id="discount-badge-text">Discount</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hidden fields for form submission --}}
                <input type="hidden" name="payment_method" id="payment-method-hidden" value="manual">
                <input type="hidden" name="pack_id" id="class-pass-purchase-id" value="">
                <input type="hidden" name="offer_id" id="offer_id" value="">
                <input type="hidden" name="promo_code" id="promo_code_hidden" value="">
                <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                <input type="hidden" name="price_override_code" id="price_override_code" value="">
                <input type="hidden" name="price_override_amount" id="price_override_amount" value="">
                <input type="hidden" name="billing_period" id="billing_period_hidden" value="">
                <input type="hidden" name="billing_discount_percent" id="billing_discount_percent_hidden" value="0">
                <input type="hidden" name="billing_credit_id" id="billing_credit_id_hidden" value="">
                <input type="hidden" name="include_registration_fee" id="include_reg_fee_hidden" value="1">

                {{-- Sections Container --}}
                <div class="space-y-6">

                    {{-- Section 1: Promo Code (Collapsible) --}}
                    <div class="border border-base-200 rounded-lg" id="promo-section">
                        <button type="button" onclick="togglePromoSection()" class="w-full px-4 py-3 bg-base-200/30 rounded-lg flex items-center justify-between cursor-pointer hover:bg-base-200/50 transition-colors">
                            <div class="flex items-center gap-2 font-medium">
                                <span class="icon-[tabler--discount-2] size-5 text-warning"></span>
                                <span>Promo Code</span>
                                <span class="text-xs text-base-content/50 font-normal">(optional)</span>
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform duration-200" id="promo-chevron"></span>
                        </button>
                        <div class="hidden" id="promo-content">
                            <div class="p-4 border-t border-base-200">
                                <p class="text-xs text-base-content/60 mb-3" id="offers-client-info">
                                    Showing offers available for <span class="font-medium" id="offers-client-name">selected client</span>
                                    <button type="button" onclick="refreshAvailableOffers()" class="btn btn-ghost btn-xs ml-2" title="Refresh offers">
                                        <span class="icon-[tabler--refresh] size-3" id="refresh-offers-icon"></span>
                                    </button>
                                </p>

                                {{-- Applied Offer Display --}}
                                <div id="applied-offer" class="hidden mb-3">
                                    <div class="alert bg-success/10 border-success/20">
                                        <span class="icon-[tabler--discount-check] size-5 text-success"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold text-success" id="applied-offer-name"></span>
                                            <p class="text-sm text-success/80" id="applied-offer-discount"></p>
                                        </div>
                                        <button type="button" onclick="removePromoCode()" class="btn btn-ghost btn-xs btn-circle">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Available Offers --}}
                                <div id="available-offers-section">
                                    <div id="offers-loading" class="hidden py-4 text-center">
                                        <span class="loading loading-spinner loading-sm"></span>
                                        <span class="text-sm text-base-content/60 ml-2">Loading offers...</span>
                                    </div>
                                    <div id="offers-empty" class="hidden text-center py-3 text-base-content/50 text-sm">
                                        No promo codes available
                                    </div>
                                    <div id="offers-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3"></div>
                                </div>

                                {{-- Manual Promo Input --}}
                                <div id="promo-input-section">
                                    <div class="divider text-xs text-base-content/50 my-2">OR ENTER CODE</div>
                                    <div class="join w-full">
                                        <input type="text" id="promo_code_input" placeholder="Enter promo code" class="input input-bordered input-sm join-item flex-1 uppercase" maxlength="20">
                                        <button type="button" onclick="applyPromoCode()" id="apply-promo-btn" class="btn btn-primary btn-sm join-item">Apply</button>
                                    </div>
                                    <p id="promo-error" class="text-error text-sm mt-2 hidden"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2.5: Billing Period Discount (collapsible) --}}
                    <div class="hidden" id="billing-discount-section">
                        <details class="group border border-base-200 rounded-lg">
                            <summary class="flex items-center justify-between px-4 py-3 bg-base-200/30 rounded-lg cursor-pointer hover:bg-base-200/50 transition-colors list-none">
                                <div class="flex items-center gap-2 font-medium">
                                    <span class="icon-[tabler--calendar-dollar] size-5 text-success"></span>
                                    <span>Billing Period Discount</span>
                                    <span class="text-xs text-base-content/50 font-normal">(optional)</span>
                                </div>
                                <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform duration-200 group-open:rotate-180"></span>
                            </summary>
                            <div class="p-4 border-t border-base-200">
                                {{-- Applied Discount Display --}}
                                <div id="applied-billing-discount" class="hidden mb-3">
                                    <div class="alert bg-success/10 border-success/20">
                                        <span class="icon-[tabler--calendar-check] size-5 text-success"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold text-success" id="applied-billing-label"></span>
                                            <p class="text-sm text-success/80" id="applied-billing-savings"></p>
                                        </div>
                                        <button type="button" onclick="removeBillingDiscount()" class="btn btn-ghost btn-xs btn-circle">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </div>
                                </div>

                                <p class="text-sm text-base-content/60 mb-3">Client pays upfront for a billing period. Credit applies to future bookings.</p>
                                <div id="billing-discount-options" class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                    {{-- Options populated by JS --}}
                                </div>
                            </div>
                        </details>
                    </div>

                    {{-- Section 2.75: Price Override --}}
                    @if($canOverridePrice ?? false)
                    {{-- Direct price edit for managers/owners with override permission --}}
                    <div class="border border-base-200 rounded-lg" id="override-section">
                        <button type="button" onclick="toggleOverrideSection()" class="w-full px-4 py-3 bg-base-200/30 rounded-lg flex items-center justify-between cursor-pointer hover:bg-base-200/50 transition-colors">
                            <div class="flex items-center gap-2 font-medium">
                                <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                                <span>Price Override</span>
                                <span class="text-xs text-base-content/50 font-normal">(optional)</span>
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform duration-200" id="override-chevron"></span>
                        </button>
                        <div class="hidden" id="override-content">
                            <div class="p-4 border-t border-base-200">
                                {{-- Applied Override Display --}}
                                <div id="applied-override" class="hidden mb-3">
                                    <div class="alert bg-primary/10 border-primary/20">
                                        <span class="icon-[tabler--check] size-5 text-primary"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold text-primary" id="applied-override-code">Direct Override</span>
                                            <p class="text-sm text-primary/80" id="applied-override-price"></p>
                                        </div>
                                        <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Direct Override Input for Managers/Owners --}}
                                <div id="override-input-section">
                                    <div class="form-control">
                                        <label class="label" for="direct-override-price">
                                            <span class="label-text">Override Price</span>
                                            <span class="label-text-alt text-success">You have override permission</span>
                                        </label>
                                        <div class="join w-full">
                                            <span class="join-item btn btn-sm no-animation">$</span>
                                            <input type="number" step="0.01" min="0" id="direct-override-price"
                                                   class="input input-bordered input-sm join-item flex-1"
                                                   placeholder="Enter new price...">
                                            <button type="button" onclick="applyDirectOverride()" class="btn btn-primary btn-sm join-item">
                                                Apply
                                            </button>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Enter a price to override the session price directly.</p>
                                    </div>
                                </div>

                                <p id="override-error" class="text-error text-sm mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>
                    @elseif($canRequestOverride ?? false)
                    {{-- Override request flow for staff without override permission --}}
                    <div class="border border-base-200 rounded-lg" id="override-section">
                        <button type="button" onclick="toggleOverrideSection()" class="w-full px-4 py-3 bg-base-200/30 rounded-lg flex items-center justify-between cursor-pointer hover:bg-base-200/50 transition-colors">
                            <div class="flex items-center gap-2 font-medium">
                                <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                                <span>Price Override</span>
                                <span class="text-xs text-base-content/50 font-normal">(optional)</span>
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform duration-200" id="override-chevron"></span>
                        </button>
                        <div class="hidden" id="override-content">
                            <div class="p-4 border-t border-base-200">
                                {{-- Applied Override Display --}}
                                <div id="applied-override" class="hidden mb-3">
                                    <div class="alert bg-primary/10 border-primary/20">
                                        <span class="icon-[tabler--check] size-5 text-primary"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold text-primary" id="applied-override-code"></span>
                                            <p class="text-sm text-primary/80" id="applied-override-price"></p>
                                        </div>
                                        <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Override Input Section --}}
                                <div id="override-input-section">
                                    {{-- Code Entry with Fetch & Verify --}}
                                    <div class="form-control mb-3">
                                        <label class="label" for="override_code_input">
                                            <span class="label-text text-xs">Enter code (PO-XXXXX or MY-XXXXX) or fetch approved override</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="text" id="override_code_input" placeholder="PO-XXXXX or MY-XXXXX"
                                                   class="input input-bordered input-sm flex-1 uppercase" maxlength="10">
                                            <button type="button" onclick="verifyOverrideCode()" id="verify-override-btn"
                                                    class="btn btn-sm btn-outline">
                                                Verify
                                            </button>
                                        </div>
                                    </div>

                                    <div class="divider text-xs my-2">OR</div>

                                    {{-- Request New Override --}}
                                    <button type="button" onclick="showOverrideModal()" class="btn btn-outline btn-primary btn-sm btn-block">
                                        <span class="icon-[tabler--send] size-4"></span>
                                        Request New Override
                                    </button>
                                </div>

                                {{-- Pending Override Status --}}
                                <div id="override-pending" class="hidden">
                                    <div class="alert bg-warning/10 border-warning/20">
                                        <span class="icon-[tabler--clock] size-5 text-warning animate-pulse"></span>
                                        <div class="flex-1">
                                            <span class="font-semibold text-warning">Pending Approval</span>
                                            <p class="text-sm text-warning/80">
                                                Code: <span id="pending-code" class="font-mono font-bold"></span>
                                            </p>
                                            <p class="text-xs text-warning/60" id="pending-expires"></p>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <button type="button" onclick="checkOverrideStatus()" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--refresh] size-4"></span>
                                                Check
                                            </button>
                                            <button type="button" onclick="cancelOverrideRequest()" class="btn btn-ghost btn-xs text-error">
                                                <span class="icon-[tabler--x] size-4"></span>
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <p id="override-error" class="text-error text-sm mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Price Override Modals - Only show when feature is enabled --}}
                    @if($canRequestOverride ?? false)
                    {{-- Price Override Request Modal (div-based) --}}
                    <div id="override-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
                        <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeOverrideModal()"></div>
                        <div class="modal-box fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                            <button type="button" onclick="closeOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                                <span class="icon-[tabler--x] size-5"></span>
                            </button>
                            <h3 class="font-bold text-lg mb-4">
                                <span class="icon-[tabler--receipt-refund] size-5 mr-2"></span>
                                Request Price Override
                            </h3>

                            <div class="space-y-4">
                                <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                    <span class="text-base-content/60">Original Price</span>
                                    <span class="font-semibold" id="modal-original-price">$0.00</span>
                                </div>

                                <div class="form-control">
                                    <label class="label" for="override-new-price">
                                        <span class="label-text">New Price *</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                        <input type="number" id="override-new-price" step="0.01" min="0"
                                               class="input input-bordered w-full pl-8" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="form-control">
                                    <label class="label" for="override-discount-code">
                                        <span class="label-text">Discount Code (optional)</span>
                                    </label>
                                    <input type="text" id="override-discount-code" class="input input-bordered"
                                           placeholder="e.g., SPECIAL50">
                                </div>

                                <div class="form-control">
                                    <label class="label" for="override-reason">
                                        <span class="label-text">Reason (optional)</span>
                                    </label>
                                    <textarea id="override-reason" rows="2" class="textarea textarea-bordered"
                                              placeholder="Reason for the price override..."></textarea>
                                </div>

                                {{-- Discount Preview --}}
                                <div id="override-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-success/80">Discount Amount</span>
                                        <span class="font-semibold text-success" id="preview-discount">$0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm mt-1">
                                        <span class="text-success/80">Discount Percentage</span>
                                        <span class="font-semibold text-success" id="preview-percent">0%</span>
                                    </div>
                                </div>

                                <p id="modal-error" class="text-error text-sm hidden"></p>
                            </div>

                            <div class="flex justify-end gap-2 mt-6">
                                <button type="button" onclick="closeOverrideModal()" class="btn btn-ghost">Cancel</button>
                                <button type="button" onclick="submitOverrideRequest()" id="submit-override-btn" class="btn btn-primary">
                                    <span class="icon-[tabler--send] size-4"></span>
                                    Send Request
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Personal Override Modal (when manager enters their code for staff) --}}
                    <div id="personal-override-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
                        <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closePersonalOverrideModal()"></div>
                        <div class="modal-box fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                            <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                                <span class="icon-[tabler--x] size-5"></span>
                            </button>
                            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                                Override Price
                            </h3>

                            {{-- Supervised By Info --}}
                            <div class="alert alert-success mb-4">
                                <span class="icon-[tabler--user-check] size-5"></span>
                                <div>
                                    <p class="font-semibold">Supervised by</p>
                                    <p class="text-sm"><span id="personal-supervisor-name">Manager</span> (<span id="personal-supervisor-code">MY-XXXXX</span>)</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                    <span class="text-base-content/60">Original Price</span>
                                    <span class="font-semibold" id="personal-modal-original-price">$0.00</span>
                                </div>

                                <div class="form-control">
                                    <label class="label" for="personal-override-new-price">
                                        <span class="label-text">New Price *</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                        <input type="number" id="personal-override-new-price" step="0.01" min="0"
                                               class="input input-bordered w-full pl-8" placeholder="0.00"
                                               oninput="updatePersonalOverridePreview()">
                                    </div>
                                </div>

                                {{-- Discount Preview --}}
                                <div id="personal-override-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-success/80">Discount Amount</span>
                                        <span class="font-semibold text-success" id="personal-preview-discount">$0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm mt-1">
                                        <span class="text-success/80">Discount Percentage</span>
                                        <span class="font-semibold text-success" id="personal-preview-percent">0%</span>
                                    </div>
                                </div>

                                <p id="personal-modal-error" class="text-error text-sm hidden"></p>
                            </div>

                            <div class="flex justify-end gap-2 mt-6">
                                <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-ghost">Cancel</button>
                                <button type="button" onclick="applyPersonalOverride()" id="apply-personal-override-btn" class="btn btn-success">
                                    <span class="icon-[tabler--check] size-4"></span>
                                    Apply Override
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Section 3: Amount & Payment Method --}}
                    <div class="border border-base-200 rounded-lg">
                        <div class="px-4 py-3 bg-base-200/30 border-b border-base-200 rounded-t-lg">
                            <div class="flex items-center gap-2 font-medium">
                                <span class="icon-[tabler--cash] size-5 text-success"></span>
                                <span>Amount & Payment Method</span>
                            </div>
                        </div>
                        <div class="p-4">
                            {{-- Series Class Price Breakdown (shown for series bookings) --}}
                            <div id="series-price-breakdown" class="hidden mb-4">
                                <div class="bg-base-200/30 rounded-lg p-4 space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-base-content/60">Base price per session</span>
                                        <span class="font-medium" id="series-base-price">$0.00</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-base-content/60">Total sessions</span>
                                        <span class="font-medium" id="series-session-count">0</span>
                                    </div>
                                    <div class="divider my-1"></div>
                                    <div class="flex items-center justify-between font-semibold">
                                        <span>Total (without discount)</span>
                                        <span id="series-total-no-discount">$0.00</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Registration Fee (shown when billing period selected) --}}
                            <div id="reg-fee-container" class="hidden mb-4">
                                <label class="flex items-center justify-between gap-3 p-3 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-200/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="reg-fee-checkbox" class="checkbox checkbox-primary checkbox-sm" checked onchange="toggleRegFee()">
                                        <div>
                                            <span class="font-medium text-sm">Include Registration Fee</span>
                                            <span class="text-xs text-base-content/60 block">One-time fee for billing period signup</span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-primary" id="reg-fee-amount">$0.00</span>
                                </label>
                            </div>

                            {{-- Apply Discount Checkbox (shown for series bookings) --}}
                            <div id="apply-discount-container" class="hidden mb-4">
                                <label class="flex items-center justify-between gap-3 p-3 border border-success/20 rounded-lg cursor-pointer hover:bg-success/5 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="apply-discount-checkbox" class="checkbox checkbox-success checkbox-sm" onchange="toggleSeriesDiscount()">
                                        <div>
                                            <span class="font-medium text-sm">Apply Billing Period Discount</span>
                                            <span class="text-xs text-base-content/60 block" id="apply-discount-desc">Apply discounted rate for this period</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-success" id="apply-discount-amount">$0.00</span>
                                        <span class="text-xs text-success block" id="apply-discount-savings">Save $0.00</span>
                                    </div>
                                </label>
                            </div>

                            {{-- Price Input --}}
                            <div class="form-control mb-4" id="price-input-container">
                                <label class="label" for="price-input">
                                    <span class="label-text font-medium">Amount to Charge</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">$</span>
                                    <input type="number" id="price-input" name="price_paid" step="0.01" min="0"
                                           class="input input-bordered w-full pl-8"
                                           value="0">
                                </div>
                            </div>

                            {{-- Trial Amount (hidden by default) --}}
                            <div id="trial-amount-container" class="hidden mb-4">
                                <div class="form-control">
                                    <label class="label" for="trial-amount">
                                        <span class="label-text font-medium">Trial Amount</span>
                                        <span class="label-text-alt text-base-content/50">Enter 0 for free trial</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">$</span>
                                        <input type="number" id="trial-amount" step="0.01" min="0" class="input input-bordered w-full pl-8" value="0" placeholder="0.00">
                                    </div>
                                </div>
                                <div id="free-trial-badge" class="mt-2">
                                    <span class="badge badge-success gap-1">
                                        <span class="icon-[tabler--discount-check] size-4"></span>
                                        Free Trial
                                    </span>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div class="form-control" id="payment-method-container">
                                <label class="label">
                                    <span class="label-text font-medium">Payment Method</span>
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="flex flex-col items-center gap-2 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="manual_method" value="cash" class="radio radio-primary radio-sm" checked>
                                        <span class="icon-[tabler--cash] size-5 text-success"></span>
                                        <span class="text-sm font-medium">Cash</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-2 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="manual_method" value="card" class="radio radio-primary radio-sm">
                                        <span class="icon-[tabler--credit-card] size-5 text-info"></span>
                                        <span class="text-sm font-medium">Card</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-2 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="manual_method" value="check" class="radio radio-primary radio-sm">
                                        <span class="icon-[tabler--file-invoice] size-5 text-warning"></span>
                                        <span class="text-sm font-medium">Check</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-2 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="manual_method" value="other" class="radio radio-primary radio-sm">
                                        <span class="icon-[tabler--dots] size-5 text-base-content/50"></span>
                                        <span class="text-sm font-medium">Other</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden trial checkbox for form submission --}}
                    <input type="checkbox" id="trial-class" name="is_trial" value="1" class="hidden">

                    {{-- Section 4: Additional Options --}}
                    <div class="border border-base-200 rounded-lg">
                        <div class="px-4 py-3 bg-base-200/30 border-b border-base-200 rounded-t-lg">
                            <div class="flex items-center gap-2 font-medium">
                                <span class="icon-[tabler--settings] size-5 text-base-content/70"></span>
                                <span>Additional Options</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-4">
                            {{-- Intake Form --}}
                            <div id="intake-form-section" class="hidden">
                                <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-info has-[:checked]:bg-info/5 transition-all">
                                    <input type="checkbox" id="send-intake-form" name="send_intake_form" value="1" class="checkbox checkbox-info checkbox-sm">
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">Send Intake Form</span>
                                        <span class="text-xs text-base-content/60 block">Email questionnaire(s) to client</span>
                                    </div>
                                </label>

                                <div id="questionnaire-selection" class="hidden mt-3 p-3 bg-base-200/50 rounded-lg">
                                    <div class="text-sm font-medium text-base-content/70 mb-2">Select questionnaires:</div>
                                    <div id="questionnaires-loading" class="hidden flex items-center justify-center py-3">
                                        <span class="loading loading-spinner loading-sm"></span>
                                    </div>
                                    <div id="questionnaires-empty" class="hidden text-center py-3 text-sm text-base-content/50">
                                        No questionnaires attached
                                    </div>
                                    <div id="no-email-warning" class="hidden alert alert-warning alert-sm mb-2">
                                        <span class="icon-[tabler--alert-triangle] size-4"></span>
                                        <span class="text-xs">Client has no email</span>
                                    </div>
                                    <div id="questionnaires-list" class="space-y-2"></div>
                                </div>
                            </div>

                            {{-- Check in now --}}
                            <div id="check-in-now-container" class="hidden">
                                <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                    <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary checkbox-sm" id="check-in-now-checkbox">
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">Check in now</span>
                                        <span class="text-xs text-base-content/60 block">Mark as arrived immediately</span>
                                    </div>
                                </label>
                            </div>

                            {{-- Notes --}}
                            <div class="form-control">
                                <label class="label" for="notes">
                                    <span class="label-text font-medium text-sm">Notes (optional)</span>
                                </label>
                                <textarea id="notes" name="notes" rows="2" class="textarea textarea-bordered textarea-sm" placeholder="Any notes about this booking..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="btn btn-ghost" id="step2-back">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                        Back
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span class="icon-[tabler--check] size-5"></span>
                        Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Quick Create Session Modal --}}
<div id="quick-create-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 hidden [&.open]:opacity-100" role="dialog" tabindex="-1">
    <div class="absolute inset-0 bg-base-content/50" onclick="closeQuickCreateModal()"></div>
    <div class="modal-dialog relative z-10 w-full max-w-5xl transform scale-95 transition-transform duration-300 [.open_&]:scale-100">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <span class="icon-[tabler--calendar-plus] size-5 inline-block align-middle mr-2"></span>
                    Quick Create Session
                </h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeQuickCreateModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body min-h-[500px]">
                {{-- Modal Error Container --}}
                <div id="modal-error" class="alert alert-error mb-4 hidden">
                    <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
                    <span id="modal-error-message"></span>
                    <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideModalError()">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Left Column: Form Fields --}}
                    <div class="space-y-5">
                        {{-- Selected Class, Date & Duration Display --}}
                        <div class="bg-base-200 rounded-lg p-3">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-lg flex items-center justify-center" id="modal-class-color" style="background-color: #6366f1;">
                                    <span class="icon-[tabler--yoga] size-5 text-white"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold" id="modal-class-name">Class Name</div>
                                    <div class="text-sm text-base-content/60" id="modal-date">Date</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-primary" id="modal-duration">60</div>
                                    <div class="text-xs text-base-content/60">minutes</div>
                                </div>
                            </div>
                        </div>

                        {{-- Primary Instructor --}}
                        <div class="form-control">
                            <label class="label" for="quick-primary-instructor">
                                <span class="label-text font-medium">Primary Instructor <span class="text-error">*</span></span>
                            </label>
                            <select id="quick-primary-instructor" class="select select-bordered w-full" required>
                                <option value="">Select an instructor...</option>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Available Time Slots --}}
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Available Time Slots <span class="text-error">*</span></span>
                            </label>

                            {{-- Placeholder when no instructor selected --}}
                            <div id="slots-placeholder" class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--clock] size-8 text-base-content/30 mx-auto mb-2"></span>
                                <p class="text-base-content/50 text-sm">Select an instructor to see available slots</p>
                            </div>

                            {{-- Loading state --}}
                            <div id="slots-loading" class="hidden text-center py-6">
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <p class="text-base-content/50 text-sm mt-2">Calculating available slots...</p>
                            </div>

                            {{-- No slots available --}}
                            <div id="slots-empty" class="hidden text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--calendar-off] size-8 text-error/50 mx-auto mb-2"></span>
                                <p class="text-base-content/50 text-sm">No available slots for this duration</p>
                            </div>

                            {{-- Time Slots with Tabs --}}
                            <div id="slots-container" class="hidden">
                                {{-- Tabs --}}
                                <div class="flex gap-1 mb-3">
                                    <button type="button" class="slot-tab btn btn-sm btn-primary flex-1" data-tab="morning" onclick="switchSlotTab('morning')">
                                        <span class="icon-[tabler--sunrise] size-4"></span>
                                        Morning
                                        <span id="morning-count" class="badge badge-xs">0</span>
                                    </button>
                                    <button type="button" class="slot-tab btn btn-sm btn-outline flex-1" data-tab="afternoon" onclick="switchSlotTab('afternoon')">
                                        <span class="icon-[tabler--sun] size-4"></span>
                                        Afternoon
                                        <span id="afternoon-count" class="badge badge-xs">0</span>
                                    </button>
                                    <button type="button" class="slot-tab btn btn-sm btn-outline flex-1" data-tab="evening" onclick="switchSlotTab('evening')">
                                        <span class="icon-[tabler--moon] size-4"></span>
                                        Evening
                                        <span id="evening-count" class="badge badge-xs">0</span>
                                    </button>
                                </div>

                                {{-- Slot Grids --}}
                                <div id="morning-slots" class="slot-panel grid grid-cols-4 gap-2 max-h-48 overflow-y-auto"></div>
                                <div id="afternoon-slots" class="slot-panel hidden grid grid-cols-4 gap-2 max-h-48 overflow-y-auto"></div>
                                <div id="evening-slots" class="slot-panel hidden grid grid-cols-4 gap-2 max-h-48 overflow-y-auto"></div>

                                {{-- No slots in selected tab --}}
                                <div id="tab-empty" class="hidden text-center py-4 text-base-content/50 text-sm">
                                    No slots available in this time period
                                </div>
                            </div>

                            <input type="hidden" id="quick-start-time" value="">
                        </div>

                        {{-- Capacity (readonly - from class plan) --}}
                        <div class="form-control">
                            <label class="label" for="quick-capacity">
                                <span class="label-text font-medium">Capacity</span>
                            </label>
                            <input type="number" id="quick-capacity" class="input input-bordered w-full bg-base-200" value="10" readonly>
                        </div>
                    </div>

                    {{-- Right Column: Instructor Availability --}}
                    <div class="border-l border-base-300 pl-6">
                        <h4 class="font-semibold text-sm text-base-content/70 mb-3">Instructor Availability</h4>

                        {{-- Placeholder when no instructor selected --}}
                        <div id="availability-placeholder" class="flex flex-col items-center justify-center h-96 text-center">
                            <span class="icon-[tabler--calendar-user] size-12 text-base-content/20 mb-3"></span>
                            <p class="text-base-content/50 text-sm">Select an instructor to view<br>their availability</p>
                        </div>

                        {{-- Loading state --}}
                        <div id="availability-loading" class="hidden flex flex-col items-center justify-center h-96">
                            <span class="loading loading-spinner loading-md text-primary"></span>
                            <p class="text-base-content/50 text-sm mt-2">Loading availability...</p>
                        </div>

                        {{-- Availability Panel --}}
                        <div id="availability-panel" class="hidden space-y-4">
                            {{-- Instructor Info --}}
                            <div class="flex items-center gap-3">
                                <div id="avail-avatar" class="avatar placeholder">
                                    <div class="bg-primary text-primary-content size-10 rounded-full">
                                        <span id="avail-initials" class="text-sm font-bold">JS</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold" id="avail-name">John Smith</div>
                                    <div class="text-xs text-base-content/60" id="avail-date">Monday, Feb 16, 2026</div>
                                </div>
                            </div>

                            {{-- Working Days --}}
                            <div>
                                <div class="text-xs font-medium text-base-content/60 mb-2">Working Days</div>
                                <div class="flex gap-1" id="avail-working-days">
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-base-200 text-base-content/40">S</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-success/20 text-success">M</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-success/20 text-success">T</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-success/20 text-success">W</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-success/20 text-success">T</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-success/20 text-success">F</span>
                                    <span class="size-8 rounded text-xs font-medium flex items-center justify-center bg-base-200 text-base-content/40">S</span>
                                </div>
                            </div>

                            {{-- Today's Hours --}}
                            <div>
                                <div class="text-xs font-medium text-base-content/60 mb-2">Today's Hours</div>
                                <div id="avail-hours-status">
                                    {{-- Will show either warning or success based on availability --}}
                                </div>
                            </div>

                            {{-- Existing Sessions --}}
                            <div>
                                <div class="text-xs font-medium text-base-content/60 mb-2">Existing Sessions Today</div>
                                <div id="avail-existing-sessions" class="space-y-1 max-h-36 overflow-y-auto">
                                    {{-- Sessions will be populated here --}}
                                </div>
                            </div>

                            {{-- Weekly Workload --}}
                            <div>
                                <div class="text-xs font-medium text-base-content/60 mb-2">Weekly Workload</div>
                                <div class="grid grid-cols-2 gap-2" id="avail-workload">
                                    <div class="bg-base-200 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold"><span id="avail-classes-count">0</span><span class="text-base-content/40" id="avail-classes-max">/10</span></div>
                                        <div class="text-xs text-base-content/60">Classes</div>
                                    </div>
                                    <div class="bg-base-200 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold"><span id="avail-hours-count">0</span><span class="text-base-content/40" id="avail-hours-max">/20</span></div>
                                        <div class="text-xs text-base-content/60">Hours</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft btn-secondary" onclick="closeQuickCreateModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="quick-create-btn" onclick="submitQuickCreate()">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create & Select
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Form error display functions (global)
function showFormError(message) {
    const errorDiv = document.getElementById('form-error');
    const errorMsg = document.getElementById('form-error-message');
    errorMsg.textContent = message;
    errorDiv.classList.remove('hidden');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideFormError() {
    document.getElementById('form-error').classList.add('hidden');
}

// Modal error display functions
function showModalError(message) {
    const errorDiv = document.getElementById('modal-error');
    const errorMsg = document.getElementById('modal-error-message');
    if (errorDiv && errorMsg) {
        errorMsg.textContent = message;
        errorDiv.classList.remove('hidden');
    }
}

function hideModalError() {
    const errorDiv = document.getElementById('modal-error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
}

// Toggle promo code section
function togglePromoSection() {
    const content = document.getElementById('promo-content');
    const chevron = document.getElementById('promo-chevron');

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle price override section
function toggleOverrideSection() {
    const content = document.getElementById('override-content');
    const chevron = document.getElementById('override-chevron');

    if (!content) return;

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentStep = 1;
    let selectedClientId = null;
    let selectedClientName = '';
    let selectedClientEmail = '';
    let selectedSessionId = null;
    let selectedSessionData = null;
    let isNewClient = false;
    let availableQuestionnaires = [];
    let selectedBillingPeriod = null;

    // ========== Booking Type & Schedule Selection ==========
    var selectedBookingType = 'single';
    var selectedPeriodMonths = null;

    window.selectBookingType = function(type) {
        selectedBookingType = type;
        var dateSelection = document.getElementById('date-selection');
        var durationSelection = document.getElementById('duration-selection');
        var sessionSelection = document.getElementById('session-selection');
        var periodSelection = document.getElementById('period-selection');
        var schedulePicker = document.getElementById('schedule-picker');

        // Reset session
        selectedSessionId = null;
        selectedSessionData = null;
        document.getElementById('session-id').value = '';
        document.getElementById('sessions-list').innerHTML = '';
        sessionSelection.classList.add('hidden');
        schedulePicker.classList.add('hidden');
        selectedPeriodMonths = null;
        _selectedScheduleParentIds = [];

        if (type === 'single' || type === 'trial') {
            periodSelection.classList.add('hidden');
            dateSelection.classList.remove('hidden');
            validateStep1();
        } else if (type === 'period') {
            dateSelection.classList.add('hidden');
            durationSelection.classList.add('hidden');
            sessionSelection.classList.add('hidden');
            buildPeriodOptions();
            periodSelection.classList.remove('hidden');
            validateStep1();
        }
    };

    function buildPeriodOptions() {
        fetch('/walk-in/class-plan-defaults?class_plan_id=' + selectedClassPlanId)
            .then(function(r) { return r.json(); })
            .then(function(defaults) {
                renderPeriodOptions(defaults.billing_discounts || {});
            })
            .catch(function() { renderPeriodOptions({}); });
    }

    function renderPeriodOptions(discounts) {
        var container = document.getElementById('period-options');
        var periods = { '1': '1 Month', '3': '3 Months', '6': '6 Months', '9': '9 Months', '12': '12 Months' };
        var html = '';

        Object.keys(periods).forEach(function(months) {
            var totalAmount = parseFloat(discounts[months]) || 0;
            if (totalAmount <= 0) return;

            var m = parseInt(months);
            var monthlyRate = m > 0 ? (totalAmount / m) : 0;

            html += '<button type="button" class="period-option-btn flex-1 flex flex-col items-center p-3 rounded-lg border-2 border-base-content/10 hover:border-success cursor-pointer transition-all" ' +
                'data-months="' + months + '" onclick="selectPeriod(' + months + ')">' +
                '<div class="text-xs text-base-content/60 font-medium">' + periods[months] + '</div>' +
                '<div class="text-lg font-bold text-success">$' + totalAmount.toFixed(2) + '</div>' +
                '<div class="text-[10px] text-base-content/50">$' + monthlyRate.toFixed(2) + '/mo</div>' +
                '</button>';
        });

        if (!html) {
            html = '<div class="col-span-full text-center py-4 text-base-content/50 text-sm">No billing period discounts configured for this class.</div>';
        }

        container.innerHTML = html;
    }

    var _allPeriodSessionsData = []; // All sessions for the period
    var _periodSessionsData = []; // Filtered sessions (for modal and pricing)
    var _scheduleOptions = [];
    var _selectedScheduleParentIds = [];
    var _periodMeta = {};

    window.selectPeriod = function(months) {
        selectedPeriodMonths = months;

        // Highlight selected period
        document.querySelectorAll('.period-option-btn').forEach(function(btn) {
            if (parseInt(btn.dataset.months) === months) {
                btn.classList.remove('border-base-content/10');
                btn.classList.add('border-success', 'bg-success/5');
            } else {
                btn.classList.remove('border-success', 'bg-success/5');
                btn.classList.add('border-base-content/10');
            }
        });

        // Reset
        var sessionSelection = document.getElementById('session-selection');
        var sessionsLoading = document.getElementById('sessions-loading');
        var sessionsEmpty = document.getElementById('sessions-empty');
        var sessionsList = document.getElementById('sessions-list');
        var schedulePicker = document.getElementById('schedule-picker');

        schedulePicker.classList.add('hidden');
        sessionSelection.classList.remove('hidden');
        sessionsLoading.classList.remove('hidden');
        sessionsEmpty.classList.add('hidden');
        sessionsList.innerHTML = '';
        _selectedScheduleParentIds = [];

        // Fetch both schedules and sessions in parallel
        Promise.all([
            fetch('/walk-in/class-schedules?class_plan_id=' + selectedClassPlanId + '&months=' + months).then(function(r) { return r.json(); }),
            fetch('/walk-in/sessions-range?class_plan_id=' + selectedClassPlanId + '&months=' + months).then(function(r) { return r.json(); })
        ]).then(function(results) {
            var scheduleData = results[0];
            var sessionData = results[1];
            sessionsLoading.classList.add('hidden');

            _scheduleOptions = scheduleData.schedules || [];
            _allPeriodSessionsData = sessionData.sessions || [];
            _periodMeta = { total: sessionData.total, period_start: sessionData.period_start, period_end: sessionData.period_end };

            if (_allPeriodSessionsData.length === 0) {
                sessionsEmpty.classList.remove('hidden');
                _periodSessionsData = [];
                selectedSessionId = null;
                selectedSessionData = null;
                validateStep1();
                return;
            }

            // If only 1 schedule or no schedules — auto-select all, skip picker
            if (_scheduleOptions.length <= 1) {
                _selectedScheduleParentIds = _scheduleOptions.length === 1 ? [_scheduleOptions[0].parent_id] : [];
                _periodSessionsData = _allPeriodSessionsData;
                autoSelectFirstSession();
                showSessionSummary();
                validateStep1();
                return;
            }

            // Multiple schedules — show picker
            var pickerHtml = '';
            _scheduleOptions.forEach(function(sched) {
                var titleLine = sched.title ? '<div class="font-semibold">' + sched.title + '</div>' : '';
                pickerHtml += '<label class="schedule-card flex items-center gap-3 p-4 rounded-lg border-2 border-base-content/10 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">' +
                    '<input type="checkbox" class="checkbox checkbox-primary" data-parent-id="' + sched.parent_id + '" onchange="toggleSchedule(this)">' +
                    '<div class="flex-1">' +
                    titleLine +
                    '<div class="' + (sched.title ? 'text-sm text-base-content/70' : 'font-semibold') + '">' + sched.label + ' · ' + sched.time + '</div>' +
                    '<div class="text-xs text-base-content/50">' + sched.instructor + (sched.location ? ' · ' + sched.location : '') +
                    (sched.last_session_date ? ' · Last session: ' + sched.last_session_date : '') + '</div>' +
                    '</div>' +
                    '<span class="badge badge-soft badge-primary shrink-0">' + sched.session_count + ' sessions</span>' +
                    '</label>';
            });

            document.getElementById('schedule-picker-list').innerHTML = pickerHtml;
            schedulePicker.classList.remove('hidden');
            sessionSelection.classList.add('hidden');
            validateStep1();
        }).catch(function() { sessionsLoading.classList.add('hidden'); });
    };

    function autoSelectFirstSession() {
        if (_periodSessionsData.length === 0) return;
        var first = _periodSessionsData[0];
        selectedSessionId = first.id;
        document.getElementById('session-id').value = first.id;

        var billingDiscounts = first.billing_discounts || {};
        selectedSessionData = {
            title: selectedClassPlanName,
            time: first.time,
            price: parseFloat(first.price) || 0,
            startTime: first.start_time_iso,
            endTime: first.end_time_iso,
            billingDiscounts: billingDiscounts,
            registrationFee: parseFloat(first.registration_fee) || 0,
            cancellationFee: parseFloat(first.cancellation_fee) || 0,
            graceHours: parseInt(first.cancellation_grace_hours) || 48
        };
    }

    function showSessionSummary() {
        var sessionsList = document.getElementById('sessions-list');
        var durationVal = document.getElementById('session-duration').value || '60';
        var html = '<div class="flex items-center justify-between p-4 bg-success/5 border border-success/20 rounded-lg">' +
            '<div class="flex items-center gap-3">' +
            '<div class="flex items-center justify-center size-10 rounded-full bg-success/10">' +
            '<span class="icon-[tabler--calendar-check] size-5 text-success"></span></div>' +
            '<div>' +
            '<div class="font-semibold">' + _periodSessionsData.length + ' Available Sessions · ' + durationVal + ' min each</div>' +
            '<div class="text-sm text-base-content/60">' + (_periodMeta.period_start || '') + ' to ' + (_periodMeta.period_end || '') + '</div>' +
            '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-ghost btn-sm gap-1" onclick="openPeriodSessionsModal()">' +
            '<span class="icon-[tabler--eye] size-4"></span> View All' +
            '</button>' +
            '</div>';
        sessionsList.innerHTML = html;
        document.getElementById('session-selection').classList.remove('hidden');
    }

    window.toggleSchedule = function(checkbox) {
        var parentId = checkbox.dataset.parentId;
        // Parse as number if numeric
        var pid = isNaN(parentId) ? parentId : parseInt(parentId);

        if (checkbox.checked) {
            if (_selectedScheduleParentIds.indexOf(pid) === -1) _selectedScheduleParentIds.push(pid);
        } else {
            _selectedScheduleParentIds = _selectedScheduleParentIds.filter(function(id) { return id !== pid; });
        }

        applyScheduleFilter();
    };

    function applyScheduleFilter() {
        if (_selectedScheduleParentIds.length === 0) {
            _periodSessionsData = [];
            selectedSessionId = null;
            selectedSessionData = null;
            document.getElementById('session-selection').classList.add('hidden');
            validateStep1();
            return;
        }

        // Filter sessions by selected schedule parent IDs
        _periodSessionsData = _allPeriodSessionsData.filter(function(session) {
            var pid = session.recurrence_parent_id;
            if (pid === null || pid === undefined) {
                return _selectedScheduleParentIds.indexOf('oneoff') !== -1;
            }
            return _selectedScheduleParentIds.indexOf(pid) !== -1;
        });

        autoSelectFirstSession();
        showSessionSummary();
        validateStep1();
    };

    window.openPeriodSessionsModal = function() {
        var modal = document.getElementById('period-sessions-modal');
        var container = document.getElementById('period-modal-sessions');
        var title = document.getElementById('period-modal-title');

        title.textContent = _periodSessionsData.length + ' Sessions — ' + selectedPeriodMonths + ' Month' + (selectedPeriodMonths > 1 ? 's' : '');

        var html = '';

        // Group by schedule if multiple selected
        if (_selectedScheduleParentIds.length > 1) {
            _scheduleOptions.forEach(function(sched) {
                var pid = sched.parent_id;
                if (_selectedScheduleParentIds.indexOf(pid) === -1 && _selectedScheduleParentIds.indexOf(parseInt(pid)) === -1) return;

                var groupSessions = _periodSessionsData.filter(function(s) {
                    var spid = s.recurrence_parent_id;
                    return spid == pid || (pid === 'oneoff' && !spid);
                });
                if (groupSessions.length === 0) return;

                html += '<div class="font-semibold text-sm text-primary flex items-center gap-2 mt-3 mb-2">' +
                    '<span class="icon-[tabler--calendar-repeat] size-4"></span>' +
                    sched.label + ' · ' + sched.time +
                    '<span class="badge badge-sm badge-soft badge-primary">' + groupSessions.length + '</span></div>';

                groupSessions.forEach(function(session, idx) {
                    html += renderSessionRow(session, idx);
                });
            });
        } else {
            _periodSessionsData.forEach(function(session, idx) {
                html += renderSessionRow(session, idx);
            });
        }

        container.innerHTML = html;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function renderSessionRow(session, idx) {
        return '<div class="flex items-center justify-between p-3 ' + (idx % 2 === 0 ? 'bg-base-200/30' : '') + ' rounded-lg">' +
            '<div class="flex items-center gap-3">' +
            '<div class="text-center shrink-0 w-14">' +
            '<div class="text-xs text-base-content/50">' + (session.date ? session.date.split(',')[0] : '') + '</div>' +
            '<div class="font-bold text-sm">' + (session.date ? (session.date.split(', ')[1] || session.date) : '') + '</div>' +
            '</div>' +
            '<div>' +
            '<div class="font-medium text-sm">' + session.time + '</div>' +
            '<div class="text-xs text-base-content/60">' + session.instructor + (session.location ? ' · ' + session.location : '') + '</div>' +
            '</div>' +
            '</div>' +
            '<span class="badge badge-sm ' + (session.spots_remaining > 0 ? 'badge-success' : 'badge-error') + ' badge-soft shrink-0">' + session.spots_remaining + ' spots</span>' +
            '</div>';
    }

    window.closePeriodSessionsModal = function() {
        document.getElementById('period-sessions-modal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.updateBillingDiscountSection = function() {
        var section = document.getElementById('billing-discount-section');
        var optionsContainer = document.getElementById('billing-discount-options');

        window.removeBillingDiscount();

        // Don't show billing discount accordion if period was already chosen in step 1
        if (selectedBookingType === 'period') {
            section.classList.add('hidden');
            return;
        }

        if (!selectedSessionData || !selectedSessionData.billingDiscounts) {
            section.classList.add('hidden');
            return;
        }

        var discounts = selectedSessionData.billingDiscounts;
        var hasDiscounts = false;
        var basePrice = selectedSessionData.price;
        var periods = { '1': '1 Month', '3': '3 Months', '6': '6 Months', '9': '9 Months', '12': '12 Months' };
        var html = '';

        Object.keys(periods).forEach(function(months) {
            var m = parseInt(months);
            var totalAmount = parseFloat(discounts[months]) || 0;
            var isDiscounted = totalAmount > 0;
            if (isDiscounted) hasDiscounts = true;
            var monthlyRate = m > 0 ? (totalAmount / m) : 0;
            var totalWithout = basePrice * m;

            html += '<button type="button" class="billing-period-btn flex flex-col items-center p-3 rounded-lg border-2 transition-all ' +
                (isDiscounted ? 'border-base-content/10 hover:border-success cursor-pointer' : 'border-base-content/5 opacity-50 cursor-default') + '" ' +
                'data-months="' + months + '" data-total="' + totalAmount + '" ' +
                (isDiscounted ? 'onclick="selectBillingPeriod(' + months + ', ' + totalAmount + ')"' : '') + '>' +
                '<div class="text-xs text-base-content/60 font-medium">' + periods[months] + '</div>' +
                '<div class="text-lg font-bold ' + (isDiscounted ? 'text-success' : 'text-base-content/40') + '">$' + (isDiscounted ? totalAmount.toFixed(2) : (basePrice * m).toFixed(2)) + '</div>';

            if (isDiscounted) {
                html += '<div class="text-[10px] text-base-content/50">$' + monthlyRate.toFixed(2) + '/mo</div>';
                if (totalWithout > totalAmount) {
                    html += '<div class="text-xs text-success mt-0.5">Save $' + (totalWithout - totalAmount).toFixed(2) + '</div>';
                }
            }

            html += '</button>';
        });

        optionsContainer.innerHTML = html;
        section.classList.toggle('hidden', !hasDiscounts);
    };

    // Store selected billing credit amount for reg fee toggle
    var _billingCreditAmount = 0;
    var _billingRegFee = 0;

    window.selectBillingPeriod = function(months, totalAmount) {
        selectedBillingPeriod = months;
        var basePrice = selectedSessionData.price;
        var regFee = selectedSessionData.registrationFee || 0;
        var cancelFee = selectedSessionData.cancellationFee || 0;
        var graceHrs = selectedSessionData.graceHours || 48;
        var monthlyRate = months > 0 ? (totalAmount / months) : 0;
        _billingCreditAmount = totalAmount;
        _billingRegFee = regFee;
        var includeRegFee = regFee > 0;
        var totalDueToday = totalAmount + (includeRegFee ? regFee : 0);
        var totalWithout = basePrice * months;
        var totalSavings = totalWithout - totalAmount;
        var periods = { 1: '1 Month', 3: '3 Months', 6: '6 Months', 9: '9 Months', 12: '12 Months' };

        document.getElementById('billing_period_hidden').value = months;
        document.getElementById('billing_discount_percent_hidden').value = totalAmount;

        // Highlight selected
        document.querySelectorAll('.billing-period-btn').forEach(function(btn) {
            if (parseInt(btn.dataset.months) === months) {
                btn.classList.remove('border-base-content/10');
                btn.classList.add('border-success', 'bg-success/5');
            } else {
                btn.classList.remove('border-success', 'bg-success/5');
                var t = parseFloat(btn.dataset.total) || 0;
                if (t > 0) btn.classList.add('border-base-content/10');
            }
        });

        // Show registration fee checkbox
        var regFeeContainer = document.getElementById('reg-fee-container');
        if (regFee > 0) {
            document.getElementById('reg-fee-amount').textContent = '$' + regFee.toFixed(2);
            document.getElementById('reg-fee-checkbox').checked = true;
            document.getElementById('include_reg_fee_hidden').value = '1';
            regFeeContainer.classList.remove('hidden');
        } else {
            regFeeContainer.classList.add('hidden');
        }

        // Build applied info text
        var label = periods[months] + ' — $' + totalAmount.toFixed(2) + ' total ($' + monthlyRate.toFixed(2) + '/mo)';
        var details = 'Prepaid credit: $' + totalAmount.toFixed(2) + ' for ' + months + ' month' + (months > 1 ? 's' : '') + '.';
        if (totalSavings > 0) details += ' Save $' + totalSavings.toFixed(2) + ' vs regular $' + totalWithout.toFixed(2) + '.';

        var policyNote = '';
        if (cancelFee > 0) policyNote += 'Early cancellation fee: $' + cancelFee.toFixed(2) + '. ';
        policyNote += graceHrs + 'hr grace period for full refund.';

        document.getElementById('applied-billing-label').textContent = label;
        document.getElementById('applied-billing-savings').innerHTML = details + '<br><span class="text-xs text-base-content/50">' + policyNote + '</span>';
        document.getElementById('applied-billing-discount').classList.remove('hidden');

        // Update price
        document.getElementById('display-price').textContent = '$' + totalDueToday.toFixed(2);
        var priceInput = document.getElementById('price-input');
        if (priceInput) priceInput.value = totalDueToday.toFixed(2);

        // Show original price crossed out
        if (totalSavings > 0) {
            document.getElementById('original-price-display').textContent = '$' + totalWithout.toFixed(2);
            document.getElementById('original-price-display').classList.remove('hidden');
        }
        document.getElementById('discount-badge').classList.remove('hidden');
        document.getElementById('discount-badge-text').textContent = months + 'mo prepaid';
    };

    var _seriesDiscountedTotal = 0;
    var _seriesTotalNoDiscount = 0;

    window.toggleRegFee = function() {
        document.getElementById('include_reg_fee_hidden').value = document.getElementById('reg-fee-checkbox').checked ? '1' : '0';
        recalcSeriesTotal();
    };

    window.toggleSeriesDiscount = function() {
        var checked = document.getElementById('apply-discount-checkbox').checked;
        if (checked && _seriesDiscountedTotal > 0) {
            // Apply discount — set billing period hidden fields
            document.getElementById('billing_period_hidden').value = selectedPeriodMonths;
            document.getElementById('billing_discount_percent_hidden').value = _seriesDiscountedTotal;
        } else {
            // Remove discount
            document.getElementById('billing_period_hidden').value = '';
            document.getElementById('billing_discount_percent_hidden').value = '0';
        }
        recalcSeriesTotal();
    };

    function recalcSeriesTotal() {
        if (selectedBookingType !== 'period') return;

        var useDiscount = document.getElementById('apply-discount-checkbox').checked;
        var includeRegFee = document.getElementById('reg-fee-checkbox').checked;
        var baseTotal = useDiscount ? _seriesDiscountedTotal : _seriesTotalNoDiscount;
        var regFee = includeRegFee ? _billingRegFee : 0;
        var finalTotal = baseTotal + regFee;

        document.getElementById('display-price').textContent = '$' + finalTotal.toFixed(2);
        document.getElementById('price-input').value = finalTotal.toFixed(2);

        // Show/hide original price strikethrough
        if (useDiscount && _seriesTotalNoDiscount > _seriesDiscountedTotal) {
            document.getElementById('original-price-display').textContent = '$' + _seriesTotalNoDiscount.toFixed(2);
            document.getElementById('original-price-display').classList.remove('hidden');
            document.getElementById('discount-badge').classList.remove('hidden');
            document.getElementById('discount-badge-text').textContent = selectedPeriodMonths + 'mo discount applied';
        } else {
            document.getElementById('original-price-display').classList.add('hidden');
            document.getElementById('discount-badge').classList.add('hidden');
        }
    };

    window.removeBillingDiscount = function() {
        selectedBillingPeriod = null;
        _billingCreditAmount = 0;
        _billingRegFee = 0;
        document.getElementById('billing_period_hidden').value = '';
        document.getElementById('billing_discount_percent_hidden').value = '0';
        document.getElementById('applied-billing-discount').classList.add('hidden');
        document.getElementById('reg-fee-container').classList.add('hidden');

        document.querySelectorAll('.billing-period-btn').forEach(function(btn) {
            btn.classList.remove('border-success', 'bg-success/5');
            if (parseFloat(btn.dataset.discount) > 0) btn.classList.add('border-base-content/10');
        });

        if (selectedSessionData) {
            document.getElementById('display-price').textContent = '$' + selectedSessionData.price.toFixed(2);
            var priceInput = document.getElementById('price-input');
            if (priceInput) priceInput.value = selectedSessionData.price.toFixed(2);
            document.getElementById('original-price-display').classList.add('hidden');
            document.getElementById('discount-badge').classList.add('hidden');
        }
    };

    // Elements
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');

    // Client type toggle
    document.querySelectorAll('input[name="client_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            isNewClient = this.value === 'new';
            document.getElementById('existing-client-section').classList.toggle('hidden', isNewClient);
            document.getElementById('new-client-section').classList.toggle('hidden', !isNewClient);
            validateStep1();
        });
    });

    // New client form validation
    ['new-first-name', 'new-last-name'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateNewClientButton);
    });

    function updateNewClientButton() {
        const firstName = document.getElementById('new-first-name').value.trim();
        const lastName = document.getElementById('new-last-name').value.trim();
        const addBtn = document.getElementById('add-new-client-btn');

        if (firstName.length > 0 && lastName.length > 0) {
            addBtn.disabled = false;
        } else {
            addBtn.disabled = true;
        }
    }

    // Add new client button handler
    window.confirmNewClient = function() {
        const firstName = document.getElementById('new-first-name').value.trim();
        const lastName = document.getElementById('new-last-name').value.trim();
        const email = document.getElementById('new-email').value.trim();
        const phone = document.getElementById('new-phone').value.trim();

        if (firstName.length === 0 || lastName.length === 0) {
            return;
        }

        // Show as selected client
        selectedClientName = firstName + ' ' + lastName;
        selectedClientEmail = email || '';
        const initials = (firstName[0] + lastName[0]).toUpperCase();
        const contact = email || phone || 'New client';

        document.getElementById('selected-initials').textContent = initials;
        document.getElementById('selected-avatar-img').classList.add('hidden');
        document.getElementById('selected-avatar-initials').classList.remove('hidden');
        document.getElementById('selected-name').textContent = selectedClientName;
        document.getElementById('selected-contact').textContent = contact;

        // New client - show new badge, use primary styling
        document.getElementById('selected-new-badge').classList.remove('hidden');
        document.getElementById('selected-client-alert').className = 'alert alert-info';
        document.getElementById('selected-avatar-circle').className = 'bg-info-content text-info size-12 rounded-full';

        // Hide form, show selected
        document.getElementById('client-type-selection').classList.add('hidden');
        document.getElementById('new-client-section').classList.add('hidden');
        document.getElementById('selected-client').classList.remove('hidden');

        // Mark as new client selected (we'll create on submit)
        selectedClientId = 'new';

        validateStep1();
    };

    // Client search
    const searchInput = document.getElementById('client-search');
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            document.getElementById('search-results').classList.add('hidden');
            return;
        }

        document.getElementById('search-loading').classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            fetch(`/walk-in/clients/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    displaySearchResults(data.clients);
                })
                .finally(() => {
                    document.getElementById('search-loading').classList.add('hidden');
                });
        }, 300);
    });

    function displaySearchResults(clients) {
        const container = document.getElementById('search-results-list');
        const resultsDiv = document.getElementById('search-results');

        if (clients.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-base-content/50">
                    <p class="text-sm">No clients found</p>
                </div>
            `;
        } else {
            container.innerHTML = clients.map(c => {
                const initials = (c.first_name[0] + c.last_name[0]).toUpperCase();
                const avatarHtml = c.avatar_url
                    ? `<div class="avatar"><div class="size-10 rounded-full"><img src="${c.avatar_url}" alt="${c.first_name}"></div></div>`
                    : `<div class="avatar placeholder"><div class="bg-primary text-primary-content size-10 rounded-full"><span>${initials}</span></div></div>`;

                return `
                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg hover:bg-base-200/50 cursor-pointer transition-colors"
                     onclick="selectClient(${c.id}, '${c.first_name}', '${c.last_name}', '${c.email || ''}', '${c.phone || ''}', '${c.avatar_url || ''}')">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div>
                            <div class="font-medium">${c.first_name} ${c.last_name}</div>
                            <div class="text-sm text-base-content/60">${c.email || c.phone || 'No contact'}</div>
                        </div>
                    </div>
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                </div>
            `}).join('');
        }

        resultsDiv.classList.remove('hidden');
    }

    window.selectClient = function(id, firstName, lastName, email, phone, avatarUrl) {
        selectedClientId = id;
        selectedClientName = firstName + ' ' + lastName;
        selectedClientEmail = email || '';
        document.getElementById('client-id').value = id;

        const initials = (firstName[0] + lastName[0]).toUpperCase();
        const contact = email || phone || 'No contact info';

        const avatarImg = document.getElementById('selected-avatar-img');
        const avatarInitials = document.getElementById('selected-avatar-initials');

        if (avatarUrl) {
            document.getElementById('selected-avatar-src').src = avatarUrl;
            avatarImg.classList.remove('hidden');
            avatarInitials.classList.add('hidden');
        } else {
            document.getElementById('selected-initials').textContent = initials;
            avatarImg.classList.add('hidden');
            avatarInitials.classList.remove('hidden');
        }

        document.getElementById('selected-name').textContent = selectedClientName;
        document.getElementById('selected-contact').textContent = contact;

        // Existing client - hide new badge, use success styling
        document.getElementById('selected-new-badge').classList.add('hidden');
        document.getElementById('selected-client-alert').className = 'alert alert-success';
        document.getElementById('selected-avatar-circle').className = 'bg-success-content text-success size-12 rounded-full';

        // Hide all client selection UI, show only selected client
        document.getElementById('client-type-selection').classList.add('hidden');
        document.getElementById('existing-client-section').classList.add('hidden');
        document.getElementById('new-client-section').classList.add('hidden');
        document.getElementById('selected-client').classList.remove('hidden');
        document.getElementById('search-results').classList.add('hidden');

        validateStep1();
    };

    window.clearClient = function() {
        selectedClientId = null;
        selectedClientName = '';
        selectedClientEmail = '';
        isNewClient = false;
        document.getElementById('client-id').value = '';
        document.getElementById('selected-client').classList.add('hidden');

        // Clear payment options
        document.getElementById('payment-method-hidden').value = 'manual';

        // Show client type selection again
        document.getElementById('client-type-selection').classList.remove('hidden');
        document.getElementById('existing-client-section').classList.remove('hidden');
        document.getElementById('new-client-section').classList.add('hidden');

        // Reset to existing client radio
        document.querySelector('input[name="client_type"][value="existing"]').checked = true;

        // Reset search
        searchInput.closest('.form-control').classList.remove('hidden');
        searchInput.value = '';
        document.getElementById('search-results').classList.add('hidden');

        // Reset new client form
        document.getElementById('new-first-name').value = '';
        document.getElementById('new-last-name').value = '';
        document.getElementById('new-email').value = '';
        document.getElementById('new-phone').value = '';

        validateStep1();
    };

    function validateStep1() {
        let clientValid = false;
        if (selectedClientId === 'new') {
            // New client - check if names are filled
            const firstName = document.getElementById('new-first-name').value.trim();
            const lastName = document.getElementById('new-last-name').value.trim();
            clientValid = firstName.length > 0 && lastName.length > 0;
        } else {
            clientValid = selectedClientId !== null && selectedClientId !== '';
        }

        const classPlanValid = selectedClassPlanId !== null && selectedClassPlanId !== '';
        const sessionValid = selectedSessionId !== null && selectedSessionId !== '';

        const allValid = clientValid && classPlanValid && sessionValid;
        document.getElementById('step1-next').disabled = !allValid;
    }

    // Class Plan selection
    let selectedClassPlanId = null;
    let selectedClassPlanName = '';
    let datePicker = null;

    const classPlanSelect = document.getElementById('class-plan-select');

    function handleClassPlanChange() {
        const selectedOption = classPlanSelect.options[classPlanSelect.selectedIndex];

        if (!classPlanSelect.value) {
            selectedClassPlanId = null;
            selectedClassPlanName = '';
            document.getElementById('booking-type-selection').classList.add('hidden');
            document.getElementById('date-selection').classList.add('hidden');
            document.getElementById('duration-selection').classList.add('hidden');
            document.getElementById('session-selection').classList.add('hidden');
            validateStep1();
            return;
        }

        selectedClassPlanId = classPlanSelect.value;
        selectedClassPlanName = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];

        // Show booking type selection
        document.getElementById('booking-type-selection').classList.remove('hidden');
        // Reset to single by default
        document.querySelector('input[name="booking_type"][value="single"]').checked = true;
        selectBookingType('single');

        // Show date selection (for single class)
        document.getElementById('date-selection').classList.remove('hidden');

        // Fetch questionnaires for this class plan
        fetchQuestionnairesForClassPlan(selectedClassPlanId);

        // Reset session selection
        selectedSessionId = null;
        selectedSessionData = null;
        document.getElementById('session-id').value = '';
        document.getElementById('session-selection').classList.add('hidden');
        document.getElementById('sessions-list').innerHTML = '';
        validateStep1();

        // Fetch default duration for this class plan and show it
        fetch(`/walk-in/class-plan-defaults?class_plan_id=${selectedClassPlanId}`)
            .then(res => res.json())
            .then(data => {
                const defaultDuration = data.duration_minutes || 60;
                document.getElementById('session-duration').value = defaultDuration;
                document.getElementById('duration-display').textContent = defaultDuration;
                document.getElementById('duration-selection').classList.remove('hidden');
            });

        // Initialize date picker if not already
        if (!datePicker) {
            datePicker = flatpickr('#booking-date', {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d',
                altInputClass: 'input input-bordered w-full',
                defaultDate: '{{ $selectedDate }}',
                onChange: function(selectedDates, dateStr) {
                    loadSessions(dateStr);
                }
            });
            // Load sessions for default date
            loadSessions('{{ $selectedDate }}');
        } else {
            // Reload sessions with new class plan
            const currentDate = datePicker.selectedDates[0];
            if (currentDate) {
                loadSessions(datePicker.formatDate(currentDate, 'Y-m-d'));
            }
        }
    }

    // Listen to change event
    classPlanSelect.addEventListener('change', handleClassPlanChange);

    // Fetch questionnaires for class plan
    function fetchQuestionnairesForClassPlan(classPlanId) {
        const intakeSection = document.getElementById('intake-form-section');
        const loading = document.getElementById('questionnaires-loading');
        const empty = document.getElementById('questionnaires-empty');
        const list = document.getElementById('questionnaires-list');

        if (!classPlanId) {
            intakeSection.classList.add('hidden');
            availableQuestionnaires = [];
            return;
        }

        // Show loading
        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        list.innerHTML = '';

        fetch(`/walk-in/class-plan-questionnaires?class_plan_id=${classPlanId}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                availableQuestionnaires = data.questionnaires || [];

                if (availableQuestionnaires.length > 0) {
                    intakeSection.classList.remove('hidden');
                    renderQuestionnaires();
                } else {
                    intakeSection.classList.add('hidden');
                }
            })
            .catch(err => {
                console.error('Error fetching questionnaires:', err);
                loading.classList.add('hidden');
                intakeSection.classList.add('hidden');
                availableQuestionnaires = [];
            });
    }

    function renderQuestionnaires() {
        const list = document.getElementById('questionnaires-list');
        const empty = document.getElementById('questionnaires-empty');

        if (availableQuestionnaires.length === 0) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.innerHTML = availableQuestionnaires.map(q => {
            const durationText = q.estimated_duration ? `${q.estimated_duration} min` : '';
            const requiredBadge = q.is_required ? '<span class="badge badge-error badge-xs ml-2">Required</span>' : '';
            return `
                <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-info has-[:checked]:bg-info/5 transition-all">
                    <input type="checkbox" name="questionnaire_ids[]" value="${q.id}" class="checkbox checkbox-info checkbox-sm" ${q.is_required ? 'checked' : ''}>
                    <div class="flex-1">
                        <div class="font-medium text-sm">${q.name}${requiredBadge}</div>
                        ${durationText ? `<div class="text-xs text-base-content/50">${durationText}</div>` : ''}
                    </div>
                    <span class="icon-[tabler--file-text] size-4 text-base-content/30"></span>
                </label>
            `;
        }).join('');
    }

    // Intake form checkbox toggle
    document.getElementById('send-intake-form').addEventListener('change', function() {
        const selection = document.getElementById('questionnaire-selection');
        const noEmailWarning = document.getElementById('no-email-warning');

        if (this.checked) {
            selection.classList.remove('hidden');

            // Check if client has email
            if (selectedClientEmail) {
                noEmailWarning.classList.add('hidden');
            } else {
                noEmailWarning.classList.remove('hidden');
            }
        } else {
            selection.classList.add('hidden');
        }
    });

    function loadSessions(date) {
        if (!selectedClassPlanId) return;

        selectedSessionId = null;
        selectedSessionData = null;
        document.getElementById('session-id').value = '';
        validateStep1();

        // Show duration selection after date is picked
        document.getElementById('duration-selection').classList.remove('hidden');

        document.getElementById('session-selection').classList.remove('hidden');
        document.getElementById('sessions-loading').classList.remove('hidden');
        document.getElementById('sessions-empty').classList.add('hidden');
        document.getElementById('next-available').classList.add('hidden');
        document.getElementById('sessions-list').innerHTML = '';

        fetch(`/walk-in/sessions?date=${date}&class_plan_id=${selectedClassPlanId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('sessions-loading').classList.add('hidden');

                if (data.sessions.length === 0) {
                    document.getElementById('sessions-empty').classList.remove('hidden');

                    // Show next available dates if any
                    if (data.next_available && data.next_available.length > 0) {
                        document.getElementById('next-available').classList.remove('hidden');
                        const datesContainer = document.getElementById('next-available-dates');
                        datesContainer.innerHTML = data.next_available.map(item => `
                            <button type="button" class="btn btn-sm btn-outline btn-primary" onclick="jumpToDate('${item.date}')">
                                ${item.formatted_date}
                                <span class="badge badge-primary badge-xs">${item.session_count}</span>
                            </button>
                        `).join('');
                    }
                    return;
                }

                const container = document.getElementById('sessions-list');
                container.innerHTML = data.sessions.map(session => `
                    <label class="flex items-center gap-4 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <input type="radio" name="session_radio" value="${session.id}" class="radio radio-primary"
                               data-title="${session.title}"
                               data-time="${session.time}"
                               data-price="${session.price || 0}"
                               data-start="${session.start_time_iso}"
                               data-end="${session.end_time_iso}"
                               data-billing-discounts='${JSON.stringify(session.billing_discounts || {})}'
                               data-registration-fee="${session.registration_fee || 0}"
                               data-cancellation-fee="${session.cancellation_fee || 0}"
                               data-grace-hours="${session.cancellation_grace_hours || 48}"
                               onchange="selectSession(this)">
                        <div class="flex-1">
                            <div class="font-semibold">${session.time}</div>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--user] size-4"></span>
                                    ${session.instructor}
                                </span>
                                ${session.location ? `
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--map-pin] size-4"></span>
                                    ${session.location}
                                </span>
                                ` : ''}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold ${session.spots_remaining > 0 ? 'text-success' : 'text-error'}">
                                ${session.spots_remaining}
                            </div>
                            <div class="text-xs text-base-content/60">spots</div>
                        </div>
                    </label>
                `).join('');

                // Auto-select preloaded session if provided
                if (window.preloadSessionId) {
                    const preloadRadio = document.querySelector(`input[name="session_radio"][value="${window.preloadSessionId}"]`);
                    if (preloadRadio) {
                        preloadRadio.checked = true;
                        selectSession(preloadRadio);
                        // Clear preload ID so it doesn't auto-select again on date change
                        window.preloadSessionId = null;
                    }
                }
            });
    }

    window.jumpToDate = function(date) {
        if (datePicker) {
            datePicker.setDate(date, true);
        }
    };

    window.selectSession = function(radio) {
        selectedSessionId = radio.value;
        var billingDiscounts = {};
        try { billingDiscounts = JSON.parse(radio.dataset.billingDiscounts || '{}'); } catch(e) {}

        selectedSessionData = {
            title: selectedClassPlanName,
            time: radio.dataset.time,
            price: parseFloat(radio.dataset.price) || 0,
            startTime: radio.dataset.start,
            endTime: radio.dataset.end,
            billingDiscounts: billingDiscounts,
            registrationFee: parseFloat(radio.dataset.registrationFee) || 0,
            cancellationFee: parseFloat(radio.dataset.cancellationFee) || 0,
            graceHours: parseInt(radio.dataset.graceHours) || 48
        };
        document.getElementById('session-id').value = selectedSessionId;

        // Check if session is happening now (30 min before start to end time)
        updateCheckInVisibility();

        // Show/hide billing discount section
        updateBillingDiscountSection();

        validateStep1();
    };

    function updateCheckInVisibility() {
        const checkInContainer = document.getElementById('check-in-now-container');
        const checkInCheckbox = document.getElementById('check-in-now-checkbox');

        if (!selectedSessionData || !selectedSessionData.startTime) {
            checkInContainer.classList.add('hidden');
            checkInCheckbox.checked = false;
            return;
        }

        const now = new Date();
        const startTime = new Date(selectedSessionData.startTime);
        const endTime = new Date(selectedSessionData.endTime);

        // Allow check-in from 30 minutes before start until session ends
        const checkInWindowStart = new Date(startTime.getTime() - 30 * 60 * 1000);

        if (now >= checkInWindowStart && now <= endTime) {
            checkInContainer.classList.remove('hidden');
        } else {
            checkInContainer.classList.add('hidden');
            checkInCheckbox.checked = false;
        }
    }

    // Promo code functionality
    let appliedOfferId = null;
    let appliedDiscount = 0;
    let originalClassPrice = 0;
    let availableOffers = [];

    // Fetch available offers when entering step 2
    function fetchAvailableOffers() {
        const offersLoading = document.getElementById('offers-loading');
        const offersEmpty = document.getElementById('offers-empty');
        const offersList = document.getElementById('offers-list');
        const availableOffersSection = document.getElementById('available-offers-section');

        // Client must be selected (and not a new client) to fetch offers
        const clientInfoEl = document.getElementById('offers-client-info');
        if (!selectedClientId || selectedClientId === 'new') {
            offersLoading.classList.add('hidden');
            offersEmpty.classList.remove('hidden');
            offersEmpty.innerHTML = '<span class="icon-[tabler--info-circle] size-4 inline-block mr-1"></span>Save new client first to see applicable offers';
            offersList.innerHTML = '';
            availableOffers = [];
            clientInfoEl.classList.add('hidden');
            return;
        }

        clientInfoEl.classList.remove('hidden');
        document.getElementById('offers-client-name').textContent = selectedClientName;

        offersLoading.classList.remove('hidden');
        offersEmpty.classList.add('hidden');
        offersEmpty.textContent = 'No promo codes available for this client';
        offersList.innerHTML = '';

        const params = new URLSearchParams({
            type: 'classes',
            original_price: originalClassPrice,
            client_id: selectedClientId,
        });

        fetch(`/walk-in/applicable-offers?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                offersLoading.classList.add('hidden');
                availableOffers = data.offers || [];

                // Update client name in the info text
                if (data.client_name) {
                    document.getElementById('offers-client-name').textContent = data.client_name;
                }

                if (availableOffers.length === 0) {
                    offersEmpty.classList.remove('hidden');
                    return;
                }

                renderAvailableOffers();
            })
            .catch(err => {
                console.error('Error fetching offers:', err);
                offersLoading.classList.add('hidden');
                offersEmpty.classList.remove('hidden');
            });
    }

    function renderAvailableOffers() {
        const offersList = document.getElementById('offers-list');

        offersList.innerHTML = availableOffers.map(offer => `
            <button type="button"
                    class="offer-card flex items-center gap-3 p-3 border border-base-300 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left ${appliedOfferId === offer.id ? 'border-success bg-success/10' : ''}"
                    onclick="selectOffer(${offer.id})"
                    ${appliedOfferId === offer.id ? 'disabled' : ''}>
                <div class="size-10 rounded-lg bg-warning/20 flex items-center justify-center shrink-0">
                    <span class="icon-[tabler--discount-2] size-5 text-warning"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">${offer.name}</div>
                    <div class="text-xs text-base-content/60">${offer.discount_display}</div>
                    ${offer.code ? `<div class="text-xs font-mono text-primary">${offer.code}</div>` : ''}
                </div>
                ${appliedOfferId === offer.id
                    ? '<span class="icon-[tabler--check] size-5 text-success shrink-0"></span>'
                    : `<span class="text-sm font-bold text-success shrink-0">-$${offer.discount_amount.toFixed(2)}</span>`
                }
            </button>
        `).join('');
    }

    window.selectOffer = function(offerId) {
        const offer = availableOffers.find(o => o.id === offerId);
        if (!offer) return;

        // Apply the offer directly
        appliedOfferId = offer.id;
        appliedDiscount = offer.discount_amount;

        // Update hidden fields
        document.getElementById('offer_id').value = offer.id;
        document.getElementById('promo_code_hidden').value = offer.code || '';
        document.getElementById('discount_amount').value = offer.discount_amount;

        // Update applied offer display
        document.getElementById('applied-offer-name').textContent = offer.name;
        document.getElementById('applied-offer-discount').textContent = offer.discount_display + ' applied!';
        document.getElementById('applied-offer').classList.remove('hidden');

        // Hide the manual input section when offer is applied
        document.getElementById('promo-input-section').classList.add('hidden');

        // Update price display
        updatePriceWithDiscount(offer.final_price, offer.discount_display);

        // Update price input if not trial
        if (!document.getElementById('trial-class').checked) {
            document.getElementById('price-input').value = offer.final_price.toFixed(2);
        }

        // Re-render offers to show selected state
        renderAvailableOffers();
    };

    window.refreshAvailableOffers = function() {
        const icon = document.getElementById('refresh-offers-icon');
        icon.classList.add('animate-spin');
        fetchAvailableOffers();
        setTimeout(() => icon.classList.remove('animate-spin'), 500);
    };

    window.applyPromoCode = function() {
        const codeInput = document.getElementById('promo_code_input');
        const code = codeInput.value.trim().toUpperCase();
        const applyBtn = document.getElementById('apply-promo-btn');
        const errorEl = document.getElementById('promo-error');

        if (!code) {
            showPromoError('Please enter a promo code.');
            return;
        }

        if (!selectedSessionData || originalClassPrice <= 0) {
            showPromoError('Please select a class first.');
            return;
        }

        // Show loading state
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
        errorEl.classList.add('hidden');

        // Make AJAX request
        fetch('/walk-in/validate-promo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: code,
                type: 'classes',
                original_price: originalClassPrice,
                client_id: selectedClientId
            })
        })
        .then(response => response.json())
        .then(data => {
            applyBtn.disabled = false;
            applyBtn.innerHTML = 'Apply';

            if (data.valid) {
                applyOffer(data);
            } else {
                showPromoError(data.error || 'Invalid promo code.');
            }
        })
        .catch(error => {
            applyBtn.disabled = false;
            applyBtn.innerHTML = 'Apply';
            showPromoError('Unable to validate promo code. Please try again.');
            console.error('Promo validation error:', error);
        });
    };

    function applyOffer(data) {
        appliedOfferId = data.offer_id;
        appliedDiscount = data.discount_amount;

        const promoInput = document.getElementById('promo_code_input');
        const appliedCode = promoInput.value.toUpperCase();

        // Update hidden fields
        document.getElementById('offer_id').value = data.offer_id;
        document.getElementById('promo_code_hidden').value = appliedCode;
        document.getElementById('discount_amount').value = data.discount_amount;

        // Update applied offer display
        document.getElementById('applied-offer-name').textContent = data.offer_name;
        document.getElementById('applied-offer-discount').textContent = data.discount_display + ' applied!';

        // Show applied offer banner
        document.getElementById('applied-offer').classList.remove('hidden');

        // Update input to show applied state
        promoInput.value = appliedCode;
        promoInput.readOnly = true;
        promoInput.classList.add('bg-base-200');

        // Change Apply button to Applied
        const applyBtn = document.getElementById('apply-promo-btn');
        applyBtn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Applied';
        applyBtn.classList.remove('btn-primary');
        applyBtn.classList.add('btn-success');
        applyBtn.disabled = true;

        // Update price display
        updatePriceWithDiscount(data.final_price, data.discount_display);

        // Update price input if not trial
        if (!document.getElementById('trial-class').checked) {
            document.getElementById('price-input').value = data.final_price.toFixed(2);
        }
    }

    window.removePromoCode = function() {
        appliedOfferId = null;
        appliedDiscount = 0;

        // Clear hidden fields
        document.getElementById('offer_id').value = '';
        document.getElementById('promo_code_hidden').value = '';
        document.getElementById('discount_amount').value = '0';

        // Hide applied offer banner
        document.getElementById('applied-offer').classList.add('hidden');

        // Show the manual input section again
        document.getElementById('promo-input-section').classList.remove('hidden');

        // Reset the promo input field
        const promoInput = document.getElementById('promo_code_input');
        promoInput.value = '';
        promoInput.readOnly = false;
        promoInput.classList.remove('bg-base-200');

        // Reset the Apply button
        const applyBtn = document.getElementById('apply-promo-btn');
        applyBtn.innerHTML = 'Apply';
        applyBtn.classList.add('btn-primary');
        applyBtn.classList.remove('btn-success');
        applyBtn.disabled = false;

        // Clear error
        document.getElementById('promo-error').classList.add('hidden');

        // Reset price display
        resetPriceDisplay();

        // Update price input if not trial
        if (!document.getElementById('trial-class').checked) {
            document.getElementById('price-input').value = originalClassPrice.toFixed(2);
        }

        // Re-render offers to remove selected state
        renderAvailableOffers();
    };

    function updatePriceWithDiscount(finalPrice, discountDisplay) {
        const displayPriceEl = document.getElementById('display-price');
        const originalPriceEl = document.getElementById('original-price-display');
        const discountBadge = document.getElementById('discount-badge');
        const discountBadgeText = document.getElementById('discount-badge-text');

        // Show original price crossed out
        originalPriceEl.textContent = '$' + originalClassPrice.toFixed(2);
        originalPriceEl.classList.remove('hidden');

        // Show discounted price
        displayPriceEl.textContent = '$' + finalPrice.toFixed(2);

        // Show discount badge
        discountBadgeText.textContent = discountDisplay + ' Off';
        discountBadge.classList.remove('hidden');
    }

    function resetPriceDisplay() {
        const displayPriceEl = document.getElementById('display-price');
        const originalPriceEl = document.getElementById('original-price-display');
        const discountBadge = document.getElementById('discount-badge');

        // Hide original price
        originalPriceEl.classList.add('hidden');

        // Reset display price
        displayPriceEl.textContent = '$' + originalClassPrice.toFixed(2);

        // Hide discount badge
        discountBadge.classList.add('hidden');
    }

    function showPromoError(message) {
        const errorEl = document.getElementById('promo-error');
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }

    // Handle Enter key in promo code input
    document.getElementById('promo_code_input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyPromoCode();
        }
    });

    // ==================== Price Override Functions ====================
    let pendingOverrideId = null;
    let pendingOverrideCode = null;
    let appliedOverridePrice = null;
    let statusPollInterval = null;
    const canOverridePrice = {{ ($canOverridePrice ?? false) ? 'true' : 'false' }};

    // Direct override for managers/owners with permission
    window.applyDirectOverride = function() {
        const priceInput = document.getElementById('direct-override-price');
        const newPrice = parseFloat(priceInput.value);

        if (!newPrice || newPrice < 0) {
            showOverrideError('Please enter a valid price.');
            return;
        }

        if (newPrice >= originalClassPrice) {
            showOverrideError('Override price must be less than original price ($' + originalClassPrice.toFixed(2) + ').');
            return;
        }

        appliedOverridePrice = newPrice;

        // Set hidden fields for form submission
        document.getElementById('price_override_code').value = 'DIRECT';
        document.getElementById('price_override_amount').value = newPrice;

        // Update UI
        document.getElementById('applied-override-code').textContent = 'Direct Override';
        document.getElementById('applied-override-price').textContent = 'New price: $' + newPrice.toFixed(2) + ' (was $' + originalClassPrice.toFixed(2) + ')';
        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');

        // Update price display
        const discountAmount = originalClassPrice - newPrice;
        const discountPercent = ((discountAmount / originalClassPrice) * 100).toFixed(0);
        updatePriceWithDiscount(newPrice, discountPercent + '%');

        // Update the amount field if visible
        const priceInputField = document.getElementById('price-input');
        if (priceInputField) {
            priceInputField.value = newPrice.toFixed(2);
        }

        // Clear the input
        priceInput.value = '';
        hideOverrideError();
    };

    function showOverrideError(message) {
        const errorEl = document.getElementById('override-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    function hideOverrideError() {
        const errorEl = document.getElementById('override-error');
        if (errorEl) {
            errorEl.classList.add('hidden');
        }
    }

    window.showOverrideModal = function() {
        const modal = document.getElementById('override-modal');
        if (!modal) return;

        // Update modal original price
        document.getElementById('modal-original-price').textContent = '$' + originalClassPrice.toFixed(2);
        document.getElementById('override-new-price').value = '';
        document.getElementById('override-discount-code').value = '';
        document.getElementById('override-reason').value = '';
        document.getElementById('override-preview').classList.add('hidden');
        const modalError = document.getElementById('modal-error');
        if (modalError) modalError.classList.add('hidden');

        // Show div-based modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeOverrideModal = function() {
        const modal = document.getElementById('override-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    // Calculate discount preview when price changes
    document.getElementById('override-new-price')?.addEventListener('input', function() {
        const newPrice = parseFloat(this.value) || 0;
        const preview = document.getElementById('override-preview');

        if (newPrice > 0 && newPrice < originalClassPrice) {
            const discountAmount = originalClassPrice - newPrice;
            const discountPercent = ((discountAmount / originalClassPrice) * 100).toFixed(1);

            document.getElementById('preview-discount').textContent = '$' + discountAmount.toFixed(2);
            document.getElementById('preview-percent').textContent = discountPercent + '%';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });

    window.submitOverrideRequest = function() {
        const newPrice = parseFloat(document.getElementById('override-new-price').value);
        const discountCode = document.getElementById('override-discount-code').value.trim();
        const reason = document.getElementById('override-reason').value.trim();
        const submitBtn = document.getElementById('submit-override-btn');
        const errorEl = document.getElementById('modal-error');

        // Validation
        if (!newPrice || newPrice <= 0) {
            showOverrideModalError('Please enter a valid new price.');
            return;
        }
        if (newPrice >= originalClassPrice) {
            showOverrideModalError('New price must be less than the original price.');
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';
        if (errorEl) errorEl.classList.add('hidden');

        // Make request
        fetch('/price-override/request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                original_price: originalClassPrice,
                requested_price: newPrice,
                location_id: null,
                client_id: selectedClientId,
                discount_code: discountCode || null,
                reason: reason || null,
                bookable_type: 'App\\Models\\ClassSession',
                bookable_id: selectedSessionId,
                metadata: selectedSessionData ? {
                    class_name: selectedSessionData.name || '',
                    class_date: selectedSessionData.datetime || ''
                } : {}
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send Request';

            if (data.success) {
                closeOverrideModal();
                showPendingOverride(data.data);
            } else {
                showOverrideModalError(data.message || 'Failed to submit request.');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send Request';
            showOverrideModalError('An error occurred. Please try again.');
            console.error('Override request error:', error);
        });
    };

    function showOverrideModalError(message) {
        const errorEl = document.getElementById('modal-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    function showPendingOverride(data) {
        pendingOverrideId = data.id;
        pendingOverrideCode = data.confirmation_code;

        document.getElementById('pending-code').textContent = data.confirmation_code;
        document.getElementById('pending-expires').textContent = 'Expires ' + new Date(data.expires_at).toLocaleTimeString();

        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('override-pending').classList.remove('hidden');

        // Start polling for status
        startStatusPolling();
    }

    function startStatusPolling() {
        // Poll every 10 seconds
        statusPollInterval = setInterval(() => {
            checkOverrideStatus();
        }, 10000);
    }

    function stopStatusPolling() {
        if (statusPollInterval) {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
        }
    }

    window.checkOverrideStatus = function() {
        if (!pendingOverrideCode) return;

        fetch('/price-override/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: pendingOverrideCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.is_approved) {
                stopStatusPolling();
                applyOverrideFromData(data.data);
            } else if (!data.success && data.status === 'expired') {
                stopStatusPolling();
                resetOverrideUI();
                showOverrideError('Override request has expired.');
            } else if (!data.success && data.status === 'rejected') {
                stopStatusPolling();
                resetOverrideUI();
                showOverrideError('Override request was rejected.' + (data.rejection_reason ? ' Reason: ' + data.rejection_reason : ''));
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
        });
    };

    window.fetchApprovedOverride = function() {
        const fetchBtn = document.getElementById('fetch-override-btn');
        const messageEl = document.getElementById('fetch-override-message');

        // Need a session selected first
        if (!selectedSessionId) {
            messageEl.textContent = 'Please select a class session first.';
            messageEl.className = 'text-sm mt-2 text-warning';
            messageEl.classList.remove('hidden');
            return;
        }

        // Show loading
        fetchBtn.disabled = true;
        fetchBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Checking...';
        messageEl.classList.add('hidden');

        // Fetch approved overrides for this session
        fetch('/price-override/fetch-approved', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                bookable_type: 'App\\Models\\ClassSession',
                bookable_id: selectedSessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            fetchBtn.disabled = false;
            fetchBtn.innerHTML = '<span class="icon-[tabler--download] size-4"></span> Fetch Approved Code';

            if (data.success && data.data) {
                // Found an approved override - apply it
                applyOverrideFromData(data.data);
                messageEl.textContent = 'Approved override found and applied!';
                messageEl.className = 'text-sm mt-2 text-success';
                messageEl.classList.remove('hidden');
            } else {
                // No approved override found
                messageEl.textContent = data.message || 'No approved override found for this booking. You can request one below.';
                messageEl.className = 'text-sm mt-2 text-base-content/60';
                messageEl.classList.remove('hidden');
            }
        })
        .catch(error => {
            fetchBtn.disabled = false;
            fetchBtn.innerHTML = '<span class="icon-[tabler--download] size-4"></span> Fetch Approved Code';
            messageEl.textContent = 'Error checking for approved overrides. Please try again.';
            messageEl.className = 'text-sm mt-2 text-error';
            messageEl.classList.remove('hidden');
            console.error('Fetch override error:', error);
        });
    };

    // Personal override state
    let personalOverrideCode = null;
    let personalOverrideSupervisor = null;

    window.verifyOverrideCode = function() {
        const code = document.getElementById('override_code_input').value.trim().toUpperCase();
        const verifyBtn = document.getElementById('verify-override-btn');

        if (!code) {
            showOverrideError('Please enter a confirmation code.');
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

        fetch('/price-override/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = 'Verify';

            if (data.success) {
                // Check for personal code first (MY-XXXXX)
                if (data.is_personal_code) {
                    personalOverrideCode = data.code;
                    personalOverrideSupervisor = data.data?.authorized_by?.name || 'Manager';
                    showPersonalOverrideModal(data.code, personalOverrideSupervisor);
                } else if (data.data.is_approved) {
                    applyOverrideFromData(data.data);
                } else if (data.data.is_pending) {
                    showPendingOverride({
                        id: data.data.id,
                        confirmation_code: data.data.confirmation_code,
                        expires_at: data.data.expires_at
                    });
                }
            } else {
                showOverrideError(data.message || 'Invalid confirmation code.');
            }
        })
        .catch(error => {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = 'Verify';
            showOverrideError('Unable to verify code. Please try again.');
            console.error('Verify error:', error);
        });
    };

    // Personal Override Modal Functions
    function showPersonalOverrideModal(code, supervisorName) {
        const modal = document.getElementById('personal-override-modal');
        document.getElementById('personal-supervisor-name').textContent = supervisorName;
        document.getElementById('personal-supervisor-code').textContent = code;

        // Set original price
        document.getElementById('personal-modal-original-price').textContent = '$' + originalClassPrice.toFixed(2);
        document.getElementById('personal-override-new-price').value = '';
        document.getElementById('personal-override-preview').classList.add('hidden');
        document.getElementById('personal-modal-error').classList.add('hidden');

        modal.classList.remove('hidden');

        // Focus on price input
        setTimeout(() => {
            document.getElementById('personal-override-new-price').focus();
        }, 100);
    }

    window.closePersonalOverrideModal = function() {
        const modal = document.getElementById('personal-override-modal');
        modal.classList.add('hidden');
    };

    window.updatePersonalOverridePreview = function() {
        const newPrice = parseFloat(document.getElementById('personal-override-new-price').value) || 0;
        const preview = document.getElementById('personal-override-preview');

        if (newPrice > 0 && newPrice < originalClassPrice) {
            const discountAmount = originalClassPrice - newPrice;
            const discountPercent = ((discountAmount / originalClassPrice) * 100).toFixed(1);

            document.getElementById('personal-preview-discount').textContent = '$' + discountAmount.toFixed(2);
            document.getElementById('personal-preview-percent').textContent = discountPercent + '%';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    };

    window.applyPersonalOverride = function() {
        const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
        const errorEl = document.getElementById('personal-modal-error');

        if (!newPrice || newPrice < 0) {
            errorEl.textContent = 'Please enter a valid price.';
            errorEl.classList.remove('hidden');
            return;
        }

        if (newPrice >= originalClassPrice) {
            errorEl.textContent = 'New price must be less than original price ($' + originalClassPrice.toFixed(2) + ').';
            errorEl.classList.remove('hidden');
            return;
        }

        // Apply the personal override
        appliedOverridePrice = newPrice;
        pendingOverrideCode = personalOverrideCode;

        // Update hidden fields
        document.getElementById('price_override_code').value = personalOverrideCode;
        document.getElementById('price_override_amount').value = newPrice;

        // Update UI
        document.getElementById('applied-override-code').textContent = 'Supervised by: ' + personalOverrideSupervisor + ' (' + personalOverrideCode + ')';
        document.getElementById('applied-override-price').textContent = 'Override price: $' + newPrice.toFixed(2);

        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('override-pending').classList.add('hidden');
        const overrideError = document.getElementById('override-error');
        if (overrideError) overrideError.classList.add('hidden');

        // Update price display
        const discountAmount = originalClassPrice - newPrice;
        updatePriceWithDiscount(newPrice, '-$' + discountAmount.toFixed(2));

        // Update price input and label
        const priceInputEl = document.getElementById('price-input');
        if (priceInputEl) {
            priceInputEl.value = newPrice.toFixed(2);
        }
        // Personal override applied

        // Show the price input container if hidden
        document.getElementById('price-input-container').classList.remove('hidden');

        // Clear promo code if any (override takes precedence)
        if (appliedOfferId) {
            removePromoCode();
        }

        // Close modal
        closePersonalOverrideModal();

        // Clear input field
        document.getElementById('override_code_input').value = '';
    };

    function applyOverrideFromData(data) {
        pendingOverrideCode = data.confirmation_code;
        appliedOverridePrice = parseFloat(data.requested_price);

        // Update hidden fields
        document.getElementById('price_override_code').value = data.confirmation_code;
        document.getElementById('price_override_amount').value = data.requested_price;

        // Update UI
        document.getElementById('applied-override-code').textContent = 'Code: ' + data.confirmation_code;
        document.getElementById('applied-override-price').textContent = 'Override price: $' + parseFloat(data.requested_price).toFixed(2);

        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('override-pending').classList.add('hidden');
        const overrideError = document.getElementById('override-error');
        if (overrideError) overrideError.classList.add('hidden');

        // Update price display
        const discountAmount = originalClassPrice - appliedOverridePrice;
        updatePriceWithDiscount(appliedOverridePrice, '-$' + discountAmount.toFixed(2));

        // Update price input and label
        const priceInputEl = document.getElementById('price-input');
        if (priceInputEl) {
            priceInputEl.value = appliedOverridePrice.toFixed(2);
        }
        // Price override applied

        // Show the price input container if hidden
        document.getElementById('price-input-container').classList.remove('hidden');

        // Clear promo code if any (override takes precedence)
        if (appliedOfferId) {
            removePromoCode();
        }
    }

    window.removeOverride = function() {
        pendingOverrideId = null;
        pendingOverrideCode = null;
        appliedOverridePrice = null;
        stopStatusPolling();

        // Clear hidden fields
        document.getElementById('price_override_code').value = '';
        document.getElementById('price_override_amount').value = '';

        // Reset UI
        document.getElementById('applied-override').classList.add('hidden');
        document.getElementById('override-input-section').classList.remove('hidden');
        const pendingEl = document.getElementById('override-pending');
        if (pendingEl) pendingEl.classList.add('hidden');
        const codeInput = document.getElementById('override_code_input');
        if (codeInput) codeInput.value = '';
        const directInput = document.getElementById('direct-override-price');
        if (directInput) directInput.value = '';

        // Reset price display
        resetPriceDisplay();

        // Reset price input
        const priceInputEl = document.getElementById('price-input');
        if (priceInputEl) {
            priceInputEl.value = originalClassPrice.toFixed(2);
        }
    };

    function resetOverrideUI() {
        pendingOverrideId = null;
        pendingOverrideCode = null;
        stopStatusPolling();

        document.getElementById('override-input-section').classList.remove('hidden');
        document.getElementById('override-pending').classList.add('hidden');
        document.getElementById('override_code_input').value = '';
    }

    window.cancelOverrideRequest = function() {
        if (!pendingOverrideId) return;

        fetch(`/price-override/${pendingOverrideId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resetOverrideUI();
            } else {
                showOverrideError(data.message || 'Failed to cancel request.');
            }
        })
        .catch(error => {
            showOverrideError('Unable to cancel request. Please try again.');
            console.error('Cancel error:', error);
        });
    };

    function showOverrideError(message) {
        const errorEl = document.getElementById('override-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            setTimeout(() => {
                errorEl.classList.add('hidden');
            }, 5000);
        }
    }

    // Handle Enter key in override code input
    document.getElementById('override_code_input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyOverrideCode();
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopStatusPolling();
    });

    // ==================== End Price Override Functions ====================

    // Trial class toggle
    const trialCheckbox = document.getElementById('trial-class');
    const trialAmountInput = document.getElementById('trial-amount');
    const trialAmountContainer = document.getElementById('trial-amount-container');
    const freeTrialBadge = document.getElementById('free-trial-badge');
    const priceInputContainer = document.getElementById('price-input-container');
    const paymentMethodContainer = document.getElementById('payment-method-container');
    const priceInput = document.getElementById('price-input');

    trialCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Show trial amount input
            trialAmountContainer.classList.remove('hidden');

            // Check trial amount to show/hide payment method
            updateTrialPaymentVisibility();
        } else {
            // Restore regular price from session data
            priceInput.value = selectedSessionData ? selectedSessionData.price.toFixed(2) : '0';

            // Hide trial amount input
            trialAmountContainer.classList.add('hidden');

            // Show payment method
            paymentMethodContainer.classList.remove('hidden');
        }
    });

    // Trial amount change handler
    trialAmountInput.addEventListener('input', updateTrialPaymentVisibility);

    function updateTrialPaymentVisibility() {
        const trialAmount = parseFloat(trialAmountInput.value) || 0;

        // Update the actual price_paid field
        priceInput.value = trialAmount.toFixed(2);

        if (trialAmount === 0) {
            // Free trial - hide payment method, show free badge
            paymentMethodContainer.classList.add('hidden');
            freeTrialBadge.classList.remove('hidden');
        } else {
            // Paid trial - show payment method, hide free badge
            paymentMethodContainer.classList.remove('hidden');
            freeTrialBadge.classList.add('hidden');
        }
    }

    // Step navigation
    document.getElementById('step1-next').addEventListener('click', async function() {
        // If new client, create them first
        if (selectedClientId === 'new') {
            const firstName = document.getElementById('new-first-name').value.trim();
            const lastName = document.getElementById('new-last-name').value.trim();
            const email = document.getElementById('new-email').value.trim();
            const phone = document.getElementById('new-phone').value.trim();

            try {
                const res = await fetch('/walk-in/clients/quick-add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ first_name: firstName, last_name: lastName, email, phone })
                });
                const data = await res.json();
                if (data.success) {
                    selectedClientId = data.client.id;
                    selectedClientName = data.client.first_name + ' ' + data.client.last_name;
                    document.getElementById('client-id').value = data.client.id;
                }
            } catch (err) {
                showFormError('Error creating client. Please try again.');
                return;
            }
        }

        // Update summary
        document.getElementById('summary-client').textContent = selectedClientName;
        document.getElementById('summary-class').textContent = selectedClassPlanName;
        if (selectedBookingType === 'period' && selectedPeriodMonths) {
            document.getElementById('summary-datetime').textContent = _periodSessionsData.length + ' sessions · ' + selectedPeriodMonths + ' month' + (selectedPeriodMonths > 1 ? 's' : '');
        } else {
            document.getElementById('summary-datetime').textContent = datePicker.altInput.value + ' at ' + selectedSessionData.time;
        }

        // Set price and store original
        const price = selectedSessionData.price;
        originalClassPrice = price;
        document.getElementById('display-price').textContent = '$' + price.toFixed(2);
        document.getElementById('price-input').value = price.toFixed(2);

        // Reset billing discount when entering booking panel
        removeBillingDiscount();

        // Reset series-specific UI
        document.getElementById('series-price-breakdown').classList.add('hidden');
        document.getElementById('apply-discount-container').classList.add('hidden');
        document.getElementById('apply-discount-checkbox').checked = false;

        // Reset promo code when session changes
        if (appliedOfferId) {
            removePromoCode();
        }

        // Update form action
        document.getElementById('booking-form').action = `/walk-in/class/${selectedSessionId}`;

        goToStep(2);

        // If "Series Class" was selected — show breakdown, reg fee, discount checkbox
        if (selectedBookingType === 'period' && selectedPeriodMonths && selectedSessionData) {
            document.getElementById('billing-discount-section').classList.add('hidden');

            var sessionCount = _periodSessionsData.length;
            var basePerSession = selectedSessionData.price;
            var totalNoDiscount = basePerSession * sessionCount;
            var regFee = selectedSessionData.registrationFee || 0;
            var discounts = selectedSessionData.billingDiscounts || {};
            var discountedTotal = parseFloat(discounts[selectedPeriodMonths]) || 0;
            var savings = totalNoDiscount - discountedTotal;

            // Show price breakdown
            document.getElementById('series-base-price').textContent = '$' + basePerSession.toFixed(2);
            document.getElementById('series-session-count').textContent = sessionCount + ' sessions (' + selectedPeriodMonths + ' month' + (selectedPeriodMonths > 1 ? 's' : '') + ')';
            document.getElementById('series-total-no-discount').textContent = '$' + totalNoDiscount.toFixed(2);
            document.getElementById('series-price-breakdown').classList.remove('hidden');

            // Set display price to total without discount
            document.getElementById('display-price').textContent = '$' + totalNoDiscount.toFixed(2);
            document.getElementById('price-input').value = totalNoDiscount.toFixed(2);

            // Show registration fee
            if (regFee > 0) {
                document.getElementById('reg-fee-amount').textContent = '$' + regFee.toFixed(2);
                document.getElementById('reg-fee-checkbox').checked = true;
                document.getElementById('include_reg_fee_hidden').value = '1';
                document.getElementById('reg-fee-container').classList.remove('hidden');
                _billingRegFee = regFee;
            }

            // Show apply discount checkbox (only if discount exists and is less than full price)
            if (discountedTotal > 0) {
                document.getElementById('apply-discount-amount').textContent = '$' + discountedTotal.toFixed(2);
                document.getElementById('apply-discount-savings').textContent = 'Save $' + savings.toFixed(2);
                document.getElementById('apply-discount-desc').textContent = selectedPeriodMonths + ' month' + (selectedPeriodMonths > 1 ? 's' : '') + ' discounted rate';
                document.getElementById('apply-discount-container').classList.remove('hidden');
            }

            // Store for toggles
            _billingCreditAmount = totalNoDiscount;
            _seriesDiscountedTotal = discountedTotal;
            _seriesTotalNoDiscount = totalNoDiscount;

            recalcSeriesTotal();
        }

        // If "Trial Class" was selected, set price to $0 and hide billing discount
        if (selectedBookingType === 'trial') {
            document.getElementById('billing-discount-section').classList.add('hidden');
            document.getElementById('display-price').textContent = '$0.00';
            document.getElementById('price-input').value = '0';
            document.getElementById('payment-method-hidden').value = 'comp';
        }

        // Fetch available offers for step 2
        fetchAvailableOffers();
    });

    document.getElementById('step2-back').addEventListener('click', () => goToStep(1));

    function goToStep(step) {
        currentStep = step;

        if (step === 1) {
            step1.classList.remove('hidden');
            step2.classList.add('hidden');
        } else {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Quick Create Session Modal Functions
    const quickCreateModal = document.getElementById('quick-create-modal');
    let selectedSlotTime = null;

    window.openQuickCreateModal = function() {
        const classPlanSelect = document.getElementById('class-plan-select');
        const selectedOption = classPlanSelect.options[classPlanSelect.selectedIndex];

        // Update modal with class info
        document.getElementById('modal-class-name').textContent = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];
        document.getElementById('modal-date').textContent = datePicker ? datePicker.altInput.value : 'Select date';
        document.getElementById('modal-class-color').style.backgroundColor = selectedOption.dataset.color || '#6366f1';
        document.getElementById('modal-duration').textContent = document.getElementById('session-duration').value;

        // Reset instructor selections
        document.getElementById('quick-primary-instructor').value = '';

        // Reset time slot selection
        selectedSlotTime = null;
        document.getElementById('quick-start-time').value = '';
        document.getElementById('slots-placeholder').classList.remove('hidden');
        document.getElementById('slots-loading').classList.add('hidden');
        document.getElementById('slots-empty').classList.add('hidden');
        document.getElementById('slots-container').classList.add('hidden');

        // Reset slot tabs
        document.getElementById('morning-slots').innerHTML = '';
        document.getElementById('afternoon-slots').innerHTML = '';
        document.getElementById('evening-slots').innerHTML = '';
        document.getElementById('morning-count').textContent = '0';
        document.getElementById('afternoon-count').textContent = '0';
        document.getElementById('evening-count').textContent = '0';

        // Reset availability panel
        document.getElementById('availability-placeholder').classList.remove('hidden');
        document.getElementById('availability-loading').classList.add('hidden');
        document.getElementById('availability-panel').classList.add('hidden');

        // Fetch defaults for capacity
        fetch(`/walk-in/class-plan-defaults?class_plan_id=${selectedClassPlanId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('quick-capacity').value = data.capacity || 10;
            });

        // Show modal
        quickCreateModal.classList.remove('hidden');
        setTimeout(() => quickCreateModal.classList.add('open'), 10);
        document.body.style.overflow = 'hidden';
    };

    // Slot selection handler
    window.selectTimeSlot = function(time, button) {
        selectedSlotTime = time;
        document.getElementById('quick-start-time').value = time;

        // Update UI - remove active state from all slots
        document.querySelectorAll('#slots-grid .slot-btn').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
        });

        // Add active state to selected slot
        button.classList.remove('btn-outline');
        button.classList.add('btn-primary');

        updateCreateButtonState();
    };

    document.getElementById('quick-primary-instructor').addEventListener('change', function() {
        fetchInstructorAvailability();
        fetchAvailableSlots();
    });

    function fetchAvailableSlots() {
        const instructorId = document.getElementById('quick-primary-instructor').value;
        const date = datePicker ? datePicker.formatDate(datePicker.selectedDates[0], 'Y-m-d') : null;
        const duration = document.getElementById('session-duration').value;

        const placeholder = document.getElementById('slots-placeholder');
        const loading = document.getElementById('slots-loading');
        const empty = document.getElementById('slots-empty');
        const container = document.getElementById('slots-container');

        // Reset slot selection
        selectedSlotTime = null;
        document.getElementById('quick-start-time').value = '';

        if (!instructorId || !date) {
            placeholder.classList.remove('hidden');
            loading.classList.add('hidden');
            empty.classList.add('hidden');
            container.classList.add('hidden');
            updateCreateButtonState();
            return;
        }

        // Show loading
        placeholder.classList.add('hidden');
        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        container.classList.add('hidden');

        fetch(`/walk-in/available-slots?instructor_id=${instructorId}&date=${date}&duration=${duration}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');

                if (!data.slots || data.slots.length === 0) {
                    empty.classList.remove('hidden');
                    updateCreateButtonState();
                    return;
                }

                // Categorize slots by time of day
                const morning = [];   // Before 12:00 PM
                const afternoon = []; // 12:00 PM - 5:00 PM
                const evening = [];   // After 5:00 PM

                data.slots.forEach(slot => {
                    const hour = parseInt(slot.time.split(':')[0]);
                    if (hour < 12) {
                        morning.push(slot);
                    } else if (hour < 17) {
                        afternoon.push(slot);
                    } else {
                        evening.push(slot);
                    }
                });

                // Render each category
                renderSlotCategory('morning', morning);
                renderSlotCategory('afternoon', afternoon);
                renderSlotCategory('evening', evening);

                // Update counts
                document.getElementById('morning-count').textContent = morning.length;
                document.getElementById('afternoon-count').textContent = afternoon.length;
                document.getElementById('evening-count').textContent = evening.length;

                // Show first tab that has slots
                if (morning.length > 0) {
                    switchSlotTab('morning');
                } else if (afternoon.length > 0) {
                    switchSlotTab('afternoon');
                } else if (evening.length > 0) {
                    switchSlotTab('evening');
                }

                container.classList.remove('hidden');
                updateCreateButtonState();
            })
            .catch(err => {
                console.error('Error fetching slots:', err);
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
                updateCreateButtonState();
            });
    }

    function renderSlotCategory(category, slots) {
        const container = document.getElementById(`${category}-slots`);
        if (slots.length === 0) {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = slots.map(slot => `
            <button type="button"
                    class="btn btn-sm btn-outline slot-btn"
                    onclick="selectTimeSlot('${slot.time}', this)">
                ${slot.display}
            </button>
        `).join('');
    }

    window.switchSlotTab = function(tab) {
        // Update tab buttons
        document.querySelectorAll('.slot-tab').forEach(btn => {
            if (btn.dataset.tab === tab) {
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.add('btn-outline');
                btn.classList.remove('btn-primary');
            }
        });

        // Show/hide panels
        document.querySelectorAll('.slot-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById(`${tab}-slots`);
        const tabEmpty = document.getElementById('tab-empty');

        if (activePanel.children.length > 0) {
            activePanel.classList.remove('hidden');
            tabEmpty.classList.add('hidden');
        } else {
            tabEmpty.classList.remove('hidden');
        }
    };

    // Instructor Availability Functions
    let instructorWorksToday = true; // Track if instructor works on selected day

    function fetchInstructorAvailability() {
        const instructorId = document.getElementById('quick-primary-instructor').value;
        const date = datePicker ? datePicker.formatDate(datePicker.selectedDates[0], 'Y-m-d') : null;

        const placeholder = document.getElementById('availability-placeholder');
        const loading = document.getElementById('availability-loading');
        const panel = document.getElementById('availability-panel');
        const createBtn = document.getElementById('quick-create-btn');

        if (!instructorId || !date) {
            placeholder.classList.remove('hidden');
            loading.classList.add('hidden');
            panel.classList.add('hidden');
            instructorWorksToday = true; // Reset to allow creation
            updateCreateButtonState();
            return;
        }

        // Show loading
        placeholder.classList.add('hidden');
        loading.classList.remove('hidden');
        panel.classList.add('hidden');

        fetch(`/walk-in/instructor-availability?instructor_id=${instructorId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                panel.classList.remove('hidden');
                instructorWorksToday = data.works_today;
                displayInstructorAvailability(data);
                updateCreateButtonState();
            })
            .catch(err => {
                console.error('Error fetching availability:', err);
                loading.classList.add('hidden');
                placeholder.classList.remove('hidden');
                instructorWorksToday = true; // Reset on error
                updateCreateButtonState();
            });
    }

    function updateCreateButtonState() {
        const createBtn = document.getElementById('quick-create-btn');
        const instructorSelected = document.getElementById('quick-primary-instructor').value;

        if (!instructorWorksToday || !selectedSlotTime || !instructorSelected) {
            createBtn.disabled = true;
            createBtn.classList.add('btn-disabled');
        } else {
            createBtn.disabled = false;
            createBtn.classList.remove('btn-disabled');
        }
    }

    function displayInstructorAvailability(data) {
        // Instructor info
        document.getElementById('avail-initials').textContent = data.instructor.initials;
        document.getElementById('avail-name').textContent = data.instructor.name;
        document.getElementById('avail-date').textContent = data.formatted_date;

        // Working days
        const dayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        const workingDaysContainer = document.getElementById('avail-working-days');
        workingDaysContainer.innerHTML = data.working_days.map((works, index) => {
            const isToday = index === data.day_of_week;
            const baseClass = 'size-8 rounded text-xs font-medium flex items-center justify-center';
            const colorClass = works
                ? (isToday ? 'bg-primary text-primary-content ring-2 ring-primary ring-offset-1' : 'bg-success/20 text-success')
                : 'bg-base-200 text-base-content/40';
            return `<span class="${baseClass} ${colorClass}">${dayLetters[index]}</span>`;
        }).join('');

        // Today's hours status
        const hoursStatus = document.getElementById('avail-hours-status');
        if (!data.works_today) {
            hoursStatus.innerHTML = `
                <div class="alert alert-error py-2 px-3">
                    <span class="icon-[tabler--calendar-x] size-4"></span>
                    <div>
                        <span class="text-sm font-medium">Does not work on ${data.day_name}s</span>
                        <p class="text-xs opacity-80 mt-0.5">Cannot create session on this day</p>
                    </div>
                </div>
            `;
        } else if (data.availability) {
            hoursStatus.innerHTML = `
                <div class="alert alert-success py-2 px-3">
                    <span class="icon-[tabler--clock-check] size-4"></span>
                    <span class="text-sm">Available ${data.availability.from} - ${data.availability.to}</span>
                </div>
            `;
        } else {
            hoursStatus.innerHTML = `
                <div class="alert alert-info py-2 px-3">
                    <span class="icon-[tabler--clock] size-4"></span>
                    <span class="text-sm">No hour restrictions set</span>
                </div>
            `;
        }

        // Existing sessions
        const sessionsContainer = document.getElementById('avail-existing-sessions');
        if (data.existing_sessions.length === 0) {
            sessionsContainer.innerHTML = `<p class="text-sm text-base-content/50">No sessions scheduled</p>`;
        } else {
            sessionsContainer.innerHTML = data.existing_sessions.map(session => `
                <div class="flex items-center gap-2 text-sm bg-base-200 rounded px-2 py-1">
                    <span class="icon-[tabler--calendar-event] size-4 text-base-content/50"></span>
                    <span class="font-medium">${session.time}</span>
                    <span class="text-base-content/60 truncate">- ${session.title}</span>
                </div>
            `).join('');
        }

        // Weekly workload
        document.getElementById('avail-classes-count').textContent = data.workload.classes_this_week;
        document.getElementById('avail-hours-count').textContent = data.workload.hours_this_week;

        if (data.workload.max_classes) {
            document.getElementById('avail-classes-max').textContent = '/' + data.workload.max_classes;
        } else {
            document.getElementById('avail-classes-max').textContent = '';
        }

        if (data.workload.max_hours) {
            document.getElementById('avail-hours-max').textContent = '/' + data.workload.max_hours;
        } else {
            document.getElementById('avail-hours-max').textContent = '';
        }
    }

    window.closeQuickCreateModal = function() {
        quickCreateModal.classList.remove('open');
        setTimeout(() => {
            quickCreateModal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    };

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !quickCreateModal.classList.contains('hidden')) {
            closeQuickCreateModal();
        }
    });

    window.submitQuickCreate = async function() {
        const btn = document.getElementById('quick-create-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Creating...';

        // Get time from selected slot
        const startTime = selectedSlotTime;
        const duration = document.getElementById('session-duration').value; // Use duration from main form
        const capacity = document.getElementById('quick-capacity').value;
        const primaryInstructorId = document.getElementById('quick-primary-instructor').value;
        const date = datePicker ? datePicker.formatDate(datePicker.selectedDates[0], 'Y-m-d') : null;

        if (!date || !startTime) {
            showModalError('Please select a time slot');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!primaryInstructorId) {
            showModalError('Please select a primary instructor');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        // Check if instructor works today
        if (!instructorWorksToday) {
            showModalError('Cannot create session - instructor does not work on this day');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        try {
            const res = await fetch('/walk-in/sessions/quick-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    class_plan_id: selectedClassPlanId,
                    date: date,
                    start_time: startTime,
                    duration_minutes: parseInt(duration),
                    capacity: parseInt(capacity),
                    primary_instructor_id: primaryInstructorId
                })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to create session');
            }

            if (data.success) {
                closeQuickCreateModal();

                // Add the new session to the list and select it
                const container = document.getElementById('sessions-list');
                const session = data.session;

                document.getElementById('sessions-empty').classList.add('hidden');
                document.getElementById('session-selection').classList.remove('hidden');
                container.innerHTML = `
                    <label class="flex items-center gap-4 p-4 border border-primary rounded-lg cursor-pointer bg-primary/5 transition-all">
                        <input type="radio" name="session_radio" value="${session.id}" class="radio radio-primary" checked
                               data-title="${session.title}"
                               data-time="${session.time}"
                               data-price="${session.price || 0}"
                               data-start="${session.start_time_iso}"
                               data-end="${session.end_time_iso}"
                               data-billing-discounts='${JSON.stringify(session.billing_discounts || {})}'
                               data-registration-fee="${session.registration_fee || 0}"
                               data-cancellation-fee="${session.cancellation_fee || 0}"
                               data-grace-hours="${session.cancellation_grace_hours || 48}"
                               onchange="selectSession(this)">
                        <div class="flex-1">
                            <div class="font-semibold">${session.time}</div>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--user] size-4"></span>
                                    ${session.instructor}
                                </span>
                                <span class="badge badge-success badge-sm">Just created</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-success">${session.spots_remaining}</div>
                            <div class="text-xs text-base-content/60">spots</div>
                        </div>
                    </label>
                `;

                // Auto-select the new session
                selectedSessionId = String(session.id);
                selectedSessionData = {
                    title: session.title,
                    time: session.time,
                    price: parseFloat(session.price) || 0,
                    startTime: session.start_time_iso,
                    endTime: session.end_time_iso
                };
                document.getElementById('session-id').value = session.id;

                // Update check-in visibility and validate
                updateCheckInVisibility();

                // Ensure validation runs after DOM update
                setTimeout(() => {
                    validateStep1();
                }, 50);
            } else {
                showModalError(data.message || 'Failed to create session. Please try again.');
            }
        } catch (err) {
            console.error(err);
            showModalError(err.message || 'Error creating session. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    };

    // Preload session if provided via URL parameter
    @if($preloadSession)
    (function() {
        const preloadData = {
            id: {{ $preloadSession->id }},
            classPlanId: {{ $preloadSession->class_plan_id }},
            date: '{{ $preloadSession->start_time->format('Y-m-d') }}'
        };

        // Wait for HSSelect to initialize
        setTimeout(() => {
            // Set class plan value
            const classPlanSelect = document.getElementById('class-plan-select');
            if (classPlanSelect) {
                classPlanSelect.value = preloadData.classPlanId;

                // Trigger HSSelect update if it exists
                const hsSelect = window.HSSelect?.getInstance(classPlanSelect);
                if (hsSelect) {
                    hsSelect.setValue(String(preloadData.classPlanId));
                }

                // Trigger change event to load sessions
                classPlanSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Store the preload session ID to auto-select after sessions load
            window.preloadSessionId = preloadData.id;
        }, 100);
    })();
    @endif
});
</script>
@endpush
@endsection
