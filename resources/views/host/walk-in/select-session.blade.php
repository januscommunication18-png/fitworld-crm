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

                    {{-- Date Selection (shown after class plan selected) --}}
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

                    {{-- Duration Selection (shown after date selected) --}}
                    <div class="form-control mb-6 hidden" id="duration-selection">
                        <label class="label" for="session-duration">
                            <span class="label-text font-medium">Duration (minutes)</span>
                        </label>
                        <div class="join w-full">
                            <button type="button" class="btn btn-outline join-item duration-preset" data-duration="30">30</button>
                            <button type="button" class="btn btn-outline join-item duration-preset" data-duration="45">45</button>
                            <button type="button" class="btn btn-primary join-item duration-preset active" data-duration="60">60</button>
                            <button type="button" class="btn btn-outline join-item duration-preset" data-duration="90">90</button>
                            <input type="number"
                                   id="session-duration"
                                   class="input input-bordered join-item w-24 text-center"
                                   value="60"
                                   min="5"
                                   max="480"
                                   placeholder="Custom">
                            <span class="btn btn-ghost join-item pointer-events-none">min</span>
                        </div>
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

                                {{-- Quick Actions --}}
                                <div class="flex flex-col sm:flex-row justify-center gap-3 mb-4">
                                    <button type="button" class="btn btn-primary" onclick="openQuickCreateModal()">
                                        <span class="icon-[tabler--plus] size-5"></span>
                                        Create Session
                                    </button>
                                </div>

                                {{-- Next Available --}}
                                <div id="next-available" class="hidden">
                                    <div class="divider text-xs text-base-content/40">OR SELECT UPCOMING</div>
                                    <div id="next-available-dates" class="flex flex-wrap justify-center gap-2"></div>
                                </div>
                            </div>
                        </div>
                        <div id="sessions-list" class="space-y-3 max-h-64 overflow-y-auto">
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

        {{-- Step 2: Pricing & Payment --}}
        <div id="step-2" class="card bg-base-100 border border-base-200 hidden">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <span class="icon-[tabler--credit-card] size-5"></span>
                    Pricing & Payment
                </h2>

                {{-- Booking Summary --}}
                <div class="bg-base-200/50 rounded-lg p-4 mb-6">
                    <div class="text-sm font-medium text-base-content/60 mb-2">Booking Summary</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Client</span>
                            <span class="font-medium" id="summary-client">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Class</span>
                            <span class="font-medium" id="summary-class">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Date & Time</span>
                            <span class="font-medium" id="summary-datetime">--</span>
                        </div>
                    </div>
                </div>

                {{-- Price Display --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Class Price</span>
                    </label>
                    <div class="text-3xl font-bold text-primary" id="display-price">$0.00</div>
                </div>

                {{-- Price Input --}}
                <div class="form-control mb-4" id="price-input-container">
                    <label class="label" for="price-input">
                        <span class="label-text font-medium">Amount to Charge</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">$</span>
                        <input type="number"
                               id="price-input"
                               name="price_paid"
                               step="0.01"
                               min="0"
                               class="input input-bordered w-full pl-8"
                               value="0">
                    </div>
                </div>

                {{-- Trial Class Checkbox --}}
                <div class="form-control mb-4">
                    <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                        <input type="checkbox" id="trial-class" name="is_trial" value="1" class="checkbox checkbox-success">
                        <div class="flex-1">
                            <span class="font-semibold">Trial Class</span>
                            <span class="text-sm text-base-content/60 block">First-time client complimentary session</span>
                        </div>
                    </label>
                </div>

                {{-- Trial Amount Input (shown when trial is checked) --}}
                <div id="trial-amount-container" class="hidden mb-4">
                    <div class="form-control">
                        <label class="label" for="trial-amount">
                            <span class="label-text font-medium">Trial Amount to Charge</span>
                            <span class="label-text-alt text-base-content/50">Enter 0 for free trial</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">$</span>
                            <input type="number"
                                   id="trial-amount"
                                   step="0.01"
                                   min="0"
                                   class="input input-bordered w-full pl-8"
                                   value="0"
                                   placeholder="0.00">
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
                <div class="form-control mb-6" id="payment-method-container">
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

                {{-- Notes --}}
                <div class="form-control mb-6">
                    <label class="label" for="notes">
                        <span class="label-text font-medium">Notes (optional)</span>
                    </label>
                    <textarea id="notes" name="notes" rows="2" class="textarea textarea-bordered" placeholder="Any notes about this booking..."></textarea>
                </div>

                {{-- Intake Form Section --}}
                <div id="intake-form-section" class="mb-6 hidden">
                    <div class="form-control">
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-info has-[:checked]:bg-info/5 transition-all">
                            <input type="checkbox" id="send-intake-form" name="send_intake_form" value="1" class="checkbox checkbox-info">
                            <div class="flex-1">
                                <span class="font-semibold">Send Intake Form</span>
                                <span class="text-sm text-base-content/60 block">Email questionnaire(s) to client after booking</span>
                            </div>
                            <span class="icon-[tabler--file-text] size-6 text-info"></span>
                        </label>
                    </div>

                    {{-- Questionnaire Selection (shown when checkbox is checked) --}}
                    <div id="questionnaire-selection" class="hidden mt-4 p-4 bg-base-200/50 rounded-lg">
                        <div class="text-sm font-medium text-base-content/70 mb-3">Select questionnaires to send:</div>

                        {{-- Loading state --}}
                        <div id="questionnaires-loading" class="hidden flex items-center justify-center py-4">
                            <span class="loading loading-spinner loading-sm text-primary"></span>
                            <span class="ml-2 text-sm text-base-content/60">Loading questionnaires...</span>
                        </div>

                        {{-- No questionnaires available --}}
                        <div id="questionnaires-empty" class="hidden text-center py-4">
                            <span class="icon-[tabler--file-off] size-6 text-base-content/30 mx-auto mb-2"></span>
                            <p class="text-sm text-base-content/50">No questionnaires attached to this class plan</p>
                            <a href="{{ route('questionnaires.index') }}" target="_blank" class="text-xs text-primary hover:underline mt-1 inline-block">
                                Manage questionnaires
                            </a>
                        </div>

                        {{-- No email warning --}}
                        <div id="no-email-warning" class="hidden alert alert-warning mb-3">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <span class="text-sm">Client has no email address. Please add an email to send intake forms.</span>
                        </div>

                        {{-- Questionnaire List --}}
                        <div id="questionnaires-list" class="space-y-2">
                            {{-- Questionnaires loaded via AJAX --}}
                        </div>
                    </div>
                </div>

                {{-- Check in now (only shown for current time slot sessions) --}}
                <div id="check-in-now-container" class="form-control mb-6 hidden">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary" id="check-in-now-checkbox">
                        <div>
                            <span class="font-medium">Check in client now</span>
                            <span class="text-sm text-base-content/60 block">Mark as arrived immediately after booking</span>
                        </div>
                    </label>
                </div>

                <input type="hidden" name="payment_method" value="manual">

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
            document.getElementById('date-selection').classList.add('hidden');
            document.getElementById('duration-selection').classList.add('hidden');
            document.getElementById('session-selection').classList.add('hidden');
            validateStep1();
            return;
        }

        selectedClassPlanId = classPlanSelect.value;
        selectedClassPlanName = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];

        // Show date selection
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

        // Fetch default duration for this class plan
        fetch(`/walk-in/class-plan-defaults?class_plan_id=${selectedClassPlanId}`)
            .then(res => res.json())
            .then(data => {
                const defaultDuration = data.duration_minutes || 60;
                document.getElementById('session-duration').value = defaultDuration;
                updateDurationPresets(defaultDuration);
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

    // Duration preset buttons
    document.querySelectorAll('.duration-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const duration = parseInt(this.dataset.duration);
            document.getElementById('session-duration').value = duration;
            updateDurationPresets(duration);
        });
    });

    // Duration input change
    document.getElementById('session-duration').addEventListener('input', function() {
        updateDurationPresets(parseInt(this.value) || 0);
    });

    function updateDurationPresets(duration) {
        document.querySelectorAll('.duration-preset').forEach(btn => {
            const presetVal = parseInt(btn.dataset.duration);
            if (presetVal === duration) {
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-primary', 'active');
            } else {
                btn.classList.add('btn-outline');
                btn.classList.remove('btn-primary', 'active');
            }
        });
    }

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
            });
    }

    window.jumpToDate = function(date) {
        if (datePicker) {
            datePicker.setDate(date, true);
        }
    };

    window.selectSession = function(radio) {
        selectedSessionId = radio.value;
        selectedSessionData = {
            title: selectedClassPlanName,
            time: radio.dataset.time,
            price: parseFloat(radio.dataset.price) || 0,
            startTime: radio.dataset.start,
            endTime: radio.dataset.end
        };
        document.getElementById('session-id').value = selectedSessionId;

        // Check if session is happening now (30 min before start to end time)
        updateCheckInVisibility();

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
            // Make regular price readonly
            priceInput.readOnly = true;
            priceInput.classList.add('bg-base-200', 'cursor-not-allowed');

            // Show trial amount input
            trialAmountContainer.classList.remove('hidden');

            // Check trial amount to show/hide payment method
            updateTrialPaymentVisibility();
        } else {
            // Restore regular price
            priceInput.readOnly = false;
            priceInput.classList.remove('bg-base-200', 'cursor-not-allowed');
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
                alert('Error creating client. Please try again.');
                return;
            }
        }

        // Update summary
        document.getElementById('summary-client').textContent = selectedClientName;
        document.getElementById('summary-class').textContent = selectedClassPlanName;
        document.getElementById('summary-datetime').textContent = datePicker.altInput.value + ' at ' + selectedSessionData.time;

        // Set price
        const price = selectedSessionData.price;
        document.getElementById('display-price').textContent = '$' + price.toFixed(2);
        document.getElementById('price-input').value = price.toFixed(2);

        // Update form action
        document.getElementById('booking-form').action = `/walk-in/class/${selectedSessionId}`;

        goToStep(2);
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
            alert('Please select a time slot');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!primaryInstructorId) {
            alert('Please select a primary instructor');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        // Check if instructor works today
        if (!instructorWorksToday) {
            alert('Cannot create session - instructor does not work on this day');
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
                alert(data.message || 'Failed to create session. Please try again.');
            }
        } catch (err) {
            console.error(err);
            alert(err.message || 'Error creating session. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    };
});
</script>
@endpush
@endsection
