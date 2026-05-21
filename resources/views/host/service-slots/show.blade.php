@extends('layouts.dashboard')

@section('title', $serviceSlot->title ?? $serviceSlot->servicePlan?->name ?? 'Service Slot')

@php
    $displayTitle = $serviceSlot->title ?? $serviceSlot->servicePlan?->name ?? 'Service Slot';
    $isRecurring = (bool) ($serviceSlot->recurrence_rule || $serviceSlot->recurrence_parent_id);
    $isPast = $serviceSlot->end_time && $serviceSlot->end_time->isPast();
    $headerImage = $serviceSlot->servicePlan?->image_url ?? null;
    $headerColor = $serviceSlot->servicePlan?->color ?? '#8b5cf6';
@endphp

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('service-slots.index') }}"><span class="icon-[tabler--massage] me-1 size-4"></span> {{ $trans['schedule.service_slots'] ?? 'Service Slots' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $displayTitle }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if($headerImage)
                <img src="{{ $headerImage }}" alt="{{ $displayTitle }}" class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $headerColor }}20;">
                    <span class="icon-[tabler--massage] size-10" style="color: {{ $headerColor }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $displayTitle }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $serviceSlot->getStatusBadgeClass() }} badge-soft capitalize">{{ $serviceSlot->status }}</span>
                    @if($serviceSlot->servicePlan?->category)
                        <span class="badge badge-soft badge-primary badge-sm capitalize">{{ $serviceSlot->servicePlan->category }}</span>
                    @endif
                    @if($serviceSlot->servicePlan?->location_type)
                        <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ str_replace('_', ' ', $serviceSlot->servicePlan->location_type) }}</span>
                    @endif
                    @if($isRecurring)
                        <span class="badge badge-soft badge-info badge-sm">{{ $trans['schedule.recurring'] ?? 'Recurring' }}</span>
                    @endif
                </div>
                <p class="text-base-content/60 mt-1 text-sm">
                    {{ $serviceSlot->start_time->format('l, F j, Y') }} &bull;
                    {{ $serviceSlot->start_time->format('g:i A') }} - {{ $serviceSlot->end_time->format('g:i A') }} &bull;
                    {{ $serviceSlot->duration_minutes }} min
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($serviceSlot->isAvailable() && auth()->user()->hasPermission('bookings.create'))
                <a href="{{ route('walk-in.service', $serviceSlot) }}" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    {{ $trans['schedule.add_booking'] ?? 'Add Booking' }}
                </a>
            @endif
            @if(auth()->user()->hasPermission('schedule.edit'))
                <a href="{{ route('service-slots.edit', $serviceSlot) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </a>
            @endif
            <a href="{{ route('service-slots.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span> {{ $trans['btn.back'] ?? 'Back' }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab tab-active" data-tab="booking" role="tab">
            <span class="icon-[tabler--user] size-4 mr-2"></span>{{ $trans['nav.bookings'] ?? 'Booking' }}
            @if($confirmedBookings->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $confirmedBookings->count() }}</span>
            @endif
        </button>
        <button class="tab" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>{{ $trans['common.overview'] ?? 'Overview' }}
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
                                    <span class="icon-[tabler--user] size-6 text-primary"></span>
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

                {{-- Slot Details --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            {{ $trans['schedule.slot_details'] ?? 'Slot Details' }}
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['common.date'] ?? 'Date' }}</label>
                                <p class="font-medium">{{ $serviceSlot->start_time->format('l, F j, Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['common.time'] ?? 'Time' }}</label>
                                <p class="font-medium">{{ $serviceSlot->start_time->format('g:i A') }} - {{ $serviceSlot->end_time->format('g:i A') }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['field.duration'] ?? 'Duration' }}</label>
                                <p class="font-medium">{{ $serviceSlot->duration_minutes }} min</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['field.service'] ?? 'Service' }}</label>
                                <p class="font-medium">{{ $serviceSlot->servicePlan?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['common.price'] ?? 'Price' }}</label>
                                <p class="font-medium">{{ $serviceSlot->formatted_price }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['common.status'] ?? 'Status' }}</label>
                                <p class="mt-0.5"><span class="badge {{ $serviceSlot->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $serviceSlot->status }}</span></p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['common.created'] ?? 'Created' }}</label>
                                <p class="font-medium">{{ $serviceSlot->created_at->format('M j, Y') }}</p>
                            </div>
                            @if($serviceSlot->servicePlan?->max_participants > 1)
                            <div>
                                <label class="text-sm text-base-content/60">{{ $trans['field.capacity'] ?? 'Capacity' }}</label>
                                <p class="font-medium">{{ $serviceSlot->servicePlan->max_participants }} {{ $trans['common.spots'] ?? 'spots' }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Assigned Instructor --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--user] size-5"></span>
                            {{ $trans['field.instructor'] ?? 'Instructor' }}
                        </h2>

                        @if(!$serviceSlot->instructor)
                            <p class="mt-4 text-base-content/40 italic text-sm">No instructor assigned.</p>
                        @else
                        @php
                            $inst = $serviceSlot->instructor;
                            $instUser = $inst->user;
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                            <div class="flex items-center gap-3 p-3 bg-primary/5 border border-primary/20 rounded-xl">
                                @if($instUser?->profile_photo_url)
                                    <img src="{{ $instUser->profile_photo_url }}" alt="{{ $inst->name }}" class="w-10 h-10 rounded-full object-cover">
                                @elseif($inst->photo_url)
                                    <img src="{{ $inst->photo_url }}" alt="{{ $inst->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center font-bold text-sm text-primary">
                                        {{ strtoupper(substr($inst->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm truncate">{{ $inst->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="badge badge-soft badge-primary badge-xs">{{ $trans['common.assigned'] ?? 'Assigned' }}</span>
                                        @if($instUser)
                                            <span class="text-xs text-base-content/50">{{ $instUser->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Location --}}
                @if($serviceSlot->location)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--map-pin] size-5"></span>
                            {{ $trans['field.location'] ?? 'Location' }}
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <label class="text-sm text-base-content/60">Venue</label>
                                <p class="font-medium">{{ $serviceSlot->location->name }}</p>
                            </div>
                            @if($serviceSlot->room)
                            <div>
                                <label class="text-sm text-base-content/60">Room</label>
                                <p class="font-medium">{{ $serviceSlot->room->name }}</p>
                            </div>
                            @endif
                            @if(!empty($serviceSlot->location->full_address) && !$serviceSlot->location->isVirtual())
                            <div class="col-span-2">
                                <label class="text-sm text-base-content/60">Address</label>
                                <p class="font-medium">{{ $serviceSlot->location->full_address }}</p>
                            </div>
                            @endif
                            @if($serviceSlot->location->isVirtual() && $serviceSlot->location->virtual_platform)
                            <div>
                                <label class="text-sm text-base-content/60">Platform</label>
                                <p class="font-medium">{{ $serviceSlot->location->virtual_platform_label }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Recurrence --}}
                @if($isRecurring)
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
                                $seriesAnchor = $serviceSlot->recurrence_parent_id
                                    ? ($serviceSlot->recurrenceParent ?? $serviceSlot)
                                    : $serviceSlot;
                            @endphp
                            <a href="{{ route('schedule-planner.show', $seriesAnchor->id) }}" class="btn btn-sm btn-outline btn-primary shrink-0">
                                <span class="icon-[tabler--calendar-event] size-4"></span>
                                {{ $trans['schedule.view_series'] ?? 'View Series' }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Cancellation Info --}}
                @if($serviceSlot->isCancelled())
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="alert alert-error alert-soft">
                            <span class="icon-[tabler--x] size-5"></span>
                            <div>
                                <div class="font-medium">{{ $trans['schedule.cancelled_on'] ?? 'Cancelled on' }} {{ $serviceSlot->cancelled_at?->format('M j, Y') ?? '—' }}</div>
                                @if($serviceSlot->cancellation_reason)
                                    <p class="text-sm">{{ $serviceSlot->cancellation_reason }}</p>
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
                        @if($serviceSlot->notes)
                            <p class="mt-2 whitespace-pre-line">{{ $serviceSlot->notes }}</p>
                        @else
                            <p class="mt-2 text-base-content/40 italic">No notes added.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Booking Tab --}}
        <div class="tab-content active" data-content="booking">
            <div class="space-y-6">

                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary/10 rounded-lg p-2">
                                    <span class="icon-[tabler--user] size-6 text-primary"></span>
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
                        <span class="icon-[tabler--user-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">{{ $trans['schedule.no_booking_yet'] ?? 'No Booking Yet' }}</h3>
                        <p class="text-base-content/60 mb-4">{{ $trans['schedule.no_one_booked_slot'] ?? 'No client has booked this slot yet.' }}</p>
                        @if($serviceSlot->isAvailable() && auth()->user()->hasPermission('bookings.create'))
                            <a href="{{ route('walk-in.service', $serviceSlot) }}" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--user-plus] size-4"></span>
                                {{ $trans['schedule.add_booking'] ?? 'Add Booking' }}
                            </a>
                        @endif
                    </div>
                </div>
                @else
                    @php
                        $intakeStatuses = \App\Models\Booking::getIntakeStatuses();
                        $intakeIcons = [
                            'completed' => 'icon-[tabler--circle-check-filled] text-success',
                            'pending' => 'icon-[tabler--clock] text-warning',
                            'waived' => 'icon-[tabler--circle-minus] text-info',
                            'not_required' => 'icon-[tabler--minus] text-base-content/30',
                        ];
                    @endphp
                    @foreach($allBookings->sortByDesc('created_at') as $b)
                        {{-- Single Booking Detail Card (services are typically 1-on-1) --}}
                        <div class="card bg-base-100 {{ $b->status === 'cancelled' ? 'opacity-70' : '' }}">
                            <div class="card-body space-y-5">
                                {{-- Client --}}
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <div class="flex items-center gap-4">
                                        @if($b->client)
                                            <x-avatar :src="$b->client->avatar_url ?? null" :initials="$b->client->initials ?? '?'" :alt="$b->client->full_name ?? 'Unknown'" size="lg" />
                                            <div>
                                                <a href="{{ route('clients.show', $b->client) }}" class="text-lg font-semibold hover:text-primary">{{ $b->client->full_name }}</a>
                                                @if($b->client->email)
                                                    <div class="text-sm text-base-content/60">{{ $b->client->email }}</div>
                                                @endif
                                                @if($b->client->phone ?? null)
                                                    <div class="text-sm text-base-content/60">{{ $b->client->phone }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-base-content/50">{{ $trans['bookings.unknown_client'] ?? 'Unknown Client' }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="badge {{ $b->status_badge_class }} badge-soft capitalize">{{ \App\Models\Booking::getStatuses()[$b->status] ?? $b->status }}</span>
                                        @if($b->isCheckedIn())
                                            <span class="badge badge-success badge-soft gap-1">
                                                <span class="icon-[tabler--circle-check-filled] size-3"></span>
                                                {{ $trans['bookings.checked_in'] ?? 'Checked In' }} {{ $b->checked_in_at->format('g:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Booking Meta --}}
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-xs text-base-content/60">{{ $trans['bookings.payment'] ?? 'Payment' }}</label>
                                        @if($b->price_paid !== null)
                                            <p class="font-semibold text-success">${{ number_format($b->price_paid, 2) }}</p>
                                            @if($b->payment_method)
                                                <p class="text-xs text-base-content/50 capitalize">{{ $b->payment_method }}</p>
                                            @endif
                                        @else
                                            <p class="text-base-content/40">—</p>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="text-xs text-base-content/60">{{ $trans['schedule.intake'] ?? 'Intake' }}</label>
                                        <p class="flex items-center gap-1.5 font-medium text-sm">
                                            <span class="{{ $intakeIcons[$b->intake_status] ?? 'icon-[tabler--minus] text-base-content/30' }} size-4"></span>
                                            <span>{{ $intakeStatuses[$b->intake_status] ?? '—' }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-base-content/60">{{ $trans['schedule.check_in'] ?? 'Check In' }}</label>
                                        @if($b->status === 'cancelled')
                                            <p class="text-base-content/40">—</p>
                                        @elseif($b->isCheckedIn())
                                            <p class="font-medium text-sm text-success">{{ $b->checked_in_at->format('g:i A') }}</p>
                                        @else
                                            <p class="text-base-content/40 text-sm">{{ $trans['schedule.not_checked_in'] ?? 'Not yet' }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="text-xs text-base-content/60">{{ $trans['schedule.booked'] ?? 'Booked' }}</label>
                                        <p class="font-medium text-sm">{{ $b->created_at->format('M j, Y') }}</p>
                                        <p class="text-xs text-base-content/60">{{ $b->created_at->format('g:i A') }}</p>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 flex-wrap pt-2 border-t border-base-200">
                                    @if($b->status !== 'cancelled' && !$b->isCheckedIn() && (auth()->user()->hasPermission('bookings.attendance') || auth()->user()->hasPermission('bookings.attendance_own')))
                                        <button type="button" class="btn btn-success btn-sm" id="checkin-btn-{{ $b->id }}" onclick="checkInBooking({{ $b->id }})">
                                            <span class="icon-[tabler--login] size-4"></span>
                                            {{ $trans['schedule.check_in'] ?? 'Check In' }}
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openDrawer('booking-{{ $b->id }}', event)">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                        {{ $trans['schedule.view_booking'] ?? 'View Booking' }}
                                    </button>
                                    @if($b->client)
                                        <a href="{{ route('clients.show', $b->client) }}" class="btn btn-ghost btn-sm">
                                            <span class="icon-[tabler--user] size-4"></span>
                                            {{ $trans['bookings.view_client'] ?? 'View Client' }}
                                        </a>
                                    @endif
                                    @if($b->questionnaireResponses->where('status', 'completed')->isNotEmpty())
                                        <button type="button" class="btn btn-ghost btn-sm text-info" onclick="openDrawer('intake-{{ $b->id }}', event)">
                                            <span class="icon-[tabler--file-text] size-4"></span>
                                            {{ $trans['schedule.view_intake_form'] ?? 'Intake' }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
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
        });
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
            window.location.reload();
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span> {{ $trans['schedule.check_in'] ?? 'Check In' }}';
            alert(data.message || 'Failed to check in');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span> {{ $trans['schedule.check_in'] ?? 'Check In' }}';
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAllDrawers()"></div>

{{-- Booking Drawers --}}
@foreach($allBookings as $b)
    @include('host.bookings.partials.drawer', ['booking' => $b])
@endforeach

{{-- Intake Form Drawers --}}
@foreach($allBookings as $b)
    @if($b->questionnaireResponses->where('status', 'completed')->isNotEmpty())
        <div id="drawer-intake-{{ $b->id }}" class="fixed inset-y-0 right-0 w-full max-w-3xl bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden overflow-y-auto">
            <div class="sticky top-0 bg-base-100 border-b border-base-200 p-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-lg font-semibold">{{ $trans['schedule.intake_form_responses'] ?? 'Intake Form Responses' }}</h3>
                    <p class="text-sm text-base-content/60">{{ $b->client?->full_name ?? ($trans['bookings.unknown_client'] ?? 'Unknown Client') }}</p>
                </div>
                <button type="button" onclick="closeDrawer('intake-{{ $b->id }}')" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <div class="p-4 space-y-6">
                @foreach($b->questionnaireResponses->where('status', 'completed') as $response)
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
@endsection
