@extends('layouts.dashboard')

@section('title', 'Fulfillment Request - ' . ($booking->request_id ?? 'RNT-' . $booking->id))

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.index') }}">Rentals</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.fulfillment.index') }}">Fulfillment</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $booking->request_id ?? 'RNT-' . $booking->id }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">{{ $booking->request_id ?? 'RNT-' . $booking->id }}</h1>
                <span class="badge {{ $booking->status_badge_class }} badge-lg">{{ $booking->formatted_status }}</span>
            </div>
            <p class="text-base-content/60 mt-1">Created {{ $booking->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        <a href="{{ route('rentals.fulfillment.index') }}" class="btn btn-ghost">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Customer Information --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon-[tabler--user] size-5 mr-2"></span>
                        Customer Information
                    </h3>
                </div>
                <div class="card-body">
                    @if($booking->client)
                        <div class="flex items-start gap-4">
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary/10 text-primary w-16 rounded-full text-xl font-medium">
                                    {{ $booking->client->initials }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold">{{ $booking->client->full_name }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 text-sm">
                                    <div>
                                        <span class="text-base-content/60">Email:</span>
                                        <a href="mailto:{{ $booking->client->email }}" class="text-primary hover:underline ml-1">
                                            {{ $booking->client->email }}
                                        </a>
                                    </div>
                                    @if($booking->client->phone)
                                        <div>
                                            <span class="text-base-content/60">Phone:</span>
                                            <a href="tel:{{ $booking->client->phone }}" class="text-primary hover:underline ml-1">
                                                {{ $booking->client->phone }}
                                            </a>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="text-base-content/60">Status:</span>
                                        <span class="badge badge-sm ml-1 {{ $booking->client->status === 'member' ? 'badge-success' : 'badge-ghost' }}">
                                            {{ ucfirst($booking->client->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-sm btn-ghost">
                                        <span class="icon-[tabler--external-link] size-4"></span>
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-4 text-base-content/60">
                            <div class="avatar avatar-placeholder">
                                <div class="bg-base-200 w-16 rounded-full text-xl">
                                    <span class="icon-[tabler--user-question] size-8"></span>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-base-content">Walk-in Customer</h4>
                                <p class="text-sm">No customer profile attached to this rental.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related Booking (if any) --}}
            @if($booking->bookable)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--calendar-event] size-5 mr-2"></span>
                            Related Booking
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($booking->bookable_type === 'App\\Models\\ClassBooking')
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--yoga] size-10 text-primary"></span>
                                <div>
                                    <h4 class="font-semibold">{{ $booking->bookable->classSession?->classPlan?->name ?? 'Class Booking' }}</h4>
                                    <p class="text-sm text-base-content/60">
                                        {{ $booking->bookable->classSession?->start_time?->format('M j, Y \a\t g:i A') ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @elseif($booking->bookable_type === 'App\\Models\\ServiceBooking')
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--sparkles] size-10 text-primary"></span>
                                <div>
                                    <h4 class="font-semibold">{{ $booking->bookable->servicePlan?->name ?? 'Service Booking' }}</h4>
                                    <p class="text-sm text-base-content/60">
                                        {{ $booking->bookable->start_time?->format('M j, Y \a\t g:i A') ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Rental Item --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon-[tabler--package] size-5 mr-2"></span>
                        Rental Item
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        @if($booking->rentalItem?->primary_image)
                            <img src="{{ Storage::url($booking->rentalItem->primary_image) }}" alt=""
                                 class="w-24 h-24 object-cover rounded-lg flex-shrink-0">
                        @else
                            <div class="w-24 h-24 rounded-lg bg-base-200 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--package] size-10 text-base-content/30"></span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold">{{ $booking->rentalItem?->name ?? 'Unknown Item' }}</h4>
                            @if($booking->rentalItem?->sku)
                                <p class="text-sm text-base-content/60">SKU: {{ $booking->rentalItem->sku }}</p>
                            @endif
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p class="text-sm text-base-content/60">Quantity</p>
                                    <p class="font-semibold">{{ $booking->quantity }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Unit Price</p>
                                    <p class="font-semibold">{{ MembershipPlan::getCurrencySymbol($booking->currency) }}{{ number_format($booking->unit_price, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Total Price</p>
                                    <p class="font-semibold text-lg">{{ $booking->formatted_total }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Security Deposit</p>
                                    <p class="font-semibold">{{ $booking->formatted_deposit }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rental Dates --}}
                    <div class="divider"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Rental Date</p>
                            <p class="font-semibold">{{ $booking->rental_date?->format('M j, Y') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Due Date</p>
                            @if($booking->due_date)
                                <p class="font-semibold {{ $booking->isOverdue() ? 'text-error' : '' }}">
                                    {{ $booking->due_date->format('M j, Y') }}
                                    @if($booking->isOverdue())
                                        <span class="badge badge-error badge-xs ml-1">Overdue</span>
                                    @endif
                                </p>
                            @else
                                <p class="text-base-content/60">Not specified</p>
                            @endif
                        </div>
                    </div>

                    {{-- Return Info (if returned) --}}
                    @if($booking->isReturned() || $booking->isLost())
                        <div class="divider"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Returned At</p>
                                <p class="font-semibold">{{ $booking->returned_at?->format('M j, Y \a\t g:i A') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Condition</p>
                                @if($booking->condition_on_return)
                                    <span class="badge {{ $booking->condition_badge_class }}">
                                        {{ ucfirst($booking->condition_on_return) }}
                                    </span>
                                @else
                                    <span class="text-base-content/60">N/A</span>
                                @endif
                            </div>
                            @if($booking->damage_notes)
                                <div class="col-span-2">
                                    <p class="text-sm text-base-content/60">Damage Notes</p>
                                    <p class="mt-1">{{ $booking->damage_notes }}</p>
                                </div>
                            @endif
                            @if($booking->damage_charge > 0)
                                <div>
                                    <p class="text-sm text-base-content/60">Damage Charge</p>
                                    <p class="font-semibold text-error">
                                        {{ MembershipPlan::getCurrencySymbol($booking->currency) }}{{ number_format($booking->damage_charge, 2) }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transaction Info --}}
            @if($booking->transaction)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--receipt] size-5 mr-2"></span>
                            Transaction
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-base-content/60">Transaction ID</p>
                                <p class="font-mono">{{ $booking->transaction->transaction_id }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-base-content/60">Payment</p>
                                <span class="badge {{ $booking->transaction->status_badge_class }}">
                                    {{ ucfirst($booking->transaction->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-sm text-base-content/60">Payment Method</p>
                                <p class="font-medium">{{ $booking->transaction->payment_method_label }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Total Amount</p>
                                <p class="font-semibold text-lg">{{ $booking->transaction->formatted_total }}</p>
                            </div>
                        </div>
                        <a href="{{ route('payments.transactions') }}" class="btn btn-ghost btn-sm mt-4">
                            <span class="icon-[tabler--external-link] size-4"></span>
                            View Transaction
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Update Status Card --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon-[tabler--refresh] size-5 mr-2"></span>
                        Update Status
                    </h3>
                </div>
                <div class="card-body">
                    @if($booking->isCompleted())
                        <div class="alert {{ $booking->isLost() ? 'alert-error' : 'alert-success' }}">
                            <span class="icon-[tabler--{{ $booking->isLost() ? 'alert-triangle' : 'circle-check' }}] size-5"></span>
                            <span>This rental has been {{ $booking->isLost() ? 'marked as lost' : 'returned' }}.</span>
                        </div>
                    @else
                        <form action="{{ route('rentals.fulfillment.update-status', $booking) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="label-text" for="status">New Status</label>
                                    <select name="status" id="status" class="select select-bordered w-full" onchange="toggleStatusFields()">
                                        @if($booking->isPending())
                                            <option value="prepared">Prepared</option>
                                        @elseif($booking->isPrepared())
                                            <option value="handed_out">Handed to Customer</option>
                                        @elseif($booking->isHandedOut())
                                            <option value="returned">Returned</option>
                                            <option value="lost">Lost</option>
                                        @endif
                                    </select>
                                </div>

                                <div id="condition-field" class="hidden">
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

                                <div id="damage-charge-field" class="hidden">
                                    <label class="label-text" for="damage_charge">Damage Charge</label>
                                    <input type="number" name="damage_charge" id="damage_charge" step="0.01" min="0"
                                           class="input input-bordered w-full" placeholder="0.00">
                                </div>

                                <div>
                                    <label class="label-text" for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" class="textarea textarea-bordered w-full"
                                              placeholder="Add notes about this status change..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--check] size-5"></span>
                                    Update Status
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Status History --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon-[tabler--history] size-5 mr-2"></span>
                        Status History
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($booking->statusLogs->isEmpty())
                        <div class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--history] size-10 block mx-auto mb-2 opacity-30"></span>
                            <p>No status history yet.</p>
                        </div>
                    @else
                        <div class="divide-y divide-base-200">
                            @foreach($booking->statusLogs as $log)
                                <div class="p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            @switch($log->to_status)
                                                @case('pending')
                                                    <span class="icon-[tabler--clock] size-4 text-warning"></span>
                                                    @break
                                                @case('prepared')
                                                    <span class="icon-[tabler--package] size-4 text-info"></span>
                                                    @break
                                                @case('handed_out')
                                                    <span class="icon-[tabler--hand-grab] size-4 text-primary"></span>
                                                    @break
                                                @case('returned')
                                                    <span class="icon-[tabler--circle-check] size-4 text-success"></span>
                                                    @break
                                                @case('lost')
                                                    <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                                                    @break
                                                @default
                                                    <span class="icon-[tabler--arrow-right] size-4"></span>
                                            @endswitch
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($log->from_status)
                                                    <span class="badge badge-ghost badge-sm">{{ $log->from_status_label }}</span>
                                                    <span class="icon-[tabler--arrow-right] size-3 text-base-content/40"></span>
                                                @endif
                                                <span class="badge badge-sm
                                                    @switch($log->to_status)
                                                        @case('pending') badge-warning @break
                                                        @case('prepared') badge-info @break
                                                        @case('handed_out') badge-primary @break
                                                        @case('returned') badge-success @break
                                                        @case('lost') badge-error @break
                                                    @endswitch
                                                ">{{ $log->to_status_label }}</span>
                                            </div>
                                            @if($log->notes)
                                                <p class="text-sm mt-1">{{ $log->notes }}</p>
                                            @endif
                                            <div class="text-xs text-base-content/60 mt-2">
                                                <span>{{ $log->created_at->format('M j, Y \a\t g:i A') }}</span>
                                                @if($log->user)
                                                    <span class="mx-1">&bull;</span>
                                                    <span>by {{ $log->user->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatusFields() {
    const status = document.getElementById('status').value;
    const conditionField = document.getElementById('condition-field');
    const damageChargeField = document.getElementById('damage-charge-field');

    if (status === 'returned') {
        conditionField.classList.remove('hidden');
    } else {
        conditionField.classList.add('hidden');
    }

    // Show damage charge for damaged returns
    document.querySelectorAll('input[name="condition"]').forEach(radio => {
        radio.addEventListener('change', function() {
            damageChargeField.classList.toggle('hidden', this.value !== 'damaged');
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleStatusFields);
</script>
@endpush
@endsection
