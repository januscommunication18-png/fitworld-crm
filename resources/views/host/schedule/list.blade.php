@extends('layouts.dashboard')

@section('title', 'Schedule List')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold">Schedule</h1>
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('schedule.today') }}" class="btn btn-sm btn-soft">
                    <span class="icon-[tabler--calendar-time] size-4"></span>
                    <span class="hidden sm:inline">Today</span>
                </a>
                <a href="{{ route('schedule.calendar') }}" class="btn btn-sm btn-soft">
                    <span class="icon-[tabler--calendar-month] size-4"></span>
                    <span class="hidden sm:inline">Calendar</span>
                </a>
                <a href="{{ route('schedule.list', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="btn btn-sm btn-primary">
                    <span class="icon-[tabler--list] size-4"></span>
                    <span class="hidden sm:inline">List</span>
                </a>
            </div>
        </div>

        {{-- Quick Add Dropdown --}}
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4"></span>
                Add New
            </div>
            <ul tabindex="0" class="dropdown-menu w-48">
                <li>
                    <a href="{{ route('class-sessions.create') }}" class="dropdown-item">
                        <span class="icon-[tabler--yoga] size-4"></span>
                        Class Session
                    </a>
                </li>
                <li>
                    <a href="{{ route('service-slots.create') }}" class="dropdown-item">
                        <span class="icon-[tabler--massage] size-4"></span>
                        Service Slot
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Date Range & Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form id="filterForm" method="GET" action="{{ route('schedule.list') }}">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    {{-- Date Range --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-base-content/60">From</span>
                        <input type="date"
                               name="start_date"
                               value="{{ $startDate->format('Y-m-d') }}"
                               class="input input-bordered input-sm w-36"
                               onchange="this.form.submit()">
                        <span class="text-sm text-base-content/60">to</span>
                        <input type="date"
                               name="end_date"
                               value="{{ $endDate->format('Y-m-d') }}"
                               class="input input-bordered input-sm w-36"
                               onchange="this.form.submit()">

                        {{-- Quick Range Buttons --}}
                        <div class="btn-group ml-2">
                            <a href="{{ route('schedule.list', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->addDays(7)->format('Y-m-d')]) }}"
                               class="btn btn-ghost btn-xs {{ $startDate->isToday() && $endDate->diffInDays($startDate) == 7 ? 'btn-active' : '' }}">
                                7 days
                            </a>
                            <a href="{{ route('schedule.list', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->addDays(14)->format('Y-m-d')]) }}"
                               class="btn btn-ghost btn-xs {{ $startDate->isToday() && $endDate->diffInDays($startDate) == 14 ? 'btn-active' : '' }}">
                                14 days
                            </a>
                            <a href="{{ route('schedule.list', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}"
                               class="btn btn-ghost btn-xs">
                                This month
                            </a>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="font-medium">{{ $stats['total'] }}</span>
                            <span class="text-base-content/60">total</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="font-medium text-primary">{{ $stats['classes'] }}</span>
                            <span class="text-base-content/60">classes</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="font-medium text-secondary">{{ $stats['services'] }}</span>
                            <span class="text-base-content/60">services</span>
                        </div>
                    </div>
                </div>

                {{-- Filters Row --}}
                <div class="mt-4 pt-4 border-t border-base-200">
                    @include('host.schedule._filters', ['filters' => $filters, 'locations' => $locations, 'instructors' => $instructors])
                </div>
            </form>
        </div>
    </div>

    {{-- Schedule List by Date --}}
    @if($scheduleByDate->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body py-16 text-center">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                <h3 class="text-lg font-semibold mt-4">No Events Found</h3>
                <p class="text-base-content/60 mt-1">
                    No events scheduled between {{ $startDate->format('M j') }} and {{ $endDate->format('M j, Y') }}.
                </p>
                <div class="flex items-center justify-center gap-2 mt-6">
                    <a href="{{ route('class-sessions.create') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Schedule Class
                    </a>
                    <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Service Slot
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($scheduleByDate as $dayGroup)
                @php
                    $dayDate = $dayGroup['date'];
                    $isToday = $dayDate->isToday();
                    $isPast = $dayDate->isPast() && !$isToday;
                @endphp
                <div class="card bg-base-100 {{ $isPast ? 'opacity-75' : '' }}">
                    <div class="card-body">
                        {{-- Date Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-lg bg-base-200 flex flex-col items-center justify-center {{ $isToday ? 'bg-primary text-primary-content' : '' }}">
                                    <span class="text-xs uppercase">{{ $dayDate->format('D') }}</span>
                                    <span class="text-xl font-bold">{{ $dayDate->format('j') }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        {{ $dayDate->format('l, F j') }}
                                        @if($isToday)
                                            <span class="badge badge-primary badge-sm ml-2">Today</span>
                                        @endif
                                    </h3>
                                    <div class="text-sm text-base-content/60">
                                        {{ $dayGroup['classes_count'] }} {{ Str::plural('class', $dayGroup['classes_count']) }},
                                        {{ $dayGroup['services_count'] }} {{ Str::plural('service', $dayGroup['services_count']) }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('schedule.today', ['date' => $dayDate->format('Y-m-d')]) }}"
                               class="btn btn-ghost btn-sm">
                                View Day
                                <span class="icon-[tabler--arrow-right] size-4"></span>
                            </a>
                        </div>

                        {{-- Events Table --}}
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="w-24">Time</th>
                                        <th class="w-20">Type</th>
                                        <th>Title</th>
                                        <th>Instructor</th>
                                        <th>Location</th>
                                        <th class="w-24">Status</th>
                                        <th class="w-20 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayGroup['items'] as $item)
                                        @php
                                            $isClass = $item->schedule_type === 'class';
                                        @endphp
                                        <tr class="{{ $item->has_conflict ? 'bg-error/5' : '' }}">
                                            <td>
                                                <div class="font-medium">{{ $item->start_time->format('g:i A') }}</div>
                                                <div class="text-xs text-base-content/60">
                                                    {{ $item->start_time->diffInMinutes($item->end_time) }} min
                                                </div>
                                            </td>
                                            <td>
                                                @if($isClass)
                                                    <span class="badge badge-soft badge-primary badge-xs">Class</span>
                                                @else
                                                    <span class="badge badge-soft badge-secondary badge-xs">Service</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ $isClass ? route('class-sessions.show', $item) : route('service-slots.show', $item) }}"
                                                   class="font-medium hover:text-primary">
                                                    {{ $item->schedule_title }}
                                                </a>
                                                @if($item->has_conflict)
                                                    <span class="tooltip tooltip-error ml-1" data-tip="Schedule conflict">
                                                        <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->schedule_instructor?->name ?? '-' }}</td>
                                            <td>
                                                {{ $item->location?->name ?? '-' }}
                                                @if($item->room)
                                                    <span class="text-base-content/60">/ {{ $item->room->name }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-soft {{ $item->getStatusBadgeClass() }} badge-xs">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ $isClass ? route('class-sessions.show', $item) : route('service-slots.show', $item) }}"
                                                       class="btn btn-ghost btn-xs btn-circle" title="View">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                    </a>
                                                    <a href="{{ $isClass ? route('class-sessions.edit', $item) : route('service-slots.edit', $item) }}"
                                                       class="btn btn-ghost btn-xs btn-circle" title="Edit">
                                                        <span class="icon-[tabler--edit] size-4"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    function applyFilters() {
        document.getElementById('filterForm').submit();
    }

    function clearFilters() {
        const url = new URL(window.location.href);
        url.searchParams.delete('type');
        url.searchParams.delete('location_id');
        url.searchParams.delete('instructor_id');
        url.searchParams.delete('status');
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection
