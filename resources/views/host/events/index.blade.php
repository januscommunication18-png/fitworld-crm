@extends('layouts.dashboard')

@section('title', 'Events')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Events</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Events</h1>
            <p class="text-base-content/60 mt-1">Manage your studio's events and workshops.</p>
        </div>
        <a href="{{ route('events.create') }}" class="btn btn-primary gap-2">
            <span class="icon-[tabler--plus] size-5"></span>
            Create Event
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--calendar-event] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['all'] }}</p>
                        <p class="text-xs text-base-content/60">Total Events</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--circle-check] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['published'] }}</p>
                        <p class="text-xs text-base-content/60">Published</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--clock] size-6 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['upcoming'] }}</p>
                        <p class="text-xs text-base-content/60">Upcoming</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--pencil] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['draft'] }}</p>
                        <p class="text-xs text-base-content/60">Drafts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <a href="{{ route('events.index') }}"
           class="btn btn-sm {{ !request('status') && !request('filter') ? 'btn-primary' : 'btn-ghost' }} rounded-full">
            All
        </a>
        <a href="{{ route('events.index', ['status' => 'draft']) }}"
           class="btn btn-sm {{ request('status') === 'draft' ? 'btn-primary' : 'btn-ghost' }} rounded-full">
            Draft
        </a>
        <a href="{{ route('events.index', ['status' => 'published']) }}"
           class="btn btn-sm {{ request('status') === 'published' ? 'btn-primary' : 'btn-ghost' }} rounded-full">
            Published
        </a>
        <a href="{{ route('events.index', ['filter' => 'upcoming']) }}"
           class="btn btn-sm {{ request('filter') === 'upcoming' ? 'btn-primary' : 'btn-ghost' }} rounded-full">
            Upcoming
        </a>
        <a href="{{ route('events.index', ['filter' => 'past']) }}"
           class="btn btn-sm {{ request('filter') === 'past' ? 'btn-primary' : 'btn-ghost' }} rounded-full">
            Past
        </a>
    </div>

    @if($events->count() > 0)
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="group card bg-base-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                    {{-- Event Image with Overlay --}}
                    <div class="relative h-36 bg-gradient-to-br from-primary/20 to-secondary/20 overflow-hidden">
                        @if($event->cover_image)
                            <img src="{{ $event->cover_image }}" alt="{{ $event->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="icon-[tabler--calendar-event] size-16 text-base-content/10"></span>
                            </div>
                        @endif

                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        {{-- Date Badge --}}
                        <div class="absolute top-3 left-3">
                            <div class="bg-base-100 rounded-lg px-2 py-1.5 text-center shadow-lg min-w-[44px]">
                                <div class="text-[10px] font-bold text-primary uppercase leading-none">{{ $event->start_datetime->format('M') }}</div>
                                <div class="text-lg font-bold leading-none mt-0.5">{{ $event->start_datetime->format('d') }}</div>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="absolute top-3 right-3">
                            @if($event->status === 'draft')
                                <span class="badge badge-sm bg-warning/90 text-warning-content border-0 backdrop-blur-sm">Draft</span>
                            @elseif($event->status === 'published')
                                <span class="badge badge-sm bg-success/90 text-success-content border-0 backdrop-blur-sm">Live</span>
                            @elseif($event->status === 'cancelled')
                                <span class="badge badge-sm bg-error/90 text-error-content border-0 backdrop-blur-sm">Cancelled</span>
                            @else
                                <span class="badge badge-sm badge-{{ $event->status_color }} border-0">{{ $event->status_label }}</span>
                            @endif
                        </div>

                        {{-- Bottom Info --}}
                        <div class="absolute bottom-2 left-3 right-3 flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-white/90 text-xs">
                                <span class="icon-[tabler--clock] size-3.5"></span>
                                <span>{{ $event->start_datetime->format('g:i A') }}</span>
                            </div>
                            <span class="badge badge-xs bg-black/30 text-white border-0 backdrop-blur-sm">
                                {{ $event->event_type_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body p-4">
                        {{-- Title --}}
                        <a href="{{ route('events.show', $event) }}"
                           class="block font-semibold text-base mb-1 line-clamp-1 hover:text-primary transition-colors">
                            {{ $event->title }}
                        </a>

                        {{-- Location --}}
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-3">
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if($event->event_type !== 'online')
                                    <span class="icon-[tabler--map-pin] size-3.5 shrink-0"></span>
                                    <span class="truncate">
                                        {{ $event->venue_name ?: ($event->city ? $event->city . ', ' . $event->state : 'Location TBD') }}
                                    </span>
                                @else
                                    <span class="icon-[tabler--device-laptop] size-3.5 shrink-0"></span>
                                    <span>{{ ucfirst(str_replace('_', ' ', $event->online_platform)) ?: 'Online Event' }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Registration Count --}}
                        <div class="flex items-center justify-between text-xs mb-3">
                            <div class="flex items-center gap-1 text-base-content/60">
                                <span class="icon-[tabler--users] size-3.5"></span>
                                <span class="font-medium text-base-content">{{ $event->registered_attendees_count }}</span>
                                @if($event->capacity)
                                    <span>/ {{ $event->capacity }}</span>
                                @endif
                                <span>registered</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-primary flex-1">
                                <span class="icon-[tabler--eye] size-4"></span>
                                View
                            </a>
                            <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-ghost">
                                <span class="icon-[tabler--edit] size-4"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="card bg-base-100">
            <div class="card-body items-center text-center py-16">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-4">
                    <span class="icon-[tabler--calendar-event] size-10 text-base-content/30"></span>
                </div>
                <h3 class="text-lg font-semibold">No events yet</h3>
                <p class="text-base-content/60 mb-4">Create your first event to start engaging with your clients.</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create Event
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
