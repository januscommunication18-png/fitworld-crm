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
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if($classPass->image_url)
                <img src="{{ $classPass->image_url }}" alt="{{ $classPass->name }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $classPass->color ?? '#6366f1' }}20;">
                    <span class="icon-[tabler--ticket] size-10" style="color: {{ $classPass->color ?? '#6366f1' }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $classPass->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge badge-soft {{ $classPass->status === 'active' ? 'badge-success' : ($classPass->status === 'draft' ? 'badge-warning' : 'badge-neutral') }}">
                        {{ ucfirst($classPass->status) }}
                    </span>
                    @if($classPass->visibility_public)
                        <span class="badge badge-soft badge-info badge-sm">Visible on Booking</span>
                    @endif
                    <span class="badge badge-soft badge-primary badge-sm">{{ $classPass->class_count }} Credits</span>
                    @if($classPass->is_recurring)
                        <span class="badge badge-soft badge-secondary badge-sm">Recurring</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if($classPass->status === 'active')
            <a href="{{ route('class-passes.sell-form', $classPass) }}" class="btn btn-success btn-sm">
                <span class="icon-[tabler--shopping-cart] size-4"></span>
                Sell Pass
            </a>
            @endif
            <x-actions-dropdown>
                <li><a href="{{ route('class-passes.edit', $classPass) }}">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </a></li>
                <li>
                    <form action="{{ route('class-passes.duplicate', $classPass) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2">
                            <span class="icon-[tabler--copy] size-4"></span> Duplicate
                        </button>
                    </form>
                </li>
                @if($classPass->status === 'draft')
                    <li>
                        <form action="{{ route('class-passes.toggle-status', $classPass) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2 text-success">
                                <span class="icon-[tabler--check] size-4"></span> Publish
                            </button>
                        </form>
                    </li>
                @elseif($classPass->status === 'active')
                    <li>
                        <form action="{{ route('class-passes.toggle-status', $classPass) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2 text-warning">
                                <span class="icon-[tabler--eye-off] size-4"></span> Unpublish
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('class-passes.archive', $classPass) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--archive] size-4"></span> Archive
                            </button>
                        </form>
                    </li>
                @endif
                <li>
                    <button type="button" class="w-full text-left flex items-center gap-2 text-error" onclick="openDeleteModal('{{ route('class-passes.destroy', $classPass) }}', '{{ $classPass->name }}', 'class pass')">
                        <span class="icon-[tabler--trash] size-4"></span> Delete
                    </button>
                </li>
            </x-actions-dropdown>
            <a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
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
            <div class="space-y-6">
            {{-- Description --}}
            @if($classPass->description)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        <p class="mt-2 whitespace-pre-line">{{ $classPass->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Pass Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Pass Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Credits</label>
                            <p class="font-medium">{{ $classPass->class_count }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Credits/Class</label>
                            <p class="font-medium">{{ $classPass->default_credits_per_class }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Validity</label>
                            <p class="font-medium">{{ $classPass->formatted_validity }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Activation</label>
                            <p class="font-medium capitalize">{{ str_replace('_', ' ', $classPass->activation_type) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--currency-dollar] size-5"></span>
                        Pricing
                    </h2>
                    <div class="overflow-x-auto mt-4">
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

            {{-- Fees & Cancellation --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--receipt] size-5"></span>
                        Fees & Cancellation
                    </h2>
                    <div class="overflow-x-auto mt-4">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    @foreach($hostCurrencies as $currency)
                                        <th class="text-center">{{ $currency }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-base-content/70">Registration Fee</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium">
                                            @if(!empty($classPass->registration_fees[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPass->registration_fees[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Cancellation Fee</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium {{ !empty($classPass->cancellation_fees[$currency]) ? 'text-error' : '' }}">
                                            @if(!empty($classPass->cancellation_fees[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPass->cancellation_fees[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm text-base-content/60">Grace Period</label>
                        <p class="font-medium">{{ $classPass->cancellation_grace_hours ?? 48 }} hours</p>
                    </div>
                </div>
            </div>

            {{-- Class Eligibility --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--list-check] size-5"></span>
                        Class Eligibility
                    </h2>
                    <div class="mt-4">
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
                            @php $eligiblePlans = \App\Models\ClassPlan::whereIn('id', $classPass->eligible_class_plan_ids ?? [])->get(); @endphp
                            <div class="flex flex-wrap gap-2">
                                @foreach($eligiblePlans as $plan)
                                    <span class="badge badge-soft badge-primary">
                                        <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $plan->color }}"></span>
                                        {{ $plan->name }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif($classPass->eligibility_type === 'categories')
                            <p class="text-sm text-base-content/60 mb-3">This pass covers the following categories:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($classPass->eligible_categories ?? [] as $category)
                                    <span class="badge badge-soft badge-secondary capitalize">{{ $category }}</span>
                                @endforeach
                            </div>
                        @elseif($classPass->eligibility_type === 'instructors')
                            <p class="text-sm text-base-content/60 mb-3">This pass covers classes by these instructors:</p>
                            @php $eligibleInstructors = \App\Models\Instructor::whereIn('id', $classPass->eligible_instructor_ids ?? [])->get(); @endphp
                            <div class="flex flex-wrap gap-2">
                                @foreach($eligibleInstructors as $instructor)
                                    <span class="badge badge-soft badge-secondary">{{ $instructor->name }}</span>
                                @endforeach
                            </div>
                        @elseif($classPass->eligibility_type === 'locations')
                            <p class="text-sm text-base-content/60 mb-3">This pass covers classes at these locations:</p>
                            @php $eligibleLocations = \App\Models\Location::whereIn('id', $classPass->eligible_location_ids ?? [])->get(); @endphp
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
            </div>

            {{-- Peak Time Settings --}}
            @if($classPass->peak_time_multiplier)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--clock-bolt] size-5"></span>
                        Peak Time Credit Multiplier
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Multiplier</label>
                            <p class="font-medium">{{ $classPass->peak_time_multiplier }}x</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Time Range</label>
                            @php
                                $tf = auth()->user()->host->time_format ?? '12h';
                                $fmt = $tf === '24h' ? 'H:i' : 'g:i A';
                                $startFormatted = $classPass->peak_time_start ? \Carbon\Carbon::parse($classPass->peak_time_start)->format($fmt) : '-';
                                $endFormatted = $classPass->peak_time_end ? \Carbon\Carbon::parse($classPass->peak_time_end)->format($fmt) : '-';
                            @endphp
                            <p class="font-medium">{{ $startFormatted }} - {{ $endFormatted }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-base-content/60">Peak Days</label>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @php $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; @endphp
                                @foreach($classPass->peak_time_days ?? [] as $day)
                                    <span class="badge badge-warning badge-sm">{{ $dayNames[$day] ?? $day }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Features & Options --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Features & Options
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
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
                                <label class="text-sm text-base-content/60">Max Rollover Credits</label>
                                <p class="font-medium">{{ $classPass->max_rollover_credits }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Max Rollover Periods</label>
                                <p class="font-medium">{{ $classPass->max_rollover_periods }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Stats & Settings --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--chart-bar] size-5"></span>
                        Stats & Settings
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Total Purchases</label>
                            <p class="font-bold text-lg">{{ $stats['total_purchases'] }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Active</label>
                            <p class="font-bold text-lg text-success">{{ $stats['active_purchases'] }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Credits Remaining</label>
                            <p class="font-bold text-lg">{{ $stats['total_credits_remaining'] }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Est. Revenue</label>
                            <p class="font-bold text-lg text-success">${{ number_format($stats['total_revenue'], 2) }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Status</label>
                            <p class="mt-0.5">
                                <span class="badge badge-soft {{ $classPass->status === 'active' ? 'badge-success' : ($classPass->status === 'draft' ? 'badge-warning' : 'badge-neutral') }} badge-sm">
                                    {{ ucfirst($classPass->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Booking Page</label>
                            <p class="mt-0.5">
                                @if($classPass->visibility_public)
                                    <span class="badge badge-soft badge-info badge-sm">Visible</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm">Hidden</span>
                                @endif
                            </p>
                        </div>
                        @if($classPass->color)
                        <div>
                            <label class="text-sm text-base-content/60">Color</label>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="w-4 h-4 rounded-full" style="background-color: {{ $classPass->color }}"></span>
                                <span class="text-sm">{{ $classPass->color }}</span>
                            </div>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-base-content/60">Created</label>
                            <p class="font-medium">{{ $classPass->created_at->format('M d, Y') }}</p>
                        </div>
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
            {{-- File Attachments --}}
            @include('host.partials._file-attachments-show', ['fileAttachments' => $classPass->file_attachments])

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
