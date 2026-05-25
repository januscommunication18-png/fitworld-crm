@extends('layouts.dashboard')

@section('title', $segment->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('segments.index') }}"><span class="icon-[tabler--users-group] me-1 size-4"></span> Segments</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $segment->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $segment->color }}20;">
                    @if($segment->type === 'static')
                        <span class="icon-[tabler--users] size-6" style="color: {{ $segment->color }};"></span>
                    @elseif($segment->type === 'dynamic')
                        <span class="icon-[tabler--filter] size-6" style="color: {{ $segment->color }};"></span>
                    @else
                        <span class="icon-[tabler--crown] size-6" style="color: {{ $segment->color }};"></span>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $segment->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="badge badge-soft {{ $segment->type === 'dynamic' ? 'badge-primary' : ($segment->type === 'smart' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ ucfirst($segment->type) }}
                        </span>
                        @if(!$segment->is_active)
                            <span class="badge badge-soft badge-error">Inactive</span>
                        @endif
                        @if($segment->is_system)
                            <span class="badge badge-soft badge-info">System</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($segment->type === 'dynamic')
                <form action="{{ route('segments.refresh', $segment) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        <span class="icon-[tabler--refresh] size-4"></span>
                        Refresh
                    </button>
                </form>
            @endif
            @if(!$segment->is_system)
                <a href="{{ route('segments.edit', $segment) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </a>
            @endif
            <a href="{{ route('segments.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
        </div>
    </div>

    @if($segment->description)
        <p class="text-base-content/70">{{ $segment->description }}</p>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--users] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($analytics['total_members']) }}</p>
                        <p class="text-sm text-base-content/60">Members</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--currency-dollar] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($analytics['total_revenue'], 0) }}</p>
                        <p class="text-sm text-base-content/60">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-stats] size-5 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($analytics['avg_visits'], 1) }}</p>
                        <p class="text-sm text-base-content/60">Avg Visits</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--tag] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $segment->offers()->count() }}</p>
                        <p class="text-sm text-base-content/60">Active Offers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Members List --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Segment Members</h2>
                        @if($segment->type === 'static')
                            <button type="button" class="btn btn-sm btn-outline" onclick="openDrawer('add-client', event)">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add Client
                            </button>
                        @endif
                    </div>

                    @if($clients->isEmpty())
                        <div class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--users-minus] size-12 mx-auto mb-2"></span>
                            <p>No clients in this segment yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Total Spent</th>
                                        <th>Last Visit</th>
                                        @if($segment->type === 'static')
                                            <th class="w-16"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar avatar-placeholder">
                                                        <div class="bg-neutral text-neutral-content w-8 h-8 rounded-full text-sm">
                                                            {{ $client->initials }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('clients.show', $client) }}" class="font-medium hover:text-primary">
                                                            {{ $client->full_name }}
                                                        </a>
                                                        <p class="text-xs text-base-content/60">{{ $client->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft badge-sm {{ $client->status === 'active' ? 'badge-success' : ($client->status === 'at_risk' ? 'badge-error' : ($client->status === 'inactive' ? 'badge-warning' : 'badge-neutral')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $client->status)) }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($client->total_spent, 0) }}</td>
                                            <td>
                                                @if($client->last_visit_at)
                                                    {{ $client->last_visit_at->diffForHumans() }}
                                                @else
                                                    <span class="text-base-content/40">Never</span>
                                                @endif
                                            </td>
                                            @if($segment->type === 'static')
                                                <td>
                                                    <form action="{{ route('segments.remove-client', [$segment, $client]) }}" method="POST"
                                                          onsubmit="return confirm('Remove this client from the segment?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error">
                                                            <span class="icon-[tabler--x] size-4"></span>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $clients->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Segment Rules --}}
            @if($segment->type === 'dynamic' && $segment->rules->isNotEmpty())
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">Segment Rules</h3>
                        <div class="space-y-3">
                            @foreach($segment->rules->groupBy('group_index') as $groupIndex => $rules)
                                @if($groupIndex > 0)
                                    <div class="divider text-sm text-base-content/60">OR</div>
                                @endif
                                <div class="bg-base-200/50 rounded-lg p-3 space-y-2">
                                    @foreach($rules as $rule)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="font-medium">{{ \App\Models\SegmentRule::getAvailableFields()[$rule->field]['label'] ?? $rule->field }}</span>
                                            <span class="text-base-content/60">{{ \App\Models\SegmentRule::getOperators()[$rule->operator] ?? $rule->operator }}</span>
                                            @if($rule->value)
                                                <span class="badge badge-soft badge-sm">{{ $rule->value }}</span>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <div class="text-xs text-base-content/40 ml-4">AND</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Smart Segment Info --}}
            @if($segment->type === 'smart')
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">Score Requirements</h3>
                        @if($segment->tier)
                            <div class="flex items-center gap-2 mb-3">
                                <span class="icon-[tabler--diamond] size-5 text-warning"></span>
                                <span class="font-medium">{{ ucfirst($segment->tier) }} Tier</span>
                            </div>
                        @endif
                        <div class="bg-base-200/50 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-base-content/60">Score Range</span>
                                <span class="font-medium">{{ $segment->min_score ?? 0 }} - {{ $segment->max_score ?? '1000' }} pts</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Last Updated --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Details</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span>{{ $segment->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Last Updated</span>
                            <span>{{ $segment->updated_at->diffForHumans() }}</span>
                        </div>
                        @if($segment->member_count_updated_at)
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Count Updated</span>
                                <span>{{ $segment->member_count_updated_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Client Drawer (for static segments) --}}
@if($segment->type === 'static')
    <x-detail-drawer id="add-client" title="Add Clients to Segment" size="lg" :showFooter="false">
        <form action="{{ route('segments.add-client', $segment) }}" method="POST" id="add-client-form">
            @csrf

            {{-- Search --}}
            <div class="relative mb-4">
                <span class="icon-[tabler--search] size-4 text-base-content/50 absolute start-3 top-1/2 -translate-y-1/2"></span>
                <input type="text" id="client-search" class="input input-sm ps-9 w-full" placeholder="Search clients by name or email..." autocomplete="off" />
            </div>

            {{-- Selected count --}}
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-base-content/60"><span id="selected-count">0</span> selected</span>
                <button type="button" class="btn btn-ghost btn-xs" id="clear-selection">Clear all</button>
            </div>

            {{-- Client list --}}
            <div class="border border-base-200 rounded-lg max-h-[400px] overflow-y-auto" id="client-list">
                @forelse($availableClients as $c)
                    <label class="client-item flex items-center gap-3 px-3 py-2.5 hover:bg-base-200/50 cursor-pointer border-b border-base-200 last:border-0"
                           data-name="{{ strtolower($c->first_name . ' ' . $c->last_name) }}"
                           data-email="{{ strtolower($c->email) }}">
                        <input type="checkbox" name="client_ids[]" value="{{ $c->id }}" class="checkbox checkbox-sm checkbox-primary client-checkbox" />
                        <div class="avatar avatar-placeholder">
                            <div class="bg-neutral text-neutral-content w-8 h-8 rounded-full text-xs">
                                {{ strtoupper(substr($c->first_name, 0, 1) . substr($c->last_name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm">{{ $c->first_name }} {{ $c->last_name }}</div>
                            <div class="text-xs text-base-content/50 truncate">{{ $c->email }}</div>
                        </div>
                    </label>
                @empty
                    <div class="text-center py-8 text-base-content/50">
                        <span class="icon-[tabler--users-minus] size-8 mx-auto mb-2 block"></span>
                        <p class="text-sm">All clients are already in this segment.</p>
                    </div>
                @endforelse
            </div>

            {{-- No results message --}}
            <div id="no-results" class="hidden text-center py-6 text-base-content/50 text-sm">
                No clients match your search.
            </div>

            {{-- Submit --}}
            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary flex-1" id="add-clients-btn" disabled>
                    <span class="icon-[tabler--plus] size-4"></span> Add Clients
                </button>
                <button type="button" class="btn btn-ghost" onclick="closeDrawer('add-client')">Cancel</button>
            </div>
        </form>
    </x-detail-drawer>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('client-search');
    var clientItems = document.querySelectorAll('.client-item');
    var checkboxes = document.querySelectorAll('.client-checkbox');
    var selectedCount = document.getElementById('selected-count');
    var addBtn = document.getElementById('add-clients-btn');
    var clearBtn = document.getElementById('clear-selection');
    var noResults = document.getElementById('no-results');

    if (!searchInput) return;

    // Search filter
    searchInput.addEventListener('input', function() {
        var query = this.value.toLowerCase().trim();
        var visible = 0;

        clientItems.forEach(function(item) {
            var name = item.dataset.name || '';
            var email = item.dataset.email || '';
            var match = !query || name.indexOf(query) !== -1 || email.indexOf(query) !== -1;
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        noResults.classList.toggle('hidden', visible > 0);
    });

    // Update selected count
    function updateCount() {
        var count = document.querySelectorAll('.client-checkbox:checked').length;
        selectedCount.textContent = count;
        addBtn.disabled = count === 0;
        addBtn.textContent = '';
        addBtn.innerHTML = '<span class="icon-[tabler--plus] size-4"></span> Add ' + (count > 0 ? count + ' ' : '') + 'Client' + (count !== 1 ? 's' : '');
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });

    // Clear all
    clearBtn.addEventListener('click', function() {
        checkboxes.forEach(function(cb) { cb.checked = false; });
        updateCount();
    });
});
</script>
@endpush
@endsection
