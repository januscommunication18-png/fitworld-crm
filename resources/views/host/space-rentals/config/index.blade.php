@extends('layouts.dashboard')

@section('title', $trans['nav.space_rentals_config'] ?? 'Rentable Spaces')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['nav.space_rentals_config'] ?? 'Rentable Spaces' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.space_rentals_config'] ?? 'Rentable Spaces' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['space_rentals.config.description'] ?? 'Configure spaces available for rent - entire locations or specific rooms.' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('space-rentals.index') }}" class="btn btn-ghost">
                <span class="icon-[tabler--calendar-event] size-5"></span>
                {{ $trans['space_rentals.view_bookings'] ?? 'View Bookings' }}
            </a>
            <a href="{{ route('space-rentals.config.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['space_rentals.add_space'] ?? 'Add Space' }}
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('space-rentals.config.index') }}" class="tab {{ !$status && !$type ? 'tab-active' : '' }}">{{ $trans['common.all'] ?? 'All' }}</a>
            <a href="{{ route('space-rentals.config.index', ['status' => 'active']) }}" class="tab {{ $status === 'active' ? 'tab-active' : '' }}">{{ $trans['common.active'] ?? 'Active' }}</a>
            <a href="{{ route('space-rentals.config.index', ['status' => 'inactive']) }}" class="tab {{ $status === 'inactive' ? 'tab-active' : '' }}">{{ $trans['common.inactive'] ?? 'Inactive' }}</a>
        </div>

        @if(count($types) > 0)
        <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
            <option value="{{ route('space-rentals.config.index', ['status' => $status]) }}" {{ !$type ? 'selected' : '' }}>{{ $trans['space_rentals.all_types'] ?? 'All Types' }}</option>
            @foreach($types as $key => $label)
                <option value="{{ route('space-rentals.config.index', ['type' => $key, 'status' => $status]) }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @endif
    </div>

    {{-- Content --}}
    @if($configs->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--building] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['space_rentals.no_spaces'] ?? 'No Rentable Spaces Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['space_rentals.get_started'] ?? 'Configure your studio or rooms for external rentals like photo shoots or workshops.' }}</p>
                <a href="{{ route('space-rentals.config.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['space_rentals.add_first_space'] ?? 'Add First Space' }}
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($configs as $config)
            <div class="card bg-base-100 {{ !$config->is_active ? 'opacity-60' : '' }}">
                <div class="card-body">
                    {{-- Header --}}
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span class="icon-[tabler--{{ $config->type_icon }}] size-6 text-primary"></span>
                            </div>
                            <div>
                                <a href="{{ route('space-rentals.config.show', $config) }}" class="font-semibold text-lg hover:text-primary">
                                    {{ $config->name }}
                                </a>
                                <p class="text-sm text-base-content/60">{{ $config->space_name }}</p>
                            </div>
                        </div>
                        <form action="{{ route('space-rentals.config.toggle-status', $config) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="badge cursor-pointer {{ $config->is_active ? 'badge-success' : 'badge-neutral' }} badge-soft badge-sm">
                                {{ $config->is_active ? ($trans['common.active'] ?? 'Active') : ($trans['common.inactive'] ?? 'Inactive') }}
                            </button>
                        </form>
                    </div>

                    {{-- Details --}}
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</span>
                            <span class="font-medium">{{ $config->getFormattedHourlyRateForCurrency() }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['space_rentals.min_max_hours'] ?? 'Min/Max Hours' }}</span>
                            <span class="font-medium">{{ $config->min_max_hours_display }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">{{ $trans['space_rentals.deposit'] ?? 'Deposit' }}</span>
                            <span class="font-medium">{{ $config->getFormattedDepositForCurrency() }}</span>
                        </div>
                        @if($config->requires_waiver)
                        <div class="flex items-center gap-2 text-sm text-warning">
                            <span class="icon-[tabler--file-certificate] size-4"></span>
                            {{ $trans['space_rentals.waiver_required'] ?? 'Waiver Required' }}
                        </div>
                        @endif
                    </div>

                    {{-- Allowed purposes --}}
                    @if(!empty($config->allowed_purposes))
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach($config->getAllowedPurposesLabels() as $purpose)
                        <span class="badge badge-ghost badge-xs">{{ $purpose }}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Stats --}}
                    <div class="mt-4 pt-4 border-t border-base-200 flex items-center justify-between">
                        <div class="text-sm text-base-content/60">
                            <span class="font-medium text-base-content">{{ $config->rentals_count }}</span>
                            {{ $trans['space_rentals.total_bookings'] ?? 'total bookings' }}
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('space-rentals.config.show', $config) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.view'] ?? 'View' }}">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                            <a href="{{ route('space-rentals.config.edit', $config) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.edit'] ?? 'Edit' }}">
                                <span class="icon-[tabler--edit] size-4"></span>
                            </a>
                            <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-ghost btn-xs btn-square text-primary" title="{{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}">
                                <span class="icon-[tabler--calendar-plus] size-4"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
