@extends('layouts.dashboard')

@section('title', 'Create Rental Invoice')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.index') }}">Rentals</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Invoice</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('rentals.invoice.store') }}" method="POST" id="invoice-form">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Client Selection --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Customer</h3>
                </div>
                <div class="card-body">
                    <div>
                        <label class="label-text" for="client_id">Select Customer</label>
                        <select id="client_id" name="client_id" class="hidden"
                            data-select='{
                                "placeholder": "Search customers...",
                                "hasSearch": true,
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "searchClasses": "input input-sm mb-2",
                                "searchWrapperClasses": "bg-base-100 p-2 sticky top-0",
                                "searchPlaceholder": "Type to search..."
                            }'>
                            <option value="">Walk-in (No customer)</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for walk-in customers</p>
                        @error('client_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Rental Items --}}
            <div class="card bg-base-100">
                <div class="card-header flex justify-between items-center">
                    <h3 class="card-title">Rental Items</h3>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div id="items-container" class="space-y-3">
                        {{-- Item rows will be added here --}}
                    </div>

                    <div id="no-items-message" class="text-center py-8 text-base-content/60">
                        <span class="icon-[tabler--package] size-12 mx-auto mb-2 block opacity-30"></span>
                        <p>No items added yet. Click "Add Item" to start.</p>
                    </div>

                    @error('items')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Rental Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Rental Details</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="rental_date">Rental Date <span class="text-error">*</span></label>
                            <input type="date" id="rental_date" name="rental_date"
                                value="{{ old('rental_date', date('Y-m-d')) }}"
                                class="input w-full @error('rental_date') input-error @enderror"
                                required>
                            @error('rental_date')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label-text" for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date"
                                value="{{ old('due_date') }}"
                                class="input w-full @error('due_date') input-error @enderror">
                            <p class="text-xs text-base-content/60 mt-1">When items should be returned</p>
                            @error('due_date')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="label-text" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="2"
                            class="textarea w-full @error('notes') input-error @enderror"
                            placeholder="Any special notes for this rental...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Payment --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Payment</h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label-text" for="currency">Currency</label>
                        <select id="currency" name="currency" class="select w-full" onchange="updatePrices()">
                            @foreach($hostCurrencies as $currency)
                                <option value="{{ $currency }}" {{ $currency === $defaultCurrency ? 'selected' : '' }}>
                                    {{ $currencySymbols[$currency] ?? $currency }} {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="payment_method">Payment Method <span class="text-error">*</span></label>
                        <select id="payment_method" name="payment_method" class="select w-full" required onchange="toggleManualMethod()">
                            <option value="manual" selected>Manual Payment</option>
                            <option value="comp">Complimentary</option>
                        </select>
                    </div>

                    <div id="manual-method-container">
                        <label class="label-text" for="manual_method">Payment Type <span class="text-error">*</span></label>
                        <select id="manual_method" name="manual_method" class="select w-full" required>
                            @foreach($manualMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="collect_deposit" value="1" id="collect_deposit"
                            class="checkbox checkbox-primary checkbox-sm"
                            {{ old('collect_deposit', true) ? 'checked' : '' }}
                            onchange="updateTotals()">
                        <div>
                            <span class="font-medium">Collect Security Deposit</span>
                            <p class="text-xs text-base-content/60">Refundable upon return</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Summary --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Subtotal</span>
                        <span id="subtotal-display" class="font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm" id="deposit-row">
                        <span class="text-base-content/60">Security Deposit</span>
                        <span id="deposit-display" class="font-medium">$0.00</span>
                    </div>
                    <hr class="border-base-200">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span id="total-display">$0.00</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card bg-base-100">
                <div class="card-body space-y-2">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="icon-[tabler--receipt] size-5"></span>
                        Create Invoice
                    </button>
                    <a href="{{ route('rentals.index') }}" class="btn btn-ghost w-full">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Item Template --}}
<template id="item-template">
    <div class="item-row flex gap-3 items-start p-3 bg-base-200/50 rounded-lg" data-index="__INDEX__">
        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <label class="label-text text-xs">Rental Item</label>
                <select name="items[__INDEX__][rental_item_id]" class="hidden item-select-raw" required
                    data-select='{
                        "placeholder": "Search items...",
                        "hasSearch": true,
                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                        "toggleClasses": "advance-select-toggle text-sm",
                        "dropdownClasses": "advance-select-menu max-h-60 overflow-y-auto",
                        "optionClasses": "advance-select-option selected:select-active text-sm",
                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                        "searchClasses": "input input-sm mb-2",
                        "searchWrapperClasses": "bg-base-100 p-2 sticky top-0",
                        "searchPlaceholder": "Type to search..."
                    }'>
                    <option value="">Select Item</option>
                    @foreach($rentalItems as $item)
                        <option value="{{ $item->id }}"
                            data-prices='@json($item->prices ?? [])'
                            data-deposits='@json($item->deposit_prices ?? [])'
                            data-available="{{ $item->available_inventory }}">
                            {{ $item->name }} ({{ $item->available_inventory }} avail.)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-text text-xs">Quantity</label>
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1"
                    class="input input-sm w-full item-quantity" required onchange="updateTotals()">
            </div>
        </div>
        <div class="text-right min-w-20">
            <label class="label-text text-xs">Price</label>
            <div class="item-price font-semibold">$0.00</div>
        </div>
        <button type="button" class="btn btn-ghost btn-sm btn-square text-error mt-5" onclick="removeItem(this)">
            <span class="icon-[tabler--trash] size-4"></span>
        </button>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = 0;
const currencySymbols = @json($currencySymbols);

function addItem() {
    const container = document.getElementById('items-container');
    const template = document.getElementById('item-template');
    const html = template.innerHTML.replace(/__INDEX__/g, itemIndex++);

    container.insertAdjacentHTML('beforeend', html);
    document.getElementById('no-items-message').classList.add('hidden');

    // Initialize HSSelect for the new item
    setTimeout(function() {
        if (typeof HSSelect !== 'undefined') {
            HSSelect.autoInit();
        }
        // Add change listener to the new select
        const newRow = container.lastElementChild;
        const rawSelect = newRow.querySelector('.item-select-raw');
        if (rawSelect) {
            rawSelect.addEventListener('change', function() {
                updateItemPrice(this);
            });
        }
    }, 50);

    updateTotals();
}

function removeItem(button) {
    const row = button.closest('.item-row');
    // Destroy HSSelect instance before removing
    const selectWrapper = row.querySelector('[data-select]');
    if (selectWrapper && selectWrapper.HSSelect) {
        selectWrapper.HSSelect.destroy();
    }
    row.remove();
    updateTotals();

    if (document.querySelectorAll('.item-row').length === 0) {
        document.getElementById('no-items-message').classList.remove('hidden');
    }
}

function updateItemPrice(select) {
    const row = select.closest('.item-row');
    const option = select.options[select.selectedIndex];
    const currency = document.getElementById('currency').value;

    if (option && option.value) {
        const prices = JSON.parse(option.dataset.prices || '{}');
        const price = prices[currency] || 0;
        const quantity = parseInt(row.querySelector('.item-quantity').value) || 1;
        const symbol = currencySymbols[currency] || currency;

        row.querySelector('.item-price').textContent = symbol + (price * quantity).toFixed(2);

        // Update max quantity based on available inventory
        const available = parseInt(option.dataset.available) || 1;
        row.querySelector('.item-quantity').max = available;
    } else {
        row.querySelector('.item-price').textContent = '$0.00';
    }

    updateTotals();
}

function updatePrices() {
    document.querySelectorAll('.item-select-raw').forEach(select => {
        updateItemPrice(select);
    });
}

function updateTotals() {
    const currency = document.getElementById('currency').value;
    const symbol = currencySymbols[currency] || currency;
    const collectDeposit = document.getElementById('collect_deposit').checked;

    let subtotal = 0;
    let depositTotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const select = row.querySelector('.item-select-raw');
        if (!select) return;
        const option = select.options[select.selectedIndex];
        const quantity = parseInt(row.querySelector('.item-quantity').value) || 1;

        if (option && option.value) {
            const prices = JSON.parse(option.dataset.prices || '{}');
            const deposits = JSON.parse(option.dataset.deposits || '{}');

            subtotal += (prices[currency] || 0) * quantity;
            depositTotal += (deposits[currency] || 0) * quantity;
        }
    });

    document.getElementById('subtotal-display').textContent = symbol + subtotal.toFixed(2);
    document.getElementById('deposit-display').textContent = symbol + depositTotal.toFixed(2);

    const total = collectDeposit ? subtotal + depositTotal : subtotal;
    document.getElementById('total-display').textContent = symbol + total.toFixed(2);

    // Show/hide deposit row
    document.getElementById('deposit-row').classList.toggle('hidden', !collectDeposit);
}

function toggleManualMethod() {
    const paymentMethod = document.getElementById('payment_method').value;
    const manualContainer = document.getElementById('manual-method-container');
    const manualSelect = document.getElementById('manual_method');

    if (paymentMethod === 'manual') {
        manualContainer.classList.remove('hidden');
        manualSelect.required = true;
    } else {
        manualContainer.classList.add('hidden');
        manualSelect.required = false;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add one item by default
    addItem();
    toggleManualMethod();
});
</script>
@endpush
@endsection
