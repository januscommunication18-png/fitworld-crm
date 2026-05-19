@extends('layouts.dashboard')

@section('title', 'Check-in — ' . $membershipPlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'memberships']) }}"><span class="icon-[tabler--id-badge-2] me-1 size-4"></span> Memberships</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('membership-plans.show', $membershipPlan) }}">{{ Str::limit($membershipPlan->name, 20) }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Check-in</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if($membershipPlan->image_url)
                <img src="{{ $membershipPlan->image_url }}" alt="{{ $membershipPlan->name }}" class="w-16 h-16 rounded-lg object-cover">
            @else
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $membershipPlan->color ?? '#6366f1' }}20;">
                    <span class="icon-[tabler--id-badge-2] size-8" style="color: {{ $membershipPlan->color ?? '#6366f1' }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $membershipPlan->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-soft badge-accent badge-sm">Open Access</span>
                    <span class="badge badge-soft badge-sm">{{ ucfirst($membershipPlan->type) }}</span>
                    <span class="text-sm text-base-content/60">{{ $stats['active_members'] }} active {{ Str::plural('member', $stats['active_members']) }}</span>
                </div>
            </div>
        </div>
        <a href="{{ route('membership-plans.show', $membershipPlan) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span> Back to Plan
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--login] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['today'] }}</p>
                        <p class="text-xs text-base-content/60">Today</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--calendar-check] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['this_week'] }}</p>
                        <p class="text-xs text-base-content/60">This Week</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--chart-bar] size-6 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['this_month'] }}</p>
                        <p class="text-xs text-base-content/60">This Month</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--users] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['active_members'] }}</p>
                        <p class="text-xs text-base-content/60">Active Members</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Check-in --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--search] size-5 text-primary"></span>
                <h3 class="card-title">Member Check-in</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="relative">
                <input type="text" id="member-search" class="input w-full pr-10"
                       placeholder="Search by name, email, or phone..." autocomplete="off">
                <span id="search-loading" class="loading loading-spinner loading-xs absolute top-1/2 right-3 -translate-y-1/2 text-primary hidden"></span>
            </div>

            {{-- Search Results --}}
            <div id="search-results" class="mt-3 space-y-2 hidden"></div>

            {{-- No results --}}
            <div id="no-results" class="mt-3 hidden">
                <p class="text-sm text-base-content/50 text-center py-4">No active members found matching your search.</p>
            </div>
        </div>
    </div>

    {{-- Today's Check-ins --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--clock] size-5 text-primary"></span>
                <h3 class="card-title">Today's Check-ins</h3>
            </div>
            <span class="badge badge-primary badge-sm">{{ $todayCheckins->count() }}</span>
        </div>
        <div class="card-body p-0">
            @if($todayCheckins->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--door-enter] size-10 text-base-content/20"></span>
                    <p class="text-base-content/60 mt-2 text-sm">No check-ins today yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Checked In</th>
                                <th>Checked Out</th>
                                <th>By</th>
                                <th class="w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="checkins-table-body">
                            @foreach($todayCheckins as $checkin)
                            <tr id="checkin-row-{{ $checkin->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center font-bold text-xs text-primary">
                                            {{ $checkin->client->initials }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-sm">{{ $checkin->client->full_name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $checkin->client->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm">{{ $checkin->checked_in_at->format('g:i A') }}</td>
                                <td class="text-sm" id="checkout-cell-{{ $checkin->id }}">
                                    @if($checkin->checked_out_at)
                                        {{ $checkin->checked_out_at->format('g:i A') }}
                                    @else
                                        <span class="badge badge-soft badge-success badge-xs">Still here</span>
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/60">{{ $checkin->checkedInBy?->first_name ?? '-' }}</td>
                                <td>
                                    @if(!$checkin->checked_out_at)
                                        <button type="button" class="btn btn-ghost btn-xs text-warning" onclick="checkOut({{ $checkin->id }})" id="checkout-btn-{{ $checkin->id }}">
                                            <span class="icon-[tabler--logout] size-4"></span> Out
                                        </button>
                                    @else
                                        <span class="text-base-content/30 text-xs">Done</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var searchInput = document.getElementById('member-search');
var searchResults = document.getElementById('search-results');
var noResults = document.getElementById('no-results');
var searchLoading = document.getElementById('search-loading');
var searchTimer;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    var query = this.value.trim();

    if (query.length < 2) {
        searchResults.classList.add('hidden');
        noResults.classList.add('hidden');
        return;
    }

    searchLoading.classList.remove('hidden');

    searchTimer = setTimeout(function() {
        fetch('{{ route("membership-checkin.search", $membershipPlan) }}?q=' + encodeURIComponent(query))
            .then(function(r) { return r.json(); })
            .then(function(results) {
                searchLoading.classList.add('hidden');

                if (!results || results.length === 0) {
                    searchResults.classList.add('hidden');
                    noResults.classList.remove('hidden');
                    return;
                }

                noResults.classList.add('hidden');
                searchResults.innerHTML = '';

                results.forEach(function(member) {
                    var creditInfo = '';
                    if (member.membership_type === 'credits' && member.credits_remaining !== null) {
                        creditInfo = '<span class="badge badge-soft badge-sm badge-info">' + member.credits_remaining + ' credits</span>';
                    }

                    var actionBtn = '';
                    if (member.already_checked_in) {
                        actionBtn = '<button type="button" class="btn btn-warning btn-sm" onclick="checkOut(' + member.checkin_id + ')">' +
                            '<span class="icon-[tabler--logout] size-4"></span> Check Out</button>';
                    } else {
                        actionBtn = '<button type="button" class="btn btn-success btn-sm" onclick="checkIn(' + member.id + ', ' + member.customer_membership_id + ')">' +
                            '<span class="icon-[tabler--login] size-4"></span> Check In</button>';
                    }

                    var row = document.createElement('div');
                    row.className = 'flex items-center justify-between p-3 bg-base-200/50 rounded-xl hover:bg-base-200 transition-colors';
                    row.innerHTML = '<div class="flex items-center gap-3">' +
                        '<div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-sm text-primary">' +
                            (member.initials || '?') +
                        '</div>' +
                        '<div>' +
                            '<div class="font-medium text-sm">' + member.full_name + '</div>' +
                            '<div class="flex items-center gap-2 mt-0.5">' +
                                '<span class="text-xs text-base-content/60">' + (member.email || '') + '</span>' +
                                creditInfo +
                                (member.already_checked_in ? '<span class="badge badge-soft badge-success badge-xs">Checked In</span>' : '') +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div>' + actionBtn + '</div>';

                    searchResults.appendChild(row);
                });

                searchResults.classList.remove('hidden');
            })
            .catch(function() {
                searchLoading.classList.add('hidden');
            });
    }, 300);
});

function checkIn(clientId, customerMembershipId) {
    var locationId = null; // Could add a location selector if needed

    fetch('{{ route("membership-checkin.store", $membershipPlan) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            client_id: clientId,
            customer_membership_id: customerMembershipId,
            location_id: locationId,
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(data.message, 'success');
            // Refresh the page to update today's check-ins
            setTimeout(function() { window.location.reload(); }, 500);
        } else {
            showToast(data.message || 'Check-in failed', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    });
}

function checkOut(checkinId) {
    fetch('/membership-checkin/' + checkinId + '/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Update the checkout cell
            var cell = document.getElementById('checkout-cell-' + checkinId);
            if (cell) cell.innerHTML = data.checked_out_at;

            var btn = document.getElementById('checkout-btn-' + checkinId);
            if (btn) btn.outerHTML = '<span class="text-base-content/30 text-xs">Done</span>';

            showToast(data.message, 'success');

            // Also refresh search results if visible
            if (!searchResults.classList.contains('hidden')) {
                searchInput.dispatchEvent(new Event('input'));
            }
        } else {
            showToast(data.message || 'Check-out failed', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    });
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
</script>
@endpush
