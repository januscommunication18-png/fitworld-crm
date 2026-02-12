@extends('layouts.dashboard')

@section('title', 'Schedule - ' . $date->format('M j, Y'))

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
                <a href="{{ route('schedule.today', array_merge(request()->except('view'), ['date' => $date->format('Y-m-d')])) }}"
                   class="btn btn-sm btn-primary">
                    <span class="icon-[tabler--calendar-time] size-4"></span>
                    <span class="hidden sm:inline">Today</span>
                </a>
                <a href="{{ route('schedule.calendar', ['date' => $date->format('Y-m-d')]) }}"
                   class="btn btn-sm btn-soft">
                    <span class="icon-[tabler--calendar-month] size-4"></span>
                    <span class="hidden sm:inline">Calendar</span>
                </a>
                <a href="{{ route('schedule.list', ['start_date' => $date->format('Y-m-d')]) }}"
                   class="btn btn-sm btn-soft">
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

    {{-- Date Navigation --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('schedule.today', array_merge(request()->except('date'), ['date' => $date->copy()->subDay()->format('Y-m-d')])) }}"
                       class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--chevron-left] size-5"></span>
                    </a>

                    <div class="relative">
                        <input type="date"
                               id="datePicker"
                               value="{{ $date->format('Y-m-d') }}"
                               class="input input-bordered input-sm w-40 text-center"
                               onchange="changeDate(this.value)">
                    </div>

                    <a href="{{ route('schedule.today', array_merge(request()->except('date'), ['date' => $date->copy()->addDay()->format('Y-m-d')])) }}"
                       class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--chevron-right] size-5"></span>
                    </a>

                    @if(!$date->isToday())
                        <a href="{{ route('schedule.today', request()->except('date')) }}"
                           class="btn btn-ghost btn-sm">
                            Today
                        </a>
                    @endif

                    <span class="text-lg font-semibold ml-2">
                        {{ $date->format('l, F j, Y') }}
                        @if($date->isToday())
                            <span class="badge badge-primary badge-sm ml-2">Today</span>
                        @endif
                    </span>
                </div>

                {{-- Stats Summary --}}
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-1">
                        <span class="icon-[tabler--calendar-event] size-4 text-base-content/60"></span>
                        <span class="font-medium">{{ $stats['total'] }}</span>
                        <span class="text-base-content/60">events</span>
                    </div>
                    @if($stats['classes'] > 0)
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--yoga] size-4 text-primary"></span>
                            <span class="font-medium">{{ $stats['classes'] }}</span>
                            <span class="text-base-content/60">classes</span>
                        </div>
                    @endif
                    @if($stats['services'] > 0)
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--massage] size-4 text-secondary"></span>
                            <span class="font-medium">{{ $stats['services'] }}</span>
                            <span class="text-base-content/60">services</span>
                        </div>
                    @endif
                    @if($stats['with_conflicts'] > 0)
                        <div class="flex items-center gap-1 text-error">
                            <span class="icon-[tabler--alert-triangle] size-4"></span>
                            <span class="font-medium">{{ $stats['with_conflicts'] }}</span>
                            <span>conflicts</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form id="filterForm" method="GET" action="{{ route('schedule.today') }}">
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        @include('host.schedule._filters', ['filters' => $filters, 'locations' => $locations, 'instructors' => $instructors])
    </form>

    {{-- Schedule by Location --}}
    @if($scheduleByLocation->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body py-16 text-center">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                <h3 class="text-lg font-semibold mt-4">No Events Scheduled</h3>
                <p class="text-base-content/60 mt-1">
                    @if($date->isToday())
                        You don't have any events scheduled for today.
                    @else
                        No events found for {{ $date->format('F j, Y') }}.
                    @endif
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
            @foreach($scheduleByLocation as $group)
                <div class="space-y-3">
                    {{-- Location Header --}}
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--map-pin] size-5 text-primary"></span>
                        <h2 class="text-lg font-semibold">{{ $group['location_name'] }}</h2>
                        <span class="badge badge-soft badge-neutral badge-sm">{{ $group['count'] }} events</span>
                    </div>

                    {{-- Events --}}
                    <div class="space-y-2">
                        @foreach($group['items'] as $item)
                            @include('host.schedule._event-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    function changeDate(date) {
        const url = new URL(window.location.href);
        url.searchParams.set('date', date);
        window.location.href = url.toString();
    }

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
