@extends('layouts.dashboard')

@section('title', $trans['nav.space_rentals'] ?? 'Space Rentals')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['nav.space_rentals'] ?? 'Space Rentals' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.space_rentals'] ?? 'Space Rentals' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['space_rentals.description'] ?? 'Manage space and room rental bookings.' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('space-rentals.config.index') }}" class="btn btn-ghost">
                <span class="icon-[tabler--settings] size-5"></span>
                {{ $trans['space_rentals.configure'] ?? 'Configure Spaces' }}
            </a>
            <a href="{{ route('space-rentals.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('space-rentals.index') }}" class="tab {{ !$status ? 'tab-active' : '' }}">{{ $trans['common.all'] ?? 'All' }}</a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('space-rentals.index', ['status' => $key, 'config_id' => $configId]) }}"
                   class="tab {{ $status === $key ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($configs->isNotEmpty())
        <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
            <option value="{{ route('space-rentals.index', ['status' => $status]) }}" {{ !$configId ? 'selected' : '' }}>
                {{ $trans['space_rentals.all_spaces'] ?? 'All Spaces' }}
            </option>
            @foreach($configs as $config)
                <option value="{{ route('space-rentals.index', ['config_id' => $config->id, 'status' => $status]) }}"
                    {{ $configId == $config->id ? 'selected' : '' }}>{{ $config->name }}</option>
            @endforeach
        </select>
        @endif
    </div>

    {{-- Content --}}
    @if($rentals->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-event] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['space_rentals.no_rentals'] ?? 'No Space Rentals Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['space_rentals.get_started_booking'] ?? 'Create your first space rental booking.' }}</p>
                <a href="{{ route('space-rentals.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['space_rentals.create_first'] ?? 'Create First Booking' }}
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
                                <th>{{ $trans['space_rentals.booking'] ?? 'Booking' }}</th>
                                <th>{{ $trans['field.client'] ?? 'Client' }}</th>
                                <th>{{ $trans['space_rentals.space'] ?? 'Space' }}</th>
                                <th>{{ $trans['space_rentals.date_time'] ?? 'Date & Time' }}</th>
                                <th>{{ $trans['field.total'] ?? 'Total' }}</th>
                                <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                <th class="w-24">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentals as $rental)
                            <tr>
                                <td>
                                    <div>
                                        <a href="{{ route('space-rentals.show', $rental) }}" class="font-medium hover:text-primary">
                                            {{ $rental->reference_number }}
                                        </a>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="icon-[tabler--{{ $rental->purpose_icon }}] size-3.5 text-base-content/60"></span>
                                            <span class="text-xs text-base-content/60">{{ $rental->formatted_purpose }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-medium">{{ $rental->client_name }}</div>
                                        @if($rental->client_company)
                                            <div class="text-xs text-base-content/60">{{ $rental->client_company }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--{{ $rental->config?->type_icon ?? 'building' }}] size-4 text-base-content/60"></span>
                                        <span>{{ $rental->config?->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-medium">{{ $rental->start_time->format('M j, Y') }}</div>
                                        <div class="text-sm text-base-content/60">{{ $rental->formatted_time_range }}</div>
                                    </div>
                                </td>
                                <td class="font-medium">{{ $rental->formatted_total }}</td>
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="badge {{ $rental->status_badge_class }} badge-soft badge-sm">
                                            {{ $rental->formatted_status }}
                                        </span>
                                        @if($rental->isWaiverPending())
                                            <span class="badge badge-warning badge-outline badge-xs">{{ $trans['space_rentals.waiver_pending'] ?? 'Waiver Pending' }}</span>
                                        @endif
                                        @if($rental->isDepositPending())
                                            <span class="badge badge-warning badge-outline badge-xs">{{ $trans['space_rentals.deposit_pending'] ?? 'Deposit Pending' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('space-rentals.show', $rental) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.view'] ?? 'View' }}">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        @if(in_array($rental->status, ['draft', 'pending']))
                                        <a href="{{ route('space-rentals.edit', $rental) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.edit'] ?? 'Edit' }}">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $rentals->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
