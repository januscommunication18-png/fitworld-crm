@extends('layouts.dashboard')

@section('title', $classSession->display_title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $classSession->display_title }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('class-sessions.index') }}" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                </a>
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $classSession->classPlan->color }};"></div>
                <h1 class="text-2xl font-bold">{{ $classSession->display_title }}</h1>
                <span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft capitalize">{{ $classSession->status }}</span>
            </div>
            <p class="text-base-content/60">{{ $classSession->formatted_date }} &bull; {{ $classSession->formatted_time_range }}</p>
            @if($classSession->hasUnresolvedConflict())
                <span class="badge badge-error badge-soft gap-1 mt-1">
                    <span class="icon-[tabler--alert-triangle] size-3"></span>
                    Has Scheduling Conflict
                </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if($classSession->isPublished() && !$classSession->isPast())
            <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-success">
                <span class="icon-[tabler--user-plus] size-5"></span>
                Add Booking
            </a>
            @endif
            @if($classSession->isDraft())
            <form action="{{ route('class-sessions.publish', $classSession) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                    <span class="icon-[tabler--send] size-5"></span>
                    Publish
                </button>
            </form>
            @elseif($classSession->isPublished())
            <form action="{{ route('class-sessions.unpublish', $classSession) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-soft btn-secondary">
                    <span class="icon-[tabler--eye-off] size-5"></span>
                    Unpublish
                </button>
            </form>
            @endif
            <a href="{{ route('class-sessions.edit', $classSession) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit
            </a>
            <details class="dropdown dropdown-bottom dropdown-end">
                <summary class="btn btn-ghost btn-square list-none cursor-pointer">
                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                </summary>
                <ul class="dropdown-content menu bg-base-100 rounded-box w-44 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                    <li>
                        <form action="{{ route('class-sessions.duplicate', $classSession) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--copy] size-4"></span> Duplicate
                            </button>
                        </form>
                    </li>
                    @if($classSession->hasBackupInstructor())
                    <li>
                        <form action="{{ route('class-sessions.promote-backup', $classSession) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--arrows-exchange] size-4"></span> Promote Backup
                            </button>
                        </form>
                    </li>
                    @endif
                    @if(!$classSession->isCancelled())
                    <li>
                        <a href="#" class="text-error" data-overlay="#cancel-modal">
                            <span class="icon-[tabler--x] size-4"></span> Cancel Session
                        </a>
                    </li>
                    @endif
                    @if(!$classSession->isPublished())
                    <li>
                        <form action="{{ route('class-sessions.destroy', $classSession) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this session?')">
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
    </div>

    {{-- Conflict Alert --}}
    @if($classSession->hasUnresolvedConflict())
    <div class="alert alert-error shadow-lg">
        <span class="icon-[tabler--alert-triangle] size-6"></span>
        <div class="flex-1">
            <h3 class="font-bold">Scheduling Conflict</h3>
            <p class="text-sm">{{ $classSession->conflict_notes ?? 'This session has a scheduling conflict that needs to be resolved.' }}</p>
        </div>
        <form action="{{ route('class-sessions.resolve-conflict', $classSession) }}" method="POST" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm">
                <span class="icon-[tabler--check] size-4"></span>
                Mark as Resolved
            </button>
        </form>
    </div>
    @elseif($classSession->isConflictResolved())
    <div class="alert alert-success shadow-lg">
        <span class="icon-[tabler--check] size-6"></span>
        <div class="flex-1">
            <h3 class="font-bold">Conflict Resolved</h3>
            <p class="text-sm">
                Resolved on {{ $classSession->conflict_resolved_at->format('M j, Y g:i A') }}
                @if($classSession->conflictResolver)
                    by {{ $classSession->conflictResolver->name }}
                @endif
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Session Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Session Details</h3>
                </div>
                <div class="card-body space-y-6">
                    {{-- Basic Info Grid --}}
                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm text-base-content/60">Status</dt>
                            <dd><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classSession->status }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Class Plan</dt>
                            <dd class="font-medium">{{ $classSession->classPlan->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Category</dt>
                            <dd class="capitalize">{{ $classSession->classPlan->category }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Difficulty</dt>
                            <dd><span class="badge {{ $classSession->classPlan->getDifficultyBadgeClass() }} badge-soft badge-sm capitalize">{{ str_replace('_', ' ', $classSession->classPlan->difficulty_level) }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Duration</dt>
                            <dd>{{ $classSession->formatted_duration }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Capacity</dt>
                            <dd>{{ $classSession->capacity }} spots</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Session Price</dt>
                            <dd class="font-medium">{{ $classSession->formatted_price }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Created</dt>
                            <dd>{{ $classSession->created_at->format('M j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Recurrence Info --}}
                    @if($classSession->isRecurring())
                    <div class="border-t border-base-content/10 pt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--repeat] size-4 text-info"></span>
                            <span class="text-sm font-medium">Recurrence</span>
                            <span class="badge badge-soft badge-info badge-sm">Recurring</span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm">
                            @if($classSession->isRecurrenceParent())
                                <div>
                                    <span class="text-base-content/60">Sessions:</span>
                                    <span class="font-medium">{{ $classSession->recurrenceChildren->count() + 1 }} total</span>
                                </div>
                                @if($classSession->recurrenceChildren->isNotEmpty())
                                <div>
                                    <span class="text-base-content/60">Ends:</span>
                                    <span class="font-medium">{{ $classSession->recurrenceChildren->last()->start_time->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($classSession->recurrence_rule)
                                @php
                                    $rule = is_string($classSession->recurrence_rule) ? json_decode($classSession->recurrence_rule, true) : $classSession->recurrence_rule;
                                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    $selectedDays = isset($rule['days']) ? array_map(fn($d) => $days[$d], $rule['days']) : [];
                                @endphp
                                @if(!empty($selectedDays))
                                <div>
                                    <span class="text-base-content/60">Days:</span>
                                    <span class="font-medium">{{ implode(', ', $selectedDays) }}</span>
                                </div>
                                @endif
                                @endif
                            @else
                                <div>
                                    <span class="text-base-content/60">Parent:</span>
                                    <a href="{{ route('class-sessions.show', $classSession->recurrenceParent) }}" class="text-primary hover:underline">View series</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Cancellation Info --}}
                    @if($classSession->isCancelled())
                    <div class="border-t border-base-content/10 pt-4">
                        <div class="alert alert-error alert-soft">
                            <span class="icon-[tabler--x] size-5"></span>
                            <div>
                                <div class="font-medium">Cancelled on {{ $classSession->cancelled_at->format('M j, Y') }}</div>
                                @if($classSession->cancellation_reason)
                                    <p class="text-sm">{{ $classSession->cancellation_reason }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Instructors Section --}}
                    <div class="border-t border-base-content/10 pt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--users] size-4 text-primary"></span>
                            <span class="text-sm font-medium">Instructors</span>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            {{-- Primary Instructor --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                @if($classSession->primaryInstructor->photo_url)
                                <img src="{{ $classSession->primaryInstructor->photo_url }}" alt="{{ $classSession->primaryInstructor->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                <div class="avatar avatar-placeholder">
                                    <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                        {{ strtoupper(substr($classSession->primaryInstructor->name, 0, 1)) }}
                                    </div>
                                </div>
                                @endif
                                <div>
                                    <div class="font-medium text-sm">{{ $classSession->primaryInstructor->name }}</div>
                                    <div class="text-xs text-base-content/60">Primary</div>
                                </div>
                            </div>

                            {{-- Backup Instructors --}}
                            @foreach($classSession->backupInstructors as $index => $backupInstructor)
                            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                                @if($backupInstructor->photo_url)
                                <img src="{{ $backupInstructor->photo_url }}" alt="{{ $backupInstructor->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                <div class="avatar avatar-placeholder">
                                    <div class="bg-secondary text-secondary-content w-10 h-10 rounded-full text-sm font-bold">
                                        {{ strtoupper(substr($backupInstructor->name, 0, 1)) }}
                                    </div>
                                </div>
                                @endif
                                <div>
                                    <div class="font-medium text-sm">{{ $backupInstructor->name }}</div>
                                    <div class="text-xs text-base-content/60">Backup #{{ $index + 1 }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            @if($classSession->location)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Location</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="{{ $classSession->location->type_icon }} size-6 text-base-content/60 mt-0.5"></span>
                        <div>
                            <div class="font-medium">{{ $classSession->location->name }}</div>
                            {{-- Location Types --}}
                            @if($classSession->location->location_types && count($classSession->location->location_types) > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($classSession->location->location_types as $type)
                                <span class="badge badge-soft badge-xs {{ match($type) {
                                    'in_person' => 'badge-primary',
                                    'public' => 'badge-success',
                                    'virtual' => 'badge-info',
                                    'mobile' => 'badge-warning',
                                    default => 'badge-neutral',
                                } }}">{{ \App\Models\Location::getLocationTypeOptions()[$type] ?? $type }}</span>
                                @endforeach
                            </div>
                            @endif
                            {{-- Address --}}
                            @if($classSession->location->full_address && !$classSession->location->isVirtual())
                            <div class="text-sm text-base-content/60 mt-1">{{ $classSession->location->full_address }}</div>
                            @endif
                            {{-- Virtual Platform --}}
                            @if($classSession->location->isVirtual() && $classSession->location->virtual_platform)
                            <div class="text-sm text-base-content/60 mt-1">Platform: {{ $classSession->location->virtual_platform_label }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Room(s) for In-Person --}}
                    @if($classSession->room)
                    <div class="flex items-center gap-2 p-3 bg-base-200 rounded-lg">
                        <span class="icon-[tabler--door] size-5 text-base-content/60"></span>
                        <div>
                            <span class="font-medium">{{ $classSession->room->name }}</span>
                            <span class="text-sm text-base-content/60">(capacity: {{ $classSession->room->capacity }})</span>
                        </div>
                    </div>
                    @endif

                    {{-- Location Notes --}}
                    @if($classSession->location_notes)
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="text-sm font-medium text-base-content/60 mb-1">Location Notes</div>
                        <p class="text-sm whitespace-pre-wrap">{{ $classSession->location_notes }}</p>
                    </div>
                    @endif

                    {{-- Public Location Instructions --}}
                    @if($classSession->location->isPublic() && $classSession->location->public_location_notes)
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div>
                            <div class="font-medium text-sm">Meeting Instructions</div>
                            <p class="text-sm">{{ $classSession->location->public_location_notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Virtual Access Notes --}}
                    @if($classSession->location->isVirtual() && $classSession->location->virtual_access_notes)
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--video] size-5"></span>
                        <div>
                            <div class="font-medium text-sm">Access Notes</div>
                            <p class="text-sm">{{ $classSession->location->virtual_access_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($classSession->notes)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Internal Notes</h3>
                </div>
                <div class="card-body">
                    <p class="text-base-content/80 whitespace-pre-wrap">{{ $classSession->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Recurring Sessions --}}
            @if($classSession->isRecurrenceParent() && $classSession->recurrenceChildren->isNotEmpty())
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Recurring Sessions</h3>
                    <span class="badge badge-soft badge-primary badge-sm">{{ $classSession->recurrenceChildren->count() }} sessions</span>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classSession->recurrenceChildren->take(10) as $child)
                                <tr>
                                    <td>{{ $child->start_time->format('D, M j, Y') }}</td>
                                    <td>{{ $child->formatted_time_range }}</td>
                                    <td><span class="badge {{ $child->getStatusBadgeClass() }} badge-soft badge-xs capitalize">{{ $child->status }}</span></td>
                                    <td><a href="{{ route('class-sessions.show', $child) }}" class="btn btn-ghost btn-xs">View</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($classSession->recurrenceChildren->count() > 10)
                    <div class="p-3 text-center text-sm text-base-content/60">
                        And {{ $classSession->recurrenceChildren->count() - 10 }} more...
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Stats --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Booking Stats</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="text-center p-3 bg-base-200/50 rounded-lg">
                            <div class="text-2xl font-bold text-primary">{{ $confirmedBookings->count() }}</div>
                            <div class="text-xs text-base-content/60">Booked</div>
                        </div>
                        <div class="text-center p-3 bg-base-200/50 rounded-lg">
                            <div class="text-2xl font-bold text-success">{{ $checkedInCount }}</div>
                            <div class="text-xs text-base-content/60">Checked In</div>
                        </div>
                        <div class="text-center p-3 bg-base-200/50 rounded-lg">
                            <div class="text-2xl font-bold text-success">{{ $intakeCompleted }}</div>
                            <div class="text-xs text-base-content/60">Intake Done</div>
                        </div>
                        <div class="text-center p-3 bg-base-200/50 rounded-lg">
                            <div class="text-2xl font-bold {{ $intakePending > 0 ? 'text-warning' : 'text-base-content/30' }}">{{ $intakePending }}</div>
                            <div class="text-xs text-base-content/60">Intake Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bookings Section - Full Width --}}
    <div class="card bg-base-100">
        <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="card-title">Bookings</h3>
                <span class="badge badge-primary">{{ $confirmedBookings->count() }} confirmed</span>
                @if($cancelledBookings->count() > 0)
                    <span class="badge badge-error badge-soft">{{ $cancelledBookings->count() }} cancelled</span>
                @endif
            </div>
            @if($classSession->isPublished() && !$classSession->isPast())
                <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Booking
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            @if($allBookings->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--users-minus] size-12 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="font-semibold text-lg mb-2">No Bookings Yet</h3>
                    <p class="text-base-content/60 text-sm mb-4">No one has booked this class session yet.</p>
                    @if($classSession->isPublished() && !$classSession->isPast())
                        <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            Add Booking
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Intake</th>
                                <th class="text-center">Check In</th>
                                <th>Booked</th>
                                <th class="w-28">Actions</th>
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
                                                <x-avatar
                                                    :src="$booking->client->avatar_url ?? null"
                                                    :initials="$booking->client->initials ?? '?'"
                                                    :alt="$booking->client->full_name ?? 'Unknown'"
                                                    size="sm"
                                                />
                                                <div>
                                                    <a href="{{ route('clients.show', $booking->client) }}" class="font-medium hover:text-primary">
                                                        {{ $booking->client->full_name }}
                                                    </a>
                                                    @if($booking->client->email)
                                                        <div class="text-xs text-base-content/60">{{ $booking->client->email }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="avatar avatar-placeholder">
                                                    <div class="bg-base-300 w-8 h-8 rounded-full">
                                                        <span class="icon-[tabler--user] size-4"></span>
                                                    </div>
                                                </div>
                                                <span class="text-base-content/50">Unknown Client</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $booking->status_badge_class }} badge-sm">
                                            {{ \App\Models\Booking::getStatuses()[$booking->status] ?? $booking->status }}
                                        </span>
                                    </td>
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
                                            <span class="{{ $intakeIcons[$booking->intake_status] ?? 'icon-[tabler--minus] text-base-content/30' }} size-4" title="{{ $intakeStatuses[$booking->intake_status] ?? 'Unknown' }}"></span>
                                            <span class="text-xs text-base-content/60">{{ $intakeStatuses[$booking->intake_status] ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center" id="checkin-cell-{{ $booking->id }}">
                                        @if($booking->status === 'cancelled')
                                            <span class="text-base-content/30">-</span>
                                        @elseif($booking->isCheckedIn())
                                            <div class="flex items-center justify-center gap-1 text-success">
                                                <span class="icon-[tabler--circle-check-filled] size-5"></span>
                                                <span class="text-xs">{{ $booking->checked_in_at->format('g:i A') }}</span>
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
                                            @if($booking->status !== 'cancelled' && !$booking->isCheckedIn())
                                                <button
                                                    type="button"
                                                    class="btn btn-ghost btn-xs btn-square text-success hover:bg-success/10"
                                                    id="checkin-btn-{{ $booking->id }}"
                                                    onclick="checkInBooking({{ $booking->id }})"
                                                    title="Check In"
                                                >
                                                    <span class="icon-[tabler--login] size-4"></span>
                                                </button>
                                            @elseif($booking->isCheckedIn())
                                                <button
                                                    type="button"
                                                    class="btn btn-ghost btn-xs btn-square btn-disabled text-success"
                                                    disabled
                                                    title="Already Checked In"
                                                >
                                                    <span class="icon-[tabler--check] size-4"></span>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Booking" onclick="openDrawer('booking-{{ $booking->id }}', event)">
                                                <span class="icon-[tabler--eye] size-4"></span>
                                            </button>
                                            @if($booking->client)
                                                <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-ghost btn-xs btn-square" title="View Client">
                                                    <span class="icon-[tabler--user] size-4"></span>
                                                </a>
                                            @endif
                                            @if($booking->questionnaireResponses->where('status', 'completed')->isNotEmpty())
                                                <button
                                                    type="button"
                                                    class="btn btn-ghost btn-xs btn-square text-info hover:bg-info/10"
                                                    onclick="openDrawer('intake-{{ $booking->id }}', event)"
                                                    title="View Intake Form"
                                                >
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
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Drawer functions
function openDrawer(id, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('drawer-backdrop');

    if (drawer) {
        document.querySelectorAll('[id^="drawer-"]').forEach(d => {
            if (d.id !== 'drawer-backdrop' && d.id !== 'drawer-' + id) {
                d.classList.add('translate-x-full', 'hidden');
            }
        });

        if (backdrop) {
            backdrop.classList.remove('hidden');
        }

        drawer.classList.remove('hidden');
        setTimeout(() => {
            drawer.classList.remove('translate-x-full');
        }, 10);

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

    if (backdrop) {
        backdrop.classList.add('hidden');
    }

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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllDrawers();
    }
});

function checkInBooking(bookingId) {
    const btn = document.getElementById(`checkin-btn-${bookingId}`);
    const checkinCell = document.getElementById(`checkin-cell-${bookingId}`);
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch(`/schedule/check-in/${bookingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the check-in status cell
            if (checkinCell) {
                checkinCell.innerHTML = `
                    <div class="flex items-center justify-center gap-1 text-success">
                        <span class="icon-[tabler--circle-check-filled] size-5"></span>
                        <span class="text-xs">${data.checked_in_at}</span>
                    </div>
                `;
            }
            // Replace check-in button with disabled checkmark
            btn.outerHTML = `
                <button type="button" class="btn btn-ghost btn-xs btn-square btn-disabled text-success" disabled title="Already Checked In">
                    <span class="icon-[tabler--check] size-4"></span>
                </button>
            `;
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span>';
            alert(data.message || 'Failed to check in');
        }
    })
    .catch(error => {
        console.error('Error:', error);
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
        <div id="drawer-intake-{{ $booking->id }}" class="fixed inset-y-0 right-0 w-full max-w-xl bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden overflow-y-auto">
            <div class="sticky top-0 bg-base-100 border-b border-base-200 p-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-lg font-semibold">Intake Form Responses</h3>
                    <p class="text-sm text-base-content/60">{{ $booking->client?->full_name ?? 'Unknown Client' }}</p>
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
                                            @if($answer->question?->is_required)
                                                <span class="text-error">*</span>
                                            @endif
                                        </div>
                                        <div class="text-sm">
                                            @if($answer->answer)
                                                @if($answer->question?->type === 'checkbox' || $answer->question?->type === 'multi_select')
                                                    @php
                                                        $values = json_decode($answer->answer, true) ?? [$answer->answer];
                                                    @endphp
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach((array)$values as $value)
                                                            <span class="badge badge-soft badge-sm">{{ $value }}</span>
                                                        @endforeach
                                                    </div>
                                                @elseif($answer->question?->type === 'textarea' || $answer->question?->type === 'long_text')
                                                    <p class="whitespace-pre-wrap text-base-content/80">{{ $answer->answer }}</p>
                                                @elseif($answer->question?->type === 'date')
                                                    {{ \Carbon\Carbon::parse($answer->answer)->format('M j, Y') }}
                                                @elseif($answer->question?->type === 'signature')
                                                    <img src="{{ $answer->answer }}" alt="Signature" class="max-w-xs border border-base-300 rounded bg-white p-2">
                                                @else
                                                    {{ $answer->answer }}
                                                @endif
                                            @else
                                                <span class="text-base-content/40 italic">No answer provided</span>
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
<div id="cancel-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-sm">
        <div class="modal-content">
            <form action="{{ route('class-sessions.cancel', $classSession) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h3 class="modal-title">Cancel Session</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#cancel-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Are you sure you want to cancel this class session?</p>
                    <div>
                        <label class="label-text" for="cancellation_reason">Reason (optional)</label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="textarea w-full" placeholder="Enter a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-overlay="#cancel-modal">Keep Session</button>
                    <button type="submit" class="btn btn-error">Cancel Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
