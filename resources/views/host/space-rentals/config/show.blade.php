@extends('layouts.dashboard')

@section('title', $config->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> {{ $trans['nav.catalog'] ?? 'Catalog' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $config->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="w-16 h-16 rounded-xl bg-secondary/10 flex items-center justify-center">
                <span class="icon-[tabler--{{ $config->type_icon }}] size-8 text-secondary"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $config->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $config->is_active ? 'badge-success' : 'badge-neutral' }} badge-soft badge-sm">
                        {{ $config->is_active ? ($trans['common.active'] ?? 'Active') : ($trans['common.inactive'] ?? 'Inactive') }}
                    </span>
                    <span class="badge badge-ghost badge-sm">{{ $config->space_name }}</span>
                    <span class="badge badge-soft badge-secondary badge-sm capitalize">{{ $config->rentable_type }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--calendar-plus] size-4"></span>
                {{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}
            </a>
            <a href="{{ route('space-rentals.config.edit', $config) }}" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                {{ $trans['btn.edit'] ?? 'Edit' }}
            </a>
        </div>
    </div>

    {{-- Main Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>{{ $trans['common.overview'] ?? 'Overview' }}
        </button>
        <button class="tab {{ $tab === 'schedule' ? 'tab-active' : '' }}" data-tab="schedule" role="tab">
            <span class="icon-[tabler--calendar] size-4 mr-2"></span>{{ $trans['common.schedule'] ?? 'Schedule' }}
            @if($upcomingRentals->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $upcomingRentals->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Details Card --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">{{ $trans['space_rentals.details'] ?? 'Space Details' }}</h2>

                            @if($config->description)
                            <p class="text-base-content/70 mt-2">{{ $config->description }}</p>
                            @endif

                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="bg-base-200/50 rounded-lg p-4">
                                    <div class="text-sm text-base-content/60 mb-1">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</div>
                                    <div class="text-lg font-semibold">{{ $config->getFormattedHourlyRateForCurrency() }}</div>
                                </div>
                                <div class="bg-base-200/50 rounded-lg p-4">
                                    <div class="text-sm text-base-content/60 mb-1">{{ $trans['space_rentals.deposit'] ?? 'Security Deposit' }}</div>
                                    <div class="text-lg font-semibold">{{ $config->getFormattedDepositForCurrency() }}</div>
                                </div>
                                <div class="bg-base-200/50 rounded-lg p-4">
                                    <div class="text-sm text-base-content/60 mb-1">{{ $trans['space_rentals.min_hours'] ?? 'Minimum Hours' }}</div>
                                    <div class="text-lg font-semibold">{{ $config->minimum_hours }} {{ $trans['common.hours'] ?? 'hours' }}</div>
                                </div>
                                <div class="bg-base-200/50 rounded-lg p-4">
                                    <div class="text-sm text-base-content/60 mb-1">{{ $trans['space_rentals.max_hours'] ?? 'Maximum Hours' }}</div>
                                    <div class="text-lg font-semibold">{{ $config->maximum_hours ? $config->maximum_hours . ' ' . ($trans['common.hours'] ?? 'hours') : ($trans['common.no_limit'] ?? 'No limit') }}</div>
                                </div>
                            </div>

                            {{-- Buffer times --}}
                            @if($config->setup_time_minutes > 0 || $config->cleanup_time_minutes > 0)
                            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-base-200">
                                @if($config->setup_time_minutes > 0)
                                <div class="flex items-center gap-2 text-sm text-base-content/60">
                                    <span class="icon-[tabler--clock-play] size-4"></span>
                                    {{ $config->setup_time_minutes }} {{ $trans['common.min'] ?? 'min' }} {{ $trans['space_rentals.setup'] ?? 'setup' }}
                                </div>
                                @endif
                                @if($config->cleanup_time_minutes > 0)
                                <div class="flex items-center gap-2 text-sm text-base-content/60">
                                    <span class="icon-[tabler--clock-pause] size-4"></span>
                                    {{ $config->cleanup_time_minutes }} {{ $trans['common.min'] ?? 'min' }} {{ $trans['space_rentals.cleanup'] ?? 'cleanup' }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Allowed Purposes --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">{{ $trans['space_rentals.allowed_purposes'] ?? 'Allowed Purposes' }}</h2>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @php $allPurposes = \App\Models\SpaceRentalConfig::getPurposes(); @endphp
                                @if(empty($config->allowed_purposes))
                                    <span class="badge badge-success badge-soft">{{ $trans['space_rentals.all_purposes'] ?? 'All purposes allowed' }}</span>
                                @else
                                    @foreach($config->allowed_purposes as $purpose)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-base-200/50 rounded-lg">
                                        <span class="icon-[tabler--{{ \App\Models\SpaceRentalConfig::getPurposeIcon($purpose) }}] size-4 text-primary"></span>
                                        <span class="text-sm">{{ $allPurposes[$purpose] ?? $purpose }}</span>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Rules --}}
                    @if($config->rules)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">{{ $trans['space_rentals.rules'] ?? 'Rules & Guidelines' }}</h2>
                            <div class="prose prose-sm mt-2 text-base-content/70">
                                {!! nl2br(e($config->rules)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Upcoming Rentals --}}
                    @if($upcomingRentals->isNotEmpty())
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <h2 class="card-title text-lg">{{ $trans['space_rentals.upcoming'] ?? 'Upcoming Rentals' }}</h2>
                                <button class="btn btn-ghost btn-sm" data-tab="schedule" onclick="switchToScheduleTab()">
                                    {{ $trans['btn.view_all'] ?? 'View All' }}
                                </button>
                            </div>
                            <div class="space-y-3 mt-4">
                                @foreach($upcomingRentals as $rental)
                                <a href="{{ route('space-rentals.show', $rental) }}" class="flex items-center gap-4 p-3 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors">
                                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex flex-col items-center justify-center">
                                        <span class="text-xs font-medium text-primary">{{ $rental->start_time->format('M') }}</span>
                                        <span class="text-lg font-bold text-primary leading-none">{{ $rental->start_time->format('j') }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $rental->client_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $rental->formatted_time_range }}</div>
                                    </div>
                                    <span class="badge {{ $rental->status_badge_class }} badge-soft badge-sm">{{ $rental->formatted_status }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Quick Stats --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="font-semibold mb-4">{{ $trans['common.statistics'] ?? 'Statistics' }}</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">{{ $trans['space_rentals.total_bookings'] ?? 'Total Bookings' }}</span>
                                    <span class="font-semibold">{{ $recentRentals->count() }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">{{ $trans['space_rentals.upcoming_count'] ?? 'Upcoming' }}</span>
                                    <span class="font-semibold">{{ $upcomingRentals->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Waiver Status --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="font-semibold mb-4">{{ $trans['space_rentals.waiver_status'] ?? 'Waiver' }}</h3>
                            @if($config->requires_waiver)
                                <div class="flex items-center gap-3 text-warning">
                                    <span class="icon-[tabler--file-certificate] size-5"></span>
                                    <span class="text-sm">{{ $trans['space_rentals.waiver_required'] ?? 'Waiver Required' }}</span>
                                </div>
                                @if($config->waiver_document_path)
                                <div class="mt-3 pt-3 border-t border-base-200">
                                    <a href="{{ Storage::disk(config('filesystems.uploads'))->url($config->waiver_document_path) }}" target="_blank" class="btn btn-ghost btn-sm w-full">
                                        <span class="icon-[tabler--download] size-4"></span>
                                        {{ $trans['space_rentals.download_waiver'] ?? 'Download Waiver' }}
                                    </a>
                                </div>
                                @endif
                            @else
                                <div class="flex items-center gap-3 text-base-content/60">
                                    <span class="icon-[tabler--file-off] size-5"></span>
                                    <span class="text-sm">{{ $trans['space_rentals.no_waiver'] ?? 'No waiver required' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Location Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="font-semibold mb-4">{{ $trans['field.location'] ?? 'Location' }}</h3>
                            @if($config->location)
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--map-pin] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <div class="font-medium">{{ $config->location->name }}</div>
                                    @if($config->location->full_address)
                                    <div class="text-sm text-base-content/60 mt-1">{{ $config->location->full_address }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if($config->room)
                            <div class="flex items-start gap-3 mt-3 pt-3 border-t border-base-200">
                                <span class="icon-[tabler--door] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <div class="font-medium">{{ $config->room->name }}</div>
                                    @if($config->room->capacity)
                                    <div class="text-sm text-base-content/60">{{ $trans['field.capacity'] ?? 'Capacity' }}: {{ $config->room->capacity }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div class="card bg-base-100 border border-error/20">
                        <div class="card-body">
                            <h3 class="font-semibold text-error mb-4">{{ $trans['common.danger_zone'] ?? 'Danger Zone' }}</h3>
                            <form action="{{ route('space-rentals.config.destroy', $config) }}" method="POST"
                                onsubmit="return confirm('{{ $trans['msg.confirm.delete_space'] ?? 'Are you sure you want to delete this rentable space? This cannot be undone.' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-error btn-outline btn-sm w-full">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    {{ $trans['btn.delete'] ?? 'Delete' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            @if($allRentals->isEmpty() && $tab === 'schedule')
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">{{ $trans['space_rentals.no_rentals'] ?? 'No Rentals Yet' }}</h3>
                        <p class="text-base-content/60 mb-4">{{ $trans['space_rentals.no_rentals_desc'] ?? 'No rental bookings have been made for this space.' }}</p>
                        <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            {{ $trans['space_rentals.create_first'] ?? 'Create First Booking' }}
                        </a>
                    </div>
                </div>
            @elseif($tab === 'schedule')
                {{-- Status Filter --}}
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--plus] size-4"></span>
                            {{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}
                        </a>
                    </div>
                    <div class="form-control w-48">
                        <select id="status-filter" class="select select-bordered select-sm">
                            <option value="all">{{ $trans['common.all'] ?? 'All' }} ({{ $allRentals->count() }})</option>
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
                                        <th>{{ $trans['space_rentals.date_time'] ?? 'Date & Time' }}</th>
                                        <th>{{ $trans['field.client'] ?? 'Client' }}</th>
                                        <th>{{ $trans['space_rentals.purpose'] ?? 'Purpose' }}</th>
                                        <th>{{ $trans['field.total'] ?? 'Total' }}</th>
                                        <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                        <th class="text-right">{{ $trans['common.actions'] ?? 'Actions' }}</th>
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
                                                    <span class="badge badge-warning badge-outline badge-xs">{{ $trans['space_rentals.waiver_pending'] ?? 'Waiver Pending' }}</span>
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
                {{-- Placeholder for when overview tab is active --}}
                <div class="card bg-base-100">
                    <div class="card-body text-center py-8">
                        <span class="loading loading-spinner loading-lg"></span>
                        <p class="text-base-content/60 mt-2">{{ $trans['common.loading'] ?? 'Loading...' }}</p>
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

            // If switching to schedule tab, reload to fetch data
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
                if (selectedStatus === 'all' || row.dataset.status === selectedStatus) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    }
});

function switchToScheduleTab() {
    const url = new URL(window.location);
    url.searchParams.set('tab', 'schedule');
    window.location.href = url.toString();
}
</script>
@endpush
