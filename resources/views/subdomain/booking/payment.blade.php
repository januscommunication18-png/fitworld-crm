@extends('layouts.subdomain')

@section('title', 'Payment — ' . $host->studio_name)

@section('content')
@php
    $item = $bookingState['selected_item'] ?? [];
    $contact = $bookingState['contact_info'] ?? [];
    $currencySymbol = $item['currency_symbol'] ?? \App\Models\MembershipPlan::getCurrencySymbol($item['currency'] ?? $host->default_currency ?? 'USD');
@endphp

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
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
                    <span class="flex items-center gap-1.5 text-success">
                        <span class="w-6 h-6 rounded-full bg-success text-success-content flex items-center justify-center text-xs">
                            <span class="icon-[tabler--check] size-4"></span>
                        </span>
                        Details
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-primary font-medium">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">2</span>
                        Payment
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-base-content/50">
                        <span class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-xs font-bold">3</span>
                        Done
                    </span>
                </div>

                {{-- Back --}}
                <a href="{{ route('booking.contact', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    <span class="hidden sm:inline">Back</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-6 md:py-10">
        <div class="container-fixed">
            <div class="max-w-4xl mx-auto">

                {{-- Booking Summary Card --}}
                <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-primary/20 mb-6">
                    <div class="card-body py-4 md:py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-primary/20 flex items-center justify-center shrink-0">
                                @if(($item['type'] ?? '') === 'class_session')
                                    <span class="icon-[tabler--yoga] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'service_slot')
                                    <span class="icon-[tabler--sparkles] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'membership_plan')
                                    <span class="icon-[tabler--id-badge-2] size-7 text-primary"></span>
                                @else
                                    <span class="icon-[tabler--calendar-check] size-7 text-primary"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-xl font-bold text-base-content">{{ $item['name'] ?? 'Your Selection' }}</h2>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-base-content/70">
                                    @if(($item['type'] ?? '') === 'membership_plan')
                                        {{-- Membership Plan Info --}}
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--id-badge-2] size-4"></span>
                                            Membership Plan
                                        </span>
                                        @if(!empty($item['membership_type']) && $item['membership_type'] === 'unlimited')
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--infinity] size-4"></span>
                                                Unlimited Classes
                                            </span>
                                        @elseif(!empty($item['credits_per_cycle']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                {{ $item['credits_per_cycle'] }} Classes {{ $item['billing_period'] ?? '' }}
                                            </span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'class_pack')
                                        {{-- Class Pack Info --}}
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--ticket] size-4"></span>
                                            Class Pack
                                        </span>
                                        @if(!empty($item['class_count']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                {{ $item['class_count'] }} classes
                                            </span>
                                        @endif
                                    @else
                                        {{-- Class/Service Booking Info --}}
                                        @if(!empty($item['datetime']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--calendar] size-4"></span>
                                                {{ $item['datetime'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['instructor']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--user] size-4"></span>
                                                {{ $item['instructor'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['location']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--map-pin] size-4"></span>
                                                {{ $item['location'] }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                @if(!empty($item['using_membership']))
                                    <span class="text-lg font-bold text-success">Included</span>
                                    <div class="badge badge-success badge-sm mt-1 gap-1">
                                        <span class="icon-[tabler--id-badge-2] size-3"></span>
                                        Membership
                                    </div>
                                @else
                                    <span class="text-2xl font-bold text-primary">{{ $currencySymbol }}{{ number_format($item['price'] ?? 0, 2) }}</span>
                                    @if(($item['type'] ?? '') === 'membership_plan' && !empty($item['billing_period']))
                                        <div class="text-sm text-base-content/60">{{ $item['billing_period'] }}</div>
                                    @endif
                                @endif
                                @if(!empty($item['is_waitlist']))
                                    <div class="badge badge-warning badge-sm mt-1">Waitlist</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Membership Info Banner --}}
                @if(!empty($usingMembership))
                <div class="alert bg-success/10 text-success border-success/20 mb-6">
                    <span class="icon-[tabler--id-badge-2] size-5"></span>
                    <div>
                        <h4 class="font-semibold">Using Your Membership</h4>
                        <p class="text-sm opacity-80">This class is included in your <strong>{{ $item['membership_name'] ?? 'membership' }}</strong>. No payment required.</p>
                    </div>
                </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error mb-6">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form id="payment-form" action="{{ route('booking.payment.process', ['subdomain' => $host->subdomain]) }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        {{-- Payment Options --}}
                        <div class="lg:col-span-3 space-y-6">

                            {{-- Promo Code Section --}}
                            @if(empty($usingMembership) && ($item['price'] ?? 0) > 0)
                            <div class="card bg-base-100 shadow-lg border border-base-200">
                                <div class="card-body py-5">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="icon-[tabler--discount-2] size-5 text-warning"></span>
                                        <span class="font-medium">Promo Code</span>
                                    </div>

                                    {{-- Hidden fields for form submission --}}
                                    <input type="hidden" name="offer_id" id="offer_id" value="{{ $autoAppliedOffer['offer']->id ?? '' }}">
                                    <input type="hidden" name="promo_code" id="promo_code_hidden" value="{{ $prefilledPromoCode ?? '' }}">
                                    <input type="hidden" name="discount_amount" id="discount_amount" value="{{ $autoAppliedOffer['discount_amount'] ?? 0 }}">

                                    {{-- Applied Offer Display (shown when a code is applied) --}}
                                    <div id="applied-offer" class="{{ empty($autoAppliedOffer) ? 'hidden' : '' }}">
                                        <div class="alert bg-success/10 border-success/20 mb-3">
                                            <span class="icon-[tabler--discount-check] size-5 text-success"></span>
                                            <div class="flex-1">
                                                <span class="font-semibold text-success" id="applied-offer-name">{{ $autoAppliedOffer['offer']->name ?? '' }}</span>
                                                <p class="text-sm text-success/80" id="applied-offer-discount">{{ !empty($autoAppliedOffer) ? $autoAppliedOffer['offer']->getFormattedDiscount() . ' applied!' : '' }}</p>
                                            </div>
                                            <button type="button" onclick="removePromoCode()" class="btn btn-ghost btn-xs btn-circle">
                                                <span class="icon-[tabler--x] size-4"></span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Promo Code Input (always in DOM) --}}
                                    <div id="promo-input-section">
                                        <div class="join w-full">
                                            <input type="text" id="promo_code_input" placeholder="Enter promo code"
                                                   value="{{ $prefilledPromoCode ?? $autoAppliedOffer['offer']->code ?? '' }}"
                                                   class="input input-bordered join-item flex-1 uppercase {{ !empty($autoAppliedOffer) ? 'bg-base-200' : '' }}"
                                                   maxlength="20"
                                                   {{ !empty($autoAppliedOffer) ? 'readonly' : '' }}>
                                            <button type="button" onclick="applyPromoCode()" id="apply-promo-btn"
                                                    class="btn join-item {{ !empty($autoAppliedOffer) ? 'btn-success' : 'btn-primary' }}"
                                                    {{ !empty($autoAppliedOffer) ? 'disabled' : '' }}>
                                                @if(!empty($autoAppliedOffer))
                                                    <span class="icon-[tabler--check] size-4"></span>
                                                    Applied
                                                @else
                                                    Apply
                                                @endif
                                            </button>
                                        </div>
                                        <p id="promo-error" class="text-error text-sm mt-2 hidden"></p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Terms Agreement --}}
                            @if($termsUrl)
                            <div class="card bg-base-100 shadow-lg border border-base-200">
                                <div class="card-body py-5">
                                    <label class="flex items-start gap-4 cursor-pointer group">
                                        <div class="pt-0.5">
                                            <input type="checkbox" name="terms_accepted" value="1" required
                                                   class="checkbox checkbox-primary checkbox-sm">
                                        </div>
                                        <div>
                                            <span class="font-medium group-hover:text-primary transition-colors">I agree to the terms and conditions</span>
                                            <p class="text-sm text-base-content/60 mt-1">
                                                By checking this box, you agree to our
                                                <a href="{{ $termsUrl }}" target="_blank" class="link link-primary font-medium">
                                                    Terms & Conditions
                                                </a>
                                                and cancellation policy.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endif

                            {{-- Payment Methods --}}
                            <div class="card bg-base-100 shadow-xl">
                                <div class="card-body">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="w-10 h-10 rounded-xl {{ !empty($usingMembership) ? 'bg-success/10' : 'bg-primary/10' }} flex items-center justify-center">
                                            <span class="icon-[tabler--{{ !empty($usingMembership) ? 'id-badge-2' : 'credit-card' }}] size-5 {{ !empty($usingMembership) ? 'text-success' : 'text-primary' }}"></span>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold">{{ !empty($usingMembership) ? 'Confirm Membership' : 'Payment Method' }}</h3>
                                            <p class="text-sm text-base-content/60">{{ !empty($usingMembership) ? 'Your membership covers this class' : 'Choose how you\'d like to pay' }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach($paymentMethods as $index => $method)
                                        <label class="payment-option flex items-center gap-4 p-4 rounded-xl border-2 {{ !empty($method['is_membership']) ? 'border-success bg-success/5' : 'border-base-200' }} cursor-pointer hover:border-{{ !empty($method['is_membership']) ? 'success' : 'primary' }}/50 hover:bg-{{ !empty($method['is_membership']) ? 'success' : 'primary' }}/5 transition-all duration-200 has-[:checked]:border-{{ !empty($method['is_membership']) ? 'success' : 'primary' }} has-[:checked]:bg-{{ !empty($method['is_membership']) ? 'success' : 'primary' }}/10 has-[:checked]:shadow-md">
                                            <input type="radio" name="payment_method" value="{{ $method['id'] }}"
                                                   {{ $index === 0 ? 'checked' : '' }}
                                                   class="radio {{ !empty($method['is_membership']) ? 'radio-success' : 'radio-primary' }}">
                                            <div class="w-12 h-12 rounded-xl {{ !empty($method['is_membership']) ? 'bg-success/20' : 'bg-base-200' }} flex items-center justify-center shrink-0">
                                                <span class="icon-[tabler--{{ $method['icon'] }}] size-6 {{ !empty($method['is_membership']) ? 'text-success' : 'text-base-content/70' }}"></span>
                                            </div>
                                            <div class="flex-1">
                                                <span class="font-semibold text-base">{{ $method['name'] }}</span>
                                                @if($method['description'])
                                                <p class="text-sm text-base-content/60 mt-0.5">{{ $method['description'] }}</p>
                                                @endif
                                            </div>
                                            @if($method['id'] === 'stripe')
                                            <div class="badge badge-ghost badge-sm">Secure</div>
                                            @elseif(!empty($method['is_membership']))
                                            <div class="badge badge-success badge-sm">Free</div>
                                            @endif
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Complete Booking Button --}}
                            <div class="card bg-base-100 shadow-xl">
                                <div class="card-body">
                                    @if(!empty($usingMembership))
                                    <button type="submit" class="btn btn-success btn-lg w-full gap-2">
                                        <span class="icon-[tabler--check] size-5"></span>
                                        Confirm Booking
                                        <span class="ml-auto badge badge-success-content">Using Membership</span>
                                    </button>
                                    <p class="text-center text-xs text-base-content/50 mt-3">
                                        <span class="icon-[tabler--id-badge-2] size-4 inline-block align-text-bottom mr-1"></span>
                                        One credit will be deducted from your membership
                                    </p>
                                    @else
                                    <button type="submit" class="btn btn-primary btn-lg w-full gap-2" id="submit-btn">
                                        <span class="icon-[tabler--lock] size-5"></span>
                                        Complete Booking
                                        <span class="ml-auto font-bold" id="btn-price">{{ $currencySymbol }}{{ number_format($item['price'] ?? 0, 2) }}</span>
                                    </button>
                                    <p class="text-center text-xs text-base-content/50 mt-3">
                                        <span class="icon-[tabler--shield-check] size-4 inline-block align-text-bottom mr-1"></span>
                                        Secure checkout powered by SSL encryption
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Order Summary Sidebar --}}
                        <div class="lg:col-span-2 space-y-4">
                            {{-- Summary Card --}}
                            <div class="card bg-base-100 shadow-xl sticky top-24">
                                <div class="card-body">
                                    <h3 class="font-bold text-lg mb-4">Order Summary</h3>

                                    {{-- Item Details --}}
                                    <div class="space-y-3 text-sm">
                                        <div class="flex justify-between items-start">
                                            <span class="text-base-content/60">Item</span>
                                            <span class="font-medium text-right max-w-[60%]">{{ $item['name'] ?? 'Not selected' }}</span>
                                        </div>

                                        @if(!empty($item['datetime']))
                                        <div class="flex justify-between">
                                            <span class="text-base-content/60">When</span>
                                            <span class="text-right">{{ $item['datetime'] }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="divider my-3"></div>

                                    {{-- Contact Info --}}
                                    <div class="bg-base-200/50 rounded-xl p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                                            <span class="font-medium text-sm">{{ $contact['first_name'] ?? '' }} {{ $contact['last_name'] ?? '' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                                            <span class="icon-[tabler--mail] size-4"></span>
                                            <span>{{ $contact['email'] ?? '' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-base-content/60 mt-1">
                                            <span class="icon-[tabler--phone] size-4"></span>
                                            <span>{{ $contact['phone'] ?? '' }}</span>
                                        </div>
                                    </div>

                                    <div class="divider my-3"></div>

                                    {{-- Subtotal --}}
                                    <div class="flex justify-between items-center" id="subtotal-row">
                                        <span class="text-base-content/70">Subtotal</span>
                                        <span id="subtotal-amount">{{ $currencySymbol }}{{ number_format($item['price'] ?? 0, 2) }}</span>
                                    </div>

                                    {{-- Discount Row (hidden by default) --}}
                                    <div class="flex justify-between items-center text-success hidden" id="discount-row">
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--discount-2] size-4"></span>
                                            <span id="discount-label">Discount</span>
                                        </span>
                                        <span id="discount-value">-{{ $currencySymbol }}0.00</span>
                                    </div>

                                    <div class="divider my-2"></div>

                                    {{-- Total --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold">Total</span>
                                        @if(!empty($item['using_membership']))
                                            <div class="text-right">
                                                @if(!empty($item['original_price']) && $item['original_price'] > 0)
                                                <span class="text-sm text-base-content/50 line-through">{{ $currencySymbol }}{{ number_format($item['original_price'], 2) }}</span>
                                                @endif
                                                <span class="text-2xl font-bold text-success">{{ $currencySymbol }}0.00</span>
                                            </div>
                                        @else
                                            <div class="text-right" id="total-display">
                                                <span class="text-sm text-base-content/50 line-through hidden" id="original-price-display"></span>
                                                <span class="text-2xl font-bold text-primary" id="final-price-display">{{ $currencySymbol }}{{ number_format($item['price'] ?? 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($item['using_membership']))
                                    <div class="alert bg-success/10 text-success py-2 px-3 mt-3 border-success/20">
                                        <span class="icon-[tabler--id-badge-2] size-4"></span>
                                        <span class="text-sm">Included in {{ $item['membership_name'] ?? 'membership' }}</span>
                                    </div>
                                    @endif

                                    @if(!empty($item['is_waitlist']))
                                    <div class="alert alert-warning py-2 px-3 mt-3">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        <span class="text-sm">You'll be added to the waitlist</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Trust Badges --}}
                            <div class="hidden lg:block space-y-3">
                                <div class="flex items-center gap-3 p-3 bg-base-100 rounded-xl shadow">
                                    <div class="w-9 h-9 rounded-full bg-success/10 flex items-center justify-center">
                                        <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-sm">Secure Payment</span>
                                        <p class="text-xs text-base-content/60">256-bit SSL encryption</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-3 bg-base-100 rounded-xl shadow">
                                    <div class="w-9 h-9 rounded-full bg-info/10 flex items-center justify-center">
                                        <span class="icon-[tabler--receipt] size-5 text-info"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-sm">Instant Confirmation</span>
                                        <p class="text-xs text-base-content/60">Receipt sent to your email</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Store original price for calculations
const originalPrice = {{ $item['price'] ?? 0 }};
const currencySymbol = '{{ $currencySymbol }}';
let appliedOfferId = {{ !empty($autoAppliedOffer) ? $autoAppliedOffer['offer']->id : 'null' }};
let appliedDiscount = {{ $autoAppliedOffer['discount_amount'] ?? 0 }};

// Form submission handler
document.getElementById('payment-form').addEventListener('submit', function(e) {
    const submitBtns = this.querySelectorAll('button[type="submit"]');

    submitBtns.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Processing...';
    });
});

// Auto-apply promo code from URL if present
document.addEventListener('DOMContentLoaded', function() {
    const promoInput = document.getElementById('promo_code_input');
    if (promoInput && promoInput.value && !promoInput.readOnly) {
        // If there's a prefilled code and it's not already applied, auto-apply it
        applyPromoCode();
    }
});

// Apply promo code
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
    fetch('{{ route("booking.validate-promo", ["subdomain" => $host->subdomain]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code: code })
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

// Apply the offer to the UI
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

    // Update order summary
    updateOrderSummary(data.original_price, data.discount_amount, data.final_price);
}

// Remove promo code
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

    // Show input section (in case it was hidden)
    document.getElementById('promo-input-section').classList.remove('hidden');
    document.getElementById('promo-error').classList.add('hidden');

    // Reset order summary
    updateOrderSummary(originalPrice, 0, originalPrice);
}

// Update order summary display
function updateOrderSummary(subtotal, discount, total) {
    const discountRow = document.getElementById('discount-row');
    const originalPriceDisplay = document.getElementById('original-price-display');
    const finalPriceDisplay = document.getElementById('final-price-display');
    const btnPrice = document.getElementById('btn-price');

    if (discount > 0) {
        // Show discount row
        discountRow.classList.remove('hidden');
        document.getElementById('discount-value').textContent = '-' + currencySymbol + discount.toFixed(2);

        // Show original price strikethrough
        if (originalPriceDisplay) {
            originalPriceDisplay.textContent = currencySymbol + subtotal.toFixed(2);
            originalPriceDisplay.classList.remove('hidden');
        }

        // Update final price with success color
        if (finalPriceDisplay) {
            finalPriceDisplay.textContent = currencySymbol + total.toFixed(2);
            finalPriceDisplay.classList.remove('text-primary');
            finalPriceDisplay.classList.add('text-success');
        }
    } else {
        // Hide discount row
        discountRow.classList.add('hidden');

        // Hide original price
        if (originalPriceDisplay) {
            originalPriceDisplay.classList.add('hidden');
        }

        // Reset final price color
        if (finalPriceDisplay) {
            finalPriceDisplay.textContent = currencySymbol + total.toFixed(2);
            finalPriceDisplay.classList.add('text-primary');
            finalPriceDisplay.classList.remove('text-success');
        }
    }

    // Update button price
    if (btnPrice) {
        btnPrice.textContent = currencySymbol + total.toFixed(2);
    }
}

// Show promo error
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

@if(!empty($autoAppliedOffer))
// Auto-apply offer on page load
document.addEventListener('DOMContentLoaded', function() {
    updateOrderSummary(
        {{ $item['price'] ?? 0 }},
        {{ $autoAppliedOffer['discount_amount'] ?? 0 }},
        {{ max(0, ($item['price'] ?? 0) - ($autoAppliedOffer['discount_amount'] ?? 0)) }}
    );
});
@endif
</script>
@endpush
@endsection
