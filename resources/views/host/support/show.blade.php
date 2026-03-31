@extends('layouts.settings')

@section('title', 'Support Request #' . $supportRequest->id)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('support.requests.index') }}">Support Requests</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">#{{ $supportRequest->id }}</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold flex items-center gap-2">
                Support Request #{{ $supportRequest->id }}
                <span class="badge {{ $supportRequest->status_badge_class }}">{{ $supportRequest->status_label }}</span>
            </h1>
            <p class="text-base-content/60 text-sm">Submitted {{ $supportRequest->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <a href="{{ route('support.requests.index') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to Requests
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Request Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Request Details</h2>
                    <div class="prose prose-sm max-w-none">
                        <p class="whitespace-pre-wrap">{{ $supportRequest->note }}</p>
                    </div>
                </div>
            </div>

            {{-- Admin Response (if any) --}}
            @if($supportRequest->admin_notes)
            <div class="card bg-info/5 border border-info/20">
                <div class="card-body">
                    <h2 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--message-reply] size-5 text-info"></span>
                        Support Team Response
                    </h2>
                    <div class="prose prose-sm max-w-none">
                        <p class="whitespace-pre-wrap">{{ $supportRequest->admin_notes }}</p>
                    </div>
                    @if($supportRequest->resolved_at)
                    <p class="text-xs text-base-content/50 mt-4">
                        Resolved on {{ $supportRequest->resolved_at->format('F d, Y \a\t h:i A') }}
                    </p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Contact Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Contact Information</h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--user] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Name</p>
                                <p class="font-medium">{{ $supportRequest->full_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Email</p>
                                <p class="font-medium">{{ $supportRequest->email }}</p>
                            </div>
                        </div>
                        @if($supportRequest->phone)
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--phone] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Phone</p>
                                <p class="font-medium">{{ $supportRequest->phone }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status Timeline --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Status</h2>
                    <ul class="timeline timeline-vertical timeline-compact">
                        <li>
                            <div class="timeline-start text-xs text-base-content/50">{{ $supportRequest->created_at->format('M d') }}</div>
                            <div class="timeline-middle">
                                <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                            </div>
                            <div class="timeline-end timeline-box">Submitted</div>
                            <hr class="{{ in_array($supportRequest->status, ['in_progress', 'resolved', 'closed']) ? 'bg-success' : 'bg-base-300' }}" />
                        </li>
                        <li>
                            <hr class="{{ in_array($supportRequest->status, ['in_progress', 'resolved', 'closed']) ? 'bg-success' : 'bg-base-300' }}" />
                            <div class="timeline-middle">
                                @if(in_array($supportRequest->status, ['in_progress', 'resolved', 'closed']))
                                    <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                                @else
                                    <span class="icon-[tabler--circle] size-5 text-base-300"></span>
                                @endif
                            </div>
                            <div class="timeline-end timeline-box {{ $supportRequest->status === 'in_progress' ? 'bg-info/10' : '' }}">In Progress</div>
                            <hr class="{{ in_array($supportRequest->status, ['resolved', 'closed']) ? 'bg-success' : 'bg-base-300' }}" />
                        </li>
                        <li>
                            <hr class="{{ in_array($supportRequest->status, ['resolved', 'closed']) ? 'bg-success' : 'bg-base-300' }}" />
                            <div class="timeline-middle">
                                @if(in_array($supportRequest->status, ['resolved', 'closed']))
                                    <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                                @else
                                    <span class="icon-[tabler--circle] size-5 text-base-300"></span>
                                @endif
                            </div>
                            <div class="timeline-end timeline-box {{ $supportRequest->status === 'resolved' ? 'bg-success/10' : '' }}">Resolved</div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Help Text --}}
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div class="text-sm">
                    <p class="font-medium">Need urgent assistance?</p>
                    <p class="text-xs mt-1">Contact us directly at <a href="mailto:support@fitcrm.com" class="link">support@fitcrm.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
