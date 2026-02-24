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
            <p class="text-base-content/60 mt-1">
                @if($range === 'today')
                    {{ $startDate->format('l, F j, Y') }}
                @elseif($range === 'month')
                    {{ $startDate->format('F Y') }}
                @elseif($range === 'all')
                    All scheduled classes
                @else
                    {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                @endif
            </p>
        </div>
        <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Schedule Class
        </a>
    </div>

    {{-- Conflict Alert Banner --}}
    @if($unresolvedConflictsCount > 0 && !request('conflicts_only'))
    <div class="alert alert-error shadow-lg">
        <span class="icon-[tabler--alert-triangle] size-6"></span>
        <div class="flex-1">
            <h3 class="font-bold">{{ $unresolvedConflictsCount }} Scheduling {{ Str::plural('Conflict', $unresolvedConflictsCount) }}</h3>
            <p class="text-sm">Some sessions have scheduling conflicts that need to be resolved.</p>
        </div>
        <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['conflicts_only' => 1])) }}" class="btn btn-sm btn-outline">
            View Conflicts
        </a>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form id="filter-form" action="{{ route('class-sessions.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
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

                {{-- Class Plan Filter --}}
                <div class="w-40">
                    <label class="label-text" for="class_plan_id">Class</label>
                    <select id="class_plan_id" name="class_plan_id" class="select select-sm w-full" onchange="submitFilters()">
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

                {{-- Conflicts Filter --}}
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer" for="conflicts_only">
                        <input type="checkbox" id="conflicts_only" name="conflicts_only" value="1"
                            class="checkbox checkbox-sm checkbox-error"
                            {{ request('conflicts_only') ? 'checked' : '' }}
                            onchange="submitFilters()">
                        <span class="label-text text-error">
                            <span class="icon-[tabler--alert-triangle] size-4 inline"></span>
                            Conflicts Only
                        </span>
                    </label>
                </div>

                @if($classPlanId || $instructorId || $status || request('conflicts_only'))
                    <a href="{{ route('class-sessions.index', ['date' => $date, 'range' => $range]) }}" class="btn btn-ghost btn-sm">
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
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->subDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Day
            </a>
        @elseif($range === 'week')
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Week
            </a>
        @elseif($range === 'month')
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->subMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                Previous Month
            </a>
        @endif

        <div class="flex items-center gap-4 text-sm text-base-content/60">
            <span>
                <span class="font-semibold text-base-content">{{ $sessions->count() }}</span> {{ Str::plural('class', $sessions->count()) }}
            </span>
        </div>

        @if($range === 'all')
            <div></div>
        @elseif($range === 'today')
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->addDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                Next Day
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'week')
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                Next Week
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'month')
            <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->addMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
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
                    @if($classPlanId || $instructorId || $status)
                        No class sessions match your current filters.
                    @else
                        @if($range === 'today')
                            No class sessions scheduled for today.
                        @elseif($range === 'week')
                            No class sessions scheduled for this week.
                        @elseif($range === 'month')
                            No class sessions scheduled for this month.
                        @else
                            No class sessions found.
                        @endif
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
                @endphp
                <div id="date-{{ $dateKey }}" class="card bg-base-100" @if($isToday) data-today="true" @endif>
                    {{-- Date Header --}}
                    <div class="px-4 py-3 border-b border-base-200">
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
                    </div>

                    {{-- Sessions Table --}}
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-24">Time</th>
                                    <th>Class</th>
                                    <th>Instructor</th>
                                    <th>Location</th>
                                    <th class="text-center">Bookings</th>
                                    <th class="text-center">Check-ins</th>
                                    <th class="text-center">Intake</th>
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
                                                    @if($session->title)
                                                        <div class="font-medium">{{ $session->title }}</div>
                                                        @if($session->classPlan)
                                                            <div class="text-xs text-base-content/60">{{ $session->classPlan->name }}</div>
                                                        @endif
                                                    @else
                                                        <div class="font-medium">{{ $session->classPlan->name ?? 'Membership Session' }}</div>
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
                                        @php
                                            $confirmedBookings = $session->confirmedBookings ?? collect();
                                            $bookingsCount = $confirmedBookings->count();
                                            $checkedInCount = $confirmedBookings->filter(fn($b) => $b->isCheckedIn())->count();
                                            $intakeCompleted = $confirmedBookings->filter(fn($b) => $b->intake_status === 'completed')->count();
                                            $intakePending = $confirmedBookings->filter(fn($b) => $b->intake_status === 'pending')->count();
                                        @endphp
                                        <td class="text-center">
                                            <span class="text-sm font-medium">{{ $bookingsCount }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($checkedInCount > 0)
                                                <span class="text-sm font-medium text-success">{{ $checkedInCount }}</span>
                                            @else
                                                <span class="text-base-content/30">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($bookingsCount > 0)
                                                <div class="flex items-center justify-center gap-1">
                                                    @if($intakePending > 0)
                                                        <span class="icon-[tabler--clock] size-4 text-warning"></span>
                                                        <span class="text-sm text-warning">{{ $intakePending }}</span>
                                                    @elseif($intakeCompleted > 0)
                                                        <span class="icon-[tabler--circle-check] size-4 text-success"></span>
                                                        <span class="text-sm text-success">{{ $intakeCompleted }}</span>
                                                    @else
                                                        <span class="text-base-content/30">-</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-base-content/30">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="badge {{ $session->getStatusBadgeClass() }} badge-sm">
                                                    {{ $statuses[$session->status] ?? $session->status }}
                                                </span>
                                                @if($session->hasUnresolvedConflict())
                                                    <span class="badge badge-error badge-sm gap-1" title="{{ $session->conflict_notes }}">
                                                        <span class="icon-[tabler--alert-triangle] size-3"></span>
                                                        Conflict
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                @if($session->isPublished() && !$session->isPast())
                                                    <a href="{{ route('walk-in.select', ['session_id' => $session->id]) }}"
                                                       class="btn btn-ghost btn-xs btn-square text-primary"
                                                       title="Add Booking">
                                                        <span class="icon-[tabler--user-plus] size-4"></span>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="View" onclick="openDrawer('class-session-{{ $session->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
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
                                                    <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                                                        @if($session->hasUnresolvedConflict())
                                                            <li>
                                                                <form action="{{ route('class-sessions.resolve-conflict', $session) }}" method="POST" class="m-0">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="w-full text-left flex items-center gap-2 text-success">
                                                                        <span class="icon-[tabler--check] size-4"></span> Resolve Conflict
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li class="menu-title px-2 py-1 text-xs">Actions</li>
                                                        @endif
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
    // For 'all', date input is hidden so no need to set value

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

// Auto-scroll to today's date or nearest future date on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = '{{ now()->format('Y-m-d') }}';
    console.log('Today:', today);

    // First try to find today's card
    let targetCard = document.querySelector('[data-today="true"]');
    console.log('Today card found:', targetCard);

    // If no today card, find the nearest future date
    if (!targetCard) {
        const allDateCards = document.querySelectorAll('[id^="date-"]');
        console.log('All date cards:', allDateCards.length);

        for (const card of allDateCards) {
            const cardDate = card.id.replace('date-', '');
            console.log('Checking card date:', cardDate);
            if (cardDate >= today) {
                targetCard = card;
                console.log('Found target card:', cardDate);
                break;
            }
        }
    }

    if (targetCard) {
        console.log('Scrolling to:', targetCard.id);
        // Small delay to ensure page is fully rendered
        setTimeout(function() {
            const rect = targetCard.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const targetPosition = rect.top + scrollTop - 120; // 120px offset for header

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
            console.log('Scrolled to position:', targetPosition);
        }, 300);
    } else {
        console.log('No target card found');
    }
});
</script>
@endpush

@endsection
