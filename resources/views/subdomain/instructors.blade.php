@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Instructors')

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
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 me-1"></span> Schedule
            </a>
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--users] size-4 me-1"></span> Instructors
            </a>
        </div>
    </div>

    {{-- Instructors Grid --}}
    @if($instructors->isEmpty())
        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-16 text-center">
                <span class="icon-[tabler--users-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="font-semibold text-lg text-base-content">No Instructors Yet</h3>
                <p class="text-base-content/60 mt-1">Check back soon to meet our team!</p>
                <div class="mt-6">
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($instructors as $instructor)
            @php
                $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
            @endphp
            <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
               class="card bg-base-100 border border-base-200 card-hover animate-fade-in">
                <div class="card-body p-5">
                    <div class="flex items-start gap-4">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                 class="w-20 h-20 rounded-xl object-cover shrink-0 ring-2 ring-base-200">
                        @else
                            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center shrink-0 ring-2 ring-base-200">
                                <span class="text-2xl font-bold text-primary">{{ $initials }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-lg text-base-content">{{ $instructor->name }}</h3>
                            @if($instructor->specialties)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @php
                                        $specs = is_array($instructor->specialties) ? $instructor->specialties : [$instructor->specialties];
                                    @endphp
                                    @foreach(array_slice($specs, 0, 3) as $specialty)
                                        <span class="badge badge-sm badge-primary badge-outline">{{ $specialty }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($instructor->bio)
                                <p class="text-sm text-base-content/60 mt-3 line-clamp-2">{{ $instructor->bio }}</p>
                            @endif
                            <div class="mt-3 flex items-center gap-1 text-sm text-primary font-medium">
                                View Profile
                                <span class="icon-[tabler--chevron-right] size-4"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
