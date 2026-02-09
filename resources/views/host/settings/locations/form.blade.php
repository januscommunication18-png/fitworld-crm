@extends('layouts.settings')

@section('title', ($isEdit ? 'Edit' : 'Add') . ' Location — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.locations.index') }}">Locations</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }} Location</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $isEdit ? 'Edit Location' : 'Add Location' }}</h1>
            <p class="text-base-content/60 text-sm">{{ $isEdit ? 'Update location details' : 'Add a new location for classes and services' }}</p>
        </div>
        <a href="{{ route('settings.locations.index') }}" class="btn btn-ghost btn-sm">
            Back to Locations
        </a>
    </div>

    {{-- Form --}}
    <form
        method="POST"
        action="{{ $isEdit ? route('settings.locations.update', $location) : route('settings.locations.store') }}"
        id="location-form"
        class="space-y-4"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Basic Info Card --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                {{-- Location Name --}}
                <div>
                    <label class="label-text" for="name">Location Name <span class="text-error">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g. Main Studio, Central Park North, Zoom Room"
                        value="{{ old('name', $location->name ?? '') }}"
                        required
                    />
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location Types (multiselect) --}}
                <div>
                    <label class="label-text" for="location_types">Location Types <span class="text-error">*</span></label>
                    <select
                        id="location_types"
                        name="location_types[]"
                        multiple
                        class="hidden @error('location_types') input-error @enderror"
                        data-select='{
                            "placeholder": "Select location types...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'
                    >
                        @php
                            $selectedTypes = old('location_types', $location->location_types ?? []);
                        @endphp
                        @foreach($locationTypeOptions as $value => $label)
                        <option value="{{ $value }}" {{ is_array($selectedTypes) && in_array($value, $selectedTypes) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-base-content/60 text-sm mt-1">Select one or more types. Settings for each type will appear below.</p>
                    @error('location_types')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ========== IN-PERSON STUDIO SECTION ========== --}}
        <div id="in-person-section" class="card bg-base-100 hidden">
            <button type="button" class="card-header cursor-pointer hover:bg-base-200/50 transition-colors section-toggle" data-section="in_person">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--building] size-5 text-primary"></span>
                    <h3 class="card-title">In-Person Studio Settings</h3>
                </div>
                <span class="section-chevron icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
            </button>
            <div id="in-person-content" class="card-body border-t border-base-200 space-y-4">
                <p class="text-base-content/60 text-sm">Configure the physical address and details for your studio location.</p>

                {{-- Address Line 1 --}}
                <div>
                    <label class="label-text" for="address_line_1">Street Address <span class="text-error">*</span></label>
                    <input
                        id="address_line_1"
                        name="address_line_1"
                        type="text"
                        class="input w-full @error('address_line_1') input-error @enderror"
                        placeholder="123 Main Street"
                        value="{{ old('address_line_1', $location->address_line_1 ?? '') }}"
                    />
                    @error('address_line_1')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address Line 2 --}}
                <div>
                    <label class="label-text" for="address_line_2">Address Line 2</label>
                    <input
                        id="address_line_2"
                        name="address_line_2"
                        type="text"
                        class="input w-full"
                        placeholder="Suite 100, Floor 2, etc."
                        value="{{ old('address_line_2', $location->address_line_2 ?? '') }}"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- City --}}
                    <div>
                        <label class="label-text" for="city">City <span class="text-error">*</span></label>
                        <input
                            id="city"
                            name="city"
                            type="text"
                            class="input w-full @error('city') input-error @enderror"
                            value="{{ old('city', $location->city ?? '') }}"
                        />
                        @error('city')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- State --}}
                    <div>
                        <label class="label-text" for="state">State/Region <span class="text-error">*</span></label>
                        <input
                            id="state"
                            name="state"
                            type="text"
                            class="input w-full @error('state') input-error @enderror"
                            placeholder="e.g. TX, CA, ON"
                            value="{{ old('state', $location->state ?? '') }}"
                        />
                        @error('state')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Postal Code --}}
                    <div>
                        <label class="label-text" for="postal_code">Postal Code <span class="text-error">*</span></label>
                        <input
                            id="postal_code"
                            name="postal_code"
                            type="text"
                            class="input w-full @error('postal_code') input-error @enderror"
                            value="{{ old('postal_code', $location->postal_code ?? '') }}"
                        />
                        @error('postal_code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="label-text" for="country">Country <span class="text-error">*</span></label>
                        <select
                            id="country"
                            name="country"
                            class="select w-full @error('country') input-error @enderror"
                        >
                            <option value="">Select country...</option>
                            @foreach($countries as $code => $name)
                            <option value="{{ $code }}" {{ old('country', $location->country ?? 'US') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Internal Notes --}}
                <div>
                    <label class="label-text" for="notes">Internal Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        class="textarea w-full"
                        rows="2"
                        placeholder="Parking instructions, entry code, etc. (staff only, not shown to clients)"
                    >{{ old('notes', $location->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ========== PUBLIC LOCATION SECTION ========== --}}
        <div id="public-section" class="card bg-base-100 hidden">
            <button type="button" class="card-header cursor-pointer hover:bg-base-200/50 transition-colors section-toggle" data-section="public">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--trees] size-5 text-success"></span>
                    <h3 class="card-title">Public Location Settings</h3>
                </div>
                <span class="section-chevron icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
            </button>
            <div id="public-content" class="card-body border-t border-base-200 space-y-4">
                <p class="text-base-content/60 text-sm">Configure details for outdoor or public meeting locations like parks.</p>

                {{-- Meeting Instructions --}}
                <div>
                    <label class="label-text" for="public_location_notes">Meeting Instructions <span class="text-error">*</span></label>
                    <textarea
                        id="public_location_notes"
                        name="public_location_notes"
                        class="textarea w-full @error('public_location_notes') input-error @enderror"
                        rows="3"
                        placeholder="e.g. Meet near the fountain at the south entrance. Look for the instructor with a blue yoga mat. Bring your own mat and water."
                    >{{ old('public_location_notes', $location->public_location_notes ?? '') }}</textarea>
                    <p class="text-base-content/60 text-sm mt-1">These instructions will be shown to clients when they book.</p>
                    @error('public_location_notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Approximate Location (only if in-person is not selected) --}}
                <div id="public-address-fields" class="space-y-4">
                    <div class="divider text-xs text-base-content/50 my-2">Approximate Location (Optional)</div>
                    <p class="text-base-content/60 text-sm">Help clients find the general area. You don't need exact address details.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- City for public --}}
                        <div>
                            <label class="label-text" for="public_city">City</label>
                            <input
                                id="public_city"
                                type="text"
                                class="input w-full"
                                placeholder="New York"
                                value="{{ old('city', $location->city ?? '') }}"
                                data-mirror="city"
                            />
                        </div>

                        {{-- State for public --}}
                        <div>
                            <label class="label-text" for="public_state">State/Region</label>
                            <input
                                id="public_state"
                                type="text"
                                class="input w-full"
                                placeholder="NY"
                                value="{{ old('state', $location->state ?? '') }}"
                                data-mirror="state"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== VIRTUAL SECTION ========== --}}
        <div id="virtual-section" class="card bg-base-100 hidden">
            <button type="button" class="card-header cursor-pointer hover:bg-base-200/50 transition-colors section-toggle" data-section="virtual">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--video] size-5 text-info"></span>
                    <h3 class="card-title">Virtual Settings</h3>
                </div>
                <span class="section-chevron icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
            </button>
            <div id="virtual-content" class="card-body border-t border-base-200 space-y-4">
                <p class="text-base-content/60 text-sm">Configure video conferencing details for online sessions.</p>

                {{-- Virtual Platform --}}
                <div>
                    <label class="label-text" for="virtual_platform">Platform <span class="text-error">*</span></label>
                    <select
                        id="virtual_platform"
                        name="virtual_platform"
                        class="select w-full @error('virtual_platform') input-error @enderror"
                    >
                        <option value="">Select platform...</option>
                        @foreach(\App\Models\Location::getPlatformLabels() as $platform => $label)
                        <option value="{{ $platform }}" {{ old('virtual_platform', $location->virtual_platform ?? '') === $platform ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('virtual_platform')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Meeting Link --}}
                <div>
                    <label class="label-text" for="virtual_meeting_link">Meeting Link <span class="text-error">*</span></label>
                    <input
                        id="virtual_meeting_link"
                        name="virtual_meeting_link"
                        type="url"
                        class="input w-full @error('virtual_meeting_link') input-error @enderror"
                        placeholder="https://zoom.us/j/123456789"
                        value="{{ old('virtual_meeting_link', $location->virtual_meeting_link ?? '') }}"
                    />
                    @error('virtual_meeting_link')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Access Notes --}}
                <div>
                    <label class="label-text" for="virtual_access_notes">Access Notes</label>
                    <textarea
                        id="virtual_access_notes"
                        name="virtual_access_notes"
                        class="textarea w-full"
                        rows="2"
                        placeholder="Password: 123456, or any other access instructions"
                    >{{ old('virtual_access_notes', $location->virtual_access_notes ?? '') }}</textarea>
                </div>

                {{-- Hide Link Until Booking --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="hide_link_until_booking"
                            value="1"
                            class="checkbox checkbox-primary"
                            {{ old('hide_link_until_booking', $location->hide_link_until_booking ?? true) ? 'checked' : '' }}
                        />
                        <div>
                            <span class="label-text">Hide meeting link until booking</span>
                            <p class="text-base-content/60 text-xs">Clients will only see the link after they book a session.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ========== MOBILE/TRAVEL SECTION ========== --}}
        <div id="mobile-section" class="card bg-base-100 hidden">
            <button type="button" class="card-header cursor-pointer hover:bg-base-200/50 transition-colors section-toggle" data-section="mobile">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--car] size-5 text-warning"></span>
                    <h3 class="card-title">Mobile/Travel Studio Settings</h3>
                </div>
                <span class="section-chevron icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
            </button>
            <div id="mobile-content" class="card-body border-t border-base-200 space-y-4">
                <p class="text-base-content/60 text-sm">Configure details for mobile or travel-based sessions where you go to the client.</p>

                {{-- Service Area --}}
                <div>
                    <label class="label-text" for="mobile_service_area">Service Area</label>
                    <input
                        id="mobile_service_area"
                        name="mobile_service_area"
                        type="text"
                        class="input w-full"
                        placeholder="e.g. Downtown Austin, Within 10 miles of 78701"
                        value="{{ old('mobile_service_area', $location->mobile_service_area ?? '') }}"
                    />
                    <p class="text-base-content/60 text-sm mt-1">Describe the areas you're willing to travel to.</p>
                </div>

                {{-- Travel Notes --}}
                <div>
                    <label class="label-text" for="mobile_travel_notes">Travel Notes</label>
                    <textarea
                        id="mobile_travel_notes"
                        name="mobile_travel_notes"
                        class="textarea w-full"
                        rows="2"
                        placeholder="e.g. Additional travel fee may apply for locations over 5 miles away."
                    >{{ old('mobile_travel_notes', $location->mobile_travel_notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ========== CONTACT SECTION (Always visible when any type selected) ========== --}}
        <div id="contact-section" class="card bg-base-100 hidden">
            <button type="button" class="card-header cursor-pointer hover:bg-base-200/50 transition-colors section-toggle" data-section="contact">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--address-book] size-5 text-base-content/70"></span>
                    <h3 class="card-title">Contact Information</h3>
                    <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
                </div>
                <span class="section-chevron icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
            </button>
            <div id="contact-content" class="card-body border-t border-base-200 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Phone --}}
                    <div>
                        <label class="label-text" for="phone">Phone</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            class="input w-full"
                            placeholder="(555) 123-4567"
                            value="{{ old('phone', $location->phone ?? '') }}"
                        />
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="label-text" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="input w-full @error('email') input-error @enderror"
                            placeholder="location@studio.com"
                            value="{{ old('email', $location->email ?? '') }}"
                        />
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-start gap-2 pt-2">
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? 'Update Location' : 'Add Location' }}
            </button>
            <a href="{{ route('settings.locations.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationTypesSelect = document.getElementById('location_types');

    // Section elements
    const sections = {
        'in_person': document.getElementById('in-person-section'),
        'public': document.getElementById('public-section'),
        'virtual': document.getElementById('virtual-section'),
        'mobile': document.getElementById('mobile-section'),
        'contact': document.getElementById('contact-section')
    };

    const contents = {
        'in_person': document.getElementById('in-person-content'),
        'public': document.getElementById('public-content'),
        'virtual': document.getElementById('virtual-content'),
        'mobile': document.getElementById('mobile-content'),
        'contact': document.getElementById('contact-content')
    };

    // Track expanded state (all expanded by default)
    const expandedState = {
        'in_person': true,
        'public': true,
        'virtual': true,
        'mobile': true,
        'contact': true
    };

    // Toggle section expand/collapse
    document.querySelectorAll('.section-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const section = this.dataset.section;
            const content = contents[section];
            const chevron = this.querySelector('.section-chevron');

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.style.transform = 'rotate(0deg)';
                expandedState[section] = true;
            } else {
                content.classList.add('hidden');
                chevron.style.transform = 'rotate(-90deg)';
                expandedState[section] = false;
            }
        });
    });

    function getSelectedTypes() {
        return Array.from(locationTypesSelect.selectedOptions).map(opt => opt.value);
    }

    function updateFormFields() {
        const selectedTypes = getSelectedTypes();
        const hasInPerson = selectedTypes.includes('in_person');
        const hasPublic = selectedTypes.includes('public');
        const hasVirtual = selectedTypes.includes('virtual');
        const hasMobile = selectedTypes.includes('mobile');
        const hasAnyType = selectedTypes.length > 0;

        // Show/hide sections based on selected types
        sections['in_person'].classList.toggle('hidden', !hasInPerson);
        sections['public'].classList.toggle('hidden', !hasPublic);
        sections['virtual'].classList.toggle('hidden', !hasVirtual);
        sections['mobile'].classList.toggle('hidden', !hasMobile);
        sections['contact'].classList.toggle('hidden', !hasAnyType);

        // Set required fields based on selection
        document.getElementById('address_line_1').required = hasInPerson;
        document.getElementById('city').required = hasInPerson;
        document.getElementById('state').required = hasInPerson;
        document.getElementById('postal_code').required = hasInPerson;
        document.getElementById('country').required = hasInPerson;
        document.getElementById('public_location_notes').required = hasPublic;
        document.getElementById('virtual_platform').required = hasVirtual;
        document.getElementById('virtual_meeting_link').required = hasVirtual;

        // Show/hide public address fields (only if in-person is not selected)
        const publicAddressFields = document.getElementById('public-address-fields');
        if (publicAddressFields) {
            publicAddressFields.classList.toggle('hidden', hasInPerson);
        }

        // Mirror public city/state to main fields if in-person is not selected
        syncMirrorFields();

        // Update section content visibility based on expanded state
        Object.keys(sections).forEach(function(key) {
            if (!sections[key].classList.contains('hidden')) {
                const content = contents[key];
                const chevron = sections[key].querySelector('.section-chevron');
                if (expandedState[key]) {
                    content.classList.remove('hidden');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                } else {
                    content.classList.add('hidden');
                    if (chevron) chevron.style.transform = 'rotate(-90deg)';
                }
            }
        });
    }

    // Mirror fields from public section to main address fields
    function syncMirrorFields() {
        const selectedTypes = getSelectedTypes();
        const hasInPerson = selectedTypes.includes('in_person');

        if (!hasInPerson) {
            // Sync public city/state to main fields
            const publicCity = document.getElementById('public_city');
            const publicState = document.getElementById('public_state');
            const mainCity = document.getElementById('city');
            const mainState = document.getElementById('state');

            if (publicCity && mainCity) {
                publicCity.addEventListener('input', function() {
                    mainCity.value = this.value;
                });
                // Initial sync
                if (publicCity.value && !mainCity.value) {
                    mainCity.value = publicCity.value;
                }
            }

            if (publicState && mainState) {
                publicState.addEventListener('input', function() {
                    mainState.value = this.value;
                });
                // Initial sync
                if (publicState.value && !mainState.value) {
                    mainState.value = publicState.value;
                }
            }
        }
    }

    // Listen for changes on the select
    locationTypesSelect.addEventListener('change', updateFormFields);

    // Also listen for HSSelect changes (FlyonUI advance-select)
    const observer = new MutationObserver(updateFormFields);
    observer.observe(locationTypesSelect, { attributes: true, childList: true, subtree: true });

    // Initial state
    updateFormFields();
});
</script>
@endpush
