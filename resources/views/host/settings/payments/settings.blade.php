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

// Manual payment methods configuration
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
    {{-- Payment Methods Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Payment Methods</h2>
            <div class="space-y-4">
                {{-- Credit/Debit Cards --}}
                <label class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--credit-card] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Credit/Debit Cards</div>
                            <div class="text-sm text-base-content/60">Accept Visa, Mastercard, Amex via Stripe</div>
                        </div>
                    </div>
                    <input type="checkbox" class="checkbox checkbox-primary" id="accept_cards" name="accept_cards" {{ ($paymentSettings['accept_cards'] ?? true) ? 'checked' : '' }} />
                </label>

                {{-- Apple Pay / Google Pay --}}
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg bg-base-200/50">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--wallet] size-8 text-base-content/40"></span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-base-content/60">Apple Pay / Google Pay</span>
                                <span class="badge badge-soft badge-sm">Coming Soon</span>
                            </div>
                            <div class="text-sm text-base-content/40">Digital wallet payments</div>
                        </div>
                    </div>
                    <input type="checkbox" class="checkbox checkbox-primary" disabled />
                </div>

                {{-- Cash Payments (Legacy - now in manual methods) --}}
                <label class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--cash] size-8 text-success"></span>
                        <div>
                            <div class="font-medium">Cash Payments</div>
                            <div class="text-sm text-base-content/60">Accept cash at studio (manual entry)</div>
                        </div>
                    </div>
                    <input type="checkbox" class="checkbox checkbox-primary" id="accept_cash" name="accept_cash" {{ ($paymentSettings['accept_cash'] ?? false) ? 'checked' : '' }} />
                </label>
            </div>
        </div>
    </div>

    {{-- Public Booking Manual Payment Methods --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Manual Payment Methods</h2>
                    <p class="text-sm text-base-content/60 mt-1">Enable payment options for your public booking page. Clients can select these at checkout.</p>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($manualMethods as $methodKey => $method)
                @php
                    $methodConfig = $enabledManualMethods[$methodKey] ?? [];
                    $isEnabled = $methodConfig['enabled'] ?? false;
                    $instructions = $methodConfig['instructions'] ?? '';
                @endphp
                <div class="border border-base-content/10 rounded-lg overflow-hidden manual-method-item" data-method="{{ $methodKey }}">
                    <label class="flex items-center justify-between p-4 cursor-pointer hover:bg-base-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="icon-[tabler--{{ $method['icon'] }}] size-8 text-base-content/70"></span>
                            <div class="font-medium">{{ $method['label'] }}</div>
                        </div>
                        <input type="checkbox"
                               class="checkbox checkbox-primary manual-method-toggle"
                               id="manual_{{ $methodKey }}"
                               data-method="{{ $methodKey }}"
                               {{ $isEnabled ? 'checked' : '' }} />
                    </label>
                    <div class="manual-method-details px-4 pb-4 {{ $isEnabled ? '' : 'hidden' }}">
                        <div class="pl-12">
                            <label class="label-text text-sm" for="manual_{{ $methodKey }}_instructions">Payment Instructions</label>
                            <input type="text"
                                   id="manual_{{ $methodKey }}_instructions"
                                   class="input input-bordered input-sm w-full mt-1"
                                   placeholder="{{ $method['placeholder'] }}"
                                   value="{{ $instructions }}" />
                            <p class="text-xs text-base-content/50 mt-1">This will be shown to clients when they select this payment method</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-base-content/10">
                <p class="text-xs text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-4 align-middle me-1"></span>
                    Manual payments create a pending transaction. You'll need to mark them as paid in the admin dashboard when payment is received.
                </p>
            </div>
        </div>
    </div>

    {{-- Currency & Locale Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Currency & Locale</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    <div id="number-format-display" class="input w-full flex items-center bg-base-200 cursor-not-allowed">
                        <span id="format-preview">{{ $currencies[$selectedCurrency]['symbol'] ?? '$' }}{{ $currencies[$selectedCurrency]['format'] ?? '1,234.56' }}</span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1">Format is based on selected currency</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Receipt Settings Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Receipt Settings</h2>
            <div class="space-y-4">
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <div class="font-medium">Send email receipts</div>
                        <div class="text-sm text-base-content/60">Automatically email receipts after purchase</div>
                    </div>
                    <input type="checkbox" class="checkbox checkbox-primary" id="send_receipts" name="send_receipts" {{ ($paymentSettings['send_receipts'] ?? true) ? 'checked' : '' }} />
                </label>
                <div>
                    <label class="label-text" for="receipt_footer">Receipt Footer Text</label>
                    <textarea id="receipt_footer" class="textarea w-full" rows="2" placeholder="Thank you for your purchase!">{{ $paymentSettings['receipt_footer'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="button" class="btn btn-primary" id="save-btn" onclick="savePaymentSettings()">
            <span class="loading loading-spinner loading-xs hidden" id="save-spinner"></span>
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

// Listen for currency select changes and manual method toggles
document.addEventListener('DOMContentLoaded', function() {
    var currencySelect = document.getElementById('currency');
    if (currencySelect) {
        currencySelect.addEventListener('change', updateNumberFormat);
    }

    // Manual payment method toggles
    document.querySelectorAll('.manual-method-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var item = this.closest('.manual-method-item');
            var details = item.querySelector('.manual-method-details');
            if (this.checked) {
                details.classList.remove('hidden');
            } else {
                details.classList.add('hidden');
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
    btn.disabled = true;
    spinner.classList.remove('hidden');

    // Collect manual payment methods
    var manualMethods = {};
    document.querySelectorAll('.manual-method-item').forEach(function(item) {
        var methodKey = item.dataset.method;
        var toggle = item.querySelector('.manual-method-toggle');
        var instructionsInput = item.querySelector('input[type="text"]');

        manualMethods[methodKey] = {
            enabled: toggle.checked,
            instructions: instructionsInput ? instructionsInput.value : ''
        };
    });

    var data = {
        accept_cards: document.getElementById('accept_cards').checked,
        accept_cash: document.getElementById('accept_cash').checked,
        currency: document.getElementById('currency').value,
        send_receipts: document.getElementById('send_receipts').checked,
        receipt_footer: document.getElementById('receipt_footer').value,
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
    });
}
</script>
@endpush
