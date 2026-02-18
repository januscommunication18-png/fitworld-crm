@extends('layouts.dashboard')

@section('title', 'Request from ' . $classRequest->full_name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-requests.index') }}"><span class="icon-[tabler--message-circle-question] me-1 size-4"></span> Requests</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Request Details</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Request from {{ $classRequest->full_name }}</h1>
            <p class="text-base-content/60 mt-1">Submitted {{ $classRequest->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($classRequest->status !== 'booked')
            {{-- Add to Waitlist button - opens modal to select sessions --}}
            @if($classRequest->classPlan)
            <button type="button" class="btn btn-soft btn-info" onclick="openWaitlistModal()">
                <span class="icon-[tabler--hourglass] size-5"></span>
                Add to Waitlist
            </button>
            @endif
            <form action="{{ route('class-requests.mark-booked', $classRequest) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Mark as Booked
                </button>
            </form>
            @endif
            <form action="{{ route('class-requests.destroy', $classRequest) }}" method="POST" class="inline" onsubmit="return confirm('Delete this request?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost text-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button type="button" class="tab tab-active" role="tab" data-tab="overview" onclick="switchTab('overview')">
            <span class="icon-[tabler--file-description] size-4 me-2"></span>
            Request Overview
        </button>
        @if($classRequest->classPlan)
        <button type="button" class="tab" role="tab" data-tab="schedule" onclick="switchTab('schedule')">
            <span class="icon-[tabler--calendar] size-4 me-2"></span>
            {{ $classRequest->classPlan->name }} Schedule
        </button>
        @endif
    </div>

    {{-- Tab Content: Overview --}}
    <div id="tab-overview" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Request Details --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Request Details</h3>
                        <span class="badge {{ $classRequest->getStatusBadgeClass() }} badge-sm">
                            {{ \App\Models\ClassRequest::getStatuses()[$classRequest->status] ?? ucfirst(str_replace('_', ' ', $classRequest->status)) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-base-content/60 mb-1">Class</dt>
                                <dd class="font-medium">
                                    @if($classRequest->classPlan)
                                    <div class="flex items-center gap-2">
                                        @if($classRequest->classPlan->color)
                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $classRequest->classPlan->color }};"></div>
                                        @endif
                                        {{ $classRequest->classPlan->name }}
                                    </div>
                                    @else
                                    <span class="text-base-content/60">-</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-base-content/60 mb-1">Waitlist</dt>
                                <dd>
                                    @if($classRequest->waitlist_requested)
                                    <span class="badge badge-info badge-sm">Yes - Added to waitlist</span>
                                    @else
                                    <span class="text-base-content/60">No</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-base-content/60 mb-1">Phone</dt>
                                <dd>{{ $classRequest->phone ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-base-content/60 mb-1">Source</dt>
                                <dd class="capitalize">{{ $classRequest->source ?: 'web' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Message --}}
                @if($classRequest->message)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Message from Customer</h3>
                    </div>
                    <div class="card-body">
                        <p class="whitespace-pre-wrap">{{ $classRequest->message }}</p>
                    </div>
                </div>
                @endif

                {{-- Linked HelpDesk Ticket --}}
                @if($classRequest->helpdeskTicket)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">HelpDesk Ticket</h3>
                        <span class="badge badge-{{ $classRequest->helpdeskTicket->status === 'resolved' ? 'success' : ($classRequest->helpdeskTicket->status === 'open' ? 'info' : 'warning') }} badge-sm">
                            {{ ucfirst(str_replace('_', ' ', $classRequest->helpdeskTicket->status)) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $classRequest->helpdeskTicket->subject }}</div>
                                <div class="text-sm text-base-content/60">
                                    Created {{ $classRequest->helpdeskTicket->created_at->diffForHumans() }}
                                    @if($classRequest->helpdeskTicket->messages->count() > 0)
                                    &bull; {{ $classRequest->helpdeskTicket->messages->count() }} message(s)
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('helpdesk.show', $classRequest->helpdeskTicket) }}" class="btn btn-sm btn-soft btn-primary">
                                <span class="icon-[tabler--ticket] size-4"></span>
                                View Ticket
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Linked Waitlist Entries --}}
                @if($classRequest->waitlistEntries->count() > 0)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Waitlist Entries</h3>
                        <span class="badge badge-info badge-sm">{{ $classRequest->waitlistEntries->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="divide-y divide-base-200">
                            @foreach($classRequest->waitlistEntries as $entry)
                            <div class="flex items-center justify-between p-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 font-medium">
                                        @if($entry->classSession)
                                        <span class="icon-[tabler--calendar-event] size-4 text-primary"></span>
                                        {{ $entry->classSession->start_time->format('M j, Y g:i A') }}
                                        @else
                                        <span class="icon-[tabler--list-check] size-4 text-info"></span>
                                        General Waitlist
                                        @endif
                                    </div>
                                    <div class="text-sm text-base-content/60">
                                        Added {{ $entry->created_at->diffForHumans() }}
                                        @if($entry->offered_at)
                                        &bull; Offered {{ $entry->offered_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge {{ $entry->getStatusBadgeClass() }} badge-sm">
                                    {{ \App\Models\WaitlistEntry::getStatuses()[$entry->status] ?? ucfirst($entry->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('waitlist.index') }}" class="btn btn-sm btn-soft btn-info">
                            <span class="icon-[tabler--hourglass] size-4"></span>
                            View All Waitlists
                        </a>
                    </div>
                </div>
                @endif

                {{-- Booked Session --}}
                @if($classRequest->isBooked() && $classRequest->classSession)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Booked Session</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg border border-success/20">
                            <div>
                                <div class="font-medium text-success">{{ $classRequest->classSession->display_title ?? $classRequest->classSession->classPlan?->name }}</div>
                                <div class="text-sm text-base-content/60">
                                    {{ $classRequest->classSession->start_time->format('M j, Y') }} at {{ $classRequest->classSession->start_time->format('g:i A') }}
                                </div>
                            </div>
                            <a href="{{ route('class-sessions.show', $classRequest->classSession) }}" class="btn btn-sm btn-success">
                                View Session
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Requester Info --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Requester</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-12 h-12 rounded-full font-bold">
                                    {{ strtoupper(substr($classRequest->first_name, 0, 1)) }}{{ strtoupper(substr($classRequest->last_name, 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $classRequest->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ $classRequest->email }}</div>
                                @if($classRequest->phone)
                                <div class="text-sm text-base-content/60">{{ $classRequest->phone }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-2">
                            <a href="mailto:{{ $classRequest->email }}" class="btn btn-soft btn-primary w-full">
                                <span class="icon-[tabler--mail] size-5"></span>
                                Send Email
                            </a>
                            @if($classRequest->phone)
                            <a href="tel:{{ $classRequest->phone }}" class="btn btn-soft btn-secondary w-full">
                                <span class="icon-[tabler--phone] size-5"></span>
                                Call
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status Update --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Update Status</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('class-requests.update-status', $classRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="select select-bordered w-full mb-3" onchange="this.form.submit()">
                                @foreach(\App\Models\ClassRequest::getStatuses() as $value => $label)
                                <option value="{{ $value }}" {{ $classRequest->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Request Info --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Request Info</h3>
                    </div>
                    <div class="card-body">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Status</dt>
                                <dd><span class="badge {{ $classRequest->getStatusBadgeClass() }} badge-sm">
                                    {{ \App\Models\ClassRequest::getStatuses()[$classRequest->status] ?? ucfirst(str_replace('_', ' ', $classRequest->status)) }}
                                </span></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Submitted</dt>
                                <dd>{{ $classRequest->created_at->format('M j, Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Time</dt>
                                <dd>{{ $classRequest->created_at->format('g:i A') }}</dd>
                            </div>
                            @if($classRequest->client)
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Existing Client</dt>
                                <dd><span class="badge badge-success badge-sm">Yes</span></dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Content: Schedule Calendar --}}
    @if($classRequest->classPlan)
    <div id="tab-schedule" class="tab-content hidden">
        <div class="card bg-base-100">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    @if($classRequest->classPlan->color)
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $classRequest->classPlan->color }};"></div>
                    @endif
                    <h3 class="card-title">{{ $classRequest->classPlan->name }} Schedule</h3>
                </div>
            </div>
            <div class="card-body p-4">
                <x-studio-calendar
                    type="all"
                    default-type="class"
                    :default-class-plan-id="$classRequest->classPlan->id"
                    :show-filters="true"
                    :show-header="true"
                    :show-legend="true"
                    height="600"
                    :lazy="true"
                />
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Waitlist Modal --}}
@if($classRequest->classPlan)
<div id="waitlist-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50" onclick="closeWaitlistModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-2xl pointer-events-auto max-h-[90vh] overflow-y-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="icon-[tabler--hourglass] size-6 text-info"></span>
                    Add to Waitlist
                </h3>
                <button type="button" onclick="closeWaitlistModal()" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5">
                <p class="text-base-content/60 text-sm mb-4">
                    Select one or more {{ $classRequest->classPlan->name }} sessions to add {{ $classRequest->full_name }} to the waitlist.
                </p>

                <form action="{{ route('class-requests.add-to-waitlist', $classRequest) }}" method="POST" id="waitlist-form">
                    @csrf

                    {{-- Session Selection --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Select Sessions</label>

                        @if(isset($upcomingSessions) && $upcomingSessions->count() > 0)
                        <div class="max-h-64 overflow-y-auto border border-base-300 rounded-lg divide-y divide-base-200">
                            @foreach($upcomingSessions as $session)
                            <label class="flex items-center gap-3 p-3 hover:bg-base-200/50 cursor-pointer transition-colors" for="session-{{ $session->id }}">
                                <input type="checkbox" id="session-{{ $session->id }}" name="session_ids[]" value="{{ $session->id }}" class="checkbox checkbox-info checkbox-sm">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $session->start_time->format('l, M j, Y') }}</div>
                                    <div class="text-sm text-base-content/60">
                                        {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                                        @if($session->primaryInstructor)
                                        <span class="mx-1">&bull;</span> {{ $session->primaryInstructor->name }}
                                        @endif
                                        @if($session->location)
                                        <span class="mx-1">&bull;</span> {{ $session->location->name }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm">
                                        <span class="font-medium">{{ $session->confirmedBookings->count() }}</span>
                                        <span class="text-base-content/60">/ {{ $session->getEffectiveCapacity() }}</span>
                                    </div>
                                    @if($session->confirmedBookings->count() >= $session->getEffectiveCapacity())
                                    <span class="badge badge-error badge-xs">Full</span>
                                    @else
                                    <span class="badge badge-success badge-xs">{{ $session->getEffectiveCapacity() - $session->confirmedBookings->count() }} spots</span>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 bg-base-200/50 rounded-lg">
                            <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-2"></span>
                            <p class="text-base-content/60">No upcoming sessions for {{ $classRequest->classPlan->name }}</p>
                            <a href="{{ route('class-sessions.create', ['class_plan_id' => $classRequest->classPlan->id]) }}" class="btn btn-sm btn-primary mt-3">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Create Session
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- Also add to general waitlist option --}}
                    <div class="form-control mb-4">
                        <label class="label cursor-pointer justify-start gap-3" for="add-to-general-waitlist">
                            <input type="checkbox" id="add-to-general-waitlist" name="add_to_general_waitlist" value="1" class="checkbox checkbox-sm" checked>
                            <span class="label-text">Also add to general waitlist for {{ $classRequest->classPlan->name }}</span>
                        </label>
                        <p class="text-xs text-base-content/50 ml-8">They'll be notified when any new session is available</p>
                    </div>

                    {{-- Notes --}}
                    <div class="form-control mb-4">
                        <label class="block text-sm font-medium mb-1" for="waitlist-notes">Notes (optional)</label>
                        <textarea id="waitlist-notes" name="notes" rows="2" class="textarea textarea-bordered w-full" placeholder="Any special notes..."></textarea>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-base-200">
                        <button type="button" class="btn btn-ghost" onclick="closeWaitlistModal()">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <span class="icon-[tabler--hourglass] size-5"></span>
                            Add to Waitlist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .tab-content.hidden {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    window.switchTab = function(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tabs .tab').forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('tab-active');
            } else {
                tab.classList.remove('tab-active');
            }
        });

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            if (content.id === 'tab-' + tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    };
});

// Waitlist Modal functions
function openWaitlistModal() {
    document.getElementById('waitlist-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeWaitlistModal() {
    document.getElementById('waitlist-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeWaitlistModal();
    }
});
</script>
@endpush
@endsection
