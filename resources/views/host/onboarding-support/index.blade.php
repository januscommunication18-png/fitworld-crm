@extends('layouts.dashboard')

@section('title', 'Request Support')

@section('breadcrumbs')
<nav class="breadcrumbs text-sm">
    <ul>
        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="opacity-60">Request Support</li>
    </ul>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Request Support</h1>
            <p class="text-base-content/60">View your onboarding support requests and setup progress</p>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form action="{{ route('onboarding-support.index') }}" method="GET" class="flex-1 max-w-md">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="join w-full">
                    <input type="text" name="search" value="{{ $search }}"
                        class="input join-item flex-1"
                        placeholder="Search by name, email...">
                    <button type="submit" class="btn btn-primary join-item">
                        <span class="icon-[tabler--search] size-5"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered">
        <a href="{{ route('onboarding-support.index', ['tab' => 'all', 'search' => $search]) }}"
           class="tab {{ $tab === 'all' ? 'tab-active' : '' }}">
            All
            <span class="badge badge-neutral badge-sm ml-2">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('onboarding-support.index', ['tab' => 'open', 'search' => $search]) }}"
           class="tab {{ $tab === 'open' ? 'tab-active' : '' }}">
            Open
            <span class="badge badge-warning badge-sm ml-2">{{ $counts['open'] }}</span>
        </a>
        <a href="{{ route('onboarding-support.index', ['tab' => 'in_progress', 'search' => $search]) }}"
           class="tab {{ $tab === 'in_progress' ? 'tab-active' : '' }}">
            In Progress
            <span class="badge badge-info badge-sm ml-2">{{ $counts['in_progress'] }}</span>
        </a>
        <a href="{{ route('onboarding-support.index', ['tab' => 'resolved', 'search' => $search]) }}"
           class="tab {{ $tab === 'resolved' ? 'tab-active' : '' }}">
            Resolved
            <span class="badge badge-success badge-sm ml-2">{{ $counts['resolved'] }}</span>
        </a>
    </div>

    {{-- Support Requests with Setup Checklist --}}
    <div class="space-y-4">
        @forelse($requests as $index => $request)
        @php
            $currentStep = $host->post_signup_step ?? 1;
            $isCompleted = $host->post_signup_completed_at !== null;

            $steps = [
                1 => ['label' => 'Verify Email', 'icon' => 'icon-[tabler--mail-check]'],
                2 => ['label' => 'Studio Info', 'icon' => 'icon-[tabler--building-store]'],
                3 => ['label' => 'Location', 'icon' => 'icon-[tabler--map-pin]'],
                4 => ['label' => 'Team', 'icon' => 'icon-[tabler--users]'],
                5 => ['label' => 'Booking Page', 'icon' => 'icon-[tabler--calendar-check]'],
            ];

            $statusColors = [
                'open' => 'badge-warning',
                'in_progress' => 'badge-info',
                'customer_reply' => 'badge-primary',
                'resolved' => 'badge-success',
            ];
        @endphp
        <div class="card bg-base-100">
            <div class="card-body">
                {{-- Header Row --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                    {{-- Requester Info --}}
                    <div class="flex items-center gap-4">
                        <div class="avatar avatar-placeholder">
                            <div class="bg-primary/10 text-primary size-12 rounded-xl text-sm font-bold">
                                {{ strtoupper(substr($request->name ?? 'U', 0, 2)) }}
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-lg">{{ $request->name }}</h3>
                                <span class="badge badge-soft {{ $statusColors[$request->status] ?? 'badge-neutral' }} badge-sm capitalize">
                                    {{ str_replace('_', ' ', $request->status) }}
                                </span>
                            </div>
                            <div class="text-sm text-base-content/60">{{ $request->email }}</div>
                        </div>
                    </div>

                    {{-- Meta Info & Actions --}}
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="text-sm font-medium">{{ $request->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-base-content/60">{{ $request->created_at->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('onboarding-support.show', $request) }}"
                           class="btn btn-sm btn-outline" title="View Details">
                            <span class="icon-[tabler--eye] size-4 mr-1"></span>
                            Details
                        </a>
                    </div>
                </div>

                {{-- Support Note --}}
                @if($request->message)
                <div class="bg-base-200/50 rounded-lg p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <span class="icon-[tabler--message] size-4 text-base-content/50 mt-0.5"></span>
                        <p class="text-sm text-base-content/70">{{ $request->message }}</p>
                    </div>
                </div>
                @endif

                {{-- Setup Checklist Steps --}}
                <div class="border-t border-base-content/10 pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--list-check] size-4 text-base-content/50"></span>
                        <span class="text-sm font-medium text-base-content/70">Onboarding Progress</span>
                        @if($isCompleted)
                            <span class="badge badge-success badge-sm">Completed</span>
                        @else
                            <span class="badge badge-neutral badge-sm">Step {{ $currentStep }} of 5</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-5 gap-2">
                        @foreach($steps as $stepNum => $step)
                        @php
                            $isStepCompleted = $isCompleted || $stepNum < $currentStep;
                            $isCurrentStep = !$isCompleted && $stepNum === $currentStep;
                            $isPending = !$isCompleted && $stepNum > $currentStep;
                        @endphp
                        <a href="{{ route('onboarding-support.step', ['ticket' => $request, 'step' => $stepNum]) }}"
                           class="group relative flex flex-col items-center p-3 rounded-lg border transition-all
                                  {{ $isStepCompleted ? 'bg-success/5 border-success/30 hover:bg-success/10' : '' }}
                                  {{ $isCurrentStep ? 'bg-primary/5 border-primary/30 hover:bg-primary/10 ring-2 ring-primary/20' : '' }}
                                  {{ $isPending ? 'bg-base-200/50 border-base-content/10 hover:bg-base-200' : '' }}">
                            {{-- Step Number Badge --}}
                            <div class="absolute -top-2 -right-2 size-5 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $isStepCompleted ? 'bg-success text-success-content' : '' }}
                                        {{ $isCurrentStep ? 'bg-primary text-primary-content' : '' }}
                                        {{ $isPending ? 'bg-base-300 text-base-content/50' : '' }}">
                                @if($isStepCompleted)
                                    <span class="icon-[tabler--check] size-3"></span>
                                @else
                                    {{ $stepNum }}
                                @endif
                            </div>

                            {{-- Icon --}}
                            <span class="{{ $step['icon'] }} size-6 mb-1
                                        {{ $isStepCompleted ? 'text-success' : '' }}
                                        {{ $isCurrentStep ? 'text-primary' : '' }}
                                        {{ $isPending ? 'text-base-content/40' : '' }}"></span>

                            {{-- Label --}}
                            <span class="text-xs font-medium text-center
                                        {{ $isStepCompleted ? 'text-success' : '' }}
                                        {{ $isCurrentStep ? 'text-primary' : '' }}
                                        {{ $isPending ? 'text-base-content/50' : '' }}">
                                {{ $step['label'] }}
                            </span>

                            {{-- Hover indicator --}}
                            <span class="icon-[tabler--chevron-right] size-4 absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity text-base-content/40"></span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card bg-base-100">
            <div class="card-body py-12">
                <div class="flex flex-col items-center gap-2 text-base-content/60">
                    <span class="icon-[tabler--headset] size-12 opacity-30"></span>
                    <p>No support requests found</p>
                    @if($search)
                        <a href="{{ route('onboarding-support.index', ['tab' => $tab]) }}" class="text-primary text-sm">Clear search</a>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
    <div class="flex justify-center">
        {{ $requests->appends(['tab' => $tab, 'search' => $search])->links() }}
    </div>
    @endif
</div>
@endsection
