@extends('layouts.dashboard')

@section('title', 'Request from ' . $classRequest->requester_name)

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
            <h1 class="text-2xl font-bold">Request from {{ $classRequest->requester_name }}</h1>
            <p class="text-base-content/60 mt-1">Submitted {{ $classRequest->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($classRequest->isPending())
            <form action="{{ route('class-requests.schedule', $classRequest) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--calendar-plus] size-5"></span>
                    Schedule
                </button>
            </form>
            <form action="{{ route('class-requests.ignore', $classRequest) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-soft btn-secondary">
                    <span class="icon-[tabler--x] size-5"></span>
                    Ignore
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Request Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Request Details</h3>
                    <span class="badge {{ $classRequest->getStatusBadgeClass() }} badge-soft capitalize">{{ $classRequest->status }}</span>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-base-content/60 mb-1">Type</dt>
                            <dd>
                                <span class="badge {{ $classRequest->isClassRequest() ? 'badge-primary' : 'badge-secondary' }} badge-soft">
                                    {{ $classRequest->getTypeLabel() }} Request
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60 mb-1">{{ $classRequest->isClassRequest() ? 'Class' : 'Service' }}</dt>
                            <dd class="font-medium">
                                @if($classRequest->getPlan())
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $classRequest->getPlan()->color }};"></div>
                                    {{ $classRequest->getPlan()->name }}
                                </div>
                                @else
                                <span class="text-base-content/60">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60 mb-1">Preferred Days</dt>
                            <dd>{{ $classRequest->formatted_preferred_days }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60 mb-1">Preferred Times</dt>
                            <dd>{{ $classRequest->formatted_preferred_times }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Notes --}}
            @if($classRequest->notes)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Additional Notes</h3>
                </div>
                <div class="card-body">
                    <p class="whitespace-pre-wrap">{{ $classRequest->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Scheduled Session --}}
            @if($classRequest->isScheduled() && $classRequest->scheduledSession)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Scheduled Session</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg border border-success/20">
                        <div>
                            <div class="font-medium text-success">{{ $classRequest->scheduledSession->display_title }}</div>
                            <div class="text-sm text-base-content/60">
                                {{ $classRequest->scheduledSession->formatted_date }} at {{ $classRequest->scheduledSession->formatted_time_range }}
                            </div>
                        </div>
                        <a href="{{ route('class-sessions.show', $classRequest->scheduledSession) }}" class="btn btn-sm btn-success">
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
                                {{ strtoupper(substr($classRequest->requester_name, 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">{{ $classRequest->requester_name }}</div>
                            <div class="text-sm text-base-content/60">{{ $classRequest->requester_email }}</div>
                        </div>
                    </div>
                    <a href="mailto:{{ $classRequest->requester_email }}" class="btn btn-soft btn-primary w-full">
                        <span class="icon-[tabler--mail] size-5"></span>
                        Send Email
                    </a>
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
                            <dd><span class="badge {{ $classRequest->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classRequest->status }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Submitted</dt>
                            <dd>{{ $classRequest->created_at->format('M j, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Time</dt>
                            <dd>{{ $classRequest->created_at->format('g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
