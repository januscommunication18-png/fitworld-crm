@extends('layouts.dashboard')

@section('title', $trans['rentals.create_invoice'] ?? 'Create Rental Invoice')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'item-rentals']) }}">{{ $trans['nav.item_rentals'] ?? 'Item Rentals' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['rentals.create_invoice'] ?? 'Create Invoice' }}</li>
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
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--user] size-5"></span>
                        {{ $trans['walk_in.select_client'] ?? 'Select Client' }}
                    </h2>

                    {{-- Client Type Selection --}}
                    <div id="client-type-selection" class="grid grid-cols-2 gap-3 mb-4">
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="radio" name="client_type" value="existing" class="radio radio-primary" checked>
                            <span class="icon-[tabler--users] size-6 text-primary"></span>
                            <div>
                                <span class="font-semibold">{{ $trans['walk_in.existing_client'] ?? 'Existing Client' }}</span>
                                <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.search_client_list'] ?? 'Search client list' }}</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                            <input type="radio" name="client_type" value="new" class="radio radio-success">
                            <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                            <div>
                                <span class="font-semibold">{{ $trans['walk_in.new_client'] ?? 'New Client' }}</span>
                                <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.create_new_profile'] ?? 'Create new profile' }}</span>
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
                                       placeholder="{{ $trans['walk_in.search_placeholder'] ?? 'Search by name, email or phone...' }}">
                            </div>
                        </div>
                        <div id="client-search-results" class="space-y-2"></div>
                        <p class="text-xs text-base-content/60 mt-2">{{ $trans['rentals.walk_in_note'] ?? 'Leave empty for walk-in customers without a profile' }}</p>
                    </div>

                    {{-- New Client Form --}}
                    <div id="new-client-section" class="hidden space-y-4">
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

// Client selection elements
const clientSearch = document.getElementById('client-search');
const clientSearchResults = document.getElementById('client-search-results');
const existingClientSection = document.getElementById('existing-client-section');
const newClientSection = document.getElementById('new-client-section');
const selectedClientDiv = document.getElementById('selected-client');
const clientIdInput = document.getElementById('client_id');

let searchTimeout;

// Client type toggle
document.querySelectorAll('input[name="client_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'existing') {
            existingClientSection.classList.remove('hidden');
            newClientSection.classList.add('hidden');
        } else {
            existingClientSection.classList.add('hidden');
            newClientSection.classList.remove('hidden');
        }
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
                    clientSearchResults.innerHTML = '<p class="text-base-content/60 text-sm p-2">{{ $trans["common.no_results"] ?? "No clients found" }}</p>';
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
        alert('{{ $trans["msg.error.name_required"] ?? "Please enter first and last name" }}');
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
    clientIdInput.value = client.id;
    document.getElementById('selected-client-initials').textContent = client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase();
    document.getElementById('selected-client-name').textContent = `${client.first_name} ${client.last_name}`;
    document.getElementById('selected-client-email').textContent = client.email || client.phone || '';

    selectedClientDiv.classList.remove('hidden');
    clientSearch.value = '';
    clientSearchResults.innerHTML = '';

    // Switch back to existing client type
    document.querySelector('input[name="client_type"][value="existing"]').checked = true;
    existingClientSection.classList.remove('hidden');
    newClientSection.classList.add('hidden');
}

window.clearSelectedClient = function() {
    clientIdInput.value = '';
    selectedClientDiv.classList.add('hidden');
};

// Rental items functions
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
