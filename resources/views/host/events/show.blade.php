@extends('layouts.dashboard')

@section('title', $event->title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('events.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Events</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ Str::limit($event->title, 30) }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            @if($event->cover_image)
                <img src="{{ $event->cover_image }}" alt="{{ $event->title }}" class="w-20 h-20 object-cover rounded-xl">
            @else
                <div class="w-20 h-20 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-event] size-10 text-primary"></span>
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
                    <span class="badge badge-{{ $event->status_color }}">{{ $event->status_label }}</span>
                </div>
                <div class="flex items-center gap-4 text-sm text-base-content/60">
                    <div class="flex items-center gap-1">
                        <span class="icon-[tabler--calendar] size-4"></span>
                        {{ $event->formatted_date }}
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="icon-[tabler--clock] size-4"></span>
                        {{ $event->formatted_time }}
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="icon-[tabler--{{ $event->event_type === 'online' ? 'device-laptop' : 'map-pin' }}] size-4"></span>
                        {{ $event->event_type_label }}
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($event->is_draft)
                <form action="{{ route('events.publish', $event) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success gap-2">
                        <span class="icon-[tabler--send] size-5"></span>
                        Publish Event
                    </button>
                </form>
            @endif
            <a href="{{ route('events.edit', $event) }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit Event
            </a>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost">
                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                </label>
                <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden">
                    @if($event->status !== 'cancelled')
                        <li>
                            <button type="button" onclick="document.getElementById('cancel-modal').showModal()" class="text-error">
                                <span class="icon-[tabler--calendar-x] size-4"></span>
                                Cancel Event
                            </button>
                        </li>
                    @endif
                    <li>
                        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                                Delete Event
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--users] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['total_registered'] }}</p>
                        <p class="text-xs text-base-content/60">Registered</p>
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
                        <p class="text-2xl font-bold">{{ $stats['attended'] }}</p>
                        <p class="text-xs text-base-content/60">Checked In</p>
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
                        <p class="text-2xl font-bold">{{ $stats['waitlist'] }}</p>
                        <p class="text-xs text-base-content/60">Waitlisted</p>
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
                        <p class="text-2xl font-bold">{{ $event->capacity ? $event->spots_remaining : '∞' }}</p>
                        <p class="text-xs text-base-content/60">Spots Left</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Event Details --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold text-lg mb-4">Event Details</h3>

                    <div class="space-y-4">
                        {{-- Date & Time --}}
                        <div class="flex items-start gap-3">
                            <div class="bg-primary/10 rounded-lg p-2">
                                <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                            </div>
                            <div>
                                <p class="font-medium">{{ $event->formatted_date }}</p>
                                <p class="text-sm text-base-content/60">{{ $event->formatted_time }}</p>
                            </div>
                        </div>

                        {{-- Location --}}
                        @if($event->event_type !== 'online')
                            <div class="flex items-start gap-3">
                                <div class="bg-success/10 rounded-lg p-2">
                                    <span class="icon-[tabler--map-pin] size-5 text-success"></span>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $event->venue_name ?: 'Location TBD' }}</p>
                                    @if($event->full_address)
                                        <p class="text-sm text-base-content/60">{{ $event->full_address }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($event->event_type !== 'in_person' && $event->online_url)
                            <div class="flex items-start gap-3">
                                <div class="bg-info/10 rounded-lg p-2">
                                    <span class="icon-[tabler--device-laptop] size-5 text-info"></span>
                                </div>
                                <div>
                                    <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $event->online_platform)) ?: 'Online' }}</p>
                                    <a href="{{ $event->online_url }}" target="_blank" class="text-sm text-primary hover:underline">Join Link</a>
                                </div>
                            </div>
                        @endif

                        {{-- Capacity --}}
                        <div class="flex items-start gap-3">
                            <div class="bg-warning/10 rounded-lg p-2">
                                <span class="icon-[tabler--users] size-5 text-warning"></span>
                            </div>
                            <div>
                                <p class="font-medium">{{ $event->capacity ?: 'Unlimited' }} Capacity</p>
                                <p class="text-sm text-base-content/60">{{ $event->skill_level_label }} &bull; {{ ucfirst($event->audience_type) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($event->description)
                        <div class="divider"></div>
                        <div>
                            <h4 class="font-medium mb-2">Description</h4>
                            <p class="text-sm text-base-content/70">{{ $event->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attendees --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-lg">Attendees</h3>
                        @if($event->canAddAttendees())
                            <a href="{{ route('walk-in.event', $event) }}" class="btn btn-primary btn-sm gap-2">
                                <span class="icon-[tabler--user-plus] size-4"></span>
                                Add Attendees
                            </a>
                        @endif
                    </div>

                    @if($event->attendees->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Attendee</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($event->attendees->sortByDesc('created_at') as $attendee)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar">
                                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                            @if($attendee->client->profile_photo)
                                                                <img src="{{ $attendee->client->profile_photo }}" alt="{{ $attendee->client->full_name }}">
                                                            @else
                                                                <span class="text-xs font-medium text-primary">{{ $attendee->client->initials }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('clients.show', $attendee->client) }}" class="font-medium hover:text-primary">
                                                            {{ $attendee->client->full_name }}
                                                        </a>
                                                        <p class="text-xs text-base-content/60">{{ $attendee->client->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm badge-{{ $attendee->status_color }}">{{ $attendee->status_label }}</span>
                                            </td>
                                            <td class="text-sm text-base-content/60">
                                                {{ $attendee->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($attendee->can_check_in)
                                                        <form action="{{ route('events.checkIn', [$event, $attendee]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-ghost btn-xs text-success" title="Check In">
                                                                <span class="icon-[tabler--user-check] size-4"></span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($attendee->can_cancel)
                                                        <form action="{{ route('events.removeClient', [$event, $attendee->client]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this attendee from the event?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-ghost btn-xs text-error" title="Remove">
                                                                <span class="icon-[tabler--x] size-4"></span>
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
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                                <span class="icon-[tabler--users] size-8 text-base-content/30"></span>
                            </div>
                            <p class="text-base-content/60">No attendees yet</p>
                            @if($event->canAddAttendees())
                                <a href="{{ route('walk-in.event', $event) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                                    <span class="icon-[tabler--user-plus] size-4"></span>
                                    Add Attendees
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Attendees Modal --}}
<dialog id="add-clients-modal" class="modal">
    <div class="modal-box max-w-lg">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4">Add Attendees to Event</h3>

        <form action="{{ route('events.addClients', $event) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <p class="text-sm text-base-content/60">Select attendees to add to this event:</p>

                <div class="max-h-64 overflow-y-auto space-y-2">
                    @foreach($availableClients as $client)
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 cursor-pointer">
                            <input type="checkbox" name="client_ids[]" value="{{ $client->id }}" class="checkbox checkbox-primary checkbox-sm">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="avatar">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        @if($client->profile_photo)
                                            <img src="{{ $client->profile_photo }}" alt="{{ $client->full_name }}">
                                        @else
                                            <span class="text-xs font-medium text-primary">{{ $client->initials }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ $client->full_name }}</p>
                                    <p class="text-xs text-base-content/60">{{ $client->email }}</p>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Cancel</button>
                    </form>
                    <button type="submit" class="btn btn-primary">Add Selected</button>
                </div>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Cancel Event Modal --}}
<dialog id="cancel-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg text-error mb-4">Cancel Event</h3>

        <form action="{{ route('events.cancel', $event) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <p class="text-sm text-base-content/60">Are you sure you want to cancel this event? All registered attendees will be notified.</p>

                <div>
                    <label class="block text-sm font-medium mb-2" for="cancellation_reason">Cancellation Reason (Optional)</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="textarea textarea-bordered w-full" placeholder="Let attendees know why the event was cancelled..."></textarea>
                </div>

                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Keep Event</button>
                    </form>
                    <button type="submit" class="btn btn-error">Cancel Event</button>
                </div>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection
