@extends('layouts.dashboard')

@section('title', $trans['setup.page_title'] ?? 'Complete Your Studio Setup')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Compact Header with Progress --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">
                @if($host->studio_name)
                    {{ str_replace(':studio_name', $host->studio_name, $trans['setup.welcome'] ?? 'Welcome to :studio_name!') }}
                @else
                    {{ $trans['setup.welcome_default'] ?? 'Welcome to Your Studio!' }}
                @endif
            </h1>
            <p class="text-base-content/60 text-sm">{{ $trans['setup.subtitle'] ?? 'Complete setup to unlock all features' }}</p>
        </div>
        <div class="flex items-center gap-4">
            {{-- Progress Ring - Compact --}}
            <div class="relative inline-flex items-center justify-center">
                <svg class="w-16 h-16 transform -rotate-90">
                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-base-300"/>
                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none"
                        class="text-primary transition-all duration-500"
                        stroke-dasharray="{{ 2 * 3.14159 * 28 }}"
                        stroke-dashoffset="{{ 2 * 3.14159 * 28 * (1 - $progress / 100) }}"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold">{{ $progress }}%</span>
                </div>
            </div>
            <div class="text-sm">
                <div class="font-semibold">{{ $completedCount }}/{{ $totalCount }}</div>
                <div class="text-base-content/50">{{ $trans['setup.completed'] ?? 'completed' }}</div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Left: Checklist (3 cols) --}}
        <div class="lg:col-span-3">
            <div class="card bg-base-100">
                <div class="card-body p-4">
                    <h2 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                        {{ $trans['setup.checklist_title'] ?? 'Setup Checklist' }}
                    </h2>

                    <div class="space-y-2">
                        {{-- 1. Verify Account --}}
                        @php $item = $checklist['verify_account']; @endphp
                        <div class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-warning/20 bg-warning/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-warning/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--mail] size-4 text-warning"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.verify_email'] ?? 'Verify Your Email' }}</div>
                                <div class="text-xs text-base-content/50 truncate">
                                    @if($item['completed'])
                                        {{ $trans['setup.email_verified'] ?? 'Email verified' }}
                                    @else
                                        {{ $trans['setup.check_inbox'] ?? 'Check your inbox for verification link' }}
                                    @endif
                                </div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <form action="{{ route('verification.send') }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <span class="icon-[tabler--send] size-4"></span>
                                        {{ $trans['setup.resend_email'] ?? 'Resend Email' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- 2. Complete Studio Profile --}}
                        @php $item = $checklist['studio_profile']; @endphp
                        <a href="{{ route('settings.studio.profile') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--building-store] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.studio_profile'] ?? 'Complete Studio Profile' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.studio_profile_desc'] ?? 'Name, logo & contact info' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>

                        {{-- 3. Setup Payment System --}}
                        @php $item = $checklist['payment']; @endphp
                        <a href="{{ route('settings.payments.settings') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--credit-card] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.payment_system'] ?? 'Setup Payment System' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.payment_desc'] ?? 'Connect Stripe to accept payments' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>

                        {{-- 4. Setup Location --}}
                        @php $item = $checklist['location']; @endphp
                        <a href="{{ route('settings.locations.index') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--map-pin] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.location'] ?? 'Setup Location' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.location_desc'] ?? 'Address & room configuration' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>

                        {{-- 5. Setup Instructor/Staff --}}
                        @php $item = $checklist['instructor']; @endphp
                        <a href="{{ route('settings.team.instructors') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--users] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.instructor'] ?? 'Setup Instructor / Staff' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.instructor_desc'] ?? 'Add team members with availability & time slots' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>

                        {{-- 6. Classes and Services --}}
                        @php $item = $checklist['catalog']; @endphp
                        <a href="{{ route('catalog.index') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--layout-grid] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.catalog'] ?? 'Classes and Services' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.catalog_desc'] ?? 'Create your classes, services & memberships' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>
                    </div>

                    {{-- CTA when all done --}}
                    @if($progress === 100)
                    <div class="mt-4 p-4 bg-success/10 rounded-lg text-center">
                        <span class="icon-[tabler--confetti] size-6 text-success mb-2"></span>
                        <p class="font-medium text-success">{{ $trans['setup.all_set'] ?? "All set! You're ready to go." }}</p>
                        <form action="{{ route('dashboard.skip-setup') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                {{ $trans['setup.go_to_dashboard'] ?? 'Go to Dashboard' }}
                                <span class="icon-[tabler--arrow-right] size-4"></span>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Video & Tips (2 cols) --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Video Card --}}
            <div class="card bg-base-100">
                <div class="card-body p-4">
                    <div class="aspect-video bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg flex items-center justify-center cursor-pointer hover:from-primary/20 hover:to-secondary/20 transition-all group">
                        <div class="text-center">
                            <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-2 group-hover:bg-primary/30 transition-all">
                                <span class="icon-[tabler--player-play-filled] size-7 text-primary"></span>
                            </div>
                            <p class="font-medium text-sm">{{ $trans['setup.watch_video'] ?? 'Watch Getting Started' }}</p>
                            <p class="text-xs text-base-content/50">{{ $trans['setup.video_duration'] ?? '2 min video' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Tips --}}
            <div class="card bg-gradient-to-br from-info/5 to-info/10 border border-info/20">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--bulb] size-4 text-info"></span>
                        {{ $trans['setup.quick_tips'] ?? 'Quick Tips' }}
                    </h3>
                    <ul class="space-y-2 text-sm text-base-content/70">
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_time'] ?? 'Setup takes about 5 minutes' }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_update'] ?? 'You can update settings anytime' }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_help'] ?? 'Need help?' }} <a href="#" class="link link-primary">{{ $trans['setup.contact_support'] ?? 'Contact support' }}</a></span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Skip Option --}}
            @if($progress >= 60)
            <form action="{{ route('dashboard.skip-setup') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm w-full text-base-content/50 hover:text-base-content">
                    {{ $trans['setup.skip_for_now'] ?? 'Skip for now' }}
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
