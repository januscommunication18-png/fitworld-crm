{{-- Schedule Event Card Partial --}}
{{-- $item: ClassSession or ServiceSlot with schedule_* attributes --}}
@php
    $isClass = $item->schedule_type === 'class';
    $statusClass = $isClass ? $item->getStatusBadgeClass() : $item->getStatusBadgeClass();
    $statusLabel = $isClass ? \App\Models\ClassSession::getStatuses()[$item->status] ?? $item->status : \App\Models\ServiceSlot::getStatuses()[$item->status] ?? $item->status;
@endphp

<div class="flex items-start gap-4 p-4 bg-base-100 rounded-lg border border-base-200 hover:border-base-300 transition-colors {{ $item->has_conflict ? 'border-l-4 border-l-error' : '' }}">
    {{-- Time Column --}}
    <div class="flex-shrink-0 w-20 text-center">
        <div class="text-lg font-bold">{{ $item->start_time->format('g:i') }}</div>
        <div class="text-xs text-base-content/60">{{ $item->start_time->format('A') }}</div>
        <div class="text-xs text-base-content/40 mt-1">
            {{ $item->start_time->diffInMinutes($item->end_time) }} min
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div>
                {{-- Title Row --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Type Badge --}}
                    @if($isClass)
                        <span class="badge badge-soft badge-primary badge-xs">Class</span>
                    @else
                        <span class="badge badge-soft badge-secondary badge-xs">Service</span>
                    @endif

                    {{-- Title --}}
                    <a href="{{ $isClass ? route('class-sessions.show', $item) : route('service-slots.show', $item) }}"
                       class="font-semibold hover:text-primary truncate">
                        {{ $item->schedule_title }}
                    </a>

                    {{-- Status Badge --}}
                    <span class="badge badge-soft {{ $statusClass }} badge-xs">{{ $statusLabel }}</span>

                    {{-- Conflict Warning --}}
                    @if($item->has_conflict)
                        <span class="tooltip tooltip-error" data-tip="Schedule conflict detected">
                            <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                        </span>
                    @endif
                </div>

                {{-- Meta Row --}}
                <div class="flex items-center gap-3 mt-1 text-sm text-base-content/60">
                    {{-- Instructor --}}
                    @if($item->schedule_instructor)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--user] size-3.5"></span>
                            {{ $item->schedule_instructor->name }}
                        </span>
                    @endif

                    {{-- Room --}}
                    @if($item->room)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--door] size-3.5"></span>
                            {{ $item->room->name }}
                        </span>
                    @endif

                    {{-- Price --}}
                    @if($item->getEffectivePrice())
                        <span class="flex items-center gap-1 text-success">
                            <span class="icon-[tabler--currency-dollar] size-3.5"></span>
                            {{ number_format($item->getEffectivePrice(), 2) }}
                        </span>
                    @endif

                    {{-- Capacity (classes only) --}}
                    @if($isClass)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-3.5"></span>
                            0/{{ $item->getEffectiveCapacity() }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                {{-- View --}}
                <a href="{{ $isClass ? route('class-sessions.show', $item) : route('service-slots.show', $item) }}"
                   class="btn btn-ghost btn-xs btn-circle"
                   title="View">
                    <span class="icon-[tabler--eye] size-4"></span>
                </a>

                {{-- Edit --}}
                <a href="{{ $isClass ? route('class-sessions.edit', $item) : route('service-slots.edit', $item) }}"
                   class="btn btn-ghost btn-xs btn-circle"
                   title="Edit">
                    <span class="icon-[tabler--edit] size-4"></span>
                </a>

                {{-- More Actions Dropdown --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs btn-circle">
                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                    </div>
                    <ul tabindex="0" class="dropdown-menu dropdown-menu-sm w-40">
                        @if($isClass)
                            @if($item->status === 'draft')
                                <li>
                                    <form method="POST" action="{{ route('class-sessions.publish', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item text-success">
                                            <span class="icon-[tabler--check] size-4"></span>
                                            Publish
                                        </button>
                                    </form>
                                </li>
                            @elseif($item->status === 'published')
                                <li>
                                    <form method="POST" action="{{ route('class-sessions.unpublish', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            <span class="icon-[tabler--eye-off] size-4"></span>
                                            Unpublish
                                        </button>
                                    </form>
                                </li>
                            @endif
                            @if($item->status !== 'cancelled')
                                <li>
                                    <form method="POST" action="{{ route('class-sessions.cancel', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item text-error"
                                                onclick="return confirm('Are you sure you want to cancel this session?')">
                                            <span class="icon-[tabler--x] size-4"></span>
                                            Cancel
                                        </button>
                                    </form>
                                </li>
                            @endif
                        @else
                            {{-- Service slot actions --}}
                            @if($item->status === 'available')
                                <li>
                                    <button type="button" class="dropdown-item">
                                        <span class="icon-[tabler--lock] size-4"></span>
                                        Block Slot
                                    </button>
                                </li>
                            @endif
                            @if($item->status !== 'cancelled')
                                <li>
                                    <form method="POST" action="{{ route('service-slots.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-error"
                                                onclick="return confirm('Are you sure you want to delete this slot?')">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                            Delete
                                        </button>
                                    </form>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
