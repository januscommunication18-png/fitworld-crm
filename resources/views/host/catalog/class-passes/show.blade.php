@extends('layouts.dashboard')

@section('title', $classPass->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}">Class Passes</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $classPass->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $classPass->color ?? '#6366f1' }}20;">
                <span class="icon-[tabler--ticket] size-8" style="color: {{ $classPass->color ?? '#6366f1' }};"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $classPass->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-soft {{ $classPass->status === 'active' ? 'badge-success' : ($classPass->status === 'draft' ? 'badge-warning' : 'badge-neutral') }}">
                        {{ ucfirst($classPass->status) }}
                    </span>
                    <span class="badge badge-soft badge-primary">{{ $classPass->class_count }} Credits</span>
                    @if($classPass->visibility_public)
                        <span class="badge badge-soft badge-info">Public</span>
                    @else
                        <span class="badge badge-soft badge-neutral">Hidden</span>
                    @endif
                    @if($classPass->is_recurring)
                        <span class="badge badge-soft badge-secondary">Recurring</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($classPass->status === 'active')
            <a href="{{ route('class-passes.sell-form', $classPass) }}" class="btn btn-success">
                <span class="icon-[tabler--shopping-cart] size-5"></span>
                Sell Pass
            </a>
            @endif
            <a href="{{ route('class-passes.edit', $classPass) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit Pass
            </a>
            <form action="{{ route('class-passes.duplicate', $classPass) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-soft btn-secondary">
                    <span class="icon-[tabler--copy] size-5"></span>
                </button>
            </form>
            <form action="{{ route('class-passes.destroy', $classPass) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this class pass?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-soft btn-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Main Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>Overview
        </button>
        <button class="tab {{ $tab === 'purchases' ? 'tab-active' : '' }}" data-tab="purchases" role="tab">
            <span class="icon-[tabler--receipt] size-4 mr-2"></span>Purchases
            @if($stats['total_purchases'] > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $stats['total_purchases'] }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Pass Details --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Pass Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-base-content/60">Credits</p>
                                    <p class="font-medium text-lg">{{ $classPass->class_count }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Credits/Class</p>
                                    <p class="font-medium text-lg">{{ $classPass->default_credits_per_class }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Validity</p>
                                    <p class="font-medium text-lg">{{ $classPass->formatted_validity }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Activation</p>
                                    <p class="font-medium text-lg capitalize">{{ str_replace('_', ' ', $classPass->activation_type) }}</p>
                                </div>
                            </div>

                            @if($classPass->description)
                            <div class="mt-4 pt-4 border-t border-base-content/10">
                                <p class="text-sm text-base-content/60 mb-1">Description</p>
                                <p class="text-base-content">{{ $classPass->description }}</p>
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
                                        {{-- New Member Pricing --}}
                                        <tr class="bg-info/5">
                                            <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                                <span class="icon-[tabler--user-plus] size-4 me-1 align-middle"></span>
                                                New Member Pricing
                                                <span class="badge badge-soft badge-info badge-sm ms-2">Public Booking</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-base-content/70">Price</td>
                                            @foreach($hostCurrencies as $currency)
                                                <td class="text-center font-medium text-success">
                                                    @if(!empty($classPass->new_member_prices[$currency]))
                                                        {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPass->new_member_prices[$currency], 2) }}
                                                    @else
                                                        <span class="text-base-content/40">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>

                                        {{-- Standard Pricing --}}
                                        <tr class="bg-base-200/50">
                                            <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                                <span class="icon-[tabler--users] size-4 me-1 align-middle"></span>
                                                Standard Pricing
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-base-content/70">Price</td>
                                            @foreach($hostCurrencies as $currency)
                                                <td class="text-center font-medium text-success">
                                                    @if(!empty($classPass->prices[$currency]))
                                                        {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPass->prices[$currency], 2) }}
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

                    {{-- Class Eligibility --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Class Eligibility</h3>
                        </div>
                        <div class="card-body">
                            @if($classPass->eligibility_type === 'all')
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                                    <div>
                                        <p class="font-medium">All Classes</p>
                                        <p class="text-sm text-base-content/60">This pass can be used for any class</p>
                                    </div>
                                </div>
                            @elseif($classPass->eligibility_type === 'class_plans')
                                <p class="text-sm text-base-content/60 mb-3">This pass covers the following class plans:</p>
                                @if(empty($classPass->eligible_class_plan_ids))
                                    <p class="text-warning">No class plans selected.</p>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $eligiblePlans = \App\Models\ClassPlan::whereIn('id', $classPass->eligible_class_plan_ids)->get();
                                        @endphp
                                        @foreach($eligiblePlans as $plan)
                                            <span class="badge badge-soft badge-primary">
                                                <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $plan->color }}"></span>
                                                {{ $plan->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($classPass->eligibility_type === 'categories')
                                <p class="text-sm text-base-content/60 mb-3">This pass covers the following categories:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($classPass->eligible_categories ?? [] as $category)
                                        <span class="badge badge-soft badge-secondary capitalize">{{ $category }}</span>
                                    @endforeach
                                </div>
                            @elseif($classPass->eligibility_type === 'instructors')
                                <p class="text-sm text-base-content/60 mb-3">This pass covers classes by these instructors:</p>
                                @php
                                    $eligibleInstructors = \App\Models\Instructor::whereIn('id', $classPass->eligible_instructor_ids ?? [])->get();
                                @endphp
                                <div class="flex flex-wrap gap-2">
                                    @foreach($eligibleInstructors as $instructor)
                                        <span class="badge badge-soft badge-secondary">{{ $instructor->name }}</span>
                                    @endforeach
                                </div>
                            @elseif($classPass->eligibility_type === 'locations')
                                <p class="text-sm text-base-content/60 mb-3">This pass covers classes at these locations:</p>
                                @php
                                    $eligibleLocations = \App\Models\Location::whereIn('id', $classPass->eligible_location_ids ?? [])->get();
                                @endphp
                                <div class="flex flex-wrap gap-2">
                                    @foreach($eligibleLocations as $location)
                                        <span class="badge badge-soft badge-secondary">{{ $location->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($classPass->excluded_class_types))
                                <div class="mt-4 pt-4 border-t border-base-content/10">
                                    <p class="text-sm text-base-content/60 mb-2">Excluded class types:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($classPass->excluded_class_types as $type)
                                            <span class="badge badge-soft badge-error">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Peak Time Settings --}}
                    @if($classPass->peak_time_multiplier)
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Peak Time Credit Multiplier</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-base-content/60">Multiplier</p>
                                    <p class="font-medium text-lg">{{ $classPass->peak_time_multiplier }}x</p>
                                </div>
                                <div>
                                    <p class="text-sm text-base-content/60">Time Range</p>
                                    <p class="font-medium">{{ $classPass->peak_time_start }} - {{ $classPass->peak_time_end }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-sm text-base-content/60">Peak Days</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @php
                                            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                        @endphp
                                        @foreach($classPass->peak_time_days ?? [] as $day)
                                            <span class="badge badge-warning badge-sm">{{ $dayNames[$day] ?? $day }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Advanced Features --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Features & Options</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="flex items-center gap-2">
                                    @if($classPass->allow_admin_extension)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                    @endif
                                    <span class="{{ $classPass->allow_admin_extension ? '' : 'text-base-content/50' }}">Admin Extension</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($classPass->allow_freeze)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                        <span>Freeze ({{ $classPass->max_freeze_days }} days)</span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                        <span class="text-base-content/50">Freeze</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($classPass->allow_transfer)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                    @endif
                                    <span class="{{ $classPass->allow_transfer ? '' : 'text-base-content/50' }}">Transfer</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($classPass->allow_family_sharing)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                        <span>Family Sharing ({{ $classPass->max_family_members }} members)</span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                        <span class="text-base-content/50">Family Sharing</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($classPass->allow_gifting)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                    @endif
                                    <span class="{{ $classPass->allow_gifting ? '' : 'text-base-content/50' }}">Gifting</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($classPass->is_recurring)
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                        <span>Auto-Renewal ({{ ucfirst($classPass->renewal_interval) }})</span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/30"></span>
                                        <span class="text-base-content/50">Auto-Renewal</span>
                                    @endif
                                </div>
                            </div>

                            @if($classPass->is_recurring && $classPass->rollover_enabled)
                            <div class="mt-4 pt-4 border-t border-base-content/10">
                                <p class="text-sm text-base-content/60 mb-2">Credit Rollover Settings</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-base-content/60">Max Rollover Credits</p>
                                        <p class="font-medium">{{ $classPass->max_rollover_credits }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-base-content/60">Max Rollover Periods</p>
                                        <p class="font-medium">{{ $classPass->max_rollover_periods }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Stats --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Total Purchases</span>
                                    <span class="font-bold text-lg">{{ $stats['total_purchases'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Active Purchases</span>
                                    <span class="font-bold text-lg text-success">{{ $stats['active_purchases'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Credits Remaining</span>
                                    <span class="font-bold text-lg">{{ $stats['total_credits_remaining'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Est. Revenue</span>
                                    <span class="font-bold text-lg text-success">${{ number_format($stats['total_revenue'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stripe Integration --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Stripe Integration</h3>
                        </div>
                        <div class="card-body">
                            @if($classPass->stripe_product_id)
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--check] size-4 text-success"></span>
                                        <span>Connected to Stripe</span>
                                    </div>
                                    <p class="text-base-content/60 text-xs">Product ID: {{ $classPass->stripe_product_id }}</p>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--link-off] size-4"></span>
                                    <span>Not connected to Stripe</span>
                                </div>
                                <p class="text-xs text-base-content/50 mt-2">Stripe product will be created when first purchase is made.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body space-y-2">
                            @if($classPass->status === 'draft')
                                <form action="{{ route('class-passes.toggle-status', $classPass) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm w-full">
                                        <span class="icon-[tabler--check] size-4"></span>
                                        Publish Pass
                                    </button>
                                </form>
                            @elseif($classPass->status === 'active')
                                <form action="{{ route('class-passes.toggle-status', $classPass) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning btn-sm w-full">
                                        <span class="icon-[tabler--eye-off] size-4"></span>
                                        Unpublish Pass
                                    </button>
                                </form>
                                <form action="{{ route('class-passes.archive', $classPass) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-sm w-full">
                                        <span class="icon-[tabler--archive] size-4"></span>
                                        Archive Pass
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Purchases Tab --}}
        <div class="tab-content {{ $tab === 'purchases' ? 'active' : 'hidden' }}" data-content="purchases">
            @if($purchases->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--receipt-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Purchases Yet</h3>
                        <p class="text-base-content/60 mb-4">This class pass hasn't been purchased by anyone yet.</p>
                    </div>
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Purchased</th>
                                        <th>Credits</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $purchase)
                                        <tr>
                                            <td>
                                                @if($purchase->client)
                                                <a href="{{ route('clients.show', ['id' => $purchase->client_id]) }}" class="font-medium hover:text-primary">
                                                    {{ $purchase->client->name }}
                                                </a>
                                                <p class="text-xs text-base-content/60">{{ $purchase->client->email }}</p>
                                                @else
                                                <span class="text-base-content/50">Unknown Client</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $purchase->created_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-base-content/60">{{ $purchase->created_at->format('g:i A') }}</div>
                                            </td>
                                            <td>
                                                <span class="font-medium {{ $purchase->classes_remaining <= 0 ? 'text-error' : '' }}">
                                                    {{ $purchase->classes_remaining }}/{{ $purchase->total_classes }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($purchase->expires_at)
                                                    <div class="{{ $purchase->expires_at->isPast() ? 'text-error' : ($purchase->expires_at->diffInDays(now()) <= 7 ? 'text-warning' : '') }}">
                                                        {{ $purchase->expires_at->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-xs text-base-content/60">
                                                        {{ $purchase->expires_at->diffForHumans() }}
                                                    </div>
                                                @elseif($purchase->is_pending_activation)
                                                    <span class="text-info">Starts on first booking</span>
                                                    <div class="text-xs text-base-content/60">{{ $purchase->classPass->formatted_validity }} validity</div>
                                                @elseif($purchase->classPass && $purchase->classPass->validity_type === 'no_expiration')
                                                    <span class="text-success">Never expires</span>
                                                @else
                                                    <span class="text-base-content/50">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->is_frozen)
                                                    <span class="badge badge-soft badge-info">Frozen</span>
                                                @elseif($purchase->is_pending_activation)
                                                    <span class="badge badge-soft badge-warning">Pending Activation</span>
                                                @elseif($purchase->isExpired())
                                                    <span class="badge badge-soft badge-error">Expired</span>
                                                @elseif($purchase->classes_remaining <= 0)
                                                    <span class="badge badge-soft badge-warning">Exhausted</span>
                                                @else
                                                    <span class="badge badge-soft badge-success">Active</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($purchase->is_pending_activation)
                                                        <form action="{{ route('class-pass-purchases.activate', $purchase) }}" method="POST" class="inline" onsubmit="return confirm('Activate this pass now? The validity period will start immediately.')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-xs" title="Activate Now">
                                                                <span class="icon-[tabler--player-play] size-4"></span>
                                                                Activate
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($purchase->client)
                                                        <a href="{{ route('clients.show', ['id' => $purchase->client_id, 'tab' => 'passes']) }}" class="btn btn-ghost btn-xs" title="View Client">
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

                        @if($purchases->hasPages())
                            <div class="mt-4">
                                {{ $purchases->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main tab switching
    const mainTabs = document.querySelectorAll('.tabs.tabs-bordered .tab');
    const mainContents = document.querySelectorAll('.tab-content');

    mainTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);

            mainTabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            mainContents.forEach(content => {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        });
    });
});
</script>
@endpush
