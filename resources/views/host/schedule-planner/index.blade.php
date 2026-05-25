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

        @if(auth()->user()->hasPermission('schedule.create'))
        {{-- Add Schedule Dropdown (matches /schedule/calendar) --}}
        <div class="relative">
            <button type="button" class="btn btn-primary" onclick="togglePlannerDropdown('planner-schedule-dropdown')">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['schedule.add_schedule_planner'] ?? 'Add Schedule Planner' }}
                <span class="icon-[tabler--chevron-down] size-4"></span>
            </button>
            <ul id="planner-schedule-dropdown" class="hidden absolute right-0 top-full mt-1 menu bg-base-100 rounded-box w-72 p-2 shadow-lg border border-base-300 z-50">
                <li class="menu-title text-xs uppercase tracking-wider text-base-content/50 px-2 pt-2">{{ $trans['common.type'] ?? 'Schedule Type' }}</li>
                <li>
                    <a href="#" onclick="handlePlannerScheduleClick('class', '{{ route('class-sessions.create') }}', {{ $classPlans->count() }}); return false;" class="flex flex-col items-start gap-0.5 py-3">
                        <span class="flex items-center gap-2">
                            <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                            <span class="font-medium">{{ $trans['page.classes'] ?? 'Class' }}</span>
                        </span>
                        <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.class_description'] ?? 'Single or recurring class session' }}</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="handlePlannerScheduleClick('service', '{{ route('service-slots.create') }}', {{ $servicePlans->count() }}); return false;" class="flex flex-col items-start gap-0.5 py-3">
                        <span class="flex items-center gap-2">
                            <span class="icon-[tabler--massage] size-5 text-success"></span>
                            <span class="font-medium">{{ $trans['page.services'] ?? 'Service' }}</span>
                        </span>
                        <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.service_description'] ?? '1-on-1 appointment slot' }}</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="handlePlannerScheduleClick('membership', '{{ route('scheduled-membership.create') }}', {{ $membershipPlans->count() }}); return false;" class="flex flex-col items-start gap-0.5 py-3">
                        <span class="flex items-center gap-2">
                            <span class="icon-[tabler--calendar-user] size-5 text-warning"></span>
                            <span class="font-medium">{{ $trans['schedule.membership_schedule'] ?? 'Membership Schedule' }}</span>
                        </span>
                        <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.membership_schedule_description'] ?? 'Recurring classes for membership holders' }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('events.create') }}" class="flex flex-col items-start gap-0.5 py-3">
                        <span class="flex items-center gap-2">
                            <span class="icon-[tabler--calendar-event] size-5 text-error"></span>
                            <span class="font-medium">{{ $trans['nav.events'] ?? 'Event' }}</span>
                        </span>
                        <span class="text-xs text-base-content/60 ml-7">{{ $trans['events.schedule_description'] ?? 'Workshop, seminar, or special event' }}</span>
                    </a>
                </li>
            </ul>
        </div>
        @endif
    </div>

    {{-- Type Toggle & Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                {{-- Type Toggle --}}
                <div class="flex rounded-lg border border-base-300 overflow-hidden">
                    <a href="{{ route('schedule-planner.index', ['type' => 'all']) }}"
                       class="px-4 py-1.5 text-sm font-medium flex items-center gap-2 transition-colors {{ $type === 'all' ? 'bg-primary text-primary-content' : 'hover:bg-base-200' }}">
                        <span class="icon-[tabler--layout-list] size-4"></span>
                        All
                    </a>
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

                {{-- Plan Filter (not shown for "All") --}}
                @if($type !== 'all')
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
                @endif

                <span class="text-sm text-base-content/60">{{ $schedules->count() }} {{ Str::plural('schedule', $schedules->count()) }}</span>
            </div>
        </div>
    </div>

    {{-- Schedules List --}}
    @if($schedules->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Schedules Found</h3>
                @if($type === 'all')
                    <p class="text-base-content/60 mb-4">No recurring or upcoming schedules found across all types.</p>
                @elseif($type === 'membership')
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
            <div class="">
                <table class="table">
                    <thead>
                        <tr>
                            @if($type === 'all')
                                <th>Type</th>
                            @endif
                            <th>Name</th>
                            @if($type !== 'all')
                                <th>Days</th>
                            @endif
                            <th>Time</th>
                            <th>Instructor</th>
                            <th>Location</th>
                            <th class="text-center">Upcoming</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            @if($type === 'all')
                            <td>
                                @if($schedule->type === 'class')
                                    <span class="badge badge-soft badge-primary badge-sm"><span class="icon-[tabler--yoga] size-3 me-1"></span>Class</span>
                                @elseif($schedule->type === 'service')
                                    <span class="badge badge-soft badge-secondary badge-sm"><span class="icon-[tabler--massage] size-3 me-1"></span>Service</span>
                                @elseif($schedule->type === 'open_access')
                                    <span class="badge badge-soft badge-accent badge-sm"><span class="icon-[tabler--door-enter] size-3 me-1"></span>Open Access</span>
                                @else
                                    <span class="badge badge-soft badge-warning badge-sm"><span class="icon-[tabler--id-badge-2] size-3 me-1"></span>Membership</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($schedule->type === 'open_access')
                                        <span class="icon-[tabler--door-enter] size-4 text-accent" title="Open Access"></span>
                                    @elseif($schedule->is_recurring)
                                        <span class="icon-[tabler--calendar-repeat] size-4 text-primary" title="Recurring"></span>
                                    @else
                                        <span class="icon-[tabler--calendar-event] size-4 text-base-content/40" title="One-off"></span>
                                    @endif
                                    <span class="font-medium">{{ $schedule->title ?? 'Untitled' }}</span>
                                </div>
                            </td>
                            @if($type !== 'all')
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(explode(', ', $schedule->days) as $day)
                                        <span class="badge badge-soft badge-sm badge-primary">{{ $day }}</span>
                                    @endforeach
                                </div>
                            </td>
                            @endif
                            <td class="text-sm">
                                @if($schedule->type === 'open_access')
                                    <span class="badge badge-soft badge-accent badge-xs">Anytime</span>
                                @else
                                    {{ $schedule->time }}
                                @endif
                            </td>
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
                                @if($schedule->type === 'open_access')
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="badge badge-soft badge-sm badge-accent">{{ $schedule->session_count }} {{ Str::plural('member', $schedule->session_count) }}</span>
                                        @if(($schedule->today_checkins ?? 0) > 0)
                                            <span class="text-xs text-success">{{ $schedule->today_checkins }} today</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-soft badge-sm {{ $schedule->session_count > 0 ? 'badge-success' : 'badge-neutral' }}">
                                        {{ $schedule->session_count }} upcoming
                                    </span>
                                @endif
                            </td>
                            <td>
                                <x-actions-dropdown size="xs">
                                    @if($schedule->type === 'open_access')
                                        <li><a href="{{ route('membership-plans.show', $schedule->id) }}">
                                            <span class="icon-[tabler--eye] size-4"></span> View Plan
                                        </a></li>
                                        <li><a href="{{ route('scheduled-membership.create', ['membership_plan_id' => $schedule->id, 'schedule_type' => 'open_access']) }}">
                                            <span class="icon-[tabler--pencil] size-4"></span> Edit
                                        </a></li>
                                        <li><a href="{{ route('membership-checkin.index', $schedule->id) }}">
                                            <span class="icon-[tabler--door-enter] size-4"></span> Check-in Screen
                                        </a></li>
                                        @if($schedule->session_count === 0 && ($schedule->today_checkins ?? 0) === 0)
                                            <li>
                                                <button type="button" class="w-full text-left flex items-center gap-2 text-error"
                                                    onclick="openDeleteModal('{{ route('membership-plans.destroy', $schedule->id) }}', '{{ addslashes($schedule->title) }}')">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </li>
                                        @endif
                                    @elseif($schedule->type === 'service')
                                        <li><a href="{{ route('schedule-planner.show', $schedule->id) }}">
                                            <span class="icon-[tabler--eye] size-4"></span> View
                                        </a></li>
                                        @if(auth()->user()->hasPermission('schedule.edit'))
                                        <li><a href="{{ route('service-slots.edit', $schedule->id) }}">
                                            <span class="icon-[tabler--pencil] size-4"></span> Edit
                                        </a></li>
                                        @endif
                                        @if($schedule->session_count === 0)
                                            <li>
                                                <button type="button" class="w-full text-left flex items-center gap-2 text-error"
                                                    onclick="openDeleteModal('{{ route('service-slots.destroy', $schedule->id) }}', '{{ addslashes($schedule->title) }}')">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </li>
                                        @endif
                                    @elseif($schedule->type === 'membership')
                                        <li><a href="{{ route('schedule-planner.show', $schedule->id) }}">
                                            <span class="icon-[tabler--eye] size-4"></span> View
                                        </a></li>
                                        @if(auth()->user()->hasPermission('schedule.edit'))
                                        <li><a href="{{ route('scheduled-membership.edit', $schedule->id) }}">
                                            <span class="icon-[tabler--pencil] size-4"></span> Edit
                                        </a></li>
                                        @endif
                                        @if($schedule->session_count === 0)
                                            <li>
                                                <button type="button" class="w-full text-left flex items-center gap-2 text-error"
                                                    onclick="openDeleteModal('{{ route('class-sessions.destroy', $schedule->id) }}', '{{ addslashes($schedule->title) }}')">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </li>
                                        @endif
                                    @else
                                        <li><a href="{{ route('schedule-planner.show', $schedule->id) }}">
                                            <span class="icon-[tabler--eye] size-4"></span> View
                                        </a></li>
                                        @if(auth()->user()->hasPermission('schedule.edit'))
                                        <li><a href="{{ route('class-sessions.edit', $schedule->id) }}">
                                            <span class="icon-[tabler--pencil] size-4"></span> Edit
                                        </a></li>
                                        @endif
                                        @if($schedule->session_count === 0)
                                            <li>
                                                <button type="button" class="w-full text-left flex items-center gap-2 text-error"
                                                    onclick="openDeleteModal('{{ route('class-sessions.destroy', $schedule->id) }}', '{{ addslashes($schedule->title) }}')">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </li>
                                        @endif
                                    @endif
                                </x-actions-dropdown>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-error/10 flex items-center justify-center">
                <span class="icon-[tabler--trash] size-6 text-error"></span>
            </div>
            <div>
                <h3 class="font-bold text-lg">Delete Schedule</h3>
                <p class="text-base-content/60 text-sm">This action cannot be undone.</p>
            </div>
        </div>
        <p class="py-2">Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete
                </button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
function openDeleteModal(action, name, type) {
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteModal').showModal();
}

// Add Schedule dropdown — matches /schedule/calendar behavior
window.togglePlannerDropdown = function(id) {
    const dropdown = document.getElementById(id);
    if (!dropdown) return;
    dropdown.classList.toggle('hidden');
};

// Close on outside click
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('planner-schedule-dropdown');
    if (!dropdown || dropdown.classList.contains('hidden')) return;
    if (!e.target.closest('#planner-schedule-dropdown') && !e.target.closest('[onclick*="togglePlannerDropdown"]')) {
        dropdown.classList.add('hidden');
    }
});

window.handlePlannerScheduleClick = function(type, url, planCount) {
    const dropdown = document.getElementById('planner-schedule-dropdown');
    if (dropdown) dropdown.classList.add('hidden');

    if (planCount > 0) {
        window.location.href = url;
        return;
    }

    let typeLabel, typeLabelLower, createUrl;
    switch(type) {
        case 'class':
            typeLabel = 'Class'; typeLabelLower = 'class';
            createUrl = '{{ route("class-plans.create") }}'; break;
        case 'service':
            typeLabel = 'Service'; typeLabelLower = 'service';
            createUrl = '{{ route("service-plans.create") }}'; break;
        case 'membership':
            typeLabel = 'Membership'; typeLabelLower = 'membership';
            createUrl = '{{ route("membership-plans.create") }}'; break;
        case 'space_rental':
            typeLabel = 'Rental Space'; typeLabelLower = 'rental space';
            createUrl = '{{ route("space-rentals.config.create") }}'; break;
        default:
            typeLabel = 'Plan'; typeLabelLower = 'plan';
            createUrl = '{{ route("catalog.index") }}';
    }

    let modalTitle, modalMessage, actionText;
    if (type === 'space_rental') {
        modalTitle = 'No Rental Spaces Found';
        modalMessage = 'To book a space rental, you need to create a rental space first in Classes & Services.';
        actionText = 'Add Rental Space';
    } else {
        modalTitle = `No ${typeLabel} Plans Found`;
        modalMessage = `To schedule a ${typeLabelLower}, you need to create a ${typeLabelLower} plan first in Classes & Services.`;
        actionText = `Add ${typeLabel} Plan`;
    }

    if (window.FitCRM && typeof window.FitCRM.showAlertModal === 'function') {
        window.FitCRM.showAlertModal({
            title: modalTitle,
            message: modalMessage,
            icon: 'alert-triangle',
            iconBg: 'bg-warning/20',
            iconColor: 'text-warning',
            actionText: actionText,
            actionUrl: createUrl,
            actionIcon: 'plus',
            actionClass: 'btn btn-primary'
        });
    } else {
        // Fallback if the alert modal helper isn't loaded on this page
        if (confirm(modalTitle + '\n\n' + modalMessage + '\n\nGo to create now?')) {
            window.location.href = createUrl;
        }
    }
};
</script>
@endpush
@endsection
