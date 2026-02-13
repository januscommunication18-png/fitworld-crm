@extends('layouts.dashboard')

@section('title', 'Class Sessions')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Class Sessions</h1>
            <p class="text-base-content/60 mt-1">Schedule and manage your class sessions.</p>
        </div>
        <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Schedule Class
        </a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('class-sessions.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="date">Week of</label>
                    <input type="text" id="date" name="date" value="{{ $date }}" class="input w-full flatpickr-date" placeholder="Select date...">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="class_plan_id">Class</label>
                    <select id="class_plan_id" name="class_plan_id" class="hidden"
                        data-select='{
                            "hasSearch": true,
                            "searchPlaceholder": "Search classes...",
                            "placeholder": "All Classes",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">All Classes</option>
                        @foreach($classPlans as $plan)
                        <option value="{{ $plan->id }}" {{ $classPlanId == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="instructor_id">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="hidden"
                        data-select='{
                            "hasSearch": true,
                            "searchPlaceholder": "Search instructors...",
                            "placeholder": "All Instructors",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ $instructorId == $instructor->id ? 'selected' : '' }}>{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="hidden"
                        data-select='{
                            "placeholder": "All Statuses",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-5"></span>
                    Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Week Navigation --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--chevron-left] size-5"></span>
            Previous Week
        </a>
        <h2 class="font-semibold">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</h2>
        <a href="{{ route('class-sessions.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            Next Week
            <span class="icon-[tabler--chevron-right] size-5"></span>
        </a>
    </div>

    {{-- Sessions List --}}
    @if($sessions->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Sessions Found</h3>
            <p class="text-base-content/60 mb-4">No class sessions match your filters for this week.</p>
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Schedule Your First Class
            </a>
        </div>
    </div>
    @else
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Class</th>
                            <th>Instructor</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $session->start_time->format('D, M j') }}</div>
                                <div class="text-sm text-base-content/60">{{ $session->formatted_time_range }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $session->classPlan->color }};"></div>
                                    <div>
                                        <div class="font-medium">{{ $session->display_title }}</div>
                                        <div class="text-sm text-base-content/60">{{ $session->formatted_duration }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($session->primaryInstructor->photo_url)
                                    <img src="{{ $session->primaryInstructor->photo_url }}" alt="{{ $session->primaryInstructor->name }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary text-primary-content w-6 h-6 rounded-full font-bold text-xs">
                                            {{ strtoupper(substr($session->primaryInstructor->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                    <div>
                                        {{ $session->primaryInstructor->name }}
                                        @if($session->backupInstructors->isNotEmpty())
                                        <div class="text-xs text-base-content/60">
                                            +{{ $session->backupInstructors->count() }} backup{{ $session->backupInstructors->count() > 1 ? 's' : '' }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($session->location)
                                {{ $session->location->name }}
                                @if($session->room)
                                <span class="text-base-content/60">/ {{ $session->room->name }}</span>
                                @endif
                                @else
                                <span class="text-base-content/60">-</span>
                                @endif
                            </td>
                            <td>{{ $session->capacity }}</td>
                            <td>
                                <span class="badge {{ $session->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $session->status }}</span>
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
                                        <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
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
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        altInputClass: 'input w-full'
    });
});
</script>
@endpush

