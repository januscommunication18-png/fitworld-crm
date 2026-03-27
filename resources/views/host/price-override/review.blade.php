@extends('layouts.dashboard')

@section('title', 'Review Price Override')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('price-override.index') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Review Price Override</h1>
            <p class="text-base-content/60">Confirmation Code: <span class="font-mono font-bold text-primary">{{ $request->confirmation_code }}</span></p>
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

    {{-- Status Banner --}}
    @if($request->status !== 'pending')
    <div class="alert {{ $request->is_approved ? 'alert-success' : ($request->is_rejected ? 'alert-error' : 'alert-warning') }} mb-6">
        <span class="icon-[tabler--{{ $request->is_approved ? 'check' : ($request->is_rejected ? 'x' : 'clock') }}] size-5"></span>
        <div>
            <div class="font-semibold">This request has been {{ $request->status_label }}</div>
            @if($request->actionedBy)
            <p class="text-sm">By {{ $request->actionedBy->name }} on {{ ($request->approved_at ?? $request->rejected_at)?->format('M j, Y \a\t g:i A') }}</p>
            @endif
            @if($request->rejection_reason)
            <p class="text-sm mt-1">Reason: {{ $request->rejection_reason }}</p>
            @endif
        </div>
    </div>
    @elseif($request->is_expired)
    <div class="alert alert-warning mb-6">
        <span class="icon-[tabler--clock] size-5"></span>
        <span>This request has expired and can no longer be approved.</span>
    </div>
    @endif

    {{-- Request Details Card --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-header">
            <h3 class="card-title">Request Details</h3>
        </div>
        <div class="card-body space-y-4">
            {{-- Requester --}}
            <div class="flex justify-between items-center">
                <span class="text-base-content/60">Requested By</span>
                <div class="flex items-center gap-2">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content size-8 rounded-full">
                            <span class="text-xs">{{ substr($request->requester->first_name ?? 'U', 0, 1) }}</span>
                        </div>
                    </div>
                    <span class="font-medium">{{ $request->requester->name ?? 'Unknown' }}</span>
                </div>
            </div>

            {{-- Location --}}
            @if($request->location)
            <div class="flex justify-between">
                <span class="text-base-content/60">Location</span>
                <span class="font-medium">{{ $request->location->name }}</span>
            </div>
            @endif

            {{-- Client --}}
            <div class="flex justify-between">
                <span class="text-base-content/60">Client</span>
                <span class="font-medium">{{ $request->client?->full_name ?? 'Walk-in Customer' }}</span>
            </div>

            {{-- Date/Time --}}
            <div class="flex justify-between">
                <span class="text-base-content/60">Requested At</span>
                <span class="font-medium">{{ $request->created_at->format('M j, Y \a\t g:i A') }}</span>
            </div>

            @if($request->status === 'pending' && !$request->is_expired)
            <div class="flex justify-between">
                <span class="text-base-content/60">Expires</span>
                <span class="font-medium text-warning">{{ $request->expires_at->diffForHumans() }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Pricing Card --}}
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-header">
            <h3 class="card-title">Pricing</h3>
        </div>
        <div class="card-body space-y-4">
            <div class="flex justify-between">
                <span class="text-base-content/60">Original Price</span>
                <span class="font-medium text-lg">${{ number_format($request->original_price, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-base-content/60">Requested Price</span>
                <span class="font-medium text-lg text-success">${{ number_format($request->requested_price, 2) }}</span>
            </div>

            <div class="divider my-2"></div>

            <div class="flex justify-between">
                <span class="text-base-content/60">Discount Amount</span>
                <span class="badge badge-success badge-lg">
                    -${{ number_format($request->discount_amount, 2) }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-base-content/60">Discount Percentage</span>
                <span class="badge badge-success badge-lg">
                    {{ $request->discount_percentage }}% off
                </span>
            </div>

            @if($request->discount_code)
            <div class="flex justify-between">
                <span class="text-base-content/60">Discount Code</span>
                <span class="font-mono font-medium">{{ $request->discount_code }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Reason Card --}}
    @if($request->reason)
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-header">
            <h3 class="card-title">Reason</h3>
        </div>
        <div class="card-body">
            <p class="text-base-content/80">{{ $request->reason }}</p>
        </div>
    </div>
    @endif

    {{-- Booking Context --}}
    @if($request->bookable)
    <div class="card bg-base-100 border border-base-200 mb-6">
        <div class="card-header">
            <h3 class="card-title">Booking Context</h3>
        </div>
        <div class="card-body">
            @if($request->metadata)
            <div class="space-y-2">
                @if(isset($request->metadata['class_name']))
                <div class="flex justify-between">
                    <span class="text-base-content/60">Class</span>
                    <span class="font-medium">{{ $request->metadata['class_name'] }}</span>
                </div>
                @endif
                @if(isset($request->metadata['class_date']))
                <div class="flex justify-between">
                    <span class="text-base-content/60">Date/Time</span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($request->metadata['class_date'])->format('M j, Y \a\t g:i A') }}</span>
                </div>
                @endif
            </div>
            @else
            <p class="text-base-content/50">{{ class_basename($request->bookable_type) }} #{{ $request->bookable_id }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Action Buttons --}}
    @if($canAction && $request->status === 'pending' && !$request->is_expired)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="approveRequest({{ $request->id }})" class="btn btn-success flex-1">
                    <span class="icon-[tabler--check] size-5"></span>
                    Approve Override
                </button>
                <button onclick="showRejectModal()" class="btn btn-error btn-outline flex-1">
                    <span class="icon-[tabler--x] size-5"></span>
                    Reject Request
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Reject Modal --}}
<dialog id="reject-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reject Override Request</h3>
        <form id="reject-form">
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

function showRejectModal() {
    document.getElementById('reject-reason').value = '';
    document.getElementById('reject-modal').showModal();
}

function closeRejectModal() {
    document.getElementById('reject-modal').close();
}

document.getElementById('reject-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const reason = document.getElementById('reject-reason').value;

    fetch(`/price-override/{{ $request->id }}/reject`, {
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
