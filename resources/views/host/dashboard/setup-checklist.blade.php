@extends('layouts.dashboard')

@section('title', 'Complete Your Studio Setup')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    {{-- Header with Progress --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">Welcome to {{ $host->studio_name ?? 'Your Studio' }}!</h1>
        <p class="text-base-content/60 mb-6">Complete these steps to start using your studio management system.</p>

        {{-- Progress Circle --}}
        <div class="flex justify-center mb-6">
            <div class="relative inline-flex items-center justify-center">
                <svg class="w-32 h-32 transform -rotate-90">
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-base-300"/>
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none"
                        class="text-primary transition-all duration-500"
                        stroke-dasharray="{{ 2 * 3.14159 * 56 }}"
                        stroke-dashoffset="{{ 2 * 3.14159 * 56 * (1 - $progress / 100) }}"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-3xl font-bold">{{ $progress }}%</span>
                </div>
            </div>
        </div>
        <p class="text-sm text-base-content/60">{{ $completedCount }} of {{ $totalCount }} tasks completed</p>
    </div>

    {{-- Video Section --}}
    <div class="card bg-base-100 mb-6">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--player-play] size-5 mr-2 text-primary"></span>
                Getting Started Guide
            </h2>
            <div class="aspect-video bg-base-200 rounded-lg flex items-center justify-center mb-4">
                {{-- Placeholder for video --}}
                <div class="text-center text-base-content/50">
                    <span class="icon-[tabler--player-play-filled] size-16 mb-2"></span>
                    <p>Getting Started Video</p>
                    <p class="text-xs">Learn how to set up your studio in minutes</p>
                </div>
                {{-- Uncomment when you have a video URL --}}
                {{-- <iframe class="w-full h-full rounded-lg" src="YOUR_VIDEO_URL" frameborder="0" allowfullscreen></iframe> --}}
            </div>
            <p class="text-base-content/70">
                Follow these simple steps to configure your studio. Each step takes just a few minutes to complete,
                and once you're done, you'll have full access to all features including scheduling, client management,
                payments, and more.
            </p>
        </div>
    </div>

    {{-- Checklist --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--checklist] size-5 mr-2 text-primary"></span>
                Setup Checklist
            </h2>

            <div class="space-y-3">
                {{-- 1. Verify Account --}}
                <a href="{{ $checklist['verify_account']['completed'] ? '#' : route('verification.notice') }}"
                   class="flex items-center gap-4 p-4 rounded-lg border transition-all {{ $checklist['verify_account']['completed'] ? 'bg-success/5 border-success/20' : 'bg-base-100 border-base-300 hover:border-primary hover:bg-primary/5' }}">
                    <div class="flex-shrink-0">
                        @if($checklist['verify_account']['completed'])
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-warning/20 flex items-center justify-center">
                                <span class="icon-[tabler--mail] size-5 text-warning"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-medium">Verify Your Email</div>
                        <div class="text-sm text-base-content/60">Confirm your email address to secure your account</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($checklist['verify_account']['completed'])
                            <span class="badge badge-success badge-soft">Completed</span>
                        @else
                            <span class="badge badge-warning badge-soft">Pending</span>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 ml-2"></span>
                        @endif
                    </div>
                </a>

                {{-- 2. Complete Studio Profile --}}
                <a href="{{ route('settings.studio.profile') }}"
                   class="flex items-center gap-4 p-4 rounded-lg border transition-all {{ $checklist['studio_profile']['completed'] ? 'bg-success/5 border-success/20' : 'bg-base-100 border-base-300 hover:border-primary hover:bg-primary/5' }}">
                    <div class="flex-shrink-0">
                        @if($checklist['studio_profile']['completed'])
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--building-store] size-5 text-base-content/50"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-medium">Complete Studio Profile</div>
                        <div class="text-sm text-base-content/60">Add your studio name, description, logo, and contact info</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($checklist['studio_profile']['completed'])
                            <span class="badge badge-success badge-soft">Completed</span>
                        @else
                            <span class="badge badge-soft">Not Started</span>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 ml-2"></span>
                        @endif
                    </div>
                </a>

                {{-- 3. Setup Location --}}
                <a href="{{ route('settings.locations.index') }}"
                   class="flex items-center gap-4 p-4 rounded-lg border transition-all {{ $checklist['location']['completed'] ? 'bg-success/5 border-success/20' : 'bg-base-100 border-base-300 hover:border-primary hover:bg-primary/5' }}">
                    <div class="flex-shrink-0">
                        @if($checklist['location']['completed'])
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--map-pin] size-5 text-base-content/50"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-medium">Setup Location</div>
                        <div class="text-sm text-base-content/60">Add your studio address and room configuration</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($checklist['location']['completed'])
                            <span class="badge badge-success badge-soft">Completed</span>
                        @else
                            <span class="badge badge-soft">Not Started</span>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 ml-2"></span>
                        @endif
                    </div>
                </a>

                {{-- 4. Setup Instructor/Staff --}}
                <a href="{{ route('settings.team.instructors') }}"
                   class="flex items-center gap-4 p-4 rounded-lg border transition-all {{ $checklist['instructor']['completed'] ? 'bg-success/5 border-success/20' : 'bg-base-100 border-base-300 hover:border-primary hover:bg-primary/5' }}">
                    <div class="flex-shrink-0">
                        @if($checklist['instructor']['completed'])
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--users] size-5 text-base-content/50"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-medium">Setup Instructor / Staff</div>
                        <div class="text-sm text-base-content/60">Add instructors and staff members to your team</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($checklist['instructor']['completed'])
                            <span class="badge badge-success badge-soft">Completed</span>
                        @else
                            <span class="badge badge-soft">Not Started</span>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 ml-2"></span>
                        @endif
                    </div>
                </a>

                {{-- 5. Setup Payment System --}}
                <a href="{{ route('settings.payments.settings') }}"
                   class="flex items-center gap-4 p-4 rounded-lg border transition-all {{ $checklist['payment']['completed'] ? 'bg-success/5 border-success/20' : 'bg-base-100 border-base-300 hover:border-primary hover:bg-primary/5' }}">
                    <div class="flex-shrink-0">
                        @if($checklist['payment']['completed'])
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--credit-card] size-5 text-base-content/50"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-medium">Setup Payment System</div>
                        <div class="text-sm text-base-content/60">Connect Stripe to accept payments from your clients</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($checklist['payment']['completed'])
                            <span class="badge badge-success badge-soft">Completed</span>
                        @else
                            <span class="badge badge-soft">Not Started</span>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 ml-2"></span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Help Section --}}
            <div class="mt-6 p-4 bg-base-200/50 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--help-circle] size-5 text-info mt-0.5"></span>
                    <div>
                        <div class="font-medium text-sm">Need Help?</div>
                        <p class="text-sm text-base-content/60">
                            If you have any questions during setup, check out our
                            <a href="#" class="link link-primary">help center</a> or
                            <a href="#" class="link link-primary">contact support</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Skip for now (optional) --}}
    @if($progress >= 60)
    <div class="text-center mt-6">
        <form action="{{ route('dashboard.skip-setup') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm text-base-content/60">
                Skip for now and explore the dashboard
                <span class="icon-[tabler--arrow-right] size-4 ml-1"></span>
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
