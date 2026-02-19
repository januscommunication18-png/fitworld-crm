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
                                        <form action="{{ route('payments.transactions.confirm', $transaction) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Confirm this payment? This will activate any associated booking or membership.')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm gap-1">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                Confirm
                                            </button>
                                        </form>

                                        {{-- Cancel Button --}}
                                        <form action="{{ route('payments.transactions.cancel', $transaction) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Cancel this transaction?')">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm text-error">
                                                <span class="icon-[tabler--x] size-4"></span>
                                            </button>
                                        </form>
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
@endsection
