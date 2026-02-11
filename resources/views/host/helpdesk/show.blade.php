@extends('layouts.dashboard')

@section('title', 'Ticket #' . $ticket->id)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('helpdesk.index') }}"><span class="icon-[tabler--help] me-1 size-4"></span> Help Desk</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Ticket #{{ $ticket->id }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-4"></span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">{{ $ticket->subject ?? 'No Subject' }}</h1>
                    @php
                        $statusColors = [
                            'open' => 'badge-info',
                            'in_progress' => 'badge-warning',
                            'customer_reply' => 'badge-primary',
                            'resolved' => 'badge-success',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$ticket->status] ?? 'badge-ghost' }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                <p class="text-base-content/60 mt-1">Ticket #{{ $ticket->id }} &middot; Created {{ $ticket->created_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(!$ticket->client_id)
                <form action="{{ route('helpdesk.convert', $ticket) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        Convert to Client
                    </button>
                </form>
            @else
                <a href="{{ route('clients.show', $ticket->client_id) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--user] size-4"></span>
                    View Client
                </a>
            @endif
            <form action="{{ route('helpdesk.destroy', $ticket) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm text-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Initial Message --}}
            @if($ticket->message)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-primary/10 text-primary rounded-full w-10 h-10">
                                    <span class="text-sm">{{ strtoupper(substr($ticket->name, 0, 2)) }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $ticket->name }}</span>
                                    <span class="text-xs text-base-content/40">&middot;</span>
                                    <span class="text-xs text-base-content/60">{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div class="mt-2 prose prose-sm max-w-none">
                                    {!! nl2br(e($ticket->message)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Message Thread --}}
            @if($ticket->messages->count() > 0)
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content/70">Conversation</h3>
                    @foreach($ticket->messages as $message)
                        <div class="card bg-base-100 {{ $message->is_staff_message ? 'border-l-4 border-primary' : '' }}">
                            <div class="card-body">
                                <div class="flex items-start gap-3">
                                    <div class="avatar placeholder">
                                        @if($message->is_staff_message)
                                            <div class="bg-primary text-primary-content rounded-full w-10 h-10">
                                                <span class="text-sm">{{ strtoupper(substr($message->sender_name, 0, 2)) }}</span>
                                            </div>
                                        @elseif($message->is_system_message)
                                            <div class="bg-base-200 text-base-content rounded-full w-10 h-10">
                                                <span class="icon-[tabler--robot] size-5"></span>
                                            </div>
                                        @else
                                            <div class="bg-base-200 text-base-content rounded-full w-10 h-10">
                                                <span class="text-sm">{{ strtoupper(substr($message->sender_name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ $message->sender_name }}</span>
                                            @if($message->is_staff_message)
                                                <span class="badge badge-xs badge-primary">Staff</span>
                                            @elseif($message->is_system_message)
                                                <span class="badge badge-xs badge-ghost">System</span>
                                            @endif
                                            <span class="text-xs text-base-content/40">&middot;</span>
                                            <span class="text-xs text-base-content/60">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                        <div class="mt-2 prose prose-sm max-w-none">
                                            {!! nl2br(e($message->message)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Reply Form --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Add Reply</h3>
                    <form action="{{ route('helpdesk.reply', $ticket) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <textarea name="message" rows="4" class="textarea w-full" placeholder="Type your reply..." required></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon-[tabler--send] size-4"></span>
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Contact Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-primary/10 text-primary rounded-full w-12 h-12">
                                    <span class="text-lg">{{ strtoupper(substr($ticket->name, 0, 2)) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium">{{ $ticket->name }}</p>
                                @if($ticket->client)
                                    <a href="{{ route('clients.show', $ticket->client_id) }}" class="text-xs text-primary hover:underline">
                                        View client profile
                                    </a>
                                @else
                                    <span class="text-xs text-base-content/60">Not a client</span>
                                @endif
                            </div>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="icon-[tabler--mail] size-4 text-base-content/50"></span>
                            <a href="mailto:{{ $ticket->email }}" class="hover:text-primary">{{ $ticket->email }}</a>
                        </div>
                        @if($ticket->phone)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="icon-[tabler--phone] size-4 text-base-content/50"></span>
                                <a href="tel:{{ $ticket->phone }}" class="hover:text-primary">{{ $ticket->phone }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ticket Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Ticket Details</h3>
                    <form action="{{ route('helpdesk.update', $ticket) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="label-text" for="status">Status</label>
                            <select id="status" name="status" class="select w-full" onchange="this.form.submit()">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label-text" for="assigned_user_id">Assigned To</label>
                            <select id="assigned_user_id" name="assigned_user_id" class="select w-full" onchange="this.form.submit()">
                                <option value="">Unassigned</option>
                                @foreach($teamMembers as $member)
                                    <option value="{{ $member->id }}" {{ $ticket->assigned_user_id == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <div class="divider my-2"></div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Source</span>
                            <span>{{ $ticket->source_label }}</span>
                        </div>
                        @if($ticket->servicePlan)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Service</span>
                                <span>{{ $ticket->servicePlan->name }}</span>
                            </div>
                        @endif
                        @if($ticket->preferred_date)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Preferred Date</span>
                                <span>{{ $ticket->preferred_date->format('M j, Y') }}</span>
                            </div>
                        @endif
                        @if($ticket->preferred_time)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Preferred Time</span>
                                <span>{{ \Carbon\Carbon::parse($ticket->preferred_time)->format('g:i A') }}</span>
                            </div>
                        @endif
                        @if($ticket->source_url)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Source URL</span>
                                <span class="truncate max-w-[150px]" title="{{ $ticket->source_url }}">{{ $ticket->source_url }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tags --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Tags</h3>
                    <form action="{{ route('helpdesk.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                           class="peer hidden"
                                           {{ $ticket->tags->contains($tag->id) ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <span class="badge peer-checked:badge-primary" style="{{ $ticket->tags->contains($tag->id) ? 'background-color: ' . $tag->color . '; color: white;' : '' }}">
                                        {{ $tag->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @if($tags->isEmpty())
                            <p class="text-sm text-base-content/60">
                                No tags available. <a href="{{ route('helpdesk.tags') }}" class="text-primary hover:underline">Create tags</a>
                            </p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
