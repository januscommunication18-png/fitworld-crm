@extends('layouts.dashboard')

@section('title', 'Service Slots')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Service Slots</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Service Slots</h1>
            <p class="text-base-content/60 mt-1">Manage available time slots for 1-on-1 services.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('service-slots.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Slot
            </a>
            <button type="button" class="btn btn-soft btn-secondary" data-overlay="#bulk-create-modal">
                <span class="icon-[tabler--calendar-plus] size-5"></span>
                Bulk Add
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('service-slots.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="date">Week of</label>
                    <input type="date" id="date" name="date" value="{{ $date }}" class="input w-full">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="service_plan_id">Service</label>
                    <select id="service_plan_id" name="service_plan_id" class="select w-full">
                        <option value="">All Services</option>
                        @foreach($servicePlans as $plan)
                        <option value="{{ $plan->id }}" {{ $servicePlanId == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="instructor_id">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="select w-full">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ $instructorId == $instructor->id ? 'selected' : '' }}>{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select w-full">
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
        <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->subWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--chevron-left] size-5"></span>
            Previous Week
        </a>
        <h2 class="font-semibold">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</h2>
        <a href="{{ route('service-slots.index', array_merge(request()->query(), ['date' => $startDate->copy()->addWeek()->format('Y-m-d')])) }}" class="btn btn-ghost btn-sm">
            Next Week
            <span class="icon-[tabler--chevron-right] size-5"></span>
        </a>
    </div>

    {{-- Slots List --}}
    @if($slots->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Slots Found</h3>
            <p class="text-base-content/60 mb-4">No service slots match your filters for this week.</p>
            <a href="{{ route('service-slots.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Your First Slot
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
                            <th>Service</th>
                            <th>Instructor</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $slot)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $slot->start_time->format('D, M j') }}</div>
                                <div class="text-sm text-base-content/60">{{ $slot->formatted_time_range }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $slot->servicePlan->color }};"></div>
                                    {{ $slot->servicePlan->name }}
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($slot->instructor->photo_url)
                                    <img src="{{ $slot->instructor->photo_url }}" alt="{{ $slot->instructor->name }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary text-primary-content w-6 h-6 rounded-full font-bold text-xs">
                                            {{ strtoupper(substr($slot->instructor->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                    {{ $slot->instructor->name }}
                                </div>
                            </td>
                            <td>
                                @if($slot->location)
                                {{ $slot->location->name }}
                                @if($slot->room)
                                <span class="text-base-content/60">/ {{ $slot->room->name }}</span>
                                @endif
                                @else
                                <span class="text-base-content/60">-</span>
                                @endif
                            </td>
                            <td>{{ $slot->formatted_price }}</td>
                            <td>
                                <span class="badge {{ $slot->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $slot->status }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if($slot->isAvailable())
                                    <a href="{{ route('walk-in.service', $slot) }}"
                                       class="btn btn-ghost btn-xs btn-square text-primary"
                                       title="Add Booking">
                                        <span class="icon-[tabler--walk] size-4"></span>
                                    </a>
                                    @endif
                                    <a href="{{ route('service-slots.edit', $slot) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </a>
                                    @if($slot->status !== 'booked')
                                    <form action="{{ route('service-slots.destroy', $slot) }}" method="POST" class="inline" onsubmit="return confirm('Delete this slot?')">
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
    </div>
    @endif
</div>

{{-- Bulk Create Modal --}}
<div id="bulk-create-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-content">
            <form action="{{ route('service-slots.bulk') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">Bulk Add Service Slots</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#bulk-create-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="bulk_service_plan_id">Service</label>
                            <select id="bulk_service_plan_id" name="service_plan_id" class="select w-full" required>
                                <option value="">Select a service...</option>
                                @foreach($servicePlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-text" for="bulk_instructor_id">Instructor</label>
                            <select id="bulk_instructor_id" name="instructor_id" class="select w-full" required>
                                <option value="">Select an instructor...</option>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="bulk_start_date">Start Date</label>
                            <input type="date" id="bulk_start_date" name="start_date" class="input w-full" min="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="label-text" for="bulk_end_date">End Date</label>
                            <input type="date" id="bulk_end_date" name="end_date" class="input w-full" required>
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Days of Week</label>
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
                        <label class="label-text">Time Slots</label>
                        <div id="time-slots-container" class="space-y-2 mt-2">
                            <div class="flex items-center gap-2">
                                <input type="time" name="times[]" class="input flex-1" value="09:00" required>
                                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="addTimeSlot()">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Add multiple times to create slots at each time on selected days.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-overlay="#bulk-create-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Slots</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addTimeSlot() {
    var container = document.getElementById('time-slots-container');
    var newSlot = document.createElement('div');
    newSlot.className = 'flex items-center gap-2';
    newSlot.innerHTML = '<input type="time" name="times[]" class="input flex-1" required>' +
        '<button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="this.parentElement.remove()">' +
        '<span class="icon-[tabler--trash] size-4"></span></button>';
    container.appendChild(newSlot);
}
</script>
@endpush
@endsection
