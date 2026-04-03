@extends('layouts.dashboard')

@section('title', $trans['page.new_booking'] ?? 'New Booking')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['page.new_booking'] ?? 'New Booking' }}</h1>
            <p class="text-base-content/60">{{ $trans['walk_in.book_client_class'] ?? 'Book a client for this class' }}</p>
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

    {{-- Session Info Card --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--yoga] size-7 text-primary"></span>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold">{{ $session->display_title }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar] size-4"></span>
                            {{ $session->start_time->format('l, M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                        </span>
                        @if($session->primaryInstructor)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $session->primaryInstructor->name }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold {{ $spotsRemaining > 0 ? 'text-success' : 'text-error' }}">
                        {{ $spotsRemaining }}
                    </div>
                    <div class="text-sm text-base-content/60">{{ $trans['walk_in.spots_left'] ?? 'spots left' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Form --}}
    <form action="{{ route('walk-in.class.book', $session) }}" method="POST" id="walk-in-form">
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

                @php
                    // Use controller-passed permission flags
                    $userCanApproveOverride = $canOverridePrice ?? false;
                @endphp

                {{-- Pricing & Payment Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--receipt] size-5 mr-2"></span>
                            {{ $trans['walk_in.pricing_payment'] ?? 'Pricing & Payment' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="divider my-2 text-xs text-base-content/50">{{ $trans['walk_in.payment_method'] ?? 'PAYMENT METHOD' }}</div>

                        <div class="space-y-3">
                            @php
                                $priceEditable = $userCanApproveOverride || (!($canOverridePrice ?? false) && !($canRequestOverride ?? false));
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
                                                   placeholder="{{ $trans['field.amount'] ?? 'Amount' }}" value="" id="manual-price-input">
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

                            {{-- Membership/Pack options will be loaded dynamically based on selected client --}}
                            <div id="client-payment-options" class="hidden space-y-3">
                                {{-- Will be populated via AJAX --}}
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

                {{-- Price Override Card - Only for staff WITHOUT override permission AND feature is enabled --}}
                @if($canRequestOverride ?? false)
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                            <span class="font-semibold">{{ $trans['walk_in.price_override'] ?? 'Price Override' }}</span>
                        </div>

                        {{-- Hidden fields for price override --}}
                        <input type="hidden" name="price_override_code" id="price_override_code" value="">
                        <input type="hidden" name="price_override_amount" id="price_override_amount" value="">

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

                        {{-- Override Input Section - STAFF WITHOUT OVERRIDE PERMISSION: Full request flow --}}
                        <div id="override-input-section">
                            <div class="form-control mb-3">
                                <label class="label" for="override_code_input">
                                    <span class="label-text text-xs">Enter code or fetch your approved override</span>
                                </label>
                                <div class="join w-full">
                                    <input type="text" id="override_code_input" placeholder="PO-XXXXX or MY-XXXXX"
                                           class="input input-bordered input-sm join-item flex-1 uppercase" maxlength="10">
                                    <button type="button" onclick="verifyOverrideCode()" id="verify-override-btn"
                                            class="btn btn-sm btn-outline join-item">
                                        {{ $trans['btn.verify'] ?? 'Verify' }}
                                    </button>
                                    <button type="button" onclick="fetchApprovedOverride()" id="fetch-override-btn"
                                            class="btn btn-sm btn-success join-item">
                                        <span class="icon-[tabler--download] size-4"></span>
                                        Fetch
                                    </button>
                                </div>
                                <p id="fetch-override-message" class="text-sm mt-2 hidden"></p>
                            </div>

                            <div class="divider text-xs my-2">OR</div>

                            {{-- Request New Override --}}
                            <button type="button" onclick="showOverrideModal()" class="btn btn-outline btn-primary btn-sm btn-block">
                                <span class="icon-[tabler--send] size-4"></span>
                                {{ $trans['btn.request_override'] ?? 'Request New Override' }}
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

                {{-- Price Override Request Modal (div-based) - Only for staff without permission --}}
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
                                <span class="font-semibold" id="modal-original-price">${{ number_format($session->price ?? $session->classPlan->default_price ?? 0, 2) }}</span>
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

                {{-- Personal Code Override Modal (when manager enters their code for staff) --}}
                <div id="personal-override-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
                    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closePersonalOverrideModal()"></div>
                    <div class="modal-box fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                        <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                        <h3 class="font-bold text-lg mb-4">
                            <span class="icon-[tabler--shield-check] size-5 mr-2 text-success"></span>
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
                                <span class="font-semibold" id="personal-original-price">${{ number_format($session->price ?? $session->classPlan->default_price ?? 0, 2) }}</span>
                            </div>

                            <div class="form-control">
                                <label class="label" for="personal-new-price">
                                    <span class="label-text">New Price *</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                    <input type="number" id="personal-new-price" step="0.01" min="0"
                                           class="input input-bordered w-full pl-8" placeholder="0.00">
                                </div>
                            </div>

                            {{-- Discount Preview --}}
                            <div id="personal-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg">
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
                            <button type="button" onclick="applyPersonalOverride()" id="apply-personal-btn" class="btn btn-success">
                                <span class="icon-[tabler--check] size-4"></span>
                                Apply Override
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
                            <span class="text-base-content/60">{{ $trans['field.class'] ?? 'Class' }}</span>
                            <span class="font-medium">{{ $session->display_title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.date'] ?? 'Date' }}</span>
                            <span class="font-medium">{{ $session->start_time->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.time'] ?? 'Time' }}</span>
                            <span class="font-medium">{{ $session->start_time->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between" id="price-row">
                            <span class="text-base-content/60">{{ $trans['field.price'] ?? 'Price' }}</span>
                            <span class="font-medium" id="original-price">${{ number_format($session->price ?? $session->classPlan->default_price ?? 0, 2) }}</span>
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
                            <span class="text-success" id="final-price">${{ number_format($session->price ?? $session->classPlan->default_price ?? 0, 2) }}</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div id="summary-client" class="hidden">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $trans['field.client'] ?? 'Client' }}</span>
                                <span class="font-medium" id="summary-client-name">--</span>
                            </div>
                        </div>

                        @if($spotsRemaining <= 0)
                        <div class="alert alert-warning">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <span>{{ $trans['walk_in.class_at_capacity'] ?? 'This class is at capacity. Booking will override limit.' }}</span>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        @endif

                        @include('components.read-to-client', ['rtcId' => 'class-session', 'rtcSubmitBtn' => 'submit-btn', 'rtcClass' => 'mb-4'])

                        <button type="submit" class="btn btn-primary btn-block" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $trans['btn.confirm_booking'] ?? 'Confirm Booking' }}
                        </button>

                        <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-block">
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
    console.log('selectClient called with:', id, firstName, lastName);
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

    // Load client payment options
    loadPaymentOptions(id);
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

function loadPaymentOptions(clientId) {
    const classPlanId = {{ $session->class_plan_id }};
    console.log('Loading payment options for client:', clientId, 'class plan:', classPlanId);

    fetch(`/walk-in/payment-methods/${clientId}?class_plan_id=${classPlanId}`)
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok: ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            console.log('Payment options response:', data);
            const container = document.getElementById('client-payment-options');
            let html = '';

            if (data.membership) {
                html += `
                    <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="payment_method" value="membership" class="radio radio-primary mt-0.5">
                        <div>
                            <div class="font-medium">Use Membership</div>
                            <div class="text-sm text-base-content/60">${data.membership.name} - ${data.membership.credits_remaining} credits remaining</div>
                        </div>
                    </label>
                `;
            }

            if (data.packs && data.packs.length > 0) {
                data.packs.forEach(pack => {
                    let statusText = '';
                    if (pack.is_pending_activation) {
                        statusText = '<span class="badge badge-warning badge-xs ml-2">Will activate on booking</span>';
                    } else if (pack.expires_at) {
                        const expiringSoonClass = pack.is_expiring_soon ? 'text-warning' : 'text-base-content/60';
                        statusText = `<span class="${expiringSoonClass}"> (expires ${pack.expires_at})</span>`;
                    }
                    html += `
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="pack" class="radio radio-primary mt-0.5" data-pack-id="${pack.id}">
                            <div>
                                <div class="font-medium">Use Class Pass ${statusText}</div>
                                <div class="text-sm text-base-content/60">${pack.name} - ${pack.classes_remaining} credits remaining</div>
                            </div>
                            <input type="hidden" name="pack_id" value="${pack.id}" disabled>
                        </label>
                    `;
                });
            }

            if (html) {
                container.innerHTML = html;
                container.classList.remove('hidden');
                console.log('Showing payment options container');

                // Handle pack selection
                container.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        container.querySelectorAll('input[name="pack_id"]').forEach(i => i.disabled = true);
                        if (this.value === 'pack') {
                            this.closest('label').querySelector('input[name="pack_id"]').disabled = false;
                        }
                    });
                });
            } else {
                console.log('No additional payment options available');
            }
        })
        .catch(error => {
            console.error('Error loading payment options:', error);
        });
}

// Promo code functionality
let currentSelectedPrice = {{ $session->price ?? $session->classPlan->default_price ?? 0 }};
let originalPrice = currentSelectedPrice;
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
            type: 'classes',
            original_price: currentSelectedPrice,
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
    updatePriceDisplay(currentSelectedPrice, 0, currentSelectedPrice);

    // Reset manual payment amount
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = currentSelectedPrice.toFixed(2);
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

// ==================== Price Override Functions (only for staff without permission AND feature enabled) ====================
@if($canRequestOverride ?? false)
let pendingOverrideId = null;
let pendingOverrideCode = null;
let appliedOverridePrice = null;

function fetchApprovedOverride() {
    const fetchBtn = document.getElementById('fetch-override-btn');
    const messageEl = document.getElementById('fetch-override-message');
    const sessionId = {{ $session->id }};

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
            bookable_type: 'App\\Models\\ClassSession',
            bookable_id: sessionId
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
            messageEl.textContent = data.message || 'No approved override found.';
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

function showOverrideModal() {
    const modal = document.getElementById('override-modal');
    document.getElementById('override-new-price').value = '';
    document.getElementById('override-discount-code').value = '';
    document.getElementById('override-reason').value = '';
    document.getElementById('override-preview').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');

    // Show div-based modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeOverrideModal() {
    const modal = document.getElementById('override-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Calculate discount preview when price changes
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

    // Validation
    if (!newPrice || newPrice <= 0) {
        showModalError('Please enter a valid new price.');
        return;
    }
    if (newPrice >= originalPrice) {
        showModalError('New price must be less than the original price.');
        return;
    }

    // Show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';
    errorEl.classList.add('hidden');

    // Get location if available
    const locationId = document.querySelector('input[name="location_id"]')?.value || null;

    // Make request
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
            location_id: locationId,
            client_id: selectedClientId,
            discount_code: discountCode || null,
            reason: reason || null,
            bookable_type: 'App\\Models\\ClassSession',
            bookable_id: {{ $session->id }},
            metadata: {
                class_name: '{{ $session->display_title }}',
                class_date: '{{ $session->start_time->format("Y-m-d H:i") }}'
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
            showModalError(data.message || 'Failed to submit request.');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send Request';
        showModalError('An error occurred. Please try again.');
        console.error('Override request error:', error);
    });
}

function showModalError(message) {
    const errorEl = document.getElementById('modal-error');
    errorEl.textContent = message;
    errorEl.classList.remove('hidden');
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

let statusPollInterval = null;

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

// Personal override modal variables
let personalOverrideCode = null;
let personalOverrideAuthorizer = null;

function verifyOverrideCode() {
    const code = document.getElementById('override_code_input').value.trim().toUpperCase();
    const verifyBtn = document.getElementById('verify-override-btn');
    const messageEl = document.getElementById('fetch-override-message');

    if (!code) {
        showOverrideError('Please enter a confirmation code.');
        return;
    }

    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
    messageEl.classList.add('hidden');

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

        if (data.success || data.valid) {
            // Check if this is a personal code (MY-XXXXX) - manager entered their code
            if (data.is_personal_code) {
                // Personal code verified - show the personal override modal
                personalOverrideCode = data.code;
                personalOverrideAuthorizer = data.data?.authorized_by?.name || 'Manager';

                showPersonalOverrideModal(personalOverrideCode, personalOverrideAuthorizer);
            } else if (data.data?.is_approved) {
                // Regular approved override request code - apply the preset price
                applyOverrideFromData(data.data);
            } else if (data.data?.is_pending) {
                // Pending override request
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
    const modal = document.getElementById('personal-override-modal');
    document.getElementById('personal-supervisor-name').textContent = supervisorName;
    document.getElementById('personal-supervisor-code').textContent = code;
    document.getElementById('personal-new-price').value = '';
    document.getElementById('personal-preview').classList.add('hidden');
    document.getElementById('personal-modal-error').classList.add('hidden');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus on price input
    setTimeout(() => {
        document.getElementById('personal-new-price').focus();
    }, 100);
}

function closePersonalOverrideModal() {
    const modal = document.getElementById('personal-override-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    personalOverrideCode = null;
    personalOverrideAuthorizer = null;
}

// Calculate discount preview for personal override modal
document.getElementById('personal-new-price')?.addEventListener('input', function() {
    const newPrice = parseFloat(this.value) || 0;
    const preview = document.getElementById('personal-preview');

    if (newPrice > 0 && newPrice < originalPrice) {
        const discountAmount = originalPrice - newPrice;
        const discountPercent = ((discountAmount / originalPrice) * 100).toFixed(1);

        document.getElementById('personal-preview-discount').textContent = '$' + discountAmount.toFixed(2);
        document.getElementById('personal-preview-percent').textContent = discountPercent + '%';
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

function applyPersonalOverride() {
    const newPrice = parseFloat(document.getElementById('personal-new-price').value);
    const errorEl = document.getElementById('personal-modal-error');
    const applyBtn = document.getElementById('apply-personal-btn');

    if (!newPrice || newPrice < 0) {
        errorEl.textContent = 'Please enter a valid price.';
        errorEl.classList.remove('hidden');
        return;
    }

    if (newPrice >= originalPrice) {
        errorEl.textContent = 'New price must be less than original price ($' + originalPrice.toFixed(2) + ').';
        errorEl.classList.remove('hidden');
        return;
    }

    if (!personalOverrideCode) {
        errorEl.textContent = 'No personal code verified.';
        errorEl.classList.remove('hidden');
        return;
    }

    errorEl.classList.add('hidden');
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    // Apply the override
    appliedOverridePrice = newPrice;

    // Update hidden fields
    document.getElementById('price_override_code').value = personalOverrideCode;
    document.getElementById('price_override_amount').value = newPrice;

    // Update applied override display
    document.getElementById('applied-override-code').textContent = 'Supervised by ' + personalOverrideAuthorizer + ' (' + personalOverrideCode + ')';
    document.getElementById('applied-override-price').textContent = 'Override price: $' + newPrice.toFixed(2);

    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');
    document.getElementById('override-error').classList.add('hidden');

    // Update price display
    const discountAmount = originalPrice - newPrice;
    updatePriceDisplay(originalPrice, discountAmount, newPrice);

    // Update manual payment input
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = newPrice.toFixed(2);
    }

    // Clear promo code if any
    if (appliedOfferId) {
        removePromoCode();
    }

    // Close modal
    closePersonalOverrideModal();

    applyBtn.disabled = false;
    applyBtn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Apply Override';

    // Clear the input
    document.getElementById('override_code_input').value = '';
}

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
    document.getElementById('override-error').classList.add('hidden');

    // Update price display
    const discountAmount = originalPrice - appliedOverridePrice;
    updatePriceDisplay(originalPrice, discountAmount, appliedOverridePrice);

    // Update manual payment input
    const manualAmountInput = document.querySelector('input[name="price_paid"]');
    if (manualAmountInput) {
        manualAmountInput.value = appliedOverridePrice.toFixed(2);
    }

    // Clear promo code if any (override takes precedence)
    if (appliedOfferId) {
        removePromoCode();
    }
}

function removeOverride() {
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
    document.getElementById('override-pending').classList.add('hidden');
    document.getElementById('override_code_input').value = '';
    document.getElementById('fetch-override-message').classList.add('hidden');

    // Show hidden elements (request button and divider)
    const requestBtn = document.querySelector('#override-input-section .btn-outline');
    if (requestBtn) requestBtn.classList.remove('hidden');
    const divider = document.querySelector('#override-input-section .divider');
    if (divider) divider.classList.remove('hidden');

    // Reset price display
    updatePriceDisplay(originalPrice, 0, originalPrice);

    // Reset manual payment input
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
        showOverrideError('Unable to cancel request. Please try again.');
        console.error('Cancel error:', error);
    });
}

function showOverrideError(message) {
    const errorEl = document.getElementById('override-error');
    errorEl.textContent = message;
    errorEl.classList.remove('hidden');
    setTimeout(() => {
        errorEl.classList.add('hidden');
    }, 5000);
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
@endif
</script>
@endpush
@endsection
