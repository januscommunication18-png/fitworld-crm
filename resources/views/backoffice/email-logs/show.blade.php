@extends('backoffice.layouts.app')

@section('title', 'Email Log Details')
@section('page-title', 'Email Log Details')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.email-logs.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Email Logs
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Email Content --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Email Content</h3>
                </div>
                <div class="card-body">
                    {{-- Email Header --}}
                    <div class="space-y-2 text-sm mb-4 pb-4 border-b border-base-content/10">
                        <div class="flex">
                            <span class="text-base-content/60 w-24">To:</span>
                            <span>{{ $emailLog->recipient_name ? $emailLog->recipient_name . ' <' . $emailLog->recipient_email . '>' : $emailLog->recipient_email }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-base-content/60 w-24">Subject:</span>
                            <span class="font-medium">{{ $emailLog->subject }}</span>
                        </div>
                        @if($emailLog->template)
                        <div class="flex">
                            <span class="text-base-content/60 w-24">Template:</span>
                            <a href="{{ route('backoffice.email-templates.edit', $emailLog->template) }}" class="text-primary hover:underline">
                                {{ $emailLog->template->name }}
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- Body Preview --}}
                    @if($emailLog->body_preview)
                    <div class="bg-base-200 rounded-lg p-4">
                        <p class="text-sm whitespace-pre-wrap">{{ $emailLog->body_preview }}</p>
                    </div>
                    @else
                    <p class="text-base-content/60 text-sm">No content preview available.</p>
                    @endif
                </div>
            </div>

            {{-- Error Details (if failed) --}}
            @if($emailLog->status === 'failed' && $emailLog->error_message)
            <div class="card bg-base-100 border border-error/20">
                <div class="card-header">
                    <h3 class="card-title text-error">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        Error Details
                    </h3>
                </div>
                <div class="card-body">
                    <pre class="text-sm bg-error/5 p-4 rounded-lg overflow-x-auto">{{ $emailLog->error_message }}</pre>
                </div>
            </div>
            @endif

            {{-- Metadata --}}
            @if($emailLog->metadata)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Metadata</h3>
                </div>
                <div class="card-body">
                    <pre class="text-xs bg-base-200 p-4 rounded-lg overflow-x-auto">{{ json_encode($emailLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Status</h3>
                </div>
                <div class="card-body">
                    @php
                        $statusColors = [
                            'queued' => 'badge-neutral',
                            'sent' => 'badge-info',
                            'delivered' => 'badge-success',
                            'failed' => 'badge-error',
                            'bounced' => 'badge-warning',
                        ];
                    @endphp
                    <span class="badge badge-soft {{ $statusColors[$emailLog->status] ?? 'badge-neutral' }} capitalize">
                        {{ $emailLog->status }}
                    </span>
                </div>
            </div>

            {{-- Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-base-content/60">ID</dt>
                            <dd class="font-mono">{{ $emailLog->id }}</dd>
                        </div>
                        @if($emailLog->host)
                        <div>
                            <dt class="text-base-content/60">Client</dt>
                            <dd>
                                <a href="{{ route('backoffice.clients.show', $emailLog->host) }}" class="text-primary hover:underline">
                                    {{ $emailLog->host->studio_name }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-base-content/60">Provider</dt>
                            <dd>{{ $emailLog->provider ?? 'Default' }}</dd>
                        </div>
                        @if($emailLog->provider_message_id)
                        <div>
                            <dt class="text-base-content/60">Provider Message ID</dt>
                            <dd class="font-mono text-xs break-all">{{ $emailLog->provider_message_id }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Timestamps</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-base-content/60">Created</dt>
                            <dd>{{ $emailLog->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @if($emailLog->sent_at)
                        <div>
                            <dt class="text-base-content/60">Sent</dt>
                            <dd>{{ $emailLog->sent_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @endif
                        @if($emailLog->opened_at)
                        <div>
                            <dt class="text-base-content/60">Opened</dt>
                            <dd>{{ $emailLog->opened_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @endif
                        @if($emailLog->clicked_at)
                        <div>
                            <dt class="text-base-content/60">Clicked</dt>
                            <dd>{{ $emailLog->clicked_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <form action="{{ route('backoffice.email-logs.destroy', $emailLog) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this log?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-soft w-full">
                            <span class="icon-[tabler--trash] size-5"></span>
                            Delete Log
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
