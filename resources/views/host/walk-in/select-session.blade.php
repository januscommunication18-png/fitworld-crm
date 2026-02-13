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

    {{-- Progress Steps --}}
    <div class="mb-8">
        <ul class="steps steps-horizontal w-full">
            <li class="step step-primary" data-step="1">Client & Class</li>
            <li class="step" data-step="2">Payment</li>
        </ul>
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
                    <div class="grid grid-cols-2 gap-3 mb-6">
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

                        {{-- Selected Client Display --}}
                        <div id="selected-client" class="hidden">
                            <div class="alert alert-success">
                                <div class="flex items-center gap-3 w-full">
                                    <div id="selected-avatar-container">
                                        <div id="selected-avatar-img" class="avatar hidden">
                                            <div class="size-12 rounded-full">
                                                <img id="selected-avatar-src" src="" alt="">
                                            </div>
                                        </div>
                                        <div id="selected-avatar-initials" class="avatar placeholder">
                                            <div class="bg-success-content text-success size-12 rounded-full">
                                                <span id="selected-initials" class="text-lg font-bold">JD</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-lg" id="selected-name">John Doe</div>
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
                        <div class="form-control">
                            <label class="label" for="new-phone"><span class="label-text">Phone</span></label>
                            <input type="tel" id="new-phone" name="phone" class="input input-bordered" placeholder="(555) 123-4567">
                        </div>
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
                                <p class="text-base-content/60">No sessions for this class on selected date</p>
                                <div id="next-available" class="hidden mt-4">
                                    <p class="text-sm text-base-content/60 mb-2">Next available:</p>
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

                {{-- Check in now --}}
                <div class="form-control mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentStep = 1;
    let selectedClientId = null;
    let selectedClientName = '';
    let selectedSessionId = null;
    let selectedSessionData = null;
    let isNewClient = false;

    // Elements
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const stepIndicators = document.querySelectorAll('.steps .step');

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
        document.getElementById(id).addEventListener('input', validateStep1);
    });

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

        document.getElementById('selected-client').classList.remove('hidden');
        document.getElementById('search-results').classList.add('hidden');
        searchInput.closest('.form-control').classList.add('hidden');

        validateStep1();
    };

    window.clearClient = function() {
        selectedClientId = null;
        selectedClientName = '';
        document.getElementById('client-id').value = '';
        document.getElementById('selected-client').classList.add('hidden');
        searchInput.closest('.form-control').classList.remove('hidden');
        searchInput.value = '';
        validateStep1();
    };

    function validateStep1() {
        let clientValid = false;
        if (isNewClient) {
            const firstName = document.getElementById('new-first-name').value.trim();
            const lastName = document.getElementById('new-last-name').value.trim();
            clientValid = firstName.length > 0 && lastName.length > 0;
        } else {
            clientValid = selectedClientId !== null;
        }

        const classPlanValid = selectedClassPlanId !== null;
        const sessionValid = selectedSessionId !== null;
        document.getElementById('step1-next').disabled = !(clientValid && classPlanValid && sessionValid);
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
            document.getElementById('session-selection').classList.add('hidden');
            validateStep1();
            return;
        }

        selectedClassPlanId = classPlanSelect.value;
        selectedClassPlanName = selectedOption.dataset.name || selectedOption.text.split(' - ')[0];

        // Show date selection
        document.getElementById('date-selection').classList.remove('hidden');

        // Reset session selection
        selectedSessionId = null;
        selectedSessionData = null;
        document.getElementById('session-id').value = '';
        document.getElementById('session-selection').classList.add('hidden');
        document.getElementById('sessions-list').innerHTML = '';
        validateStep1();

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

    function loadSessions(date) {
        if (!selectedClassPlanId) return;

        selectedSessionId = null;
        selectedSessionData = null;
        document.getElementById('session-id').value = '';
        validateStep1();

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
            price: parseFloat(radio.dataset.price) || 0
        };
        document.getElementById('session-id').value = selectedSessionId;
        validateStep1();
    };

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
        if (isNewClient) {
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

        // Update indicators
        stepIndicators.forEach((indicator, index) => {
            if (index + 1 <= step) {
                indicator.classList.add('step-primary');
            } else {
                indicator.classList.remove('step-primary');
            }
        });

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>
@endpush
@endsection
