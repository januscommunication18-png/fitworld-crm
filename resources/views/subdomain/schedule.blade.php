@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Schedule')

@section('content')

{{-- Navigation Bar - 75px height --}}
<nav class="bg-base-100 border-b border-base-200 sticky top-0 z-40" style="height: 75px;">
    <div class="container-fixed h-full">
        <div class="flex items-center justify-between h-full">
            {{-- Left: Logo --}}
            <div class="flex items-center">
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-lg hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>

            {{-- Right: Social Icons + Member Login --}}
            <div class="flex items-center gap-3">
                {{-- Social Media Icons --}}
                @if(($host->show_social_links ?? true) && $host->social_links && count(array_filter((array)$host->social_links)))
                <div class="hidden sm:flex items-center gap-1">
                    @foreach(['instagram' => 'brand-instagram', 'facebook' => 'brand-facebook', 'tiktok' => 'brand-tiktok', 'twitter' => 'brand-x', 'website' => 'world'] as $key => $icon)
                        @if(!empty($host->social_links[$key]))
                        <a href="{{ $host->social_links[$key] }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-full bg-base-200 hover:bg-primary hover:text-white flex items-center justify-center transition-colors"
                           title="{{ ucfirst($key) }}">
                            <span class="icon-[tabler--{{ $icon }}] size-5"></span>
                        </a>
                        @endif
                    @endforeach
                </div>
                <div class="hidden sm:block w-px h-8 bg-base-300"></div>
                @endif

                {{-- Member Login (Coming Soon) --}}
                <div class="relative group">
                    <button class="btn btn-ghost btn-sm sm:btn-md" disabled>
                        <span class="icon-[tabler--login] size-5"></span>
                        <span class="hidden sm:inline">Member Login</span>
                    </button>
                    <div class="absolute top-full right-0 mt-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        <span class="badge badge-sm badge-neutral whitespace-nowrap">Coming Soon</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Main Content --}}
<div class="container-fixed py-8 space-y-6">

    {{-- Page Navigation Tabs --}}
    <div class="flex justify-center mb-6">
        <div class="tabs tabs-boxed bg-base-200 p-1">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--home] size-4 me-1"></span> Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--calendar] size-4 me-1"></span> Schedule
            </a>
            @if($bookingSettings['show_instructors'] ?? true)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--users] size-4 me-1"></span> Instructors
            </a>
            @endif
        </div>
    </div>

    {{-- Schedule by Date --}}
    @if($sessionsByDate->isEmpty())
        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-16 text-center">
                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="font-semibold text-lg text-base-content">No Classes Scheduled</h3>
                <p class="text-base-content/60 mt-1">Check back soon for upcoming classes!</p>
                <div class="mt-6">
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($sessionsByDate as $date => $sessions)
            <div class="animate-fade-in">
                {{-- Date Header --}}
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-primary text-primary-content flex flex-col items-center justify-center">
                        <span class="text-xs font-medium uppercase leading-none">{{ \Carbon\Carbon::parse($date)->format('M') }}</span>
                        <span class="text-lg font-bold leading-none">{{ \Carbon\Carbon::parse($date)->format('j') }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-base-content">{{ \Carbon\Carbon::parse($date)->format('l') }}</h3>
                        <p class="text-sm text-base-content/60">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                    </div>
                </div>

                {{-- Classes for this date --}}
                <div class="space-y-2 ml-15">
                    @foreach($sessions as $session)
                    <div class="card bg-base-100 border border-base-200 card-hover">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-4">
                                {{-- Time --}}
                                <div class="text-center min-w-[80px] shrink-0">
                                    <div class="text-lg font-bold text-base-content">{{ $session->start_time->format('g:i A') }}</div>
                                    <div class="text-xs text-base-content/50">{{ $session->duration_minutes }} min</div>
                                </div>

                                {{-- Divider --}}
                                <div class="w-px h-12 bg-base-200"></div>

                                {{-- Class Info --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-base-content">{{ $session->title ?? ($session->classPlan->name ?? 'Class') }}</h4>
                                    <p class="text-sm text-base-content/60">
                                        @if($session->primaryInstructor)
                                            with {{ $session->primaryInstructor->name }}
                                        @endif
                                        @if($session->room)
                                            @if($session->primaryInstructor)<span class="mx-1.5 text-base-content/30">&bull;</span>@endif
                                            <span class="icon-[tabler--map-pin] size-3 inline"></span>
                                            {{ $session->room->name }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Spots & Action --}}
                                <div class="flex items-center gap-3 shrink-0">
                                    @php
                                        $spotsLeft = $session->capacity - ($session->bookings_count ?? 0);
                                    @endphp
                                    @if($spotsLeft > 0 && $spotsLeft <= 3)
                                        <span class="badge badge-warning badge-sm">{{ $spotsLeft }} left</span>
                                    @elseif($spotsLeft <= 0)
                                        <span class="badge badge-error badge-sm">Full</span>
                                    @endif

                                    @if($spotsLeft <= 0)
                                        <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                                           class="btn btn-warning btn-sm">
                                            <span class="icon-[tabler--list-check] size-4"></span>
                                            Join Waitlist
                                        </a>
                                    @else
                                        <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id]) }}"
                                           class="btn btn-primary btn-sm">
                                            <span class="icon-[tabler--calendar-plus] size-4"></span>
                                            Request Info
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
