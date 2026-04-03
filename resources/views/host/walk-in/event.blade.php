@extends('layouts.dashboard')

@section('title', $trans['page.add_attendee'] ?? 'Add Event Attendee')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('events.show', $event) }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['page.add_attendee'] ?? 'Add Event Attendee' }}</h1>
            <p class="text-base-content/60">{{ $trans['events.register_client'] ?? 'Register a client for this event' }}</p>
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

    {{-- Dynamic Error Container --}}
    <div id="form-error" class="alert alert-error mb-6 hidden">
        <span class="icon-[tabler--alert-circle] size-5 shrink-0"></span>
        <span id="form-error-message"></span>
        <button type="button" class="btn btn-sm btn-ghost btn-circle ml-auto" onclick="hideFormError()">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>

    {{-- Event Info Card --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-body">
            <div class="flex items-center gap-4">
                @if($event->cover_image)
                <div class="w-20 h-20 rounded-xl overflow-hidden">
                    <img src="{{ $event->cover_image }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-20 h-20 rounded-xl bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-event] size-8 text-error"></span>
                </div>
                @endif
                <div class="flex-1">
                    <h2 class="text-xl font-semibold">{{ $event->title }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar] size-4"></span>
                            {{ $event->start_datetime->format('l, M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}
                        </span>
                        @if($event->venue_name)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--map-pin] size-4"></span>
                            {{ $event->venue_name }}
                        </span>
                        @elseif($event->event_type === 'online')
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--device-laptop] size-4"></span>
                            Online Event
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="badge badge-soft badge-sm capitalize">
                            @if($event->event_type === 'in_person')
                                <span class="icon-[tabler--map-pin] size-3 mr-1"></span> In-Person
                            @elseif($event->event_type === 'online')
                                <span class="icon-[tabler--device-laptop] size-3 mr-1"></span> Online
                            @else
                                <span class="icon-[tabler--arrows-exchange] size-3 mr-1"></span> Hybrid
                            @endif
                        </span>
                        <span class="badge badge-soft badge-sm capitalize">
                            <span class="icon-[tabler--eye] size-3 mr-1"></span> {{ $event->visibility }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold {{ $spotsRemaining > 0 || !$event->capacity ? 'text-success' : 'text-error' }}">
                        {{ $spotsRemaining !== null ? $spotsRemaining : '∞' }}
                    </div>
                    <div class="text-sm text-base-content/60">{{ $trans['events.spots_left'] ?? 'spots left' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Registration Form --}}
    <form action="{{ route('walk-in.event.register', $event) }}" method="POST" id="event-registration-form">
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
                                        <div id="selected-avatar-img" class="avatar hidden">
                                            <div class="size-12 rounded-full">
                                                <img id="selected-avatar-src" src="" alt="">
                                            </div>
                                        </div>
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
                                @php
                                    $isAlreadyRegistered = $event->isClientRegistered($client->id);
                                @endphp
                                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg {{ $isAlreadyRegistered ? 'opacity-50' : 'hover:bg-base-200/50 cursor-pointer' }} transition-colors"
                                     @if(!$isAlreadyRegistered)
                                     onclick="selectClient({{ $client->id }}, '{{ $client->first_name }}', '{{ $client->last_name }}', '{{ $client->email }}', '{{ $client->phone }}', '{{ $client->avatar_url }}')"
                                     @endif>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :src="$client->avatar_url" :initials="$client->initials" :alt="$client->full_name" size="md" />
                                        <div>
                                            <div class="font-medium">{{ $client->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $client->email ?: $client->phone ?: ($trans['common.no_contact_info'] ?? 'No contact info') }}</div>
                                        </div>
                                    </div>
                                    @if($isAlreadyRegistered)
                                        <span class="badge badge-info badge-sm">Already Registered</span>
                                    @else
                                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                                    @endif
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

                        <button type="button" class="btn btn-outline btn-error btn-block" onclick="toggleQuickAdd()">
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
                            <button type="button" class="btn btn-error btn-block" onclick="createClient()">
                                <span class="icon-[tabler--check] size-5"></span>
                                {{ $trans['btn.create_select'] ?? 'Create & Select' }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Registration Options Card --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--settings] size-5 mr-2"></span>
                            {{ $trans['events.registration_options'] ?? 'Registration Options' }}
                        </h3>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Registration Status --}}
                        <div class="form-control">
                            <label class="label" for="status"><span class="label-text font-medium">{{ $trans['field.status'] ?? 'Registration Status' }}</span></label>
                            <select name="status" id="status" class="select select-bordered">
                                <option value="registered">{{ $trans['events.status.registered'] ?? 'Registered' }}</option>
                                <option value="confirmed">{{ $trans['events.status.confirmed'] ?? 'Confirmed' }}</option>
                                <option value="waitlisted" {{ $spotsRemaining !== null && $spotsRemaining <= 0 ? 'selected' : '' }}>{{ $trans['events.status.waitlisted'] ?? 'Waitlisted' }}</option>
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">{{ $trans['events.status_help'] ?? 'Choose the initial registration status for this attendee' }}</span>
                            </label>
                        </div>

                        {{-- Check In Now --}}
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="check_in_now" value="1" class="checkbox checkbox-error">
                            <div>
                                <span class="font-medium">{{ $trans['walk_in.check_in_now'] ?? 'Check in attendee now' }}</span>
                                <span class="text-sm text-base-content/60 block">{{ $trans['events.mark_arrived'] ?? 'Mark as arrived immediately after registration' }}</span>
                            </div>
                        </label>

                        {{-- Send Confirmation --}}
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="send_confirmation" value="1" checked class="checkbox checkbox-error">
                            <div>
                                <span class="font-medium">{{ $trans['events.send_confirmation'] ?? 'Send confirmation email' }}</span>
                                <span class="text-sm text-base-content/60 block">{{ $trans['events.send_confirmation_help'] ?? 'Email the attendee with event details' }}</span>
                            </div>
                        </label>

                        {{-- Notes --}}
                        <div class="form-control">
                            <label class="label" for="notes"><span class="label-text">{{ $trans['field.notes_optional'] ?? 'Notes (optional)' }}</span></label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="{{ $trans['events.notes_placeholder'] ?? 'Any notes about this registration...' }}"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Registration Summary Card --}}
                <div class="card bg-base-100 border border-base-200 sticky top-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $trans['events.registration_summary'] ?? 'Registration Summary' }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.event'] ?? 'Event' }}</span>
                            <span class="font-medium text-right max-w-[60%] truncate">{{ $event->title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.date'] ?? 'Date' }}</span>
                            <span class="font-medium">{{ $event->start_datetime->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.time'] ?? 'Time' }}</span>
                            <span class="font-medium">{{ $event->start_datetime->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.type'] ?? 'Type' }}</span>
                            <span class="font-medium capitalize">{{ str_replace('_', '-', $event->event_type) }}</span>
                        </div>

                        <div class="divider my-2"></div>

                        <div id="summary-client" class="hidden">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $trans['field.attendee'] ?? 'Attendee' }}</span>
                                <span class="font-medium" id="summary-client-name">--</span>
                            </div>
                        </div>

                        @if($spotsRemaining !== null && $spotsRemaining <= 0)
                        <div class="alert alert-warning">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <span>{{ $trans['events.event_at_capacity'] ?? 'This event is at capacity. Registration will be added to waitlist.' }}</span>
                        </div>
                        @endif

                        @include('components.read-to-client', ['rtcId' => 'event', 'rtcSubmitBtn' => 'submit-btn', 'rtcClass' => 'mb-4'])

                        <button type="submit" class="btn btn-error btn-block" id="submit-btn" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $trans['btn.register_attendee'] ?? 'Register Attendee' }}
                        </button>

                        <a href="{{ route('events.show', $event) }}" class="btn btn-ghost btn-block">
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

const registeredClientIds = @json($event->clients->pluck('id'));

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
            const isRegistered = registeredClientIds.includes(c.id);
            const avatarHtml = c.avatar_url
                ? `<div class="avatar"><div class="size-10 rounded-full"><img src="${c.avatar_url}" alt="${c.first_name}"></div></div>`
                : `<div class="avatar placeholder"><div class="bg-error text-error-content size-10 rounded-full"><span>${initials}</span></div></div>`;

            if (isRegistered) {
                return `
                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg opacity-50">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div>
                            <div class="font-medium">${c.first_name} ${c.last_name}</div>
                            <div class="text-sm text-base-content/60">${c.email || c.phone || 'No contact'}</div>
                        </div>
                    </div>
                    <span class="badge badge-info badge-sm">Already Registered</span>
                </div>
            `}

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
    // Check if already registered
    if (registeredClientIds.includes(id)) {
        showFormError('This client is already registered for this event.');
        return;
    }

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
</script>
@endpush
@endsection
