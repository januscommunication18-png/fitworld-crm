@extends('layouts.subdomain')

@section('title', ($ticket->subject ?? 'Ticket') . ' — ' . $host->studio_name)

@php
    // Match the staff-side rendering: HTML body if tags present, else nl2br plain text.
    $allowedTags = '<p><br><strong><b><em><i><u><s><ol><ul><li><a><h1><h2><h3><blockquote><pre><code>';
    $renderMessage = function (?string $body) use ($allowedTags): string {
        $body = (string) $body;
        if ($body === '') return '';
        if (!preg_match('/<[a-z][^>]*>/i', $body)) {
            return nl2br(e($body));
        }
        $clean = strip_tags($body, $allowedTags);
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean) ?? $clean;
        $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '$1="#"', $clean) ?? $clean;
        $clean = preg_replace("/(href|src)\s*=\s*'\s*javascript:[^']*'/i", '$1="#"', $clean) ?? $clean;
        return $clean;
    };

    $statusBadge = match($ticket->status) {
        'open' => 'badge-info',
        'in_progress' => 'badge-warning',
        'customer_reply' => 'badge-primary',
        'resolved' => 'badge-success',
        default => 'badge-ghost',
    };
@endphp

@section('content')
<x-portal-shell :host="$host" :member="$member">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('member.portal.helpdesk', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to Support
        </a>
        <span class="badge {{ $statusBadge }}">{{ $ticket->status_label }}</span>
    </div>

    <div class="mb-4">
        <h1 class="text-xl md:text-2xl font-bold">{{ $ticket->subject ?? 'No subject' }}</h1>
        <p class="text-sm text-base-content/60 mt-1">Opened {{ $ticket->created_at->format('M j, Y g:i A') }} · Ticket #{{ $ticket->id }}</p>
    </div>

    {{-- Conversation thread --}}
    <div class="space-y-3 mb-6">
        @if($ticket->message)
            <div class="card bg-base-100">
                <div class="card-body p-4">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-2">
                        <span class="badge badge-soft badge-sm">{{ $ticket->name }}</span>
                        <span>{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="prose prose-sm max-w-none">{!! $renderMessage($ticket->message) !!}</div>
                </div>
            </div>
        @endif

        @foreach($ticket->messages as $msg)
            @php $isStaff = $msg->is_staff_message ?? ($msg->sender_type === 'staff'); @endphp
            <div class="card bg-base-100 {{ $isStaff ? 'border-l-4 border-primary' : '' }}">
                <div class="card-body p-4">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-2">
                        @if($isStaff)
                            <span class="badge badge-primary badge-sm">{{ $msg->sender_name ?? 'Studio' }}</span>
                        @else
                            <span class="badge badge-soft badge-sm">You</span>
                        @endif
                        <span>{{ $msg->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="prose prose-sm max-w-none">{!! $renderMessage($msg->message) !!}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Reply form (hidden once ticket is resolved) --}}
    @if($ticket->status !== 'resolved')
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold mb-3">Your reply</h3>
            @if($errors->any())
                <div class="alert alert-error mb-3">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <form id="member-reply-form" action="{{ route('member.portal.helpdesk.reply', ['subdomain' => $host->subdomain, 'ticket' => $ticket->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="message" id="member-reply-message">
                <div id="member-reply-editor" class="bg-base-100"></div>
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-base-200">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>This ticket is resolved. Please <a href="{{ route('member.portal.helpdesk.create', ['subdomain' => $host->subdomain]) }}" class="link link-primary">start a new request</a> if you need more help.</span>
    </div>
    @endif
</x-portal-shell>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
    #member-reply-editor { min-height: 140px; }
    #member-reply-editor .ql-editor { min-height: 140px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editorEl = document.getElementById('member-reply-editor');
    var form = document.getElementById('member-reply-form');
    var hidden = document.getElementById('member-reply-message');
    if (!editorEl || !form || !hidden) return;

    var quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Type your reply...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'blockquote'],
                ['clean']
            ]
        }
    });

    form.addEventListener('submit', function (e) {
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
</script>
@endpush
