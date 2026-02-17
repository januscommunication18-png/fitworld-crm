@extends('layouts.dashboard')

@section('title', 'Today\'s Schedule')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Schedule</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Today's Schedule</h1>
            <p class="text-base-content/60 mt-1">{{ now()->format('l, F j, Y') }} <span class="text-base-content/40">|</span> <span class="font-medium text-primary">{{ now()->format('g:i A') }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Class
            </a>
            <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Service
            </a>
        </div>
    </div>

    {{-- Sub Navigation --}}
    @include('host.schedule.partials.sub-nav')

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--yoga] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalClasses }}</p>
                        <p class="text-xs text-base-content/60">Classes</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--massage] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalServices }}</p>
                        <p class="text-xs text-base-content/60">Services</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--ticket] size-6 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalBookings }}</p>
                        <p class="text-xs text-base-content/60">Bookings</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--user-check] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalCheckIns }}</p>
                        <p class="text-xs text-base-content/60">Check-ins</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3 px-4">
            <form action="{{ route('schedule.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-40">
                    <label class="label-text" for="type">Type</label>
                    <select id="type" name="type" class="select w-full select-sm">
                        <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="class" {{ ($filters['type'] ?? '') === 'class' ? 'selected' : '' }}>Classes</option>
                        <option value="service" {{ ($filters['type'] ?? '') === 'service' ? 'selected' : '' }}>Services</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="location_id">Location</label>
                    <select id="location_id" name="location_id" class="select w-full select-sm">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ ($filters['location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="instructor_id">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="select w-full select-sm">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ ($filters['instructor_id'] ?? '') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--filter] size-4"></span>
                    Filter
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('schedule.index') }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Empty State --}}
    @if($upcomingItems->isEmpty() && $pastItems->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Schedule for Today</h3>
                <p class="text-base-content/60 mb-4">
                    @if(!empty(array_filter($filters ?? [])))
                        No classes or services match your current filters.
                    @else
                        There are no classes or services scheduled for today.
                    @endif
                </p>
                <div class="flex justify-center gap-2">
                    <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Class
                    </a>
                    <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Service
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- Upcoming Sessions --}}
        @if($upcomingItems->isNotEmpty())
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="icon-[tabler--clock-play] size-5 text-primary"></span>
                        <h2 class="text-lg font-semibold">Upcoming</h2>
                        <span class="badge badge-sm badge-primary">{{ $upcomingItems->count() }}</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($upcomingItems as $entry)
                            @include('host.schedule.partials.schedule-item', ['entry' => $entry, 'isPast' => false])
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-6 text-center">
                    <span class="icon-[tabler--calendar-check] size-12 text-success/40 mx-auto mb-3"></span>
                    <p class="text-base-content/60">No more sessions scheduled for today</p>
                </div>
            </div>
        @endif

        {{-- Past Sessions (Collapsed) --}}
        @if($pastItems->isNotEmpty())
            <div class="card bg-base-100 shadow-sm" x-data="{ expanded: false }">
                <div class="card-body p-4">
                    <button type="button" class="flex items-center gap-2 w-full text-left" @click="expanded = !expanded">
                        <span class="icon-[tabler--clock-pause] size-5 text-base-content/50"></span>
                        <h2 class="text-lg font-semibold text-base-content/70">Earlier Today</h2>
                        <span class="badge badge-sm badge-ghost">{{ $pastItems->count() }}</span>
                        <span class="ml-auto">
                            <span x-show="!expanded" class="icon-[tabler--chevron-down] size-5 text-base-content/50"></span>
                            <span x-show="expanded" class="icon-[tabler--chevron-up] size-5 text-base-content/50"></span>
                        </span>
                    </button>

                    <div x-show="expanded" x-collapse class="mt-4">
                        <div class="space-y-3">
                            @foreach($pastItems as $entry)
                                @include('host.schedule.partials.schedule-item', ['entry' => $entry, 'isPast' => true])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

{{-- Class Session Drawers --}}
@foreach($classSessions as $session)
    @include('host.schedule.partials.class-session-drawer', ['classSession' => $session])
@endforeach

{{-- Service Slot Drawers --}}
@foreach($serviceSlots as $slot)
    @include('host.schedule.partials.service-slot-drawer', ['serviceSlot' => $slot])
@endforeach

@push('scripts')
<script>
    function markComplete(classSessionId, button) {
        if (button.disabled) return;

        // Confirmation
        if (!confirm('Mark this class session as completed?')) {
            return;
        }

        button.disabled = true;
        const originalHtml = button.innerHTML;
        button.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

        fetch(`/schedule/mark-complete/${classSessionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the button to show completed
                button.classList.remove('btn-ghost');
                button.classList.add('btn-success', 'btn-disabled');
                button.innerHTML = '<span class="icon-[tabler--check] size-4"></span>';
                button.title = 'Completed';
                button.disabled = true;

                // Update status badge in the same row
                const row = button.closest('.schedule-item-row');
                if (row) {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.className = 'badge badge-info badge-sm status-badge';
                        statusBadge.textContent = 'Completed';
                    }
                }
            } else {
                alert(data.message || 'Failed to mark as completed');
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }
</script>
@endpush
@endsection
