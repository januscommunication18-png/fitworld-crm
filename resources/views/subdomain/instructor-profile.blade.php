@extends('layouts.subdomain')

@section('title', $instructor->name . ' — ' . $host->studio_name)

@section('content')
@php
    $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
@endphp

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

    {{-- Back Link --}}
    <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-primary transition-colors">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Instructors
    </a>

    {{-- Instructor Profile Card --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Photo --}}
                <div class="shrink-0">
                    @if($instructor->photo_url)
                        <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                             class="w-32 h-32 md:w-40 md:h-40 rounded-2xl object-cover ring-4 ring-base-200 mx-auto">
                    @else
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center ring-4 ring-base-200 mx-auto">
                            <span class="text-4xl md:text-5xl font-bold text-primary">{{ $initials }}</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-2xl md:text-3xl font-bold text-base-content">{{ $instructor->name }}</h2>

                    @if($instructor->specialties)
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-3">
                            @php
                                $specs = is_array($instructor->specialties) ? $instructor->specialties : [$instructor->specialties];
                            @endphp
                            @foreach($specs as $specialty)
                                <span class="badge badge-primary badge-outline">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($instructor->bio)
                        <p class="text-base-content/70 mt-4 leading-relaxed">{{ $instructor->bio }}</p>
                    @endif

                    @if($instructor->certifications)
                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-base-content/60 uppercase tracking-wide mb-2">Certifications</h4>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                @php
                                    $certs = is_array($instructor->certifications) ? $instructor->certifications : [$instructor->certifications];
                                @endphp
                                @foreach($certs as $cert)
                                    <span class="badge badge-ghost">{{ $cert }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Social Links --}}
                    @if($instructor->social_links && count(array_filter((array)$instructor->social_links)))
                        <div class="flex justify-center md:justify-start gap-2 mt-4">
                            @foreach(['instagram' => 'brand-instagram', 'facebook' => 'brand-facebook', 'twitter' => 'brand-x', 'website' => 'world'] as $key => $icon)
                                @if(!empty($instructor->social_links[$key]))
                                    <a href="{{ $instructor->social_links[$key] }}" target="_blank" rel="noopener"
                                       class="btn btn-ghost btn-sm btn-circle">
                                        <span class="icon-[tabler--{{ $icon }}] size-5"></span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Classes --}}
    @if($upcomingSessions->isNotEmpty())
    <section>
        <h3 class="text-lg font-bold text-base-content flex items-center gap-2 mb-4">
            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
            Upcoming Classes with {{ $instructor->name }}
        </h3>

        <div class="space-y-3">
            @foreach($upcomingSessions as $session)
            <div class="card bg-base-100 border border-base-200 card-hover">
                <div class="card-body p-4">
                    <div class="flex gap-4">
                        {{-- Date Badge --}}
                        <div class="shrink-0 w-14 h-14 rounded-xl bg-primary/10 flex flex-col items-center justify-center">
                            <span class="text-xs font-medium text-primary uppercase">
                                {{ $session->start_time->format('M') }}
                            </span>
                            <span class="text-xl font-bold text-primary leading-none">
                                {{ $session->start_time->format('j') }}
                            </span>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-base-content">
                                {{ $session->title ?? ($session->classPlan->name ?? 'Class') }}
                            </h4>
                            <p class="text-sm text-base-content/60">
                                {{ $session->start_time->format('l, g:i A') }} - {{ $session->end_time->format('g:i A') }}
                            </p>
                            @if($session->room)
                            <p class="text-xs text-base-content/50 mt-1 flex items-center gap-1">
                                <span class="icon-[tabler--map-pin] size-3"></span>
                                {{ $session->room->name }}@if($session->room->location), {{ $session->room->location->name }}@endif
                            </p>
                            @endif
                        </div>

                        {{-- Action --}}
                        <div class="shrink-0">
                            <button type="button" class="btn btn-outline btn-sm" disabled>
                                Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @else
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body py-8 text-center">
            <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-3"></span>
            <p class="text-base-content/60">No upcoming classes scheduled with {{ $instructor->name }}.</p>
        </div>
    </div>
    @endif

</div>
@endsection
