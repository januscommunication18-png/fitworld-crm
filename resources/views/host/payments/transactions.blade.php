@extends('layouts.dashboard')

@section('title', 'Transactions')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--credit-card] me-1 size-4"></span> Transactions</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Transactions</h1>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Status Tabs --}}
    <div class="tabs tabs-boxed bg-base-100 w-fit">
        <a href="{{ route('payments.transactions', ['status' => 'all']) }}"
           class="tab {{ $status === 'all' ? 'tab-active' : '' }}">
            All
            <span class="badge badge-sm ml-2">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('payments.transactions', ['status' => 'pending']) }}"
           class="tab {{ $status === 'pending' ? 'tab-active' : '' }}">
            Pending
            @if($counts['pending'] > 0)
                <span class="badge badge-warning badge-sm ml-2">{{ $counts['pending'] }}</span>
            @endif
        </a>
        <a href="{{ route('payments.transactions', ['status' => 'paid']) }}"
           class="tab {{ $status === 'paid' ? 'tab-active' : '' }}">
            Paid
            <span class="badge badge-success badge-sm ml-2">{{ $counts['paid'] }}</span>
        </a>
    </div>

    {{-- Transactions Table --}}
    <div class="card bg-base-100">
        <div class="card-body">
            @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $transaction->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $transaction->created_at->format('g:i A') }}</div>
                            </td>
                            <td>
                                <span class="font-mono text-xs">{{ $transaction->transaction_id }}</span>
                            </td>
                            <td>
                                @if($transaction->client)
                                    <div class="font-medium">{{ $transaction->client->full_name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $transaction->client->email }}</div>
                                @else
                                    <span class="text-base-content/50">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeIcon = match($transaction->type) {
                                        'class_booking' => 'yoga',
                                        'service_booking' => 'sparkles',
                                        'membership_purchase' => 'id-badge-2',
                                        'class_pack_purchase' => 'ticket',
                                        default => 'receipt',
                                    };
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--{{ $typeIcon }}] size-4 text-base-content/60"></span>
                                    <span>{{ $transaction->type_label }}</span>
                                </div>
                                @if($transaction->purchasable)
                                    <div class="text-xs text-base-content/60">
                                        {{ $transaction->metadata['item_name'] ?? $transaction->purchasable->name ?? '' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="font-semibold">{{ $transaction->formatted_total }}</span>
                            </td>
                            <td>
                                <span class="flex items-center gap-1">
                                    @php
                                        $methodIcon = match($transaction->payment_method) {
                                            'stripe' => 'credit-card',
                                            'membership' => 'id-badge-2',
                                            'pack' => 'ticket',
                                            default => 'cash',
                                        };
                                    @endphp
                                    <span class="icon-[tabler--{{ $methodIcon }}] size-4"></span>
                                    {{ $transaction->payment_method_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $transaction->status_badge_class }} badge-sm">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($transaction->status === 'pending')
                                        {{-- Confirm Payment Button --}}
                                        <button type="button"
                                                class="btn btn-success btn-sm gap-1"
                                                onclick="openConfirmModal('{{ $transaction->id }}', '{{ $transaction->transaction_id }}', '{{ $transaction->formatted_total }}')">
                                            <span class="icon-[tabler--check] size-4"></span>
                                            Confirm
                                        </button>

                                        {{-- Cancel Button --}}
                                        <button type="button"
                                                class="btn btn-ghost btn-sm text-error"
                                                onclick="openCancelModal('{{ $transaction->id }}', '{{ $transaction->transaction_id }}')">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    @else
                                        {{-- View Details --}}
                                        <a href="{{ route('payments.transactions.show', $transaction) }}" class="btn btn-ghost btn-sm">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <span class="icon-[tabler--receipt-off] size-16 text-base-content/20 mx-auto"></span>
                <h3 class="text-lg font-semibold mt-4">No Transactions</h3>
                <p class="text-base-content/60 mt-2">
                    @if($status === 'pending')
                        No pending transactions at the moment.
                    @else
                        No transactions found.
                    @endif
                </p>
            </div>
            @endif
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
