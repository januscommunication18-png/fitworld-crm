@extends('layouts.dashboard')

@section('title', 'Support Request - ' . $ticket->name)

@section('breadcrumbs')
<nav class="breadcrumbs text-sm">
    <ul>
        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('onboarding-support.index') }}">Request Support</a></li>
        <li class="opacity-60">{{ $ticket->name }}</li>
    </ul>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                {{-- Requester Info --}}
                <div class="flex items-start gap-4">
                    <div class="avatar avatar-placeholder">
                        <div class="bg-primary/10 text-primary size-14 rounded-xl text-lg font-bold">
                            {{ strtoupper(substr($ticket->name ?? 'U', 0, 2)) }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">{{ $ticket->name }}</h2>
                        <p class="text-base-content/60">{{ $ticket->email }}</p>
                        @if($ticket->phone)
                            <p class="text-base-content/60">{{ $ticket->phone }}</p>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex flex-col items-end gap-3">
                    @php
                        $statusColors = [
                            'open' => 'badge-warning',
                            'in_progress' => 'badge-info',
                            'customer_reply' => 'badge-primary',
                            'resolved' => 'badge-success',
                        ];
                    @endphp
                    <span class="badge badge-soft {{ $statusColors[$ticket->status] ?? 'badge-neutral' }} capitalize">
                        {{ str_replace('_', ' ', $ticket->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Support Note --}}
    @if($ticket->message)
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold mb-2">Support Note</h3>
            <p class="text-base-content/70">{{ $ticket->message }}</p>
        </div>
    </div>
    @endif

    {{-- Onboarding Progress --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold mb-4">Onboarding Progress</h3>

            @php
                $currentStep = $host->post_signup_step ?? 1;
                $isCompleted = $host->post_signup_completed_at !== null;

                $steps = [
                    1 => ['label' => 'Verify Email', 'icon' => 'icon-[tabler--mail-check]', 'description' => 'Email and phone verification'],
                    2 => ['label' => 'Studio Info', 'icon' => 'icon-[tabler--building-store]', 'description' => 'Studio name, type, and settings'],
                    3 => ['label' => 'Location', 'icon' => 'icon-[tabler--map-pin]', 'description' => 'Physical or virtual location'],
                    4 => ['label' => 'Team', 'icon' => 'icon-[tabler--users]', 'description' => 'Staff members and invitations'],
                    5 => ['label' => 'Booking Page', 'icon' => 'icon-[tabler--calendar-check]', 'description' => 'Booking page and logo'],
                ];
            @endphp

            @if($isCompleted)
                <div class="alert alert-success mb-4">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>Onboarding completed on {{ $host->post_signup_completed_at->format('M d, Y h:i A') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($steps as $stepNum => $step)
                @php
                    $isStepCompleted = $isCompleted || $stepNum < $currentStep;
                    $isCurrentStep = !$isCompleted && $stepNum === $currentStep;
                    $isPending = !$isCompleted && $stepNum > $currentStep;
                @endphp
                <a href="{{ route('onboarding-support.step', ['ticket' => $ticket, 'step' => $stepNum]) }}"
                   class="group relative flex flex-col items-center p-4 rounded-xl border-2 transition-all
                          {{ $isStepCompleted ? 'bg-success/5 border-success/30 hover:bg-success/10' : '' }}
                          {{ $isCurrentStep ? 'bg-primary/5 border-primary/30 hover:bg-primary/10 ring-2 ring-primary/20' : '' }}
                          {{ $isPending ? 'bg-base-200/50 border-base-content/10 hover:bg-base-200' : '' }}">
                    {{-- Step Number Badge --}}
                    <div class="absolute -top-2.5 -right-2.5 size-6 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $isStepCompleted ? 'bg-success text-success-content' : '' }}
                                {{ $isCurrentStep ? 'bg-primary text-primary-content' : '' }}
                                {{ $isPending ? 'bg-base-300 text-base-content/50' : '' }}">
                        @if($isStepCompleted)
                            <span class="icon-[tabler--check] size-3.5"></span>
                        @else
                            {{ $stepNum }}
                        @endif
                    </div>

                    {{-- Icon --}}
                    <span class="{{ $step['icon'] }} size-8 mb-2
                                {{ $isStepCompleted ? 'text-success' : '' }}
                                {{ $isCurrentStep ? 'text-primary' : '' }}
                                {{ $isPending ? 'text-base-content/40' : '' }}"></span>

                    {{-- Label --}}
                    <span class="text-sm font-medium text-center
                                {{ $isStepCompleted ? 'text-success' : '' }}
                                {{ $isCurrentStep ? 'text-primary' : '' }}
                                {{ $isPending ? 'text-base-content/50' : '' }}">
                        {{ $step['label'] }}
                    </span>

                    {{-- Description --}}
                    <span class="text-xs text-center mt-1
                                {{ $isStepCompleted ? 'text-success/70' : '' }}
                                {{ $isCurrentStep ? 'text-primary/70' : '' }}
                                {{ $isPending ? 'text-base-content/40' : '' }}">
                        {{ $step['description'] }}
                    </span>

                    {{-- Hover indicator --}}
                    <span class="mt-2 text-xs opacity-0 group-hover:opacity-100 transition-opacity text-base-content/50">
                        View details
                    </span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Request Meta --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold mb-4">Request Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-xs text-base-content/50">Submitted</span>
                    <p class="font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</p>
                    <p class="text-sm text-base-content/60">{{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                <div>
                    <span class="text-xs text-base-content/50">Last Updated</span>
                    <p class="font-medium">{{ $ticket->updated_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <span class="text-xs text-base-content/50">Ticket ID</span>
                    <p class="font-medium font-mono">#{{ $ticket->id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
