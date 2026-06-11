@extends('layouts.dashboard')

@section('title', $classSession->display_title)

@php
    $isMembershipSession = !$classSession->class_plan_id;
    $linkedMembershipPlan = $classSession->membershipPlans->first();
@endphp

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        @if($isMembershipSession)
            <li><a href="{{ route('membership-schedules.index') }}"><span class="icon-[tabler--id-badge-2] me-1 size-4"></span> Membership Sessions</a></li>
        @else
            <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> {{ $trans['schedule.class_sessions'] ?? 'Class Sessions' }}</a></li>
        @endif
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $classSession->display_title }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @php
                $headerImage = $classSession->classPlan?->image_url ?? $classSession->membershipPlans->first()?->image_url ?? null;
                $headerColor = $classSession->classPlan?->color ?? $classSession->membershipPlans->first()?->color ?? '#6366f1';
            @endphp
            @if($headerImage)
                <img src="{{ $headerImage }}" alt="{{ $classSession->display_title }}" class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $headerColor }}20;">
                    <span class="icon-[tabler--{{ $isMembershipSession ? 'id-badge-2' : 'calendar-event' }}] size-10" style="color: {{ $headerColor }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $classSession->display_title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft capitalize">{{ $classSession->status }}</span>
                    @if($isMembershipSession)
                        <span class="badge badge-soft badge-warning badge-sm">Membership Session</span>
                        @if($linkedMembershipPlan)
                            <span class="badge badge-soft badge-secondary badge-sm">{{ $linkedMembershipPlan->name }}</span>
                        @endif
                    @endif
                    @if($classSession->classPlan?->category)
                        <span class="badge badge-soft badge-primary badge-sm capitalize">{{ $classSession->classPlan->category }}</span>
                    @endif
                    @if($classSession->classPlan?->difficulty_level)
                        <span class="badge {{ $classSession->classPlan->getDifficultyBadgeClass() }} badge-soft badge-sm capitalize">{{ str_replace('_', ' ', $classSession->classPlan->difficulty_level) }}</span>
                    @endif
                    @if($classSession->isRecurring())
                        <span class="badge badge-soft badge-info badge-sm">{{ $trans['schedule.recurring'] ?? 'Recurring' }}</span>
                    @endif
                    @if($classSession->hasUnresolvedConflict())
                        <span class="badge badge-error badge-soft badge-sm gap-1">
                            <span class="icon-[tabler--alert-triangle] size-3"></span>
                            {{ $trans['schedule.has_scheduling_conflict'] ?? 'Conflict' }}
                        </span>
                    @endif
                </div>
                <p class="text-base-content/60 mt-1 text-sm">{{ $classSession->formatted_date }} &bull; {{ $classSession->formatted_time_range }} &bull; {{ $classSession->formatted_duration }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($classSession->isPublished() && !$classSession->isPast() && auth()->user()->hasPermission('bookings.create'))
                @if($classSession->membershipPlans->isNotEmpty())
                    <a href="{{ route('walk-in.select-membership', ['class_session_id' => $classSession->id]) }}" class="btn btn-warning btn-sm">
                        <span class="icon-[tabler--id-badge-2] size-4"></span>
                        {{ $trans['schedule.add_booking'] ?? 'Add Booking' }}
                    </a>
                @else
                    <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        {{ $trans['schedule.add_booking'] ?? 'Add Booking' }}
                    </a>
                @endif
            @endif
            @if(isset($progressTemplates) && $progressTemplates->count() > 0 && $confirmedBookings->count() > 0)
                <details class="dropdown dropdown-bottom dropdown-end">
                    <summary class="btn btn-primary btn-soft btn-sm list-none cursor-pointer">
                        <span class="icon-[tabler--chart-line] size-4"></span>
                        Record Progress
                        <span class="icon-[tabler--chevron-down] size-3"></span>
                    </summary>
                    <ul class="dropdown-content menu bg-base-100 rounded-box w-56 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                        @foreach($progressTemplates as $template)
                            <li><a href="{{ route('class-sessions.record-progress', [$classSession, $template]) }}">
                                <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-4 text-primary"></span>
                                {{ $template->name }}
                            </a></li>
                        @endforeach
                    </ul>
                </details>
            @endif
            @if($classSession->isDraft() && auth()->user()->hasPermission('schedule.publish'))
                <form action="{{ route('class-sessions.publish', $classSession) }}" method="POST" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--send] size-4"></span> {{ $trans['btn.publish'] ?? 'Publish' }}
                    </button>
                </form>
            @endif
            @if(auth()->user()->hasPermission('schedule.edit'))
                @if($classSession->class_plan_id)
                    <a href="{{ route('class-sessions.edit', $classSession) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                    </a>
                @else
                    <a href="{{ route('scheduled-membership.edit', $classSession) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                    </a>
                @endif
            @endif
            <a href="{{ $isMembershipSession ? route('membership-schedules.index') : route('class-sessions.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back
            </a>
        </div>
    </div>

    {{-- Conflict Alert --}}
    @if($classSession->hasUnresolvedConflict())
    <div class="alert alert-error shadow-lg flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="icon-[tabler--alert-triangle] size-6 shrink-0"></span>
            <div>
                <h3 class="font-bold">{{ $trans['schedule.scheduling_conflict'] ?? 'Scheduling Conflict' }}</h3>
                <p class="text-sm">{{ $classSession->conflict_notes ?? ($trans['schedule.conflict_needs_resolved'] ?? 'This session has a conflict that needs to be resolved.') }}</p>
            </div>
        </div>
        <button type="button" onclick="openConflictDrawer('conflict-drawer-{{ $classSession->id }}')" class="btn btn-sm btn-outline ml-auto shrink-0 !text-white !border-white hover:!bg-white hover:!text-error">
            {{ $trans['schedule.view_conflict_details'] ?? 'View Conflict Details' }}
        </button>
    </div>
    @elseif(!empty($dynamicConflict))
    <div class="alert alert-error shadow-lg flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="icon-[tabler--alert-triangle] size-6 shrink-0"></span>
            <div>
                <h3 class="font-bold">{{ $trans['schedule.scheduling_conflict'] ?? 'Scheduling Conflict' }}</h3>
                <p class="text-sm">{{ $dynamicConflictMessage }}</p>
            </div>
        </div>
        <button type="button" onclick="openConflictDrawer('conflict-drawer-{{ $classSession->id }}')" class="btn btn-sm btn-outline ml-auto shrink-0 !text-white !border-white hover:!bg-white hover:!text-error">
            {{ $trans['schedule.view_conflict_details'] ?? 'View Conflict Details' }}
        </button>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab tab-active" data-tab="bookings" role="tab">
            <span class="icon-[tabler--users] size-4 mr-2"></span>Bookings
            @if($confirmedBookings->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $confirmedBookings->count() }}</span>
            @endif
        </button>
        <button class="tab" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>Overview
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content hidden" data-content="overview">
            <div class="space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/10 rounded-lg p-2">
                                <span class="icon-[tabler--users] size-6 text-primary"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $confirmedBookings->count() }}</p>
                                <p class="text-xs text-base-content/60">{{ $trans['schedule.booked'] ?? 'Booked' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-success/10 rounded-lg p-2">
                                <span class="icon-[tabler--user-check] size-6 text-success"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $checkedInCount }}</p>
                                <p class="text-xs text-base-content/60">{{ $trans['bookings.checked_in'] ?? 'Checked In' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-info/10 rounded-lg p-2">
                                <span class="icon-[tabler--file-check] size-6 text-info"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $intakeCompleted }}</p>
                                <p class="text-xs text-base-content/60">{{ $trans['schedule.intake_done'] ?? 'Intake Done' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-warning/10 rounded-lg p-2">
                                <span class="icon-[tabler--clock-pause] size-6 text-warning"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $intakePending }}</p>
                                <p class="text-xs text-base-content/60">{{ $trans['schedule.intake_pending'] ?? 'Intake Pending' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Session Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        {{ $trans['schedule.session_details'] ?? 'Session Details' }}
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['common.date'] ?? 'Date' }}</label>
                            <p class="font-medium">{{ $classSession->start_time->format('l, F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['common.time'] ?? 'Time' }}</label>
                            <p class="font-medium">{{ $classSession->formatted_time_range }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['field.duration'] ?? 'Duration' }}</label>
                            <p class="font-medium">{{ $classSession->formatted_duration }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['field.capacity'] ?? 'Capacity' }}</label>
                            <p class="font-medium">{{ $classSession->capacity }} {{ $trans['common.spots'] ?? 'spots' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $isMembershipSession ? 'Membership Plan' : ($trans['field.class_plan'] ?? 'Class Plan') }}</label>
                            <p class="font-medium">{{ $isMembershipSession ? ($linkedMembershipPlan?->name ?? '-') : ($classSession->classPlan?->name ?? '-') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['field.session_price'] ?? 'Session Price' }}</label>
                            <p class="font-medium">{{ $classSession->formatted_price }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['common.status'] ?? 'Status' }}</label>
                            <p class="mt-0.5"><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classSession->status }}</span></p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">{{ $trans['common.created'] ?? 'Created' }}</label>
                            <p class="font-medium">{{ $classSession->created_at->format('M j, Y') }}</p>
                        </div>
                        @if($classSession->isRecurring())
                        <div class="col-span-2">
                            <label class="text-sm text-base-content/60">Recurring Days</label>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @php
                                    $rule = $classSession->recurrence_rule;
                                    if ($classSession->isRecurrenceChild() && $classSession->recurrenceParent) {
                                        $rule = $classSession->recurrenceParent->recurrence_rule;
                                    }
                                    $parsedRule = is_string($rule) ? app(\App\Services\Schedule\RecurrenceService::class)->parseRecurrenceRule($rule) : null;
                                    $dayMap = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
                                    $recurringDayLabels = [];
                                    if ($parsedRule && !empty($parsedRule['days_of_week'])) {
                                        $recurringDayLabels = array_map(fn($d) => $dayMap[(int)$d] ?? $d, $parsedRule['days_of_week']);
                                    }
                                @endphp
                                @if(!empty($recurringDayLabels))
                                    @foreach($recurringDayLabels as $day)
                                        <span class="badge badge-soft badge-primary badge-sm">{{ $day }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-soft badge-sm">{{ $classSession->start_time->format('l') }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Assigned Staff & Instructors --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--users] size-5"></span>
                        Assigned Staff & Instructors
                    </h2>

                    @if(!$classSession->primaryInstructor && $classSession->backupInstructors->isEmpty())
                        <p class="mt-4 text-base-content/40 italic text-sm">No staff or instructors assigned.</p>
                    @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        @if($classSession->primaryInstructor)
                        @php
                            $pi = $classSession->primaryInstructor;
                            $piUser = $pi->user;
                        @endphp
                        <div class="flex items-center gap-3 p-3 bg-primary/5 border border-primary/20 rounded-xl">
                            @if($piUser?->profile_photo_url)
                                <img src="{{ $piUser->profile_photo_url }}" alt="{{ $pi->name }}" class="w-10 h-10 rounded-full object-cover">
                            @elseif($pi->photo_url)
                                <img src="{{ $pi->photo_url }}" alt="{{ $pi->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center font-bold text-sm text-primary">
                                    {{ strtoupper(substr($pi->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $pi->name }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="badge badge-soft badge-primary badge-xs">Primary</span>
                                    @if($piUser)
                                        <span class="text-xs text-base-content/50">{{ $piUser->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @foreach($classSession->backupInstructors as $index => $backup)
                        @php $backupUser = $backup->user; @endphp
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 border border-base-300 rounded-xl">
                            @if($backupUser?->profile_photo_url)
                                <img src="{{ $backupUser->profile_photo_url }}" alt="{{ $backup->name }}" class="w-10 h-10 rounded-full object-cover">
                            @elseif($backup->photo_url)
                                <img src="{{ $backup->photo_url }}" alt="{{ $backup->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center font-bold text-sm text-secondary">
                                    {{ strtoupper(substr($backup->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $backup->name }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="badge badge-soft badge-neutral badge-xs">Backup #{{ $index + 1 }}</span>
                                    @if($backupUser)
                                        <span class="text-xs text-base-content/50">{{ $backupUser->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Location --}}
            @if($classSession->location)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--map-pin] size-5"></span>
                        {{ $trans['field.location'] ?? 'Location' }}
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Venue</label>
                            <p class="font-medium">{{ $classSession->location->name }}</p>
                        </div>
                        @if($classSession->room)
                        <div>
                            <label class="text-sm text-base-content/60">Room</label>
                            <p class="font-medium">{{ $classSession->room->name }} ({{ $classSession->room->capacity }} cap.)</p>
                        </div>
                        @endif
                        @if($classSession->location->full_address && !$classSession->location->isVirtual())
                        <div class="col-span-2">
                            <label class="text-sm text-base-content/60">Address</label>
                            <p class="font-medium">{{ $classSession->location->full_address }}</p>
                        </div>
                        @endif
                        @if($classSession->location->isVirtual() && $classSession->location->virtual_platform)
                        <div>
                            <label class="text-sm text-base-content/60">Platform</label>
                            <p class="font-medium">{{ $classSession->location->virtual_platform_label }}</p>
                        </div>
                        @endif
                    </div>
                    @if($classSession->location_notes)
                    <div class="mt-4 p-3 bg-base-200/50 rounded-lg">
                        <label class="text-sm text-base-content/60 font-medium">{{ $trans['schedule.location_notes'] ?? 'Location Notes' }}</label>
                        <p class="text-sm mt-1 whitespace-pre-wrap">{{ $classSession->location_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Recurrence (this page shows only this single occurrence — link to planner for series) --}}
            @if($classSession->isRecurring())
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--repeat] size-5 text-primary"></span>
                            <div>
                                <h2 class="card-title text-base">{{ $trans['schedule.part_of_recurring_series'] ?? 'Part of a recurring series' }}</h2>
                                <p class="text-sm text-base-content/60">{{ $trans['schedule.viewing_single_occurrence'] ?? 'You are viewing this single occurrence. Use the planner to see the full series.' }}</p>
                            </div>
                        </div>
                        @php
                            $seriesAnchor = $classSession->isRecurrenceParent()
                                ? $classSession
                                : ($classSession->recurrenceParent ?? $classSession);
                        @endphp
                        <a href="{{ route('schedule-planner.show', $seriesAnchor) }}" class="btn btn-sm btn-outline btn-primary shrink-0">
                            <span class="icon-[tabler--calendar-event] size-4"></span>
                            {{ $trans['schedule.view_series'] ?? 'View Series' }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Cancellation Info --}}
            @if($classSession->isCancelled())
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="alert alert-error alert-soft">
                        <span class="icon-[tabler--x] size-5"></span>
                        <div>
                            <div class="font-medium">{{ $trans['schedule.cancelled_on'] ?? 'Cancelled on' }} {{ $classSession->cancelled_at->format('M j, Y') }}</div>
                            @if($classSession->cancellation_reason)
                                <p class="text-sm">{{ $classSession->cancellation_reason }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--notes] size-5"></span>
                        {{ $trans['schedule.internal_notes'] ?? 'Internal Notes' }}
                    </h2>
                    @if($classSession->notes)
                        <p class="mt-2 whitespace-pre-line">{{ $classSession->notes }}</p>
                    @else
                        <p class="mt-2 text-base-content/40 italic">No notes added.</p>
                    @endif
                </div>
            </div>

            </div>
        </div>

        {{-- Bookings Tab --}}
        <div class="tab-content active" data-content="bookings">
            <div class="space-y-6">

                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary/10 rounded-lg p-2">
                                    <span class="icon-[tabler--users] size-6 text-primary"></span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">{{ $confirmedBookings->count() }}</p>
                                    <p class="text-xs text-base-content/60">{{ $trans['schedule.booked'] ?? 'Booked' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-success/10 rounded-lg p-2">
                                    <span class="icon-[tabler--user-check] size-6 text-success"></span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">{{ $checkedInCount }}</p>
                                    <p class="text-xs text-base-content/60">{{ $trans['bookings.checked_in'] ?? 'Checked In' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-info/10 rounded-lg p-2">
                                    <span class="icon-[tabler--file-check] size-6 text-info"></span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">{{ $intakeCompleted }}</p>
                                    <p class="text-xs text-base-content/60">{{ $trans['schedule.intake_done'] ?? 'Intake Done' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-warning/10 rounded-lg p-2">
                                    <span class="icon-[tabler--clock-pause] size-6 text-warning"></span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">{{ $intakePending }}</p>
                                    <p class="text-xs text-base-content/60">{{ $trans['schedule.intake_pending'] ?? 'Intake Pending' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($allBookings->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--users-minus] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">{{ $trans['schedule.no_bookings_yet'] ?? 'No Bookings Yet' }}</h3>
                        <p class="text-base-content/60 mb-4">{{ $trans['schedule.no_one_booked'] ?? 'No one has booked this class session yet.' }}</p>
                        @if($classSession->isPublished() && !$classSession->isPast() && auth()->user()->hasPermission('bookings.create'))
                            <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--user-plus] size-4"></span>
                                {{ $trans['schedule.add_booking'] ?? 'Add Booking' }}
                            </a>
                        @endif
                    </div>
                </div>
                @else
                <div class="card bg-base-100">
                    <div class="card-body p-0">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ $trans['field.client'] ?? 'Client' }}</th>
                                        <th class="text-center">{{ $trans['common.status'] ?? 'Status' }}</th>
                                        <th class="text-center">{{ $trans['bookings.payment'] ?? 'Payment' }}</th>
                                        <th class="text-center">{{ $trans['schedule.intake'] ?? 'Intake' }}</th>
                                        <th class="text-center">{{ $trans['schedule.check_in'] ?? 'Check In' }}</th>
                                        <th>{{ $trans['schedule.booked'] ?? 'Booked' }}</th>
                                        <th class="w-28">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allBookings->sortByDesc('created_at') as $booking)
                                        @php
                                            $intakeStatuses = \App\Models\Booking::getIntakeStatuses();
                                            $intakeIcons = [
                                                'completed' => 'icon-[tabler--circle-check-filled] text-success',
                                                'pending' => 'icon-[tabler--clock] text-warning',
                                                'waived' => 'icon-[tabler--circle-minus] text-info',
                                                'not_required' => 'icon-[tabler--minus] text-base-content/30',
                                            ];
                                        @endphp
                                        <tr class="hover:bg-base-200/50 {{ $booking->status === 'cancelled' ? 'opacity-60' : '' }}">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    @if($booking->client)
                                                        <x-avatar :src="$booking->client->avatar_url ?? null" :initials="$booking->client->initials ?? '?'" :alt="$booking->client->full_name ?? 'Unknown'" size="sm" />
                                                        <div>
                                                            <a href="{{ route('clients.show', $booking->client) }}" class="font-medium hover:text-primary">{{ $booking->client->full_name }}</a>
                                                            @if($booking->client->email)
                                                                <div class="text-xs text-base-content/60">{{ $booking->client->email }}</div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-base-content/50">{{ $trans['bookings.unknown_client'] ?? 'Unknown Client' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center"><span class="badge {{ $booking->status_badge_class }} badge-sm">{{ \App\Models\Booking::getStatuses()[$booking->status] ?? $booking->status }}</span></td>
                                            <td class="text-center">
                                                @if($booking->price_paid !== null)
                                                    <div class="font-medium text-success">${{ number_format($booking->price_paid, 2) }}</div>
                                                    @if($booking->payment_method)
                                                        <div class="text-xs text-base-content/50 capitalize">{{ $booking->payment_method }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-base-content/40">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <span class="{{ $intakeIcons[$booking->intake_status] ?? 'icon-[tabler--minus] text-base-content/30' }} size-4"></span>
                                                    <span class="text-xs text-base-content/60">{{ $intakeStatuses[$booking->intake_status] ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center" id="checkin-cell-{{ $booking->id }}">
                                                @if($booking->status === 'cancelled')
                                                    <span class="text-base-content/30">-</span>
                                                @elseif($booking->isCheckedIn())
                                                    <div class="flex flex-col items-center justify-center gap-0.5 text-success">
                                                        <div class="flex items-center gap-1">
                                                            <span class="icon-[tabler--circle-check-filled] size-5"></span>
                                                            <span class="text-xs">{{ $booking->checked_in_at->format('g:i A') }}</span>
                                                        </div>
                                                        @if($booking->checked_in_method)
                                                            <span class="badge badge-soft badge-xs">{{ \App\Models\Booking::getCheckInMethods()[$booking->checked_in_method] ?? $booking->checked_in_method }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="icon-[tabler--circle-dashed] size-5 text-base-content/30"></span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-sm">{{ $booking->created_at->format('M j, Y') }}</div>
                                                <div class="text-xs text-base-content/60">{{ $booking->created_at->format('g:i A') }}</div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    @if($booking->status !== 'cancelled' && !$booking->isCheckedIn() && (auth()->user()->hasPermission('bookings.attendance') || auth()->user()->hasPermission('bookings.attendance_own')))
                                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-success hover:bg-success/10" id="checkin-btn-{{ $booking->id }}" onclick="checkInBooking({{ $booking->id }})" title="{{ $trans['schedule.check_in'] ?? 'Check In' }}">
                                                            <span class="icon-[tabler--login] size-4"></span>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="openDrawer('booking-{{ $booking->id }}', event)" title="{{ $trans['schedule.view_booking'] ?? 'View' }}">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                    </button>
                                                    @if($booking->client)
                                                        <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['bookings.view_client'] ?? 'Client' }}">
                                                            <span class="icon-[tabler--user] size-4"></span>
                                                        </a>
                                                    @endif
                                                    @if($booking->questionnaireResponses->where('status', 'completed')->isNotEmpty())
                                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-info hover:bg-info/10" onclick="openDrawer('intake-{{ $booking->id }}', event)" title="{{ $trans['schedule.view_intake_form'] ?? 'Intake' }}">
                                                            <span class="icon-[tabler--file-text] size-4"></span>
                                                        </button>
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
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mainTabs = document.querySelectorAll('.tabs.tabs-bordered .tab');
    var mainContents = document.querySelectorAll('.tab-content');

    mainTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var targetTab = this.dataset.tab;
            mainTabs.forEach(function(t) { t.classList.remove('tab-active'); });
            this.classList.add('tab-active');
            mainContents.forEach(function(content) {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        }.bind(tab));
    });
});

function openDrawer(id, event) {
    if (event) { event.preventDefault(); event.stopPropagation(); }
    var drawer = document.getElementById('drawer-' + id);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer) {
        document.querySelectorAll('[id^="drawer-"]').forEach(function(d) {
            if (d.id !== 'drawer-backdrop' && d.id !== 'drawer-' + id) d.classList.add('translate-x-full', 'hidden');
        });
        if (backdrop) backdrop.classList.remove('hidden');
        drawer.classList.remove('hidden');
        setTimeout(function() { drawer.classList.remove('translate-x-full'); }, 10);
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(id) {
    var drawer = document.getElementById('drawer-' + id);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer) { drawer.classList.add('translate-x-full'); setTimeout(function() { drawer.classList.add('hidden'); }, 300); }
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = '';
}

function closeAllDrawers() {
    document.querySelectorAll('[id^="drawer-"]').forEach(function(d) {
        if (d.id !== 'drawer-backdrop') { d.classList.add('translate-x-full'); setTimeout(function() { d.classList.add('hidden'); }, 300); }
    });
    var backdrop = document.getElementById('drawer-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAllDrawers(); });

function checkInBooking(bookingId) {
    var btn = document.getElementById('checkin-btn-' + bookingId);
    var checkinCell = document.getElementById('checkin-cell-' + bookingId);
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch('/schedule/check-in/' + bookingId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (checkinCell) checkinCell.innerHTML = '<div class="flex items-center justify-center gap-1 text-success"><span class="icon-[tabler--circle-check-filled] size-5"></span><span class="text-xs">' + data.checked_in_at + '</span></div>';
            btn.outerHTML = '<button type="button" class="btn btn-ghost btn-xs btn-square btn-disabled text-success" disabled><span class="icon-[tabler--check] size-4"></span></button>';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span>';
            alert(data.message || 'Failed to check in');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span>';
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAllDrawers()"></div>

{{-- Booking Drawers --}}
@foreach($allBookings as $booking)
    @include('host.bookings.partials.drawer', ['booking' => $booking])
@endforeach

{{-- Intake Form Drawers --}}
@foreach($allBookings as $booking)
    @if($booking->questionnaireResponses->where('status', 'completed')->isNotEmpty())
        <div id="drawer-intake-{{ $booking->id }}" class="fixed inset-y-0 right-0 w-full max-w-3xl bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden overflow-y-auto">
            <div class="sticky top-0 bg-base-100 border-b border-base-200 p-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-lg font-semibold">{{ $trans['schedule.intake_form_responses'] ?? 'Intake Form Responses' }}</h3>
                    <p class="text-sm text-base-content/60">{{ $booking->client?->full_name ?? ($trans['bookings.unknown_client'] ?? 'Unknown Client') }}</p>
                </div>
                <button type="button" onclick="closeDrawer('intake-{{ $booking->id }}')" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <div class="p-4 space-y-6">
                @foreach($booking->questionnaireResponses->where('status', 'completed') as $response)
                    <div class="card bg-base-200/50">
                        <div class="card-header py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                                <h4 class="font-semibold">{{ $response->version?->questionnaire?->name ?? 'Questionnaire' }}</h4>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-base-content/60">
                                <span class="badge badge-success badge-xs">Completed</span>
                                @if($response->completed_at)
                                    <span>{{ $response->completed_at->format('M j, Y g:i A') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body py-3 px-4">
                            <div class="space-y-4">
                                @foreach($response->answers as $answer)
                                    <div class="border-b border-base-300 pb-3 last:border-0 last:pb-0">
                                        <div class="text-sm font-medium text-base-content/70 mb-1">
                                            {{ $answer->question?->label ?? 'Question' }}
                                            @if($answer->question?->is_required)<span class="text-error">*</span>@endif
                                        </div>
                                        <div class="text-sm">
                                            @if($answer->answer)
                                                @if(in_array($answer->question?->type, ['checkbox', 'multi_select']))
                                                    @php $values = json_decode($answer->answer, true) ?? [$answer->answer]; @endphp
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach((array)$values as $value)<span class="badge badge-soft badge-sm">{{ $value }}</span>@endforeach
                                                    </div>
                                                @elseif(in_array($answer->question?->type, ['textarea', 'long_text']))
                                                    <p class="whitespace-pre-wrap text-base-content/80">{{ $answer->answer }}</p>
                                                @elseif($answer->question?->type === 'date')
                                                    {{ \Carbon\Carbon::parse($answer->answer)->format('M j, Y') }}
                                                @elseif($answer->question?->type === 'signature')
                                                    <img src="{{ $answer->answer }}" alt="Signature" class="max-w-xs border border-base-300 rounded bg-white p-2">
                                                @else
                                                    {{ $answer->answer }}
                                                @endif
                                            @else
                                                <span class="text-base-content/40 italic">{{ $trans['schedule.no_answer_provided'] ?? 'No answer provided' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endforeach

{{-- Cancel Modal --}}
@if(auth()->user()->hasPermission('schedule.cancel'))
<div id="cancel-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-sm">
        <div class="modal-content">
            <form action="{{ route('class-sessions.cancel', $classSession) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h3 class="modal-title">{{ $trans['schedule.cancel_session'] ?? 'Cancel Session' }}</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" data-overlay="#cancel-modal"><span class="icon-[tabler--x] size-4"></span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">{{ $trans['schedule.confirm_cancel_class'] ?? 'Are you sure you want to cancel this class session?' }}</p>
                    <div>
                        <label class="label-text" for="cancellation_reason">{{ $trans['schedule.reason_optional'] ?? 'Reason (optional)' }}</label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="textarea w-full" placeholder="{{ $trans['schedule.enter_reason'] ?? 'Enter a reason...' }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-overlay="#cancel-modal">{{ $trans['schedule.keep_session'] ?? 'Keep Session' }}</button>
                    <button type="submit" class="btn btn-error">{{ $trans['schedule.cancel_session'] ?? 'Cancel Session' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Conflict Resolution Drawer --}}
@if($classSession->hasUnresolvedConflict() || !empty($dynamicConflict))
    @include('host.class-sessions.partials.conflict-drawer', [
        'classSession' => $classSession,
        'conflictMessage' => $dynamicConflictMessage ?? $classSession->conflict_notes,
        'availableInstructors' => $availableInstructors,
    ])
@endif
@endsection
