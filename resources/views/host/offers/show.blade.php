@extends('layouts.dashboard')

@section('title', $offer->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('offers.index') }}"><span class="icon-[tabler--tag] me-1 size-4"></span> Offers</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $offer->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('offers.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    @if($offer->discount_type === 'percentage')
                        <span class="icon-[tabler--percentage] size-6 text-primary"></span>
                    @elseif($offer->discount_type === 'fixed_amount')
                        <span class="icon-[tabler--currency-dollar] size-6 text-primary"></span>
                    @else
                        <span class="icon-[tabler--gift] size-6 text-primary"></span>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold">{{ $offer->name }}</h1>
                        <span class="badge badge-soft {{ \App\Models\Offer::getStatusBadgeClass($offer->status) }}">
                            {{ ucfirst($offer->status) }}
                        </span>
                    </div>
                    @if($offer->code)
                        <p class="text-base-content/60 mt-1">
                            Code: <span class="font-mono bg-base-200 px-2 py-0.5 rounded text-sm">{{ $offer->code }}</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('offers.toggle-status', $offer) }}" method="POST" class="inline">
                @csrf
                @if($offer->status === 'active')
                    <button type="submit" class="btn btn-outline btn-warning btn-sm">
                        <span class="icon-[tabler--player-pause] size-4"></span>
                        Pause
                    </button>
                @elseif(in_array($offer->status, ['draft', 'paused']))
                    <button type="submit" class="btn btn-outline btn-success btn-sm">
                        <span class="icon-[tabler--player-play] size-4"></span>
                        Activate
                    </button>
                @endif
            </form>
            <a href="{{ route('offers.edit', $offer) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
        </div>
    </div>

    @if($offer->description)
        <p class="text-base-content/70">{{ $offer->description }}</p>
    @endif

    {{-- Analytics Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--receipt] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($analytics['total_redemptions']) }}</p>
                        <p class="text-xs text-base-content/60">Redemptions</p>
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
                        <p class="text-2xl font-bold">${{ number_format($analytics['total_discount_given'], 0) }}</p>
                        <p class="text-xs text-base-content/60">Total Discounts</p>
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
                        <p class="text-2xl font-bold">${{ number_format($analytics['total_revenue'], 0) }}</p>
                        <p class="text-xs text-base-content/60">Revenue Generated</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--user-plus] size-5 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($analytics['new_members']) }}</p>
                        <p class="text-xs text-base-content/60">New Members</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                        <span class="icon-[tabler--chart-bar] size-5 text-accent"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($analytics['avg_discount'], 2) }}</p>
                        <p class="text-xs text-base-content/60">Avg Discount</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Redemptions --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Recent Redemptions</h2>

                    @if($recentRedemptions->isEmpty())
                        <div class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--receipt-off] size-12 mx-auto mb-2"></span>
                            <p>No redemptions yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Original</th>
                                        <th>Discount</th>
                                        <th>Final</th>
                                        <th>Channel</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRedemptions as $redemption)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <div class="avatar avatar-placeholder">
                                                        <div class="bg-neutral text-neutral-content w-8 h-8 rounded-full text-sm">
                                                            {{ $redemption->client->initials }}
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('clients.show', $redemption->client) }}" class="hover:text-primary">
                                                        {{ $redemption->client->full_name }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>${{ number_format($redemption->original_price, 2) }}</td>
                                            <td class="text-warning">-${{ number_format($redemption->discount_amount, 2) }}</td>
                                            <td class="font-medium">${{ number_format($redemption->final_price, 2) }}</td>
                                            <td>
                                                <span class="badge badge-soft badge-sm">{{ ucfirst(str_replace('_', ' ', $redemption->channel)) }}</span>
                                            </td>
                                            <td class="text-sm text-base-content/60">{{ $redemption->created_at->format('M d, g:i a') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Offer Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Offer Details</h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-base-content/60">Discount</p>
                            <p class="font-medium text-lg">{{ $offer->getFormattedDiscount() }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-base-content/60">Applies To</p>
                            <p class="font-medium">{{ \App\Models\Offer::getAppliesTo()[$offer->applies_to] ?? 'All' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-base-content/60">Target Audience</p>
                            @if($offer->segment)
                                <a href="{{ route('segments.show', $offer->segment) }}" class="link link-primary">
                                    {{ $offer->segment->name }}
                                </a>
                            @else
                                <p class="font-medium">{{ \App\Models\Offer::getTargetAudiences()[$offer->target_audience] ?? 'All' }}</p>
                            @endif
                        </div>

                        @if($offer->start_date || $offer->end_date)
                            <div>
                                <p class="text-sm text-base-content/60">Duration</p>
                                <p class="font-medium">
                                    {{ $offer->start_date ? $offer->start_date->format('M d, Y') : 'Start' }}
                                    -
                                    {{ $offer->end_date ? $offer->end_date->format('M d, Y') : 'No end' }}
                                </p>
                            </div>
                        @endif

                        @if($offer->total_usage_limit)
                            <div>
                                <p class="text-sm text-base-content/60">Usage Limit</p>
                                <p class="font-medium">{{ $offer->total_redemptions }} / {{ $offer->total_usage_limit }}</p>
                                <div class="w-full bg-base-200 rounded-full h-2 mt-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ min(100, ($offer->total_redemptions / $offer->total_usage_limit) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Channel Breakdown --}}
            @if($byChannel->isNotEmpty())
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">Redemptions by Channel</h3>

                        <div class="space-y-3">
                            @foreach($byChannel as $channel => $data)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $channel)) }}</span>
                                    <div class="text-right">
                                        <span class="font-medium">{{ $data->count }}</span>
                                        <span class="text-xs text-base-content/60 ml-1">(${!! number_format($data->total_discount, 0) !!})</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Options --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Options</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            @if($offer->auto_apply)
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                            @else
                                <span class="icon-[tabler--x] size-4 text-base-content/40"></span>
                            @endif
                            <span>Auto-apply</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($offer->require_code)
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                            @else
                                <span class="icon-[tabler--x] size-4 text-base-content/40"></span>
                            @endif
                            <span>Requires code</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($offer->can_combine)
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                            @else
                                <span class="icon-[tabler--x] size-4 text-base-content/40"></span>
                            @endif
                            <span>Combinable</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($offer->show_on_invoice)
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                            @else
                                <span class="icon-[tabler--x] size-4 text-base-content/40"></span>
                            @endif
                            <span>Show on invoice</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Internal Notes --}}
            @if($offer->internal_notes)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">Internal Notes</h3>
                        <p class="text-sm text-base-content/70">{{ $offer->internal_notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Metadata --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Details</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span>{{ $offer->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($offer->createdBy)
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Created by</span>
                                <span>{{ $offer->createdBy->full_name }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Last Updated</span>
                            <span>{{ $offer->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
