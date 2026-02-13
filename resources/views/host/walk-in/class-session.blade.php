@extends('layouts.dashboard')

@section('title', 'Walk-In Booking')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Walk-In Booking</h1>
            <p class="text-base-content/60">Book a client for this class</p>
        </div>
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
                    <div class="text-sm text-base-content/60">spots left</div>
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
                            Select Client
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
                                       placeholder="Search by name, email, or phone..."
                                       autocomplete="off">
                                <div id="search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                    <span class="loading loading-spinner loading-sm"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Search Results --}}
                        <div id="search-results" class="hidden mb-4">
                            <div class="text-sm font-medium text-base-content/60 mb-2">Search Results</div>
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
                                        Change
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="client_id" id="client-id" value="">
                        </div>

                        {{-- Recent Clients --}}
                        <div id="recent-clients">
                            <div class="text-sm font-medium text-base-content/60 mb-2">Recent Clients</div>
                            <div class="space-y-2">
                                @forelse($recentClients as $client)
                                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg hover:bg-base-200/50 cursor-pointer transition-colors"
                                     onclick="selectClient({{ $client->id }}, '{{ $client->first_name }}', '{{ $client->last_name }}', '{{ $client->email }}', '{{ $client->phone }}', '{{ $client->avatar_url }}')">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :src="$client->avatar_url" :initials="$client->initials" :alt="$client->full_name" size="md" />
                                        <div>
                                            <div class="font-medium">{{ $client->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $client->email ?: $client->phone ?: 'No contact info' }}</div>
                                        </div>
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-base-content/50">
                                    <span class="icon-[tabler--users-off] size-8 mb-2"></span>
                                    <p class="text-sm">No recent clients</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Quick Add --}}
                        <div class="divider">OR</div>

                        <button type="button" class="btn btn-outline btn-primary btn-block" onclick="toggleQuickAdd()">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            Add New Client
                        </button>

                        <div id="quick-add-form" class="hidden mt-4 p-4 border border-base-300 rounded-lg bg-base-200/30">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="form-control">
                                    <label class="label" for="new-first-name"><span class="label-text">First Name *</span></label>
                                    <input type="text" id="new-first-name" class="input input-bordered" placeholder="John">
                                </div>
                                <div class="form-control">
                                    <label class="label" for="new-last-name"><span class="label-text">Last Name *</span></label>
                                    <input type="text" id="new-last-name" class="input input-bordered" placeholder="Doe">
                                </div>
                            </div>
                            <div class="form-control mb-3">
                                <label class="label" for="new-email"><span class="label-text">Email</span></label>
                                <input type="email" id="new-email" class="input input-bordered" placeholder="john@example.com">
                            </div>
                            <div class="form-control mb-4">
                                <label class="label" for="new-phone"><span class="label-text">Phone</span></label>
                                <input type="tel" id="new-phone" class="input input-bordered" placeholder="(555) 123-4567">
                            </div>
                            <button type="button" class="btn btn-primary btn-block" onclick="createClient()">
                                <span class="icon-[tabler--check] size-5"></span>
                                Create & Select
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Payment Method Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--credit-card] size-5 mr-2"></span>
                            Payment Method
                        </h3>
                    </div>
                    <div class="card-body space-y-3">
                        {{-- Manual Payment --}}
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="manual" class="radio radio-primary mt-0.5" checked>
                            <div class="flex-1">
                                <div class="font-medium">Manual Payment</div>
                                <div class="text-sm text-base-content/60">Cash, card, check, or other</div>
                                <div class="mt-3 grid grid-cols-2 gap-3" id="manual-details">
                                    <select name="manual_method" class="select select-bordered select-sm">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="check">Check</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="number" name="price_paid" step="0.01" min="0" class="input input-bordered input-sm" placeholder="Amount" value="{{ $session->price ?? $session->classPlan->default_price ?? 0 }}">
                                </div>
                            </div>
                        </label>

                        {{-- Complimentary --}}
                        @can('comp', App\Models\Booking::class)
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="comp" class="radio radio-primary mt-0.5">
                            <div>
                                <div class="font-medium">Complimentary</div>
                                <div class="text-sm text-base-content/60">Free booking (no charge)</div>
                            </div>
                        </label>
                        @endcan

                        {{-- Membership/Pack options will be loaded dynamically based on selected client --}}
                        <div id="client-payment-options" class="hidden space-y-3">
                            {{-- Will be populated via AJAX --}}
                        </div>
                    </div>
                </div>

                {{-- Options Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--settings] size-5 mr-2"></span>
                            Options
                        </h3>
                    </div>
                    <div class="card-body">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-primary">
                            <div>
                                <span class="font-medium">Check in client now</span>
                                <span class="text-sm text-base-content/60 block">Mark as arrived immediately after booking</span>
                            </div>
                        </label>

                        <div class="form-control mt-4">
                            <label class="label" for="notes"><span class="label-text">Notes (optional)</span></label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="Any notes about this booking..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="lg:col-span-1">
                <div class="card bg-base-100 border border-base-200 sticky top-4">
                    <div class="card-header">
                        <h3 class="card-title">Booking Summary</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Class</span>
                            <span class="font-medium">{{ $session->display_title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Date</span>
                            <span class="font-medium">{{ $session->start_time->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Time</span>
                            <span class="font-medium">{{ $session->start_time->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Price</span>
                            <span class="font-medium">${{ number_format($session->price ?? $session->classPlan->default_price ?? 0, 2) }}</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div id="summary-client" class="hidden">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Client</span>
                                <span class="font-medium" id="summary-client-name">--</span>
                            </div>
                        </div>

                        @if($spotsRemaining <= 0)
                        <div class="alert alert-warning">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <span>This class is at capacity. Booking will override limit.</span>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary btn-block" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            Confirm Booking
                        </button>

                        <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-block">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
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
        alert('Please enter first and last name');
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
    fetch(`/walk-in/payment-methods/${clientId}?class_plan_id=${classPlanId}`)
        .then(res => res.json())
        .then(data => {
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
                    html += `
                        <label class="flex items-start gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="payment_method" value="pack" class="radio radio-primary mt-0.5" data-pack-id="${pack.id}">
                            <div>
                                <div class="font-medium">Use Class Pack</div>
                                <div class="text-sm text-base-content/60">${pack.name} - ${pack.classes_remaining} classes remaining</div>
                            </div>
                            <input type="hidden" name="pack_id" value="${pack.id}" disabled>
                        </label>
                    `;
                });
            }

            if (html) {
                container.innerHTML = html;
                container.classList.remove('hidden');

                // Handle pack selection
                container.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        container.querySelectorAll('input[name="pack_id"]').forEach(i => i.disabled = true);
                        if (this.value === 'pack') {
                            this.closest('label').querySelector('input[name="pack_id"]').disabled = false;
                        }
                    });
                });
            }
        });
}
</script>
@endpush
@endsection
