@extends('layouts.settings')

@section('title', 'Rooms — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Rooms</li>
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
            <h1 class="text-xl font-semibold">Rooms</h1>
            <p class="text-base-content/60 text-sm">Manage your studio rooms and their capacities</p>
        </div>
        @if($locations->isNotEmpty())
        <div class="flex items-center gap-3">
            @if($locations->count() > 1)
            <select
                id="location-filter"
                data-select='{
                    "hasSearch": true,
                    "searchPlaceholder": "Search locations...",
                    "placeholder": "All Locations",
                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                    "toggleClasses": "advance-select-toggle w-48",
                    "dropdownClasses": "advance-select-menu max-h-48 overflow-y-auto",
                    "optionClasses": "advance-select-option selected:select-active",
                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                }'
                class="hidden"
            >
                <option value="">All Locations</option>
                @foreach($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
            @endif
            <a href="{{ route('settings.rooms.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-4"></span> Add Room
            </a>
        </div>
        @endif
    </div>

    {{-- No Locations Warning --}}
    @if($locations->isEmpty())
    <div class="alert alert-soft alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <div class="font-medium">No locations available</div>
            <div class="text-sm">You need to add a location before you can create rooms.</div>
        </div>
        <a href="{{ route('settings.locations.create') }}" class="btn btn-warning btn-sm">Add Location</a>
    </div>
    @endif

    {{-- Rooms by Location --}}
    @if($locations->isNotEmpty())
        @forelse($locations as $location)
        <div class="card bg-base-100 overflow-visible location-card" data-location-id="{{ $location->id }}">
            <div class="card-body overflow-visible">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10">
                            <span class="icon-[tabler--map-pin] size-5 text-primary"></span>
                        </div>
                        <div>
                            <h2 class="font-semibold">{{ $location->name }}</h2>
                            <p class="text-base-content/60 text-sm">{{ $location->rooms->count() }} room(s)</p>
                        </div>
                    </div>
                </div>

                @if($location->rooms->isEmpty())
                <div class="text-center py-8 border border-dashed border-base-content/20 rounded-lg">
                    <span class="icon-[tabler--door] size-10 text-base-content/30"></span>
                    <p class="text-base-content/60 text-sm mt-2">No rooms in this location</p>
                    <a href="{{ route('settings.rooms.create') }}?location={{ $location->id }}" class="btn btn-ghost btn-sm mt-3">
                        <span class="icon-[tabler--plus] size-4"></span> Add Room
                    </a>
                </div>
                @else
                <div class="overflow-visible">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Room Name</th>
                                <th>Capacity</th>
                                <th>Amenities</th>
                                <th>Status</th>
                                <th class="w-20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($location->rooms as $room)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $room->name }}</div>
                                    @if($room->description)
                                    <div class="text-sm text-base-content/60">{{ Str::limit($room->description, 50) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                        {{ $room->capacity }}
                                    </div>
                                </td>
                                <td>
                                    @if($room->amenities && count($room->amenities) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($room->amenities, 0, 3) as $amenity)
                                        <span class="badge badge-soft badge-xs">{{ $amenitiesList[$amenity] ?? $amenity }}</span>
                                        @endforeach
                                        @if(count($room->amenities) > 3)
                                        <span class="badge badge-soft badge-xs">+{{ count($room->amenities) - 3 }}</span>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-base-content/40 text-sm">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($room->is_active)
                                    <span class="badge badge-success badge-soft badge-sm">Active</span>
                                    @else
                                    <span class="badge badge-neutral badge-soft badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="relative">
                                        <details class="dropdown dropdown-bottom dropdown-end">
                                            <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                                <li><a href="javascript:void(0)" onclick="viewRoom({{ $room->id }})">
                                                    <span class="icon-[tabler--eye] size-4"></span> View
                                                </a></li>
                                                <li><a href="{{ route('settings.rooms.edit', $room) }}">
                                                    <span class="icon-[tabler--edit] size-4"></span> Edit
                                                </a></li>
                                                <li><a href="javascript:void(0)" onclick="toggleRoomStatus({{ $room->id }}, {{ $room->is_active ? 1 : 0 }})">
                                                    <span class="icon-[tabler--{{ $room->is_active ? 'eye-off' : 'eye' }}] size-4"></span>
                                                    {{ $room->is_active ? 'Deactivate' : 'Activate' }}
                                                </a></li>
                                                <li><a href="javascript:void(0)" onclick="confirmDeleteRoom({{ $room->id }}, '{{ addslashes($room->name) }}')" class="text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </a></li>
                                            </ul>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--door] size-12 text-base-content/30 mx-auto"></span>
                <h3 class="font-medium mt-4">No rooms yet</h3>
                <p class="text-sm text-base-content/60 mt-1">Add rooms to manage class capacities and scheduling</p>
            </div>
        </div>
        @endforelse
    @endif
</div>

{{-- Room View Drawer --}}
<div id="room-drawer" class="fixed inset-0 z-[60] pointer-events-none">
    {{-- Backdrop --}}
    <div id="drawer-backdrop" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300" onclick="closeRoomDrawer()"></div>
    {{-- Drawer Panel --}}
    <div id="drawer-panel" class="absolute top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-semibold">Room Details</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeRoomDrawer()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            {{-- Content --}}
            <div class="flex-1 overflow-y-auto p-4" id="drawer-content">
                {{-- Loading State --}}
                <div id="drawer-loading" class="flex items-center justify-center py-12">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>
                {{-- Room Details --}}
                <div id="drawer-details" class="hidden space-y-6">
                    {{-- Room Info --}}
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 shrink-0">
                            <span class="icon-[tabler--door] size-6 text-primary"></span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg" id="drawer-room-name"></h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="badge badge-soft badge-sm" id="drawer-room-status"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Capacity & Dimensions --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-sm text-base-content/60">Capacity</div>
                            <div class="font-semibold text-lg flex items-center gap-1">
                                <span class="icon-[tabler--users] size-5"></span>
                                <span id="drawer-room-capacity"></span>
                            </div>
                        </div>
                        <div class="p-3 bg-base-200 rounded-lg" id="drawer-dimensions-box">
                            <div class="text-sm text-base-content/60">Dimensions</div>
                            <div class="font-semibold" id="drawer-room-dimensions"></div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div id="drawer-description-section" class="hidden">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-2">Description</h5>
                        <p class="text-sm" id="drawer-room-description"></p>
                    </div>

                    {{-- Amenities --}}
                    <div id="drawer-amenities-section">
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-3">Amenities</h5>
                        <div class="flex flex-wrap gap-2" id="drawer-room-amenities"></div>
                        <div id="drawer-no-amenities" class="hidden text-sm text-base-content/60">No amenities listed</div>
                    </div>

                    {{-- Location --}}
                    <div>
                        <h5 class="font-medium text-sm text-base-content/60 uppercase tracking-wider mb-3">Location</h5>
                        <div class="flex items-start gap-3 p-3 bg-base-200 rounded-lg">
                            <span class="icon-[tabler--map-pin] size-5 text-primary shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium" id="drawer-location-name"></div>
                                <div class="text-sm text-base-content/60" id="drawer-location-address"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="p-4 border-t border-base-200">
                <a href="#" id="drawer-edit-link" class="btn btn-primary w-full">
                    <span class="icon-[tabler--edit] size-4"></span> Edit Room
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
                    <h3 class="text-lg font-semibold">Delete Room?</h3>
                    <p class="text-sm text-base-content/70 mt-2" id="delete-message">
                        Are you sure you want to delete this room? This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="flex justify-start gap-2 mt-6">
                <button type="button" class="btn btn-error" id="confirm-delete-btn">
                    <span class="loading loading-spinner loading-xs hidden" id="delete-spinner"></span>
                    Delete Room
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
var deleteRoomId = null;

// Room and location data for drawer
var locationsData = @json($locations);
var amenitiesList = @json($amenitiesList);

// Build a flat list of rooms with their location
var roomsData = [];
locationsData.forEach(function(loc) {
    if (loc.rooms) {
        loc.rooms.forEach(function(room) {
            room.location = loc;
            roomsData.push(room);
        });
    }
});

// View room drawer
function viewRoom(id) {
    // Close all open dropdowns
    document.querySelectorAll('details.dropdown[open]').forEach(function(d) { d.removeAttribute('open'); });

    var room = roomsData.find(function(r) { return r.id === id; });
    if (!room) return;

    // Show drawer
    openRoomDrawer();

    // Hide loading, show details
    document.getElementById('drawer-loading').classList.add('hidden');
    document.getElementById('drawer-details').classList.remove('hidden');

    // Populate room info
    document.getElementById('drawer-room-name').textContent = room.name;

    // Status
    var statusEl = document.getElementById('drawer-room-status');
    if (room.is_active) {
        statusEl.className = 'badge badge-success badge-soft badge-sm';
        statusEl.textContent = 'Active';
    } else {
        statusEl.className = 'badge badge-neutral badge-soft badge-sm';
        statusEl.textContent = 'Inactive';
    }

    // Capacity
    document.getElementById('drawer-room-capacity').textContent = room.capacity;

    // Dimensions
    if (room.dimensions) {
        document.getElementById('drawer-dimensions-box').classList.remove('hidden');
        document.getElementById('drawer-room-dimensions').textContent = room.dimensions;
    } else {
        document.getElementById('drawer-dimensions-box').classList.add('hidden');
    }

    // Description
    if (room.description) {
        document.getElementById('drawer-description-section').classList.remove('hidden');
        document.getElementById('drawer-room-description').textContent = room.description;
    } else {
        document.getElementById('drawer-description-section').classList.add('hidden');
    }

    // Amenities
    var amenitiesContainer = document.getElementById('drawer-room-amenities');
    var noAmenities = document.getElementById('drawer-no-amenities');
    amenitiesContainer.innerHTML = '';

    if (room.amenities && room.amenities.length > 0) {
        noAmenities.classList.add('hidden');
        room.amenities.forEach(function(amenity) {
            var label = amenitiesList[amenity] || amenity;
            amenitiesContainer.insertAdjacentHTML('beforeend',
                '<span class="badge badge-soft badge-sm">' + label + '</span>'
            );
        });
    } else {
        noAmenities.classList.remove('hidden');
    }

    // Location
    if (room.location) {
        document.getElementById('drawer-location-name').textContent = room.location.name;
        var address = [
            room.location.address_line_1,
            room.location.city,
            room.location.state,
            room.location.postal_code
        ].filter(Boolean).join(', ');
        document.getElementById('drawer-location-address').textContent = address;
    }

    // Edit link
    document.getElementById('drawer-edit-link').href = '/settings/locations/rooms/' + room.id + '/edit';
}

function openRoomDrawer() {
    var drawer = document.getElementById('room-drawer');
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

function closeRoomDrawer() {
    var drawer = document.getElementById('room-drawer');
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

// Filter by location
function filterByLocation(locationId) {
    var cards = document.querySelectorAll('.location-card');
    cards.forEach(function(card) {
        if (!locationId || card.dataset.locationId === locationId) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

// Listen for location filter changes
document.addEventListener('DOMContentLoaded', function() {
    var filterSelect = document.getElementById('location-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterByLocation(this.value);
        });
    }
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

// Toggle room status
function toggleRoomStatus(id, currentStatus) {
    fetch('/settings/locations/rooms/' + id + '/toggle-status', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast(result.message);
            location.reload();
        } else {
            showToast(result.message || 'Failed to update status', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });
}

// Delete modal
function confirmDeleteRoom(id, name) {
    deleteRoomId = id;
    document.getElementById('delete-message').innerHTML = 'Are you sure you want to delete <strong>"' + name + '"</strong>? This action cannot be undone.';
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
    deleteRoomId = null;
}

// Delete room
document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    if (!deleteRoomId) return;

    var btn = this;
    var spinner = document.getElementById('delete-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('/settings/locations/rooms/' + deleteRoomId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            closeDeleteModal();
            showToast('Room deleted');
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
        closeRoomDrawer();
    }
});
</script>
@endpush
