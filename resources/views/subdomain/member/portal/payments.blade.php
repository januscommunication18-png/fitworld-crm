@extends('layouts.subdomain')

@section('title', 'Payment History — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            <h1 class="text-2xl font-bold mb-6">Payment History</h1>

            @if($transactions->count() > 0)
                <div class="card bg-base-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>
                                        <div class="text-sm">
                                            {{ $transaction->created_at->format('M j, Y') }}
                                        </div>
                                        <div class="text-xs text-base-content/50">
                                            {{ $transaction->created_at->format('g:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium">
                                            {{ $transaction->metadata['item_name'] ?? $transaction->type_label }}
                                        </div>
                                        <div class="text-xs text-base-content/50">
                                            {{ $transaction->payment_method_label }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $transaction->status_badge_class }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-semibold">
                                        {{ $transaction->formatted_total }}
                                    </td>
                                    <td>
                                        @if($transaction->invoice)
                                        <a href="{{ route('member.portal.invoice.download', ['subdomain' => $host->subdomain, 'invoice' => $transaction->invoice->id]) }}"
                                           class="btn btn-ghost btn-sm"
                                           title="Download Invoice">
                                            <span class="icon-[tabler--download] size-4"></span>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--receipt-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Payment History</h3>
                        <p class="text-base-content/60 mt-2">
                            You haven't made any payments yet.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
