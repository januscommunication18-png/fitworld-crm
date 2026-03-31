@extends('layouts.dashboard')

@section('title', $servicePlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'services']) }}">Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $servicePlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('catalog.index', ['tab' => 'services']) }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            @if($servicePlan->image_url)
                <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--massage] size-10 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $servicePlan->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($servicePlan->is_active)
                        <span class="badge badge-soft badge-success">Active</span>
                    @else
                        <span class="badge badge-soft badge-neutral">Inactive</span>
                    @endif
                    @if($servicePlan->is_visible_on_booking_page)
                        <span class="badge badge-soft badge-info badge-sm">Visible on Booking</span>
                    @endif
                    @if($servicePlan->category)
                        <span class="badge badge-soft badge-primary badge-sm">{{ \App\Models\ServicePlan::getCategories()[$servicePlan->category] ?? $servicePlan->category }}</span>
                    @endif
                    @if($servicePlan->location_type)
                        <span class="badge badge-soft badge-secondary badge-sm">{{ \App\Models\ServicePlan::getLocationTypes()[$servicePlan->location_type] ?? $servicePlan->location_type }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
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
                $totalUpcoming = $slotsByLocation->flatten()->count();
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            @if($servicePlan->description)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        <p class="mt-2 whitespace-pre-line">{{ $servicePlan->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Service Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Service Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Duration</label>
                            <p class="font-medium">{{ $servicePlan->formatted_duration }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Buffer Time</label>
                            <p class="font-medium">{{ $servicePlan->buffer_minutes ? $servicePlan->buffer_minutes . ' min' : 'None' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Max Participants</label>
                            <p class="font-medium">{{ $servicePlan->max_participants ?? '1' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Location Type</label>
                            <p class="font-medium">{{ \App\Models\ServicePlan::getLocationTypes()[$servicePlan->location_type] ?? '-' }}</p>
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
                                {{-- New Member Pricing Section --}}
                                <tr class="bg-info/5">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--user-plus] size-4 me-1 align-middle"></span>
                                        New Member Pricing
                                        <span class="badge badge-soft badge-info badge-sm ms-2">Public Booking</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Service Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($servicePlan->new_member_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($servicePlan->new_member_prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Deposit</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($servicePlan->new_member_deposit_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($servicePlan->new_member_deposit_prices[$currency], 2) }}
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
                                    <td class="text-base-content/70">Service Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($servicePlan->prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($servicePlan->prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Deposit</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($servicePlan->deposit_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($servicePlan->deposit_prices[$currency], 2) }}
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

            {{-- Billing Period Discounts --}}
            @if($servicePlan->billing_discounts && count(array_filter($servicePlan->billing_discounts)) > 0)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--discount] size-5"></span>
                        Billing Period Discounts
                    </h2>
                    <p class="text-sm text-base-content/60 mt-1">Discounts applied for longer billing commitments.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-4">
                        @php
                            $billingPeriods = [
                                '1' => '1 Month',
                                '3' => '3 Months',
                                '6' => '6 Months',
                                '9' => '9 Months',
                                '12' => '12 Months',
                            ];
                        @endphp
                        @foreach($billingPeriods as $months => $label)
                            @php
                                $discount = $servicePlan->billing_discounts[$months] ?? 0;
                            @endphp
                            <div class="text-center p-3 rounded-lg {{ $discount > 0 ? 'bg-success/10' : 'bg-base-200/50' }}">
                                <div class="text-sm text-base-content/60">{{ $label }}</div>
                                <div class="text-xl font-bold {{ $discount > 0 ? 'text-success' : 'text-base-content/40' }}">
                                    {{ $discount }}%
                                </div>
                                @if($discount > 0)
                                    <div class="text-xs text-success">Save {{ $discount }}%</div>
                                @else
                                    <div class="text-xs text-base-content/40">Base price</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Booking Rules --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        Booking Rules
                    </h2>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Booking Notice</label>
                            <p class="font-medium">{{ $servicePlan->booking_notice_hours ? $servicePlan->booking_notice_hours . ' hours in advance' : 'No minimum notice' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Cancellation Policy</label>
                            <p class="font-medium">{{ $servicePlan->cancellation_hours ? $servicePlan->cancellation_hours . ' hours notice required' : 'No cancellation policy' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assigned Staff Members --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--users] size-5"></span>
                            Assigned Staff Members
                        </h2>
                        <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-ghost btn-xs">
                            Manage
                        </a>
                    </div>
                    @php
                        $staffMembers = $servicePlan->staffMembers()->get();
                    @endphp
                    @if($staffMembers->isEmpty())
                        <div class="text-center py-8">
                            <span class="icon-[tabler--user-off] size-10 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No staff members assigned yet.</p>
                            <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-primary btn-sm mt-4">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Assign Staff Members
                            </a>
                        </div>
                    @else
                        <div class="space-y-3 mt-4">
                            @foreach($staffMembers as $member)
                                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        @if($member->profile_photo_url)
                                            <img src="{{ $member->profile_photo_url }}" alt="{{ $member->name }}"
                                                 class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="avatar placeholder">
                                                <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                                </div>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-medium">{{ $member->name }}</span>
                                            <p class="text-sm text-base-content/60">{{ ucfirst($member->role) }}</p>
                                            @if($member->pivot->custom_price !== null)
                                                <p class="text-sm text-success">${{ number_format($member->pivot->custom_price, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($member->pivot->is_active)
                                            <span class="badge badge-soft badge-success badge-xs">Active</span>
                                        @else
                                            <span class="badge badge-soft badge-neutral badge-xs">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Stats --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--chart-bar] size-5"></span>
                        Stats
                    </h2>
                    @php
                        $totalSlots = $servicePlan->slots()->count();
                        $upcomingSlots = $servicePlan->slots()->where('start_time', '>', now())->count();
                        $completedSlots = $servicePlan->slots()->where('start_time', '<', now())->count();
                        $staffMemberCount = $servicePlan->staffMembers()->count();
                    @endphp
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Total Slots</span>
                            <span class="font-bold">{{ $totalSlots }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Upcoming</span>
                            <span class="font-bold text-primary">{{ $upcomingSlots }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Completed</span>
                            <span class="font-bold text-success">{{ $completedSlots }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Assigned Staff</span>
                            <span class="font-bold">{{ $staffMemberCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Display Settings --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Settings
                    </h2>
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Status</span>
                            @if($servicePlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Booking Page</span>
                            @if($servicePlan->is_visible_on_booking_page)
                                <span class="badge badge-soft badge-info badge-sm">Visible</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Hidden</span>
                            @endif
                        </div>
                        @if($servicePlan->color)
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Color</span>
                                <div class="flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" style="background-color: {{ $servicePlan->color }}"></span>
                                    <span class="text-sm">{{ $servicePlan->color }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Meta Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-square] size-5"></span>
                        Info
                    </h2>
                    <div class="space-y-3 mt-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span>{{ $servicePlan->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Updated</span>
                            <span>{{ $servicePlan->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Slug</span>
                            <span class="font-mono text-xs">{{ $servicePlan->slug }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            @if($locations->isEmpty() && $slotsByLocation->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Scheduled Slots</h3>
                        <p class="text-base-content/60 mb-4">This service plan has no upcoming slots at any location.</p>
                        <a href="{{ route('service-slots.create', ['service_plan_id' => $servicePlan->id]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create First Slot
                        </a>
                    </div>
                </div>
            @else
                {{-- Location Filter Dropdown --}}
                <div class="flex justify-end mb-6">
                    <div class="form-control w-64">
                        <select id="location-filter" class="select select-bordered select-sm">
                            <option value="all" selected>
                                All Locations ({{ $slotsByLocation->flatten()->count() }})
                            </option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->name }}
                                    @if(isset($slotsByLocation[$location->id]))
                                        ({{ $slotsByLocation[$location->id]->count() }})
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
                                    All Upcoming Slots
                                </h2>
                                <a href="{{ route('service-slots.index', ['service_plan_id' => $servicePlan->id]) }}" class="btn btn-ghost btn-sm">
                                    View All <span class="icon-[tabler--chevron-right] size-4"></span>
                                </a>
                            </div>
                            @if($slotsByLocation->flatten()->isEmpty())
                                <div class="text-center py-8">
                                    <span class="icon-[tabler--calendar-off] size-10 text-base-content/20"></span>
                                    <p class="text-base-content/60 mt-2">No upcoming slots scheduled.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Location</th>
                                                <th>Instructor</th>
                                                <th>Status</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($slotsByLocation->flatten()->sortBy('start_time') as $slot)
                                                <tr>
                                                    <td>
                                                        <div class="font-medium">{{ $slot->start_time->format('D, M d, Y') }}</div>
                                                        <div class="text-sm text-base-content/60">{{ $slot->start_time->format('g:i A') }} - {{ $slot->end_time->format('g:i A') }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="flex items-center gap-2">
                                                            <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                                                            {{ $slot->location?->name ?? 'No location' }}
                                                        </div>
                                                        @if($slot->room)
                                                            <div class="text-sm text-base-content/60">{{ $slot->room->name }}</div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $slot->instructor?->name ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-soft badge-sm {{ $slot->getStatusBadgeClass() }}">{{ ucfirst($slot->status) }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="{{ route('service-slots.show', $slot) }}" class="btn btn-ghost btn-xs">
                                                            <span class="icon-[tabler--eye] size-4"></span>
                                                        </a>
                                                        <a href="{{ route('service-slots.edit', $slot) }}" class="btn btn-ghost btn-xs">
                                                            <span class="icon-[tabler--edit] size-4"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
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
                                    <a href="{{ route('service-slots.create', ['service_plan_id' => $servicePlan->id, 'location_id' => $location->id]) }}" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Slot
                                    </a>
                                </div>

                                @if(isset($slotsByLocation[$location->id]) && $slotsByLocation[$location->id]->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Room</th>
                                                    <th>Instructor</th>
                                                    <th>Status</th>
                                                    <th class="text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($slotsByLocation[$location->id] as $slot)
                                                    <tr>
                                                        <td>
                                                            <div class="font-medium">{{ $slot->start_time->format('D, M d, Y') }}</div>
                                                            <div class="text-sm text-base-content/60">{{ $slot->start_time->format('g:i A') }} - {{ $slot->end_time->format('g:i A') }}</div>
                                                        </td>
                                                        <td>{{ $slot->room?->name ?? '-' }}</td>
                                                        <td>{{ $slot->instructor?->name ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-soft badge-sm {{ $slot->getStatusBadgeClass() }}">{{ ucfirst($slot->status) }}</span>
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="{{ route('service-slots.show', $slot) }}" class="btn btn-ghost btn-xs">
                                                                <span class="icon-[tabler--eye] size-4"></span>
                                                            </a>
                                                            <a href="{{ route('service-slots.edit', $slot) }}" class="btn btn-ghost btn-xs">
                                                                <span class="icon-[tabler--edit] size-4"></span>
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
                                        <p class="text-base-content/60 mt-2">No upcoming slots at this location.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
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
