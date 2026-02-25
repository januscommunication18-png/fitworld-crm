@php
    $user = auth()->user();
    $host = $user->currentHost() ?? $user->host;
    $selectedLang = session("studio_language_{$host->id}", $host->default_language_app ?? 'en');
    $t = \App\Services\TranslationService::make($host, $selectedLang);
    $trans = $t->all();
@endphp

@extends('layouts.dashboard')

@section('title', $trans['schedule.service_slots'] ?? 'Service Slots')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> {{ $trans['nav.schedule'] ?? 'Schedule' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['page.services'] ?? 'Services' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['schedule.service_slots'] ?? 'Service Slots' }}</h1>
            <p class="text-base-content/60 mt-1">
                @if($range === 'today')
                    {{ $startDate->format('l, F j, Y') }}
                @elseif($range === 'month')
                    {{ $startDate->format('F Y') }}
                @elseif($range === 'all')
                    {{ $trans['schedule.all_upcoming_slots'] ?? 'All upcoming slots' }}
                @else
                    {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('service-slots.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['schedule.add_slot'] ?? 'Add Slot' }}
            </a>
            <button type="button" class="btn btn-soft btn-secondary" data-overlay="#bulk-create-modal">
                <span class="icon-[tabler--calendar-plus] size-5"></span>
                {{ $trans['schedule.bulk_add'] ?? 'Bulk Add' }}
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form id="filter-form" action="{{ route('service-slots.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                {{-- Range Toggle --}}
                <div>
                    <label class="label-text">{{ $trans['schedule.range'] ?? 'Range' }}</label>
                    <div class="join">
                        <button type="button" class="btn btn-sm join-item {{ $range === 'today' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('today')">
                            {{ $trans['schedule.today'] ?? 'Today' }}
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'week' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('week')">
                            {{ $trans['schedule.week'] ?? 'Week' }}
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'month' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('month')">
                            {{ $trans['schedule.month'] ?? 'Month' }}
                        </button>
                        <button type="button" class="btn btn-sm join-item {{ $range === 'all' ? 'btn-primary' : 'btn-ghost' }}" onclick="setRange('all')">
                            {{ $trans['common.all'] ?? 'All' }}
                        </button>
                    </div>
                    <input type="hidden" name="range" id="range-input" value="{{ $range }}">
                    <input type="hidden" name="date" id="date" value="{{ $date }}">
                </div>

                {{-- Service Plan Filter --}}
                <div class="w-40">
                    <label class="label-text" for="service_plan_id">{{ $trans['field.service'] ?? 'Service' }}</label>
                    <select id="service_plan_id" name="service_plan_id" class="select select-sm w-full" onchange="submitFilters()">
                        <option value="">{{ $trans['schedule.all_services'] ?? 'All Services' }}</option>
                        @foreach($servicePlans as $plan)
                            <option value="{{ $plan->id }}" {{ $servicePlanId == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor Filter --}}
                <div class="w-40">
                    <label class="label-text" for="instructor_id">{{ $trans['field.instructor'] ?? 'Instructor' }}</label>
                    <select id="instructor_id" name="instructor_id" class="select select-sm w-full" onchange="submitFilters()">
                        <option value="">{{ $trans['schedule.all_instructors'] ?? 'All Instructors' }}</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ $instructorId == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="w-32">
                    <label class="label-text" for="status">{{ $trans['common.status'] ?? 'Status' }}</label>
                    <select id="status" name="status" class="select select-sm w-full" onchange="submitFilters()">
                        <option value="">{{ $trans['schedule.all_statuses'] ?? 'All Statuses' }}</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($servicePlanId || $instructorId || $status)
                    <a href="{{ route('service-slots.index', ['date' => $date, 'range' => $range]) }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--x] size-4"></span>
                        {{ $trans['btn.clear'] ?? 'Clear' }}
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
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->subDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                {{ $trans['schedule.previous_day'] ?? 'Previous Day' }}
            </a>
        @elseif($range === 'week')
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                {{ $trans['schedule.previous_week'] ?? 'Previous Week' }}
            </a>
        @elseif($range === 'month')
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->subMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--chevron-left] size-5"></span>
                {{ $trans['schedule.previous_month'] ?? 'Previous Month' }}
            </a>
        @endif

        <div class="flex items-center gap-4 text-sm text-base-content/60">
            <span>
                <span class="font-semibold text-base-content">{{ $slots->count() }}</span> {{ $trans['common.slot'] ?? 'slot' }}{{ $slots->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        @if($range === 'all')
            <div></div>
        @elseif($range === 'today')
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->addDay()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                {{ $trans['schedule.next_day'] ?? 'Next Day' }}
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'week')
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                {{ $trans['schedule.next_week'] ?? 'Next Week' }}
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @elseif($range === 'month')
            <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->addMonth()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
                {{ $trans['schedule.next_month'] ?? 'Next Month' }}
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </a>
        @endif
    </div>

    {{-- Slots List --}}
    @if($slots->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['schedule.no_slots_found'] ?? 'No Slots Found' }}</h3>
                <p class="text-base-content/60 mb-4">
                    @if($servicePlanId || $instructorId || $status)
                        {{ $trans['schedule.no_slots_filter'] ?? 'No service slots match your current filters.' }}
                    @else
                        @if($range === 'today')
                            {{ $trans['schedule.no_slots_today'] ?? 'No service slots scheduled for today.' }}
                        @elseif($range === 'week')
                            {{ $trans['schedule.no_slots_week'] ?? 'No service slots scheduled for this week.' }}
                        @elseif($range === 'month')
                            {{ $trans['schedule.no_slots_month'] ?? 'No service slots scheduled for this month.' }}
                        @else
                            {{ $trans['schedule.no_slots_upcoming'] ?? 'No upcoming service slots found.' }}
                        @endif
                    @endif
                </p>
                <a href="{{ route('service-slots.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['schedule.add_first_slot'] ?? 'Add Your First Slot' }}
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($slotsByDate as $dateKey => $daySlots)
                @php
                    $dateObj = \Carbon\Carbon::parse($dateKey);
                    $isToday = $dateObj->isToday();
                @endphp
                <div class="card bg-base-100">
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
                                        <span class="badge badge-primary badge-sm ml-2">{{ $trans['schedule.today'] ?? 'Today' }}</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-base-content/60">{{ $daySlots->count() }} {{ $trans['common.slot'] ?? 'slot' }}{{ $daySlots->count() !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Slots Table --}}
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-24">{{ $trans['common.time'] ?? 'Time' }}</th>
                                    <th>{{ $trans['field.service'] ?? 'Service' }}</th>
                                    <th>{{ $trans['field.instructor'] ?? 'Instructor' }}</th>
                                    <th>{{ $trans['field.location'] ?? 'Location' }}</th>
                                    <th>{{ $trans['field.client'] ?? 'Client' }}</th>
                                    <th>{{ $trans['common.price'] ?? 'Price' }}</th>
                                    <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                    <th class="w-32">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daySlots as $slot)
                                    @php
                                        $booking = $slot->bookings->first();
                                    @endphp
                                    <tr class="hover:bg-base-200/50">
                                        <td>
                                            <div class="font-medium">{{ $slot->start_time->format('g:i A') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $slot->duration_minutes }} min</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                @if($slot->servicePlan)
                                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $slot->servicePlan->color ?? '#6366f1' }};"></div>
                                                @endif
                                                <span class="font-medium">{{ $slot->servicePlan->name ?? 'Unknown' }}</span>
                                            </div>
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
                                        <td>
                                            @if($booking && $booking->client)
                                                <div class="flex items-center gap-2">
                                                    <x-avatar
                                                        :src="$booking->client->avatar_url ?? null"
                                                        :initials="$booking->client->initials ?? '?'"
                                                        size="xs"
                                                    />
                                                    <span class="text-sm">{{ $booking->client->full_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-base-content/30">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $slot->formatted_price }}</td>
                                        <td>
                                            <span class="badge {{ $slot->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $slot->status }}</span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                @if($slot->isAvailable())
                                                    <a href="{{ route('walk-in.select-service', ['slot' => $slot->id]) }}"
                                                       class="btn btn-ghost btn-xs btn-square text-primary"
                                                       title="{{ $trans['schedule.add_booking'] ?? 'Add Booking' }}">
                                                        <span class="icon-[tabler--user-plus] size-4"></span>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.view'] ?? 'View' }}" onclick="openDrawer('service-slot-{{ $slot->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                                <a href="{{ route('service-slots.edit', $slot) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['btn.edit'] ?? 'Edit' }}">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                </a>
                                                @if($slot->status !== 'booked')
                                                    <form action="{{ route('service-slots.destroy', $slot) }}" method="POST" class="inline" onsubmit="return confirm('{{ $trans['schedule.confirm_delete_slot'] ?? 'Delete this slot?' }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="{{ $trans['btn.delete'] ?? 'Delete' }}">
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

{{-- Service Slot Drawers --}}
@foreach($slots as $slot)
    @include('host.schedule.partials.service-slot-drawer', ['serviceSlot' => $slot])
@endforeach

{{-- Bulk Create Modal --}}
<div id="bulk-create-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-content">
            <form action="{{ route('service-slots.bulk') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">{{ $trans['schedule.bulk_add_slots'] ?? 'Bulk Add Service Slots' }}</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#bulk-create-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="bulk_service_plan_id">{{ $trans['field.service'] ?? 'Service' }}</label>
                            <select id="bulk_service_plan_id" name="service_plan_id" class="select w-full" required>
                                <option value="">{{ $trans['field.select_service'] ?? 'Select a service...' }}</option>
                                @foreach($servicePlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-text" for="bulk_instructor_id">{{ $trans['field.instructor'] ?? 'Instructor' }}</label>
                            <select id="bulk_instructor_id" name="instructor_id" class="select w-full" required>
                                <option value="">{{ $trans['field.select_instructor'] ?? 'Select an instructor...' }}</option>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="bulk_start_date">{{ $trans['field.start_date'] ?? 'Start Date' }}</label>
                            <input type="date" id="bulk_start_date" name="start_date" class="input w-full" min="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="label-text" for="bulk_end_date">{{ $trans['field.end_date'] ?? 'End Date' }}</label>
                            <input type="date" id="bulk_end_date" name="end_date" class="input w-full" required>
                        </div>
                    </div>

                    <div>
                        <label class="label-text">{{ $trans['field.days_of_week'] ?? 'Days of Week' }}</label>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $day)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200">
                                <input type="checkbox" name="days_of_week[]" value="{{ $index }}" class="checkbox checkbox-sm checkbox-primary" {{ in_array($index, [1,2,3,4,5]) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="label-text">{{ $trans['schedule.time_slots'] ?? 'Time Slots' }}</label>
                        <div id="time-slots-container" class="space-y-2 mt-2">
                            <div class="flex items-center gap-2">
                                <input type="time" name="times[]" class="input flex-1" value="09:00" required>
                                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="addTimeSlot()">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">{{ $trans['schedule.time_slots_help'] ?? 'Add multiple times to create slots at each time on selected days.' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-overlay="#bulk-create-modal">{{ $trans['btn.cancel'] ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-primary">{{ $trans['schedule.create_slots'] ?? 'Create Slots' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

// Bulk create time slots
function addTimeSlot() {
    var container = document.getElementById('time-slots-container');
    var newSlot = document.createElement('div');
    newSlot.className = 'flex items-center gap-2';
    newSlot.innerHTML = '<input type="time" name="times[]" class="input flex-1" required>' +
        '<button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="this.parentElement.remove()">' +
        '<span class="icon-[tabler--trash] size-4"></span></button>';
    container.appendChild(newSlot);
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
