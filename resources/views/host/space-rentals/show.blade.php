@extends('layouts.dashboard')

@section('title', $rental->reference_number)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('space-rentals.index') }}">{{ $trans['nav.space_rentals'] ?? 'Space Rentals' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $rental->reference_number }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">{{ $rental->reference_number }}</h1>
                <span class="badge {{ $rental->status_badge_class }} badge-soft">{{ $rental->formatted_status }}</span>
            </div>
            <div class="flex items-center gap-4 mt-2 text-base-content/60">
                <div class="flex items-center gap-1">
                    <span class="icon-[tabler--{{ $rental->purpose_icon }}] size-4"></span>
                    {{ $rental->formatted_purpose }}
                </div>
                <div class="flex items-center gap-1">
                    <span class="icon-[tabler--calendar] size-4"></span>
                    {{ $rental->formatted_date }}
                </div>
                <div class="flex items-center gap-1">
                    <span class="icon-[tabler--clock] size-4"></span>
                    {{ $rental->formatted_time_range }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($rental->canBeCancelled())
                <button onclick="document.getElementById('cancel-modal').showModal()" class="btn btn-error btn-outline btn-sm">
                    <span class="icon-[tabler--x] size-4"></span>
                    {{ $trans['btn.cancel'] ?? 'Cancel' }}
                </button>
            @endif
            @if(in_array($rental->status, ['draft', 'pending']))
                <a href="{{ route('space-rentals.edit', $rental) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span>
                    {{ $trans['btn.edit'] ?? 'Edit' }}
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Status Actions Card --}}
            @if($rental->isActive())
            <div class="card bg-base-100 border-2 border-primary/20">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.actions'] ?? 'Actions' }}</h3>

                    <div class="flex flex-wrap gap-3">
                        @if($rental->canBeConfirmed())
                            <form action="{{ route('space-rentals.confirm', $rental) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <span class="icon-[tabler--check] size-5"></span>
                                    {{ $trans['btn.confirm'] ?? 'Confirm' }}
                                </button>
                            </form>
                        @endif

                        @if($rental->canBeStarted())
                            @if($rental->isWaiverPending() || $rental->isDepositPending())
                                <div class="alert alert-warning">
                                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                                    <span>
                                        {{ $trans['space_rentals.complete_requirements'] ?? 'Complete requirements before starting:' }}
                                        @if($rental->isWaiverPending()) {{ $trans['space_rentals.sign_waiver'] ?? 'Sign waiver' }}@endif
                                        @if($rental->isWaiverPending() && $rental->isDepositPending()), @endif
                                        @if($rental->isDepositPending()) {{ $trans['space_rentals.pay_deposit'] ?? 'Pay deposit' }}@endif
                                    </span>
                                </div>
                            @else
                                <form action="{{ route('space-rentals.start', $rental) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <span class="icon-[tabler--player-play] size-5"></span>
                                        {{ $trans['space_rentals.start'] ?? 'Start Rental' }}
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if($rental->canBeCompleted())
                            <button onclick="document.getElementById('complete-modal').showModal()" class="btn btn-success">
                                <span class="icon-[tabler--check] size-5"></span>
                                {{ $trans['space_rentals.complete'] ?? 'Complete Rental' }}
                            </button>
                        @endif
                    </div>

                    {{-- Waiver & Deposit Actions --}}
                    @if($rental->isConfirmed() || $rental->isInProgress())
                    <div class="mt-4 pt-4 border-t border-base-200 flex flex-wrap gap-3">
                        @if($rental->isWaiverPending())
                            <button onclick="document.getElementById('waiver-modal').showModal()" class="btn btn-outline btn-sm">
                                <span class="icon-[tabler--file-certificate] size-4"></span>
                                {{ $trans['space_rentals.sign_waiver'] ?? 'Sign Waiver' }}
                            </button>
                        @endif

                        @if($rental->isDepositPending())
                            <form action="{{ route('space-rentals.record-deposit', $rental) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">
                                    <span class="icon-[tabler--cash] size-4"></span>
                                    {{ $trans['space_rentals.record_deposit'] ?? 'Record Deposit Payment' }}
                                </button>
                            </form>
                        @endif

                        @if($rental->deposit_status === 'paid')
                            <button onclick="document.getElementById('refund-modal').showModal()" class="btn btn-outline btn-sm">
                                <span class="icon-[tabler--receipt-refund] size-4"></span>
                                {{ $trans['space_rentals.refund_deposit'] ?? 'Refund Deposit' }}
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Rental Details Card --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.details'] ?? 'Rental Details' }}</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-base-200/50 rounded-lg p-4">
                            <div class="text-sm text-base-content/60 mb-1">{{ $trans['space_rentals.space'] ?? 'Space' }}</div>
                            <div class="font-medium">{{ $rental->config?->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-base-content/60">{{ $rental->location_display }}</div>
                        </div>
                        <div class="bg-base-200/50 rounded-lg p-4">
                            <div class="text-sm text-base-content/60 mb-1">{{ $trans['schedule.duration'] ?? 'Duration' }}</div>
                            <div class="font-medium">{{ $rental->duration_hours }} {{ $trans['common.hours'] ?? 'hours' }}</div>
                        </div>
                        <div class="bg-base-200/50 rounded-lg p-4">
                            <div class="text-sm text-base-content/60 mb-1">{{ $trans['field.date'] ?? 'Date' }}</div>
                            <div class="font-medium">{{ $rental->start_time->format('l, M j, Y') }}</div>
                        </div>
                        <div class="bg-base-200/50 rounded-lg p-4">
                            <div class="text-sm text-base-content/60 mb-1">{{ $trans['field.time'] ?? 'Time' }}</div>
                            <div class="font-medium">{{ $rental->formatted_time_range }}</div>
                        </div>
                    </div>

                    @if($rental->purpose_notes)
                    <div class="mt-4 pt-4 border-t border-base-200">
                        <div class="text-sm text-base-content/60 mb-2">{{ $trans['space_rentals.purpose_notes'] ?? 'Purpose Details' }}</div>
                        <p class="text-base-content/80">{{ $rental->purpose_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Client Card --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['field.client'] ?? 'Client' }}</h3>

                    <div class="flex items-center gap-4">
                        @if($rental->client)
                            <x-avatar
                                :src="$rental->client->avatar_url ?? null"
                                :initials="$rental->client->initials"
                                :alt="$rental->client->full_name"
                                size="lg"
                            />
                        @else
                            <div class="w-14 h-14 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--user] size-6 text-base-content/40"></span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <div class="font-semibold text-lg">{{ $rental->client_name }}</div>
                            @if($rental->client_company)
                                <div class="text-sm text-base-content/60">{{ $rental->client_company }}</div>
                            @endif
                            @if($rental->client_email)
                                <div class="flex items-center gap-2 text-sm text-base-content/60 mt-1">
                                    <span class="icon-[tabler--mail] size-4"></span>
                                    {{ $rental->client_email }}
                                </div>
                            @endif
                            @if($rental->client_phone)
                                <div class="flex items-center gap-2 text-sm text-base-content/60">
                                    <span class="icon-[tabler--phone] size-4"></span>
                                    {{ $rental->client_phone }}
                                </div>
                            @endif
                        </div>
                        @if($rental->client)
                            <a href="{{ route('clients.show', $rental->client) }}" class="btn btn-ghost btn-sm">
                                {{ $trans['btn.view_profile'] ?? 'View Profile' }}
                            </a>
                        @endif
                    </div>

                    @if($rental->isExternalClient())
                        <div class="mt-3 px-3 py-2 bg-info/10 rounded-lg text-sm text-info">
                            <span class="icon-[tabler--info-circle] size-4 inline"></span>
                            {{ $trans['space_rentals.external_client_note'] ?? 'This is an external client not registered in your system.' }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status History --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.history'] ?? 'Status History' }}</h3>

                    <div class="space-y-4">
                        @foreach($rental->statusLogs as $log)
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--arrow-right] size-4 text-base-content/60"></span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">{{ $log->status_change_label }}</div>
                                @if($log->notes)
                                    <p class="text-sm text-base-content/60 mt-1">{{ $log->notes }}</p>
                                @endif
                                <div class="text-xs text-base-content/40 mt-1">
                                    {{ $log->created_at->format('M j, Y g:i A') }}
                                    @if($log->updatedByUser)
                                        - {{ $log->updatedByUser->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Pricing Card --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.pricing'] ?? 'Pricing' }}</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</span>
                            <span class="font-medium">{{ $rental->formatted_hourly_rate }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['schedule.duration'] ?? 'Duration' }}</span>
                            <span class="font-medium">{{ $rental->duration_hours }} hrs</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.subtotal'] ?? 'Subtotal' }}</span>
                            <span class="font-medium">{{ $rental->formatted_subtotal }}</span>
                        </div>
                        @if($rental->tax_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.tax'] ?? 'Tax' }}</span>
                            <span class="font-medium">{{ $rental->formatted_tax }}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t border-base-200 flex justify-between">
                            <span class="font-semibold">{{ $trans['field.total'] ?? 'Total' }}</span>
                            <span class="text-lg font-bold text-primary">{{ $rental->formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deposit Card --}}
            @if($rental->requiresDeposit())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.deposit'] ?? 'Security Deposit' }}</h3>

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-bold">{{ $rental->formatted_deposit }}</span>
                        <span class="badge {{ $rental->deposit_status_badge_class }} badge-soft">
                            {{ $rental->formatted_deposit_status }}
                        </span>
                    </div>

                    @if($rental->deposit_refund_amount)
                    <div class="text-sm text-base-content/60">
                        {{ $trans['space_rentals.refunded'] ?? 'Refunded' }}: {{ \App\Models\MembershipPlan::getCurrencySymbol($rental->currency) }}{{ number_format($rental->deposit_refund_amount, 2) }}
                        @if($rental->deposit_refund_reason)
                            <br>{{ $trans['field.reason'] ?? 'Reason' }}: {{ $rental->deposit_refund_reason }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Waiver Card --}}
            @if($rental->requiresWaiver())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['space_rentals.waiver'] ?? 'Liability Waiver' }}</h3>

                    @if($rental->waiver_signed)
                        <div class="flex items-center gap-3 text-success mb-3">
                            <span class="icon-[tabler--circle-check-filled] size-6"></span>
                            <div>
                                <div class="font-medium">{{ $trans['space_rentals.waiver_signed'] ?? 'Waiver Signed' }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ $rental->waiver_signed_at->format('M j, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-base-content/60">
                            {{ $trans['space_rentals.signed_by'] ?? 'Signed by' }}: {{ $rental->waiver_signer_name }}
                        </div>
                    @else
                        <div class="flex items-center gap-3 text-warning">
                            <span class="icon-[tabler--alert-circle] size-6"></span>
                            <div>
                                <div class="font-medium">{{ $trans['space_rentals.waiver_pending'] ?? 'Waiver Pending' }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ $trans['space_rentals.waiver_required_msg'] ?? 'Must be signed before rental starts' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Damage Card (if completed with damage) --}}
            @if($rental->isCompleted() && $rental->damage_reported)
            <div class="card bg-base-100 border border-error/20">
                <div class="card-body">
                    <h3 class="font-semibold text-error mb-4">{{ $trans['space_rentals.damage'] ?? 'Damage Reported' }}</h3>

                    @if($rental->damage_notes)
                        <p class="text-sm text-base-content/70 mb-3">{{ $rental->damage_notes }}</p>
                    @endif

                    @if($rental->damage_charge > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">{{ $trans['space_rentals.damage_charge'] ?? 'Damage Charge' }}</span>
                            <span class="font-bold text-error">
                                {{ \App\Models\MembershipPlan::getCurrencySymbol($rental->currency) }}{{ number_format($rental->damage_charge, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Internal Notes --}}
            @if($rental->internal_notes)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">{{ $trans['field.internal_notes'] ?? 'Internal Notes' }}</h3>
                    <p class="text-sm text-base-content/70">{{ $rental->internal_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Cancellation Info --}}
            @if($rental->isCancelled())
            <div class="card bg-base-100 border border-error/20">
                <div class="card-body">
                    <h3 class="font-semibold text-error mb-4">{{ $trans['space_rentals.cancelled'] ?? 'Cancelled' }}</h3>
                    <div class="text-sm text-base-content/60">
                        {{ $rental->cancelled_at->format('M j, Y g:i A') }}
                        @if($rental->cancelledBy)
                            - {{ $rental->cancelledBy->name }}
                        @endif
                    </div>
                    @if($rental->cancellation_reason)
                        <p class="text-sm text-base-content/70 mt-2">{{ $rental->cancellation_reason }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Waiver Modal --}}
<dialog id="waiver-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">{{ $trans['space_rentals.sign_waiver'] ?? 'Sign Liability Waiver' }}</h3>
        <form action="{{ route('space-rentals.sign-waiver', $rental) }}" method="POST" class="mt-4">
            @csrf
            <div class="form-control">
                <label for="signer_name" class="label">
                    <span class="label-text">{{ $trans['space_rentals.signer_name'] ?? 'Full Legal Name' }} <span class="text-error">*</span></span>
                </label>
                <input type="text" name="signer_name" id="signer_name" class="input input-bordered" required>
            </div>
            <p class="text-sm text-base-content/60 mt-4">
                {{ $trans['space_rentals.waiver_agreement'] ?? 'By signing, the client agrees to the liability waiver terms.' }}
            </p>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('waiver-modal').close()">
                    {{ $trans['btn.cancel'] ?? 'Cancel' }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ $trans['space_rentals.sign'] ?? 'Sign Waiver' }}
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

{{-- Complete Modal --}}
<dialog id="complete-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">{{ $trans['space_rentals.complete_rental'] ?? 'Complete Rental' }}</h3>
        <form action="{{ route('space-rentals.complete', $rental) }}" method="POST" class="mt-4">
            @csrf
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="has_damage" value="1" class="checkbox checkbox-error" id="has-damage-check">
                    <span class="label-text">{{ $trans['space_rentals.report_damage'] ?? 'Report damage to space' }}</span>
                </label>
            </div>
            <div id="damage-fields" class="hidden mt-4 space-y-4">
                <div class="form-control">
                    <label for="damage_notes" class="label">
                        <span class="label-text">{{ $trans['space_rentals.damage_description'] ?? 'Damage Description' }}</span>
                    </label>
                    <textarea name="damage_notes" id="damage_notes" rows="3" class="textarea textarea-bordered"
                        placeholder="{{ $trans['space_rentals.describe_damage'] ?? 'Describe the damage...' }}"></textarea>
                </div>
                <div class="form-control">
                    <label for="damage_charge" class="label">
                        <span class="label-text">{{ $trans['space_rentals.damage_charge'] ?? 'Damage Charge' }}</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="damage_charge" id="damage_charge"
                        class="input input-bordered" placeholder="0.00">
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('complete-modal').close()">
                    {{ $trans['btn.cancel'] ?? 'Cancel' }}
                </button>
                <button type="submit" class="btn btn-success">
                    {{ $trans['space_rentals.complete'] ?? 'Complete' }}
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

{{-- Refund Modal --}}
@if($rental->deposit_status === 'paid')
<dialog id="refund-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">{{ $trans['space_rentals.refund_deposit'] ?? 'Refund Deposit' }}</h3>
        <form action="{{ route('space-rentals.refund-deposit', $rental) }}" method="POST" class="mt-4">
            @csrf
            <div class="form-control">
                <label for="refund_amount" class="label">
                    <span class="label-text">{{ $trans['space_rentals.refund_amount'] ?? 'Refund Amount' }} <span class="text-error">*</span></span>
                    <span class="label-text-alt">{{ $trans['common.max'] ?? 'Max' }}: {{ $rental->formatted_deposit }}</span>
                </label>
                <input type="number" step="0.01" min="0.01" max="{{ $rental->deposit_amount }}"
                    name="refund_amount" id="refund_amount" class="input input-bordered"
                    value="{{ $rental->deposit_amount }}" required>
            </div>
            <div class="form-control mt-4">
                <label for="refund_reason" class="label">
                    <span class="label-text">{{ $trans['field.reason'] ?? 'Reason' }}</span>
                </label>
                <textarea name="refund_reason" id="refund_reason" rows="2" class="textarea textarea-bordered"
                    placeholder="{{ $trans['space_rentals.refund_reason_placeholder'] ?? 'Optional reason for refund' }}"></textarea>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('refund-modal').close()">
                    {{ $trans['btn.cancel'] ?? 'Cancel' }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ $trans['space_rentals.process_refund'] ?? 'Process Refund' }}
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endif

{{-- Cancel Modal --}}
<dialog id="cancel-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">{{ $trans['space_rentals.cancel_rental'] ?? 'Cancel Rental' }}</h3>
        <form action="{{ route('space-rentals.cancel', $rental) }}" method="POST" class="mt-4">
            @csrf
            <div class="form-control">
                <label for="cancellation_reason" class="label">
                    <span class="label-text">{{ $trans['space_rentals.cancellation_reason'] ?? 'Reason for cancellation' }}</span>
                </label>
                <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="textarea textarea-bordered"
                    placeholder="{{ $trans['space_rentals.cancellation_reason_placeholder'] ?? 'Why is this rental being cancelled?' }}"></textarea>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('cancel-modal').close()">
                    {{ $trans['btn.go_back'] ?? 'Go Back' }}
                </button>
                <button type="submit" class="btn btn-error">
                    {{ $trans['space_rentals.confirm_cancel'] ?? 'Yes, Cancel Rental' }}
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const damageCheck = document.getElementById('has-damage-check');
    const damageFields = document.getElementById('damage-fields');

    if (damageCheck && damageFields) {
        damageCheck.addEventListener('change', function() {
            damageFields.classList.toggle('hidden', !this.checked);
        });
    }
});
</script>
@endpush
@endsection
