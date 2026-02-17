@php
    $item = $entry['item'];
    $type = $entry['type'];
    $isClass = $type === 'class';
@endphp

@if($isClass)
    @php $session = $item; @endphp
    <div class="flex items-center gap-4 p-3 {{ $isPast ? 'bg-base-200/30 opacity-75' : 'bg-base-200/50' }} rounded-lg hover:bg-base-200 transition-colors schedule-item-row">
        {{-- Time --}}
        <div class="w-24 text-center shrink-0">
            <div class="font-semibold text-sm {{ $isPast ? 'text-base-content/60' : '' }}">{{ $session->start_time->format('g:i A') }}</div>
            <div class="text-xs text-base-content/60">{{ $session->formatted_duration }}</div>
        </div>

        {{-- Type Badge --}}
        <div class="shrink-0">
            <span class="badge badge-primary badge-sm">
                <span class="icon-[tabler--yoga] size-3 mr-1"></span>
                Class
            </span>
        </div>

        {{-- Details --}}
        <div class="flex-1 min-w-0">
            <div class="font-medium truncate {{ $isPast ? 'text-base-content/70' : '' }}">{{ $session->display_title }}</div>
            <div class="text-sm text-base-content/60 flex items-center gap-2 mt-0.5">
                @if($session->primaryInstructor)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--user] size-3"></span>
                        {{ $session->primaryInstructor->name }}
                    </span>
                @endif
                @if($session->room)
                    <span class="text-base-content/40">·</span>
                    <span>{{ $session->room->name }}</span>
                @endif
                @if($session->location)
                    <span class="text-base-content/40">·</span>
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--map-pin] size-3"></span>
                        {{ $session->location->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Capacity --}}
        <div class="text-center shrink-0">
            <div class="text-sm font-medium">
                {{ $session->confirmedBookings->count() }}/{{ $session->getEffectiveCapacity() }}
            </div>
            <div class="text-xs text-base-content/60">booked</div>
        </div>

        {{-- Status --}}
        <div class="shrink-0">
            <span class="badge {{ $session->getStatusBadgeClass() }} badge-sm status-badge">
                {{ \App\Models\ClassSession::getStatuses()[$session->status] ?? $session->status }}
            </span>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 shrink-0">
            {{-- Mark Complete Button (for past sessions that aren't completed) --}}
            @if($isPast && !$session->isCompleted() && !$session->isCancelled())
                <button type="button"
                        class="btn btn-ghost btn-xs btn-square text-success hover:bg-success/10"
                        title="Mark as Completed"
                        onclick="markComplete({{ $session->id }}, this)">
                    <span class="icon-[tabler--circle-check] size-4"></span>
                </button>
            @elseif($session->isCompleted())
                <button type="button"
                        class="btn btn-success btn-xs btn-square btn-disabled"
                        title="Completed"
                        disabled>
                    <span class="icon-[tabler--check] size-4"></span>
                </button>
            @endif

            <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Details" onclick="openDrawer('class-session-{{ $session->id }}', event)">
                <span class="icon-[tabler--eye] size-4"></span>
            </button>
            <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs btn-square" title="Full Details">
                <span class="icon-[tabler--external-link] size-4"></span>
            </a>
        </div>
    </div>
@else
    @php $slot = $item; @endphp
    <div class="flex items-center gap-4 p-3 {{ $isPast ? 'bg-base-200/30 opacity-75' : 'bg-base-200/50' }} rounded-lg hover:bg-base-200 transition-colors schedule-item-row">
        {{-- Time --}}
        <div class="w-24 text-center shrink-0">
            <div class="font-semibold text-sm {{ $isPast ? 'text-base-content/60' : '' }}">{{ $slot->start_time->format('g:i A') }}</div>
            <div class="text-xs text-base-content/60">{{ $slot->duration_minutes }} min</div>
        </div>

        {{-- Type Badge --}}
        <div class="shrink-0">
            <span class="badge badge-success badge-sm">
                <span class="icon-[tabler--massage] size-3 mr-1"></span>
                Service
            </span>
        </div>

        {{-- Details --}}
        <div class="flex-1 min-w-0">
            <div class="font-medium truncate {{ $isPast ? 'text-base-content/70' : '' }}">{{ $slot->servicePlan?->name ?? 'Service' }}</div>
            <div class="text-sm text-base-content/60 flex items-center gap-2 mt-0.5">
                @if($slot->instructor)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--user] size-3"></span>
                        {{ $slot->instructor->name }}
                    </span>
                @endif
                @if($slot->room)
                    <span class="text-base-content/40">·</span>
                    <span>{{ $slot->room->name }}</span>
                @endif
                @if($slot->location)
                    <span class="text-base-content/40">·</span>
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--map-pin] size-3"></span>
                        {{ $slot->location->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Client (if booked) --}}
        <div class="text-center shrink-0 w-24">
            @if($slot->status === \App\Models\ServiceSlot::STATUS_BOOKED && $slot->bookings->first()?->client)
                <div class="text-sm font-medium truncate">{{ $slot->bookings->first()->client->full_name }}</div>
                <div class="text-xs text-base-content/60">Client</div>
            @else
                <div class="text-sm text-base-content/40">-</div>
            @endif
        </div>

        {{-- Status --}}
        <div class="shrink-0">
            <span class="badge {{ $slot->getStatusBadgeClass() }} badge-sm status-badge">
                {{ \App\Models\ServiceSlot::getStatuses()[$slot->status] ?? $slot->status }}
            </span>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 shrink-0">
            <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Details" onclick="openDrawer('service-slot-{{ $slot->id }}', event)">
                <span class="icon-[tabler--eye] size-4"></span>
            </button>
            <a href="{{ route('service-slots.show', $slot) }}" class="btn btn-ghost btn-xs btn-square" title="Full Details">
                <span class="icon-[tabler--external-link] size-4"></span>
            </a>
        </div>
    </div>
@endif
