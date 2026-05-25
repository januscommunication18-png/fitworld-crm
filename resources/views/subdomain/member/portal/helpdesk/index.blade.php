@extends('layouts.subdomain')

@section('title', 'Support — ' . $host->studio_name)

@section('content')
<x-portal-shell :host="$host" :member="$member">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Support</h1>
            <p class="text-base-content/60 mt-1">Conversations between you and the {{ $host->studio_name }} team.</p>
        </div>
        <a href="{{ route('member.portal.helpdesk.create', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span>
            New Request
        </a>
    </div>

    @if($tickets->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--lifebuoy] size-12 text-base-content/30 mx-auto"></span>
                <h2 class="font-semibold mt-3">No support requests yet</h2>
                <p class="text-base-content/60 mt-1">When you reach out we'll keep the conversation here.</p>
                <a href="{{ route('member.portal.helpdesk.create', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary btn-sm mt-4 mx-auto w-fit">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Start a new request
                </a>
            </div>
        </div>
    @else
        <div class="card bg-base-100 overflow-hidden">
            <ul class="divide-y divide-base-200">
                @foreach($tickets as $ticket)
                    @php
                        $statusBadge = match($ticket->status) {
                            'open' => 'badge-info',
                            'in_progress' => 'badge-warning',
                            'customer_reply' => 'badge-primary',
                            'resolved' => 'badge-success',
                            default => 'badge-ghost',
                        };
                    @endphp
                    @php
                        // "customer_reply" status means the studio replied and is waiting on the
                        // member — show an incoming-message indicator so it's obvious there's a
                        // new reply for them to read.
                        $hasNewReply = $ticket->status === 'customer_reply';
                    @endphp
                    <li>
                        <a href="{{ route('member.portal.helpdesk.show', ['subdomain' => $host->subdomain, 'ticket' => $ticket->id]) }}"
                           class="flex items-start gap-4 p-4 hover:bg-base-200/40 transition {{ $hasNewReply ? 'bg-primary/5' : '' }}">
                            <div class="shrink-0 w-9 flex items-start justify-center pt-0.5">
                                @if($hasNewReply)
                                    <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/15 text-primary" title="New reply from {{ $host->studio_name }}">
                                        <span class="icon-[tabler--message-2-down] size-5"></span>
                                        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-primary ring-2 ring-base-100"></span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-base-200 text-base-content/40">
                                        <span class="icon-[tabler--message-2] size-5"></span>
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-{{ $hasNewReply ? 'semibold' : 'medium' }} truncate">{{ $ticket->subject ?? 'No subject' }}</span>
                                    <span class="badge badge-sm {{ $statusBadge }}">{{ $ticket->status_label }}</span>
                                    @if($hasNewReply)
                                        <span class="badge badge-xs badge-primary">New reply</span>
                                    @endif
                                </div>
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string) $ticket->message), 140) }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-base-content/60">{{ $ticket->updated_at->diffForHumans() }}</div>
                                <div class="text-xs text-base-content/40 mt-1">#{{ $ticket->id }}</div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
</x-portal-shell>
@endsection
