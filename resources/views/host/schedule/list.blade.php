@extends('layouts.dashboard')

@section('title', 'Schedule List')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">List View</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Schedule List</h1>
            <p class="text-base-content/60 mt-1">{{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Class
            </a>
            <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Service
            </a>
        </div>
    </div>

    {{-- Sub Navigation --}}
    @include('host.schedule.partials.sub-nav')

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form action="{{ route('schedule.list') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                {{-- Date Range --}}
                <div class="w-36">
                    <label class="label-text" for="start_date">From</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="input input-sm w-full">
                </div>
                <div class="w-36">
                    <label class="label-text" for="end_date">To</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="input input-sm w-full">
                </div>

                {{-- Type Filter --}}
                <div class="w-32">
                    <label class="label-text" for="type">Type</label>
                    <select id="type" name="type" class="select select-sm w-full">
                        <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="class" {{ ($filters['type'] ?? '') === 'class' ? 'selected' : '' }}>Classes</option>
                        <option value="service" {{ ($filters['type'] ?? '') === 'service' ? 'selected' : '' }}>Services</option>
                    </select>
                </div>

                {{-- Location Filter --}}
                <div class="w-40">
                    <label class="label-text" for="location_id">Location</label>
                    <select id="location_id" name="location_id" class="select select-sm w-full">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ ($filters['location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor Filter --}}
                <div class="w-40">
                    <label class="label-text" for="instructor_id">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="select select-sm w-full">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ ($filters['instructor_id'] ?? '') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--filter] size-4"></span>
                    Filter
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('schedule.list') }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="flex items-center gap-4 text-sm text-base-content/60">
        <span>
            <span class="font-semibold text-base-content">{{ $classSessions->count() }}</span> classes
        </span>
        <span class="text-base-content/30">·</span>
        <span>
            <span class="font-semibold text-base-content">{{ $serviceSlots->count() }}</span> services
        </span>
        <span class="text-base-content/30">·</span>
        <span>
            <span class="font-semibold text-base-content">{{ $classSessions->count() + $serviceSlots->count() }}</span> total
        </span>
    </div>

    {{-- Schedule Content --}}
    @if($scheduleByDate->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Schedule Found</h3>
                <p class="text-base-content/60 mb-4">
                    @if(!empty(array_filter($filters ?? [])))
                        No classes or services match your current filters.
                    @else
                        There are no classes or services scheduled for this period.
                    @endif
                </p>
                <div class="flex justify-center gap-2">
                    <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Class
                    </a>
                    <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Service
                    </a>
                </div>
            </div>
        </div>
    @else
        @foreach($scheduleByDate as $dateKey => $items)
            @php
                $date = \Carbon\Carbon::parse($dateKey);
                $isToday = $date->isToday();
            @endphp
            <div class="card bg-base-100" x-data="{ open: true }">
                {{-- Date Header --}}
                <div class="card-body p-4 border-b border-base-200 cursor-pointer" @click="open = !open">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center {{ $isToday ? 'bg-primary text-primary-content' : 'bg-base-200' }}">
                                <span class="text-xs uppercase {{ $isToday ? 'text-primary-content/70' : 'text-base-content/60' }}">{{ $date->format('D') }}</span>
                                <span class="text-lg font-bold">{{ $date->format('j') }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold {{ $isToday ? 'text-primary' : '' }}">
                                    {{ $date->format('l, F j, Y') }}
                                    @if($isToday)
                                        <span class="badge badge-primary badge-sm ml-2">Today</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-base-content/60">{{ $items->count() }} {{ Str::plural('item', $items->count()) }}</p>
                            </div>
                        </div>
                        <button class="btn btn-ghost btn-sm btn-circle">
                            <span class="icon-[tabler--chevron-down] size-5 transition-transform" :class="{ 'rotate-180': !open }"></span>
                        </button>
                    </div>
                </div>

                {{-- Schedule Items --}}
                <div class="overflow-x-auto" x-show="open" x-collapse>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-24">Time</th>
                                <th class="w-20">Type</th>
                                <th>Name</th>
                                <th>Instructor</th>
                                <th>Location</th>
                                <th class="text-center">Capacity</th>
                                <th>Status</th>
                                <th class="w-28">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $entry)
                                @if($entry['type'] === 'class')
                                    @php $session = $entry['item']; @endphp
                                    <tr class="hover:bg-base-200/50">
                                        <td>
                                            <div class="font-medium">{{ $session->start_time->format('g:i A') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $session->formatted_duration }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary badge-sm">
                                                <span class="icon-[tabler--yoga] size-3 mr-1"></span>
                                                Class
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $session->display_title }}</div>
                                            @if($session->classPlan)
                                                <div class="text-xs text-base-content/60">{{ $session->classPlan->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                @if($session->primaryInstructor)
                                                    <x-avatar
                                                        :src="$session->primaryInstructor->photo_url ?? null"
                                                        :initials="$session->primaryInstructor->initials ?? '?'"
                                                        size="xs"
                                                    />
                                                    <span class="text-sm">{{ $session->primaryInstructor->name }}</span>
                                                @else
                                                    <span class="text-base-content/40">TBD</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm">{{ $session->location?->name ?? 'TBD' }}</div>
                                            @if($session->room)
                                                <div class="text-xs text-base-content/60">{{ $session->room->name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="text-sm font-medium">
                                                {{ $session->confirmedBookings->count() }}/{{ $session->getEffectiveCapacity() }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $session->getStatusBadgeClass() }} badge-sm">
                                                {{ \App\Models\ClassSession::getStatuses()[$session->status] ?? $session->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="View" onclick="openDrawer('class-session-{{ $session->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                                <a href="{{ route('class-sessions.edit', $session) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                </a>
                                                <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs btn-square" title="Details">
                                                    <span class="icon-[tabler--external-link] size-4"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @php $slot = $entry['item']; @endphp
                                    <tr class="hover:bg-base-200/50">
                                        <td>
                                            <div class="font-medium">{{ $slot->start_time->format('g:i A') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $slot->duration_minutes }} min</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success badge-sm">
                                                <span class="icon-[tabler--massage] size-3 mr-1"></span>
                                                Service
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $slot->servicePlan?->name ?? 'Service' }}</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                @if($slot->instructor)
                                                    <x-avatar
                                                        :src="$slot->instructor->photo_url ?? null"
                                                        :initials="$slot->instructor->initials ?? '?'"
                                                        size="xs"
                                                    />
                                                    <span class="text-sm">{{ $slot->instructor->name }}</span>
                                                @else
                                                    <span class="text-base-content/40">TBD</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm">{{ $slot->location?->name ?? 'TBD' }}</div>
                                            @if($slot->room)
                                                <div class="text-xs text-base-content/60">{{ $slot->room->name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($slot->status === \App\Models\ServiceSlot::STATUS_BOOKED)
                                                <span class="text-sm font-medium">1/1</span>
                                            @else
                                                <span class="text-sm text-base-content/40">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $slot->getStatusBadgeClass() }} badge-sm">
                                                {{ \App\Models\ServiceSlot::getStatuses()[$slot->status] ?? $slot->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="View" onclick="openDrawer('service-slot-{{ $slot->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                                <a href="{{ route('service-slots.edit', $slot) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                </a>
                                                <a href="{{ route('service-slots.show', $slot) }}" class="btn btn-ghost btn-xs btn-square" title="Details">
                                                    <span class="icon-[tabler--external-link] size-4"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>

{{-- Class Session Drawers --}}
@foreach($classSessions as $session)
    @include('host.schedule.partials.class-session-drawer', ['classSession' => $session])
@endforeach

{{-- Service Slot Drawers --}}
@foreach($serviceSlots as $slot)
    @include('host.schedule.partials.service-slot-drawer', ['serviceSlot' => $slot])
@endforeach
@endsection
