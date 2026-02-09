@extends('layouts.settings')

@section('title', 'Locations — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Locations</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Locations</h1>
            <p class="text-base-content/60 text-sm">Manage your studio, public, and virtual locations (max 5)</p>
        </div>
        @if($locations->count() < 5)
        <a href="{{ route('settings.locations.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-4"></span> Add Location
        </a>
        @else
        <span class="badge badge-soft badge-neutral">Maximum locations reached</span>
        @endif
    </div>

    {{-- Info Alert --}}
    @if($locations->count() === 0)
    <div class="alert alert-soft alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <div class="font-medium">No locations yet</div>
            <div class="text-sm">Add your first location to enable booking addresses and room management.</div>
        </div>
    </div>
    @endif

    {{-- Locations List --}}
    <div class="space-y-4" id="locations-list">
        @forelse($locations as $location)
        <div class="card bg-base-100 overflow-visible" data-location-id="{{ $location->id }}">
            <div class="card-body overflow-visible">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 shrink-0">
                            <span class="{{ $location->type_icon }} size-6 text-primary"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold">{{ $location->name }}</h3>
                                @if($location->location_types && count($location->location_types) > 0)
                                    @foreach($location->location_types as $type)
                                    <span class="badge badge-soft badge-sm {{ match($type) {
                                        'in_person' => 'badge-primary',
                                        'public' => 'badge-success',
                                        'virtual' => 'badge-info',
                                        'mobile' => 'badge-warning',
                                        default => 'badge-neutral',
                                    } }}">{{ \App\Models\Location::getLocationTypeOptions()[$type] ?? $type }}</span>
                                    @endforeach
                                @else
                                    <span class="badge {{ $location->type_badge_class }} badge-soft badge-sm">{{ $location->type_label }}</span>
                                @endif
                                @if($location->is_default)
                                <span class="badge badge-primary badge-sm">Default</span>
                                @endif
                            </div>
                            <p class="text-sm text-base-content/70 mt-1">{{ $location->full_address }}</p>
                            @if($location->phone || $location->email)
                            <div class="flex items-center gap-4 mt-2 text-sm text-base-content/60">
                                @if($location->phone)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--phone] size-4"></span>
                                    {{ $location->phone }}
                                </span>
                                @endif
                                @if($location->email)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--mail] size-4"></span>
                                    {{ $location->email }}
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Stats --}}
                        <div class="flex items-center gap-4 text-sm text-base-content/60 mr-4">
                            @if($location->isInPerson())
                            <span class="flex items-center gap-1" title="Rooms">
                                <span class="icon-[tabler--door] size-4"></span>
                                {{ $location->rooms_count ?? 0 }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1" title="Classes (next 30 days)">
                                <span class="icon-[tabler--calendar] size-4"></span>
                                {{ $location->upcoming_classes_count ?? 0 }}
                            </span>
                        </div>

                        {{-- Actions Dropdown (CSS-only) --}}
                        <div class="relative">
                            <details class="dropdown dropdown-bottom dropdown-end">
                                <summary class="btn btn-ghost btn-sm btn-square list-none cursor-pointer">
                                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                    <li><a href="javascript:void(0)" onclick="viewLocation({{ $location->id }})">
                                        <span class="icon-[tabler--eye] size-4"></span> View
                                    </a></li>
                                    <li><a href="{{ route('settings.locations.edit', $location) }}">
                                        <span class="icon-[tabler--edit] size-4"></span> Edit
                                    </a></li>
                                    <li><a href="javascript:void(0)" onclick="toggleLocationStatus({{ $location->id }}, {{ $location->is_active ?? 1 }})">
                                        <span class="icon-[tabler--eye-off] size-4"></span> Mark as Inactive
                                    </a></li>
                                    <li><a href="javascript:void(0)" onclick="confirmDeleteLocation({{ $location->id }}, '{{ addslashes($location->name) }}', {{ $location->rooms_count ?? 0 }})" class="text-error">
                                        <span class="icon-[tabler--trash] size-4"></span> Delete
                                    </a></li>
                                </ul>
                            </details>
                        </div>
                    </div>
                </div>

                @if($location->notes)
                <div class="mt-4 p-3 bg-base-200 rounded-lg text-sm">
                    <span class="font-medium">Notes:</span> {{ $location->notes }}
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--map-pin-off] size-12 text-base-content/30 mx-auto"></span>
                <h3 class="font-medium mt-4">No locations added</h3>
                <p class="text-sm text-base-content/60 mt-1">Add your first location to get started</p>
                <a href="{{ route('settings.locations.create') }}" class="btn btn-primary btn-sm mt-4">
                    <span class="icon-[tabler--plus] size-4"></span> Add Location
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Location View Drawer --}}
<div id="location-drawer" class="fixed inset-0 z-[60] pointer-events-none">
    {{-- Backdrop --}}
    <div id="drawer-backdrop" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300" onclick="closeLocationDrawer()"></div>
    {{-- Drawer Panel --}}
    <div id="drawer-panel" class="absolute top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-semibold">Location Details</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeLocationDrawer()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            {{-- Content --}}
            <div class="flex-1 overflow-y-auto p-4" id="drawer-content">
                {{-- Loading State --}}
                <div id="drawer-loading" class="flex items-center justify-center py-12">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>
                {{-- Location Details --}}
                <div id="drawer-details" class="hidden space-y-6">
                    {{-- Location Info --}}
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 shrink-0">
                            <span class="icon-[tabler--map-pin] size-6 text-primary"></span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg" id="drawer-location-name"></h4>
                            <p class="text-sm text-base-content/70 mt-1" id="drawer-location-address"></p>
                        </div>
                    </div>

                    {{-- Location Types --}}
                    <div id="drawer-location-types-section" class="hidden">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-2">Location Types</h5>
                        <div class="flex flex-wrap gap-2" id="drawer-location-types"></div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="space-y-2" id="drawer-contact-section">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider">Contact</h5>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2" id="drawer-phone-row">
                                <span class="icon-[tabler--phone] size-4 text-base-content/60"></span>
                                <span id="drawer-location-phone"></span>
                            </div>
                            <div class="flex items-center gap-2" id="drawer-email-row">
                                <span class="icon-[tabler--mail] size-4 text-base-content/60"></span>
                                <span id="drawer-location-email"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div id="drawer-notes-section" class="hidden">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-2">Notes</h5>
                        <div class="p-3 bg-base-200 rounded-lg text-sm" id="drawer-location-notes"></div>
                    </div>

                    {{-- Virtual Location Info --}}
                    <div id="drawer-virtual-section" class="hidden">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-2">Virtual Details</h5>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--video] size-4 text-base-content/60"></span>
                                <span id="drawer-virtual-platform"></span>
                            </div>
                            <div id="drawer-virtual-notes-row" class="flex items-start gap-2">
                                <span class="icon-[tabler--note] size-4 text-base-content/60 mt-0.5"></span>
                                <span id="drawer-virtual-notes" class="text-sm"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Public Location Info --}}
                    <div id="drawer-public-section" class="hidden">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-2">Meeting Instructions</h5>
                        <div class="p-3 bg-base-200 rounded-lg text-sm" id="drawer-public-notes"></div>
                    </div>

                    {{-- Rooms (only for in-person) --}}
                    <div id="drawer-rooms-section">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-3">Rooms</h5>
                        <div id="drawer-rooms-list" class="space-y-2">
                            {{-- Rooms will be populated here --}}
                        </div>
                        <div id="drawer-no-rooms" class="hidden text-center py-6 border border-dashed border-base-content/20 rounded-lg">
                            <span class="icon-[tabler--door] size-8 text-base-content/30"></span>
                            <p class="text-sm text-base-content/60 mt-2">No rooms in this location</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="p-4 border-t border-base-200">
                <a href="#" id="drawer-edit-link" class="btn btn-primary w-full">
                    <span class="icon-[tabler--edit] size-4"></span> Edit Location
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="card bg-base-100 w-full max-w-md mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="flex items-center justify-center size-12 rounded-full bg-error/20 shrink-0">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Delete Location?</h3>
                    <p class="text-sm text-base-content/70 mt-2" id="delete-message">
                        Are you sure you want to delete this location? This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="flex justify-start gap-2 mt-6">
                <button type="button" class="btn btn-error" id="confirm-delete-btn">
                    <span class="loading loading-spinner loading-xs hidden" id="delete-spinner"></span>
                    Delete Location
                </button>
                <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var deleteLocationId = null;

// Location data for drawer
var locationsData = @json($locations->load('rooms'));

// View location drawer
function viewLocation(id) {
    // Close all open dropdowns
    document.querySelectorAll('details.dropdown[open]').forEach(function(d) { d.removeAttribute('open'); });

    var location = locationsData.find(function(loc) { return loc.id === id; });
    if (!location) return;

    // Show drawer
    openLocationDrawer();

    // Hide loading, show details
    document.getElementById('drawer-loading').classList.add('hidden');
    document.getElementById('drawer-details').classList.remove('hidden');

    // Populate location info
    document.getElementById('drawer-location-name').textContent = location.name;
    document.getElementById('drawer-location-address').textContent = location.full_address || [
        location.address_line_1,
        location.address_line_2,
        location.city,
        location.state,
        location.postal_code
    ].filter(Boolean).join(', ');

    // Location types
    var locationTypesSection = document.getElementById('drawer-location-types-section');
    var locationTypesContainer = document.getElementById('drawer-location-types');
    var typeLabels = { 'in_person': 'In-Person Studio', 'public': 'Public Location', 'virtual': 'Virtual', 'mobile': 'Mobile/Travel Studio' };
    var typeBadgeClasses = { 'in_person': 'badge-primary', 'public': 'badge-success', 'virtual': 'badge-info', 'mobile': 'badge-warning' };

    if (location.location_types && location.location_types.length > 0) {
        locationTypesSection.classList.remove('hidden');
        locationTypesContainer.innerHTML = '';
        location.location_types.forEach(function(type) {
            var badge = document.createElement('span');
            badge.className = 'badge badge-soft badge-sm ' + (typeBadgeClasses[type] || 'badge-neutral');
            badge.textContent = typeLabels[type] || type;
            locationTypesContainer.appendChild(badge);
        });
    } else {
        locationTypesSection.classList.add('hidden');
    }

    // Contact info
    var hasContact = location.phone || location.email;
    document.getElementById('drawer-contact-section').classList.toggle('hidden', !hasContact);

    if (location.phone) {
        document.getElementById('drawer-phone-row').classList.remove('hidden');
        document.getElementById('drawer-location-phone').textContent = location.phone;
    } else {
        document.getElementById('drawer-phone-row').classList.add('hidden');
    }

    if (location.email) {
        document.getElementById('drawer-email-row').classList.remove('hidden');
        document.getElementById('drawer-location-email').textContent = location.email;
    } else {
        document.getElementById('drawer-email-row').classList.add('hidden');
    }

    // Notes
    if (location.notes) {
        document.getElementById('drawer-notes-section').classList.remove('hidden');
        document.getElementById('drawer-location-notes').textContent = location.notes;
    } else {
        document.getElementById('drawer-notes-section').classList.add('hidden');
    }

    // Location type-specific sections
    var virtualSection = document.getElementById('drawer-virtual-section');
    var publicSection = document.getElementById('drawer-public-section');
    var roomsSection = document.getElementById('drawer-rooms-section');
    var roomsList = document.getElementById('drawer-rooms-list');
    var noRooms = document.getElementById('drawer-no-rooms');

    // Hide all type-specific sections first
    virtualSection.classList.add('hidden');
    publicSection.classList.add('hidden');
    roomsSection.classList.add('hidden');
    roomsList.innerHTML = '';

    // Check which types are selected
    var types = location.location_types || [location.location_type];
    var hasInPerson = types.includes('in_person');
    var hasPublic = types.includes('public');
    var hasVirtual = types.includes('virtual');

    // Show virtual info if virtual type is selected
    if (hasVirtual) {
        virtualSection.classList.remove('hidden');
        var platformLabels = { zoom: 'Zoom', google_meet: 'Google Meet', teams: 'Microsoft Teams', other: 'Other' };
        document.getElementById('drawer-virtual-platform').textContent = platformLabels[location.virtual_platform] || location.virtual_platform || 'Not specified';

        if (location.virtual_access_notes) {
            document.getElementById('drawer-virtual-notes-row').classList.remove('hidden');
            document.getElementById('drawer-virtual-notes').textContent = location.virtual_access_notes;
        } else {
            document.getElementById('drawer-virtual-notes-row').classList.add('hidden');
        }
    }

    // Show public location info if public type is selected
    if (hasPublic) {
        publicSection.classList.remove('hidden');
        document.getElementById('drawer-public-notes').textContent = location.public_location_notes || 'No instructions provided.';
    }

    // Show rooms for in-person type
    if (hasInPerson) {
        roomsSection.classList.remove('hidden');
        if (location.rooms && location.rooms.length > 0) {
            noRooms.classList.add('hidden');
            location.rooms.forEach(function(room) {
                var roomHtml = '<div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">' +
                    '<div class="flex items-center gap-3">' +
                    '<span class="icon-[tabler--door] size-5 text-base-content/60"></span>' +
                    '<div>' +
                    '<div class="font-medium">' + room.name + '</div>' +
                    '<div class="text-sm text-base-content/60">Capacity: ' + room.capacity + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<span class="badge badge-' + (room.is_active ? 'success' : 'neutral') + ' badge-soft badge-sm">' + (room.is_active ? 'Active' : 'Inactive') + '</span>' +
                    '</div>';
                roomsList.insertAdjacentHTML('beforeend', roomHtml);
            });
        } else {
            noRooms.classList.remove('hidden');
        }
    }

    // Edit link
    document.getElementById('drawer-edit-link').href = '/settings/locations/' + location.id + '/edit';
}

function openLocationDrawer() {
    var drawer = document.getElementById('location-drawer');
    var backdrop = document.getElementById('drawer-backdrop');
    var panel = document.getElementById('drawer-panel');

    drawer.classList.remove('pointer-events-none');
    drawer.classList.add('pointer-events-auto');
    backdrop.classList.remove('opacity-0');
    backdrop.classList.add('opacity-100');
    panel.classList.remove('translate-x-full');
    panel.classList.add('translate-x-0');

    // Reset to loading state
    document.getElementById('drawer-loading').classList.remove('hidden');
    document.getElementById('drawer-details').classList.add('hidden');
}

function closeLocationDrawer() {
    var drawer = document.getElementById('location-drawer');
    var backdrop = document.getElementById('drawer-backdrop');
    var panel = document.getElementById('drawer-panel');

    backdrop.classList.add('opacity-0');
    backdrop.classList.remove('opacity-100');
    panel.classList.add('translate-x-full');
    panel.classList.remove('translate-x-0');

    setTimeout(function() {
        drawer.classList.add('pointer-events-none');
        drawer.classList.remove('pointer-events-auto');
    }, 300);
}

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

// Delete modal
function confirmDeleteLocation(id, name, roomsCount) {
    deleteLocationId = id;
    var message = 'Are you sure you want to delete <strong>"' + name + '"</strong>? This action cannot be undone.';
    if (roomsCount > 0) {
        message = 'This location has <strong>' + roomsCount + ' room(s)</strong> assigned. Please reassign or delete the rooms before deleting this location.';
        document.getElementById('confirm-delete-btn').disabled = true;
    } else {
        document.getElementById('confirm-delete-btn').disabled = false;
    }
    document.getElementById('delete-message').innerHTML = message;
    openDeleteModal();
}

function openDeleteModal() {
    var modal = document.getElementById('delete-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeDeleteModal() {
    var modal = document.getElementById('delete-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
    deleteLocationId = null;
}

// Toggle location status (active/inactive)
function toggleLocationStatus(id, currentStatus) {
    // TODO: Implement when is_active field is added
    showToast('Status toggle coming soon', 'info');
}

// Delete location
document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    if (!deleteLocationId) return;

    var btn = this;
    var spinner = document.getElementById('delete-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('/settings/locations/' + deleteLocationId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            closeDeleteModal();
            showToast('Location deleted');
            location.reload();
        } else {
            showToast(result.message || 'Failed to delete', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeLocationDrawer();
    }
});
</script>
@endpush
