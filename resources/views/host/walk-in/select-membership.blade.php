@extends('layouts.dashboard')

@section('title', 'Sell Membership')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.calendar') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Calendar</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Sell Membership</li>
    </ol>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ $selectedClassSession ? route('membership-schedules.index') : route('schedule.calendar') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Sell Membership</h1>
            <p class="text-base-content/60">Sell a membership plan to a client</p>
        </div>
    </div>

    {{-- Selected Class Session Info --}}
    @if($selectedClassSession)
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--calendar-event] size-5"></span>
            <div>
                <h3 class="font-semibold">Booking for: {{ $selectedClassSession->display_title }}</h3>
                <p class="text-sm">
                    {{ $selectedClassSession->start_time->format('l, M j, Y \a\t g:i A') }}
                    @if($selectedClassSession->primaryInstructor)
                        &bull; {{ $selectedClassSession->primaryInstructor->name }}
                    @endif
                    @if($selectedClassSession->location)
                        &bull; {{ $selectedClassSession->location->name }}
                    @endif
                </p>
            </div>
        </div>
    @endif

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

    {{-- Session Error --}}
    @if (session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form id="membership-form" action="{{ route('walk-in.membership.book') }}" method="POST">
        @csrf

        @if($selectedClassSession)
            <input type="hidden" name="class_session_id" value="{{ $selectedClassSession->id }}">
        @endif

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
                                    <input type="text"
                                           id="client-search"
                                           class="input input-bordered w-full pl-10"
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
                                <span class="icon-[tabler--plus] size-4"></span>
                                Create Client
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

                {{-- Membership Plan Selection / Display --}}
                @if($preselectedMembershipPlanId && $selectedClassSession)
                    {{-- Pre-selected membership - show as fixed selection --}}
                    @php
                        $selectedPlan = $membershipPlans->firstWhere('id', $preselectedMembershipPlanId);
                    @endphp
                    @if($selectedPlan)
                        <div class="card bg-base-100 border border-primary/30 bg-primary/5">
                            <div class="card-body">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                                        <span class="icon-[tabler--id-badge-2] size-6 text-primary"></span>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold">{{ $selectedPlan->name }}</h2>
                                        <p class="text-sm text-base-content/60">Membership Enrollment</p>
                                    </div>
                                    <div class="ml-auto text-right">
                                        <div class="text-2xl font-bold text-primary">{{ $selectedPlan->getFormattedPriceForCurrency($defaultCurrency) }}</div>
                                        <div class="text-sm text-base-content/60">{{ $selectedPlan->formatted_interval }}</div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="badge badge-soft {{ $selectedPlan->type_badge_class }}">{{ $selectedPlan->formatted_type }}</span>
                                    @if($selectedPlan->type === 'credits')
                                        <span class="badge badge-soft badge-info">{{ $selectedPlan->credits_per_cycle }} credits/cycle</span>
                                    @endif
                                </div>

                                @if($selectedPlan->description)
                                    <p class="text-sm text-base-content/70 mt-3">{{ $selectedPlan->description }}</p>
                                @endif

                                <input type="hidden" name="membership_plan_id" value="{{ $selectedPlan->id }}"
                                       data-price="{{ $selectedPlan->getPriceForCurrency($defaultCurrency) }}"
                                       data-name="{{ $selectedPlan->name }}"
                                       id="preselected-plan">
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Regular membership plan selection --}}
                    <div class="card bg-base-100 border border-base-200">
                        <div class="card-body">
                            <h2 class="card-title mb-4">
                                <span class="icon-[tabler--id-badge-2] size-5"></span>
                                Select Membership Plan
                            </h2>

                            @if($membershipPlans->isEmpty())
                                <div class="text-center py-8">
                                    <span class="icon-[tabler--package-off] size-12 text-base-content/20"></span>
                                    <p class="text-base-content/60 mt-2">No active membership plans available.</p>
                                    <a href="{{ route('membership-plans.create') }}" class="btn btn-primary btn-sm mt-4">
                                        Create Membership Plan
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($membershipPlans as $plan)
                                        <label class="membership-plan-option flex flex-col p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" name="membership_plan_id" value="{{ $plan->id }}"
                                                           data-price="{{ $plan->getPriceForCurrency($defaultCurrency) }}"
                                                           data-name="{{ $plan->name }}"
                                                           class="radio radio-primary">
                                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $plan->color }}"></div>
                                                    <span class="font-semibold">{{ $plan->name }}</span>
                                                </div>
                                                <span class="badge badge-soft {{ $plan->type_badge_class }}">{{ $plan->formatted_type }}</span>
                                            </div>
                                            <div class="ml-7">
                                                <div class="text-lg font-bold text-primary">{{ $plan->getFormattedPriceForCurrency($defaultCurrency) }}{{ $plan->formatted_interval }}</div>
                                                @if($plan->description)
                                                    <p class="text-xs text-base-content/60 mt-1 line-clamp-2">{{ $plan->description }}</p>
                                                @endif
                                                @if($plan->type === 'credits')
                                                    <div class="text-xs text-base-content/60 mt-1">
                                                        {{ $plan->credits_per_cycle }} credits per cycle
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Start Date --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--calendar] size-5"></span>
                            Start Date
                        </h2>
                        <div class="form-control">
                            <input type="date" name="start_date" id="start_date"
                                   class="input input-bordered w-full max-w-xs"
                                   value="{{ now()->format('Y-m-d') }}"
                                   min="{{ now()->format('Y-m-d') }}">
                            <p class="text-xs text-base-content/60 mt-1">When should the membership start?</p>
                        </div>
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
                                <input type="radio" name="payment_method" value="manual" class="radio radio-primary" checked>
                                <span class="icon-[tabler--wallet] size-5 text-primary"></span>
                                <span class="font-medium">Manual Payment</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="payment_method" value="comp" class="radio radio-success">
                                <span class="icon-[tabler--gift] size-5 text-success"></span>
                                <span class="font-medium">Complimentary</span>
                            </label>
                        </div>

                        {{-- Manual Payment Options --}}
                        <div id="manual-payment-options" class="mt-4 space-y-3">
                            <div class="form-control">
                                <label class="label-text" for="manual_method">Payment Method</label>
                                <select name="manual_method" id="manual_method" class="select select-bordered w-full">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="check">Check</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label-text" for="price_paid">Amount Paid</label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$defaultCurrency] ?? '$' }}</span>
                                    <input type="number" name="price_paid" id="price_paid" step="0.01" min="0"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="form-control mt-4">
                            <label class="label-text" for="notes">Notes (optional)</label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="Payment notes..."></textarea>
                        </div>

                        @if($selectedClassSession)
                            {{-- Book into Class Option --}}
                            <div class="form-control mt-4 pt-4 border-t border-base-200">
                                <label class="cursor-pointer flex items-start gap-3">
                                    <input type="checkbox" name="book_into_class" value="1" class="checkbox checkbox-primary mt-0.5" checked>
                                    <div>
                                        <span class="font-medium">Also book into class</span>
                                        <p class="text-xs text-base-content/60">Book the client into "{{ $selectedClassSession->display_title }}" using this membership</p>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Summary --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--receipt] size-5"></span>
                            Summary
                        </h2>
                        <div id="summary-content" class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Client</span>
                                <span id="summary-client" class="font-medium">Not selected</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Membership</span>
                                <span id="summary-plan" class="font-medium">Not selected</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Start Date</span>
                                <span id="summary-date" class="font-medium">{{ now()->format('M j, Y') }}</span>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="flex justify-between text-lg">
                                <span class="font-semibold">Total</span>
                                <span id="summary-total" class="font-bold text-primary">$0.00</span>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="btn btn-primary w-full mt-4" disabled>
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
    const clientSearch = document.getElementById('client-search');
    const clientSearchResults = document.getElementById('client-search-results');
    const existingClientSection = document.getElementById('existing-client-section');
    const newClientSection = document.getElementById('new-client-section');
    const selectedClientDiv = document.getElementById('selected-client');
    const clientIdInput = document.getElementById('client_id');
    const submitBtn = document.getElementById('submit-btn');
    const manualPaymentOptions = document.getElementById('manual-payment-options');
    const pricePaidInput = document.getElementById('price_paid');
    const startDateInput = document.getElementById('start_date');

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

    // Payment method toggle
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'manual') {
                manualPaymentOptions.classList.remove('hidden');
            } else {
                manualPaymentOptions.classList.add('hidden');
            }
            updateSummary();
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
                        clientSearchResults.innerHTML = '<p class="text-base-content/60 text-sm p-2">No clients found</p>';
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
            alert('Please enter first and last name');
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

    // Membership plan selection
    document.querySelectorAll('input[name="membership_plan_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateSummary();
            updateSubmitButton();
        });
    });

    // Start date change
    startDateInput.addEventListener('change', updateSummary);

    function selectClient(client) {
        clientIdInput.value = client.id;
        document.getElementById('selected-client-initials').textContent = client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase();
        document.getElementById('selected-client-name').textContent = `${client.first_name} ${client.last_name}`;
        document.getElementById('selected-client-email').textContent = client.email || client.phone || '';

        selectedClientDiv.classList.remove('hidden');
        clientSearch.value = '';
        clientSearchResults.innerHTML = '';

        updateSummary();
        updateSubmitButton();
    }

    window.clearSelectedClient = function() {
        clientIdInput.value = '';
        selectedClientDiv.classList.add('hidden');
        updateSummary();
        updateSubmitButton();
    };

    function updateSummary() {
        // Client
        const clientName = document.getElementById('selected-client-name').textContent;
        document.getElementById('summary-client').textContent = clientIdInput.value ? clientName : 'Not selected';

        // Plan - check for both radio button (regular selection) and hidden input (pre-selected)
        let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
        const preselectedPlan = document.getElementById('preselected-plan');

        if (!selectedPlan && preselectedPlan) {
            selectedPlan = preselectedPlan;
        }

        if (selectedPlan) {
            document.getElementById('summary-plan').textContent = selectedPlan.dataset.name;
            const price = parseFloat(selectedPlan.dataset.price) || 0;

            // Update price field and summary based on payment method
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (paymentMethod === 'comp') {
                document.getElementById('summary-total').textContent = '$0.00 (Comp)';
            } else {
                document.getElementById('summary-total').textContent = '$' + price.toFixed(2);
                if (!pricePaidInput.value) {
                    pricePaidInput.value = price.toFixed(2);
                }
            }
        } else {
            document.getElementById('summary-plan').textContent = 'Not selected';
            document.getElementById('summary-total').textContent = '$0.00';
        }

        // Date
        const dateValue = startDateInput.value;
        if (dateValue) {
            const date = new Date(dateValue + 'T00:00:00');
            document.getElementById('summary-date').textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
    }

    function updateSubmitButton() {
        const hasClient = clientIdInput.value !== '';
        const hasPlan = document.querySelector('input[name="membership_plan_id"]:checked') !== null || document.getElementById('preselected-plan') !== null;
        submitBtn.disabled = !(hasClient && hasPlan);
    }

    // Initial update
    updateSummary();
    updateSubmitButton();

    // If a plan is pre-selected (either radio or hidden input), update the price field
    let initialPlan = document.querySelector('input[name="membership_plan_id"]:checked');
    if (!initialPlan) {
        initialPlan = document.getElementById('preselected-plan');
    }
    if (initialPlan) {
        const price = parseFloat(initialPlan.dataset.price) || 0;
        if (!pricePaidInput.value) {
            pricePaidInput.value = price.toFixed(2);
        }
    }
});
</script>
@endpush
@endsection
