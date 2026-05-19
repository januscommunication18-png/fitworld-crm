@extends('layouts.settings')

@section('title', 'Payment Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Payment Settings</li>
    </ol>
@endsection

@php
$currencies = [
    'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'format' => '1,234.56'],
    'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar', 'format' => '1,234.56'],
    'GBP' => ['symbol' => '£', 'name' => 'Pound Sterling', 'format' => '1,234.56'],
    'EUR' => ['symbol' => '€', 'name' => 'Euro', 'format' => '1.234,56'],
    'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar', 'format' => '1,234.56'],
    'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'format' => '1,23,456.78'],
];

$paymentSettings = $host->payment_settings ?? [];
$selectedCurrency = $paymentSettings['currency'] ?? ($host->currencies[0] ?? 'USD');

$manualMethods = [
    'venmo' => ['label' => 'Venmo', 'icon' => 'brand-venmo', 'placeholder' => '@your-venmo-handle'],
    'zelle' => ['label' => 'Zelle', 'icon' => 'cash', 'placeholder' => 'email@example.com or phone'],
    'cash_app' => ['label' => 'Cash App', 'icon' => 'cash', 'placeholder' => '$YourCashTag'],
    'paypal' => ['label' => 'PayPal', 'icon' => 'brand-paypal', 'placeholder' => 'email@example.com'],
    'bank_transfer' => ['label' => 'Bank Transfer', 'icon' => 'building-bank', 'placeholder' => 'Account details or instructions'],
    'cash' => ['label' => 'Cash (In Person)', 'icon' => 'cash-banknote', 'placeholder' => 'Pay at the studio'],
];
$enabledManualMethods = $paymentSettings['manual_methods'] ?? [];
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold">Payment Settings</h1>
        <p class="text-base-content/60 mt-1">Configure payment methods, currency, and receipt preferences.</p>
    </div>

    <div class="accordion space-y-1.5" id="payment-settings-accordion">

        {{-- ═══ 1. Payment Methods ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="payment-methods-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="payment-methods-content" aria-expanded="false">
                <span class="icon-[tabler--credit-card] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Payment Methods</span>
                    <span class="text-base-content/60 text-sm block font-normal">Credit cards, cash, and digital wallets</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="payment-methods-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5 space-y-3 pt-2">
                    {{-- Credit/Debit Cards --}}
                    <div class="flex items-center justify-between p-4 rounded-xl border border-base-300 hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <div class="flex items-center gap-4">
                            <span class="icon-[tabler--credit-card] size-7 text-primary"></span>
                            <div>
                                <span class="font-medium text-sm">Credit/Debit Cards</span>
                                <p class="text-xs text-base-content/60">Accept Visa, Mastercard, Amex via Stripe</p>
                            </div>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" id="accept_cards" name="accept_cards" {{ ($paymentSettings['accept_cards'] ?? true) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>

                    {{-- Cash Payments --}}
                    <div class="flex items-center justify-between p-4 rounded-xl border border-base-300 hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <div class="flex items-center gap-4">
                            <span class="icon-[tabler--cash] size-7 text-success"></span>
                            <div>
                                <span class="font-medium text-sm">Cash Payments</span>
                                <p class="text-xs text-base-content/60">Accept cash at studio (manual entry)</p>
                            </div>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" id="accept_cash" name="accept_cash" {{ ($paymentSettings['accept_cash'] ?? false) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>

                    {{-- Apple Pay / Google Pay --}}
                    <div class="flex items-center justify-between p-4 rounded-xl border border-base-content/10 bg-base-200/30 opacity-60">
                        <div class="flex items-center gap-4">
                            <span class="icon-[tabler--wallet] size-7 text-base-content/40"></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm text-base-content/60">Apple Pay / Google Pay</span>
                                    <span class="badge badge-soft badge-sm">Coming Soon</span>
                                </div>
                                <p class="text-xs text-base-content/40">Digital wallet payments</p>
                            </div>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" disabled />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 2. Manual Payment Methods ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="manual-methods-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="manual-methods-content" aria-expanded="false">
                <span class="icon-[tabler--cash-register] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Manual Payment Methods</span>
                    <span class="text-base-content/60 text-sm block font-normal">Venmo, Zelle, PayPal, bank transfer, and cash options for booking page</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="manual-methods-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5">
                    <p class="text-sm text-base-content/60 mb-4 pt-2">Enable payment options for your public booking page. Clients can select these at checkout.</p>

                    <div class="space-y-3">
                        @foreach($manualMethods as $methodKey => $method)
                        @php
                            $methodConfig = $enabledManualMethods[$methodKey] ?? [];
                            $isEnabled = $methodConfig['enabled'] ?? false;
                            $instructions = $methodConfig['instructions'] ?? '';
                        @endphp
                        <div class="border border-base-300 rounded-xl overflow-hidden manual-method-item transition-colors {{ $isEnabled ? 'border-primary bg-primary/5' : '' }}" data-method="{{ $methodKey }}">
                            <div class="flex items-center justify-between p-4">
                                <div class="flex items-center gap-4">
                                    <span class="icon-[tabler--{{ $method['icon'] }}] size-6 text-base-content/70"></span>
                                    <span class="font-medium text-sm">{{ $method['label'] }}</span>
                                </div>
                                <label class="switch switch-primary">
                                    <input type="checkbox"
                                           class="manual-method-toggle"
                                           id="manual_{{ $methodKey }}"
                                           data-method="{{ $methodKey }}"
                                           {{ $isEnabled ? 'checked' : '' }} />
                                    <span class="switch-indicator"></span>
                                </label>
                            </div>
                            <div class="manual-method-details px-4 pb-4 {{ $isEnabled ? '' : 'hidden' }}">
                                <div class="pl-10">
                                    <label class="label-text text-xs" for="manual_{{ $methodKey }}_instructions">Payment Instructions</label>
                                    <textarea id="manual_{{ $methodKey }}_instructions"
                                              class="textarea w-full mt-1"
                                              rows="2"
                                              placeholder="{{ $method['placeholder'] }}">{{ $instructions }}</textarea>
                                    <p class="text-xs text-base-content/50 mt-1">Shown to clients when they select this payment method.</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 p-3 bg-base-200/50 rounded-lg">
                        <p class="text-xs text-base-content/50">
                            <span class="icon-[tabler--info-circle] size-4 align-middle me-1"></span>
                            Manual payments create a pending transaction. Mark them as paid in the admin dashboard when payment is received.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 3. Currency & Locale ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="currency-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="currency-content" aria-expanded="false">
                <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Currency & Locale</span>
                    <span class="text-base-content/60 text-sm block font-normal">Default currency and number format</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="currency-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div>
                            <label class="label-text" for="currency">Default Currency</label>
                            <select
                                id="currency"
                                name="currency"
                                data-select='{
                                    "hasSearch": true,
                                    "searchPlaceholder": "Search currencies...",
                                    "placeholder": "Select currency...",
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle w-full",
                                    "dropdownClasses": "advance-select-menu max-h-48 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }'
                                class="hidden"
                            >
                                <option value="">Select currency...</option>
                                @if($host->currencies && count($host->currencies) > 0)
                                    @foreach($host->currencies as $code)
                                        @if(isset($currencies[$code]))
                                            <option value="{{ $code }}" {{ $selectedCurrency == $code ? 'selected' : '' }}>
                                                {{ $code }} ({{ $currencies[$code]['symbol'] }}) — {{ $currencies[$code]['name'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                @else
                                    <option value="USD" selected>USD ($) — US Dollar</option>
                                @endif
                            </select>
                            <p class="text-xs text-base-content/50 mt-1">
                                <a href="{{ route('settings.studio.profile') }}" class="link link-primary">Add more currencies</a> in Studio Profile
                            </p>
                        </div>
                        <div>
                            <label class="label-text" for="number_format">Number Format</label>
                            <div id="number-format-display" class="input w-full flex items-center bg-base-200 cursor-not-allowed mt-1">
                                <span id="format-preview">{{ $currencies[$selectedCurrency]['symbol'] ?? '$' }}{{ $currencies[$selectedCurrency]['format'] ?? '1,234.56' }}</span>
                            </div>
                            <p class="text-xs text-base-content/50 mt-1">Format is based on selected currency</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 4. Read to Client ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="read-to-client-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="read-to-client-content" aria-expanded="false">
                <span class="icon-[tabler--speakerphone] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Read to Client</span>
                    <span class="text-base-content/60 text-sm block font-normal">Terms & conditions shown during payment confirmation</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="read-to-client-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5 pt-2">
                    <p class="text-sm text-base-content/60 mb-4">This text will be shown in the payment confirmation drawer. Staff should read it to the client before confirming payment.</p>
                    <div>
                        <label class="label-text" for="read_to_client">Terms & Conditions</label>
                        <textarea id="read_to_client" class="textarea w-full mt-1" rows="4" placeholder="Enter terms, cancellation policy, or any important information to read to the client before confirming payment...">{{ $paymentSettings['read_to_client'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 5. Receipt Settings ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="receipt-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="receipt-content" aria-expanded="false">
                <span class="icon-[tabler--receipt] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Receipt Settings</span>
                    <span class="text-base-content/60 text-sm block font-normal">Email receipts and footer text</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="receipt-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5 space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium">Send email receipts</span>
                            <p class="text-xs text-base-content/60">Automatically email receipts after purchase</p>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" id="send_receipts" name="send_receipts" {{ ($paymentSettings['send_receipts'] ?? true) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>
                    <div>
                        <label class="label-text" for="receipt_footer">Receipt Footer Text</label>
                        <textarea id="receipt_footer" class="textarea w-full mt-1" rows="2" placeholder="Thank you for your purchase!">{{ $paymentSettings['receipt_footer'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /accordion --}}

    {{-- Save --}}
    <div class="flex justify-start">
        <button type="button" class="btn btn-primary btn-sm" id="save-btn" onclick="savePaymentSettings()">
            <span class="loading loading-spinner loading-xs hidden" id="save-spinner"></span>
            <span class="icon-[tabler--check] size-4" id="save-icon"></span>
            Save Changes
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var currencies = @json($currencies);

function updateNumberFormat() {
    var currencySelect = document.getElementById('currency');
    var selectedCode = currencySelect.value;
    var currencyInfo = currencies[selectedCode];

    if (currencyInfo) {
        document.getElementById('format-preview').textContent = currencyInfo.symbol + currencyInfo.format;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var currencySelect = document.getElementById('currency');
    if (currencySelect) {
        currencySelect.addEventListener('change', updateNumberFormat);
    }

    document.querySelectorAll('.manual-method-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var item = this.closest('.manual-method-item');
            var details = item.querySelector('.manual-method-details');
            if (this.checked) {
                details.classList.remove('hidden');
                item.classList.add('border-primary', 'bg-primary/5');
                item.classList.remove('border-base-300');
            } else {
                details.classList.add('hidden');
                item.classList.remove('border-primary', 'bg-primary/5');
                item.classList.add('border-base-300');
            }
        });
    });
});

function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
    toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

function savePaymentSettings() {
    var btn = document.getElementById('save-btn');
    var spinner = document.getElementById('save-spinner');
    var icon = document.getElementById('save-icon');
    btn.disabled = true;
    spinner.classList.remove('hidden');
    icon.classList.add('hidden');

    var manualMethods = {};
    document.querySelectorAll('.manual-method-item').forEach(function(item) {
        var methodKey = item.dataset.method;
        var toggle = item.querySelector('.manual-method-toggle');
        var instructionsTextarea = item.querySelector('textarea');

        manualMethods[methodKey] = {
            enabled: toggle.checked,
            instructions: instructionsTextarea ? instructionsTextarea.value : ''
        };
    });

    var data = {
        accept_cards: document.getElementById('accept_cards').checked,
        accept_cash: document.getElementById('accept_cash').checked,
        currency: document.getElementById('currency').value,
        send_receipts: document.getElementById('send_receipts').checked,
        receipt_footer: document.getElementById('receipt_footer').value,
        read_to_client: document.getElementById('read_to_client').value,
        manual_methods: manualMethods
    };

    fetch('{{ route("settings.payments.settings.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Payment settings saved!');
        } else {
            showToast(result.message || 'Failed to save settings', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
        icon.classList.remove('hidden');
    });
}
</script>
@endpush
