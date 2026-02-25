@extends('layouts.dashboard')

@section('title', $membershipPlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'memberships']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $membershipPlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.index', ['tab' => 'memberships']) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $membershipPlan->color }}20;">
                <span class="icon-[tabler--id-badge-2] size-8" style="color: {{ $membershipPlan->color }};"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $membershipPlan->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-soft {{ $membershipPlan->status_badge_class }}">{{ ucfirst($membershipPlan->status) }}</span>
                    <span class="badge badge-soft {{ $membershipPlan->type_badge_class }}">{{ $membershipPlan->formatted_type }}</span>
                    @if($membershipPlan->visibility_public)
                        <span class="badge badge-soft badge-info">Public</span>
                    @else
                        <span class="badge badge-soft badge-neutral">Hidden</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('membership-plans.edit', $membershipPlan) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit Plan
            </a>
            <form action="{{ route('membership-plans.destroy', $membershipPlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this membership plan?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-soft btn-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Pricing & Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Plan Details</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Type</p>
                            <p class="font-medium">{{ $membershipPlan->formatted_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Billing</p>
                            <p class="font-medium">{{ ucfirst($membershipPlan->interval) }}</p>
                        </div>
                        @if($membershipPlan->isCredits())
                        <div>
                            <p class="text-sm text-base-content/60">Credits/Cycle</p>
                            <p class="font-medium">{{ $membershipPlan->credits_per_cycle }}</p>
                        </div>
                        @endif
                    </div>

                    @if($membershipPlan->description)
                    <div class="mt-4 pt-4 border-t border-base-content/10">
                        <p class="text-sm text-base-content/60 mb-1">Description</p>
                        <p class="text-base-content">{{ $membershipPlan->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Pricing</h3>
                </div>
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th class="w-48">Price Type</th>
                                    @foreach($hostCurrencies as $currency)
                                        <th class="text-center">
                                            {{ $currency }}
                                            @if($currency === $defaultCurrency)
                                                <span class="badge badge-primary badge-xs ms-1">Default</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- New Member Pricing Section --}}
                                <tr class="bg-info/5">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--user-plus] size-4 me-1 align-middle"></span>
                                        New Member Pricing
                                        <span class="badge badge-soft badge-info badge-sm ms-2">Public Booking</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Price{{ $membershipPlan->formatted_interval }}</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($membershipPlan->new_member_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($membershipPlan->new_member_prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Existing Member Pricing Section --}}
                                <tr class="bg-base-200/50">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--users] size-4 me-1 align-middle"></span>
                                        Existing Member Pricing
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Price{{ $membershipPlan->formatted_interval }}</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($membershipPlan->prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($membershipPlan->prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Eligibility --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Class Eligibility</h3>
                </div>
                <div class="card-body">
                    @if($membershipPlan->coversAllClasses())
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                            <div>
                                <p class="font-medium">All Classes</p>
                                <p class="text-sm text-base-content/60">Members can book any class in the studio</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-base-content/60 mb-3">This membership covers the following class plans:</p>
                        @if($membershipPlan->classPlans->isEmpty())
                            <p class="text-warning">No class plans selected. Members won't be able to book any classes.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($membershipPlan->classPlans as $classPlan)
                                    <span class="badge badge-soft badge-primary">
                                        <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $classPlan->color }}"></span>
                                        {{ $classPlan->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Location Access --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Location Access</h3>
                </div>
                <div class="card-body">
                    @if($membershipPlan->location_scope_type === 'all')
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                            <div>
                                <p class="font-medium">All Locations</p>
                                <p class="text-sm text-base-content/60">Members can book at any studio location</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-base-content/60 mb-3">This membership is valid at these locations:</p>
                        @if(empty($membershipPlan->location_ids))
                            <p class="text-warning">No locations selected.</p>
                        @else
                            <p class="text-base-content">{{ count($membershipPlan->location_ids) }} location(s) selected</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Stats (placeholder for future) --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Active Members</span>
                            <span class="font-bold text-lg">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Monthly Revenue</span>
                            <span class="font-bold text-lg">$0.00</span>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/50 mt-4">Customer memberships coming soon</p>
                </div>
            </div>

            {{-- Stripe Integration --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Stripe Integration</h3>
                </div>
                <div class="card-body">
                    @if($membershipPlan->stripe_product_id)
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                <span>Connected to Stripe</span>
                            </div>
                            <p class="text-base-content/60 text-xs">Product ID: {{ $membershipPlan->stripe_product_id }}</p>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-base-content/60">
                            <span class="icon-[tabler--link-off] size-4"></span>
                            <span>Not connected to Stripe</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-2">Stripe integration will be configured when customer purchases are enabled.</p>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body space-y-2">
                    @if($membershipPlan->status === 'draft')
                        <form action="{{ route('membership-plans.toggle-status', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm w-full">
                                <span class="icon-[tabler--check] size-4"></span>
                                Publish Plan
                            </button>
                        </form>
                    @elseif($membershipPlan->status === 'active')
                        <form action="{{ route('membership-plans.toggle-status', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning btn-sm w-full">
                                <span class="icon-[tabler--eye-off] size-4"></span>
                                Unpublish Plan
                            </button>
                        </form>
                        <form action="{{ route('membership-plans.archive', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-ghost btn-sm w-full">
                                <span class="icon-[tabler--archive] size-4"></span>
                                Archive Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
