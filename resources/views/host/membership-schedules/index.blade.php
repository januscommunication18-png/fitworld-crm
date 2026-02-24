@extends('layouts.dashboard')

@section('title', 'Membership Sessions')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Membership Sessions</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Membership Sessions</h1>
            <p class="text-base-content/60 mt-1">
                @if($range === 'today')
                    {{ $startDate->format('l, F j, Y') }}
                @elseif($range === 'month')
                    {{ $startDate->format('F Y') }}
                @elseif($range === 'all')
                    All upcoming sessions
                @else
                    {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('scheduled-membership.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Session
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form id="filter-form" action="{{ route('membership-schedules.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                {{-- Range Toggle --}}
                <div>
                    <label class="label-text">Range</label>
                    <div class="join">
                        <button type="button" class="btn btn-sm join-item {{ $range === 'today' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('today')">
                            Today
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'week' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('week')">
                            Week
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'month' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('month')">
                            Month
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'all' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('all')">
                            All
                        </button>
                    </div>
                    <input type="hidden" name="range" id="range-input" value="{{ $range }}">
                    <input type="hidden" name="date" id="date" value="{{ $date }}">
                </div>

                {{-- Membership Plan Filter --}}
                <div class="w-48">
                    <label class="label-text" for="membership_plan_id">Membership</label>
                    <select id="membership_plan_id" name="membership_plan_id" class="select select-sm w-full" onchange="submitFilters()">
                        <option value="">All Memberships</option>
                        @foreach($membershipPlans as $plan)
                            <option value="{{ $plan->id }}" {{ $membershipPlanId == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor Filter --}}
                <div class="w-40">
                    <label class="label-text" for="instructor_id">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="select select-sm w-full" onchange="submitFilters()">
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
                    <select id="status" name="status" class="select select-sm w-full" onchange="submitFilters()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($membershipPlanId || $instructorId || $status)
                    <a href="{{ route('membership-schedules.index', ['date' => $date, 'range' => $range]) }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex items-center justify-between">
        @if($range === 'all')
            <div></div>
        @elseif($range === 'today')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->subDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Day
            </a>
        @elseif($range === 'week')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Week
            </a>
        @elseif($range === 'month')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->subMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Month
            </a>
        @endif

        <div class="flex items-center gap-4 text-sm text-base-content/60">
            <span>
                <span class="font-semibold text-base-content">{{ $sessions->count() }}</span> {{ Str::plural('session', $sessions->count()) }}
            </span>
        </div>

        @if($range === 'all')
            <div></div>
        @elseif($range === 'today')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->addDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                Next Day
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'week')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                Next Week
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'month')
            <a href="{{ route('membership-schedules.index', array_merge(request()->query(), ['date' => $startDate->copy()->addMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                Next Month
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @endif
    </div>

    {{-- Sessions List --}}
    @if($sessions->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Sessions Found</h3>
                <p class="text-base-content/60 mb-4">
                    @if($membershipPlanId || $instructorId || $status)
                        No membership sessions match your current filters.
                    @else
                        @if($range === 'today')
                            No membership sessions scheduled for today.
                        @elseif($range === 'week')
                            No membership sessions scheduled for this week.
                        @elseif($range === 'month')
                            No membership sessions scheduled for this month.
                        @else
                            No upcoming membership sessions found.
                        @endif
                    @endif
                </p>
                <a href="{{ route('scheduled-membership.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Schedule First Session
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($sessionsByDate as $dateKey => $daySessions)
                @php
                    $dateObj = \Carbon\Carbon::parse($dateKey);
                    $isToday = $dateObj->isToday();
                @endphp
                <div class="card bg-base-100">
                    {{-- Date Header --}}
                    <div class="px-4 py-3 border-b border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center {{ $isToday ? 'bg-secondary text-secondary-content' : 'bg-base-200' }}">
                                <span class="text-xs uppercase {{ $isToday ? 'text-secondary-content/70' : 'text-base-content/60' }}">{{ $dateObj->format('D') }}</span>
                                <span class="text-lg font-bold">{{ $dateObj->format('j') }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold {{ $isToday ? 'text-secondary' : '' }}">
                                    {{ $dateObj->format('l, F j, Y') }}
                                    @if($isToday)
                                        <span class="badge badge-secondary badge-sm ml-2">Today</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-base-content/60">{{ $daySessions->count() }} {{ Str::plural('session', $daySessions->count()) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sessions Table --}}
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-24">Time</th>
                                    <th>Session</th>
                                    <th>Membership</th>
                                    <th>Instructor</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th class="w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daySessions as $session)
                                    @php
                                        $membershipPlan = $session->membershipPlans->first();
                                        $confirmedCount = $session->confirmedBookings->count();
                                        $capacity = $session->getEffectiveCapacity();
                                    @endphp
                                    <tr class="hover:bg-base-200/50">
                                        <td>
                                            <div class="font-medium">{{ $session->start_time->format('g:i A') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $session->duration_minutes }} min</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full" style="background-color: #8b5cf6;"></div>
                                                <span class="font-medium">{{ $session->title ?? 'Membership Session' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($membershipPlan)
                                                <span class="badge badge-soft badge-secondary badge-sm">{{ $membershipPlan->name }}</span>
                                            @else
                                                <span class="text-base-content/40">-</span>
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
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium {{ $confirmedCount >= $capacity ? 'text-error' : '' }}">
                                                    {{ $confirmedCount }}/{{ $capacity }}
                                                </span>
                                                @if($confirmedCount >= $capacity)
                                                    <span class="badge badge-error badge-xs">Full</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $session->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $session->status }}</span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="View" onclick="openDrawer('class-session-{{ $session->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                                <a href="{{ route('class-sessions.edit', $session) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                </a>
                                                @if($session->status !== 'cancelled' && $confirmedCount === 0)
                                                    <form action="{{ route('class-sessions.destroy', $session) }}" method="POST" class="inline" onsubmit="return confirm('Delete this session?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                                            <span class="icon-[tabler--trash] size-4"></span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAllDrawers()"></div>

{{-- Class Session Drawers --}}
@foreach($sessions as $session)
    @include('host.schedule.partials.class-session-drawer', ['classSession' => $session])
@endforeach

@push('scripts')
<script>
// Filter form auto-submit
function submitFilters() {
    document.getElementById('filter-form').submit();
}

// Range toggle
function setRange(range) {
    document.getElementById('range-input').value = range;
    const dateInput = document.getElementById('date');

    // Set appropriate date value based on range
    if (range === 'today') {
        if (dateInput) dateInput.value = '{{ now()->format('Y-m-d') }}';
    } else if (range === 'month') {
        if (dateInput) dateInput.value = '{{ now()->format('Y-m-d') }}';
    }

    submitFilters();
}

// Drawer functions
function openDrawer(id, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('drawer-backdrop');

    if (drawer) {
        // Close any open drawers first
        document.querySelectorAll('[id^="drawer-"]').forEach(d => {
            if (d.id !== 'drawer-backdrop' && d.id !== 'drawer-' + id) {
                d.classList.add('translate-x-full', 'hidden');
            }
        });

        // Show backdrop
        if (backdrop) {
            backdrop.classList.remove('hidden');
        }

        // Show and animate drawer
        drawer.classList.remove('hidden');
        setTimeout(() => {
            drawer.classList.remove('translate-x-full');
        }, 10);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(id) {
    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('drawer-backdrop');

    if (drawer) {
        drawer.classList.add('translate-x-full');
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 300);
    }

    // Hide backdrop
    if (backdrop) {
        backdrop.classList.add('hidden');
    }

    // Restore body scroll
    document.body.style.overflow = '';
}

function closeAllDrawers() {
    document.querySelectorAll('[id^="drawer-"]').forEach(drawer => {
        if (drawer.id !== 'drawer-backdrop') {
            drawer.classList.add('translate-x-full');
            setTimeout(() => {
                drawer.classList.add('hidden');
            }, 300);
        }
    });

    const backdrop = document.getElementById('drawer-backdrop');
    if (backdrop) {
        backdrop.classList.add('hidden');
    }

    document.body.style.overflow = '';
}

// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllDrawers();
    }
});
</script>
@endpush

@endsection
