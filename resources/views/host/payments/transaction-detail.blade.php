@extends('layouts.dashboard')

@section('title', 'Transaction ' . $transaction->transaction_id)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('payments.transactions') }}"><span class="icon-[tabler--credit-card] me-1 size-4"></span> Transactions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $transaction->transaction_id }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Transaction Details</h1>
            <p class="text-base-content/60 mt-1">{{ $transaction->transaction_id }}</p>
        </div>
        <a href="{{ route('payments.transactions') }}" class="btn btn-ghost">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to Transactions
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Transaction Overview --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Overview</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-base-content/60">Status</span>
                            <div class="mt-1">
                                <span class="badge {{ $transaction->status_badge_class }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Type</span>
                            <p class="font-medium">{{ $transaction->type_label }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Date</span>
                            <p class="font-medium">{{ $transaction->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Payment Method</span>
                            <p class="font-medium">{{ $transaction->payment_method_label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Amount Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Amount</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Subtotal</span>
                            <span>{{ $transaction->formatted_subtotal }}</span>
                        </div>
                        @if($transaction->tax_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Tax</span>
                            <span>${{ number_format($transaction->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($transaction->discount_amount > 0)
                        <div class="flex justify-between text-success">
                            <span>Discount</span>
                            <span>-${{ number_format($transaction->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="divider my-2"></div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total</span>
                            <span>{{ $transaction->formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Details (if applicable) --}}
            @if($transaction->booking)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Booking Details</h2>
                    <div class="space-y-3">
                        @if($transaction->booking->bookable)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Service/Class</span>
                            <span class="font-medium">{{ $transaction->booking->bookable->name ?? 'N/A' }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Booking Status</span>
                            <span class="badge badge-sm">{{ ucfirst($transaction->booking->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Booking ID</span>
                            <span class="font-mono text-sm">{{ $transaction->booking->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Item Details (for purchases) --}}
            @if($transaction->purchasable)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Purchase Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Item</span>
                            <span class="font-medium">{{ $transaction->purchasable->name ?? 'N/A' }}</span>
                        </div>
                        @if(isset($transaction->metadata['item_name']))
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Description</span>
                            <span>{{ $transaction->metadata['item_name'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Payment Details --}}
            @if($transaction->stripe_charge_id || $transaction->stripe_payment_intent_id)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Payment Details</h2>
                    <div class="space-y-3">
                        @if($transaction->stripe_charge_id)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Stripe Charge ID</span>
                            <span class="font-mono text-sm">{{ $transaction->stripe_charge_id }}</span>
                        </div>
                        @endif
                        @if($transaction->stripe_payment_intent_id)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Payment Intent ID</span>
                            <span class="font-mono text-sm">{{ $transaction->stripe_payment_intent_id }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes & Activity Log --}}
            @if($transaction->notes || ($transaction->metadata && (isset($transaction->metadata['cancellation_reason']) || isset($transaction->metadata['confirmed_by_name']))))
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Notes & Activity</h2>

                    {{-- Cancellation Reason --}}
                    @if(isset($transaction->metadata['cancellation_reason']))
                    <div class="alert alert-error mb-4">
                        <span class="icon-[tabler--x-circle] size-5"></span>
                        <div>
                            <h4 class="font-semibold">Cancellation Reason</h4>
                            <p class="text-sm">{{ $transaction->metadata['cancellation_reason'] }}</p>
                            @if(isset($transaction->metadata['cancelled_by_name']))
                            <p class="text-xs opacity-70 mt-1">
                                Cancelled by {{ $transaction->metadata['cancelled_by_name'] }}
                                @if(isset($transaction->metadata['cancelled_at']))
                                on {{ \Carbon\Carbon::parse($transaction->metadata['cancelled_at'])->format('M j, Y \a\t g:i A') }}
                                @endif
                            </p>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Confirmation Info --}}
                    @if(isset($transaction->metadata['confirmed_by_name']) && $transaction->status === 'paid')
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--check-circle] size-5"></span>
                        <div>
                            <h4 class="font-semibold">Payment Confirmed</h4>
                            <p class="text-sm">
                                Confirmed by {{ $transaction->metadata['confirmed_by_name'] }}
                                @if(isset($transaction->metadata['confirmed_at']))
                                on {{ \Carbon\Carbon::parse($transaction->metadata['confirmed_at'])->format('M j, Y \a\t g:i A') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Notes --}}
                    @if($transaction->notes)
                    <div>
                        <span class="text-sm text-base-content/60 font-medium">Notes</span>
                        <div class="mt-2 bg-base-200 rounded-lg p-3">
                            <pre class="whitespace-pre-wrap text-sm font-sans">{{ $transaction->notes }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Client Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Client</h2>
                    @if($transaction->client)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-primary/10 text-primary rounded-full w-12 h-12">
                                <span class="text-lg">{{ substr($transaction->client->first_name, 0, 1) }}{{ substr($transaction->client->last_name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium">{{ $transaction->client->full_name }}</p>
                            <p class="text-sm text-base-content/60">{{ $transaction->client->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('clients.show', $transaction->client) }}" class="btn btn-outline btn-sm w-full">
                            <span class="icon-[tabler--user] size-4"></span>
                            View Client Profile
                        </a>
                    </div>
                    @else
                    <p class="text-base-content/50">No client associated</p>
                    @endif
                </div>
            </div>

            {{-- Invoice --}}
            @if($transaction->invoice)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Invoice</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Invoice #</span>
                            <span class="font-mono">{{ $transaction->invoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Status</span>
                            <span class="badge badge-sm">{{ ucfirst($transaction->invoice->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Actions</h2>
                    <div class="space-y-2">
                        @if($transaction->status === 'pending')
                        <button type="button"
                                class="btn btn-success w-full"
                                onclick="openConfirmModal('{{ $transaction->id }}', '{{ $transaction->transaction_id }}', '{{ $transaction->formatted_total }}')">
                            <span class="icon-[tabler--check] size-4"></span>
                            Confirm Payment
                        </button>
                        <button type="button"
                                class="btn btn-error btn-outline w-full"
                                onclick="openCancelModal('{{ $transaction->id }}', '{{ $transaction->transaction_id }}')">
                            <span class="icon-[tabler--x] size-4"></span>
                            Cancel Transaction
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hide From Books (Cash Transactions Only) --}}
            @if($transaction->canHideFromBooks())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-2">Bookkeeping</h2>
                    <p class="text-sm text-base-content/60 mb-4">
                        Cash transactions can be hidden from financial reports for tax purposes.
                    </p>
                    <form action="{{ route('payments.transactions.toggle-hide', $transaction) }}" method="POST">
                        @csrf
                        <label class="flex items-center justify-between cursor-pointer p-3 rounded-lg border {{ $transaction->hide_from_books ? 'border-warning bg-warning/5' : 'border-base-200' }}">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--eye-off] size-5 {{ $transaction->hide_from_books ? 'text-warning' : 'text-base-content/40' }}"></span>
                                <div>
                                    <span class="font-medium">Hide from books</span>
                                    @if($transaction->hide_from_books && $transaction->hidden_at)
                                    <p class="text-xs text-base-content/50 mt-0.5">
                                        Hidden {{ $transaction->hidden_at->diffForHumans() }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            <input type="checkbox"
                                   class="toggle toggle-warning"
                                   {{ $transaction->hide_from_books ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                        </label>
                    </form>
                    @if($transaction->hide_from_books)
                    <div class="alert alert-warning mt-3">
                        <span class="icon-[tabler--alert-triangle] size-4"></span>
                        <span class="text-sm">This transaction is hidden from financial reports.</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Activity</h2>
                    <div class="space-y-4">
                        {{-- Transaction Created --}}
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--plus] size-4 text-primary"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Transaction created</p>
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    {{ $transaction->type_label }} for {{ $transaction->formatted_total }}
                                    @if($transaction->client)
                                        by {{ $transaction->client->full_name }}
                                    @endif
                                </p>
                                <p class="text-xs text-base-content/40 mt-1">
                                    {{ $transaction->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        @if($transaction->payment_method)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-info/10 flex items-center justify-center">
                                    <span class="icon-[tabler--credit-card] size-4 text-info"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Payment method: {{ $transaction->payment_method_label }}</p>
                                @if($transaction->stripe_payment_intent_id)
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    Stripe Payment Intent: {{ Str::limit($transaction->stripe_payment_intent_id, 20) }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Payment Confirmed --}}
                        @if($transaction->paid_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Payment confirmed</p>
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    @if($transaction->metadata['confirmed_by_name'] ?? null)
                                        Confirmed by {{ $transaction->metadata['confirmed_by_name'] }}
                                    @elseif($transaction->metadata['confirmed_by'] ?? null)
                                        Confirmed by staff
                                    @elseif($transaction->payment_method === 'stripe')
                                        Automatically confirmed via Stripe
                                    @else
                                        Payment received
                                    @endif
                                </p>
                                <p class="text-xs text-base-content/40 mt-1">
                                    {{ $transaction->paid_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Booking Created --}}
                        @if($transaction->booking)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--calendar-check] size-4 text-secondary"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Booking created</p>
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    {{ $transaction->booking->bookable->name ?? 'Service/Class' }} - {{ ucfirst($transaction->booking->status) }}
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Refunded --}}
                        @if($transaction->refunded_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-warning/10 flex items-center justify-center">
                                    <span class="icon-[tabler--receipt-refund] size-4 text-warning"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Refunded</p>
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    @if($transaction->refund_amount)
                                        ${{ number_format($transaction->refund_amount, 2) }} refunded
                                    @endif
                                    @if($transaction->refund_reason)
                                        - {{ $transaction->refund_reason }}
                                    @endif
                                </p>
                                <p class="text-xs text-base-content/40 mt-1">
                                    {{ $transaction->refunded_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Cancelled --}}
                        @if($transaction->status === 'cancelled')
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-error/10 flex items-center justify-center">
                                    <span class="icon-[tabler--x] size-4 text-error"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Transaction cancelled</p>
                                @if(isset($transaction->metadata['cancelled_by_name']))
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    By {{ $transaction->metadata['cancelled_by_name'] }}
                                </p>
                                @endif
                                @if(isset($transaction->metadata['cancellation_reason']))
                                <p class="text-xs text-error/80 mt-0.5">
                                    Reason: {{ $transaction->metadata['cancellation_reason'] }}
                                </p>
                                @endif
                                <p class="text-xs text-base-content/40 mt-1">
                                    @if(isset($transaction->metadata['cancelled_at']))
                                        {{ \Carbon\Carbon::parse($transaction->metadata['cancelled_at'])->format('M j, Y \a\t g:i A') }}
                                    @else
                                        {{ $transaction->updated_at->format('M j, Y \a\t g:i A') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Failed --}}
                        @if($transaction->status === 'failed')
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-error/10 flex items-center justify-center">
                                    <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">Payment failed</p>
                                @if($transaction->failure_reason)
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    {{ $transaction->failure_reason }}
                                </p>
                                @endif
                                <p class="text-xs text-base-content/40 mt-1">
                                    {{ $transaction->updated_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Payment Modal --}}
<div id="confirm-modal" class="fixed inset-0 z-50 hidden" role="dialog" tabindex="-1">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal()"></div>
    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md relative">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                    Confirm Payment
                </h3>
                <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeConfirmModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <form id="confirm-form" method="POST">
                @csrf
                <div class="p-4 space-y-4">
                    <p class="text-base-content/70">
                        Confirm payment for transaction <span id="confirm-transaction-id" class="font-mono font-semibold"></span>?
                    </p>
                    <p class="text-sm text-base-content/60">
                        Amount: <span id="confirm-amount" class="font-semibold text-success"></span>
                    </p>

                    <div class="form-control">
                        <label class="label" for="confirm-notes">
                            <span class="label-text">Notes (optional)</span>
                        </label>
                        <textarea id="confirm-notes"
                                  name="notes"
                                  class="textarea textarea-bordered h-24"
                                  placeholder="Add any notes about this payment confirmation..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost" onclick="closeConfirmModal()">Cancel</button>
                    <button type="submit" class="btn btn-success gap-1">
                        <span class="icon-[tabler--check] size-4"></span>
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Transaction Modal --}}
<div id="cancel-modal" class="fixed inset-0 z-50 hidden" role="dialog" tabindex="-1">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeCancelModal()"></div>
    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md relative">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--x-circle] size-6 text-error"></span>
                    Cancel Transaction
                </h3>
                <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeCancelModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <form id="cancel-form" method="POST">
                @csrf
                <div class="p-4 space-y-4">
                    <p class="text-base-content/70">
                        Cancel transaction <span id="cancel-transaction-id" class="font-mono font-semibold"></span>?
                    </p>
                    <div class="alert alert-warning">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <span>This action cannot be undone.</span>
                    </div>

                    <div class="form-control">
                        <label class="label" for="cancel-reason">
                            <span class="label-text">Reason for cancellation <span class="text-error">*</span></span>
                        </label>
                        <textarea id="cancel-reason"
                                  name="reason"
                                  class="textarea textarea-bordered h-24"
                                  placeholder="Enter the reason for cancelling this transaction..."
                                  required></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Back</button>
                    <button type="submit" class="btn btn-error gap-1">
                        <span class="icon-[tabler--x] size-4"></span>
                        Cancel Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const confirmModal = document.getElementById('confirm-modal');
    const cancelModal = document.getElementById('cancel-modal');

    function openConfirmModal(transactionId, transactionCode, amount) {
        document.getElementById('confirm-transaction-id').textContent = transactionCode;
        document.getElementById('confirm-amount').textContent = amount;
        document.getElementById('confirm-form').action = '/payments/transactions/' + transactionId + '/confirm';
        document.getElementById('confirm-notes').value = '';
        confirmModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal() {
        confirmModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openCancelModal(transactionId, transactionCode) {
        document.getElementById('cancel-transaction-id').textContent = transactionCode;
        document.getElementById('cancel-form').action = '/payments/transactions/' + transactionId + '/cancel';
        document.getElementById('cancel-reason').value = '';
        cancelModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCancelModal() {
        cancelModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
            closeCancelModal();
        }
    });
</script>
@endpush
@endsection
