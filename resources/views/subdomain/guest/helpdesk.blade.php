@extends('layouts.subdomain')

@section('title', ($ticket->subject ?? 'Support') . ' — ' . $host->studio_name)

@php
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
<div class="min-h-screen bg-base-200">
    {{-- Minimal studio header (no nav, this is a guest page) --}}
    <header class="bg-base-100 border-b border-base-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
            @if($host->logo_url)
                <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
            @else
                <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
                    <span class="text-sm font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                </div>
            @endif
            <div>
                <div class="font-semibold">{{ $host->studio_name }}</div>
                <div class="text-xs text-base-content/60">Support conversation</div>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6 space-y-4">
        @if(session('success'))
            <div class="alert alert-success">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl md:text-2xl font-bold">{{ $ticket->subject ?? 'No subject' }}</h1>
                <p class="text-sm text-base-content/60 mt-1">Opened {{ $ticket->created_at->format('M j, Y g:i A') }} · Ticket #{{ $ticket->id }}</p>
            </div>
            <span class="badge {{ $statusBadge }} shrink-0">{{ $ticket->status_label }}</span>
        </div>

        {{-- Conversation --}}
        <div class="space-y-3">
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
                                <span class="badge badge-primary badge-sm">{{ $msg->sender_name ?? $host->studio_name }}</span>
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

        {{-- Reply form (hidden once resolved) --}}
        @if($ticket->status !== 'resolved')
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-3">Reply to {{ $host->studio_name }}</h3>
                    @if($errors->any())
                        <div class="alert alert-error mb-3">
                            <span class="icon-[tabler--alert-circle] size-5"></span>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="guest-reply-form" action="{{ $replyUrl }}" method="POST">
                        @csrf
                        <input type="hidden" name="message" id="guest-reply-message">
                        <div id="guest-reply-editor" class="bg-base-100"></div>
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
                <span>This ticket has been resolved. If you need more help, reply to one of our emails or contact us directly.</span>
            </div>
        @endif

        <p class="text-xs text-base-content/50 text-center pt-4">
            You're viewing this page via a secure link sent to your email by {{ $host->studio_name }}. Don't share this link with others.
        </p>
    </main>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
    #guest-reply-editor { min-height: 160px; }
    #guest-reply-editor .ql-editor { min-height: 160px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editorEl = document.getElementById('guest-reply-editor');
    var form = document.getElementById('guest-reply-form');
    var hidden = document.getElementById('guest-reply-message');
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
