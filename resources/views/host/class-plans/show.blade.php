@extends('layouts.dashboard')

@section('title', $classPlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'classes']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $classPlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('catalog.index', ['tab' => 'classes']) }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            @if($classPlan->image_url)
                <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--yoga] size-10 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $classPlan->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($classPlan->is_active)
                        <span class="badge badge-soft badge-success">Active</span>
                    @else
                        <span class="badge badge-soft badge-neutral">Inactive</span>
                    @endif
                    @if($classPlan->is_visible_on_booking_page)
                        <span class="badge badge-soft badge-info badge-sm">Visible on Booking</span>
                    @endif
                    @if($classPlan->category)
                        <span class="badge badge-soft badge-primary badge-sm">{{ \App\Models\ClassPlan::getCategories()[$classPlan->category] ?? $classPlan->category }}</span>
                    @endif
                    @if($classPlan->difficulty_level)
                        <span class="badge badge-soft badge-sm {{ $classPlan->getDifficultyBadgeClass() }}">
                            {{ \App\Models\ClassPlan::getDifficultyLevels()[$classPlan->difficulty_level] ?? $classPlan->difficulty_level }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('class-plans.edit', $classPlan) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
            <a href="{{ route('class-sessions.create', ['class_plan_id' => $classPlan->id]) }}" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--plus] size-4"></span>
                Schedule Class
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            @if($classPlan->description)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        <p class="mt-2 whitespace-pre-line">{{ $classPlan->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Class Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Class Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Type</label>
                            <p class="font-medium">{{ \App\Models\ClassPlan::getTypes()[$classPlan->type] ?? $classPlan->type ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Duration</label>
                            <p class="font-medium">{{ $classPlan->formatted_duration }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Capacity</label>
                            <p class="font-medium">{{ $classPlan->default_capacity ?? '-' }} students</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Min Capacity</label>
                            <p class="font-medium">{{ $classPlan->min_capacity ?? '-' }}</p>
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
                                    <td class="text-base-content/70">Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($classPlan->new_member_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPlan->new_member_prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Drop-in Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($classPlan->new_member_drop_in_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPlan->new_member_drop_in_prices[$currency], 2) }}
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
                                    <td class="text-base-content/70">Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($classPlan->prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPlan->prices[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Drop-in Price</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium text-success">
                                            @if(!empty($classPlan->drop_in_prices[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($classPlan->drop_in_prices[$currency], 2) }}
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

            {{-- Equipment Needed --}}
            @if($classPlan->equipment_needed && count($classPlan->equipment_needed) > 0)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--tool] size-5"></span>
                            Equipment Needed
                        </h2>
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($classPlan->equipment_needed as $equipment)
                                <span class="badge badge-soft badge-neutral">{{ $equipment }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upcoming Sessions --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--calendar] size-5"></span>
                            Upcoming Sessions
                        </h2>
                        <a href="{{ route('class-sessions.index', ['class_plan_id' => $classPlan->id]) }}" class="btn btn-ghost btn-xs">
                            View All
                        </a>
                    </div>
                    @php
                        $upcomingSessions = $classPlan->sessions()
                            ->where('start_time', '>', now())
                            ->where('status', '!=', 'cancelled')
                            ->with(['primaryInstructor', 'location'])
                            ->orderBy('start_time')
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($upcomingSessions->isEmpty())
                        <div class="text-center py-8">
                            <span class="icon-[tabler--calendar-off] size-10 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No upcoming sessions scheduled.</p>
                            <a href="{{ route('class-sessions.create', ['class_plan_id' => $classPlan->id]) }}" class="btn btn-primary btn-sm mt-4">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Schedule First Session
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto mt-4">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Instructor</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingSessions as $session)
                                        <tr>
                                            <td>
                                                <a href="{{ route('class-sessions.show', $session) }}" class="font-medium hover:text-primary">
                                                    {{ $session->start_time->format('D, M d') }}
                                                </a>
                                                <div class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }}</div>
                                            </td>
                                            <td>{{ $session->primaryInstructor?->name ?? '-' }}</td>
                                            <td>{{ $session->location?->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $statusBadge = match($session->status) {
                                                        'published' => 'badge-success',
                                                        'draft' => 'badge-warning',
                                                        'cancelled' => 'badge-error',
                                                        default => 'badge-neutral'
                                                    };
                                                @endphp
                                                <span class="badge badge-soft badge-xs {{ $statusBadge }}">{{ ucfirst($session->status) }}</span>
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
                        $totalSessions = $classPlan->sessions()->count();
                        $upcomingCount = $classPlan->sessions()->where('start_time', '>', now())->where('status', '!=', 'cancelled')->count();
                        $completedCount = $classPlan->sessions()->where('start_time', '<', now())->where('status', 'published')->count();
                    @endphp
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Total Sessions</span>
                            <span class="font-bold">{{ $totalSessions }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Upcoming</span>
                            <span class="font-bold text-primary">{{ $upcomingCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Completed</span>
                            <span class="font-bold text-success">{{ $completedCount }}</span>
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
                            @if($classPlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Booking Page</span>
                            @if($classPlan->is_visible_on_booking_page)
                                <span class="badge badge-soft badge-info badge-sm">Visible</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Hidden</span>
                            @endif
                        </div>
                        @if($classPlan->color)
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Color</span>
                                <div class="flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" style="background-color: {{ $classPlan->color }}"></span>
                                    <span class="text-sm">{{ $classPlan->color }}</span>
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
                            <span>{{ $classPlan->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Updated</span>
                            <span>{{ $classPlan->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Slug</span>
                            <span class="font-mono text-xs">{{ $classPlan->slug }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            @if($locations->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Scheduled Sessions</h3>
                        <p class="text-base-content/60 mb-4">This class plan has no upcoming sessions at any location.</p>
                        <a href="{{ route('class-sessions.create', ['class_plan_id' => $classPlan->id]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Schedule First Session
                        </a>
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
                                <a href="{{ route('class-sessions.index', ['class_plan_id' => $classPlan->id]) }}" class="btn btn-ghost btn-sm">
                                    View All <span class="icon-[tabler--chevron-right] size-4"></span>
                                </a>
                            </div>
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
                                        @foreach($sessionsByLocation->flatten()->sortBy('start_time') as $session)
                                            <tr>
                                                <td>
                                                    <div class="font-medium">{{ $session->start_time->format('D, M d, Y') }}</div>
                                                    <div class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}</div>
                                                </td>
                                                <td>
                                                    <div class="flex items-center gap-2">
                                                        <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                                                        {{ $session->location?->name ?? 'No location' }}
                                                    </div>
                                                    @if($session->room)
                                                        <div class="text-sm text-base-content/60">{{ $session->room->name }}</div>
                                                    @endif
                                                </td>
                                                <td>{{ $session->primaryInstructor?->name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-soft badge-sm {{ $session->getStatusBadgeClass() }}">{{ ucfirst($session->status) }}</span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                    </a>
                                                    <a href="{{ route('class-sessions.edit', $session) }}" class="btn btn-ghost btn-xs">
                                                        <span class="icon-[tabler--edit] size-4"></span>
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
                                        @if($location->address)
                                            <p class="text-sm text-base-content/60 mt-1">{{ $location->address }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('class-sessions.create', ['class_plan_id' => $classPlan->id, 'location_id' => $location->id]) }}" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Session
                                    </a>
                                </div>

                                @if(isset($sessionsByLocation[$location->id]) && $sessionsByLocation[$location->id]->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Room</th>
                                                    <th>Instructor</th>
                                                    <th>Capacity</th>
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
                                                        <td>{{ $session->room?->name ?? '-' }}</td>
                                                        <td>{{ $session->primaryInstructor?->name ?? '-' }}</td>
                                                        <td>{{ $session->getEffectiveCapacity() }}</td>
                                                        <td>
                                                            <span class="badge badge-soft badge-sm {{ $session->getStatusBadgeClass() }}">{{ ucfirst($session->status) }}</span>
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs">
                                                                <span class="icon-[tabler--eye] size-4"></span>
                                                            </a>
                                                            <a href="{{ route('class-sessions.edit', $session) }}" class="btn btn-ghost btn-xs">
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
                                        <p class="text-base-content/60 mt-2">No upcoming sessions at this location.</p>
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
