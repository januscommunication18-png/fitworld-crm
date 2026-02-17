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
                            <span class="text-base-content/70">Service</span>
                            <span class="font-medium" id="summary-service">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Date & Time</span>
                            <span class="font-medium" id="summary-datetime">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Instructor</span>
                            <span class="font-medium" id="summary-instructor">--</span>
                        </div>
                    </div>
                </div>

                {{-- Price Display --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Service Price</span>
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

        const allValid = clientValid && servicePlanValid && slotValid;
        document.getElementById('step1-next').disabled = !allValid;
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
            document.getElementById('date-selection').classList.add('hidden');
            document.getElementById('slot-selection').classList.add('hidden');
            validateStep1();
            return;
        }

        selectedServicePlanId = servicePlanSelect.value;
        selectedServicePlanName = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];
        selectedServiceDuration = parseInt(selectedOption.dataset.duration) || 60;

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
        selectedSlotData = {
            service: radio.dataset.service,
            time: radio.dataset.time,
            instructor: radio.dataset.instructor,
            price: parseFloat(radio.dataset.price) || 0,
            startTime: radio.dataset.start,
            endTime: radio.dataset.end
        };
        document.getElementById('slot-id').value = selectedSlotId;

        updateCheckInVisibility();
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
                alert('Please enter both first and last name for the new client.');
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
                    alert(errorMsg);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return;
                }

                if (!data.success) {
                    alert(data.message || 'Failed to create client. Please try again.');
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
                alert('Error creating client. Please try again.');
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
            alert('Client ID was not properly set. Please try again.');
            return;
        }

        document.getElementById('summary-client').textContent = selectedClientName;
        document.getElementById('summary-service').textContent = selectedServicePlanName;
        document.getElementById('summary-datetime').textContent = datePicker.altInput.value + ' at ' + selectedSlotData.time;
        document.getElementById('summary-instructor').textContent = selectedSlotData.instructor;

        const price = selectedSlotData.price;
        document.getElementById('display-price').textContent = '$' + price.toFixed(2);
        document.getElementById('price-input').value = price.toFixed(2);

        document.getElementById('booking-form').action = `/walk-in/service/${selectedSlotId}`;
        console.log('Form action set to:', document.getElementById('booking-form').action);

        goToStep(2);
    });

    // Form submit validation
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        const clientId = document.getElementById('client-id').value;
        const slotId = document.getElementById('slot-id').value;

        console.log('Form submitting with:', { client_id: clientId, slot_id: slotId, action: this.action });

        if (!clientId || clientId === 'new') {
            e.preventDefault();
            alert('Error: Client ID is not set. Please go back and select a client again.');
            return false;
        }

        if (!slotId) {
            e.preventDefault();
            alert('Error: Slot ID is not set. Please go back and select a slot again.');
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
            alert('Please select an instructor');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!startTime) {
            alert('Please select a time slot');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        if (!modalInstructorWorksToday) {
            alert('Cannot create slot - instructor does not work on this day');
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
            alert(err.message || 'Error creating slot. Please try again.');
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
});
</script>
@endpush
@endsection
