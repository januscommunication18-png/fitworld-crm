@extends('layouts.dashboard')

@section('title', $config->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}">Rental Spaces</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $config->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <div class="w-24 h-24 rounded-lg bg-secondary/10 flex items-center justify-center">
                <span class="icon-[tabler--{{ $config->type_icon }}] size-10 text-secondary"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $config->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $config->is_active ? 'badge-success' : 'badge-neutral' }} badge-soft">
                        {{ $config->is_active ? ($trans['common.active'] ?? 'Active') : ($trans['common.inactive'] ?? 'Inactive') }}
                    </span>
                    <span class="badge badge-soft badge-secondary capitalize">{{ $config->rentable_type }}</span>
                    @if($config->location)
                        <span class="badge badge-ghost badge-sm">{{ $config->space_name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('space-rentals.config.edit', $config) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
            <x-actions-dropdown>
                <li><a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}">
                    <span class="icon-[tabler--calendar-plus] size-4"></span> New Booking
                </a></li>
                <li>
                    <form action="{{ route('space-rentals.config.destroy', $config) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this rentable space?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                            <span class="icon-[tabler--trash] size-4"></span> Delete Space
                        </button>
                    </form>
                </li>
            </x-actions-dropdown>
            <a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}" class="btn btn-ghost btn-sm gap-1.5">
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
            @if($upcomingRentals->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $upcomingRentals->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="space-y-6">

            {{-- Description --}}
            @if($config->description)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        <p class="mt-2 whitespace-pre-line">{{ $config->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Space Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Space Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Location</label>
                            <p class="font-medium">{{ $config->location?->name ?? '-' }}</p>
                        </div>
                        @if($config->room)
                        <div>
                            <label class="text-sm text-base-content/60">Room</label>
                            <p class="font-medium">{{ $config->room->name }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-base-content/60">Minimum Hours</label>
                            <p class="font-medium">{{ $config->minimum_hours }} hours</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Maximum Hours</label>
                            <p class="font-medium">{{ $config->maximum_hours ? $config->maximum_hours . ' hours' : 'No limit' }}</p>
                        </div>
                    </div>

                    @if($config->setup_time_minutes > 0 || $config->cleanup_time_minutes > 0)
                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-base-200">
                        @if($config->setup_time_minutes > 0)
                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                            <span class="icon-[tabler--clock-play] size-4"></span>
                            {{ $config->setup_time_minutes }} min setup
                        </div>
                        @endif
                        @if($config->cleanup_time_minutes > 0)
                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                            <span class="icon-[tabler--clock-pause] size-4"></span>
                            {{ $config->cleanup_time_minutes }} min cleanup
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--currency-dollar] size-5"></span>
                        Pricing
                    </h2>
                    @php
                        $host = auth()->user()->host;
                        $hostCurrencies = $host->currencies ?? ['USD'];
                        $currencySymbols = ['USD' => '$', 'CAD' => 'C$', 'GBP' => '£', 'EUR' => '€', 'AUD' => 'A$', 'INR' => '₹'];
                    @endphp
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
                                    <td class="text-base-content/70">Hourly Rate</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium">
                                            @if(!empty($config->hourly_rates[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($config->hourly_rates[$currency], 2) }}
                                            @else
                                                <span class="text-base-content/40">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-base-content/70">Security Deposit</td>
                                    @foreach($hostCurrencies as $currency)
                                        <td class="text-center font-medium">
                                            @if(!empty($config->deposit_rates[$currency]))
                                                {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($config->deposit_rates[$currency], 2) }}
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

            {{-- Allowed Purposes --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--target] size-5"></span>
                        Allowed Purposes
                    </h2>
                    @php $allPurposes = \App\Models\SpaceRentalConfig::getPurposes(); @endphp
                    <div class="flex flex-wrap gap-2 mt-3">
                        @if(empty($config->allowed_purposes))
                            <span class="badge badge-success badge-soft">All purposes allowed</span>
                        @else
                            @foreach($config->allowed_purposes as $purpose)
                            <span class="badge badge-soft badge-primary">
                                <span class="icon-[tabler--{{ \App\Models\SpaceRentalConfig::getPurposeIcon($purpose) }}] size-3.5 me-1"></span>
                                {{ $allPurposes[$purpose] ?? $purpose }}
                            </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Waiver --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--file-certificate] size-5"></span>
                        Waiver
                    </h2>
                    <div class="mt-3">
                        @if($config->requires_waiver)
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--alert-triangle] size-5 text-warning"></span>
                                <span class="text-sm font-medium">Waiver required before rental</span>
                            </div>
                            @if($config->waiver_document_path)
                            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg mt-3">
                                <span class="icon-[tabler--file-type-pdf] size-6 text-base-content/60 shrink-0"></span>
                                <span class="text-sm flex-1 truncate">{{ basename($config->waiver_document_path) }}</span>
                                <a href="{{ Storage::disk(config('filesystems.uploads'))->url($config->waiver_document_path) }}" target="_blank" download class="btn btn-ghost btn-sm btn-circle">
                                    <span class="icon-[tabler--download] size-5"></span>
                                </a>
                            </div>
                            @endif
                        @else
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--check-circle] size-5 text-success"></span>
                                <span class="text-sm">No waiver required</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rules --}}
            @if($config->rules)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--list-check] size-5"></span>
                        Rules & Guidelines
                    </h2>
                    <div class="mt-2 whitespace-pre-line text-base-content/70">{{ $config->rules }}</div>
                </div>
            </div>
            @endif

            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            @if($allRentals->isEmpty() && $tab === 'schedule')
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Rentals Yet</h3>
                        <p class="text-base-content/60 mb-4">No rental bookings have been made for this space.</p>
                        <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create First Booking
                        </a>
                    </div>
                </div>
            @elseif($tab === 'schedule')
                {{-- Status Filter --}}
                <div class="flex justify-between items-center mb-6">
                    <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        New Booking
                    </a>
                    <div class="form-control w-48">
                        <select id="status-filter" class="select select-bordered select-sm">
                            <option value="all">All ({{ $allRentals->count() }})</option>
                            @foreach($statuses as $statusKey => $statusLabel)
                                @php $count = $allRentals->where('status', $statusKey)->count(); @endphp
                                @if($count > 0)
                                <option value="{{ $statusKey }}">{{ $statusLabel }} ({{ $count }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Rentals Table --}}
                <div class="card bg-base-100">
                    <div class="card-body p-0">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Client</th>
                                        <th>Purpose</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rentals-tbody">
                                    @foreach($allRentals as $rental)
                                    <tr data-status="{{ $rental->status }}">
                                        <td>
                                            <div class="font-medium">{{ $rental->start_time->format('D, M d, Y') }}</div>
                                            <div class="text-sm text-base-content/60">{{ $rental->formatted_time_range }}</div>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $rental->client_name }}</div>
                                            @if($rental->client_company)
                                            <div class="text-sm text-base-content/60">{{ $rental->client_company }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--{{ $rental->purpose_icon }}] size-4 text-base-content/60"></span>
                                                <span>{{ $rental->formatted_purpose }}</span>
                                            </div>
                                        </td>
                                        <td class="font-medium">{{ $rental->formatted_total }}</td>
                                        <td>
                                            <div class="flex flex-col gap-1">
                                                <span class="badge {{ $rental->status_badge_class }} badge-soft badge-sm">{{ $rental->formatted_status }}</span>
                                                @if($rental->isWaiverPending())
                                                    <span class="badge badge-warning badge-outline badge-xs">Waiver Pending</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('space-rentals.show', $rental) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--eye] size-4"></span>
                                            </a>
                                            @if(in_array($rental->status, ['draft', 'pending']))
                                            <a href="{{ route('space-rentals.edit', $rental) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-8">
                        <span class="loading loading-spinner loading-lg"></span>
                        <p class="text-base-content/60 mt-2">Loading...</p>
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

            if (targetTab === 'schedule') {
                window.location.href = url.toString();
                return;
            }

            window.history.pushState({}, '', url);

            mainTabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            mainContents.forEach(content => {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        });
    });

    // Status filter for schedule tab
    const statusFilter = document.getElementById('status-filter');
    const rentalsRows = document.querySelectorAll('#rentals-tbody tr');

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            rentalsRows.forEach(row => {
                row.classList.toggle('hidden', selectedStatus !== 'all' && row.dataset.status !== selectedStatus);
            });
        });
    }
});
</script>
@endpush
