@extends('backoffice.layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')
<div class="space-y-6">
    {{-- Search and Filter --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                {{-- Search --}}
                <form action="{{ route('backoffice.clients.index') }}" method="GET" class="flex-1 max-w-md">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="join w-full">
                        <input type="text" name="search" value="{{ $search }}"
                            class="input join-item flex-1"
                            placeholder="Search by studio name, owner, or email...">
                        <button type="submit" class="btn btn-primary join-item">
                            <span class="icon-[tabler--search] size-5"></span>
                        </button>
                    </div>
                </form>

                {{-- Export (placeholder) --}}
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-soft btn-neutral btn-sm" disabled>
                        <span class="icon-[tabler--download] size-4"></span>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered">
        <a href="{{ route('backoffice.clients.index', ['tab' => 'all', 'search' => $search]) }}"
           class="tab {{ $tab === 'all' ? 'tab-active' : '' }}">
            All
            <span class="badge badge-neutral badge-sm ml-2">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('backoffice.clients.index', ['tab' => 'today', 'search' => $search]) }}"
           class="tab {{ $tab === 'today' ? 'tab-active' : '' }}">
            Today
            <span class="badge badge-info badge-sm ml-2">{{ $counts['today'] }}</span>
        </a>
        <a href="{{ route('backoffice.clients.index', ['tab' => 'active', 'search' => $search]) }}"
           class="tab {{ $tab === 'active' ? 'tab-active' : '' }}">
            Active
            <span class="badge badge-success badge-sm ml-2">{{ $counts['active'] }}</span>
        </a>
        <a href="{{ route('backoffice.clients.index', ['tab' => 'inactive', 'search' => $search]) }}"
           class="tab {{ $tab === 'inactive' ? 'tab-active' : '' }}">
            Inactive
            <span class="badge badge-neutral badge-sm ml-2">{{ $counts['inactive'] }}</span>
        </a>
        <a href="{{ route('backoffice.clients.index', ['tab' => 'pending', 'search' => $search]) }}"
           class="tab {{ $tab === 'pending' ? 'tab-active' : '' }}">
            Pending Verify
            <span class="badge badge-warning badge-sm ml-2">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('backoffice.clients.index', ['tab' => 'suspended', 'search' => $search]) }}"
           class="tab {{ $tab === 'suspended' ? 'tab-active' : '' }}">
            Suspended
            <span class="badge badge-error badge-sm ml-2">{{ $counts['suspended'] }}</span>
        </a>
    </div>

    {{-- Clients Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-12">S.No</th>
                            <th>Studio Name</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Plan</th>
                            <th>Registered</th>
                            <th>Verified</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $index => $client)
                        <tr>
                            <td class="text-base-content/60">
                                {{ ($clients->currentPage() - 1) * $clients->perPage() + $index + 1 }}
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary/10 text-primary size-10 rounded-lg text-sm font-bold">
                                            {{ strtoupper(substr($client->studio_name ?? 'S', 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('backoffice.clients.show', $client) }}" class="font-medium hover:text-primary">
                                            {{ $client->studio_name ?? 'Unnamed Studio' }}
                                        </a>
                                        <div class="text-xs text-base-content/60">{{ $client->subdomain }}.fitcrm.app</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($client->owner)
                                    <div class="text-sm">{{ $client->owner->first_name }} {{ $client->owner->last_name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $client->owner->email }}</div>
                                @else
                                    <span class="text-base-content/40">No owner</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'badge-success',
                                        'inactive' => 'badge-neutral',
                                        'pending_verify' => 'badge-warning',
                                        'suspended' => 'badge-error',
                                    ];
                                @endphp
                                <span class="badge badge-soft {{ $statusColors[$client->status] ?? 'badge-neutral' }} badge-sm capitalize">
                                    {{ str_replace('_', ' ', $client->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                @if($client->plan)
                                    <span class="badge badge-soft badge-primary badge-sm">{{ $client->plan->name }}</span>
                                @else
                                    <span class="text-base-content/40 text-sm">No plan</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $client->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $client->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($client->verified_at)
                                    <span class="icon-[tabler--circle-check] size-5 text-success" title="Verified {{ $client->verified_at->format('M d, Y') }}"></span>
                                @else
                                    <span class="icon-[tabler--circle-x] size-5 text-base-content/30" title="Not verified"></span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('backoffice.clients.show', $client) }}"
                                       class="btn btn-ghost btn-xs btn-square" title="View Details">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                    <a href="{{ route('backoffice.clients.edit', $client) }}"
                                       class="btn btn-ghost btn-xs btn-square" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </a>
                                    <button type="button"
                                            class="btn btn-ghost btn-xs btn-square"
                                            title="Change Status"
                                            onclick="openStatusModal({{ $client->id }}, '{{ $client->status }}')">
                                        <span class="icon-[tabler--toggle-left] size-4"></span>
                                    </button>
                                    @if(!$client->verified_at && $client->owner)
                                    <form action="{{ route('backoffice.clients.resend-verification', $client) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square" title="Resend Verification">
                                            <span class="icon-[tabler--mail-forward] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--building-off] size-12 opacity-30"></span>
                                    <p>No clients found</p>
                                    @if($search)
                                        <a href="{{ route('backoffice.clients.index', ['tab' => $tab]) }}" class="text-primary text-sm">Clear search</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($clients->hasPages())
            <div class="border-t border-base-content/10 px-4 py-3">
                {{ $clients->appends(['tab' => $tab, 'search' => $search])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Status Change Modal --}}
<dialog id="status-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4">Change Client Status</h3>
        <form id="status-form" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="status">New Status</label>
                    <select name="status" id="modal-status" class="select w-full" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending_verify">Pending Verify</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="label-text" for="reason">Reason (optional)</label>
                    <textarea name="reason" id="reason" class="textarea w-full" rows="3"
                        placeholder="Reason for status change..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('status-modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Status</button>
                </div>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function openStatusModal(clientId, currentStatus) {
    var form = document.getElementById('status-form');
    form.action = '/backoffice/clients/' + clientId + '/status';
    document.getElementById('modal-status').value = currentStatus;
    document.getElementById('reason').value = '';
    document.getElementById('status-modal').showModal();
}
</script>
@endsection
