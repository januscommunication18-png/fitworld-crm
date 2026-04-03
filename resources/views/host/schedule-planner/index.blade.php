@extends('layouts.dashboard')

@section('title', 'Schedule Planner')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Schedule Planner</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Schedule Planner</h1>
            <p class="text-base-content/60 mt-1">View and manage recurring schedules</p>
        </div>
        @if($type === 'membership')
            <a href="{{ route('scheduled-membership.create') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4"></span> Create Membership Session
            </a>
        @elseif($type === 'service')
            <a href="{{ route('service-slots.create') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4"></span> Create Service Slot
            </a>
        @else
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4"></span> Create Session
            </a>
        @endif
    </div>

    {{-- Type Toggle & Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                {{-- Type Toggle --}}
                <div class="flex rounded-lg border border-base-300 overflow-hidden">
                    <a href="{{ route('schedule-planner.index', ['type' => 'class', 'class_plan_id' => $type === 'class' ? $selectedPlanId : null]) }}"
                       class="px-4 py-1.5 text-sm font-medium flex items-center gap-2 transition-colors {{ $type === 'class' ? 'bg-primary text-primary-content' : 'hover:bg-base-200' }}">
                        <span class="icon-[tabler--yoga] size-4"></span>
                        Classes
                    </a>
                    <a href="{{ route('schedule-planner.index', ['type' => 'service', 'service_plan_id' => $type === 'service' ? $selectedPlanId : null]) }}"
                       class="px-4 py-1.5 text-sm font-medium flex items-center gap-2 transition-colors {{ $type === 'service' ? 'bg-primary text-primary-content' : 'hover:bg-base-200' }}">
                        <span class="icon-[tabler--massage] size-4"></span>
                        Services
                    </a>
                    <a href="{{ route('schedule-planner.index', ['type' => 'membership', 'membership_plan_id' => $type === 'membership' ? $selectedPlanId : null]) }}"
                       class="px-4 py-1.5 text-sm font-medium flex items-center gap-2 transition-colors {{ $type === 'membership' ? 'bg-primary text-primary-content' : 'hover:bg-base-200' }}">
                        <span class="icon-[tabler--id-badge-2] size-4"></span>
                        Memberships
                    </a>
                </div>

                {{-- Plan Filter --}}
                <div class="form-control w-64">
                    @if($type === 'membership')
                        <select id="plan-filter" class="select select-bordered select-sm"
                                onchange="window.location.href='/schedule-planner?type=membership&membership_plan_id=' + this.value">
                            @foreach($membershipPlans as $plan)
                                <option value="{{ $plan->id }}" {{ $selectedPlanId == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                            @if($membershipPlans->isEmpty())
                                <option value="" disabled selected>No active membership plans</option>
                            @endif
                        </select>
                    @elseif($type === 'service')
                        <select id="plan-filter" class="select select-bordered select-sm"
                                onchange="window.location.href='/schedule-planner?type=service&service_plan_id=' + this.value">
                            @foreach($servicePlans as $plan)
                                <option value="{{ $plan->id }}" {{ $selectedPlanId == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                            @if($servicePlans->isEmpty())
                                <option value="" disabled selected>No active service plans</option>
                            @endif
                        </select>
                    @else
                        <select id="plan-filter" class="select select-bordered select-sm"
                                onchange="window.location.href='/schedule-planner?type=class&class_plan_id=' + this.value">
                            @foreach($classPlans as $plan)
                                <option value="{{ $plan->id }}" {{ $selectedPlanId == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                            @if($classPlans->isEmpty())
                                <option value="" disabled selected>No active class plans</option>
                            @endif
                        </select>
                    @endif
                </div>

                @if($selectedPlanId)
                    <span class="text-sm text-base-content/60">{{ $schedules->count() }} {{ Str::plural('schedule', $schedules->count()) }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Schedules List --}}
    @if($schedules->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Schedules Found</h3>
                @if($type === 'membership')
                    <p class="text-base-content/60 mb-4">No recurring or upcoming sessions found for this membership plan.</p>
                    <a href="{{ route('scheduled-membership.create') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Create First Session
                    </a>
                @elseif($type === 'service')
                    <p class="text-base-content/60 mb-4">No recurring or upcoming service slots found for this service plan.</p>
                    <a href="{{ route('service-slots.create', ['service_plan_id' => $selectedPlanId]) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Create First Slot
                    </a>
                @else
                    <p class="text-base-content/60 mb-4">No recurring or upcoming sessions found for this class plan.</p>
                    <a href="{{ route('class-sessions.create', ['class_plan_id' => $selectedPlanId]) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Create First Session
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Custom Name</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Instructor</th>
                            <th>Location</th>
                            <th class="text-center">{{ $type === 'service' ? 'Slots' : 'Sessions' }}</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($schedule->is_recurring)
                                        <span class="icon-[tabler--calendar-repeat] size-4 text-primary" title="Recurring"></span>
                                    @else
                                        <span class="icon-[tabler--calendar-event] size-4 text-base-content/40" title="One-off"></span>
                                    @endif
                                    <span class="font-medium">{{ $schedule->title ?? 'Untitled' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(explode(', ', $schedule->days) as $day)
                                        <span class="badge badge-soft badge-sm badge-primary">{{ $day }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-sm">{{ $schedule->time }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--user] size-4 text-base-content/50"></span>
                                    <span class="text-sm">{{ $schedule->instructor }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--map-pin] size-4 text-base-content/50"></span>
                                    <span class="text-sm">{{ $schedule->location }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft badge-sm {{ $schedule->session_count > 0 ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $schedule->session_count }} upcoming
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if($type === 'service')
                                        <a href="{{ route('service-slots.show', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('service-slots.edit', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--pencil] size-4"></span>
                                        </a>
                                    @elseif($type === 'membership')
                                        <a href="{{ route('class-sessions.show', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('scheduled-membership.edit', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--pencil] size-4"></span>
                                        </a>
                                    @else
                                        <a href="{{ route('class-sessions.show', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('class-sessions.edit', $schedule->id) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--pencil] size-4"></span>
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
    @endif
</div>
@endsection
