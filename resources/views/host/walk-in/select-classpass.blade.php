@extends('layouts.dashboard')

@section('title', 'Sell Class Pass')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.calendar') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Calendar</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Sell Class Pass</li>
    </ol>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('schedule.calendar') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Sell Class Pass</h1>
            <p class="text-base-content/60">Sell a class pass to a client</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <div>
            <div class="font-medium">Please fix the following errors:</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form id="classpass-form" action="{{ route('walk-in.classpass.sell') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Client Selection --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--user] size-5"></span>
                            Select Client
                        </h2>

                        {{-- Client Type Selection --}}
                        <div id="client-type-selection" class="grid grid-cols-2 gap-3 mb-4">
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
                                    <input type="text" id="client-search" class="input input-bordered w-full pl-10"
                                           placeholder="Search by name, email or phone...">
                                </div>
                            </div>
                            <div id="client-search-results" class="space-y-2"></div>
                        </div>

                        {{-- New Client Form --}}
                        <div id="new-client-section" class="hidden space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label-text" for="new_first_name">First Name</label>
                                    <input type="text" id="new_first_name" class="input input-bordered" placeholder="John">
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="new_last_name">Last Name</label>
                                    <input type="text" id="new_last_name" class="input input-bordered" placeholder="Doe">
                                </div>
                            </div>
                            <div class="form-control">
                                <label class="label-text" for="new_email">Email</label>
                                <input type="email" id="new_email" class="input input-bordered" placeholder="john@example.com">
                            </div>
                            <div class="form-control">
                                <label class="label-text" for="new_phone">Phone</label>
                                <input type="tel" id="new_phone" class="input input-bordered" placeholder="+1 234 567 8900">
                            </div>
                            <button type="button" id="create-client-btn" class="btn btn-success btn-sm">
                                <span class="icon-[tabler--plus] size-4"></span> Create Client
                            </button>
                        </div>

                        {{-- Selected Client Display --}}
                        <div id="selected-client" class="hidden mt-4 p-4 bg-primary/5 border border-primary/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
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

                {{-- Class Pass Selection --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--ticket] size-5"></span>
                            Select Class Pass
                        </h2>

                        @if($classPasses->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--ticket-off] size-12 text-base-content/20"></span>
                                <p class="text-base-content/60 mt-2">No active class passes available.</p>
                                <a href="{{ route('class-passes.create') }}" class="btn btn-primary btn-sm mt-4">
                                    Create Class Pass
                                </a>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($classPasses as $pass)
                                    <label class="classpass-option flex flex-col p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="class_pass_id" value="{{ $pass->id }}"
                                                       data-price="{{ $pass->getPriceForCurrency($defaultCurrency) }}"
                                                       data-name="{{ $pass->name }}"
                                                       data-credits="{{ $pass->class_count }}"
                                                       data-validity="{{ $pass->formatted_validity }}"
                                                       class="radio radio-primary">
                                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $pass->color ?? '#6366f1' }}"></div>
                                                <span class="font-semibold">{{ $pass->name }}</span>
                                            </div>
                                            <span class="badge badge-soft badge-info">{{ $pass->class_count }} credits</span>
                                        </div>
                                        <div class="ml-7">
                                            <div class="text-lg font-bold text-primary">{{ $pass->getFormattedPriceForCurrency($defaultCurrency) }}</div>
                                            <div class="text-xs text-base-content/60 mt-1">Valid for {{ $pass->formatted_validity }}</div>
                                            @if($pass->description)
                                                <p class="text-xs text-base-content/60 mt-1 line-clamp-2">{{ $pass->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Payment --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--cash] size-5"></span>
                            Payment
                        </h2>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="payment_method" value="cash" class="radio radio-primary" checked>
                                <span class="icon-[tabler--cash] size-5 text-success"></span>
                                <span class="font-medium">Cash</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="payment_method" value="card" class="radio radio-primary">
                                <span class="icon-[tabler--credit-card] size-5 text-info"></span>
                                <span class="font-medium">Card</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="payment_method" value="check" class="radio radio-primary">
                                <span class="icon-[tabler--file-invoice] size-5 text-warning"></span>
                                <span class="font-medium">Check</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="payment_method" value="other" class="radio radio-primary">
                                <span class="icon-[tabler--dots] size-5 text-base-content/50"></span>
                                <span class="font-medium">Other</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="payment_method" value="comp" class="radio radio-success">
                                <span class="icon-[tabler--gift] size-5 text-success"></span>
                                <span class="font-medium">Complimentary</span>
                            </label>
                        </div>

                        {{-- Amount Paid --}}
                        <div id="amount-section" class="mt-4">
                            <div class="form-control">
                                <label class="label-text" for="amount_paid">Amount Paid</label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$defaultCurrency] ?? '$' }}</span>
                                    <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="0"
                                           class="grow w-full" placeholder="0.00" value="0">
                                </label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="form-control mt-4">
                            <label class="label-text" for="notes">Notes (optional)</label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="Payment notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Summary & Submit --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--receipt] size-5"></span>
                            Summary
                        </h2>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Class Pass</span>
                                <span class="font-medium" id="summary-pass">--</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Credits</span>
                                <span class="font-medium" id="summary-credits">--</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Validity</span>
                                <span class="font-medium" id="summary-validity">--</span>
                            </div>
                            <div class="divider my-1"></div>
                            <div class="flex justify-between text-base font-bold">
                                <span>Total</span>
                                <span class="text-primary" id="summary-total">$0.00</span>
                            </div>
                        </div>

                        @include('components.read-to-client', ['rtcId' => 'classpass', 'rtcSubmitBtn' => 'submit-btn', 'rtcClass' => 'mt-4'])

                        <button type="submit" class="btn btn-primary w-full mt-4" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectedClientId = null;
    var selectedPassPrice = 0;

    // Client type toggle
    document.querySelectorAll('input[name="client_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var isNew = this.value === 'new';
            document.getElementById('existing-client-section').classList.toggle('hidden', isNew);
            document.getElementById('new-client-section').classList.toggle('hidden', !isNew);
        });
    });

    // Client search
    var searchTimeout = null;
    document.getElementById('client-search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();
        if (query.length < 2) {
            document.getElementById('client-search-results').innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(function() {
            fetch('/walk-in/clients/search?q=' + encodeURIComponent(query))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var html = '';
                    if (data.clients.length === 0) {
                        html = '<div class="text-center py-4 text-base-content/50 text-sm">No clients found</div>';
                    } else {
                        data.clients.forEach(function(c) {
                            var initials = (c.first_name[0] + c.last_name[0]).toUpperCase();
                            html += '<div class="flex items-center justify-between p-3 border border-base-300 rounded-lg hover:bg-base-200/50 cursor-pointer transition-colors" onclick="selectClient(' + c.id + ', \'' + c.first_name.replace(/'/g, "\\'") + '\', \'' + c.last_name.replace(/'/g, "\\'") + '\', \'' + (c.email || '').replace(/'/g, "\\'") + '\')">' +
                                '<div class="flex items-center gap-3">' +
                                '<div class="avatar placeholder"><div class="bg-primary text-primary-content w-10 h-10 rounded-full"><span>' + initials + '</span></div></div>' +
                                '<div><div class="font-medium">' + c.first_name + ' ' + c.last_name + '</div>' +
                                '<div class="text-sm text-base-content/60">' + (c.email || c.phone || 'No contact') + '</div></div>' +
                                '</div><span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span></div>';
                        });
                    }
                    document.getElementById('client-search-results').innerHTML = html;
                });
        }, 300);
    });

    window.selectClient = function(id, firstName, lastName, email) {
        selectedClientId = id;
        document.getElementById('client_id').value = id;
        document.getElementById('selected-client-initials').textContent = (firstName[0] + lastName[0]).toUpperCase();
        document.getElementById('selected-client-name').textContent = firstName + ' ' + lastName;
        document.getElementById('selected-client-email').textContent = email || 'No email';
        document.getElementById('selected-client').classList.remove('hidden');
        document.getElementById('client-type-selection').classList.add('hidden');
        document.getElementById('existing-client-section').classList.add('hidden');
        document.getElementById('client-search-results').innerHTML = '';
        updateSubmitBtn();
    };

    window.clearSelectedClient = function() {
        selectedClientId = null;
        document.getElementById('client_id').value = '';
        document.getElementById('selected-client').classList.add('hidden');
        document.getElementById('client-type-selection').classList.remove('hidden');
        document.getElementById('existing-client-section').classList.remove('hidden');
        document.querySelector('input[name="client_type"][value="existing"]').checked = true;
        document.getElementById('new-client-section').classList.add('hidden');
        document.getElementById('client-search').value = '';
        updateSubmitBtn();
    };

    // Create new client
    document.getElementById('create-client-btn').addEventListener('click', function() {
        var firstName = document.getElementById('new_first_name').value.trim();
        var lastName = document.getElementById('new_last_name').value.trim();
        var email = document.getElementById('new_email').value.trim();
        var phone = document.getElementById('new_phone').value.trim();

        if (!firstName || !lastName) { alert('First and last name are required.'); return; }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Creating...';

        fetch('/walk-in/clients/quick-add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ first_name: firstName, last_name: lastName, email: email, phone: phone })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--plus] size-4"></span> Create Client';
            if (data.success) {
                selectClient(data.client.id, data.client.first_name, data.client.last_name, data.client.email || '');
            } else { alert(data.message || 'Failed to create client.'); }
        })
        .catch(function() { btn.disabled = false; btn.innerHTML = '<span class="icon-[tabler--plus] size-4"></span> Create Client'; });
    });

    // Class pass selection
    document.querySelectorAll('input[name="class_pass_id"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            selectedPassPrice = parseFloat(this.dataset.price) || 0;
            document.getElementById('summary-pass').textContent = this.dataset.name;
            document.getElementById('summary-credits').textContent = this.dataset.credits;
            document.getElementById('summary-validity').textContent = this.dataset.validity;
            document.getElementById('summary-total').textContent = '$' + selectedPassPrice.toFixed(2);
            document.getElementById('amount_paid').value = selectedPassPrice.toFixed(2);
            updateSubmitBtn();
        });
    });

    // Payment method toggle
    document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'comp') {
                document.getElementById('amount-section').classList.add('hidden');
                document.getElementById('amount_paid').value = '0';
                document.getElementById('summary-total').textContent = '$0.00';
            } else {
                document.getElementById('amount-section').classList.remove('hidden');
                document.getElementById('amount_paid').value = selectedPassPrice.toFixed(2);
                document.getElementById('summary-total').textContent = '$' + selectedPassPrice.toFixed(2);
            }
        });
    });

    function updateSubmitBtn() {
        var hasClient = !!selectedClientId;
        var hasPass = !!document.querySelector('input[name="class_pass_id"]:checked');
        document.getElementById('submit-btn').disabled = !(hasClient && hasPass);
    }
});
</script>
@endpush
@endsection
