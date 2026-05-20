@extends('layouts.dashboard')

@section('title', $classSession->title ?? 'Schedule Planner')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule-planner.index') }}"><span class="icon-[tabler--calendar-repeat] me-1 size-4"></span> Schedule Planner</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ Str::limit($classSession->title, 30) }}</li>
    </ol>
@endsection

@php
    $membershipPlan = $classSession->membershipPlans->first();
    $planColor = $classSession->classPlan?->color ?? $membershipPlan?->color ?? '#6366f1';
@endphp

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $planColor }}20;">
                <span class="icon-[tabler--calendar-repeat] size-8" style="color: {{ $planColor }};"></span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $classSession->title ?? 'Untitled Planner' }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft capitalize">{{ $classSession->status }}</span>
                    @if($plannerType === 'membership')
                        <span class="badge badge-soft badge-warning badge-sm">Membership</span>
                        @if($membershipPlan)
                            <span class="badge badge-soft badge-secondary badge-sm">{{ $membershipPlan->name }}</span>
                        @endif
                    @else
                        <span class="badge badge-soft badge-primary badge-sm">Class</span>
                        @if($classSession->classPlan)
                            <span class="badge badge-soft badge-secondary badge-sm">{{ $classSession->classPlan->name }}</span>
                        @endif
                    @endif
                    @if($classSession->isRecurring())
                        <span class="badge badge-soft badge-info badge-sm">Recurring</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->hasPermission('schedule.edit'))
                @if($plannerType === 'membership')
                    <a href="{{ route('scheduled-membership.edit', $classSession) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span> Edit
                    </a>
                @else
                    <a href="{{ route('class-sessions.edit', $classSession) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span> Edit
                    </a>
                @endif
            @endif
            <a href="{{ route('schedule-planner.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2"><span class="icon-[tabler--calendar-stats] size-6 text-primary"></span></div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalSessions }}</p>
                        <p class="text-xs text-base-content/60">Total Sessions</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2"><span class="icon-[tabler--calendar-check] size-6 text-success"></span></div>
                    <div>
                        <p class="text-2xl font-bold">{{ $upcomingSessions }}</p>
                        <p class="text-xs text-base-content/60">Upcoming</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2"><span class="icon-[tabler--circle-check] size-6 text-info"></span></div>
                    <div>
                        <p class="text-2xl font-bold">{{ $completedSessions }}</p>
                        <p class="text-xs text-base-content/60">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    @if($conflictSessionIds->count() > 0)
                        <div class="bg-error/10 rounded-lg p-2"><span class="icon-[tabler--alert-triangle] size-6 text-error"></span></div>
                        <div>
                            <p class="text-2xl font-bold text-error">{{ $conflictSessionIds->count() }}</p>
                            <p class="text-xs text-base-content/60">Conflicts</p>
                        </div>
                    @else
                        <div class="bg-warning/10 rounded-lg p-2"><span class="icon-[tabler--repeat] size-6 text-warning"></span></div>
                        <div>
                            <p class="text-2xl font-bold">{{ count($recurringDays) }}</p>
                            <p class="text-xs text-base-content/60">Days/Week</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- All Sessions --}}
    @if($classSession->recurrenceChildren->isNotEmpty())
    <div class="card bg-base-100">
        <div class="card-header">
            <div class="flex items-center gap-2 border-b border-base-200 pb-3">
                <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                <h3 class="card-title">All Sessions <span class="badge badge-primary badge-sm ml-1">{{ $totalSessions }}</span></h3>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="max-h-72 overflow-y-auto">
                <table class="table table-sm">
                    <thead class="sticky top-0 bg-base-100 z-10">
                        <tr>
                            <th class="w-12">#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Instructor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-base-content/50">1</td>
                            <td class="font-medium">
                                {{ $classSession->start_time->format('D, M j, Y') }}
                                @if($conflictSessionIds->contains($classSession->id))
                                    <span class="icon-[tabler--alert-triangle] size-3.5 text-error ml-1 conflict-icon"
                                        onmouseenter="showConflictAlert(this)"
                                        onmouseleave="hideConflictAlert()"
                                        data-message="{{ $classSession->primaryInstructor?->name ?? 'Instructor' }} is not available on {{ $classSession->start_time->format('l, M j') }}"></span>
                                @endif
                            </td>
                            <td>{{ $classSession->start_time->format('g:i A') }} - {{ $classSession->end_time->format('g:i A') }}</td>
                            <td class="text-sm">{{ $classSession->primaryInstructor?->name ?? '-' }}</td>
                            <td><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-xs capitalize">{{ $classSession->status }}</span></td>
                            <td>
                                @if($plannerType === 'membership')
                                    <a href="{{ route('scheduled-membership.show', $classSession) }}" class="btn btn-ghost btn-xs">View</a>
                                @else
                                    <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-ghost btn-xs">View</a>
                                @endif
                            </td>
                        </tr>
                        @foreach($classSession->recurrenceChildren as $index => $child)
                        <tr>
                            <td class="text-base-content/50">{{ $index + 2 }}</td>
                            <td>
                                {{ $child->start_time->format('D, M j, Y') }}
                                @if($conflictSessionIds->contains($child->id))
                                    <span class="icon-[tabler--alert-triangle] size-3.5 text-error ml-1 conflict-icon"
                                        onmouseenter="showConflictAlert(this)"
                                        onmouseleave="hideConflictAlert()"
                                        data-message="{{ $child->primaryInstructor?->name ?? 'Instructor' }} is not available on {{ $child->start_time->format('l, M j') }}"></span>
                                @endif
                            </td>
                            <td>{{ $child->start_time->format('g:i A') }} - {{ $child->end_time->format('g:i A') }}</td>
                            <td class="text-sm">{{ $child->primaryInstructor?->name ?? '-' }}</td>
                            <td><span class="badge {{ $child->getStatusBadgeClass() }} badge-soft badge-xs capitalize">{{ $child->status }}</span></td>
                            <td>
                                @if($plannerType === 'membership')
                                    <a href="{{ route('scheduled-membership.show', $child) }}" class="btn btn-ghost btn-xs">View</a>
                                @else
                                    <a href="{{ route('class-sessions.show', $child) }}" class="btn btn-ghost btn-xs">View</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="border-b border-base-200"></div>
    </div>
    @endif

    {{-- Planner Details --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--info-circle] size-5"></span>
                Planner Details
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <label class="text-sm text-base-content/60">Type</label>
                    <p class="font-medium capitalize">{{ $plannerType }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">{{ $plannerType === 'membership' ? 'Membership Plan' : 'Class Plan' }}</label>
                    <p class="font-medium">{{ $plannerType === 'membership' ? ($membershipPlan?->name ?? '-') : ($classSession->classPlan?->name ?? '-') }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Time</label>
                    <p class="font-medium">{{ $classSession->start_time->format('g:i A') }} - {{ $classSession->end_time->format('g:i A') }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Duration</label>
                    <p class="font-medium">{{ $classSession->duration_minutes }} min</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Capacity</label>
                    <p class="font-medium">{{ $classSession->capacity ?? 'Unlimited' }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Location</label>
                    <p class="font-medium">{{ $classSession->location?->name ?? '-' }}</p>
                </div>
                @if($classSession->room)
                <div>
                    <label class="text-sm text-base-content/60">Room</label>
                    <p class="font-medium">{{ $classSession->room->name }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm text-base-content/60">Status</label>
                    <p class="mt-0.5"><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classSession->status }}</span></p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Start Date</label>
                    <p class="font-medium">{{ $classSession->start_time->format('M j, Y') }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">End Date</label>
                    <p class="font-medium text-base-content/40 italic">No end date</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Created</label>
                    <p class="font-medium">{{ $classSession->created_at->format('M j, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recurring Schedule --}}
    @if(!empty($recurringDays))
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--repeat] size-5"></span>
                Recurring Schedule
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="col-span-2">
                    <label class="text-sm text-base-content/60">Recurring Days</label>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach($recurringDays as $day)
                            <span class="badge badge-soft badge-primary badge-sm">{{ $day }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Last Session</label>
                    <p class="font-medium">
                        @if($classSession->recurrenceChildren->isNotEmpty())
                            {{ $classSession->recurrenceChildren->last()->start_time->format('M j, Y') }}
                        @else
                            {{ $classSession->start_time->format('M j, Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Instructor --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--users] size-5"></span>
                Assigned Instructor
            </h2>
            @if($classSession->primaryInstructor)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                <div class="flex items-center gap-3 p-3 bg-primary/5 border border-primary/20 rounded-xl">
                    @php $pi = $classSession->primaryInstructor; @endphp
                    @if($pi->photo_url)
                        <img src="{{ $pi->photo_url }}" alt="{{ $pi->name }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center font-bold text-sm text-primary">
                            {{ strtoupper(substr($pi->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-medium text-sm">{{ $pi->name }}</div>
                        <span class="badge badge-soft badge-primary badge-xs">Primary</span>
                    </div>
                </div>
                @foreach($classSession->backupInstructors as $index => $backup)
                <div class="flex items-center gap-3 p-3 bg-base-200/50 border border-base-300 rounded-xl">
                    @if($backup->photo_url)
                        <img src="{{ $backup->photo_url }}" alt="{{ $backup->name }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center font-bold text-sm text-secondary">
                            {{ strtoupper(substr($backup->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-medium text-sm">{{ $backup->name }}</div>
                        <span class="badge badge-soft badge-neutral badge-xs">Backup #{{ $index + 1 }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="mt-4 text-base-content/40 italic text-sm">No instructor assigned.</p>
            @endif
        </div>
    </div>

    {{-- Notes --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--notes] size-5"></span>
                Internal Notes
            </h2>
            @if($classSession->notes)
                <p class="mt-2 whitespace-pre-line">{{ $classSession->notes }}</p>
            @else
                <p class="mt-2 text-base-content/40 italic">No notes added.</p>
            @endif
        </div>
    </div>

</div>

{{-- Conflict Detail Popover --}}
<div id="conflict-popover" class="fixed z-50 hidden">
    <div class="bg-error text-error-content rounded-lg shadow-lg px-4 py-3 max-w-xs">
        <div class="flex items-start gap-2">
            <span class="icon-[tabler--alert-triangle] size-4 mt-0.5 shrink-0"></span>
            <p class="text-sm" id="conflict-popover-text"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
var conflictPopover = document.getElementById('conflict-popover');
var conflictPopoverText = document.getElementById('conflict-popover-text');

function showConflictAlert(el) {
    conflictPopoverText.textContent = el.dataset.message;

    var rect = el.getBoundingClientRect();
    conflictPopover.style.top = (rect.bottom + 6) + 'px';
    conflictPopover.style.left = rect.left + 'px';
    conflictPopover.classList.remove('hidden');
}

function hideConflictAlert() {
    conflictPopover.classList.add('hidden');
}
</script>
@endpush
@endsection
