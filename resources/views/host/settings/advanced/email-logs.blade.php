@extends('layouts.settings')

@section('title', 'Email Logs — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Email Logs</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold">Email Logs</h2>
                        <span class="badge badge-warning badge-soft badge-sm">Dev Only</span>
                    </div>
                    <p class="text-base-content/60 text-sm">View emails sent from this application (MAIL_MAILER=log)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-soft btn-sm" onclick="location.reload()">
                        <span class="icon-[tabler--refresh] size-4"></span> Refresh
                    </button>
                    @if(count($emails) > 0)
                    <form action="{{ route('settings.dev.email-logs.clear') }}" method="POST" onsubmit="return confirm('Clear all email logs?')">
                        @csrf
                        <button type="submit" class="btn btn-soft btn-error btn-sm">
                            <span class="icon-[tabler--trash] size-4"></span> Clear Logs
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Email Count --}}
    @if(count($emails) > 0)
    <div class="text-sm text-base-content/60">
        Showing {{ count($emails) }} email(s)
    </div>
    @endif

    {{-- Email List --}}
    @if(count($emails) > 0)
        <div class="space-y-4">
            @foreach($emails as $index => $email)
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon-[tabler--mail] size-5 text-primary"></span>
                                <span class="font-semibold">{{ $email['subject'] }}</span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-base-content/60 w-12">To:</span>
                                    <span class="font-medium text-primary">{{ $email['to'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base-content/60 w-12">From:</span>
                                    <span>{{ $email['from'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base-content/60 w-12">Sent:</span>
                                    <span>{{ $email['timestamp'] }}</span>
                                </div>
                                @if($email['body'])
                                <div class="flex gap-2 mt-2 pt-2 border-t border-base-content/5">
                                    <span class="text-base-content/40 text-xs">{{ $email['body'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline" onclick="toggleEmailPreview({{ $index }})">
                            <span class="icon-[tabler--eye] size-4"></span> Preview
                        </button>
                    </div>

                    {{-- Email Preview --}}
                    <div id="email-preview-{{ $index }}" class="hidden mt-4 pt-4 border-t border-base-content/10">
                        @if($email['html'])
                        <div class="bg-white rounded-lg border border-base-300 overflow-hidden">
                            <iframe id="email-iframe-{{ $index }}" class="w-full" style="border: none; min-height: 500px;"></iframe>
                        </div>
                        @else
                        <div class="bg-base-200 rounded-lg p-4">
                            <p class="text-sm text-base-content/60">No HTML content available for this email.</p>
                            @if($email['body'])
                            <pre class="mt-2 text-sm whitespace-pre-wrap">{{ $email['body'] }}</pre>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--mail-off] size-12 text-base-content/30 mx-auto"></span>
                <h3 class="font-medium mt-4">No emails logged</h3>
                <p class="text-sm text-base-content/60 mt-1">Emails will appear here when sent with MAIL_MAILER=log</p>
            </div>
        </div>
    @endif

    {{-- Info --}}
    <div class="alert alert-soft alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <div class="font-medium">Development Mode</div>
            <div class="text-sm">
                This page is only visible when APP_ENV=local. Emails are logged to <code class="text-xs bg-base-200 px-1 rounded">storage/logs/laravel.log</code> instead of being sent.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var emailHtmlData = @json(collect($emails)->pluck('html')->toArray());

function toggleEmailPreview(index) {
    var preview = document.getElementById('email-preview-' + index);
    var iframe = document.getElementById('email-iframe-' + index);

    preview.classList.toggle('hidden');

    // Load HTML content into iframe if not already loaded and iframe exists
    if (iframe && !iframe.dataset.loaded) {
        var html = emailHtmlData[index] || '';
        if (html) {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
            iframe.dataset.loaded = 'true';

            // Adjust iframe height after content loads
            setTimeout(function() {
                try {
                    var height = doc.body.scrollHeight || doc.documentElement.scrollHeight;
                    iframe.style.height = Math.max(height + 40, 500) + 'px';
                } catch(e) {}
            }, 300);
        }
    }
}
</script>
@endpush
