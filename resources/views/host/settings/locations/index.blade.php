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
            <p class="text-base-content/60 text-sm">Manage your studio locations (max 3)</p>
        </div>
        @if($locations->count() < 3)
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
                            <span class="icon-[tabler--map-pin] size-6 text-primary"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold">{{ $location->name }}</h3>
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
                            <span class="flex items-center gap-1" title="Rooms">
                                <span class="icon-[tabler--door] size-4"></span>
                                {{ $location->rooms_count ?? 0 }}
                            </span>
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
    }
});
</script>
@endpush
