@extends('layouts.dashboard')

@section('title', 'Help Desk')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--help] me-1 size-4"></span> Help Desk</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Help Desk</h1>
            <p class="text-base-content/60 mt-1">Manage service requests, inquiries, and customer tickets.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('helpdesk.tags') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--tags] size-4"></span>
                Manage Tags
            </a>
            <a href="{{ route('helpdesk.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                New Ticket
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('helpdesk.index') }}" class="card bg-base-100 hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-base-200 rounded-lg p-2">
                        <span class="icon-[tabler--inbox] size-6 text-base-content"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['all'] }}</p>
                        <p class="text-xs text-base-content/60">All Tickets</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('helpdesk.index', ['status' => 'open']) }}" class="card bg-base-100 hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--circle-dot] size-6 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['open'] }}</p>
                        <p class="text-xs text-base-content/60">Open</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('helpdesk.index', ['status' => 'in_progress']) }}" class="card bg-base-100 hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--progress] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['in_progress'] }}</p>
                        <p class="text-xs text-base-content/60">In Progress</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('helpdesk.index', ['status' => 'customer_reply']) }}" class="card bg-base-100 hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--message-2] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['customer_reply'] }}</p>
                        <p class="text-xs text-base-content/60">Customer Reply</p>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('helpdesk.index', ['status' => 'resolved']) }}" class="card bg-base-100 hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--check] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['resolved'] }}</p>
                        <p class="text-xs text-base-content/60">Resolved</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('helpdesk.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="search">Search</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Name, email, phone, or subject..."
                               class="input w-full pl-10">
                    </div>
                </div>
                <div class="w-40">
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select w-full">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="source">Source</label>
                    <select id="source" name="source" class="select w-full">
                        <option value="">All Sources</option>
                        @foreach($sources as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['source'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="assigned">Assigned</label>
                    <select id="assigned" name="assigned" class="select w-full">
                        <option value="">All</option>
                        <option value="unassigned" {{ ($filters['assigned'] ?? '') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}" {{ ($filters['assigned'] ?? '') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-4"></span>
                    Filter
                </button>
                @if(array_filter($filters ?? []))
                    <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($tickets->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--inbox] size-16 text-base-content/20 mx-auto"></span>
                    <h3 class="text-lg font-medium mt-4">No tickets found</h3>
                    <p class="text-base-content/60 mt-1">
                        @if(array_filter($filters ?? []))
                            Try adjusting your filters or search terms.
                        @else
                            Create a new ticket or wait for customer inquiries.
                        @endif
                    </p>
                    <a href="{{ route('helpdesk.create') }}" class="btn btn-primary mt-4">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Create Ticket
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>Contact</th>
                                <th>Subject</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Assigned</th>
                                <th>Created</th>
                                <th class="w-20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-base-200/50 cursor-pointer" onclick="window.location='{{ route('helpdesk.show', $ticket) }}'">
                                    <td class="font-mono text-sm text-base-content/60">{{ $ticket->id }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary/10 text-primary rounded-full w-10 h-10">
                                                    <span class="text-sm">{{ strtoupper(substr($ticket->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $ticket->name }}</p>
                                                <p class="text-xs text-base-content/60">{{ $ticket->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="max-w-xs">
                                            <p class="font-medium truncate">{{ $ticket->subject ?? 'No subject' }}</p>
                                            @if($ticket->servicePlan)
                                                <p class="text-xs text-base-content/60">
                                                    <span class="icon-[tabler--sparkles] size-3"></span>
                                                    {{ $ticket->servicePlan->name }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $sourceColors = [
                                                'booking_request' => 'badge-primary',
                                                'general_inquiry' => 'badge-info',
                                                'lead_magnet' => 'badge-warning',
                                                'manual' => 'badge-ghost',
                                            ];
                                        @endphp
                                        <span class="badge badge-sm {{ $sourceColors[$ticket->source_type] ?? 'badge-ghost' }}">
                                            {{ $ticket->source_label }}
                                        </span>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        @if($ticket->assignedUser)
                                            <div class="flex items-center gap-2">
                                                <div class="avatar placeholder">
                                                    <div class="bg-base-200 rounded-full w-6 h-6">
                                                        <span class="text-xs">{{ strtoupper(substr($ticket->assignedUser->name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <span class="text-sm">{{ $ticket->assignedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-base-content/40 text-sm">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-base-content/60">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </td>
                                    <td onclick="event.stopPropagation()">
                                        <details class="dropdown dropdown-bottom dropdown-end">
                                            <summary class="btn btn-ghost btn-sm btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                                                <li><a href="{{ route('helpdesk.show', $ticket) }}">
                                                    <span class="icon-[tabler--eye] size-4"></span> View
                                                </a></li>
                                                @if(!$ticket->client_id)
                                                    <li>
                                                        <form action="{{ route('helpdesk.convert', $ticket) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left">
                                                                <span class="icon-[tabler--user-plus] size-4"></span> Convert to Client
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li class="menu-title pt-2 mt-2 border-t border-base-200"></li>
                                                <li>
                                                    <form action="{{ route('helpdesk.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-error w-full text-left">
                                                            <span class="icon-[tabler--trash] size-4"></span> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($tickets->hasPages())
                    <div class="p-4 border-t border-base-200">
                        {{ $tickets->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
