@extends('layouts.dashboard')

@section('title', 'Book Service')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('bookings.index') }}"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Book Service</li>
    </ol>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Book Service</h1>
            <p class="text-base-content/60">Book a client for a service appointment</p>
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

        {{-- Step 1: Client, Date & Slot Selection --}}
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

                    {{-- Selected Client Display --}}
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

            {{-- Service & Slot Selection --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        Select Service & Date
                    </h2>

                    {{-- Service Plan Selection --}}
                    <div class="form-control mb-6">
                        <label class="label" for="service-plan-select">
                            <span class="label-text font-medium">Service Type</span>
                        </label>
                        <select id="service-plan-select" name="service_plan_id" class="hidden"
                            data-select='{
                                "placeholder": "Select a service type...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select a service type...</option>
                            @foreach($servicePlans as $plan)
                            <option value="{{ $plan->id }}" data-name="{{ $plan->name }}" data-duration="{{ $plan->duration_minutes }}" data-price="{{ $plan->price }}">
                                {{ $plan->name }} - {{ $plan->duration_minutes }} min - ${{ number_format($plan->price, 2) }}
                            </option>
                            @endforeach
                        </select>
                        @if($servicePlans->isEmpty())
                        <div class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg mt-2">
                            <span class="icon-[tabler--massage] size-8 text-base-content/30 mx-auto mb-2"></span>
                            <p class="text-base-content/60">No active service plans in catalog</p>
                        </div>
                        @endif
                    </div>

                    {{-- Booking Type Selection (shown after service plan selected) --}}
                    <div class="form-control mb-6 hidden" id="booking-type-selection">
                        <label class="label">
                            <span class="label-text font-medium">Booking Type</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3" id="booking-type-options">
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-primary/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="booking_type" value="single" class="radio radio-primary" checked onchange="selectServiceBookingType('single')">
                                <div>
                                    <div class="font-medium">Single Service</div>
                                    <div class="text-xs text-base-content/60">Book one slot</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-success/30 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="booking_type" value="period" class="radio radio-success" onchange="selectServiceBookingType('period')">
                                <div>
                                    <div class="font-medium">Series Service</div>
                                    <div class="text-xs text-base-content/60">Prepay for a billing period</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 border-base-content/10 rounded-lg cursor-pointer hover:border-warning/30 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all">
                                <input type="radio" name="booking_type" value="trial" class="radio radio-warning" onchange="selectServiceBookingType('trial')">
                                <div>
                                    <div class="font-medium">Trial Service</div>
                                    <div class="text-xs text-base-content/60">Free or discounted first session</div>
                                </div>
                            </label>
                        </div>

                        {{-- Period Selection (shown when "Series Service" selected) --}}
                        <div id="svc-period-selection" class="hidden mt-4">
                            <label class="label"><span class="label-text font-medium">Select Period</span></label>
                            <div id="svc-period-options" class="flex gap-2">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Picker (shown for series service with multiple schedules) --}}
                    <div class="form-control hidden mb-6" id="svc-schedule-picker">
                        <label class="label">
                            <span class="label-text font-medium">Select Schedule</span>
                        </label>
                        <div id="svc-schedule-picker-list" class="space-y-2"></div>
                    </div>

                    {{-- Series Slot Summary (shown for series service after schedule selected) --}}
                    <div class="form-control hidden" id="svc-series-slot-summary">
                        <label class="label">
                            <span class="label-text font-medium">Available Slots</span>
                        </label>
                        <div id="svc-series-slots-loading" class="hidden">
                            <div class="flex items-center justify-center py-8">
                                <span class="loading loading-spinner loading-md"></span>
                                <span class="ml-2 text-base-content/60">Loading slots...</span>
                            </div>
                        </div>
                        <div id="svc-series-slots-empty" class="hidden">
                            <div class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--calendar-off] size-8 text-base-content/30 mx-auto mb-2"></span>
                                <p class="text-base-content/60">No available slots for this service in the selected period</p>
                            </div>
                        </div>
                        <div id="svc-series-slots-list" class="space-y-3"></div>
                    </div>

                    {{-- Date Selection --}}
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

                    {{-- Slot Selection --}}
                    <div class="form-control hidden" id="slot-selection">
                        <label class="label">
                            <span class="label-text font-medium">Available Slots</span>
                        </label>
                        <div id="slots-loading" class="hidden">
                            <div class="flex items-center justify-center py-8">
                                <span class="loading loading-spinner loading-md"></span>
                                <span class="ml-2 text-base-content/60">Loading slots...</span>
                            </div>
                        </div>
                        <div id="slots-empty" class="hidden">
                            <div class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--calendar-off] size-8 text-base-content/30 mx-auto mb-2"></span>
                                <p class="text-base-content/60 mb-4">No available slots for this service on selected date</p>

                                {{-- Quick Create --}}
                                <div class="flex flex-col sm:flex-row justify-center gap-3 mb-4">
                                    <button type="button" class="btn btn-primary" onclick="openQuickCreateModal()">
                                        <span class="icon-[tabler--plus] size-5"></span>
                                        Create Slot
                                    </button>
                                </div>

                                {{-- Next Available --}}
                                <div id="next-available" class="hidden">
                                    <div class="divider text-xs text-base-content/40">OR SELECT UPCOMING</div>
                                    <div id="next-available-dates" class="flex flex-wrap justify-center gap-2"></div>
                                </div>
                            </div>
                        </div>
                        <div id="slots-list" class="space-y-3 max-h-64 overflow-y-auto">
                            {{-- Slots loaded via AJAX --}}
                        </div>
                        <input type="hidden" name="slot_id" id="slot-id" value="">
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

        {{-- Period Slots Modal --}}
        <div id="svc-period-slots-modal" class="fixed inset-0 z-50 hidden">
            <div class="fixed inset-0 bg-black/50" onclick="closeSvcPeriodSlotsModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="bg-base-100 rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] flex flex-col pointer-events-auto relative">
                    <div class="flex items-center justify-between p-4 border-b border-base-200">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                            <span id="svc-period-modal-title">Slots</span>
                        </h3>
                        <button type="button" class="btn btn-sm btn-circle btn-ghost" onclick="closeSvcPeriodSlotsModal()">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        <div id="svc-period-modal-slots" class="space-y-2"></div>
                    </div>
                    <div class="p-4 border-t border-base-200">
                        <button type="button" class="btn btn-ghost w-full" onclick="closeSvcPeriodSlotsModal()">Close</button>
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
                <div class="bg-base-200/50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--user] size-4 text-base-content/50"></span>
                                <span class="font-medium" id="summary-client">--</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--massage] size-4 text-base-content/50"></span>
                                <span id="summary-service">--</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--calendar] size-4 text-base-content/50"></span>
                                <span class="text-sm text-base-content/70" id="summary-datetime">--</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--user-check] size-4 text-base-content/50"></span>
                                <span class="text-sm text-base-content/70" id="summary-instructor">--</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-primary" id="display-price">$0.00</div>
                            <span id="svc-original-price-display" class="text-sm text-base-content/50 line-through hidden">$0.00</span>
                            <div id="svc-discount-badge" class="hidden mt-1">
                                <span class="badge badge-success badge-sm gap-1">
                                    <span class="icon-[tabler--discount-check] size-3"></span>
                                    <span id="svc-discount-badge-text">Discount</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Price Override Hidden Fields --}}
                <input type="hidden" name="price_override_code" id="price_override_code" value="">
                <input type="hidden" name="price_override_amount" id="price_override_amount" value="">

                @if($canOverridePrice ?? false)
                {{-- Direct price edit for managers/owners with override permission --}}
                <div class="card bg-base-200/30 border border-base-300 mb-4">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                            <span class="font-semibold text-sm">Price Override</span>
                            <span class="badge badge-success badge-xs">Authorized</span>
                        </div>

                        <div id="applied-override" class="hidden mb-3">
                            <div class="alert alert-sm bg-primary/10 border-primary/20">
                                <span class="icon-[tabler--check] size-4 text-primary"></span>
                                <div class="flex-1">
                                    <span class="font-semibold text-primary text-sm" id="applied-override-code">Direct Override</span>
                                    <p class="text-xs text-primary/80" id="applied-override-price"></p>
                                </div>
                                <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </div>

                        <div id="override-input-section">
                            <div class="join w-full">
                                <span class="join-item btn btn-sm no-animation">$</span>
                                <input type="number" step="0.01" min="0" id="direct-override-price"
                                       class="input input-bordered input-sm join-item flex-1"
                                       placeholder="Enter new price...">
                                <button type="button" onclick="applyDirectOverride()" class="btn btn-primary btn-sm join-item">
                                    Apply
                                </button>
                            </div>
                            <p class="text-xs text-base-content/50 mt-1">You have override permission.</p>
                        </div>

                        <p id="override-error" class="text-error text-xs mt-1 hidden"></p>
                    </div>
                </div>
                @elseif($canRequestOverride ?? false)
                {{-- Override request flow for staff --}}
                <div class="card bg-base-200/30 border border-base-300 mb-4">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                            <span class="font-semibold text-sm">Price Override</span>
                        </div>

                        <div id="applied-override" class="hidden mb-3">
                            <div class="alert alert-sm bg-primary/10 border-primary/20">
                                <span class="icon-[tabler--check] size-4 text-primary"></span>
                                <div class="flex-1">
                                    <span class="font-semibold text-primary text-sm" id="applied-override-code"></span>
                                    <p class="text-xs text-primary/80" id="applied-override-price"></p>
                                </div>
                                <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </div>

                        <div id="override-input-section">
                            <div class="flex gap-2 mb-2">
                                <input type="text" id="override_code_input" placeholder="PO-XXXXX or MY-XXXXX"
                                       class="input input-bordered input-sm flex-1 uppercase" maxlength="10">
                                <button type="button" onclick="verifyOverrideCode()" id="verify-override-btn"
                                        class="btn btn-sm btn-outline">Verify</button>
                            </div>
                            <button type="button" onclick="showOverrideModal()" class="btn btn-outline btn-primary btn-xs btn-block mt-2">
                                <span class="icon-[tabler--send] size-3"></span>
                                Request New Override
                            </button>
                        </div>

                        <div id="override-pending" class="hidden">
                            <div class="alert alert-sm bg-warning/10 border-warning/20">
                                <span class="icon-[tabler--clock] size-4 text-warning animate-pulse"></span>
                                <div class="flex-1">
                                    <span class="font-semibold text-warning text-sm">Pending</span>
                                    <p class="text-xs text-warning/80">Code: <span id="pending-code" class="font-mono font-bold"></span></p>
                                </div>
                                <button type="button" onclick="checkOverrideStatus()" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--refresh] size-3"></span>
                                </button>
                            </div>
                        </div>
                        <p id="override-error" class="text-error text-xs mt-1 hidden"></p>
                    </div>
                </div>
                @endif

                {{-- Price Override Modals - Only show when feature is enabled --}}
                @if($canRequestOverride ?? false)
                {{-- Price Override Modal --}}
                <div id="override-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
                    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeOverrideModal()"></div>
                    <div class="modal-box fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6">
                        <button type="button" onclick="closeOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                        <h3 class="font-bold text-lg mb-4">Request Price Override</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                <span>Original Price</span>
                                <span class="font-semibold" id="modal-original-price">$0.00</span>
                            </div>
                            <div class="form-control">
                                <label class="label" for="override-new-price"><span class="label-text">New Price *</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                    <input type="number" id="override-new-price" step="0.01" min="0" class="input input-bordered w-full pl-8" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-control">
                                <label class="label" for="override-reason"><span class="label-text">Reason (optional)</span></label>
                                <textarea id="override-reason" rows="2" class="textarea textarea-bordered" placeholder="Reason..."></textarea>
                            </div>
                            <div id="override-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg text-sm">
                                <div class="flex justify-between"><span>Discount</span><span class="font-semibold text-success" id="preview-discount">$0.00</span></div>
                            </div>
                            <p id="modal-error" class="text-error text-sm hidden"></p>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" onclick="closeOverrideModal()" class="btn btn-ghost">Cancel</button>
                            <button type="button" onclick="submitOverrideRequest()" id="submit-override-btn" class="btn btn-primary">
                                <span class="icon-[tabler--send] size-4"></span> Send
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Personal Override Modal --}}
                <div id="personal-override-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
                    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closePersonalOverrideModal()"></div>
                    <div class="modal-box fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6">
                        <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                            Override Price
                        </h3>
                        <div class="alert alert-success mb-4">
                            <span class="icon-[tabler--user-check] size-5"></span>
                            <div>
                                <p class="font-semibold">Supervised by</p>
                                <p class="text-sm"><span id="personal-supervisor-name">Manager</span> (<span id="personal-supervisor-code">MY-XXXXX</span>)</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                <span>Original Price</span>
                                <span class="font-semibold" id="personal-modal-original-price">$0.00</span>
                            </div>
                            <div class="form-control">
                                <label class="label" for="personal-override-new-price"><span class="label-text">New Price *</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                    <input type="number" id="personal-override-new-price" step="0.01" min="0"
                                           class="input input-bordered w-full pl-8" placeholder="0.00"
                                           oninput="updatePersonalOverridePreview()">
                                </div>
                            </div>
                            <div id="personal-override-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg text-sm">
                                <div class="flex justify-between"><span>Discount</span><span class="font-semibold text-success" id="personal-preview-discount">$0.00</span></div>
                                <div class="flex justify-between mt-1"><span>Percentage</span><span class="font-semibold text-success" id="personal-preview-percent">0%</span></div>
                            </div>
                            <p id="personal-modal-error" class="text-error text-sm hidden"></p>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-ghost">Cancel</button>
                            <button type="button" onclick="applyPersonalOverride()" class="btn btn-success">
                                <span class="icon-[tabler--check] size-4"></span> Apply Override
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Series Price Breakdown (shown for series bookings) --}}
                <div id="svc-series-price-breakdown" class="hidden mb-4">
                    <div class="bg-base-200/30 rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">Base price per slot</span>
                            <span class="font-medium" id="svc-series-base-price">$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">Total slots</span>
                            <span class="font-medium" id="svc-series-slot-count">0</span>
                        </div>
                        <div class="divider my-1"></div>
                        <div class="flex items-center justify-between font-semibold">
                            <span>Total (without discount)</span>
                            <span id="svc-series-total-no-discount">$0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Registration Fee (shown for series bookings with reg fee) --}}
                <div id="svc-series-reg-fee-container" class="hidden mb-4">
                    <label class="flex items-center justify-between gap-3 p-3 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-200/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="svc-series-reg-fee-checkbox" class="checkbox checkbox-primary checkbox-sm" checked onchange="toggleSvcSeriesRegFee()">
                            <div>
                                <span class="font-medium text-sm">Include Registration Fee</span>
                                <span class="text-xs text-base-content/60 block">One-time fee for billing period signup</span>
                            </div>
                        </div>
                        <span class="font-bold text-primary" id="svc-series-reg-fee-amount">$0.00</span>
                    </label>
                </div>

                {{-- Apply Discount Checkbox (shown for series bookings) --}}
                <div id="svc-apply-discount-container" class="hidden mb-4">
                    <label class="flex items-center justify-between gap-3 p-3 border border-success/20 rounded-lg cursor-pointer hover:bg-success/5 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="svc-apply-discount-checkbox" class="checkbox checkbox-success checkbox-sm" onchange="toggleSvcSeriesDiscount()">
                            <div>
                                <span class="font-medium text-sm">Apply Billing Period Discount</span>
                                <span class="text-xs text-base-content/60 block" id="svc-apply-discount-desc">Apply discounted rate for this period</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-success" id="svc-apply-discount-amount">$0.00</span>
                            <span class="text-xs text-success block" id="svc-apply-discount-savings">Save $0.00</span>
                        </div>
                    </label>
                </div>

                {{-- Service Price (hidden, used by JS) --}}
                <span class="hidden" id="svc-base-price-display">$0.00</span>

                {{-- Payment Options (hidden, loaded via AJAX for billing credit detection) --}}
                <div class="hidden">
                    <div id="svc-payment-options-loading" class="hidden"></div>
                    <div id="svc-payment-options-list"></div>
                </div>

                {{-- Billing Period Discount (collapsible) --}}
                <div id="svc-billing-discount-section" class="hidden mb-4">
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
                            <div id="svc-applied-billing-discount" class="hidden mb-3">
                                <div class="alert bg-success/10 border-success/20">
                                    <span class="icon-[tabler--calendar-check] size-5 text-success"></span>
                                    <div class="flex-1">
                                        <span class="font-semibold text-success" id="svc-applied-billing-label"></span>
                                        <p class="text-sm text-success/80" id="svc-applied-billing-savings"></p>
                                    </div>
                                    <button type="button" onclick="removeSvcBillingDiscount()" class="btn btn-ghost btn-xs btn-circle">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/60 mb-3">Client pays upfront for a billing period. Credit applies to future bookings.</p>
                            <div id="svc-billing-discount-options" class="grid grid-cols-2 sm:grid-cols-5 gap-2"></div>
                        </div>
                    </details>
                </div>

                {{-- Registration Fee (shown when billing period selected) --}}
                <div id="svc-reg-fee-container" class="hidden mb-4">
                    <label class="flex items-center justify-between gap-3 p-3 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-200/30 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="svc-reg-fee-checkbox" class="checkbox checkbox-primary checkbox-sm" checked onchange="toggleSvcRegFee()">
                            <div>
                                <span class="font-medium text-sm">Include Registration Fee</span>
                                <span class="text-xs text-base-content/60 block">One-time fee for billing period signup</span>
                            </div>
                        </div>
                        <span class="font-bold text-primary" id="svc-reg-fee-amount">$0.00</span>
                    </label>
                </div>

                {{-- Payment Method (shown for manual pay) --}}
                <div class="form-control mb-4" id="payment-method-container">
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

                {{-- Amount to Charge --}}
                <div class="form-control mb-4" id="price-input-container">
                    <label class="label" for="price-input">
                        <span class="label-text font-medium">Amount to Charge</span>
                        <span class="label-text-alt text-base-content/50" id="price-input-hint">Set by payment option</span>
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

                {{-- Notes --}}
                <div class="form-control mb-6">
                    <label class="label" for="notes">
                        <span class="label-text font-medium">Notes (optional)</span>
                    </label>
                    <textarea id="notes" name="notes" rows="2" class="textarea textarea-bordered" placeholder="Any notes about this booking..."></textarea>
                </div>

                {{-- Check in now --}}
                <div id="check-in-now-container" class="form-control mb-6 hidden">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary" id="check-in-now-checkbox">
                        <div>
                            <span class="font-medium">Check in client now</span>
                            <span class="text-sm text-base-content/60 block">Mark as arrived immediately after booking</span>
                        </div>
                    </label>
                </div>

                <input type="hidden" name="payment_method" id="svc-payment-method-hidden" value="manual">
                <input type="hidden" name="billing_period" id="svc-billing-period" value="">
                <input type="hidden" name="billing_discount_percent" id="svc-billing-discount-percent" value="0">
                <input type="hidden" name="billing_credit_id" id="svc-billing-credit-id" value="">
                <input type="hidden" name="include_registration_fee" id="svc-include-reg-fee" value="1">
                <input type="hidden" name="booking_type" id="svc-booking-type-hidden" value="single">
                <input type="hidden" name="series_slot_ids" id="svc-series-slot-ids-hidden" value="">

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

{{-- Quick Create Slot Modal --}}
<div id="quick-create-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 hidden [&.open]:opacity-100" role="dialog" tabindex="-1">
    <div class="absolute inset-0 bg-base-content/50" onclick="closeQuickCreateModal()"></div>
    <div class="modal-dialog relative z-10 w-full max-w-5xl max-h-[90vh] transform scale-95 transition-transform duration-300 [.open_&]:scale-100">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <span class="icon-[tabler--calendar-plus] size-5 inline-block align-middle mr-2"></span>
                    Create Service Slot
                </h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeQuickCreateModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body overflow-y-auto" style="max-height: calc(90vh - 140px);">
                {{-- Modal Error Container --}}
                <div id="modal-error" class="alert alert-error mb-4 hidden">
                    <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
                    <span id="modal-error-message"></span>
                    <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideModalError()">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left Column: Selection --}}
                    <div class="space-y-5">
                        {{-- Service & Date Display --}}
                        <div class="bg-base-200 rounded-lg p-4">
                            <div class="flex items-center gap-4">
                                <div class="size-14 rounded-lg bg-primary/20 flex items-center justify-center">
                                    <span class="icon-[tabler--massage] size-7 text-primary"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-lg" id="modal-service-name">Service Name</div>
                                    <div class="text-sm text-base-content/60" id="modal-date">Date</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-primary" id="modal-duration">60</div>
                                    <div class="text-xs text-base-content/60">minutes</div>
                                </div>
                            </div>
                        </div>

                        {{-- Instructor Selection --}}
                        <div class="form-control">
                            <label class="label" for="quick-instructor">
                                <span class="label-text font-medium text-base">Instructor <span class="text-error">*</span></span>
                            </label>
                            <select id="quick-instructor" class="select select-bordered w-full select-lg" required>
                                <option value="">Select an instructor...</option>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Available Time Slots --}}
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-base">Available Time Slots</span>
                            </label>
                            <div id="modal-slots-placeholder" class="text-center py-10 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--clock] size-12 text-base-content/30 mx-auto mb-3"></span>
                                <p class="text-base text-base-content/50">Select an instructor to see available slots</p>
                            </div>
                            <div id="modal-slots-loading" class="hidden text-center py-10">
                                <span class="loading loading-spinner loading-lg text-primary"></span>
                                <p class="text-base text-base-content/50 mt-3">Loading available slots...</p>
                            </div>
                            <div id="modal-slots-grid" class="hidden grid grid-cols-4 gap-2 max-h-72 overflow-y-auto p-1"></div>
                            <div id="modal-slots-empty" class="hidden text-center py-10 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--calendar-off] size-12 text-base-content/30 mx-auto mb-3"></span>
                                <p class="text-base text-base-content/50">No available slots for this instructor</p>
                            </div>
                            <input type="hidden" id="quick-start-time" value="">
                        </div>
                    </div>

                    {{-- Right Column: Instructor Availability --}}
                    <div class="space-y-5">
                        {{-- Availability Placeholder --}}
                        <div id="modal-avail-placeholder" class="text-center py-12 border-2 border-dashed border-base-300 rounded-lg">
                            <span class="icon-[tabler--user] size-12 text-base-content/30 mx-auto mb-3"></span>
                            <p class="text-base text-base-content/50">Select an instructor to see their availability</p>
                        </div>

                        {{-- Availability Loading --}}
                        <div id="modal-avail-loading" class="hidden text-center py-12">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="text-base text-base-content/50 mt-3">Loading availability...</p>
                        </div>

                        {{-- Availability Panel --}}
                        <div id="modal-avail-panel" class="hidden space-y-4">
                            {{-- Instructor Info --}}
                            <div class="flex items-center gap-4 bg-base-200 rounded-lg p-4">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content size-14 rounded-full">
                                        <span id="modal-avail-initials" class="text-lg font-bold">JS</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-lg" id="modal-avail-name">Instructor Name</div>
                                    <div class="text-sm text-base-content/60" id="modal-avail-date">Date</div>
                                </div>
                            </div>

                            {{-- Working Days --}}
                            <div>
                                <div class="text-sm font-medium text-base-content/60 mb-2">Working Days</div>
                                <div class="flex gap-2" id="modal-avail-working-days">
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">S</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">M</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">T</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">W</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">T</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">F</span>
                                    <span class="size-9 rounded text-sm font-medium flex items-center justify-center bg-base-200">S</span>
                                </div>
                            </div>

                            {{-- Hours Status --}}
                            <div id="modal-avail-hours-status"></div>

                            {{-- Existing Appointments --}}
                            <div>
                                <div class="text-sm font-medium text-base-content/60 mb-2">Existing Appointments</div>
                                <div id="modal-avail-existing-sessions" class="space-y-2 max-h-40 overflow-y-auto">
                                    <p class="text-sm text-base-content/50">No appointments scheduled</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft btn-secondary" onclick="closeQuickCreateModal()">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg" id="quick-create-btn" onclick="submitQuickCreate()" disabled>
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
    errorMsg.textContent = message;
    errorDiv.classList.remove('hidden');
}

function hideModalError() {
    document.getElementById('modal-error').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentStep = 1;
    let selectedClientId = null;
    let selectedClientName = '';
    let selectedClientEmail = '';
    let selectedSlotId = null;
    let selectedSlotData = null;
    let isNewClient = false;

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
        addBtn.disabled = !(firstName.length > 0 && lastName.length > 0);
    }

    window.confirmNewClient = function() {
        const firstName = document.getElementById('new-first-name').value.trim();
        const lastName = document.getElementById('new-last-name').value.trim();
        const email = document.getElementById('new-email').value.trim();
        const phone = document.getElementById('new-phone').value.trim();

        if (firstName.length === 0 || lastName.length === 0) return;

        selectedClientName = firstName + ' ' + lastName;
        selectedClientEmail = email || '';
        const initials = (firstName[0] + lastName[0]).toUpperCase();
        const contact = email || phone || 'New client';

        document.getElementById('selected-initials').textContent = initials;
        document.getElementById('selected-avatar-img').classList.add('hidden');
        document.getElementById('selected-avatar-initials').classList.remove('hidden');
        document.getElementById('selected-name').textContent = selectedClientName;
        document.getElementById('selected-contact').textContent = contact;
        document.getElementById('selected-new-badge').classList.remove('hidden');
        document.getElementById('selected-client-alert').className = 'alert alert-info';
        document.getElementById('selected-avatar-circle').className = 'bg-info-content text-info size-12 rounded-full';

        document.getElementById('client-type-selection').classList.add('hidden');
        document.getElementById('new-client-section').classList.add('hidden');
        document.getElementById('selected-client').classList.remove('hidden');

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
                .then(data => displaySearchResults(data.clients))
                .finally(() => document.getElementById('search-loading').classList.add('hidden'));
        }, 300);
    });

    function displaySearchResults(clients) {
        const container = document.getElementById('search-results-list');
        const resultsDiv = document.getElementById('search-results');

        if (clients.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-base-content/50"><p class="text-sm">No clients found</p></div>';
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
        document.getElementById('selected-new-badge').classList.add('hidden');
        document.getElementById('selected-client-alert').className = 'alert alert-success';
        document.getElementById('selected-avatar-circle').className = 'bg-success-content text-success size-12 rounded-full';

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
        document.getElementById('client-type-selection').classList.remove('hidden');
        document.getElementById('existing-client-section').classList.remove('hidden');
        document.getElementById('new-client-section').classList.add('hidden');
        document.querySelector('input[name="client_type"][value="existing"]').checked = true;
        searchInput.closest('.form-control').classList.remove('hidden');
        searchInput.value = '';
        document.getElementById('search-results').classList.add('hidden');
        document.getElementById('new-first-name').value = '';
        document.getElementById('new-last-name').value = '';
        document.getElementById('new-email').value = '';
        document.getElementById('new-phone').value = '';
        validateStep1();
    };

    function validateStep1() {
        let clientValid = false;
        if (selectedClientId === 'new') {
            const firstName = document.getElementById('new-first-name').value.trim();
            const lastName = document.getElementById('new-last-name').value.trim();
            clientValid = firstName.length > 0 && lastName.length > 0;
        } else {
            clientValid = selectedClientId !== null && selectedClientId !== '';
        }

        const servicePlanValid = selectedServicePlanId !== null && selectedServicePlanId !== '';
        const slotValid = selectedSlotId !== null && selectedSlotId !== '';

        // For series bookings, slot is auto-selected when slots are loaded
        const allValid = clientValid && servicePlanValid && slotValid;
        document.getElementById('step1-next').disabled = !allValid;
    }

    // ========== Booking Type & Schedule Selection ==========
    var selectedServiceBookingType = 'single';
    var selectedSvcPeriodMonths = null;
    var _allSvcPeriodSlotsData = [];
    var _svcPeriodSlotsData = [];
    var _svcScheduleOptions = [];
    var _selectedSvcScheduleParentIds = [];
    var _svcPeriodMeta = {};
    var _svcSeriesDiscountedTotal = 0;
    var _svcSeriesTotalNoDiscount = 0;
    var _svcSeriesBillingRegFee = 0;

    window.selectServiceBookingType = function(type) {
        selectedServiceBookingType = type;
        var dateSelection = document.getElementById('date-selection');
        var slotSelection = document.getElementById('slot-selection');
        var periodSelection = document.getElementById('svc-period-selection');
        var schedulePicker = document.getElementById('svc-schedule-picker');
        var seriesSlotSummary = document.getElementById('svc-series-slot-summary');

        // Reset slot
        selectedSlotId = null;
        selectedSlotData = null;
        document.getElementById('slot-id').value = '';
        document.getElementById('slots-list').innerHTML = '';
        slotSelection.classList.add('hidden');
        schedulePicker.classList.add('hidden');
        seriesSlotSummary.classList.add('hidden');
        selectedSvcPeriodMonths = null;
        _selectedSvcScheduleParentIds = [];

        if (type === 'single' || type === 'trial') {
            periodSelection.classList.add('hidden');
            dateSelection.classList.remove('hidden');
            validateStep1();
        } else if (type === 'period') {
            dateSelection.classList.add('hidden');
            slotSelection.classList.add('hidden');
            buildSvcPeriodOptions();
            periodSelection.classList.remove('hidden');
            validateStep1();
        }
    };

    function buildSvcPeriodOptions() {
        fetch('/walk-in/service-plan-defaults?service_plan_id=' + selectedServicePlanId)
            .then(function(r) { return r.json(); })
            .then(function(defaults) {
                renderSvcPeriodOptions(defaults.billing_discounts || {});
            })
            .catch(function() { renderSvcPeriodOptions({}); });
    }

    function renderSvcPeriodOptions(discounts) {
        var container = document.getElementById('svc-period-options');
        var periods = { '1': '1 Month', '3': '3 Months', '6': '6 Months', '9': '9 Months', '12': '12 Months' };
        var html = '';

        Object.keys(periods).forEach(function(months) {
            var totalAmount = parseFloat(discounts[months]) || 0;
            if (totalAmount <= 0) return;

            var m = parseInt(months);
            var monthlyRate = m > 0 ? (totalAmount / m) : 0;

            html += '<button type="button" class="svc-period-option-btn flex-1 flex flex-col items-center p-3 rounded-lg border-2 border-base-content/10 hover:border-success cursor-pointer transition-all" ' +
                'data-months="' + months + '" onclick="selectSvcPeriod(' + months + ')">' +
                '<div class="text-xs text-base-content/60 font-medium">' + periods[months] + '</div>' +
                '<div class="text-lg font-bold text-success">$' + totalAmount.toFixed(2) + '</div>' +
                '<div class="text-[10px] text-base-content/50">$' + monthlyRate.toFixed(2) + '/mo</div>' +
                '</button>';
        });

        if (!html) {
            html = '<div class="col-span-full text-center py-4 text-base-content/50 text-sm">No billing period discounts configured for this service.</div>';
        }

        container.innerHTML = html;
    }

    window.selectSvcPeriod = function(months) {
        selectedSvcPeriodMonths = months;

        // Highlight selected period
        document.querySelectorAll('.svc-period-option-btn').forEach(function(btn) {
            if (parseInt(btn.dataset.months) === months) {
                btn.classList.remove('border-base-content/10');
                btn.classList.add('border-success', 'bg-success/5');
            } else {
                btn.classList.remove('border-success', 'bg-success/5');
                btn.classList.add('border-base-content/10');
            }
        });

        // Reset
        var seriesSlotSummary = document.getElementById('svc-series-slot-summary');
        var slotsLoading = document.getElementById('svc-series-slots-loading');
        var slotsEmpty = document.getElementById('svc-series-slots-empty');
        var slotsList = document.getElementById('svc-series-slots-list');
        var schedulePicker = document.getElementById('svc-schedule-picker');

        schedulePicker.classList.add('hidden');
        seriesSlotSummary.classList.remove('hidden');
        slotsLoading.classList.remove('hidden');
        slotsEmpty.classList.add('hidden');
        slotsList.innerHTML = '';
        _selectedSvcScheduleParentIds = [];

        // Fetch both schedules and slots in parallel
        Promise.all([
            fetch('/walk-in/service-schedules?service_plan_id=' + selectedServicePlanId + '&months=' + months).then(function(r) { return r.json(); }),
            fetch('/walk-in/service-slots-range?service_plan_id=' + selectedServicePlanId + '&months=' + months).then(function(r) { return r.json(); })
        ]).then(function(results) {
            var scheduleData = results[0];
            var slotData = results[1];
            slotsLoading.classList.add('hidden');

            _svcScheduleOptions = scheduleData.schedules || [];
            _allSvcPeriodSlotsData = slotData.slots || [];
            _svcPeriodMeta = { total: slotData.total, period_start: slotData.period_start, period_end: slotData.period_end };

            if (_allSvcPeriodSlotsData.length === 0) {
                slotsEmpty.classList.remove('hidden');
                _svcPeriodSlotsData = [];
                selectedSlotId = null;
                selectedSlotData = null;
                validateStep1();
                return;
            }

            // If only 1 schedule or no schedules — auto-select all, skip picker
            if (_svcScheduleOptions.length <= 1) {
                _selectedSvcScheduleParentIds = _svcScheduleOptions.length === 1 ? [_svcScheduleOptions[0].parent_id] : [];
                _svcPeriodSlotsData = _allSvcPeriodSlotsData;
                autoSelectFirstSvcSlot();
                showSvcSlotSummary();
                validateStep1();
                return;
            }

            // Multiple schedules — show picker
            var pickerHtml = '';
            _svcScheduleOptions.forEach(function(sched) {
                var titleLine = sched.title ? '<div class="font-semibold">' + sched.title + '</div>' : '';
                pickerHtml += '<label class="svc-schedule-card flex items-center gap-3 p-4 rounded-lg border-2 border-base-content/10 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">' +
                    '<input type="checkbox" class="checkbox checkbox-primary" data-parent-id="' + sched.parent_id + '" onchange="toggleSvcSchedule(this)">' +
                    '<div class="flex-1">' +
                    titleLine +
                    '<div class="' + (sched.title ? 'text-sm text-base-content/70' : 'font-semibold') + '">' + sched.label + ' · ' + sched.time + '</div>' +
                    '<div class="text-xs text-base-content/50">' + sched.instructor + (sched.location ? ' · ' + sched.location : '') +
                    (sched.last_slot_date ? ' · Last slot: ' + sched.last_slot_date : '') + '</div>' +
                    '</div>' +
                    '<span class="badge badge-soft badge-primary shrink-0">' + sched.slot_count + ' slots</span>' +
                    '</label>';
            });

            document.getElementById('svc-schedule-picker-list').innerHTML = pickerHtml;
            schedulePicker.classList.remove('hidden');
            seriesSlotSummary.classList.add('hidden');
            validateStep1();
        }).catch(function() { slotsLoading.classList.add('hidden'); });
    };

    function autoSelectFirstSvcSlot() {
        if (_svcPeriodSlotsData.length === 0) return;
        var first = _svcPeriodSlotsData[0];
        selectedSlotId = first.id;
        document.getElementById('slot-id').value = first.id;

        var billingDiscounts = first.billing_discounts || {};
        selectedSlotData = {
            service: selectedServicePlanName,
            time: first.time,
            price: parseFloat(first.price) || 0,
            startTime: first.start_time_iso,
            endTime: first.end_time_iso,
            billingDiscounts: billingDiscounts,
            servicePlanId: selectedServicePlanId,
            registrationFee: parseFloat(first.registration_fee) || 0,
            cancellationFee: parseFloat(first.cancellation_fee) || 0,
            graceHours: parseInt(first.cancellation_grace_hours) || 48,
            instructor: first.instructor
        };
    }

    function showSvcSlotSummary() {
        var slotsList = document.getElementById('svc-series-slots-list');
        var html = '<div class="flex items-center justify-between p-4 bg-success/5 border border-success/20 rounded-lg">' +
            '<div class="flex items-center gap-3">' +
            '<div class="flex items-center justify-center size-10 rounded-full bg-success/10">' +
            '<span class="icon-[tabler--calendar-check] size-5 text-success"></span></div>' +
            '<div>' +
            '<div class="font-semibold">' + _svcPeriodSlotsData.length + ' Available Slots</div>' +
            '<div class="text-sm text-base-content/60">' + (_svcPeriodMeta.period_start || '') + ' to ' + (_svcPeriodMeta.period_end || '') + '</div>' +
            '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-ghost btn-sm gap-1" onclick="openSvcPeriodSlotsModal()">' +
            '<span class="icon-[tabler--eye] size-4"></span> View All' +
            '</button>' +
            '</div>';
        slotsList.innerHTML = html;
        document.getElementById('svc-series-slot-summary').classList.remove('hidden');
    }

    window.toggleSvcSchedule = function(checkbox) {
        var parentId = checkbox.dataset.parentId;
        var pid = isNaN(parentId) ? parentId : parseInt(parentId);

        if (checkbox.checked) {
            if (_selectedSvcScheduleParentIds.indexOf(pid) === -1) _selectedSvcScheduleParentIds.push(pid);
        } else {
            _selectedSvcScheduleParentIds = _selectedSvcScheduleParentIds.filter(function(id) { return id !== pid; });
        }

        applySvcScheduleFilter();
    };

    function applySvcScheduleFilter() {
        if (_selectedSvcScheduleParentIds.length === 0) {
            _svcPeriodSlotsData = [];
            selectedSlotId = null;
            selectedSlotData = null;
            document.getElementById('svc-series-slot-summary').classList.add('hidden');
            validateStep1();
            return;
        }

        _svcPeriodSlotsData = _allSvcPeriodSlotsData.filter(function(slot) {
            var pid = slot.recurrence_parent_id;
            // Match child slots by their recurrence_parent_id
            if (pid !== null && pid !== undefined) {
                return _selectedSvcScheduleParentIds.indexOf(pid) !== -1;
            }
            // Match parent slots (no recurrence_parent_id) by their own ID, or one-off slots
            return _selectedSvcScheduleParentIds.indexOf(slot.id) !== -1 ||
                   _selectedSvcScheduleParentIds.indexOf('oneoff') !== -1;
        });

        autoSelectFirstSvcSlot();
        showSvcSlotSummary();
        validateStep1();
    }

    window.openSvcPeriodSlotsModal = function() {
        var modal = document.getElementById('svc-period-slots-modal');
        var container = document.getElementById('svc-period-modal-slots');
        var title = document.getElementById('svc-period-modal-title');

        title.textContent = _svcPeriodSlotsData.length + ' Slots — ' + selectedSvcPeriodMonths + ' Month' + (selectedSvcPeriodMonths > 1 ? 's' : '');

        var html = '';

        if (_selectedSvcScheduleParentIds.length > 1) {
            _svcScheduleOptions.forEach(function(sched) {
                var pid = sched.parent_id;
                if (_selectedSvcScheduleParentIds.indexOf(pid) === -1 && _selectedSvcScheduleParentIds.indexOf(parseInt(pid)) === -1) return;

                var groupSlots = _svcPeriodSlotsData.filter(function(s) {
                    var spid = s.recurrence_parent_id;
                    return spid == pid || (pid === 'oneoff' && !spid);
                });
                if (groupSlots.length === 0) return;

                html += '<div class="font-semibold text-sm text-primary flex items-center gap-2 mt-3 mb-2">' +
                    '<span class="icon-[tabler--calendar-repeat] size-4"></span>' +
                    sched.label + ' · ' + sched.time +
                    '<span class="badge badge-sm badge-soft badge-primary">' + groupSlots.length + '</span></div>';

                groupSlots.forEach(function(slot, idx) {
                    html += renderSvcSlotRow(slot, idx);
                });
            });
        } else {
            _svcPeriodSlotsData.forEach(function(slot, idx) {
                html += renderSvcSlotRow(slot, idx);
            });
        }

        container.innerHTML = html;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function renderSvcSlotRow(slot, idx) {
        return '<div class="flex items-center justify-between p-3 ' + (idx % 2 === 0 ? 'bg-base-200/30' : '') + ' rounded-lg">' +
            '<div class="flex items-center gap-3">' +
            '<div class="text-center shrink-0 w-14">' +
            '<div class="text-xs text-base-content/50">' + (slot.date ? slot.date.split(',')[0] : '') + '</div>' +
            '<div class="font-bold text-sm">' + (slot.date ? (slot.date.split(', ')[1] || slot.date) : '') + '</div>' +
            '</div>' +
            '<div>' +
            '<div class="font-medium text-sm">' + slot.time + '</div>' +
            '<div class="text-xs text-base-content/60">' + slot.instructor + (slot.location ? ' · ' + slot.location : '') + '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }

    window.closeSvcPeriodSlotsModal = function() {
        document.getElementById('svc-period-slots-modal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    // ========== Series Discount & Reg Fee Toggles ==========
    window.toggleSvcSeriesDiscount = function() {
        var checked = document.getElementById('svc-apply-discount-checkbox').checked;
        if (checked && _svcSeriesDiscountedTotal > 0) {
            document.getElementById('svc-billing-period').value = selectedSvcPeriodMonths;
            document.getElementById('svc-billing-discount-percent').value = _svcSeriesDiscountedTotal;
        } else {
            document.getElementById('svc-billing-period').value = '';
            document.getElementById('svc-billing-discount-percent').value = '0';
        }
        recalcSvcSeriesTotal();
    };

    window.toggleSvcSeriesRegFee = function() {
        document.getElementById('svc-include-reg-fee').value = document.getElementById('svc-series-reg-fee-checkbox').checked ? '1' : '0';
        recalcSvcSeriesTotal();
    };

    function recalcSvcSeriesTotal() {
        if (selectedServiceBookingType !== 'period') return;

        var useDiscount = document.getElementById('svc-apply-discount-checkbox').checked;
        var includeRegFee = document.getElementById('svc-series-reg-fee-checkbox').checked;
        var baseTotal = useDiscount ? _svcSeriesDiscountedTotal : _svcSeriesTotalNoDiscount;
        var regFee = includeRegFee ? _svcSeriesBillingRegFee : 0;
        var finalTotal = baseTotal + regFee;

        document.getElementById('display-price').textContent = '$' + finalTotal.toFixed(2);
        document.getElementById('price-input').value = finalTotal.toFixed(2);

        // Show/hide original price strikethrough and discount badge
        if (useDiscount && _svcSeriesTotalNoDiscount > _svcSeriesDiscountedTotal) {
            document.getElementById('svc-original-price-display').textContent = '$' + _svcSeriesTotalNoDiscount.toFixed(2);
            document.getElementById('svc-original-price-display').classList.remove('hidden');
            document.getElementById('svc-discount-badge').classList.remove('hidden');
            document.getElementById('svc-discount-badge-text').textContent = selectedSvcPeriodMonths + 'mo discount applied';
        } else {
            document.getElementById('svc-original-price-display').classList.add('hidden');
            document.getElementById('svc-discount-badge').classList.add('hidden');
        }
    }

    // Service Plan selection
    let selectedServicePlanId = null;
    let selectedServicePlanName = '';
    let selectedServiceDuration = 60;
    let datePicker = null;

    const servicePlanSelect = document.getElementById('service-plan-select');

    function handleServicePlanChange() {
        const selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];

        if (!servicePlanSelect.value) {
            selectedServicePlanId = null;
            selectedServicePlanName = '';
            document.getElementById('booking-type-selection').classList.add('hidden');
            document.getElementById('date-selection').classList.add('hidden');
            document.getElementById('slot-selection').classList.add('hidden');
            document.getElementById('svc-period-selection').classList.add('hidden');
            document.getElementById('svc-schedule-picker').classList.add('hidden');
            document.getElementById('svc-series-slot-summary').classList.add('hidden');
            validateStep1();
            return;
        }

        selectedServicePlanId = servicePlanSelect.value;
        selectedServicePlanName = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];
        selectedServiceDuration = parseInt(selectedOption.dataset.duration) || 60;

        // Show booking type selection and reset to single
        document.getElementById('booking-type-selection').classList.remove('hidden');
        document.querySelector('input[name="booking_type"][value="single"]').checked = true;
        selectedServiceBookingType = 'single';
        selectedSvcPeriodMonths = null;
        _selectedSvcScheduleParentIds = [];
        document.getElementById('svc-period-selection').classList.add('hidden');
        document.getElementById('svc-schedule-picker').classList.add('hidden');
        document.getElementById('svc-series-slot-summary').classList.add('hidden');

        document.getElementById('date-selection').classList.remove('hidden');

        selectedSlotId = null;
        selectedSlotData = null;
        document.getElementById('slot-id').value = '';
        document.getElementById('slot-selection').classList.add('hidden');
        document.getElementById('slots-list').innerHTML = '';
        validateStep1();

        if (!datePicker) {
            datePicker = flatpickr('#booking-date', {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d',
                altInputClass: 'input input-bordered w-full',
                defaultDate: '{{ $selectedDate }}',
                onChange: function(selectedDates, dateStr) {
                    loadSlots(dateStr);
                }
            });
            loadSlots('{{ $selectedDate }}');
        } else {
            const currentDate = datePicker.selectedDates[0];
            if (currentDate) {
                loadSlots(datePicker.formatDate(currentDate, 'Y-m-d'));
            }
        }
    }

    servicePlanSelect.addEventListener('change', handleServicePlanChange);

    function loadSlots(date) {
        if (!selectedServicePlanId) return;

        selectedSlotId = null;
        selectedSlotData = null;
        document.getElementById('slot-id').value = '';
        validateStep1();

        document.getElementById('slot-selection').classList.remove('hidden');
        document.getElementById('slots-loading').classList.remove('hidden');
        document.getElementById('slots-empty').classList.add('hidden');
        document.getElementById('next-available').classList.add('hidden');
        document.getElementById('slots-list').innerHTML = '';

        fetch(`/walk-in/service-slots?date=${date}&service_plan_id=${selectedServicePlanId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('slots-loading').classList.add('hidden');

                if (data.slots.length === 0) {
                    document.getElementById('slots-empty').classList.remove('hidden');

                    if (data.next_available && data.next_available.length > 0) {
                        document.getElementById('next-available').classList.remove('hidden');
                        const datesContainer = document.getElementById('next-available-dates');
                        datesContainer.innerHTML = data.next_available.map(item => `
                            <button type="button" class="btn btn-sm btn-outline btn-primary" onclick="jumpToDate('${item.date}')">
                                ${item.formatted_date}
                                <span class="badge badge-primary badge-xs">${item.slot_count}</span>
                            </button>
                        `).join('');
                    }
                    return;
                }

                const container = document.getElementById('slots-list');
                container.innerHTML = data.slots.map(slot => `
                    <label class="flex items-center gap-4 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                        <input type="radio" name="slot_radio" value="${slot.id}" class="radio radio-primary"
                               data-service="${slot.service}"
                               data-time="${slot.time}"
                               data-instructor="${slot.instructor}"
                               data-price="${slot.price || 0}"
                               data-start="${slot.start_time_iso}"
                               data-end="${slot.end_time_iso}"
                               data-billing-discounts='${JSON.stringify(slot.billing_discounts || {})}'
                               data-service-plan-id="${slot.service_plan_id || ''}"
                               data-registration-fee="${slot.registration_fee || 0}"
                               data-cancellation-fee="${slot.cancellation_fee || 0}"
                               data-grace-hours="${slot.cancellation_grace_hours || 48}"
                               onchange="selectSlot(this)">
                        <div class="flex-1">
                            <div class="font-semibold">${slot.time}</div>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--user] size-4"></span>
                                    ${slot.instructor}
                                </span>
                                ${slot.location ? `
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--map-pin] size-4"></span>
                                    ${slot.location}
                                </span>
                                ` : ''}
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--clock] size-4"></span>
                                    ${slot.duration} min
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-primary">${slot.formatted_price}</div>
                        </div>
                    </label>
                `).join('');

                // Auto-select preloaded slot if provided
                if (window.preloadSlotId) {
                    const preloadRadio = document.querySelector(`input[name="slot_radio"][value="${window.preloadSlotId}"]`);
                    if (preloadRadio) {
                        preloadRadio.checked = true;
                        selectSlot(preloadRadio);
                        window.preloadSlotId = null;
                    }
                }
            });
    }

    window.jumpToDate = function(date) {
        if (datePicker) {
            datePicker.setDate(date, true);
        }
    };

    window.selectSlot = function(radio) {
        selectedSlotId = radio.value;
        var billingDiscounts = {};
        try { billingDiscounts = JSON.parse(radio.dataset.billingDiscounts || '{}'); } catch(e) {}

        selectedSlotData = {
            service: radio.dataset.service,
            time: radio.dataset.time,
            instructor: radio.dataset.instructor,
            price: parseFloat(radio.dataset.price) || 0,
            startTime: radio.dataset.start,
            endTime: radio.dataset.end,
            billingDiscounts: billingDiscounts,
            servicePlanId: radio.dataset.servicePlanId || '',
            registrationFee: parseFloat(radio.dataset.registrationFee) || 0,
            cancellationFee: parseFloat(radio.dataset.cancellationFee) || 0,
            graceHours: parseInt(radio.dataset.graceHours) || 48
        };
        document.getElementById('slot-id').value = selectedSlotId;

        updateCheckInVisibility();
        updateSvcBillingDiscountSection();
        validateStep1();
    };

    function updateCheckInVisibility() {
        const checkInContainer = document.getElementById('check-in-now-container');
        const checkInCheckbox = document.getElementById('check-in-now-checkbox');

        if (!selectedSlotData || !selectedSlotData.startTime) {
            checkInContainer.classList.add('hidden');
            checkInCheckbox.checked = false;
            return;
        }

        const now = new Date();
        const startTime = new Date(selectedSlotData.startTime);
        const endTime = new Date(selectedSlotData.endTime);
        const checkInWindowStart = new Date(startTime.getTime() - 30 * 60 * 1000);

        if (now >= checkInWindowStart && now <= endTime) {
            checkInContainer.classList.remove('hidden');
        } else {
            checkInContainer.classList.add('hidden');
            checkInCheckbox.checked = false;
        }
    }

    // Step navigation
    document.getElementById('step1-next').addEventListener('click', async function() {
        console.log('Step1 Next clicked. State:', {
            selectedClientId,
            selectedClientName,
            selectedSlotId,
            selectedSlotData,
            selectedServicePlanId
        });

        const btn = this;
        const originalText = btn.innerHTML;

        if (selectedClientId === 'new') {
            console.log('Creating new client...');
            const firstName = document.getElementById('new-first-name').value.trim();
            const lastName = document.getElementById('new-last-name').value.trim();
            const email = document.getElementById('new-email').value.trim();
            const phone = document.getElementById('new-phone').value.trim();

            // Validate required fields
            if (!firstName || !lastName) {
                showFormError('Please enter both first and last name for the new client.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Creating client...';

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

                if (!res.ok) {
                    // Handle validation errors from Laravel
                    let errorMsg = 'Error creating client.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    showFormError(errorMsg);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return;
                }

                if (!data.success) {
                    showFormError(data.message || 'Failed to create client. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return;
                }

                selectedClientId = data.client.id;
                selectedClientName = data.client.first_name + ' ' + data.client.last_name;
                document.getElementById('client-id').value = data.client.id;
                console.log('Client created successfully:', data.client);
            } catch (err) {
                console.error('Error creating client:', err);
                showFormError('Error creating client. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
                return;
            }

            btn.disabled = false;
            btn.innerHTML = originalText;
        }

        // Verify client_id is set before proceeding
        const clientIdValue = document.getElementById('client-id').value;
        console.log('Proceeding to step 2 with client_id:', clientIdValue, 'selectedClientId:', selectedClientId);

        if (!clientIdValue || clientIdValue === 'new') {
            showFormError('Client ID was not properly set. Please try again.');
            return;
        }

        document.getElementById('summary-client').textContent = selectedClientName;
        document.getElementById('summary-service').textContent = selectedServicePlanName;
        if (selectedServiceBookingType === 'period' && selectedSvcPeriodMonths) {
            document.getElementById('summary-datetime').textContent = _svcPeriodSlotsData.length + ' slots · ' + selectedSvcPeriodMonths + ' month' + (selectedSvcPeriodMonths > 1 ? 's' : '');
        } else {
            document.getElementById('summary-datetime').textContent = datePicker.altInput.value + ' at ' + selectedSlotData.time;
        }
        document.getElementById('summary-instructor').textContent = selectedSlotData.instructor || 'Various';

        // For series bookings, check if client already has bookings for some slots
        if (selectedServiceBookingType === 'period' && _svcPeriodSlotsData.length > 0 && selectedClientId && selectedClientId !== 'new') {
            try {
                var checkIds = _svcPeriodSlotsData.map(function(s) { return s.id; }).join(',');
                var checkRes = await fetch('/walk-in/check-service-series-conflict?client_id=' + selectedClientId + '&slot_ids=' + checkIds);
                var checkData = await checkRes.json();
                if (checkData.has_conflict) {
                    var newSlots = _svcPeriodSlotsData.length - checkData.existing_count;
                    if (newSlots <= 0) {
                        showFormError(checkData.message);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        return;
                    }
                    if (!confirm(checkData.client_name + ' is already booked into ' + checkData.existing_count + ' slot(s). Only ' + newSlots + ' new slot(s) will be added. Continue?')) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        return;
                    }
                }
            } catch(e) {}
        }

        const price = selectedSlotData.price;
        document.getElementById('display-price').textContent = '$' + price.toFixed(2);
        document.getElementById('price-input').value = price.toFixed(2);
        document.getElementById('svc-base-price-display').textContent = '$' + price.toFixed(2);

        // Reset billing discount and load payment options for this client
        removeSvcBillingDiscount();

        // Reset series-specific UI
        document.getElementById('svc-series-price-breakdown').classList.add('hidden');
        document.getElementById('svc-series-reg-fee-container').classList.add('hidden');
        document.getElementById('svc-apply-discount-container').classList.add('hidden');
        document.getElementById('svc-apply-discount-checkbox').checked = false;

        loadSvcPaymentOptions(clientIdValue);

        // Set booking type and series slot IDs
        document.getElementById('svc-booking-type-hidden').value = selectedServiceBookingType;
        if (selectedServiceBookingType === 'period' && _svcPeriodSlotsData.length > 0) {
            document.getElementById('svc-series-slot-ids-hidden').value = _svcPeriodSlotsData.map(function(s) { return s.id; }).join(',');
        } else {
            document.getElementById('svc-series-slot-ids-hidden').value = '';
        }

        document.getElementById('booking-form').action = `/walk-in/service/${selectedSlotId}`;
        console.log('Form action set to:', document.getElementById('booking-form').action);

        goToStep(2);

        // If "Series Service" was selected — show breakdown, reg fee, discount checkbox
        if (selectedServiceBookingType === 'period' && selectedSvcPeriodMonths && selectedSlotData) {
            document.getElementById('svc-billing-discount-section').classList.add('hidden');

            var slotCount = _svcPeriodSlotsData.length;
            var basePerSlot = selectedSlotData.price;
            var totalNoDiscount = basePerSlot * slotCount;
            var regFee = selectedSlotData.registrationFee || 0;
            var discounts = selectedSlotData.billingDiscounts || {};
            var discountedTotal = parseFloat(discounts[selectedSvcPeriodMonths]) || 0;
            var savings = totalNoDiscount - discountedTotal;

            // Show price breakdown
            document.getElementById('svc-series-base-price').textContent = '$' + basePerSlot.toFixed(2);
            document.getElementById('svc-series-slot-count').textContent = slotCount + ' slots (' + selectedSvcPeriodMonths + ' month' + (selectedSvcPeriodMonths > 1 ? 's' : '') + ')';
            document.getElementById('svc-series-total-no-discount').textContent = '$' + totalNoDiscount.toFixed(2);
            document.getElementById('svc-series-price-breakdown').classList.remove('hidden');

            // Set display price to total without discount
            document.getElementById('display-price').textContent = '$' + totalNoDiscount.toFixed(2);
            document.getElementById('price-input').value = totalNoDiscount.toFixed(2);

            // Show registration fee
            if (regFee > 0) {
                document.getElementById('svc-series-reg-fee-amount').textContent = '$' + regFee.toFixed(2);
                document.getElementById('svc-series-reg-fee-checkbox').checked = true;
                document.getElementById('svc-include-reg-fee').value = '1';
                document.getElementById('svc-series-reg-fee-container').classList.remove('hidden');
                _svcSeriesBillingRegFee = regFee;
            }

            // Show apply discount checkbox (only if discount exists)
            if (discountedTotal > 0) {
                document.getElementById('svc-apply-discount-amount').textContent = '$' + discountedTotal.toFixed(2);
                document.getElementById('svc-apply-discount-savings').textContent = 'Save $' + savings.toFixed(2);
                document.getElementById('svc-apply-discount-desc').textContent = selectedSvcPeriodMonths + ' month' + (selectedSvcPeriodMonths > 1 ? 's' : '') + ' discounted rate';
                document.getElementById('svc-apply-discount-container').classList.remove('hidden');
            }

            // Store for toggles
            _svcSeriesDiscountedTotal = discountedTotal;
            _svcSeriesTotalNoDiscount = totalNoDiscount;

            recalcSvcSeriesTotal();
        }

        // If "Trial Service" was selected, set price to $0 and comp
        if (selectedServiceBookingType === 'trial') {
            document.getElementById('svc-billing-discount-section').classList.add('hidden');
            document.getElementById('display-price').textContent = '$0.00';
            document.getElementById('price-input').value = '0';
            document.getElementById('svc-payment-method-hidden').value = 'comp';
        }
    });

    // Form submit validation
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        const clientId = document.getElementById('client-id').value;
        const slotId = document.getElementById('slot-id').value;

        console.log('Form submitting with:', { client_id: clientId, slot_id: slotId, action: this.action });

        if (!clientId || clientId === 'new') {
            e.preventDefault();
            showFormError('Client ID is not set. Please go back and select a client again.');
            return false;
        }

        if (!slotId) {
            e.preventDefault();
            showFormError('Slot ID is not set. Please go back and select a slot again.');
            return false;
        }
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
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Quick Create Modal
    const quickCreateModal = document.getElementById('quick-create-modal');
    let modalSelectedSlotTime = null;
    let modalInstructorWorksToday = true;

    window.openQuickCreateModal = function() {
        const servicePlanSelect = document.getElementById('service-plan-select');
        const selectedOption = servicePlanSelect.options[servicePlanSelect.selectedIndex];

        document.getElementById('modal-service-name').textContent = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];
        document.getElementById('modal-date').textContent = datePicker ? datePicker.altInput.value : 'Select date';
        document.getElementById('modal-duration').textContent = selectedOption.dataset.duration || '60';

        // Reset state
        document.getElementById('quick-instructor').value = '';
        document.getElementById('quick-start-time').value = '';
        modalSelectedSlotTime = null;
        modalInstructorWorksToday = true;
        updateQuickCreateButton();

        // Reset UI
        document.getElementById('modal-slots-placeholder').classList.remove('hidden');
        document.getElementById('modal-slots-loading').classList.add('hidden');
        document.getElementById('modal-slots-grid').classList.add('hidden');
        document.getElementById('modal-slots-empty').classList.add('hidden');
        document.getElementById('modal-avail-placeholder').classList.remove('hidden');
        document.getElementById('modal-avail-loading').classList.add('hidden');
        document.getElementById('modal-avail-panel').classList.add('hidden');

        quickCreateModal.classList.remove('hidden');
        setTimeout(() => quickCreateModal.classList.add('open'), 10);
        document.body.style.overflow = 'hidden';
    };

    // Instructor selection in modal
    document.getElementById('quick-instructor').addEventListener('change', function() {
        const instructorId = this.value;
        const date = datePicker ? datePicker.formatDate(datePicker.selectedDates[0], 'Y-m-d') : null;

        modalSelectedSlotTime = null;
        document.getElementById('quick-start-time').value = '';
        updateQuickCreateButton();

        if (!instructorId || !date) {
            document.getElementById('modal-slots-placeholder').classList.remove('hidden');
            document.getElementById('modal-slots-grid').classList.add('hidden');
            document.getElementById('modal-slots-empty').classList.add('hidden');
            document.getElementById('modal-avail-placeholder').classList.remove('hidden');
            document.getElementById('modal-avail-panel').classList.add('hidden');
            return;
        }

        // Fetch instructor availability
        loadInstructorAvailability(instructorId, date);

        // Fetch available time slots
        loadAvailableTimeSlots(instructorId, date);
    });

    function loadInstructorAvailability(instructorId, date) {
        document.getElementById('modal-avail-placeholder').classList.add('hidden');
        document.getElementById('modal-avail-loading').classList.remove('hidden');
        document.getElementById('modal-avail-panel').classList.add('hidden');

        fetch(`/walk-in/instructor-availability?instructor_id=${instructorId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-avail-loading').classList.add('hidden');
                document.getElementById('modal-avail-panel').classList.remove('hidden');
                modalInstructorWorksToday = data.works_today;
                displayModalInstructorAvailability(data);
                updateQuickCreateButton();
            })
            .catch(err => {
                console.error('Error fetching availability:', err);
                document.getElementById('modal-avail-loading').classList.add('hidden');
                document.getElementById('modal-avail-placeholder').classList.remove('hidden');
            });
    }

    function displayModalInstructorAvailability(data) {
        document.getElementById('modal-avail-initials').textContent = data.instructor.initials;
        document.getElementById('modal-avail-name').textContent = data.instructor.name;
        document.getElementById('modal-avail-date').textContent = data.formatted_date;

        // Working days
        const dayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        const workingDaysContainer = document.getElementById('modal-avail-working-days');
        workingDaysContainer.innerHTML = data.working_days.map((works, index) => {
            const isToday = index === data.day_of_week;
            const baseClass = 'size-9 rounded text-sm font-medium flex items-center justify-center';
            const colorClass = works
                ? (isToday ? 'bg-primary text-primary-content ring-2 ring-primary ring-offset-1' : 'bg-success/20 text-success')
                : 'bg-base-200 text-base-content/40';
            return `<span class="${baseClass} ${colorClass}">${dayLetters[index]}</span>`;
        }).join('');

        // Hours status
        const hoursStatus = document.getElementById('modal-avail-hours-status');
        if (!data.works_today) {
            hoursStatus.innerHTML = `
                <div class="alert alert-error py-3 px-4">
                    <span class="icon-[tabler--calendar-x] size-5"></span>
                    <span class="text-base">Does not work on ${data.day_name}s</span>
                </div>
            `;
        } else if (data.availability) {
            hoursStatus.innerHTML = `
                <div class="alert alert-success py-3 px-4">
                    <span class="icon-[tabler--clock-check] size-5"></span>
                    <span class="text-base">Available ${data.availability.from} - ${data.availability.to}</span>
                </div>
            `;
        } else {
            hoursStatus.innerHTML = `
                <div class="alert alert-info py-3 px-4">
                    <span class="icon-[tabler--clock] size-5"></span>
                    <span class="text-base">No hour restrictions</span>
                </div>
            `;
        }

        // Existing sessions
        const sessionsContainer = document.getElementById('modal-avail-existing-sessions');
        if (data.existing_sessions.length === 0) {
            sessionsContainer.innerHTML = `<p class="text-base text-base-content/50">No appointments scheduled</p>`;
        } else {
            sessionsContainer.innerHTML = data.existing_sessions.map(session => `
                <div class="flex items-center gap-2 text-sm bg-base-200 rounded px-3 py-2">
                    <span class="icon-[tabler--calendar-event] size-5 text-base-content/50"></span>
                    <span class="font-medium">${session.time}</span>
                    <span class="text-base-content/60 truncate">- ${session.title}</span>
                </div>
            `).join('');
        }
    }

    function loadAvailableTimeSlots(instructorId, date) {
        const duration = selectedServiceDuration;

        document.getElementById('modal-slots-placeholder').classList.add('hidden');
        document.getElementById('modal-slots-loading').classList.remove('hidden');
        document.getElementById('modal-slots-grid').classList.add('hidden');
        document.getElementById('modal-slots-empty').classList.add('hidden');

        fetch(`/walk-in/available-slots?instructor_id=${instructorId}&date=${date}&duration=${duration}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-slots-loading').classList.add('hidden');

                if (data.slots.length === 0) {
                    document.getElementById('modal-slots-empty').classList.remove('hidden');
                    return;
                }

                const slotsGrid = document.getElementById('modal-slots-grid');
                slotsGrid.innerHTML = data.slots.map(slot => `
                    <button type="button"
                            class="btn btn-outline modal-slot-btn"
                            data-time="${slot.time}"
                            onclick="selectModalSlot(this, '${slot.time}')">
                        ${slot.display}
                    </button>
                `).join('');
                slotsGrid.classList.remove('hidden');
            })
            .catch(err => {
                console.error('Error fetching slots:', err);
                document.getElementById('modal-slots-loading').classList.add('hidden');
                document.getElementById('modal-slots-empty').classList.remove('hidden');
            });
    }

    window.selectModalSlot = function(btn, time) {
        // Remove active class from all buttons
        document.querySelectorAll('#modal-slots-grid .modal-slot-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline');
        });

        // Add active class to selected
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-primary');

        modalSelectedSlotTime = time;
        document.getElementById('quick-start-time').value = time;
        updateQuickCreateButton();
    };

    function updateQuickCreateButton() {
        const btn = document.getElementById('quick-create-btn');
        const instructorId = document.getElementById('quick-instructor').value;

        if (!modalInstructorWorksToday || !modalSelectedSlotTime || !instructorId) {
            btn.disabled = true;
        } else {
            btn.disabled = false;
        }
    }

    window.closeQuickCreateModal = function() {
        quickCreateModal.classList.remove('open');
        setTimeout(() => {
            quickCreateModal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    };

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

        const instructorId = document.getElementById('quick-instructor').value;
        const startTime = document.getElementById('quick-start-time').value;
        const date = datePicker ? datePicker.formatDate(datePicker.selectedDates[0], 'Y-m-d') : null;

        if (!instructorId) {
            showModalError('Please select an instructor');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!startTime) {
            showModalError('Please select a time slot');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!modalInstructorWorksToday) {
            showModalError('Cannot create slot - instructor does not work on this day');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        try {
            const res = await fetch('/walk-in/service-slots/quick-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    service_plan_id: selectedServicePlanId,
                    instructor_id: instructorId,
                    date: date,
                    start_time: startTime
                })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to create slot');
            }

            closeQuickCreateModal();

            const container = document.getElementById('slots-list');
            const slot = data.slot;

            document.getElementById('slots-empty').classList.add('hidden');
            document.getElementById('slot-selection').classList.remove('hidden');
            container.innerHTML = `
                <label class="flex items-center gap-4 p-4 border border-primary rounded-lg cursor-pointer bg-primary/5 transition-all">
                    <input type="radio" name="slot_radio" value="${slot.id}" class="radio radio-primary" checked
                           data-service="${slot.service}"
                           data-time="${slot.time}"
                           data-instructor="${slot.instructor}"
                           data-price="${slot.price || 0}"
                           data-start="${slot.start_time_iso}"
                           data-end="${slot.end_time_iso}"
                           onchange="selectSlot(this)">
                    <div class="flex-1">
                        <div class="font-semibold">${slot.time}</div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--user] size-4"></span>
                                ${slot.instructor}
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--clock] size-4"></span>
                                ${slot.duration} min
                            </span>
                            <span class="badge badge-success badge-sm">Just created</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-primary">${slot.formatted_price}</div>
                    </div>
                </label>
            `;

            selectedSlotId = String(slot.id);
            selectedSlotData = {
                service: slot.service,
                time: slot.time,
                instructor: slot.instructor,
                price: parseFloat(slot.price) || 0,
                startTime: slot.start_time_iso,
                endTime: slot.end_time_iso
            };
            document.getElementById('slot-id').value = slot.id;

            updateCheckInVisibility();
            setTimeout(() => validateStep1(), 50);

        } catch (err) {
            console.error(err);
            showModalError(err.message || 'Error creating slot. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    };

    // Preload slot if provided
    @if($preloadSlot)
    (function() {
        const preloadData = {
            id: {{ $preloadSlot->id }},
            servicePlanId: {{ $preloadSlot->service_plan_id }},
            date: '{{ $preloadSlot->start_time->format('Y-m-d') }}'
        };

        // Set the preload slot ID immediately so loadSlots can auto-select it
        window.preloadSlotId = preloadData.id;
        window.preloadSlotDate = preloadData.date;

        setTimeout(() => {
            const servicePlanSelect = document.getElementById('service-plan-select');
            if (servicePlanSelect) {
                servicePlanSelect.value = preloadData.servicePlanId;

                const hsSelect = window.HSSelect?.getInstance(servicePlanSelect);
                if (hsSelect) {
                    hsSelect.setValue(String(preloadData.servicePlanId));
                }

                // Trigger change - this will create datePicker and load slots
                servicePlanSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, 100);
    })();
    @endif

    // ==================== Price Override Functions ====================
    let pendingOverrideId = null;
    let pendingOverrideCode = null;
    let appliedOverridePrice = null;
    let statusPollInterval = null;
    const canOverridePrice = {{ ($canOverridePrice ?? false) ? 'true' : 'false' }};

    // Direct override for managers/owners with permission
    window.applyDirectOverride = function() {
        if (!selectedSlotData) {
            showOverrideError('Please select a slot first.');
            return;
        }

        const priceInput = document.getElementById('direct-override-price');
        const newPrice = parseFloat(priceInput.value);
        const originalPrice = selectedSlotData.price;

        if (!newPrice || newPrice < 0) {
            showOverrideError('Please enter a valid price.');
            return;
        }

        if (newPrice >= originalPrice) {
            showOverrideError('Override price must be less than original price ($' + originalPrice.toFixed(2) + ').');
            return;
        }

        appliedOverridePrice = newPrice;

        // Set hidden fields for form submission
        document.getElementById('price_override_code').value = 'DIRECT';
        document.getElementById('price_override_amount').value = newPrice;

        // Update UI
        document.getElementById('applied-override-code').textContent = 'Direct Override';
        document.getElementById('applied-override-price').textContent = 'Override: $' + newPrice.toFixed(2);
        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');

        // Update price display
        document.getElementById('price-input').value = newPrice.toFixed(2);
        document.getElementById('display-price').textContent = '$' + newPrice.toFixed(2);

        // Clear the input
        priceInput.value = '';
        hideOverrideError();
    };

    function showOverrideError(msg) {
        const el = document.getElementById('override-error');
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function hideOverrideError() {
        const el = document.getElementById('override-error');
        if (el) { el.classList.add('hidden'); }
    }

    window.showOverrideModal = function() {
        const modal = document.getElementById('override-modal');
        if (!modal || !selectedSlotData) return;
        document.getElementById('modal-original-price').textContent = '$' + selectedSlotData.price.toFixed(2);
        document.getElementById('override-new-price').value = '';
        document.getElementById('override-reason').value = '';
        document.getElementById('override-preview').classList.add('hidden');
        document.getElementById('modal-error')?.classList.add('hidden');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeOverrideModal = function() {
        const modal = document.getElementById('override-modal');
        if (modal) { modal.classList.add('hidden'); document.body.style.overflow = ''; }
    };

    document.getElementById('override-new-price')?.addEventListener('input', function() {
        const newPrice = parseFloat(this.value) || 0;
        const preview = document.getElementById('override-preview');
        if (selectedSlotData && newPrice > 0 && newPrice < selectedSlotData.price) {
            document.getElementById('preview-discount').textContent = '$' + (selectedSlotData.price - newPrice).toFixed(2);
            preview.classList.remove('hidden');
        } else { preview.classList.add('hidden'); }
    });

    window.submitOverrideRequest = function() {
        if (!selectedSlotData) return;
        const newPrice = parseFloat(document.getElementById('override-new-price').value);
        const reason = document.getElementById('override-reason').value.trim();
        const submitBtn = document.getElementById('submit-override-btn');

        if (!newPrice || newPrice <= 0 || newPrice >= selectedSlotData.price) {
            document.getElementById('modal-error').textContent = 'Please enter a valid price less than original.';
            document.getElementById('modal-error').classList.remove('hidden');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';

        fetch('/price-override/request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({
                original_price: selectedSlotData.price,
                requested_price: newPrice,
                client_id: selectedClientId,
                reason: reason || null,
                bookable_type: 'App\\Models\\ServiceSlot',
                bookable_id: selectedSlotData.id,
                metadata: { slot_time: selectedSlotData.time }
            })
        })
        .then(r => r.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send';
            if (data.success) { closeOverrideModal(); showPendingOverride(data.data); }
            else { document.getElementById('modal-error').textContent = data.message || 'Failed'; document.getElementById('modal-error').classList.remove('hidden'); }
        })
        .catch(() => { submitBtn.disabled = false; submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send'; });
    };

    function showPendingOverride(data) {
        pendingOverrideId = data.id;
        pendingOverrideCode = data.confirmation_code;
        document.getElementById('pending-code').textContent = data.confirmation_code;
        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('override-pending').classList.remove('hidden');
        statusPollInterval = setInterval(() => checkOverrideStatus(), 10000);
    }

    window.checkOverrideStatus = function() {
        if (!pendingOverrideCode) return;
        fetch('/price-override/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ code: pendingOverrideCode })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data.is_approved) { clearInterval(statusPollInterval); applyOverrideFromData(data.data); }
            else if (!data.success && (data.status === 'expired' || data.status === 'rejected')) {
                clearInterval(statusPollInterval);
                document.getElementById('override-input-section').classList.remove('hidden');
                document.getElementById('override-pending').classList.add('hidden');
            }
        });
    };

    window.fetchApprovedOverride = function() {
        if (!selectedSlotData) { showOverrideError('Please select a slot first.'); return; }
        const btn = document.getElementById('fetch-override-btn');
        const msg = document.getElementById('fetch-override-message');
        btn.disabled = true; btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
        msg.classList.add('hidden');

        fetch('/price-override/fetch-approved', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ bookable_type: 'App\\Models\\ServiceSlot', bookable_id: selectedSlotData.id })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = '<span class="icon-[tabler--download] size-4"></span>';
            if (data.success && data.data) { applyOverrideFromData(data.data); msg.textContent = 'Applied!'; msg.className = 'text-xs mt-1 text-success'; }
            else { msg.textContent = 'No approved override found.'; msg.className = 'text-xs mt-1 text-base-content/60'; }
            msg.classList.remove('hidden');
        });
    };

    // Personal override state
    let personalOverrideCode = null;
    let personalOverrideSupervisor = null;

    window.verifyOverrideCode = function() {
        const code = document.getElementById('override_code_input').value.trim().toUpperCase();
        if (!code) { showOverrideError('Enter a code.'); return; }
        const btn = document.getElementById('verify-override-btn');
        btn.disabled = true; btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

        fetch('/price-override/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ code: code })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = 'Verify';
            if (data.success) {
                if (data.is_personal_code) {
                    personalOverrideCode = data.code;
                    personalOverrideSupervisor = data.data?.authorized_by?.name || 'Manager';
                    showPersonalOverrideModal(data.code, personalOverrideSupervisor);
                } else if (data.data.is_approved) {
                    applyOverrideFromData(data.data);
                } else if (data.data.is_pending) {
                    showPendingOverride(data.data);
                }
            } else {
                showOverrideError(data.message || 'Invalid code.');
            }
        });
    };

    function showPersonalOverrideModal(code, supervisorName) {
        document.getElementById('personal-supervisor-name').textContent = supervisorName;
        document.getElementById('personal-supervisor-code').textContent = code;
        const originalPrice = selectedSlotData?.price || 0;
        document.getElementById('personal-modal-original-price').textContent = '$' + originalPrice.toFixed(2);
        document.getElementById('personal-override-new-price').value = '';
        document.getElementById('personal-override-preview').classList.add('hidden');
        document.getElementById('personal-modal-error').classList.add('hidden');
        document.getElementById('personal-override-modal').classList.remove('hidden');
        setTimeout(() => document.getElementById('personal-override-new-price').focus(), 100);
    }

    window.closePersonalOverrideModal = function() {
        document.getElementById('personal-override-modal').classList.add('hidden');
    };

    window.updatePersonalOverridePreview = function() {
        const newPrice = parseFloat(document.getElementById('personal-override-new-price').value) || 0;
        const originalPrice = selectedSlotData?.price || 0;
        const preview = document.getElementById('personal-override-preview');
        if (newPrice > 0 && newPrice < originalPrice) {
            const discount = originalPrice - newPrice;
            document.getElementById('personal-preview-discount').textContent = '$' + discount.toFixed(2);
            document.getElementById('personal-preview-percent').textContent = ((discount / originalPrice) * 100).toFixed(1) + '%';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    };

    window.applyPersonalOverride = function() {
        const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
        const originalPrice = selectedSlotData?.price || 0;
        const errorEl = document.getElementById('personal-modal-error');
        if (!newPrice || newPrice < 0) { errorEl.textContent = 'Enter a valid price.'; errorEl.classList.remove('hidden'); return; }
        if (newPrice >= originalPrice) { errorEl.textContent = 'Price must be less than $' + originalPrice.toFixed(2); errorEl.classList.remove('hidden'); return; }

        appliedOverridePrice = newPrice;
        document.getElementById('price_override_code').value = personalOverrideCode;
        document.getElementById('price_override_amount').value = newPrice;
        document.getElementById('applied-override-code').textContent = 'Supervised by: ' + personalOverrideSupervisor + ' (' + personalOverrideCode + ')';
        document.getElementById('applied-override-price').textContent = 'Override: $' + newPrice.toFixed(2);
        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('price-input').value = newPrice.toFixed(2);
        document.getElementById('display-price').textContent = '$' + newPrice.toFixed(2);
        closePersonalOverrideModal();
        document.getElementById('override_code_input').value = '';
    };

    function applyOverrideFromData(data) {
        appliedOverridePrice = parseFloat(data.requested_price);
        document.getElementById('price_override_code').value = data.confirmation_code;
        document.getElementById('price_override_amount').value = data.requested_price;
        document.getElementById('applied-override-code').textContent = 'Code: ' + data.confirmation_code;
        document.getElementById('applied-override-price').textContent = 'Override: $' + appliedOverridePrice.toFixed(2);
        document.getElementById('applied-override').classList.remove('hidden');
        document.getElementById('override-input-section').classList.add('hidden');
        document.getElementById('override-pending').classList.add('hidden');
        document.getElementById('price-input').value = appliedOverridePrice.toFixed(2);
        document.getElementById('display-price').textContent = '$' + appliedOverridePrice.toFixed(2);
    }

    window.removeOverride = function() {
        appliedOverridePrice = null;
        document.getElementById('price_override_code').value = '';
        document.getElementById('price_override_amount').value = '';
        document.getElementById('applied-override').classList.add('hidden');
        document.getElementById('override-input-section').classList.remove('hidden');
        const codeInput = document.getElementById('override_code_input');
        if (codeInput) codeInput.value = '';
        const directInput = document.getElementById('direct-override-price');
        if (directInput) directInput.value = '';
        if (selectedSlotData) {
            document.getElementById('price-input').value = selectedSlotData.price.toFixed(2);
            document.getElementById('display-price').textContent = '$' + selectedSlotData.price.toFixed(2);
        }
    };

    document.getElementById('override_code_input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); verifyOverrideCode(); }
    });

    // ========== Service Billing Period Discount ==========
    let selectedSvcBillingPeriod = null;

    window.updateSvcBillingDiscountSection = function() {
        var section = document.getElementById('svc-billing-discount-section');
        var optionsContainer = document.getElementById('svc-billing-discount-options');
        removeSvcBillingDiscount();

        // Don't show billing discount accordion if period was already chosen in step 1
        if (selectedServiceBookingType === 'period') {
            section.classList.add('hidden');
            return;
        }

        if (!selectedSlotData || !selectedSlotData.billingDiscounts) {
            section.classList.add('hidden');
            return;
        }

        var discounts = selectedSlotData.billingDiscounts;
        var hasDiscounts = false;
        var basePrice = selectedSlotData.price;
        var periods = { '1': '1 Month', '3': '3 Months', '6': '6 Months', '9': '9 Months', '12': '12 Months' };
        var html = '';

        Object.keys(periods).forEach(function(months) {
            var m = parseInt(months);
            var totalAmount = parseFloat(discounts[months]) || 0;
            var isDiscounted = totalAmount > 0;
            if (isDiscounted) hasDiscounts = true;
            var monthlyRate = m > 0 ? (totalAmount / m) : 0;
            var totalWithout = basePrice * m;

            html += '<button type="button" class="svc-billing-btn flex flex-col items-center p-3 rounded-lg border-2 transition-all ' +
                (isDiscounted ? 'border-base-content/10 hover:border-success cursor-pointer' : 'border-base-content/5 opacity-50 cursor-default') + '" ' +
                'data-months="' + months + '" data-total="' + totalAmount + '" ' +
                (isDiscounted ? 'onclick="selectSvcBillingPeriod(' + months + ', ' + totalAmount + ')"' : '') + '>' +
                '<div class="text-xs text-base-content/60 font-medium">' + periods[months] + '</div>' +
                '<div class="text-lg font-bold ' + (isDiscounted ? 'text-success' : 'text-base-content/40') + '">$' + (isDiscounted ? totalAmount.toFixed(2) : totalWithout.toFixed(2)) + '</div>';
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

    var _svcBillingCreditAmount = 0;
    var _svcBillingRegFee = 0;

    window.selectSvcBillingPeriod = function(months, totalAmount) {
        selectedSvcBillingPeriod = months;
        var basePrice = selectedSlotData.price;
        var regFee = selectedSlotData.registrationFee || 0;
        var cancelFee = selectedSlotData.cancellationFee || 0;
        var graceHrs = selectedSlotData.graceHours || 48;
        var monthlyRate = months > 0 ? (totalAmount / months) : 0;
        _svcBillingCreditAmount = totalAmount;
        _svcBillingRegFee = regFee;
        var includeRegFee = regFee > 0;
        var totalDueToday = totalAmount + (includeRegFee ? regFee : 0);
        var totalWithout = basePrice * months;
        var totalSavings = totalWithout - totalAmount;
        var periods = { 1: '1 Month', 3: '3 Months', 6: '6 Months', 9: '9 Months', 12: '12 Months' };

        document.getElementById('svc-billing-period').value = months;
        document.getElementById('svc-billing-discount-percent').value = totalAmount;

        document.querySelectorAll('.svc-billing-btn').forEach(function(btn) {
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
        var regFeeContainer = document.getElementById('svc-reg-fee-container');
        if (regFee > 0) {
            document.getElementById('svc-reg-fee-amount').textContent = '$' + regFee.toFixed(2);
            document.getElementById('svc-reg-fee-checkbox').checked = true;
            document.getElementById('svc-include-reg-fee').value = '1';
            regFeeContainer.classList.remove('hidden');
        } else {
            regFeeContainer.classList.add('hidden');
        }

        var label = periods[months] + ' — $' + totalAmount.toFixed(2) + ' total ($' + monthlyRate.toFixed(2) + '/mo)';
        var details = 'Prepaid credit: $' + totalAmount.toFixed(2) + ' for ' + months + ' month' + (months > 1 ? 's' : '') + '.';
        if (totalSavings > 0) details += ' Save $' + totalSavings.toFixed(2) + ' vs regular $' + totalWithout.toFixed(2) + '.';

        var policyNote = '';
        if (cancelFee > 0) policyNote += 'Early cancellation fee: $' + cancelFee.toFixed(2) + '. ';
        policyNote += graceHrs + 'hr grace period for full refund.';

        document.getElementById('svc-applied-billing-label').textContent = label;
        document.getElementById('svc-applied-billing-savings').innerHTML = details + '<br><span class="text-xs text-base-content/50">' + policyNote + '</span>';
        document.getElementById('svc-applied-billing-discount').classList.remove('hidden');

        document.getElementById('display-price').textContent = '$' + totalDueToday.toFixed(2);
        document.getElementById('price-input').value = totalDueToday.toFixed(2);
    };

    window.toggleSvcRegFee = function() {
        var checked = document.getElementById('svc-reg-fee-checkbox').checked;
        document.getElementById('svc-include-reg-fee').value = checked ? '1' : '0';
        var total = _svcBillingCreditAmount + (checked ? _svcBillingRegFee : 0);
        document.getElementById('display-price').textContent = '$' + total.toFixed(2);
        document.getElementById('price-input').value = total.toFixed(2);
    };

    window.removeSvcBillingDiscount = function() {
        selectedSvcBillingPeriod = null;
        _svcBillingCreditAmount = 0;
        _svcBillingRegFee = 0;
        document.getElementById('svc-billing-period').value = '';
        document.getElementById('svc-billing-discount-percent').value = '0';
        document.getElementById('svc-applied-billing-discount').classList.add('hidden');
        document.getElementById('svc-reg-fee-container').classList.add('hidden');

        document.querySelectorAll('.svc-billing-btn').forEach(function(btn) {
            btn.classList.remove('border-success', 'bg-success/5');
            var t = parseFloat(btn.dataset.total) || 0;
            if (t > 0) btn.classList.add('border-base-content/10');
        });

        if (selectedSlotData) {
            document.getElementById('display-price').textContent = '$' + selectedSlotData.price.toFixed(2);
            document.getElementById('price-input').value = selectedSlotData.price.toFixed(2);
        }
    };

    // ========== Payment Options for Services ==========
    window.loadSvcPaymentOptions = function(clientId) {
        var list = document.getElementById('svc-payment-options-list');
        var loading = document.getElementById('svc-payment-options-loading');
        list.innerHTML = '';
        document.getElementById('svc-payment-method-hidden').value = 'manual';
        document.getElementById('svc-billing-credit-id').value = '';

        if (!clientId) return;

        loading.classList.remove('hidden');

        fetch('/walk-in/payment-methods/' + clientId + '?source_type=service_plan')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';

                // Billing credits
                if (data.billing_credits && data.billing_credits.length > 0) {
                    data.billing_credits.forEach(function(credit) {
                        html += '<label class="flex items-start gap-3 p-4 border-2 border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">' +
                            '<input type="radio" name="svc_payment_type" value="billing_credit_' + credit.id + '" class="radio radio-success mt-0.5" onchange="selectSvcPaymentOption(\'billing_credit\', ' + credit.id + ')">' +
                            '<div class="flex-1">' +
                            '<div class="font-medium flex items-center gap-2">' +
                            '<span class="icon-[tabler--calendar-dollar] size-5 text-success"></span> Use Billing Credit' +
                            '<span class="badge badge-success badge-sm">Prepaid</span></div>' +
                            '<div class="text-sm text-base-content/60 mt-1">' + credit.source_name + ' — $' + credit.credit_remaining.toFixed(2) + ' remaining</div>' +
                            '<div class="text-xs text-base-content/50 mt-0.5">' + credit.billing_period + 'mo prepaid · Valid until ' + credit.end_date + '</div>' +
                            '</div></label>';
                    });
                }

                // Pay Now (default)
                html += '<label class="flex items-start gap-3 p-4 border-2 border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">' +
                    '<input type="radio" name="svc_payment_type" value="manual" class="radio radio-primary mt-0.5" onchange="selectSvcPaymentOption(\'manual\')" checked>' +
                    '<div class="flex-1"><div class="font-medium flex items-center gap-2">' +
                    '<span class="icon-[tabler--cash] size-5 text-primary"></span> Pay Now</div>' +
                    '<div class="text-sm text-base-content/60 mt-1">Cash, card, or other payment</div></div></label>';

                // Comp (if allowed)
                if (data.comp) {
                    html += '<label class="flex items-start gap-3 p-4 border-2 border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all">' +
                        '<input type="radio" name="svc_payment_type" value="comp" class="radio radio-warning mt-0.5" onchange="selectSvcPaymentOption(\'comp\')">' +
                        '<div class="flex-1"><div class="font-medium flex items-center gap-2">' +
                        '<span class="icon-[tabler--gift] size-5 text-warning"></span> Complimentary</div>' +
                        '<div class="text-sm text-base-content/60 mt-1">No charge for this service</div></div></label>';
                }

                list.innerHTML = html;
            })
            .catch(function() {})
            .finally(function() { loading.classList.add('hidden'); });
    };

    window.selectSvcPaymentOption = function(option, creditId) {
        var paymentMethodHidden = document.getElementById('svc-payment-method-hidden');
        var billingCreditId = document.getElementById('svc-billing-credit-id');
        var paymentMethodContainer = document.getElementById('payment-method-container');
        var priceInput = document.getElementById('price-input');
        var displayPrice = document.getElementById('display-price');
        var priceHint = document.getElementById('price-input-hint');

        billingCreditId.value = '';

        if (option === 'billing_credit') {
            paymentMethodHidden.value = 'billing_credit';
            billingCreditId.value = creditId;
            paymentMethodContainer.classList.add('hidden');
            displayPrice.textContent = '$0.00';
            priceInput.value = '0';
            if (priceHint) priceHint.textContent = 'Using prepaid billing credit';
        } else if (option === 'comp') {
            paymentMethodHidden.value = 'comp';
            paymentMethodContainer.classList.add('hidden');
            displayPrice.textContent = '$0.00';
            priceInput.value = '0';
            if (priceHint) priceHint.textContent = 'Complimentary — no charge';
        } else {
            // Manual pay
            paymentMethodHidden.value = 'manual';
            paymentMethodContainer.classList.remove('hidden');
            if (selectedSlotData) {
                displayPrice.textContent = '$' + selectedSlotData.price.toFixed(2);
                priceInput.value = selectedSlotData.price.toFixed(2);
            }
            if (priceHint) priceHint.textContent = 'Set by payment option';
        }
    };
});
</script>
@endpush
@endsection
