@extends('layouts.dashboard')

@section('title', 'Price Override Requests')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Price Override Requests</h1>
            <p class="text-base-content/60">Manage price override requests from your team</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--clock] size-5 text-warning"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['pending'] }}</div>
                        <div class="text-sm text-base-content/60">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--check] size-5 text-success"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['approved_today'] }}</div>
                        <div class="text-sm text-base-content/60">Approved Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-error/10 flex items-center justify-center">
                        <span class="icon-[tabler--x] size-5 text-error"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['rejected_today'] }}</div>
                        <div class="text-sm text-base-content/60">Rejected Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--receipt-refund] size-5 text-primary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($stats['total_discount_today'], 2) }}</div>
                        <div class="text-sm text-base-content/60">Discounts Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Pending Requests --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--clock] size-5 mr-2 text-warning"></span>
                Pending Requests
                @if($pendingRequests->count() > 0)
                <span class="badge badge-warning badge-sm ml-2">{{ $pendingRequests->count() }}</span>
                @endif
            </h3>
        </div>
        <div class="card-body p-0">
            @if($pendingRequests->isEmpty())
            <div class="text-center py-8 text-base-content/50">
                <span class="icon-[tabler--inbox] size-12 mb-2"></span>
                <p>No pending override requests</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Requested By</th>
                            <th>Client</th>
                            <th>Original</th>
                            <th>Requested</th>
                            <th>Discount</th>
                            <th>Expires</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                        <tr class="{{ $request->is_expired ? 'opacity-50' : '' }}">
                            <td>
                                <span class="font-mono font-bold text-primary">{{ $request->confirmation_code }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content size-8 rounded-full">
                                            <span class="text-xs">{{ substr($request->requester->first_name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $request->requester->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-base-content/60">{{ $request->location?->name ?? 'No location' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($request->client)
                                <div class="font-medium">{{ $request->client->full_name }}</div>
                                @else
                                <span class="text-base-content/50">Walk-in</span>
                                @endif
                            </td>
                            <td class="font-medium">${{ number_format($request->original_price, 2) }}</td>
                            <td class="font-medium text-success">${{ number_format($request->requested_price, 2) }}</td>
                            <td>
                                <span class="badge badge-success badge-sm">
                                    -${{ number_format($request->discount_amount, 2) }}
                                    ({{ $request->discount_percentage }}%)
                                </span>
                            </td>
                            <td>
                                @if($request->is_expired)
                                <span class="badge badge-error badge-sm">Expired</span>
                                @else
                                <span class="text-sm text-warning">{{ $request->expires_at->diffForHumans() }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($canApprove && !$request->is_expired)
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="approveRequest({{ $request->id }})"
                                            class="btn btn-success btn-sm">
                                        <span class="icon-[tabler--check] size-4"></span>
                                        Approve
                                    </button>
                                    <button onclick="showRejectModal({{ $request->id }})"
                                            class="btn btn-error btn-sm btn-outline">
                                        <span class="icon-[tabler--x] size-4"></span>
                                        Reject
                                    </button>
                                </div>
                                @elseif($request->is_expired)
                                <span class="text-error text-sm">Expired</span>
                                @endif
                            </td>
                        </tr>
                        @if($request->reason)
                        <tr class="bg-base-200/30">
                            <td colspan="8" class="py-2 px-4">
                                <span class="text-sm text-base-content/60">Reason: {{ $request->reason }}</span>
                                @if($request->discount_code)
                                <span class="badge badge-ghost badge-sm ml-2">Code: {{ $request->discount_code }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- History --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--history] size-5 mr-2"></span>
                Recent History
            </h3>
        </div>
        <div class="card-body p-0">
            @if($history->isEmpty())
            <div class="text-center py-8 text-base-content/50">
                <span class="icon-[tabler--history-off] size-12 mb-2"></span>
                <p>No override history yet</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Actioned By</th>
                            <th>Original</th>
                            <th>Final</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $request)
                        <tr>
                            <td>
                                <span class="font-mono text-sm">{{ $request->confirmation_code }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $request->status_badge_class }} badge-sm">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td>{{ $request->requester->name ?? 'Unknown' }}</td>
                            <td>{{ $request->actionedBy->name ?? '-' }}</td>
                            <td>${{ number_format($request->original_price, 2) }}</td>
                            <td>
                                @if($request->is_approved)
                                <span class="text-success">${{ number_format($request->requested_price, 2) }}</span>
                                @else
                                <span class="text-base-content/50">-</span>
                                @endif
                            </td>
                            <td class="text-sm text-base-content/60">
                                {{ $request->created_at->format('M j, g:i A') }}
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

{{-- Reject Modal --}}
<dialog id="reject-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reject Override Request</h3>
        <form id="reject-form">
            <input type="hidden" name="request_id" id="reject-request-id">
            <div class="form-control">
                <label class="label" for="reject-reason">
                    <span class="label-text">Reason (optional)</span>
                </label>
                <textarea id="reject-reason" name="reason" rows="3" class="textarea textarea-bordered"
                          placeholder="Why is this request being rejected?"></textarea>
            </div>
            <div class="modal-action">
                <button type="button" onclick="closeRejectModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--x] size-4"></span>
                    Reject Request
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
function approveRequest(requestId) {
    if (!confirm('Are you sure you want to approve this price override?')) {
        return;
    }

    fetch(`/price-override/${requestId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to approve request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function showRejectModal(requestId) {
    document.getElementById('reject-request-id').value = requestId;
    document.getElementById('reject-reason').value = '';
    document.getElementById('reject-modal').showModal();
}

function closeRejectModal() {
    document.getElementById('reject-modal').close();
}

document.getElementById('reject-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const requestId = document.getElementById('reject-request-id').value;
    const reason = document.getElementById('reject-reason').value;

    fetch(`/price-override/${requestId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRejectModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to reject request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>
@endpush
@endsection
