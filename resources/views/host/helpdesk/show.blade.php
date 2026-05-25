@extends('layouts.dashboard')

@php
    // Render a message body that may be either plain text (legacy / customer email) or
    // Quill-produced HTML (new staff replies). HTML is whitelisted to a safe tag set
    // to keep XSS surface area minimal; plain text uses nl2br for readability.
    $allowedTags = '<p><br><strong><b><em><i><u><s><ol><ul><li><a><h1><h2><h3><blockquote><pre><code>';
    $renderHelpdeskMessage = function (?string $body) use ($allowedTags): string {
        $body = (string) $body;
        if ($body === '') return '';
        $hasHtml = (bool) preg_match('/<[a-z][^>]*>/i', $body);
        if (!$hasHtml) {
            return nl2br(e($body));
        }
        // Strip everything outside the allowlist, then neutralize on*= handlers and javascript: hrefs.
        $clean = strip_tags($body, $allowedTags);
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean) ?? $clean;
        $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '$1="#"', $clean) ?? $clean;
        $clean = preg_replace("/(href|src)\s*=\s*'\s*javascript:[^']*'/i", '$1="#"', $clean) ?? $clean;
        return $clean;
    };
@endphp

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
@php
    $authUser = auth()->user();
    $canEditTicket = $authUser->hasPermission('helpdesk.edit');
    $canDeleteTicket = $authUser->hasPermission('helpdesk.delete');
    $canReply = $authUser->hasPermission('helpdesk.reply');
    $canAssign = $authUser->hasPermission('helpdesk.assign');
    // View Client buttons / links — gated by the dedicated helpdesk.view_client permission.
    $canViewClients = $authUser->hasPermission('helpdesk.view_client');
@endphp
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex items-center gap-4">
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
                @if($canEditTicket)
                    <form action="{{ route('helpdesk.convert', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--user-plus] size-4"></span>
                            Convert to Client
                        </button>
                    </form>
                @endif
            @elseif($canViewClients)
                <a href="{{ route('clients.show', $ticket->client_id) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--user] size-4"></span>
                    View Client
                </a>
            @endif
            @if($canDeleteTicket)
                <form id="helpdesk-delete-form" action="{{ route('helpdesk.destroy', $ticket) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-ghost btn-sm text-error" onclick="openHelpdeskDeleteModal()" aria-label="Delete ticket">
                        <span class="icon-[tabler--trash] size-4"></span>
                    </button>
                </form>
            @endif
            <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
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
                                    {!! $renderHelpdeskMessage($ticket->message) !!}
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
                                            {!! $renderHelpdeskMessage($message->message) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Reply Form — hidden once the ticket is resolved. --}}
            @if($canReply && $ticket->status !== 'resolved')
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Add Reply</h3>
                    <form id="helpdesk-reply-form" action="{{ route('helpdesk.reply', $ticket) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <div id="helpdesk-reply-editor" class="bg-base-100"></div>
                                <input type="hidden" name="message" id="helpdesk-reply-message" required>
                                @error('message')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" name="waiting_for_customer_reply" value="1" class="checkbox checkbox-primary checkbox-sm">
                                <span>Waiting for customer reply</span>
                            </label>
                            <div class="flex justify-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon-[tabler--send] size-4"></span>
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($canReply && $ticket->status === 'resolved')
            <div class="alert alert-soft alert-success">
                <span class="icon-[tabler--check] size-5"></span>
                <span>This ticket is resolved and closed. Reopen it from the sidebar status if you need to reply.</span>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Contact Info — only shown to users with helpdesk.view_client permission. --}}
            @if($canViewClients)
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
            @endif

            {{-- Ticket Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Ticket Details</h3>
                    @if($canEditTicket || $canAssign)
                    <form action="{{ route('helpdesk.update', $ticket) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="label-text" for="status">Status</label>
                            @if($canEditTicket)
                                <select id="status" name="status" class="select w-full" onchange="this.form.submit()">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <p class="font-medium mt-1">{{ $statuses[$ticket->status] ?? ucfirst($ticket->status) }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="label-text" for="assigned_user_id">Assigned To</label>
                            @if($canAssign)
                                <select id="assigned_user_id" name="assigned_user_id" class="hidden" onchange="this.form.submit()"
                                    data-select='{
                                        "placeholder": "Select team member...",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex items-center\"><div data-icon></div><span class=\"text-base-content\" data-title></span></div>",
                                        "hasSearch": true,
                                        "searchPlaceholder": "Search team members...",
                                        "searchClasses": "input input-sm border-base-content/20 w-full mb-2",
                                        "searchWrapperClasses": "px-2 py-1 sticky top-0 bg-base-100"
                                    }'>
                                    <option value="">Unassigned</option>
                                    @foreach($teamMembers as $member)
                                        <option value="{{ $member->id }}" {{ $ticket->assigned_user_id == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <p class="font-medium mt-1">{{ $ticket->assignedUser?->name ?? 'Unassigned' }}</p>
                            @endif
                        </div>
                    </form>
                    @else
                        <div class="space-y-3">
                            <div>
                                <label class="label-text">Status</label>
                                <p class="font-medium mt-1">{{ $statuses[$ticket->status] ?? ucfirst($ticket->status) }}</p>
                            </div>
                            <div>
                                <label class="label-text">Assigned To</label>
                                <p class="font-medium mt-1">{{ $ticket->assignedUser?->name ?? 'Unassigned' }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="divider my-2"></div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Source</span>
                            <span>{{ $ticket->source_label }}</span>
                        </div>
                        @php
                            // Prefer the polymorphic requestedItem; fall back to the
                            // legacy servicePlan relation for tickets created before
                            // the polymorphic columns existed.
                            $requested = $ticket->requestedItem ?? $ticket->servicePlan;
                            $requestedLabel = $ticket->requested_type_label ?? ($ticket->servicePlan ? 'Service Plan' : null);
                            $requestedName = $requested?->name ?? $requested?->title ?? null;
                        @endphp
                        @if($requested && $requestedName)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $requestedLabel ?? 'Requested' }}</span>
                                <span class="text-right">{{ $requestedName }}</span>
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
                    @if($canEditTicket)
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
                                    No tags available. <a href="{{ route('helpdesk.index') }}" class="text-primary hover:underline">Create tags from the helpdesk list</a>.
                                </p>
                            @endif
                        </form>
                    @else
                        @if($ticket->tags->isEmpty())
                            <p class="text-sm text-base-content/60">No tags applied.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($ticket->tags as $tag)
                                    <span class="badge" style="background-color: {{ $tag->color }}; color: white;">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete confirmation modal --}}
@if($canDeleteTicket)
<div id="helpdesk-delete-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog max-w-md">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title flex items-center gap-2">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                    Delete ticket?
                </h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeHelpdeskDeleteModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm">
                    You're about to delete ticket <strong>#{{ $ticket->id }}</strong> — <em class="break-words">{{ $ticket->subject ?? 'No subject' }}</em>.
                    All messages on this ticket will be removed. This can't be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft btn-secondary" onclick="closeHelpdeskDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-error" id="helpdesk-delete-confirm" onclick="submitHelpdeskDelete()">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
    #helpdesk-reply-editor { min-height: 140px; }
    #helpdesk-reply-editor .ql-editor { min-height: 140px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editorEl = document.getElementById('helpdesk-reply-editor');
    var form = document.getElementById('helpdesk-reply-form');
    var hidden = document.getElementById('helpdesk-reply-message');
    if (!editorEl || !form || !hidden) return;

    var quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Type your reply...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    form.addEventListener('submit', function(e) {
        var text = quill.getText().trim();
        if (!text) {
            e.preventDefault();
            editorEl.classList.add('border', 'border-error', 'rounded');
            quill.focus();
            return;
        }
        hidden.value = quill.root.innerHTML;
    });
});

// Delete-ticket confirmation modal
function openHelpdeskDeleteModal() {
    var m = document.getElementById('helpdesk-delete-modal');
    if (!m) return;
    m.classList.remove('hidden');
    m.classList.add('overlay-open');
    document.body.style.overflow = 'hidden';
}
function closeHelpdeskDeleteModal() {
    var m = document.getElementById('helpdesk-delete-modal');
    if (!m) return;
    m.classList.remove('overlay-open');
    m.classList.add('hidden');
    document.body.style.overflow = '';
}
function submitHelpdeskDelete() {
    var btn = document.getElementById('helpdesk-delete-confirm');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Deleting...';
    }
    var form = document.getElementById('helpdesk-delete-form');
    if (form) form.submit();
}
// Backdrop click + Escape to close
document.addEventListener('click', function (e) {
    var m = document.getElementById('helpdesk-delete-modal');
    if (m && e.target === m) closeHelpdeskDeleteModal();
});
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var m = document.getElementById('helpdesk-delete-modal');
    if (m && !m.classList.contains('hidden')) closeHelpdeskDeleteModal();
});
</script>
@endpush
@endsection
