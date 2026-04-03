@extends('layouts.dashboard')

@section('title', $trans['page.book_service'] ?? 'Book Service')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('service-slots.index') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['page.book_service'] ?? 'Book Service' }}</h1>
            <p class="text-base-content/60">{{ $trans['walk_in.book_client_appointment'] ?? 'Book a client for this appointment' }}</p>
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

    {{-- Service Info Card --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--massage] size-7 text-primary"></span>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold">{{ $slot->servicePlan->name ?? 'Service' }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar] size-4"></span>
                            {{ $slot->start_time->format('l, M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $slot->formatted_time_range }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--hourglass] size-4"></span>
                            {{ $slot->duration_minutes }} min
                        </span>
                        @if($slot->instructor)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $slot->instructor->name }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-primary">
                        {{ $slot->formatted_price }}
                    </div>
                    <div class="text-sm text-base-content/60">{{ $trans['field.price'] ?? 'price' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Form --}}
    <form action="{{ route('walk-in.service.book', $slot) }}" method="POST" id="walk-in-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Client Selection --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Client Selection Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--user] size-5 mr-2"></span>
                            {{ $trans['walk_in.select_client'] ?? 'Select Client' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- Search --}}
                        <div class="form-control mb-4">
                            <div class="relative">
                                <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                <input type="text"
                                       id="client-search"
                                       class="input input-bordered w-full pl-10"
                                       placeholder="{{ $trans['walk_in.search_placeholder'] ?? 'Search by name, email, or phone...' }}"
                                       autocomplete="off">
                                <div id="search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                    <span class="loading loading-spinner loading-sm"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Search Results --}}
                        <div id="search-results" class="hidden mb-4">
                            <div class="text-sm font-medium text-base-content/60 mb-2">{{ $trans['walk_in.search_results'] ?? 'Search Results' }}</div>
                            <div id="search-results-list" class="space-y-2"></div>
                        </div>

                        {{-- Selected Client --}}
                        <div id="selected-client" class="hidden mb-4">
                            <div class="alert alert-success">
                                <div class="flex items-center gap-3 w-full">
                                    <div id="selected-avatar-container">
                                        {{-- Avatar with image --}}
                                        <div id="selected-avatar-img" class="avatar hidden">
                                            <div class="size-12 rounded-full">
                                                <img id="selected-avatar-src" src="" alt="">
                                            </div>
                                        </div>
                                        {{-- Avatar with initials --}}
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
                                        {{ $trans['btn.change'] ?? 'Change' }}
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="client_id" id="client-id" value="">
                        </div>

                        {{-- Recent Clients --}}
                        <div id="recent-clients">
                            <div class="text-sm font-medium text-base-content/60 mb-2">{{ $trans['walk_in.recent_clients'] ?? 'Recent Clients' }}</div>
                            <div class="space-y-2">
                                @forelse($recentClients as $client)
                                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg hover:bg-base-200/50 cursor-pointer transition-colors"
                                     onclick="selectClient({{ $client->id }}, '{{ $client->first_name }}', '{{ $client->last_name }}', '{{ $client->email }}', '{{ $client->phone }}', '{{ $client->avatar_url }}')">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :src="$client->avatar_url" :initials="$client->initials" :alt="$client->full_name" size="md" />
                                        <div>
                                            <div class="font-medium">{{ $client->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $client->email ?: $client->phone ?: ($trans['common.no_contact_info'] ?? 'No contact info') }}</div>
                                        </div>
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-base-content/50">
                                    <span class="icon-[tabler--users-off] size-8 mb-2"></span>
                                    <p class="text-sm">{{ $trans['walk_in.no_recent_clients'] ?? 'No recent clients' }}</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Quick Add --}}
                        <div class="divider">OR</div>

                        <button type="button" class="btn btn-outline btn-primary btn-block" onclick="toggleQuickAdd()">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            {{ $trans['btn.add_new_client'] ?? 'Add New Client' }}
                        </button>

                        <div id="quick-add-form" class="hidden mt-4 p-4 border border-base-300 rounded-lg bg-base-200/30">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="form-control">
                                    <label class="label" for="new-first-name"><span class="label-text">{{ $trans['field.first_name'] ?? 'First Name' }} *</span></label>
                                    <input type="text" id="new-first-name" class="input input-bordered" placeholder="John">
                                </div>
                                <div class="form-control">
                                    <label class="label" for="new-last-name"><span class="label-text">{{ $trans['field.last_name'] ?? 'Last Name' }} *</span></label>
                                    <input type="text" id="new-last-name" class="input input-bordered" placeholder="Doe">
                                </div>
                            </div>
                            <div class="form-control mb-3">
                                <label class="label" for="new-email"><span class="label-text">{{ $trans['field.email'] ?? 'Email' }}</span></label>
                                <input type="email" id="new-email" class="input input-bordered" placeholder="john@example.com">
                            </div>
                            <div class="form-control mb-4">
                                <label class="label" for="new-phone"><span class="label-text">{{ $trans['field.phone'] ?? 'Phone' }}</span></label>
                                <input type="tel" id="new-phone" class="input input-bordered" placeholder="(555) 123-4567">
                            </div>
                            <button type="button" class="btn btn-primary btn-block" onclick="createClient()">
                                <span class="icon-[tabler--check] size-5"></span>
                                {{ $trans['btn.create_select'] ?? 'Create & Select' }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Payment Method Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--credit-card] size-5 mr-2"></span>
                            {{ $trans['walk_in.payment_method'] ?? 'Payment Method' }}
                        </h3>
                    </div>
                    <div class="card-body space-y-3">
                        @php
                            $priceEditable = ($canOverridePrice ?? false) || (!($canOverridePrice ?? false) && !($canRequestOverride ?? false));
                        @endphp
                        {{-- Manual Payment --}}
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="manual" class="radio radio-primary mt-0.5" checked>
                            <div class="flex-1">
                                <div class="font-medium flex items-center gap-2">
                                    {{ $trans['walk_in.manual_payment'] ?? 'Manual Payment' }}
                                    @if($priceEditable)
                                        <span class="badge badge-success badge-xs">Editable</span>
                                    @endif
                                </div>
                                <div class="text-sm text-base-content/60">{{ $trans['walk_in.cash_card_check'] ?? 'Cash, card, check, or other' }}</div>
                                <div class="mt-3 grid grid-cols-2 gap-3" id="manual-details">
                                    <select name="manual_method" class="select select-bordered select-sm">
                                        <option value="cash">{{ $trans['payment.cash'] ?? 'Cash' }}</option>
                                        <option value="card">{{ $trans['payment.card'] ?? 'Card' }}</option>
                                        <option value="check">{{ $trans['payment.check'] ?? 'Check' }}</option>
                                        <option value="other">{{ $trans['payment.other'] ?? 'Other' }}</option>
                                    </select>
                                    <div class="relative">
                                        <input type="number" name="price_paid" step="0.01" min="0"
                                               class="input input-bordered input-sm w-full {{ $priceEditable ? 'pr-8' : '' }}"
                                               placeholder="{{ $trans['field.amount'] ?? 'Amount' }}" value="{{ $slot->getEffectivePrice() ?? 0 }}">
                                        @if($priceEditable)
                                            <span class="icon-[tabler--edit] size-4 text-success absolute right-2 top-1/2 -translate-y-1/2" title="You can edit this price"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Complimentary --}}
                        @can('comp', App\Models\Booking::class)
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="comp" class="radio radio-primary mt-0.5">
                            <div>
                                <div class="font-medium">{{ $trans['walk_in.complimentary'] ?? 'Complimentary' }}</div>
                                <div class="text-sm text-base-content/60">{{ $trans['walk_in.free_booking'] ?? 'Free booking (no charge)' }}</div>
                            </div>
                        </label>
                        @endcan

                        {{-- Membership/Pack options - can be added via AJAX if needed in future --}}
                        <div id="client-payment-options" class="hidden space-y-3">
                            {{-- Will be populated via AJAX if needed --}}
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
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary">
                            <div>
                                <span class="font-medium">{{ $trans['walk_in.check_in_now'] ?? 'Check in client now' }}</span>
                                <span class="text-sm text-base-content/60 block">{{ $trans['walk_in.mark_arrived'] ?? 'Mark as arrived immediately after booking' }}</span>
                            </div>
                        </label>

                        <div class="form-control mt-4">
                            <label class="label" for="notes"><span class="label-text">{{ $trans['field.notes_optional'] ?? 'Notes (optional)' }}</span></label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="{{ $trans['walk_in.notes_placeholder'] ?? 'Any notes about this booking...' }}"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Promo Code Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--discount-2] size-5 text-warning"></span>
                            <span class="font-semibold">{{ $trans['walk_in.promo_code'] ?? 'Promo Code' }}</span>
                        </div>

                        {{-- Hidden fields for form submission --}}
                        <input type="hidden" name="offer_id" id="offer_id" value="">
                        <input type="hidden" name="promo_code" id="promo_code_hidden" value="">
                        <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                        <input type="hidden" name="price_override_code" id="price_override_code" value="">
                        <input type="hidden" name="price_override_amount" id="price_override_amount" value="">

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

                        {{-- Promo Code Input (always visible) --}}
                        <div id="promo-input-section">
                            <div class="join w-full">
                                <input type="text" id="promo_code_input" placeholder="{{ $trans['walk_in.enter_promo_code'] ?? 'Enter promo code' }}"
                                       class="input input-bordered join-item flex-1 uppercase" maxlength="20">
                                <button type="button" onclick="applyPromoCode()" id="apply-promo-btn"
                                        class="btn btn-primary join-item">
                                    {{ $trans['btn.apply'] ?? 'Apply' }}
                                </button>
                            </div>
                            <p id="promo-error" class="text-error text-sm mt-2 hidden"></p>
                        </div>
                    </div>
                </div>

                {{-- Price Override Card --}}
                @if($canOverridePrice ?? false)
                {{-- Direct price edit for managers/owners with override permission --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                            <span class="font-semibold">Price Override</span>
                            <span class="badge badge-success badge-xs">Authorized</span>
                        </div>

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
                                <p class="text-xs text-base-content/50 mt-1">You have override permission.</p>
                            </div>
                        </div>

                        <p id="override-error" class="text-error text-sm mt-2 hidden"></p>
                    </div>
                </div>
                @elseif($canRequestOverride ?? false)
                {{-- Override request flow for staff without override permission --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                            <span class="font-semibold">Price Override</span>
                        </div>

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
                            <div class="form-control mb-3">
                                <label class="label" for="override_code_input">
                                    <span class="label-text text-xs">Enter code (PO-XXXXX or MY-XXXXX)</span>
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
                @endif

                {{-- Price Override Modals - Only show when feature is enabled --}}
                @if($canRequestOverride ?? false)
                {{-- Price Override Request Modal --}}
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
                                <span class="font-semibold" id="modal-original-price">${{ number_format($slot->getEffectivePrice() ?? 0, 2) }}</span>
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
                                <span class="font-semibold" id="personal-modal-original-price">${{ number_format($slot->getEffectivePrice() ?? 0, 2) }}</span>
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

                {{-- Booking Summary Card --}}
                <div class="card bg-base-100 border border-base-200 sticky top-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $trans['walk_in.booking_summary'] ?? 'Booking Summary' }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.service'] ?? 'Service' }}</span>
                            <span class="font-medium">{{ $slot->servicePlan->name ?? ($trans['field.service'] ?? 'Service') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.date'] ?? 'Date' }}</span>
                            <span class="font-medium">{{ $slot->start_time->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.time'] ?? 'Time' }}</span>
                            <span class="font-medium">{{ $slot->start_time->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['schedule.duration'] ?? 'Duration' }}</span>
                            <span class="font-medium">{{ $slot->duration_minutes }} {{ $trans['common.min'] ?? 'min' }}</span>
                        </div>
                        @if($slot->instructor)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.instructor'] ?? 'Instructor' }}</span>
                            <span class="font-medium">{{ $slot->instructor->name }}</span>
                        </div>
                        @endif
                        @if($slot->location)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.location'] ?? 'Location' }}</span>
                            <span class="font-medium">{{ $slot->location->name }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between" id="price-row">
                            <span class="text-base-content/60">{{ $trans['field.price'] ?? 'Price' }}</span>
                            <span class="font-medium" id="original-price">{{ $slot->formatted_price }}</span>
                        </div>

                        {{-- Discount Row (hidden by default) --}}
                        <div class="flex justify-between text-success hidden" id="discount-row">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--discount-2] size-4"></span>
                                {{ $trans['field.discount'] ?? 'Discount' }}
                            </span>
                            <span id="discount-value">-$0.00</span>
                        </div>

                        {{-- Final Price Row (hidden by default) --}}
                        <div class="flex justify-between font-bold hidden" id="final-price-row">
                            <span>{{ $trans['field.total'] ?? 'Total' }}</span>
                            <span class="text-success" id="final-price">{{ $slot->formatted_price }}</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div id="summary-client" class="hidden">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $trans['field.client'] ?? 'Client' }}</span>
                                <span class="font-medium" id="summary-client-name">--</span>
                            </div>
                        </div>

                        @if($slot->status !== 'available')
                        <div class="alert alert-warning">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <span>{{ $trans['walk_in.slot_status_warning'] ?? 'This slot is' }} {{ $slot->status }}. {{ $trans['walk_in.booking_not_possible'] ?? 'Booking may not be possible.' }}</span>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        @endif

                        @include('components.read-to-client', ['rtcId' => 'service-slot', 'rtcSubmitBtn' => 'submit-btn', 'rtcClass' => 'mb-4'])

                        <button type="submit" class="btn btn-primary btn-block" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $trans['btn.confirm_booking'] ?? 'Confirm Booking' }}
                        </button>

                        <a href="{{ route('service-slots.index') }}" class="btn btn-ghost btn-block">
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
// Form error display functions
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

let selectedClientId = null;

// Client search
const searchInput = document.getElementById('client-search');
let searchTimeout = null;

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
        document.getElementById('search-results').classList.add('hidden');
        document.getElementById('recent-clients').classList.remove('hidden');
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
    document.getElementById('recent-clients').classList.add('hidden');
}

function selectClient(id, firstName, lastName, email, phone, avatarUrl) {
    selectedClientId = id;
    document.getElementById('client-id').value = id;

    const initials = (firstName[0] + lastName[0]).toUpperCase();
    const fullName = firstName + ' ' + lastName;
    const contact = email || phone || 'No contact info';

    // Handle avatar display
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

    document.getElementById('selected-name').textContent = fullName;
    document.getElementById('selected-contact').textContent = contact;
    document.getElementById('summary-client-name').textContent = fullName;

    document.getElementById('selected-client').classList.remove('hidden');
    document.getElementById('summary-client').classList.remove('hidden');
    document.getElementById('recent-clients').classList.add('hidden');
    document.getElementById('search-results').classList.add('hidden');
    document.getElementById('quick-add-form').classList.add('hidden');
    searchInput.closest('.form-control').classList.add('hidden');

    document.getElementById('submit-btn').disabled = false;
}

function clearClient() {
    selectedClientId = null;
    document.getElementById('client-id').value = '';
    document.getElementById('selected-client').classList.add('hidden');
    document.getElementById('summary-client').classList.add('hidden');
    document.getElementById('recent-clients').classList.remove('hidden');
    searchInput.closest('.form-control').classList.remove('hidden');
    searchInput.value = '';
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('client-payment-options').classList.add('hidden');
}

function toggleQuickAdd() {
    const form = document.getElementById('quick-add-form');
    form.classList.toggle('hidden');
}

function createClient() {
    const firstName = document.getElementById('new-first-name').value.trim();
    const lastName = document.getElementById('new-last-name').value.trim();
    const email = document.getElementById('new-email').value.trim();
    const phone = document.getElementById('new-phone').value.trim();

    if (!firstName || !lastName) {
        showFormError('Please enter first and last name');
        return;
    }

    fetch('/walk-in/clients/quick-add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ first_name: firstName, last_name: lastName, email, phone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectClient(data.client.id, data.client.first_name, data.client.last_name, data.client.email, data.client.phone, data.client.avatar_url || null);
        }
    });
}

// Promo code functionality
const originalPrice = {{ $slot->getEffectivePrice() ?? 0 }};
let appliedOfferId = null;
let appliedDiscount = 0;

function applyPromoCode() {
    const codeInput = document.getElementById('promo_code_input');
    const code = codeInput.value.trim().toUpperCase();
    const applyBtn = document.getElementById('apply-promo-btn');
    const errorEl = document.getElementById('promo-error');

    if (!code) {
        showPromoError('Please enter a promo code.');
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
            type: 'services',
            original_price: originalPrice,
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
}

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
    updatePriceDisplay(data.original_price, data.discount_amount, data.final_price);

    // Update manual payment amount if selected
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = data.final_price.toFixed(2);
    }
}

function removePromoCode() {
    appliedOfferId = null;
    appliedDiscount = 0;

    // Clear hidden fields
    document.getElementById('offer_id').value = '';
    document.getElementById('promo_code_hidden').value = '';
    document.getElementById('discount_amount').value = '0';

    // Hide applied offer banner
    document.getElementById('applied-offer').classList.add('hidden');

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

    // Show input section and clear error
    document.getElementById('promo-input-section').classList.remove('hidden');
    document.getElementById('promo-error').classList.add('hidden');

    // Reset price display
    updatePriceDisplay(originalPrice, 0, originalPrice);

    // Reset manual payment amount
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = originalPrice.toFixed(2);
    }
}

function updatePriceDisplay(subtotal, discount, total) {
    const discountRow = document.getElementById('discount-row');
    const finalPriceRow = document.getElementById('final-price-row');
    const originalPriceEl = document.getElementById('original-price');

    if (discount > 0) {
        // Show discount and final price rows
        discountRow.classList.remove('hidden');
        finalPriceRow.classList.remove('hidden');
        document.getElementById('discount-value').textContent = '-$' + discount.toFixed(2);
        document.getElementById('final-price').textContent = '$' + total.toFixed(2);

        // Strike through original price
        originalPriceEl.classList.add('line-through', 'text-base-content/50');
    } else {
        // Hide discount and final price rows
        discountRow.classList.add('hidden');
        finalPriceRow.classList.add('hidden');
        originalPriceEl.classList.remove('line-through', 'text-base-content/50');
    }
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
function applyDirectOverride() {
    const priceInput = document.getElementById('direct-override-price');
    const newPrice = parseFloat(priceInput.value);

    if (!newPrice || newPrice < 0) {
        showDirectOverrideError('Please enter a valid price.');
        return;
    }

    if (newPrice >= originalPrice) {
        showDirectOverrideError('Override price must be less than original price ($' + originalPrice.toFixed(2) + ').');
        return;
    }

    appliedOverridePrice = newPrice;

    // Set hidden fields for form submission
    document.getElementById('price_override_code').value = 'DIRECT';
    document.getElementById('price_override_amount').value = newPrice;

    // Update UI
    document.getElementById('applied-override-code').textContent = 'Direct Override';
    document.getElementById('applied-override-price').textContent = 'Override price: $' + newPrice.toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');

    // Update price display
    const discountAmount = originalPrice - newPrice;
    updatePriceDisplay(originalPrice, discountAmount, newPrice);

    // Update manual payment amount
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = newPrice.toFixed(2);
    }

    // Clear the input
    priceInput.value = '';
    hideDirectOverrideError();
}

function showDirectOverrideError(message) {
    const errorEl = document.getElementById('override-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

function hideDirectOverrideError() {
    const errorEl = document.getElementById('override-error');
    if (errorEl) {
        errorEl.classList.add('hidden');
    }
}

function showOverrideModal() {
    const modal = document.getElementById('override-modal');
    if (!modal) return;

    document.getElementById('override-new-price').value = '';
    document.getElementById('override-discount-code').value = '';
    document.getElementById('override-reason').value = '';
    document.getElementById('override-preview').classList.add('hidden');
    const modalError = document.getElementById('modal-error');
    if (modalError) modalError.classList.add('hidden');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeOverrideModal() {
    const modal = document.getElementById('override-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Calculate discount preview
document.getElementById('override-new-price')?.addEventListener('input', function() {
    const newPrice = parseFloat(this.value) || 0;
    const preview = document.getElementById('override-preview');

    if (newPrice > 0 && newPrice < originalPrice) {
        const discountAmount = originalPrice - newPrice;
        const discountPercent = ((discountAmount / originalPrice) * 100).toFixed(1);

        document.getElementById('preview-discount').textContent = '$' + discountAmount.toFixed(2);
        document.getElementById('preview-percent').textContent = discountPercent + '%';
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

function submitOverrideRequest() {
    const newPrice = parseFloat(document.getElementById('override-new-price').value);
    const discountCode = document.getElementById('override-discount-code').value.trim();
    const reason = document.getElementById('override-reason').value.trim();
    const submitBtn = document.getElementById('submit-override-btn');
    const errorEl = document.getElementById('modal-error');

    if (!newPrice || newPrice <= 0) {
        showOverrideModalError('Please enter a valid new price.');
        return;
    }
    if (newPrice >= originalPrice) {
        showOverrideModalError('New price must be less than the original price.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';
    if (errorEl) errorEl.classList.add('hidden');

    fetch('/price-override/request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            original_price: originalPrice,
            requested_price: newPrice,
            location_id: null,
            client_id: selectedClientId,
            discount_code: discountCode || null,
            reason: reason || null,
            bookable_type: 'App\\Models\\ServiceSlot',
            bookable_id: {{ $slot->id }},
            metadata: {
                service_name: '{{ $slot->servicePlan->name ?? "Service" }}',
                service_date: '{{ $slot->start_time->format("Y-m-d H:i") }}'
            }
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
}

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

    startStatusPolling();
}

function startStatusPolling() {
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

function checkOverrideStatus() {
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
}

function fetchApprovedOverride() {
    const fetchBtn = document.getElementById('fetch-override-btn');
    const messageEl = document.getElementById('fetch-override-message');

    fetchBtn.disabled = true;
    fetchBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
    messageEl.classList.add('hidden');

    fetch('/price-override/fetch-approved', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            bookable_type: 'App\\Models\\ServiceSlot',
            bookable_id: {{ $slot->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        fetchBtn.disabled = false;
        fetchBtn.innerHTML = '<span class="icon-[tabler--download] size-4"></span> Fetch';

        if (data.success && data.data) {
            applyOverrideFromData(data.data);
            messageEl.textContent = 'Approved override found and applied!';
            messageEl.className = 'text-sm mt-2 text-success';
            messageEl.classList.remove('hidden');
        } else {
            messageEl.textContent = data.message || 'No approved override found. You can request one below.';
            messageEl.className = 'text-sm mt-2 text-base-content/60';
            messageEl.classList.remove('hidden');
        }
    })
    .catch(error => {
        fetchBtn.disabled = false;
        fetchBtn.innerHTML = '<span class="icon-[tabler--download] size-4"></span> Fetch';
        messageEl.textContent = 'Error checking for approved overrides.';
        messageEl.className = 'text-sm mt-2 text-error';
        messageEl.classList.remove('hidden');
        console.error('Fetch override error:', error);
    });
}

// Personal override state
let personalOverrideCode = null;
let personalOverrideSupervisor = null;

function verifyOverrideCode() {
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
}

function showPersonalOverrideModal(code, supervisorName) {
    document.getElementById('personal-supervisor-name').textContent = supervisorName;
    document.getElementById('personal-supervisor-code').textContent = code;
    document.getElementById('personal-modal-original-price').textContent = '$' + originalPrice.toFixed(2);
    document.getElementById('personal-override-new-price').value = '';
    document.getElementById('personal-override-preview').classList.add('hidden');
    document.getElementById('personal-modal-error').classList.add('hidden');
    document.getElementById('personal-override-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('personal-override-new-price').focus(), 100);
}

function closePersonalOverrideModal() {
    document.getElementById('personal-override-modal').classList.add('hidden');
}

function updatePersonalOverridePreview() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value) || 0;
    const preview = document.getElementById('personal-override-preview');
    if (newPrice > 0 && newPrice < originalPrice) {
        const discount = originalPrice - newPrice;
        document.getElementById('personal-preview-discount').textContent = '$' + discount.toFixed(2);
        document.getElementById('personal-preview-percent').textContent = ((discount / originalPrice) * 100).toFixed(1) + '%';
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function applyPersonalOverride() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
    const errorEl = document.getElementById('personal-modal-error');
    if (!newPrice || newPrice < 0) { errorEl.textContent = 'Enter a valid price.'; errorEl.classList.remove('hidden'); return; }
    if (newPrice >= originalPrice) { errorEl.textContent = 'Price must be less than $' + originalPrice.toFixed(2); errorEl.classList.remove('hidden'); return; }

    appliedOverridePrice = newPrice;
    pendingOverrideCode = personalOverrideCode;
    document.getElementById('price_override_code').value = personalOverrideCode;
    document.getElementById('price_override_amount').value = newPrice;
    document.getElementById('applied-override-code').textContent = 'Supervised by: ' + personalOverrideSupervisor + ' (' + personalOverrideCode + ')';
    document.getElementById('applied-override-price').textContent = 'Override price: $' + newPrice.toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');

    const discountAmount = originalPrice - newPrice;
    updatePriceDisplay(originalPrice, discountAmount, newPrice);

    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = newPrice.toFixed(2);
    }

    closePersonalOverrideModal();
    document.getElementById('override_code_input').value = '';
}

function applyOverrideFromData(data) {
    pendingOverrideCode = data.confirmation_code;
    appliedOverridePrice = parseFloat(data.requested_price);

    document.getElementById('price_override_code').value = data.confirmation_code;
    document.getElementById('price_override_amount').value = data.requested_price;

    document.getElementById('applied-override-code').textContent = 'Code: ' + data.confirmation_code;
    document.getElementById('applied-override-price').textContent = 'Override price: $' + parseFloat(data.requested_price).toFixed(2);

    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');
    document.getElementById('override-pending').classList.add('hidden');
    const overrideError = document.getElementById('override-error');
    if (overrideError) overrideError.classList.add('hidden');

    const discountAmount = originalPrice - appliedOverridePrice;
    updatePriceDisplay(originalPrice, discountAmount, appliedOverridePrice);

    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = appliedOverridePrice.toFixed(2);
    }

    if (appliedOfferId) {
        removePromoCode();
    }
}

function removeOverride() {
    pendingOverrideId = null;
    pendingOverrideCode = null;
    appliedOverridePrice = null;
    stopStatusPolling();

    document.getElementById('price_override_code').value = '';
    document.getElementById('price_override_amount').value = '';

    document.getElementById('applied-override').classList.add('hidden');
    document.getElementById('override-input-section').classList.remove('hidden');
    const pendingEl = document.getElementById('override-pending');
    if (pendingEl) pendingEl.classList.add('hidden');
    const codeInput = document.getElementById('override_code_input');
    if (codeInput) codeInput.value = '';
    const directInput = document.getElementById('direct-override-price');
    if (directInput) directInput.value = '';

    updatePriceDisplay(originalPrice, 0, originalPrice);

    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = originalPrice.toFixed(2);
    }
}

function resetOverrideUI() {
    pendingOverrideId = null;
    pendingOverrideCode = null;
    stopStatusPolling();

    document.getElementById('override-input-section').classList.remove('hidden');
    document.getElementById('override-pending').classList.add('hidden');
    document.getElementById('override_code_input').value = '';
}

function cancelOverrideRequest() {
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
        showOverrideError('Unable to cancel request.');
        console.error('Cancel error:', error);
    });
}

function showOverrideError(message) {
    const errorEl = document.getElementById('override-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
        setTimeout(() => errorEl.classList.add('hidden'), 5000);
    }
}

document.getElementById('override_code_input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        verifyOverrideCode();
    }
});

window.addEventListener('beforeunload', function() {
    stopStatusPolling();
});
</script>
@endpush
@endsection
