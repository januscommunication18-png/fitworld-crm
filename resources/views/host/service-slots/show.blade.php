@extends('layouts.dashboard')

@section('title', $serviceSlot->title ?? $serviceSlot->servicePlan?->name ?? 'Service Slot')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('service-slots.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Service Slots</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $serviceSlot->title ?? $serviceSlot->servicePlan?->name ?? 'Slot' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('service-slots.index') }}" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                </a>
                <h1 class="text-2xl font-bold">{{ $serviceSlot->title ?? $serviceSlot->servicePlan?->name ?? 'Service Slot' }}</h1>
                @php
                    $badgeClass = match($serviceSlot->status) {
                        'available' => 'badge-success',
                        'booked' => 'badge-info',
                        'blocked' => 'badge-neutral',
                        'draft' => 'badge-warning',
                        'cancelled' => 'badge-error',
                        default => 'badge-neutral',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} badge-soft capitalize">{{ $serviceSlot->status }}</span>
            </div>
            <p class="text-base-content/60">{{ $serviceSlot->start_time->format('l, F j, Y') }} &bull; {{ $serviceSlot->start_time->format('g:i A') }} - {{ $serviceSlot->end_time->format('g:i A') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($serviceSlot->isAvailable())
                <a href="{{ route('walk-in.service', $serviceSlot) }}" class="btn btn-success">
                    <span class="icon-[tabler--user-plus] size-5"></span>
                    Book Client
                </a>
            @endif
            <a href="{{ route('service-slots.edit', $serviceSlot) }}" class="btn btn-outline">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit
            </a>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">Slot Details</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Service</div>
                        <div class="font-medium">{{ $serviceSlot->servicePlan?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Instructor</div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4 text-base-content/50"></span>
                            <span class="font-medium">{{ $serviceSlot->instructor?->name ?? 'TBD' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Location</div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--map-pin] size-4 text-base-content/50"></span>
                            <span class="font-medium">{{ $serviceSlot->location?->name ?? '—' }}</span>
                        </div>
                    </div>
                    @if($serviceSlot->room)
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Room</div>
                        <div class="font-medium">{{ $serviceSlot->room->name }}</div>
                    </div>
                    @endif
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Date & Time</div>
                        <div class="font-medium">{{ $serviceSlot->start_time->format('l, F j, Y') }}</div>
                        <div class="text-sm text-base-content/70">{{ $serviceSlot->start_time->format('g:i A') }} - {{ $serviceSlot->end_time->format('g:i A') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Duration</div>
                        <div class="font-medium">{{ $serviceSlot->servicePlan?->duration_minutes ?? $serviceSlot->start_time->diffInMinutes($serviceSlot->end_time) }} minutes</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Price</div>
                        <div class="font-medium text-lg text-primary">${{ number_format($serviceSlot->price ?? $serviceSlot->servicePlan?->price ?? 0, 2) }}</div>
                    </div>
                    @if($serviceSlot->notes)
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Notes</div>
                        <div class="text-sm">{{ $serviceSlot->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Related Slots (if part of a recurring series) --}}
    @if($serviceSlot->recurrence_parent_id || $serviceSlot->recurrence_rule)
    @php
        $parentId = $serviceSlot->recurrence_parent_id ?? $serviceSlot->id;
        $relatedSlots = \App\Models\ServiceSlot::where(function($q) use ($parentId) {
                $q->where('recurrence_parent_id', $parentId)->orWhere('id', $parentId);
            })
            ->where('id', '!=', $serviceSlot->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(10)
            ->get();
    @endphp
    @if($relatedSlots->isNotEmpty())
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <span class="icon-[tabler--calendar-repeat] size-5 text-primary"></span>
                Upcoming in Series
            </h3>
            <span class="badge badge-soft badge-primary badge-sm">{{ $relatedSlots->count() }} slots</span>
        </div>
        <div class="card-body">
            <div class="space-y-2">
                @foreach($relatedSlots as $related)
                <a href="{{ route('service-slots.show', $related) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="text-center w-14">
                            <div class="text-xs text-base-content/50">{{ $related->start_time->format('D') }}</div>
                            <div class="font-bold text-sm">{{ $related->start_time->format('M d') }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-sm">{{ $related->start_time->format('g:i A') }} - {{ $related->end_time->format('g:i A') }}</div>
                            <div class="text-xs text-base-content/60">{{ $related->instructor?->name ?? 'TBD' }}</div>
                        </div>
                    </div>
                    @php
                        $relBadge = match($related->status) {
                            'available' => 'badge-success',
                            'booked' => 'badge-info',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <span class="badge {{ $relBadge }} badge-soft badge-sm capitalize">{{ $related->status }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
