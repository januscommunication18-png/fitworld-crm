@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — ' . ($trans['nav.instructors'] ?? 'Our Team'))

@section('content')

@include('subdomain.partials.navbar')

{{-- Hero Section --}}
<div class="bg-gradient-to-br from-primary/5 via-base-100 to-secondary/5 border-b border-base-200">
    <div class="container-fixed py-12 md:py-16">
        <div class="text-center max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-medium mb-4">
                <span class="icon-[tabler--users] size-4"></span>
                {{ $trans['subdomain.instructors.meet_team'] ?? 'Meet Our Team' }}
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-base-content">
                {{ $trans['subdomain.instructors.title'] ?? 'Expert Instructors' }}
            </h1>
            <p class="text-base-content/60 mt-3 text-lg">
                {{ $trans['subdomain.instructors.subtitle'] ?? 'Our talented team is here to guide you on your fitness journey' }}
            </p>
        </div>

        {{-- Page Navigation Tabs --}}
        <div class="flex justify-center mt-8">
            <div class="tabs tabs-boxed bg-base-100 shadow-sm p-1.5 border border-base-200">
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab gap-2">
                    <span class="icon-[tabler--home] size-4"></span>
                    <span class="hidden sm:inline">{{ $trans['nav.dashboard'] ?? 'Home' }}</span>
                </a>
                <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab gap-2">
                    <span class="icon-[tabler--calendar] size-4"></span>
                    <span class="hidden sm:inline">{{ $trans['nav.schedule'] ?? 'Schedule' }}</span>
                </a>
                <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab tab-active gap-2">
                    <span class="icon-[tabler--users] size-4"></span>
                    <span class="hidden sm:inline">{{ $trans['nav.instructors'] ?? 'Instructors' }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="container-fixed py-10">

    {{-- Instructors Grid --}}
    @if($instructors->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--users-off] size-10 text-base-content/30"></span>
            </div>
            <h3 class="font-semibold text-xl text-base-content">{{ $trans['subdomain.instructors.no_instructors'] ?? 'No Instructors Yet' }}</h3>
            <p class="text-base-content/60 mt-2">{{ $trans['subdomain.instructors.check_back'] ?? 'Check back soon to meet our amazing team!' }}</p>
            <div class="mt-6">
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    {{ $trans['btn.back_home'] ?? 'Back to Home' }}
                </a>
            </div>
        </div>
    @else
        {{-- Stats Bar --}}
        <div class="flex items-center justify-center gap-6 mb-10">
            <div class="flex items-center gap-2 text-base-content/60">
                <span class="icon-[tabler--users] size-5 text-primary"></span>
                <span class="font-medium">{{ $instructors->count() }} {{ $instructors->count() === 1 ? 'Instructor' : 'Instructors' }}</span>
            </div>
            @php
                $totalSpecialties = $instructors->pluck('specialties')->flatten()->unique()->count();
            @endphp
            @if($totalSpecialties > 0)
            <div class="w-px h-5 bg-base-300"></div>
            <div class="flex items-center gap-2 text-base-content/60">
                <span class="icon-[tabler--star] size-5 text-secondary"></span>
                <span class="font-medium">{{ $totalSpecialties }} Specialties</span>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
            @foreach($instructors as $instructor)
            @php
                $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                $hasBookingProfile = $instructor->bookingProfile && $instructor->bookingProfile->canAcceptBookings();
            @endphp
            <div class="text-center group">
                {{-- Avatar --}}
                <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}" class="block">
                    @if($instructor->photo_url)
                        <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                             class="w-32 h-32 rounded-full object-cover mx-auto ring-4 ring-base-200 group-hover:ring-primary/30 transition-all duration-300 group-hover:scale-105">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center mx-auto ring-4 ring-base-200 group-hover:ring-primary/30 transition-all duration-300 group-hover:scale-105">
                            <span class="text-4xl font-bold text-white">{{ $initials }}</span>
                        </div>
                    @endif
                </a>

                {{-- Info --}}
                <div class="mt-5">
                    <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                       class="inline-block">
                        <h3 class="font-bold text-xl text-base-content group-hover:text-primary transition-colors">
                            {{ $instructor->name }}
                        </h3>
                    </a>

                    @if($hasBookingProfile)
                    <span class="badge badge-success badge-soft badge-sm ml-2">
                        1:1 Available
                    </span>
                    @endif

                    {{-- Specialties --}}
                    @if($instructor->specialties)
                        <div class="flex flex-wrap justify-center gap-1.5 mt-3">
                            @php
                                $specs = is_array($instructor->specialties) ? $instructor->specialties : [$instructor->specialties];
                            @endphp
                            @foreach(array_slice($specs, 0, 3) as $specialty)
                                <span class="badge badge-sm badge-primary badge-outline">{{ $specialty }}</span>
                            @endforeach
                            @if(count($specs) > 3)
                                <span class="badge badge-sm badge-ghost">+{{ count($specs) - 3 }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Bio --}}
                    @if($instructor->bio)
                        <p class="text-sm text-base-content/60 mt-3 line-clamp-2 max-w-xs mx-auto">{{ $instructor->bio }}</p>
                    @endif

                    {{-- Social Links --}}
                    @if(($instructor->show_social_links ?? true) && $instructor->social_links && count(array_filter((array)$instructor->social_links)))
                    <div class="flex items-center justify-center gap-2 mt-4">
                        @foreach(['instagram' => 'brand-instagram', 'facebook' => 'brand-facebook', 'twitter' => 'brand-x'] as $key => $icon)
                            @if(!empty($instructor->social_links[$key]))
                            <a href="{{ $instructor->social_links[$key] }}" target="_blank" rel="noopener"
                               class="w-8 h-8 rounded-full bg-base-200 hover:bg-primary hover:text-white flex items-center justify-center transition-colors">
                                <span class="icon-[tabler--{{ $icon }}] size-4"></span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center justify-center gap-2 mt-5">
                        <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                           class="btn btn-ghost btn-sm">
                            View Profile
                        </a>
                        @if($hasBookingProfile)
                        <a href="{{ route('subdomain.instructor.book-meeting', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                           class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--calendar-plus] size-4"></span>
                            Book 1:1
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Footer CTA --}}
@if($instructors->isNotEmpty())
<div class="bg-gradient-to-r from-primary/5 via-secondary/5 to-primary/5 border-t border-base-200">
    <div class="container-fixed py-12">
        <div class="text-center max-w-xl mx-auto">
            <h2 class="text-2xl font-bold text-base-content">Ready to Start Your Journey?</h2>
            <p class="text-base-content/60 mt-2">Check out our class schedule and find the perfect session for you.</p>
            <div class="flex items-center justify-center gap-3 mt-6">
                <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--calendar] size-5"></span>
                    View Schedule
                </a>
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost">
                    <span class="icon-[tabler--home] size-5"></span>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
