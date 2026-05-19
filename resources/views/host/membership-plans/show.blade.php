@extends('layouts.dashboard')

@section('title', $membershipPlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'memberships']) }}">Memberships</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $membershipPlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $membershipPlan->color }}20;">
                <span class="icon-[tabler--id-badge-2] size-10" style="color: {{ $membershipPlan->color }};"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $membershipPlan->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge badge-soft {{ $membershipPlan->status_badge_class }}">{{ ucfirst($membershipPlan->status) }}</span>
                    <span class="badge badge-soft {{ $membershipPlan->type_badge_class }}">{{ $membershipPlan->formatted_type }}</span>
                    @if($membershipPlan->isOpenAccess())
                        <span class="badge badge-soft badge-accent badge-sm">Open Access</span>
                    @endif
                    @if($membershipPlan->visibility_public)
                        <span class="badge badge-soft badge-info badge-sm">Visible on Booking</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if($membershipPlan->isOpenAccess())
                <a href="{{ route('membership-checkin.index', $membershipPlan) }}" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--door-enter] size-4"></span>
                    Check-in Screen
                </a>
            @endif
            <a href="{{ route('membership-plans.edit', $membershipPlan) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
            <x-actions-dropdown>
                @if($membershipPlan->status === 'draft')
                    <li>
                        <form action="{{ route('membership-plans.toggle-status', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span> Publish Plan
                            </button>
                        </form>
                    </li>
                @elseif($membershipPlan->status === 'active')
                    <li>
                        <form action="{{ route('membership-plans.toggle-status', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--eye-off] size-4 text-warning"></span> Unpublish Plan
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('membership-plans.archive', $membershipPlan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--archive] size-4"></span> Archive Plan
                            </button>
                        </form>
                    </li>
                @endif
                <li>
                    <form action="{{ route('membership-plans.destroy', $membershipPlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this membership plan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                            <span class="icon-[tabler--trash] size-4"></span> Delete Plan
                        </button>
                    </form>
                </li>
            </x-actions-dropdown>
            <a href="{{ route('catalog.index', ['tab' => 'memberships']) }}" class="btn btn-ghost btn-sm gap-1.5">
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
        <button class="tab {{ $tab === 'schedule' ? 'tab-active' : '' }}" data-tab="schedule" role="tab">
            <span class="icon-[tabler--calendar] size-4 mr-2"></span>Schedule
            @php
                $totalUpcoming = $sessionsByLocation->flatten()->count();
            @endphp
            @if($totalUpcoming > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $totalUpcoming }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="space-y-6">

            {{-- Description --}}
            @if($membershipPlan->description)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        <p class="mt-2 whitespace-pre-line">{{ $membershipPlan->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Plan Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Plan Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Type</label>
                            <p class="font-medium">{{ $membershipPlan->formatted_type }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Billing</label>
                            <p class="font-medium">{{ ucfirst($membershipPlan->interval) }}</p>
                        </div>
                        @if($membershipPlan->isCredits())
                        <div>
                            <label class="text-sm text-base-content/60">Credits/Cycle</label>
                            <p class="font-medium">{{ $membershipPlan->credits_per_cycle }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-base-content/60">Addon Members</label>
                            <p class="font-medium">{{ $membershipPlan->addon_members > 0 ? '+' . $membershipPlan->addon_members . ' Guest(s)' : 'Individual' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            @php
                $symbol = $currencySymbols[$defaultCurrency] ?? $defaultCurrency;
                $basePrice = $membershipPlan->prices[$defaultCurrency] ?? 0;
                $billingPeriods = ['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
            @endphp
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--currency-dollar] size-5"></span>
                        Pricing
                    </h2>

                    {{-- Multi-currency pricing table --}}
                    @if(count($hostCurrencies) > 1)
                    <div class="overflow-x-auto mt-4">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th class="w-48">Period</th>
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
                                @foreach($billingPeriods as $months => $label)
                                    @php
                                        $periodPrices = $membershipPlan->billing_discounts[$months] ?? [];
                                        if (!is_array($periodPrices)) $periodPrices = [];
                                        $hasAny = count(array_filter($periodPrices)) > 0;
                                    @endphp
                                    <tr class="{{ $months === '1' ? 'bg-primary/5' : '' }}">
                                        <td class="font-medium">
                                            {{ $label }}
                                            @if($months === '1')
                                                <span class="badge badge-primary badge-xs ms-1">Base</span>
                                            @endif
                                        </td>
                                        @foreach($hostCurrencies as $currency)
                                            <td class="text-center font-medium {{ $hasAny ? 'text-success' : 'text-base-content/40' }}">
                                                @if(!empty($periodPrices[$currency]))
                                                    {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($periodPrices[$currency], 2) }}
                                                @else
                                                    <span class="text-base-content/30">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Visual pricing cards (default currency) --}}
                    <div class="mt-4">
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                            @foreach($billingPeriods as $months => $label)
                                @php
                                    $periodPrices = $membershipPlan->billing_discounts[$months] ?? [];
                                    if (is_array($periodPrices)) {
                                        $totalForPeriod = (float) ($periodPrices[$defaultCurrency] ?? 0);
                                    } else {
                                        $totalForPeriod = (float) $periodPrices;
                                    }
                                    $hasValue = $totalForPeriod > 0;
                                    $m = (int) $months;
                                    $monthlyRate = $m > 0 ? $totalForPeriod / $m : 0;
                                    $totalWithout = $basePrice * $m;
                                    $savings = $hasValue && $m > 1 ? $totalWithout - $totalForPeriod : 0;
                                @endphp
                                <div class="text-center p-3 rounded-lg {{ $hasValue ? ($months === '1' ? 'bg-primary/10 ring-1 ring-primary/20' : 'bg-success/10') : 'bg-base-200/50' }}">
                                    <div class="text-sm text-base-content/60">{{ $label }}</div>
                                    @if($hasValue)
                                        <div class="text-xl font-bold {{ $months === '1' ? 'text-primary' : 'text-success' }}">
                                            {{ $symbol }}{{ number_format($totalForPeriod, 2) }}
                                        </div>
                                        @if($m > 1)
                                            <div class="text-xs text-base-content/50">{{ $symbol }}{{ number_format($monthlyRate, 2) }}/mo</div>
                                        @else
                                            <div class="text-xs text-base-content/50">Base price</div>
                                        @endif
                                        @if($savings > 0)
                                            <div class="text-xs text-success mt-0.5">Save {{ $symbol }}{{ number_format($savings, 2) }}</div>
                                        @endif
                                    @else
                                        <div class="text-xl font-bold text-base-content/30">—</div>
                                        <div class="text-xs text-base-content/40">Not set</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
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
                                            @if(!empty($membershipPlan->registration_fees[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($membershipPlan->registration_fees[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Cancellation Fee</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium {{ !empty($membershipPlan->cancellation_fees[$currency]) ? 'text-error' : '' }}">
                                            @if(!empty($membershipPlan->cancellation_fees[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($membershipPlan->cancellation_fees[$currency], 2) }}
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
                        <p class="font-medium">{{ $membershipPlan->cancellation_grace_hours ?? 48 }} hours</p>
                    </div>
                </div>
            </div>

            {{-- Class Eligibility --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--yoga] size-5"></span>
                        Class Eligibility
                    </h2>
                    <div class="mt-3">
                    @if($membershipPlan->coversAllClasses())
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                            <div>
                                <p class="font-medium">All Classes</p>
                                <p class="text-sm text-base-content/60">Members can book any class in the studio</p>
                            </div>
                        </div>
                    @else
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
            </div>

            {{-- Location Access --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--map-pin] size-5"></span>
                        Location Access
                    </h2>
                    <div class="mt-3">
                    @if($membershipPlan->location_scope_type === 'all')
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                            <div>
                                <p class="font-medium">All Locations</p>
                                <p class="text-sm text-base-content/60">Members can book at any studio location</p>
                            </div>
                        </div>
                    @else
                        @if(empty($membershipPlan->location_ids))
                            <p class="text-warning">No locations selected.</p>
                        @else
                            <p class="text-base-content">{{ count($membershipPlan->location_ids) }} location(s) selected</p>
                        @endif
                    @endif
                    </div>
                </div>
            </div>

            {{-- Free Amenities --}}
            @if($membershipPlan->free_amenities && count($membershipPlan->free_amenities) > 0)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--star] size-5"></span>
                        Free Amenities
                    </h2>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($membershipPlan->free_amenities as $amenity)
                            <span class="badge badge-soft badge-primary">{{ $amenity }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Free Rentals --}}
            @if($membershipPlan->free_rental_ids && count($membershipPlan->free_rental_ids) > 0)
            @php
                $freeRentals = \App\Models\RentalItem::whereIn('id', $membershipPlan->free_rental_ids)->get();
            @endphp
            @if($freeRentals->isNotEmpty())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--package] size-5"></span>
                        Free Rentals
                    </h2>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($freeRentals as $rental)
                            <span class="badge badge-soft badge-primary">
                                {{ $rental->name }}
                                @if($rental->category)
                                    <span class="badge badge-ghost badge-xs ml-1">{{ $rental->formatted_category }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @endif

            {{-- File Attachments --}}
            @include('host.partials._file-attachments-show', ['fileAttachments' => $membershipPlan->file_attachments])

            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">

            @if($membershipPlan->isOpenAccess())
                {{-- Open Access Info --}}
                <div class="space-y-6">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-accent/10 rounded-lg p-3">
                                        <span class="icon-[tabler--door-enter] size-8 text-accent"></span>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold">Open Access</h2>
                                        <p class="text-sm text-base-content/60">Members can walk in anytime — no session booking needed.</p>
                                    </div>
                                </div>
                                <a href="{{ route('membership-checkin.index', $membershipPlan) }}" class="btn btn-success btn-sm">
                                    <span class="icon-[tabler--door-enter] size-4"></span>
                                    Open Check-in Screen
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Open Access Details --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--info-circle] size-5"></span>
                                Open Access Details
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Schedule Type</label>
                                    <p class="mt-0.5"><span class="badge badge-soft badge-accent badge-sm">Open Access</span></p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Location</label>
                                    <p class="font-medium">
                                        @if(!empty($membershipPlan->location_ids))
                                            @php $openAccessLocation = \App\Models\Location::find($membershipPlan->location_ids[0]); @endphp
                                            {{ $openAccessLocation?->name ?? '—' }}
                                        @else
                                            Any Location
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Active Members</label>
                                    <p class="font-bold text-lg text-primary">{{ $membershipPlan->customerMemberships()->where('status', 'active')->count() }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Check-ins Today</label>
                                    <p class="font-bold text-lg text-success">{{ $membershipPlan->checkins()->whereDate('checked_in_at', today())->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Check-ins --}}
                    @php
                        $recentCheckins = $membershipPlan->checkins()
                            ->with(['client', 'checkedInBy'])
                            ->latest('checked_in_at')
                            ->limit(10)
                            ->get();
                    @endphp
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--clock] size-5"></span>
                                Recent Check-ins
                            </h2>
                            @if($recentCheckins->isEmpty())
                                <div class="text-center py-8">
                                    <span class="icon-[tabler--door-enter] size-10 text-base-content/20"></span>
                                    <p class="text-base-content/60 mt-2 text-sm">No check-ins yet.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto mt-4">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Checked In</th>
                                                <th>Checked Out</th>
                                                <th>By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentCheckins as $checkin)
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center font-bold text-xs text-primary">
                                                            {{ $checkin->client->initials }}
                                                        </div>
                                                        <span class="font-medium text-sm">{{ $checkin->client->full_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-sm">{{ $checkin->checked_in_at->format('M j, g:i A') }}</td>
                                                <td class="text-sm">
                                                    @if($checkin->checked_out_at)
                                                        {{ $checkin->checked_out_at->format('g:i A') }}
                                                    @else
                                                        <span class="badge badge-soft badge-success badge-xs">Still here</span>
                                                    @endif
                                                </td>
                                                <td class="text-sm text-base-content/60">{{ $checkin->checkedInBy?->first_name ?? '—' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else

            <div class="alert alert-soft alert-info mb-6">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div>
                    <p class="font-medium">Linked Class Sessions</p>
                    <p class="text-sm">Showing sessions specifically linked to this membership plan for auto-enrollment.</p>
                </div>
            </div>

            @if($locations->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Upcoming Sessions</h3>
                        <p class="text-base-content/60 mb-4">There are no upcoming sessions for the classes covered by this membership plan.</p>
                    </div>
                </div>
            @else
                {{-- Location Filter Dropdown --}}
                <div class="flex justify-end mb-6">
                    <div class="form-control w-64">
                        <select id="location-filter" class="select select-bordered select-sm">
                            <option value="all" selected>
                                All Locations ({{ $sessionsByLocation->flatten()->count() }})
                            </option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->name }}
                                    @if(isset($sessionsByLocation[$location->id]))
                                        ({{ $sessionsByLocation[$location->id]->count() }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- All Locations Content --}}
                <div class="location-content" data-location-content="all">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--calendar-event] size-5"></span>
                                    All Upcoming Sessions
                                </h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Class</th>
                                            <th>Location</th>
                                            <th>Instructor</th>
                                            <th>Status</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessionsByLocation->flatten()->sortBy('start_time') as $session)
                                            <tr>
                                                <td>
                                                    <div class="font-medium">{{ $session->start_time->format('D, M d, Y') }}</div>
                                                    <div class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}</div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('class-plans.show', $session->classPlan) }}" class="font-medium hover:text-primary">
                                                        {{ $session->classPlan?->name ?? 'Unknown' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="flex items-center gap-2">
                                                        <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                                                        {{ $session->location?->name ?? 'No location' }}
                                                    </div>
                                                </td>
                                                <td>{{ $session->primaryInstructor?->name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-soft badge-sm {{ $session->getStatusBadgeClass() }}">{{ ucfirst($session->status) }}</span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Individual Location Contents --}}
                @foreach($locations as $location)
                    <div class="location-content hidden" data-location-content="{{ $location->id }}">
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h2 class="card-title text-lg">
                                            <span class="icon-[tabler--map-pin] size-5"></span>
                                            {{ $location->name }}
                                        </h2>
                                        @if($location->full_address)
                                            <p class="text-sm text-base-content/60 mt-1">{{ $location->full_address }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if(isset($sessionsByLocation[$location->id]) && $sessionsByLocation[$location->id]->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Class</th>
                                                    <th>Room</th>
                                                    <th>Instructor</th>
                                                    <th>Status</th>
                                                    <th class="text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sessionsByLocation[$location->id] as $session)
                                                    <tr>
                                                        <td>
                                                            <div class="font-medium">{{ $session->start_time->format('D, M d, Y') }}</div>
                                                            <div class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}</div>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('class-plans.show', $session->classPlan) }}" class="font-medium hover:text-primary">
                                                                {{ $session->classPlan?->name ?? 'Unknown' }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $session->room?->name ?? '-' }}</td>
                                                        <td>{{ $session->primaryInstructor?->name ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-soft badge-sm {{ $session->getStatusBadgeClass() }}">{{ ucfirst($session->status) }}</span>
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs">
                                                                <span class="icon-[tabler--eye] size-4"></span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <span class="icon-[tabler--calendar-off] size-10 text-base-content/20"></span>
                                        <p class="text-base-content/60 mt-2">No upcoming sessions at this location.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
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

    // Location dropdown filter
    const locationFilter = document.getElementById('location-filter');
    const locationContents = document.querySelectorAll('.location-content');

    if (locationFilter) {
        locationFilter.addEventListener('change', function() {
            const targetLocation = this.value;

            locationContents.forEach(content => {
                content.classList.toggle('hidden', content.dataset.locationContent !== targetLocation);
            });
        });
    }
});
</script>
@endpush
