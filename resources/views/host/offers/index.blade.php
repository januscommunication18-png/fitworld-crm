@extends('layouts.dashboard')

@section('title', $trans['nav.marketing.offers'] ?? 'Offers')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><span class="icon-[tabler--speakerphone] me-1 size-4"></span> {{ $trans['nav.marketing'] ?? 'Marketing' }}</li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['nav.marketing.offers'] ?? 'Offers' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['offers.title'] ?? 'Offers & Promotions' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['offers.description'] ?? 'Create targeted discounts and promotional offers for your clients.' }}</p>
        </div>
        <a href="{{ route('offers.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            {{ $trans['offers.create'] ?? 'Create Offer' }}
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--tag] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_offers']) }}</p>
                        <p class="text-xs text-base-content/60">{{ $trans['offers.total_offers'] ?? 'Total Offers' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($stats['active_offers']) }}</p>
                        <p class="text-xs text-base-content/60">{{ $trans['common.active'] ?? 'Active' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--receipt] size-5 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_redemptions']) }}</p>
                        <p class="text-xs text-base-content/60">{{ $trans['offers.redemptions'] ?? 'Redemptions' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--discount-2] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['total_discount_given'], 0) }}</p>
                        <p class="text-xs text-base-content/60">{{ $trans['offers.discounts_given'] ?? 'Discounts Given' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--currency-dollar] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['total_revenue'], 0) }}</p>
                        <p class="text-xs text-base-content/60">{{ $trans['offers.revenue_generated'] ?? 'Revenue Generated' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('offers.index') }}" class="tab {{ !$status ? 'tab-active' : '' }}">{{ $trans['common.all'] ?? 'All' }}</a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('offers.index', ['status' => $key]) }}"
                   class="tab {{ $status === $key ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Content --}}
    @if($offers->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--tag-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['offers.no_offers'] ?? 'No Offers Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['offers.get_started'] ?? 'Create your first offer to attract and retain clients.' }}</p>
                <a href="{{ route('offers.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['offers.create_first'] ?? 'Create First Offer' }}
                </a>
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ $trans['offers.offer'] ?? 'Offer' }}</th>
                                <th>{{ $trans['offers.discount'] ?? 'Discount' }}</th>
                                <th>{{ $trans['offers.target'] ?? 'Target' }}</th>
                                <th>{{ $trans['offers.duration'] ?? 'Duration' }}</th>
                                <th>{{ $trans['offers.redemptions'] ?? 'Redemptions' }}</th>
                                <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                <th class="w-24">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                                <tr class="{{ !$offer->isActive() ? 'opacity-60' : '' }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                                @if($offer->discount_type === 'percentage')
                                                    <span class="icon-[tabler--percentage] size-5 text-primary"></span>
                                                @elseif($offer->discount_type === 'fixed_amount')
                                                    <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                                                @else
                                                    <span class="icon-[tabler--gift] size-5 text-primary"></span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('offers.show', $offer) }}" class="font-medium hover:text-primary">
                                                    {{ $offer->name }}
                                                </a>
                                                @if($offer->code)
                                                    <p class="text-xs text-base-content/60">
                                                        <span class="font-mono bg-base-200 px-1 rounded">{{ $offer->code }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ $offer->getFormattedDiscount() }}</span>
                                    </td>
                                    <td>
                                        @if($offer->segment)
                                            <span class="badge badge-soft badge-primary badge-sm">{{ $offer->segment->name }}</span>
                                        @else
                                            <span class="text-sm text-base-content/60">{{ \App\Models\Offer::getTargetAudiences()[$offer->target_audience] ?? 'All' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($offer->start_date || $offer->end_date)
                                            <span class="text-sm">
                                                @if($offer->start_date)
                                                    {{ $offer->start_date->format('M d') }}
                                                @else
                                                    Start
                                                @endif
                                                -
                                                @if($offer->end_date)
                                                    {{ $offer->end_date->format('M d, Y') }}
                                                @else
                                                    No end
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-sm text-base-content/60">{{ $trans['offers.no_limit'] ?? 'No limit' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ number_format($offer->total_redemptions) }}</span>
                                        @if($offer->total_usage_limit)
                                            <span class="text-xs text-base-content/60">/ {{ number_format($offer->total_usage_limit) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-soft {{ \App\Models\Offer::getStatusBadgeClass($offer->status) }} badge-sm">
                                            {{ ucfirst($offer->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <details class="dropdown dropdown-end">
                                            <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                                                <li><a href="{{ route('offers.show', $offer) }}"><span class="icon-[tabler--eye] size-4"></span> {{ $trans['btn.view'] ?? 'View' }}</a></li>
                                                <li><a href="{{ route('offers.edit', $offer) }}"><span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}</a></li>
                                                <li>
                                                    <form action="{{ route('offers.duplicate', $offer) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left flex items-center gap-2">
                                                            <span class="icon-[tabler--copy] size-4"></span> {{ $trans['btn.duplicate'] ?? 'Duplicate' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('offers.toggle-status', $offer) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left flex items-center gap-2">
                                                            @if($offer->status === 'active')
                                                                <span class="icon-[tabler--player-pause] size-4"></span> {{ $trans['btn.pause'] ?? 'Pause' }}
                                                            @else
                                                                <span class="icon-[tabler--player-play] size-4"></span> {{ $trans['btn.activate'] ?? 'Activate' }}
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('{{ $trans['msg.confirm.delete_offer'] ?? 'Delete this offer?' }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                            <span class="icon-[tabler--trash] size-4"></span> {{ $trans['btn.delete'] ?? 'Delete' }}
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($offers->hasPages())
                    <div class="p-4 border-t border-base-300">
                        {{ $offers->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
