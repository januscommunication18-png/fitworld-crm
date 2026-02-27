@extends('layouts.dashboard')

@section('title', $trans['space_rentals.new_booking'] ?? 'New Space Rental')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('space-rentals.index') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['space_rentals.new_booking'] ?? 'New Space Rental' }}</h1>
            <p class="text-base-content/60">{{ $trans['space_rentals.book_space_desc'] ?? 'Book a space for professional use or workshops' }}</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <div>
            <div class="font-medium">{{ $trans['common.fix_errors'] ?? 'Please fix the following errors:' }}</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Dynamic Error Container --}}
    <div id="form-error" class="alert alert-error mb-6 hidden">
        <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
        <span id="form-error-message"></span>
        <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideFormError()">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>

    {{-- Selected Space Info Card --}}
    <div id="space-info-card" class="card bg-base-100 border border-base-200 mb-6 {{ !$selectedConfigId ? 'hidden' : '' }}">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-secondary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building] size-7 text-secondary" id="space-icon"></span>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold" id="space-name">{{ $selectedConfig?->name ?? '--' }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--map-pin] size-4"></span>
                            <span id="space-location">{{ $selectedConfig?->location?->name ?? '--' }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4"></span>
                            <span id="space-rate">{{ $selectedConfig?->getFormattedHourlyRateForCurrency() ?? '--' }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            <span id="space-min-hours">{{ $selectedConfig?->minimum_hours ?? '--' }}h {{ $trans['common.minimum'] ?? 'min' }}</span>
                        </span>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="changeSpace()">
                    <span class="icon-[tabler--refresh] size-4"></span>
                    {{ $trans['btn.change'] ?? 'Change' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Booking Form --}}
    <form action="{{ route('space-rentals.store') }}" method="POST" id="rental-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Space Selection Card (shown when no space selected) --}}
                <div id="space-selection-card" class="card bg-base-100 border border-base-200 {{ $selectedConfigId ? 'hidden' : '' }}">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--building] size-5 mr-2"></span>
                            {{ $trans['space_rentals.select_space'] ?? 'Select Space' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($configs as $config)
                            <div class="border border-base-300 rounded-lg p-4 hover:bg-base-200/50 cursor-pointer transition-colors space-card"
                                 data-id="{{ $config->id }}"
                                 data-name="{{ $config->name }}"
                                 data-location="{{ $config->location?->name }}"
                                 data-rate="{{ $config->getHourlyRateForCurrency() }}"
                                 data-rate-formatted="{{ $config->getFormattedHourlyRateForCurrency() }}"
                                 data-deposit="{{ $config->getDepositForCurrency() ?? 0 }}"
                                 data-deposit-formatted="{{ $config->getFormattedDepositForCurrency() }}"
                                 data-min-hours="{{ $config->minimum_hours }}"
                                 data-max-hours="{{ $config->maximum_hours }}"
                                 data-requires-waiver="{{ $config->requires_waiver ? '1' : '0' }}"
                                 data-type-icon="{{ $config->type_icon }}"
                                 onclick="selectSpace(this)">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center shrink-0">
                                        <span class="icon-[tabler--{{ $config->type_icon }}] size-5 text-secondary"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold">{{ $config->name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $config->location?->name }}</div>
                                        <div class="flex items-center gap-3 mt-2 text-sm">
                                            <span class="font-medium text-primary">{{ $config->getFormattedHourlyRateForCurrency() }}</span>
                                            <span class="text-base-content/50">{{ $config->minimum_hours }}h min</span>
                                        </div>
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <input type="hidden" name="space_rental_config_id" id="space_rental_config_id" value="{{ old('space_rental_config_id', $selectedConfigId) }}">

                {{-- Client Selection Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--user] size-5"></span>
                            {{ $trans['walk_in.select_client'] ?? 'Select Client' }}
                        </h2>

                        {{-- Client Type Selection --}}
                        <div id="client-type-selection" class="grid grid-cols-3 gap-3 mb-4">
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="client_type" value="existing" class="radio radio-primary" {{ old('client_type', 'existing') === 'existing' ? 'checked' : '' }}>
                                <span class="icon-[tabler--users] size-6 text-primary"></span>
                                <div>
                                    <span class="font-semibold">{{ $trans['walk_in.existing_client'] ?? 'Existing Client' }}</span>
                                    <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.search_client_list'] ?? 'Search client list' }}</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="client_type" value="new" class="radio radio-success" {{ old('client_type') === 'new' ? 'checked' : '' }}>
                                <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                                <div>
                                    <span class="font-semibold">{{ $trans['walk_in.new_client'] ?? 'New Client' }}</span>
                                    <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.create_new_profile'] ?? 'Create new profile' }}</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all">
                                <input type="radio" name="client_type" value="external" class="radio radio-warning" {{ old('client_type') === 'external' ? 'checked' : '' }}>
                                <span class="icon-[tabler--user-question] size-6 text-warning"></span>
                                <div>
                                    <span class="font-semibold">{{ $trans['space_rentals.external_client'] ?? 'External Client' }}</span>
                                    <span class="text-xs text-base-content/60 block">{{ $trans['space_rentals.one_time_rental'] ?? 'One-time rental' }}</span>
                                </div>
                            </label>
                        </div>

                        {{-- Existing Client Section --}}
                        <div id="existing-client-section" class="{{ old('client_type', 'existing') !== 'existing' ? 'hidden' : '' }}">
                            <div class="form-control mb-4">
                                <div class="relative">
                                    <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                    <input type="text" id="client-search" class="input input-bordered w-full pl-10"
                                           placeholder="{{ $trans['walk_in.search_placeholder'] ?? 'Search by name, email or phone...' }}">
                                </div>
                            </div>
                            <div id="client-search-results" class="space-y-2"></div>
                        </div>

                        {{-- New Client Section --}}
                        <div id="new-client-section" class="{{ old('client_type') !== 'new' ? 'hidden' : '' }} space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label-text" for="new_first_name">{{ $trans['field.first_name'] ?? 'First Name' }} *</label>
                                    <input type="text" id="new_first_name" class="input input-bordered" placeholder="John">
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="new_last_name">{{ $trans['field.last_name'] ?? 'Last Name' }} *</label>
                                    <input type="text" id="new_last_name" class="input input-bordered" placeholder="Doe">
                                </div>
                            </div>
                            <div class="form-control">
                                <label class="label-text" for="new_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                                <input type="email" id="new_email" class="input input-bordered" placeholder="john@example.com">
                            </div>
                            <div class="form-control">
                                <label class="label-text" for="new_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                                <input type="tel" id="new_phone" class="input input-bordered" placeholder="+1 234 567 8900">
                            </div>
                            <button type="button" id="create-client-btn" class="btn btn-success btn-sm">
                                <span class="icon-[tabler--plus] size-4"></span>
                                {{ $trans['btn.create_client'] ?? 'Create Client' }}
                            </button>
                        </div>

                        {{-- External Client Section --}}
                        <div id="external-client-section" class="{{ old('client_type') !== 'external' ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control md:col-span-2">
                                    <label class="label-text" for="external_client_name">{{ $trans['field.name'] ?? 'Name' }} *</label>
                                    <input type="text" name="external_client_name" id="external_client_name"
                                        value="{{ old('external_client_name') }}"
                                        class="input input-bordered @error('external_client_name') input-error @enderror"
                                        placeholder="Company or Person Name">
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="external_client_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                                    <input type="email" name="external_client_email" id="external_client_email"
                                        value="{{ old('external_client_email') }}"
                                        class="input input-bordered"
                                        placeholder="contact@company.com">
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="external_client_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                                    <input type="tel" name="external_client_phone" id="external_client_phone"
                                        value="{{ old('external_client_phone') }}"
                                        class="input input-bordered"
                                        placeholder="+1 234 567 8900">
                                </div>
                                <div class="form-control md:col-span-2">
                                    <label class="label-text" for="external_client_company">{{ $trans['field.company'] ?? 'Company' }}</label>
                                    <input type="text" name="external_client_company" id="external_client_company"
                                        value="{{ old('external_client_company') }}"
                                        class="input input-bordered"
                                        placeholder="Company Name (if different from name)">
                                </div>
                            </div>
                        </div>

                        {{-- Selected Client Display --}}
                        <div id="selected-client" class="hidden mt-4 p-4 bg-primary/5 border border-primary/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div id="selected-client-avatar" class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold">
                                            <span id="selected-client-initials">JD</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="selected-client-name" class="font-semibold">John Doe</div>
                                        <div id="selected-client-email" class="text-sm text-base-content/60">john@example.com</div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelectedClient()" class="btn btn-ghost btn-sm btn-circle">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                            <input type="hidden" name="client_id" id="client_id" value="">
                        </div>
                    </div>
                </div>

                {{-- Schedule Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--calendar] size-5 mr-2"></span>
                            {{ $trans['space_rentals.purpose_schedule'] ?? 'Purpose & Schedule' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Purpose --}}
                            <div class="form-control">
                                <label for="purpose" class="label">
                                    <span class="label-text">{{ $trans['space_rentals.purpose'] ?? 'Purpose' }} <span class="text-error">*</span></span>
                                </label>
                                <select name="purpose" id="purpose" class="select select-bordered @error('purpose') select-error @enderror" required>
                                    @foreach($purposes as $key => $label)
                                        <option value="{{ $key }}" {{ old('purpose') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Date --}}
                            <div class="form-control">
                                <label for="date" class="label">
                                    <span class="label-text">{{ $trans['field.date'] ?? 'Date' }} <span class="text-error">*</span></span>
                                </label>
                                <input type="date" name="date" id="date" value="{{ old('date', today()->format('Y-m-d')) }}"
                                    min="{{ today()->format('Y-m-d') }}"
                                    class="input input-bordered @error('date') input-error @enderror" required>
                            </div>

                            {{-- Start Time --}}
                            <div class="form-control">
                                <label for="start_time" class="label">
                                    <span class="label-text">{{ $trans['field.start_time'] ?? 'Start Time' }} <span class="text-error">*</span></span>
                                </label>
                                <input type="time" name="start_time" id="start_time" value="{{ old('start_time', '09:00') }}"
                                    class="input input-bordered @error('start_time') input-error @enderror" required>
                            </div>

                            {{-- End Time --}}
                            <div class="form-control">
                                <label for="end_time" class="label">
                                    <span class="label-text">{{ $trans['field.end_time'] ?? 'End Time' }} <span class="text-error">*</span></span>
                                </label>
                                <input type="time" name="end_time" id="end_time" value="{{ old('end_time', '11:00') }}"
                                    class="input input-bordered @error('end_time') input-error @enderror" required>
                            </div>

                            {{-- Purpose Notes --}}
                            <div class="form-control md:col-span-2">
                                <label for="purpose_notes" class="label">
                                    <span class="label-text">{{ $trans['space_rentals.purpose_notes'] ?? 'Purpose Details' }}</span>
                                </label>
                                <textarea name="purpose_notes" id="purpose_notes" rows="2"
                                    class="textarea textarea-bordered"
                                    placeholder="{{ $trans['space_rentals.purpose_notes_placeholder'] ?? 'Any specific requirements or details about the rental' }}">{{ old('purpose_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Options Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--settings] size-5 mr-2"></span>
                            {{ $trans['common.options'] ?? 'Options' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- Initial Status --}}
                        <div class="form-control mb-4">
                            <label for="status" class="label">
                                <span class="label-text">{{ $trans['space_rentals.initial_status'] ?? 'Initial Status' }}</span>
                            </label>
                            <select name="status" id="status" class="select select-bordered">
                                <option value="confirmed" {{ old('status', 'confirmed') === 'confirmed' ? 'selected' : '' }}>{{ $trans['status.confirmed'] ?? 'Confirmed' }}</option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>{{ $trans['status.pending'] ?? 'Pending' }}</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>{{ $trans['status.draft'] ?? 'Draft' }}</option>
                            </select>
                        </div>

                        {{-- Internal Notes --}}
                        <div class="form-control">
                            <label for="internal_notes" class="label">
                                <span class="label-text">{{ $trans['field.internal_notes'] ?? 'Internal Notes' }}</span>
                            </label>
                            <textarea name="internal_notes" id="internal_notes" rows="2"
                                class="textarea textarea-bordered"
                                placeholder="{{ $trans['space_rentals.internal_notes_placeholder'] ?? 'Notes for staff (not visible to client)' }}">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Booking Summary Card --}}
                <div class="card bg-base-100 border border-base-200 sticky top-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $trans['walk_in.booking_summary'] ?? 'Booking Summary' }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['space_rentals.space'] ?? 'Space' }}</span>
                            <span class="font-medium" id="summary-space">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.date'] ?? 'Date' }}</span>
                            <span class="font-medium" id="summary-date">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.time'] ?? 'Time' }}</span>
                            <span class="font-medium" id="summary-time">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['schedule.duration'] ?? 'Duration' }}</span>
                            <span class="font-medium" id="summary-duration">--</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</span>
                            <span class="font-medium" id="summary-rate">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.subtotal'] ?? 'Subtotal' }}</span>
                            <span class="font-medium" id="summary-subtotal">--</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>{{ $trans['field.total'] ?? 'Total' }}</span>
                            <span class="text-primary" id="summary-total">--</span>
                        </div>

                        <div class="flex justify-between text-sm" id="deposit-row">
                            <span class="text-base-content/60">{{ $trans['space_rentals.deposit_required'] ?? 'Security Deposit' }}</span>
                            <span class="font-medium" id="summary-deposit">--</span>
                        </div>

                        {{-- Waiver Notice --}}
                        <div id="waiver-notice" class="hidden alert alert-warning py-2">
                            <span class="icon-[tabler--file-certificate] size-5"></span>
                            <span class="text-sm">{{ $trans['space_rentals.waiver_required_notice'] ?? 'Waiver required' }}</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div id="summary-client-row" class="hidden">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $trans['field.client'] ?? 'Client' }}</span>
                                <span class="font-medium" id="summary-client">--</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $trans['space_rentals.create_booking'] ?? 'Confirm Booking' }}
                        </button>

                        <a href="{{ route('space-rentals.index') }}" class="btn btn-ghost btn-block">
                            {{ $trans['btn.cancel'] ?? 'Cancel' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// State
let selectedSpaceId = {{ $selectedConfigId ?? 'null' }};
let selectedClientId = null;
let clientType = '{{ old("client_type", "existing") }}';
const currencySymbol = '{{ \App\Models\MembershipPlan::getCurrencySymbol($defaultCurrency) }}';

// Space data cache
const spaceData = {};
@foreach($configs as $config)
spaceData[{{ $config->id }}] = {
    name: '{{ $config->name }}',
    location: '{{ $config->location?->name ?? "" }}',
    rate: {{ $config->getHourlyRateForCurrency() ?? 0 }},
    rateFormatted: '{{ $config->getFormattedHourlyRateForCurrency() }}',
    deposit: {{ $config->getDepositForCurrency() ?? 0 }},
    depositFormatted: '{{ $config->getFormattedDepositForCurrency() }}',
    minHours: {{ $config->minimum_hours }},
    maxHours: {{ $config->maximum_hours ?? 'null' }},
    requiresWaiver: {{ $config->requires_waiver ? 'true' : 'false' }},
    typeIcon: '{{ $config->type_icon }}'
};
@endforeach

// DOM Elements
const clientSearch = document.getElementById('client-search');
const clientSearchResults = document.getElementById('client-search-results');
const existingClientSection = document.getElementById('existing-client-section');
const newClientSection = document.getElementById('new-client-section');
const externalClientSection = document.getElementById('external-client-section');
const selectedClientDiv = document.getElementById('selected-client');
const clientIdInput = document.getElementById('client_id');
const submitBtn = document.getElementById('submit-btn');

let searchTimeout;

// Error display
function showFormError(message) {
    const errorDiv = document.getElementById('form-error');
    document.getElementById('form-error-message').textContent = message;
    errorDiv.classList.remove('hidden');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideFormError() {
    document.getElementById('form-error').classList.add('hidden');
}

// Space selection
function selectSpace(el) {
    const id = el.dataset.id;
    selectedSpaceId = parseInt(id);
    document.getElementById('space_rental_config_id').value = id;

    const data = spaceData[id];

    // Update space info card
    document.getElementById('space-name').textContent = data.name;
    document.getElementById('space-location').textContent = data.location || '--';
    document.getElementById('space-rate').textContent = data.rateFormatted;
    document.getElementById('space-min-hours').textContent = data.minHours + 'h min';

    // Show info card, hide selection
    document.getElementById('space-info-card').classList.remove('hidden');
    document.getElementById('space-selection-card').classList.add('hidden');

    updateSummary();
    validateForm();
}

function changeSpace() {
    selectedSpaceId = null;
    document.getElementById('space_rental_config_id').value = '';
    document.getElementById('space-info-card').classList.add('hidden');
    document.getElementById('space-selection-card').classList.remove('hidden');
    updateSummary();
    validateForm();
}

// Client type toggle (radio buttons)
document.querySelectorAll('input[name="client_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        clientType = this.value;

        existingClientSection.classList.toggle('hidden', this.value !== 'existing');
        newClientSection.classList.toggle('hidden', this.value !== 'new');
        externalClientSection.classList.toggle('hidden', this.value !== 'external');

        // Clear client selection when switching types
        if (this.value !== 'existing') {
            clearSelectedClient();
        }

        validateForm();
    });
});

// Client search
clientSearch.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
        clientSearchResults.innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('walk-in.clients.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                clientSearchResults.innerHTML = '';
                if (data.clients.length === 0) {
                    clientSearchResults.innerHTML = '<p class="text-base-content/60 text-sm p-2">No clients found</p>';
                    return;
                }

                data.clients.forEach(client => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-3 p-3 bg-base-200/50 rounded-lg cursor-pointer hover:bg-base-200 transition-colors';
                    div.innerHTML = `
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                ${client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase()}
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">${client.first_name} ${client.last_name}</div>
                            <div class="text-xs text-base-content/60">${client.email || client.phone || ''}</div>
                        </div>
                    `;
                    div.addEventListener('click', () => selectClient(client));
                    clientSearchResults.appendChild(div);
                });
            });
    }, 300);
});

// Create new client
document.getElementById('create-client-btn').addEventListener('click', function() {
    const firstName = document.getElementById('new_first_name').value.trim();
    const lastName = document.getElementById('new_last_name').value.trim();
    const email = document.getElementById('new_email').value.trim();
    const phone = document.getElementById('new_phone').value.trim();

    if (!firstName || !lastName) {
        showFormError('Please enter first and last name');
        return;
    }

    fetch('{{ route('walk-in.clients.quick-add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            first_name: firstName,
            last_name: lastName,
            email: email,
            phone: phone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            selectClient(data.client);
            // Clear form
            document.getElementById('new_first_name').value = '';
            document.getElementById('new_last_name').value = '';
            document.getElementById('new_email').value = '';
            document.getElementById('new_phone').value = '';
        }
    });
});

function selectClient(client) {
    selectedClientId = client.id;
    clientIdInput.value = client.id;

    const initials = client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase();
    const fullName = `${client.first_name} ${client.last_name}`;

    document.getElementById('selected-client-initials').textContent = initials;
    document.getElementById('selected-client-name').textContent = fullName;
    document.getElementById('selected-client-email').textContent = client.email || client.phone || '';
    document.getElementById('summary-client').textContent = fullName;

    selectedClientDiv.classList.remove('hidden');
    document.getElementById('summary-client-row').classList.remove('hidden');
    clientSearch.value = '';
    clientSearchResults.innerHTML = '';

    // Switch to existing client type and hide other sections
    document.querySelector('input[name="client_type"][value="existing"]').checked = true;
    clientType = 'existing';
    existingClientSection.classList.remove('hidden');
    newClientSection.classList.add('hidden');
    externalClientSection.classList.add('hidden');

    validateForm();
}

window.clearSelectedClient = function() {
    selectedClientId = null;
    clientIdInput.value = '';
    selectedClientDiv.classList.add('hidden');
    document.getElementById('summary-client-row').classList.add('hidden');
    document.getElementById('summary-client').textContent = 'Not selected';
    validateForm();
};

// Summary updates
function updateSummary() {
    const dateInput = document.getElementById('date');
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');

    // Space
    if (selectedSpaceId && spaceData[selectedSpaceId]) {
        const space = spaceData[selectedSpaceId];
        document.getElementById('summary-space').textContent = space.name;
        document.getElementById('summary-rate').textContent = space.rateFormatted;

        // Deposit
        if (space.deposit > 0) {
            document.getElementById('summary-deposit').textContent = space.depositFormatted;
            document.getElementById('deposit-row').classList.remove('hidden');
        } else {
            document.getElementById('deposit-row').classList.add('hidden');
        }

        // Waiver
        document.getElementById('waiver-notice').classList.toggle('hidden', !space.requiresWaiver);
    } else {
        document.getElementById('summary-space').textContent = '--';
        document.getElementById('summary-rate').textContent = '--';
        document.getElementById('deposit-row').classList.add('hidden');
        document.getElementById('waiver-notice').classList.add('hidden');
    }

    // Date
    if (dateInput.value) {
        const date = new Date(dateInput.value + 'T00:00:00');
        document.getElementById('summary-date').textContent = date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    } else {
        document.getElementById('summary-date').textContent = '--';
    }

    // Time & Duration
    if (startInput.value && endInput.value) {
        document.getElementById('summary-time').textContent = formatTime(startInput.value) + ' - ' + formatTime(endInput.value);

        const startParts = startInput.value.split(':');
        const endParts = endInput.value.split(':');
        const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
        const durationMinutes = endMinutes - startMinutes;
        const hours = durationMinutes / 60;

        if (hours > 0) {
            document.getElementById('summary-duration').textContent = hours.toFixed(1) + ' hours';

            // Calculate pricing
            if (selectedSpaceId && spaceData[selectedSpaceId]) {
                const rate = spaceData[selectedSpaceId].rate;
                const subtotal = rate * hours;
                document.getElementById('summary-subtotal').textContent = currencySymbol + subtotal.toFixed(2);
                document.getElementById('summary-total').textContent = currencySymbol + subtotal.toFixed(2);
            }
        } else {
            document.getElementById('summary-duration').textContent = 'Invalid';
            document.getElementById('summary-subtotal').textContent = '--';
            document.getElementById('summary-total').textContent = '--';
        }
    } else {
        document.getElementById('summary-time').textContent = '--';
        document.getElementById('summary-duration').textContent = '--';
        document.getElementById('summary-subtotal').textContent = '--';
        document.getElementById('summary-total').textContent = '--';
    }

    // Client
    if (selectedClientId) {
        const clientName = document.getElementById('selected-client-name').textContent;
        document.getElementById('summary-client').textContent = clientName;
    } else if (clientType === 'external') {
        const externalName = document.getElementById('external_client_name').value.trim();
        document.getElementById('summary-client').textContent = externalName || 'Not entered';
        document.getElementById('summary-client-row').classList.toggle('hidden', !externalName);
    } else {
        document.getElementById('summary-client').textContent = 'Not selected';
    }
}

function formatTime(time24) {
    const [hours, minutes] = time24.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return h12 + ':' + minutes + ' ' + ampm;
}

// Validation
function validateForm() {
    let isValid = true;

    // Space required
    if (!selectedSpaceId) isValid = false;

    // Client required based on type
    const currentClientType = document.querySelector('input[name="client_type"]:checked')?.value || 'existing';

    if (currentClientType === 'existing' && !selectedClientId) isValid = false;
    if (currentClientType === 'new' && !selectedClientId) isValid = false; // Must create and select
    if (currentClientType === 'external' && !document.getElementById('external_client_name').value.trim()) isValid = false;

    submitBtn.disabled = !isValid;
}

// Event listeners
document.getElementById('date').addEventListener('change', updateSummary);
document.getElementById('start_time').addEventListener('change', updateSummary);
document.getElementById('end_time').addEventListener('change', updateSummary);
document.getElementById('external_client_name').addEventListener('input', function() {
    updateSummary();
    validateForm();
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
    validateForm();
});
</script>
@endpush
@endsection
