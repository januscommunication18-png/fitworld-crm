@extends('layouts.dashboard')

@section('title', 'Create Ticket')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('helpdesk.index') }}"><span class="icon-[tabler--help] me-1 size-4"></span> Help Desk</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Ticket</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Create Ticket</h1>
            <p class="text-base-content/60 mt-1">Manually create a new support ticket.</p>
        </div>
        <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route('helpdesk.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            {{-- Contact Information --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Contact Information</h2>

                    {{-- Existing / New client toggle --}}
                    <div id="helpdesk-client-type-selection" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all" for="helpdesk_client_type_existing">
                            <input type="radio" id="helpdesk_client_type_existing" name="client_type" value="existing" class="radio radio-primary" {{ old('client_type', 'existing') === 'existing' ? 'checked' : '' }}>
                            <span class="icon-[tabler--users] size-6 text-primary"></span>
                            <div>
                                <span class="font-semibold block">Existing Client</span>
                                <span class="text-xs text-base-content/60">Search your client list</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all" for="helpdesk_client_type_new">
                            <input type="radio" id="helpdesk_client_type_new" name="client_type" value="new" class="radio radio-success" {{ old('client_type') === 'new' ? 'checked' : '' }}>
                            <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                            <div>
                                <span class="font-semibold block">New Contact</span>
                                <span class="text-xs text-base-content/60">Enter contact details manually</span>
                            </div>
                        </label>
                    </div>

                    {{-- Existing Client: search --}}
                    <div id="helpdesk-existing-client-section" class="{{ old('client_type', 'existing') !== 'existing' ? 'hidden' : '' }} space-y-3 mb-4">
                        <div>
                            <label class="label-text" for="helpdesk-client-search">Search Clients</label>
                            <div class="relative">
                                <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                <input type="text" id="helpdesk-client-search" class="input w-full pl-10"
                                       placeholder="Search by name, email or phone...">
                            </div>
                            <div id="helpdesk-client-search-results" class="space-y-2 mt-3"></div>
                        </div>

                        {{-- Selected Client Display --}}
                        <div id="helpdesk-selected-client" class="hidden p-4 bg-primary/5 border border-primary/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold flex items-center justify-center">
                                            <span id="helpdesk-selected-client-initials">JD</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="helpdesk-selected-client-name" class="font-semibold">John Doe</div>
                                        <div id="helpdesk-selected-client-email" class="text-sm text-base-content/60">john@example.com</div>
                                    </div>
                                </div>
                                <button type="button" id="helpdesk-clear-selected-client" class="btn btn-ghost btn-sm btn-circle" aria-label="Clear selected client">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden client_id, populated when a client is picked --}}
                    <input type="hidden" name="client_id" id="helpdesk_client_id" value="{{ old('client_id') }}">

                    {{-- Contact fields: editable in "new" mode, read-only-ish in "existing" once selected --}}
                    <div id="helpdesk-contact-fields" class="space-y-4">
                        <div>
                            <label class="label-text" for="name">Name <span class="text-error">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="input w-full @error('name') input-error @enderror" required>
                            @error('name')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="email">Email <span class="text-error">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="input w-full @error('email') input-error @enderror" required>
                                @error('email')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-phone-input name="phone" :value="old('phone')" label="Phone" id-suffix="helpdesk" />
                                @error('phone')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Ticket Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="source_type">Source <span class="text-error">*</span></label>
                            <select id="source_type" name="source_type"
                                    class="select w-full @error('source_type') select-error @enderror" required>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" {{ old('source_type', 'manual') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_type')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                   class="input w-full @error('subject') input-error @enderror"
                                   placeholder="Brief description of the request">
                            @error('subject')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="message">Message</label>
                            <textarea id="message" name="message" rows="4"
                                      class="textarea w-full @error('message') textarea-error @enderror"
                                      placeholder="Detailed information about the request...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service Request Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Service Request (Optional)</h2>
                    <div class="space-y-4">
                        @php
                            $selectedAlias = old('requested_type_alias');
                            $selectedOfferingId = old('requested_offering_id');
                        @endphp

                        {{-- Step 1: Type chooser --}}
                        <div>
                            <label class="label-text" for="requested_type_alias">Requested Type</label>
                            <x-studio-select
                                name="requested_type_alias"
                                id="requested_type_alias"
                                placeholder="-- No specific offering --"
                                :option-count="count($offeringsByType)">
                                <option value="">-- No specific offering --</option>
                                @foreach($offeringsByType as $alias => $group)
                                    <option value="{{ $alias }}" {{ $selectedAlias === $alias ? 'selected' : '' }}>
                                        {{ $group['label'] }}
                                    </option>
                                @endforeach
                            </x-studio-select>
                            @error('requested_type_alias')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 2: Per-type offering picker. Only the one matching the
                              chosen type is shown. Each inner picker uses a scratch name
                              (`__inner_<alias>`); a single hidden input below carries the
                              canonical `requested_offering_id` value that the form posts. --}}
                        @if(!empty($offeringsByType))
                            <input type="hidden" name="requested_offering_id" id="requested_offering_id" value="{{ $selectedOfferingId }}">
                            <div id="requested-offering-pickers" class="{{ $selectedAlias ? '' : 'hidden' }}">
                                <label class="label-text">Choose</label>
                                @foreach($offeringsByType as $alias => $group)
                                    @php $isActiveAlias = $selectedAlias === $alias; @endphp
                                    <div
                                        data-offering-picker="{{ $alias }}"
                                        class="{{ $isActiveAlias ? '' : 'hidden' }}"
                                    >
                                        <x-studio-select
                                            :name="'__inner_' . $alias"
                                            :id="'requested_offering_id_' . $alias"
                                            :placeholder="'Select a ' . strtolower($group['label']) . '...'"
                                            :option-count="$group['items']->count()">
                                            <option value="">-- Select a {{ strtolower($group['label']) }} --</option>
                                            @foreach($group['items'] as $item)
                                                <option value="{{ $item->id }}" {{ $isActiveAlias && (string)$selectedOfferingId === (string)$item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </x-studio-select>
                                    </div>
                                @endforeach
                                @error('requested_offering_id')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <p class="text-xs text-base-content/60">No active catalog offerings yet — add some in <a href="{{ route('catalog.index') }}" class="link link-primary">Catalog</a> to enable this picker.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Assignment --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Assignment</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="assigned_user_id">Assign To</label>
                            <x-studio-select
                                name="assigned_user_id"
                                id="assigned_user_id"
                                placeholder="-- Unassigned --"
                                :option-count="$teamMembers->count()">
                                <option value="">-- Unassigned --</option>
                                @foreach($teamMembers as $member)
                                    <option value="{{ $member->id }}" {{ old('assigned_user_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </x-studio-select>
                            @error('assigned_user_id')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($tags->count() > 0)
                            <div>
                                <label class="label-text mb-2 block">Tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer hidden"
                                                   {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                            <span class="badge peer-checked:badge-primary transition-colors" style="--badge-color: {{ $tag->color }}">
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2">
                <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Create Ticket
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="client_type"]');
    const existingSection = document.getElementById('helpdesk-existing-client-section');
    const searchInput = document.getElementById('helpdesk-client-search');
    const resultsBox = document.getElementById('helpdesk-client-search-results');
    const selectedBox = document.getElementById('helpdesk-selected-client');
    const clearBtn = document.getElementById('helpdesk-clear-selected-client');
    const clientIdInput = document.getElementById('helpdesk_client_id');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    // x-phone-input renders the tel <input> with id phone_input_<idSuffix> — but
    // since the name is unique on this page, the name lookup is the safest bet.
    const phoneInput = document.querySelector('input[name="phone"]');

    function setMode(mode) {
        if (mode === 'existing') {
            existingSection.classList.remove('hidden');
        } else {
            existingSection.classList.add('hidden');
            clearSelectedClient();
        }
    }

    function lockContactFields(lock) {
        [nameInput, emailInput, phoneInput].forEach(el => {
            if (!el) return;
            el.readOnly = lock;
            el.classList.toggle('opacity-70', lock);
        });
    }

    function applyClient(client) {
        const fullName = [client.first_name, client.last_name].filter(Boolean).join(' ').trim();
        const initials = client.initials || (fullName.split(/\s+/).map(p => p[0]).slice(0,2).join('') || 'C').toUpperCase();

        clientIdInput.value = client.id;
        nameInput.value = fullName;
        emailInput.value = client.email || '';
        if (phoneInput) phoneInput.value = client.phone || '';

        document.getElementById('helpdesk-selected-client-initials').textContent = initials;
        document.getElementById('helpdesk-selected-client-name').textContent = fullName || 'Client';
        document.getElementById('helpdesk-selected-client-email').textContent = client.email || client.phone || '';
        selectedBox.classList.remove('hidden');

        searchInput.value = '';
        resultsBox.innerHTML = '';

        lockContactFields(true);
    }

    function clearSelectedClient() {
        clientIdInput.value = '';
        selectedBox.classList.add('hidden');
        resultsBox.innerHTML = '';
        if (searchInput) searchInput.value = '';
        lockContactFields(false);
    }

    radios.forEach(r => r.addEventListener('change', e => setMode(e.target.value)));

    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) { resultsBox.innerHTML = ''; return; }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('walk-in.clients.search') }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (!data.clients || data.clients.length === 0) {
                            resultsBox.innerHTML = '<p class="text-base-content/60 text-sm p-2">No clients found</p>';
                            return;
                        }
                        data.clients.forEach(client => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center gap-3 p-3 bg-base-200/50 rounded-lg cursor-pointer hover:bg-base-200 transition-colors';
                            const fname = (client.first_name || '').replace(/[<>]/g, '');
                            const lname = (client.last_name || '').replace(/[<>]/g, '');
                            const initials = client.initials || ((fname[0] || '') + (lname[0] || '')).toUpperCase() || 'C';
                            const contact = (client.email || client.phone || '').replace(/[<>]/g, '');
                            div.innerHTML = `
                                <div class="avatar avatar-placeholder">
                                    <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm flex items-center justify-center">
                                        ${initials}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">${fname} ${lname}</div>
                                    <div class="text-xs text-base-content/60">${contact}</div>
                                </div>
                            `;
                            div.addEventListener('click', () => applyClient(client));
                            resultsBox.appendChild(div);
                        });
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<p class="text-error text-sm p-2">Search failed. Try again.</p>';
                    });
            }, 300);
        });
    }

    if (clearBtn) clearBtn.addEventListener('click', clearSelectedClient);

    // Initialize: if old('client_id') already set (validation failure round-trip), lock fields.
    if (clientIdInput.value) lockContactFields(true);
    // Initialize mode from the radio that's checked.
    const checked = document.querySelector('input[name="client_type"]:checked');
    if (checked) setMode(checked.value);

    // ─── Two-step Requested Service picker ────────────────────────────────
    // The type select drives which inner picker is shown. Each inner picker
    // uses a scratch name (`__inner_<alias>`); on change we copy the value
    // into the canonical hidden #requested_offering_id input that the form
    // actually posts. Switching type clears the previous selection so the
    // controller never receives a value from the wrong type.
    const typeSelect = document.getElementById('requested_type_alias');
    const offeringHidden = document.getElementById('requested_offering_id');
    const pickersWrap = document.getElementById('requested-offering-pickers');

    if (typeSelect && offeringHidden && pickersWrap) {
        const pickers = pickersWrap.querySelectorAll('[data-offering-picker]');

        function showPickerFor(alias) {
            pickers.forEach(p => {
                const matches = p.dataset.offeringPicker === alias;
                p.classList.toggle('hidden', !matches);
                if (!matches) {
                    // Reset inactive inner selects so their value doesn't linger.
                    const innerSelect = p.querySelector('select');
                    if (innerSelect && innerSelect.value) {
                        innerSelect.value = '';
                        innerSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
            pickersWrap.classList.toggle('hidden', !alias);
        }

        typeSelect.addEventListener('change', function () {
            const alias = this.value;
            offeringHidden.value = '';
            showPickerFor(alias);
        });

        // Each inner select writes to the single hidden input when changed.
        pickers.forEach(picker => {
            const innerSelect = picker.querySelector('select');
            if (!innerSelect) return;
            innerSelect.addEventListener('change', function () {
                if (picker.dataset.offeringPicker === typeSelect.value) {
                    offeringHidden.value = this.value || '';
                }
            });
        });

        // Initial state (handles validation round-trip).
        showPickerFor(typeSelect.value);
    }
});
</script>
@endpush
