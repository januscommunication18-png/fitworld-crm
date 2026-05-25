@extends('layouts.dashboard')

@section('title', $trans['space_rentals.new_booking'] ?? 'New Space Rental')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('space-rentals.index') }}"><span class="icon-[tabler--building] me-1 size-4"></span> {{ $trans['nav.space_rentals'] ?? 'Space Rentals' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['space_rentals.new_booking'] ?? 'New Space Rental' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['space_rentals.book_space_desc'] ?? 'Book a space for professional use or workshops.' }}</p>
        </div>
        <a href="{{ route('space-rentals.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    {{-- Dynamic Error Container --}}
    <div id="form-error" class="alert alert-soft alert-error hidden" role="alert">
        <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
        <span id="form-error-message"></span>
        <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideFormError()">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>

    <x-form-validate action="{{ route('space-rentals.store') }}" id="rental-form">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Form Cards --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Card 1: Space Selection --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">1</span>
                            <h3 class="card-title">{{ $trans['space_rentals.select_space'] ?? 'Select Space' }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Selected Space Display --}}
                        <div id="space-info-card" class="{{ !$selectedConfigId ? 'hidden' : '' }}">
                            <div class="flex items-center gap-4 p-4 bg-secondary/5 border border-secondary/20 rounded-lg">
                                <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--building] size-6 text-secondary" id="space-icon"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold" id="space-name">{{ $selectedConfig?->name ?? '--' }}</div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-base-content/60 mt-1">
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--map-pin] size-3.5"></span>
                                            <span id="space-location">{{ $selectedConfig?->location?->name ?? '--' }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--currency-dollar] size-3.5"></span>
                                            <span id="space-rate">{{ $selectedConfig?->getFormattedHourlyRateForCurrency() ?? '--' }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--clock] size-3.5"></span>
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

                        {{-- Space Picker Grid --}}
                        <div id="space-selection-card" class="{{ $selectedConfigId ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($configs as $config)
                                <div class="border border-base-300 rounded-lg p-4 hover:border-primary hover:bg-primary/5 cursor-pointer transition-all space-card"
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
                                            <div class="font-semibold truncate">{{ $config->name }}</div>
                                            <div class="text-xs text-base-content/60 truncate">{{ $config->location?->name }}</div>
                                            <div class="flex items-center gap-3 mt-2 text-xs">
                                                <span class="font-medium text-primary">{{ $config->getFormattedHourlyRateForCurrency() }}</span>
                                                <span class="text-base-content/50">{{ $config->minimum_hours }}h min</span>
                                            </div>
                                        </div>
                                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($configs->isEmpty())
                            <div class="text-center py-8 border-2 border-dashed border-base-300 rounded-lg">
                                <span class="icon-[tabler--building-off] size-10 text-base-content/30 mx-auto block mb-2"></span>
                                <p class="text-sm text-base-content/60">{{ $trans['space_rentals.no_spaces_configured'] ?? 'No rentable spaces configured yet.' }}</p>
                            </div>
                            @endif
                        </div>

                        <input type="hidden" name="space_rental_config_id" id="space_rental_config_id" value="{{ old('space_rental_config_id', $selectedConfigId) }}" required>
                    </div>
                </div>

                {{-- Card 2: Client Selection --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">2</span>
                            <h3 class="card-title">{{ $trans['walk_in.select_client'] ?? 'Select Client' }}</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Client Type Selection (option cards) --}}
                        <div id="client-type-selection" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all" for="client_type_existing">
                                <input type="radio" id="client_type_existing" name="client_type" value="existing" class="radio radio-primary" {{ old('client_type', 'existing') === 'existing' ? 'checked' : '' }}>
                                <span class="icon-[tabler--users] size-6 text-primary"></span>
                                <div>
                                    <span class="font-semibold block">{{ $trans['walk_in.existing_client'] ?? 'Existing Client' }}</span>
                                    <span class="text-xs text-base-content/60">{{ $trans['walk_in.search_client_list'] ?? 'Search client list' }}</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all" for="client_type_new">
                                <input type="radio" id="client_type_new" name="client_type" value="new" class="radio radio-success" {{ old('client_type') === 'new' ? 'checked' : '' }}>
                                <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                                <div>
                                    <span class="font-semibold block">{{ $trans['walk_in.new_client'] ?? 'New Client' }}</span>
                                    <span class="text-xs text-base-content/60">{{ $trans['walk_in.create_new_profile'] ?? 'Create new profile' }}</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all" for="client_type_external">
                                <input type="radio" id="client_type_external" name="client_type" value="external" class="radio radio-warning" {{ old('client_type') === 'external' ? 'checked' : '' }}>
                                <span class="icon-[tabler--user-question] size-6 text-warning"></span>
                                <div>
                                    <span class="font-semibold block">{{ $trans['space_rentals.external_client'] ?? 'External Client' }}</span>
                                    <span class="text-xs text-base-content/60">{{ $trans['space_rentals.one_time_rental'] ?? 'One-time rental' }}</span>
                                </div>
                            </label>
                        </div>

                        {{-- Existing Client Section --}}
                        <div id="existing-client-section" class="{{ old('client_type', 'existing') !== 'existing' ? 'hidden' : '' }}">
                            <label class="label-text" for="client-search">{{ $trans['walk_in.search_client'] ?? 'Search Clients' }}</label>
                            <div class="relative">
                                <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                <input type="text" id="client-search" class="input w-full pl-10"
                                       placeholder="{{ $trans['walk_in.search_placeholder'] ?? 'Search by name, email or phone...' }}">
                            </div>
                            <div id="client-search-results" class="space-y-2 mt-3"></div>
                        </div>

                        {{-- New Client Section --}}
                        <div id="new-client-section" class="{{ old('client_type') !== 'new' ? 'hidden' : '' }} space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text" for="new_first_name">{{ $trans['field.first_name'] ?? 'First Name' }} <span class="text-error">*</span></label>
                                    <input type="text" id="new_first_name" class="input w-full" placeholder="John">
                                </div>
                                <div>
                                    <label class="label-text" for="new_last_name">{{ $trans['field.last_name'] ?? 'Last Name' }} <span class="text-error">*</span></label>
                                    <input type="text" id="new_last_name" class="input w-full" placeholder="Doe">
                                </div>
                                <div>
                                    <label class="label-text" for="new_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                                    <input type="email" id="new_email" class="input w-full" placeholder="john@example.com">
                                </div>
                                <div>
                                    <label class="label-text" for="new_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                                    <input type="tel" id="new_phone" class="input w-full" placeholder="+1 234 567 8900">
                                </div>
                            </div>
                            <button type="button" id="create-client-btn" class="btn btn-success btn-sm">
                                <span class="icon-[tabler--plus] size-4"></span>
                                {{ $trans['btn.create_client'] ?? 'Create Client' }}
                            </button>
                        </div>

                        {{-- External Client Section --}}
                        <div id="external-client-section" class="{{ old('client_type') !== 'external' ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="label-text" for="external_client_name">{{ $trans['field.name'] ?? 'Name' }} <span class="text-error">*</span></label>
                                    <input type="text" name="external_client_name" id="external_client_name"
                                        value="{{ old('external_client_name') }}"
                                        class="input w-full @error('external_client_name') input-error @enderror"
                                        placeholder="Company or Person Name">
                                    @error('external_client_name')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="label-text" for="external_client_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                                    <input type="email" name="external_client_email" id="external_client_email"
                                        value="{{ old('external_client_email') }}"
                                        class="input w-full"
                                        placeholder="contact@company.com">
                                </div>
                                <div>
                                    <label class="label-text" for="external_client_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                                    <input type="tel" name="external_client_phone" id="external_client_phone"
                                        value="{{ old('external_client_phone') }}"
                                        class="input w-full"
                                        placeholder="+1 234 567 8900">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="label-text" for="external_client_company">{{ $trans['field.company'] ?? 'Company' }}</label>
                                    <input type="text" name="external_client_company" id="external_client_company"
                                        value="{{ old('external_client_company') }}"
                                        class="input w-full"
                                        placeholder="Company Name (if different from name)">
                                </div>
                            </div>
                        </div>

                        {{-- Selected Client Display --}}
                        <div id="selected-client" class="hidden p-4 bg-primary/5 border border-primary/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div id="selected-client-avatar" class="avatar avatar-placeholder">
                                        <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold flex items-center justify-center">
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

                {{-- Card 3: Purpose & Schedule --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">3</span>
                            <h3 class="card-title">{{ $trans['space_rentals.purpose_schedule'] ?? 'Purpose & Schedule' }}</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Purpose --}}
                            <div>
                                <label class="label-text" for="purpose">{{ $trans['space_rentals.purpose'] ?? 'Purpose' }} <span class="text-error">*</span></label>
                                <x-studio-select
                                    name="purpose"
                                    :options="$purposes"
                                    :selected="old('purpose')"
                                    placeholder="Select a purpose..."
                                    :required="true"
                                    id="purpose"
                                />
                                @error('purpose')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date --}}
                            <div>
                                <x-date-picker
                                    name="date"
                                    :value="old('date', today()->format('Y-m-d'))"
                                    :label="$trans['field.date'] ?? 'Date'"
                                    placeholder="Select date..."
                                    :required="true"
                                    :min-date="today()->format('Y-m-d')"
                                    id-suffix="date"
                                />
                            </div>

                            {{-- Start Time --}}
                            <div>
                                <x-time-picker
                                    name="start_time"
                                    :value="old('start_time', '09:00')"
                                    :label="$trans['field.start_time'] ?? 'Start Time'"
                                    placeholder="Select start time..."
                                    :required="true"
                                />
                            </div>

                            {{-- End Time --}}
                            <div>
                                <x-time-picker
                                    name="end_time"
                                    :value="old('end_time', '11:00')"
                                    :label="$trans['field.end_time'] ?? 'End Time'"
                                    placeholder="Select end time..."
                                    :required="true"
                                />
                            </div>

                            {{-- Purpose Notes --}}
                            <div class="md:col-span-2">
                                <label class="label-text" for="purpose_notes">{{ $trans['space_rentals.purpose_notes'] ?? 'Purpose Details' }}</label>
                                <textarea name="purpose_notes" id="purpose_notes" rows="2"
                                    class="textarea w-full"
                                    placeholder="{{ $trans['space_rentals.purpose_notes_placeholder'] ?? 'Any specific requirements or details about the rental' }}">{{ old('purpose_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Options --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center size-6 rounded-full bg-primary text-primary-content text-sm font-bold">4</span>
                            <h3 class="card-title">{{ $trans['common.options'] ?? 'Options' }}</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Initial Status --}}
                        <div>
                            <label class="label-text" for="status">{{ $trans['space_rentals.initial_status'] ?? 'Initial Status' }}</label>
                            <x-studio-select
                                name="status"
                                :options="[
                                    'confirmed' => $trans['status.confirmed'] ?? 'Confirmed',
                                    'pending' => $trans['status.pending'] ?? 'Pending',
                                    'draft' => $trans['status.draft'] ?? 'Draft',
                                ]"
                                :selected="old('status', 'confirmed')"
                                placeholder="Select status..."
                                id="status"
                            />
                        </div>

                        {{-- Internal Notes --}}
                        <div>
                            <label class="label-text" for="internal_notes">{{ $trans['field.internal_notes'] ?? 'Internal Notes' }}</label>
                            <textarea name="internal_notes" id="internal_notes" rows="2"
                                class="textarea w-full"
                                placeholder="{{ $trans['space_rentals.internal_notes_placeholder'] ?? 'Notes for staff (not visible to client)' }}">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Booking Summary --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="card bg-base-100 sticky top-4">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--receipt] size-5 text-primary"></span>
                            <h3 class="card-title">{{ $trans['walk_in.booking_summary'] ?? 'Booking Summary' }}</h3>
                        </div>
                    </div>
                    <div class="card-body space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['space_rentals.space'] ?? 'Space' }}</span>
                            <span class="font-medium" id="summary-space">--</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['field.date'] ?? 'Date' }}</span>
                            <span class="font-medium" id="summary-date">--</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['field.time'] ?? 'Time' }}</span>
                            <span class="font-medium" id="summary-time">--</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['schedule.duration'] ?? 'Duration' }}</span>
                            <span class="font-medium" id="summary-duration">--</span>
                        </div>

                        <div class="divider my-1"></div>

                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</span>
                            <span class="font-medium" id="summary-rate">--</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['field.subtotal'] ?? 'Subtotal' }}</span>
                            <span class="font-medium" id="summary-subtotal">--</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg pt-1">
                            <span>{{ $trans['field.total'] ?? 'Total' }}</span>
                            <span class="text-primary" id="summary-total">--</span>
                        </div>

                        <div class="flex justify-between text-sm" id="deposit-row">
                            <span class="text-base-content/60">{{ $trans['space_rentals.deposit_required'] ?? 'Security Deposit' }}</span>
                            <span class="font-medium" id="summary-deposit">--</span>
                        </div>

                        {{-- Waiver Notice --}}
                        <div id="waiver-notice" class="hidden alert alert-soft alert-warning py-2" role="alert">
                            <span class="icon-[tabler--file-certificate] size-5"></span>
                            <span class="text-sm">{{ $trans['space_rentals.waiver_required_notice'] ?? 'Waiver required' }}</span>
                        </div>

                        <div id="summary-client-row" class="hidden">
                            <div class="divider my-1"></div>
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">{{ $trans['field.client'] ?? 'Client' }}</span>
                                <span class="font-medium" id="summary-client">--</span>
                            </div>
                        </div>

                        {{-- Price Override Section --}}
                        @if($canOverridePrice || $canRequestOverride)
                        <div class="divider my-1"></div>

                        <input type="hidden" name="price_override_code" id="price_override_code" value="">
                        <input type="hidden" name="price_override_amount" id="price_override_amount" value="">

                        {{-- Applied Override Display --}}
                        <div id="applied-override" class="hidden">
                            <div class="alert alert-soft alert-success py-2" role="alert">
                                <span class="icon-[tabler--discount-check] size-5"></span>
                                <div class="flex-1">
                                    <div class="font-medium text-sm" id="applied-override-code">--</div>
                                    <div class="text-xs" id="applied-override-price"></div>
                                </div>
                                <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Override Input Section --}}
                        <div id="override-input-section">
                            @if($canOverridePrice)
                            <div>
                                <label class="label-text text-sm" for="direct-override-price">Override Total Price</label>
                                <div class="join w-full mt-1">
                                    <span class="join-item btn btn-sm">{{ \App\Models\MembershipPlan::getCurrencySymbol($defaultCurrency) }}</span>
                                    <input type="number" step="0.01" min="0" id="direct-override-price"
                                           class="input input-sm join-item flex-1"
                                           placeholder="Enter new total">
                                    <button type="button" onclick="applyDirectOverride()" class="btn btn-primary btn-sm join-item">Apply</button>
                                </div>
                            </div>
                            @else
                            <div>
                                <label class="label-text text-sm" for="override_code_input">Price Override Code</label>
                                <div class="join w-full mt-1">
                                    <input type="text" id="override_code_input"
                                           class="input input-sm join-item flex-1 uppercase"
                                           placeholder="PO-XXXXX or MY-XXXXX">
                                    <button type="button" id="verify-override-btn" onclick="verifyOverrideCode()" class="btn btn-secondary btn-sm join-item">Verify</button>
                                </div>
                                <p id="override-error" class="text-error text-xs mt-1 hidden"></p>

                                <div id="override-pending" class="hidden mt-2">
                                    <div class="alert alert-soft alert-warning py-2" role="alert">
                                        <span class="icon-[tabler--clock] size-5"></span>
                                        <div class="flex-1 text-sm">
                                            Awaiting approval: <strong id="pending-code">--</strong>
                                        </div>
                                        <button type="button" onclick="checkOverrideStatus()" class="btn btn-ghost btn-xs">
                                            <span class="icon-[tabler--refresh] size-3"></span>
                                        </button>
                                    </div>
                                </div>

                                <button type="button" onclick="showOverrideModal()" id="request-override-btn" class="btn btn-outline btn-secondary btn-sm btn-block mt-2">
                                    <span class="icon-[tabler--discount] size-4"></span>
                                    Request Price Override
                                </button>
                            </div>
                            @endif
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary btn-block mt-2" id="submit-btn" disabled>
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
    </x-form-validate>
</div>

{{-- Price Override Request Modal --}}
@if($canRequestOverride)
<div id="override-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog max-w-md">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Request Price Override</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeOverrideModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body space-y-4">
                <div>
                    <label class="label-text" for="override-new-price">New Total Price</label>
                    <div class="join w-full">
                        <span class="join-item btn">{{ \App\Models\MembershipPlan::getCurrencySymbol($defaultCurrency) }}</span>
                        <input type="number" step="0.01" min="0" id="override-new-price" class="input join-item flex-1" placeholder="0.00">
                    </div>
                    <p class="text-xs text-base-content/60 mt-1">Original: <span id="modal-original-price">$0.00</span></p>
                </div>
                <div>
                    <label class="label-text" for="override-reason">Reason</label>
                    <textarea id="override-reason" class="textarea w-full" rows="2" placeholder="Why is this override needed?"></textarea>
                </div>
                <p id="modal-error" class="text-error text-sm hidden"></p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeOverrideModal()" class="btn btn-soft btn-secondary">Cancel</button>
                <button type="button" onclick="submitOverrideRequest()" id="submit-override-btn" class="btn btn-primary">
                    <span class="icon-[tabler--send] size-4"></span>
                    Send Request
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Personal Override Modal --}}
<div id="personal-override-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog max-w-md">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <span class="icon-[tabler--shield-check] size-5 text-secondary mr-2"></span>
                    Personal Override
                </h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closePersonalOverrideModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body space-y-4">
                <div class="alert alert-soft alert-info py-2" role="alert">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span class="text-sm">Supervised by <strong id="personal-supervisor-name">--</strong> (<span id="personal-supervisor-code">--</span>)</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-base-content/60">Original Price:</span>
                    <span class="font-medium" id="personal-modal-original-price">$0.00</span>
                </div>
                <div>
                    <label class="label-text" for="personal-override-new-price">New Total Price</label>
                    <div class="join w-full">
                        <span class="join-item btn">{{ \App\Models\MembershipPlan::getCurrencySymbol($defaultCurrency) }}</span>
                        <input type="number" step="0.01" min="0" id="personal-override-new-price" class="input join-item flex-1" placeholder="0.00" oninput="updatePersonalOverridePreview()">
                    </div>
                </div>
                <div id="personal-override-preview" class="hidden bg-success/10 border border-success/30 rounded-lg p-3">
                    <div class="flex justify-between text-sm">
                        <span>Discount:</span>
                        <span class="font-bold text-success" id="personal-preview-discount">-$0.00</span>
                    </div>
                </div>
                <p id="personal-modal-error" class="text-error text-sm hidden"></p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-soft btn-secondary">Cancel</button>
                <button type="button" onclick="applyPersonalOverride()" class="btn btn-success">
                    <span class="icon-[tabler--check] size-4"></span>
                    Apply Override
                </button>
            </div>
        </div>
    </div>
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
    name: '{{ addslashes($config->name) }}',
    location: '{{ addslashes($config->location?->name ?? "") }}',
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

    document.getElementById('space-name').textContent = data.name;
    document.getElementById('space-location').textContent = data.location || '--';
    document.getElementById('space-rate').textContent = data.rateFormatted;
    document.getElementById('space-min-hours').textContent = data.minHours + 'h min';

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

        if (this.value !== 'existing') {
            clearSelectedClient();
        }

        validateForm();
    });
});

// Client search
if (clientSearch) {
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
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm flex items-center justify-center">
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
}

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

// Helper to find the date/time input by name (x-date-picker/x-time-picker use generated ids)
function findInputByName(name) {
    return document.querySelector('input[name="' + name + '"]');
}

// Summary updates
function updateSummary() {
    const dateInput = findInputByName('date');
    const startInput = findInputByName('start_time');
    const endInput = findInputByName('end_time');

    // Space
    if (selectedSpaceId && spaceData[selectedSpaceId]) {
        const space = spaceData[selectedSpaceId];
        document.getElementById('summary-space').textContent = space.name;
        document.getElementById('summary-rate').textContent = space.rateFormatted;

        if (space.deposit > 0) {
            document.getElementById('summary-deposit').textContent = space.depositFormatted;
            document.getElementById('deposit-row').classList.remove('hidden');
        } else {
            document.getElementById('deposit-row').classList.add('hidden');
        }

        document.getElementById('waiver-notice').classList.toggle('hidden', !space.requiresWaiver);
    } else {
        document.getElementById('summary-space').textContent = '--';
        document.getElementById('summary-rate').textContent = '--';
        document.getElementById('deposit-row').classList.add('hidden');
        document.getElementById('waiver-notice').classList.add('hidden');
    }

    // Date
    if (dateInput && dateInput.value) {
        const date = new Date(dateInput.value + 'T00:00:00');
        document.getElementById('summary-date').textContent = date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    } else {
        document.getElementById('summary-date').textContent = '--';
    }

    // Time & Duration
    if (startInput && endInput && startInput.value && endInput.value) {
        document.getElementById('summary-time').textContent = formatTime(startInput.value) + ' - ' + formatTime(endInput.value);

        const startParts = startInput.value.split(':');
        const endParts = endInput.value.split(':');
        const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
        const durationMinutes = endMinutes - startMinutes;
        const hours = durationMinutes / 60;

        if (hours > 0) {
            document.getElementById('summary-duration').textContent = hours.toFixed(1) + ' hours';

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

    if (!selectedSpaceId) isValid = false;

    const currentClientType = document.querySelector('input[name="client_type"]:checked')?.value || 'existing';

    if (currentClientType === 'existing' && !selectedClientId) isValid = false;
    if (currentClientType === 'new' && !selectedClientId) isValid = false;
    if (currentClientType === 'external' && !document.getElementById('external_client_name').value.trim()) isValid = false;

    submitBtn.disabled = !isValid;
}

// Event listeners — wait for pickers to mount, then bind change handlers.
// x-time-picker (12h mode) renames the visible input and creates a hidden one for submission.
// flatpickr fires 'change' on the visible input, so listen on data-time-picker elements directly.
let listenersBound = false;
function bindDateTimeListeners() {
    if (listenersBound) return;
    const dateInput = findInputByName('date');
    if (dateInput && !dateInput.dataset.listenerBound) {
        dateInput.addEventListener('change', updateSummary);
        dateInput.dataset.listenerBound = '1';
    }
    document.querySelectorAll('[data-time-picker]').forEach(function(el) {
        if (el.dataset.listenerBound) return;
        // flatpickr fires onChange callbacks then dispatches change on the input
        el.addEventListener('change', updateSummary);
        // Also hook flatpickr's onChange for the rare case where altInput swallows the event
        if (el._flatpickr) {
            el._flatpickr.config.onChange.push(updateSummary);
        }
        el.dataset.listenerBound = '1';
    });
    listenersBound = !!findInputByName('start_time');
}

document.getElementById('external_client_name').addEventListener('input', function() {
    updateSummary();
    validateForm();
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    bindDateTimeListeners();
    // Re-bind after pickers initialize (they replace inputs/wrap with flatpickr)
    setTimeout(bindDateTimeListeners, 200);
    setTimeout(bindDateTimeListeners, 600);
    updateSummary();
    validateForm();
});

// ========== Price Override Functions ==========
let pendingOverrideId = null;
let pendingOverrideCode = null;
let statusCheckInterval = null;
let personalOverrideCode = null;
let personalOverrideSupervisor = null;

function getCalculatedTotal() {
    if (!selectedSpaceId || !spaceData[selectedSpaceId]) return 0;
    const space = spaceData[selectedSpaceId];
    const startInput = findInputByName('start_time');
    const endInput = findInputByName('end_time');
    if (!startInput || !endInput || !startInput.value || !endInput.value) return 0;
    const start = new Date('2000-01-01 ' + startInput.value);
    const end = new Date('2000-01-01 ' + endInput.value);
    const hours = (end - start) / (1000 * 60 * 60);
    return hours > 0 ? space.rate * hours : 0;
}

@if($canOverridePrice ?? false)
function applyDirectOverride() {
    const priceInput = document.getElementById('direct-override-price');
    const newPrice = parseFloat(priceInput.value);
    if (isNaN(newPrice) || newPrice < 0) {
        showFormError('Please enter a valid price.');
        return;
    }
    applyOverride('DIRECT', newPrice);
    priceInput.value = '';
}
@endif

@if($canRequestOverride ?? false)
function showOverrideModal() {
    const total = getCalculatedTotal();
    document.getElementById('modal-original-price').textContent = currencySymbol + total.toFixed(2);
    document.getElementById('override-new-price').value = '';
    document.getElementById('override-reason').value = '';
    document.getElementById('modal-error').classList.add('hidden');
    document.getElementById('override-modal').classList.remove('hidden');
}

function closeOverrideModal() {
    document.getElementById('override-modal').classList.add('hidden');
}

function submitOverrideRequest() {
    const newPrice = parseFloat(document.getElementById('override-new-price').value);
    const reason = document.getElementById('override-reason').value.trim();
    const originalPrice = getCalculatedTotal();
    const modalError = document.getElementById('modal-error');
    const submitOverrideBtn = document.getElementById('submit-override-btn');

    if (isNaN(newPrice) || newPrice < 0) {
        modalError.textContent = 'Please enter a valid price.';
        modalError.classList.remove('hidden');
        return;
    }

    submitOverrideBtn.disabled = true;
    submitOverrideBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';

    fetch('{{ route("price-override.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            original_price: originalPrice,
            requested_price: newPrice,
            reason: reason,
            bookable_type: 'space_rental',
            client_id: selectedClientId || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeOverrideModal();
            pendingOverrideId = data.data.id;
            pendingOverrideCode = data.data.confirmation_code;
            document.getElementById('pending-code').textContent = data.data.confirmation_code;
            document.getElementById('override-input-section').querySelector('#request-override-btn')?.classList.add('hidden');
            document.getElementById('override-pending').classList.remove('hidden');
            startStatusCheck();
        } else {
            modalError.textContent = data.message || 'Failed to create override request.';
            modalError.classList.remove('hidden');
        }
    })
    .catch(() => {
        modalError.textContent = 'Failed to create override request. Please try again.';
        modalError.classList.remove('hidden');
    })
    .finally(() => {
        submitOverrideBtn.disabled = false;
        submitOverrideBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send Request';
    });
}
@endif

function verifyOverrideCode() {
    const code = document.getElementById('override_code_input').value.trim().toUpperCase();
    const errorEl = document.getElementById('override-error');
    const btn = document.getElementById('verify-override-btn');

    if (!code) {
        errorEl.textContent = 'Please enter a code.';
        errorEl.classList.remove('hidden');
        return;
    }

    errorEl.classList.add('hidden');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch('{{ route("price-override.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = 'Verify';

        if (data.success || data.valid) {
            if (data.is_personal_code) {
                personalOverrideCode = data.code;
                personalOverrideSupervisor = data.data?.authorized_by?.name || 'Manager';
                showPersonalOverrideModal(data.code, personalOverrideSupervisor);
            } else if (data.data?.is_approved || data.valid) {
                applyOverride(data.code || data.data?.confirmation_code, data.requested_price || data.data?.requested_price);
            } else if (data.data?.is_pending) {
                document.getElementById('pending-code').textContent = data.data.confirmation_code;
                document.getElementById('override-input-section').querySelector('#request-override-btn')?.classList.add('hidden');
                document.getElementById('override-pending').classList.remove('hidden');
                pendingOverrideId = data.data.id;
                pendingOverrideCode = data.data.confirmation_code;
                startStatusCheck();
            }
        } else {
            errorEl.textContent = data.message || 'Invalid or expired code.';
            errorEl.classList.remove('hidden');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = 'Verify';
        errorEl.textContent = 'Error verifying code.';
        errorEl.classList.remove('hidden');
    });
}

function showPersonalOverrideModal(code, supervisorName) {
    document.getElementById('personal-supervisor-name').textContent = supervisorName;
    document.getElementById('personal-supervisor-code').textContent = code;
    const originalPrice = getCalculatedTotal();
    document.getElementById('personal-modal-original-price').textContent = currencySymbol + originalPrice.toFixed(2);
    document.getElementById('personal-override-new-price').value = '';
    document.getElementById('personal-override-preview').classList.add('hidden');
    document.getElementById('personal-modal-error').classList.add('hidden');
    document.getElementById('personal-override-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('personal-override-new-price').focus(), 100);
}

function closePersonalOverrideModal() {
    document.getElementById('personal-override-modal').classList.add('hidden');
    personalOverrideCode = null;
    personalOverrideSupervisor = null;
}

function updatePersonalOverridePreview() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
    const originalPrice = getCalculatedTotal();
    const previewDiv = document.getElementById('personal-override-preview');

    if (!isNaN(newPrice) && newPrice >= 0 && newPrice < originalPrice) {
        const discount = originalPrice - newPrice;
        document.getElementById('personal-preview-discount').textContent = '-' + currencySymbol + discount.toFixed(2);
        previewDiv.classList.remove('hidden');
    } else {
        previewDiv.classList.add('hidden');
    }
}

function applyPersonalOverride() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
    const errorEl = document.getElementById('personal-modal-error');

    if (isNaN(newPrice) || newPrice < 0) {
        errorEl.textContent = 'Please enter a valid price.';
        errorEl.classList.remove('hidden');
        return;
    }

    applyOverride(personalOverrideCode, newPrice);
    closePersonalOverrideModal();
}

function applyOverride(code, price) {
    document.getElementById('price_override_code').value = code;
    document.getElementById('price_override_amount').value = price;
    document.getElementById('applied-override-code').textContent = code;
    document.getElementById('applied-override-price').textContent = currencySymbol + parseFloat(price).toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');
    document.getElementById('override-pending').classList.add('hidden');
    const codeInput = document.getElementById('override_code_input');
    if (codeInput) codeInput.value = '';

    document.getElementById('summary-subtotal').textContent = currencySymbol + parseFloat(price).toFixed(2);
    document.getElementById('summary-total').textContent = currencySymbol + parseFloat(price).toFixed(2);

    stopStatusCheck();
}

function removeOverride() {
    document.getElementById('price_override_code').value = '';
    document.getElementById('price_override_amount').value = '';
    document.getElementById('applied-override').classList.add('hidden');
    document.getElementById('override-input-section').classList.remove('hidden');
    const requestBtn = document.getElementById('request-override-btn');
    if (requestBtn) requestBtn.classList.remove('hidden');

    updateSummary();
    stopStatusCheck();
}

function startStatusCheck() {
    if (statusCheckInterval) clearInterval(statusCheckInterval);
    checkOverrideStatus();
    statusCheckInterval = setInterval(checkOverrideStatus, 5000);
}

function stopStatusCheck() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
}

function checkOverrideStatus() {
    if (!pendingOverrideId) return;

    fetch(`{{ url('price-override') }}/${pendingOverrideId}/status`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'approved') {
            stopStatusCheck();
            applyOverride(data.code, data.requested_price);
        } else if (data.status === 'rejected' || data.status === 'expired') {
            stopStatusCheck();
            document.getElementById('override-pending').classList.add('hidden');
            document.getElementById('override-input-section').classList.remove('hidden');
            const requestBtn = document.getElementById('request-override-btn');
            if (requestBtn) requestBtn.classList.remove('hidden');
            const errorEl = document.getElementById('override-error');
            errorEl.textContent = data.status === 'rejected'
                ? 'Override request was rejected.' + (data.rejection_reason ? ' Reason: ' + data.rejection_reason : '')
                : 'Override request expired.';
            errorEl.classList.remove('hidden');
            pendingOverrideId = null;
            pendingOverrideCode = null;
        }
    })
    .catch(error => console.error('Status check error:', error));
}
</script>
@endpush
@endsection
