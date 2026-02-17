@extends('layouts.dashboard')

@section('title', 'Class Sessions')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Classes</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Class Sessions</h1>
            <p class="text-base-content/60 mt-1">{{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}</p>
        </div>
        <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Schedule Class
        </a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form action="{{ route('class-sessions.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                {{-- Date Range --}}
                <div class="w-36">
                    <label class="label-text" for="date">Week of</label>
                    <input type="date" id="date" name="date" value="{{ $date }}"
                           class="input input-sm w-full">
                </div>

                {{-- Class Plan Filter --}}
                <div class="w-40">
                    <label class="label-text" for="class_plan_id">Class</label>
                    <select id="class_plan_id" name="class_plan_id" class="select select-sm w-full">
                        <option value="">All Classes</option>
                        @foreach($classPlans as $plan)
                            <option value="{{ $plan->id }}" {{ $classPlanId == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
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
                            <option value="{{ $instructor->id }}" {{ $instructorId == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="w-32">
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select select-sm w-full">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--filter] size-4"></span>
                    Filter
                </button>
                @if($classPlanId || $instructorId || $status)
                    <a href="{{ route('class-sessions.index', ['date' => $date]) }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Week Navigation --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--chevron-left] size-5"></span>
            Previous Week
        </a>
        <div class="flex items-center gap-4 text-sm text-base-content/60">
            <span>
                <span class="font-semibold text-base-content">{{ $sessions->count() }}</span> classes
            </span>
        </div>
        <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            Next Week
            <span class="icon-[tabler--chevron-right] size-5"></span>
        </a>
    </div>

    {{-- Sessions List Grouped by Date --}}
    @if($sessionsByDate->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Sessions Found</h3>
                <p class="text-base-content/60 mb-4">
                    @if($classPlanId || $instructorId || $status)
                        No class sessions match your current filters.
                    @else
                        No class sessions scheduled for this week.
                    @endif
                </p>
                <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Schedule Your First Class
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($sessionsByDate as $dateKey => $daySessions)
                @php
                    $dateObj = \Carbon\Carbon::parse($dateKey);
                    $isToday = $dateObj->isToday();
                    $isPast = $dateObj->lt(\Carbon\Carbon::today());
                    $isOpen = !$isPast;
                    $accordionId = 'day-' . $dateObj->format('Y-m-d');
                @endphp
                <div class="accordion" id="accordion-{{ $accordionId }}" data-accordion-always-open>
                <div class="accordion-item card bg-base-100 {{ !$isPast ? 'active' : '' }}" id="{{ $accordionId }}">
                    {{-- Date Header --}}
                    <button class="accordion-toggle w-full p-4 text-start {{ !$isPast ? 'open' : '' }}" aria-controls="{{ $accordionId }}-content" aria-expanded="{{ !$isPast ? 'true' : 'false' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center {{ $isToday ? 'bg-primary text-primary-content' : 'bg-base-200' }}">
                                    <span class="text-xs uppercase {{ $isToday ? 'text-primary-content/70' : 'text-base-content/60' }}">{{ $dateObj->format('D') }}</span>
                                    <span class="text-lg font-bold">{{ $dateObj->format('j') }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold {{ $isToday ? 'text-primary' : '' }}">
                                        {{ $dateObj->format('l, F j, Y') }}
                                        @if($isToday)
                                            <span class="badge badge-primary badge-sm ml-2">Today</span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-base-content/60">{{ $daySessions->count() }} {{ Str::plural('class', $daySessions->count()) }}</p>
                                </div>
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/60 transition-transform duration-300" style="{{ !$isPast ? 'transform: rotate(180deg);' : '' }}"></span>
                        </div>
                    </button>

                    {{-- Sessions Table --}}
                    <div id="{{ $accordionId }}-content" class="accordion-content w-full overflow-hidden transition-[height] duration-300" role="region" aria-labelledby="{{ $accordionId }}" style="{{ $isPast ? 'display: none;' : '' }}">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="w-24">Time</th>
                                        <th>Class</th>
                                        <th>Instructor</th>
                                        <th>Location</th>
                                        <th class="text-center">Capacity</th>
                                        <th>Status</th>
                                        <th class="w-32">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daySessions as $session)
                                        <tr class="hover:bg-base-200/50">
                                            <td>
                                                <div class="font-medium">{{ $session->start_time->format('g:i A') }}</div>
                                                <div class="text-xs text-base-content/60">{{ $session->formatted_duration }}</div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    @if($session->classPlan)
                                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $session->classPlan->color }};"></div>
                                                    @endif
                                                    <div>
                                                        <div class="font-medium">{{ $session->display_title }}</div>
                                                        @if($session->classPlan)
                                                            <div class="text-xs text-base-content/60">{{ $session->classPlan->name }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    @if($session->primaryInstructor)
                                                        <x-avatar
                                                            :src="$session->primaryInstructor->photo_url ?? null"
                                                            :initials="$session->primaryInstructor->initials ?? '?'"
                                                            size="xs"
                                                        />
                                                        <div>
                                                            <span class="text-sm">{{ $session->primaryInstructor->name }}</span>
                                                            @if($session->backupInstructors && $session->backupInstructors->isNotEmpty())
                                                                <div class="text-xs text-base-content/60">
                                                                    +{{ $session->backupInstructors->count() }} backup{{ $session->backupInstructors->count() > 1 ? 's' : '' }}
                                                                </div>
                                                            @endif
                                                        </div>
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
                                                    {{ $session->confirmedBookings ? $session->confirmedBookings->count() : 0 }}/{{ $session->getEffectiveCapacity() }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $session->getStatusBadgeClass() }} badge-sm">
                                                    {{ $statuses[$session->status] ?? $session->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    @if($session->isPublished() && !$session->isPast())
                                                        <a href="{{ route('walk-in.class', $session) }}"
                                                           class="btn btn-ghost btn-xs btn-square text-primary"
                                                           title="Walk-in Booking">
                                                            <span class="icon-[tabler--walk] size-4"></span>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                    </a>
                                                    <a href="{{ route('class-sessions.edit', $session) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                                        <span class="icon-[tabler--edit] size-4"></span>
                                                    </a>
                                                    @if($session->isDraft())
                                                        <form action="{{ route('class-sessions.publish', $session) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Publish">
                                                                <span class="icon-[tabler--send] size-4"></span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <details class="dropdown dropdown-bottom dropdown-end">
                                                        <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                                                        </summary>
                                                        <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                                                            <li>
                                                                <form action="{{ route('class-sessions.duplicate', $session) }}" method="POST" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="w-full text-left flex items-center gap-2">
                                                                        <span class="icon-[tabler--copy] size-4"></span> Duplicate
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @if($session->isPublished())
                                                                <li>
                                                                    <form action="{{ route('class-sessions.unpublish', $session) }}" method="POST" class="m-0">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="w-full text-left flex items-center gap-2">
                                                                            <span class="icon-[tabler--eye-off] size-4"></span> Unpublish
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                            @if(!$session->isCancelled())
                                                                <li>
                                                                    <form action="{{ route('class-sessions.cancel', $session) }}" method="POST" class="m-0" onsubmit="return confirm('Cancel this session?')">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                                            <span class="icon-[tabler--x] size-4"></span> Cancel
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                            @if(!$session->isPublished())
                                                                <li>
                                                                    <form action="{{ route('class-sessions.destroy', $session) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this session?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                                            <span class="icon-[tabler--trash] size-4"></span> Delete
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </details>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
