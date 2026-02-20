@extends('layouts.dashboard')

@section('title', 'Rental Fulfillment')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.index') }}">Rentals</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Fulfillment</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Rental Fulfillment</h1>
            <p class="text-base-content/60 mt-1">Manage rental requests, pickups, and returns.</p>
        </div>
        <a href="{{ route('rentals.invoice.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            New Rental
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('rentals.fulfillment.index', ['status' => 'pending']) }}"
           class="card bg-warning/10 border border-warning/20 hover:border-warning/40 transition-colors cursor-pointer">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--clock] size-8 text-warning"></span>
                    <div>
                        <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
                        <p class="text-sm text-base-content/60">Pending</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('rentals.fulfillment.index', ['status' => 'prepared']) }}"
           class="card bg-info/10 border border-info/20 hover:border-info/40 transition-colors cursor-pointer">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--package] size-8 text-info"></span>
                    <div>
                        <p class="text-2xl font-bold text-info">{{ $stats['prepared'] }}</p>
                        <p class="text-sm text-base-content/60">Prepared</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('rentals.fulfillment.index', ['status' => 'handed_out']) }}"
           class="card bg-primary/10 border border-primary/20 hover:border-primary/40 transition-colors cursor-pointer">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--hand-grab] size-8 text-primary"></span>
                    <div>
                        <p class="text-2xl font-bold text-primary">{{ $stats['handed_out'] }}</p>
                        <p class="text-sm text-base-content/60">Out</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('rentals.fulfillment.index', ['status' => 'returned']) }}"
           class="card bg-success/10 border border-success/20 hover:border-success/40 transition-colors cursor-pointer">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--circle-check] size-8 text-success"></span>
                    <div>
                        <p class="text-2xl font-bold text-success">{{ $stats['returned'] }}</p>
                        <p class="text-sm text-base-content/60">Returned</p>
                    </div>
                </div>
            </div>
        </a>
        <div class="card bg-error/10 border border-error/20">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--alert-triangle] size-8 text-error"></span>
                    <div>
                        <p class="text-2xl font-bold text-error">{{ $stats['overdue'] }}</p>
                        <p class="text-sm text-base-content/60">Overdue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form method="GET" action="{{ route('rentals.fulfillment.index') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-48">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Search by request ID, customer, or item..."
                           class="input input-bordered w-full">
                </div>
                <div>
                    <select name="status" class="select select-bordered" onchange="this.form.submit()">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ ($status ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--search] size-4"></span>
                    Search
                </button>
                @if($search || ($status && $status !== 'all'))
                    <a href="{{ route('rentals.fulfillment.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($bookings->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--clipboard-check] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Rental Requests</h3>
                    <p class="text-base-content/60 mb-4">There are no rental fulfillment requests matching your criteria.</p>
                    <a href="{{ route('rentals.invoice.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Create Rental Invoice
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Rental Date</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                <tr class="hover:bg-base-200/50">
                                    <td>
                                        <a href="{{ route('rentals.fulfillment.show', $booking) }}" class="font-mono text-sm text-primary hover:underline">
                                            {{ $booking->request_id ?? 'RNT-' . $booking->id }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($booking->client)
                                            <div class="flex items-center gap-2">
                                                <div class="avatar avatar-placeholder">
                                                    <div class="bg-primary/10 text-primary w-8 rounded-full text-xs font-medium">
                                                        {{ $booking->client->initials }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $booking->client->full_name }}</div>
                                                    <div class="text-xs text-base-content/60">{{ $booking->client->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-base-content/60">Walk-in</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $booking->rentalItem?->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-base-content/60">{{ $booking->formatted_total }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-ghost">x{{ $booking->quantity }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $booking->rental_date?->format('M j, Y') }}</div>
                                        @if($booking->due_date)
                                            <div class="text-xs {{ $booking->isOverdue() ? 'text-error' : 'text-base-content/60' }}">
                                                Due: {{ $booking->due_date->format('M j, Y') }}
                                                @if($booking->isOverdue())
                                                    <span class="icon-[tabler--alert-triangle] size-3 inline-block"></span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $booking->status_badge_class }}">
                                            {{ $booking->formatted_status }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($booking->isPending())
                                                <form action="{{ route('rentals.fulfillment.prepare', $booking) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-info btn-xs" title="Mark as Prepared">
                                                        <span class="icon-[tabler--package] size-4"></span>
                                                    </button>
                                                </form>
                                            @elseif($booking->isPrepared())
                                                <form action="{{ route('rentals.fulfillment.hand-out', $booking) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-xs" title="Hand Out">
                                                        <span class="icon-[tabler--hand-grab] size-4"></span>
                                                    </button>
                                                </form>
                                            @elseif($booking->isHandedOut())
                                                <button type="button" class="btn btn-success btn-xs" title="Process Return"
                                                        onclick="openQuickReturnModal({{ $booking->id }})">
                                                    <span class="icon-[tabler--check] size-4"></span>
                                                </button>
                                            @endif
                                            <a href="{{ route('rentals.fulfillment.show', $booking) }}" class="btn btn-ghost btn-xs" title="View Details">
                                                <span class="icon-[tabler--eye] size-4"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($bookings->hasPages())
                    <div class="p-4 border-t border-base-200">
                        {{ $bookings->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Quick Return Modal --}}
<div id="quick-return-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeQuickReturnModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md relative">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Process Return</h3>
                <form id="quick-return-form" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="label-text">Return Condition</label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="condition" value="good" class="radio radio-success" checked>
                                    <span>Good</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="condition" value="damaged" class="radio radio-warning">
                                    <span>Damaged</span>
                                </label>
                            </div>
                        </div>

                        <div id="damage-fields" class="hidden space-y-3">
                            <div>
                                <label class="label-text">Damage Notes</label>
                                <textarea name="damage_notes" class="textarea textarea-bordered w-full" rows="2" placeholder="Describe the damage..."></textarea>
                            </div>
                            <div>
                                <label class="label-text">Damage Charge</label>
                                <input type="number" name="damage_charge" step="0.01" min="0" class="input input-bordered w-full" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeQuickReturnModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Process Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openQuickReturnModal(bookingId) {
    document.getElementById('quick-return-form').action = `/rentals/fulfillment/${bookingId}/return`;
    document.getElementById('quick-return-modal').classList.remove('hidden');
}

function closeQuickReturnModal() {
    document.getElementById('quick-return-modal').classList.add('hidden');
}

// Toggle damage fields based on condition selection
document.querySelectorAll('input[name="condition"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('damage-fields').classList.toggle('hidden', this.value !== 'damaged');
    });
});
</script>
@endpush
@endsection
