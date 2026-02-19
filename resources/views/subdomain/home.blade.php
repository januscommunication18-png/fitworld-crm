@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Book a Class')

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

            {{-- Right: Social Icons + Request Booking + Member Login --}}
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

                {{-- Request Booking Button --}}
                @if($servicePlans->isNotEmpty())
                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain]) }}"
                   class="btn btn-primary btn-sm sm:btn-md">
                    <span class="icon-[tabler--calendar-plus] size-5 hidden sm:inline"></span>
                    Request Booking
                </a>
                @endif

                {{-- Member Login --}}
                @if($host->isMemberPortalEnabled())
                    @if(auth('member')->check())
                        {{-- Already logged in --}}
                        <a href="{{ route('member.portal', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--user] size-5"></span>
                            <span class="hidden sm:inline">My Portal</span>
                        </a>
                    @else
                        <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </a>
                    @endif
                @else
                    <div class="relative group">
                        <button class="btn btn-ghost btn-sm sm:btn-md" disabled>
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </button>
                        <div class="absolute top-full right-0 mt-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            <span class="badge badge-sm badge-neutral whitespace-nowrap">Coming Soon</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

{{-- Hero Section --}}
<section class="relative min-h-[320px] md:min-h-[400px] flex items-center"
         @if($host->cover_image_url)
         style="background-image: url('{{ $host->cover_image_url }}'); background-size: cover; background-position: center;"
         @endif>

    {{-- Gradient Overlay --}}
    @if($host->cover_image_url)
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary to-secondary"></div>
    @endif

    {{-- Hero Content --}}
    <div class="relative w-full py-12">
        <div class="container-fixed text-center">
            {{-- Studio Name --}}
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">
                {{ $bookingSettings['display_name'] ?? $host->studio_name }}
            </h1>

            {{-- Tagline/About --}}
            @if($bookingSettings['about_text'] ?? $host->about)
            <p class="text-white/90 text-lg md:text-xl max-w-2xl mx-auto mb-6">
                {{ Str::limit($bookingSettings['about_text'] ?? $host->about, 150) }}
            </p>
            @endif

            {{-- Location --}}
            @if(($host->show_address ?? true) && $host->address)
            <p class="text-white/80 text-base flex items-center justify-center gap-2">
                <span class="icon-[tabler--map-pin] size-5"></span>
                {{ is_array($host->address) ? ($host->address['city'] ?? $host->address['street'] ?? '') : $host->address }}
            </p>
            @endif
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="container-fixed py-10">

    {{-- Page Navigation Tabs --}}
    <div class="flex justify-center mb-10">
        <div class="tabs tabs-boxed bg-base-200 p-1">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--home] size-4 me-1"></span> Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 me-1"></span> Schedule
            </a>
            @if($bookingSettings['show_instructors'] ?? true)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--users] size-4 me-1"></span> Instructors
            </a>
            @endif
        </div>
    </div>

    {{-- Services Section --}}
    @if($servicePlans->isNotEmpty())
    <section class="mb-12">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold">Our Services</h2>
            <p class="text-base-content/60 mt-1">Book a private session or request information</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($servicePlans as $service)
            <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow border border-base-200">
                <div class="card-body">
                    {{-- Icon & Price --}}
                    <div class="flex items-start justify-between">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background-color: {{ $service->color ?? '#6366f1' }}15;">
                            <span class="icon-[tabler--sparkles] size-7" style="color: {{ $service->color ?? '#6366f1' }};"></span>
                        </div>
                        @if($service->price)
                        <div class="text-2xl font-bold" style="color: {{ $service->color ?? '#6366f1' }};">
                            ${{ number_format($service->price, 0) }}
                        </div>
                        @endif
                    </div>

                    {{-- Name & Description --}}
                    <h3 class="card-title text-lg mt-4">{{ $service->name }}</h3>
                    @if($service->description)
                    <p class="text-sm text-base-content/60 line-clamp-2">{{ $service->description }}</p>
                    @endif

                    {{-- Meta --}}
                    <div class="flex items-center gap-4 text-sm text-base-content/50 mt-2">
                        @if($service->duration_minutes)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $service->duration_minutes }} min
                        </span>
                        @endif
                    </div>

                    {{-- Buttons --}}
                    <div class="card-actions mt-4 flex-col gap-2">
                        <a href="{{ route('booking.select-service', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                           class="btn btn-primary w-full">
                            <span class="icon-[tabler--calendar-plus] size-5"></span>
                            Book Now
                        </a>
                        <a href="{{ route('subdomain.service-request.plan', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                           class="btn btn-ghost btn-sm w-full">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            Request Info
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Upcoming Classes --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Upcoming Classes</h2>
                <p class="text-base-content/60 mt-1">Join our group fitness sessions</p>
            </div>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                View All <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
            </a>
        </div>

        @if($upcomingSessions->isEmpty())
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body items-center text-center py-16">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-4">
                    <span class="icon-[tabler--calendar-off] size-10 text-base-content/30"></span>
                </div>
                <h3 class="text-lg font-semibold">No Upcoming Classes</h3>
                <p class="text-base-content/60">Check back soon for new classes!</p>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($upcomingSessions->take(6) as $session)
            <div class="card card-side bg-base-100 shadow border border-base-200 overflow-hidden hover:shadow-lg transition-shadow">
                {{-- Date Badge --}}
                <div class="flex flex-col items-center justify-center w-24 bg-primary text-primary-content shrink-0">
                    <span class="text-sm font-medium uppercase">{{ $session->start_time->format('M') }}</span>
                    <span class="text-3xl font-bold leading-none">{{ $session->start_time->format('j') }}</span>
                    <span class="text-sm">{{ $session->start_time->format('D') }}</span>
                </div>

                {{-- Content --}}
                <div class="card-body p-4">
                    <h3 class="card-title text-base">{{ $session->title ?? ($session->classPlan->name ?? 'Class') }}</h3>

                    <div class="text-sm text-base-content/60 space-y-1">
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                        </p>
                        @if($session->primaryInstructor)
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $session->primaryInstructor->name }}
                        </p>
                        @endif
                        @if($session->room)
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--map-pin] size-4"></span>
                            {{ $session->room->name }}
                        </p>
                        @endif
                    </div>

                    <div class="card-actions justify-between items-center mt-auto pt-2">
                        @php $spotsLeft = $session->capacity - ($session->bookings_count ?? 0); @endphp
                        @if($spotsLeft <= 0)
                            <span class="badge badge-error">Full</span>
                            <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                               class="btn btn-warning btn-sm">
                                Join Waitlist
                            </a>
                        @else
                            @if($spotsLeft <= 3)
                                <span class="badge badge-warning">{{ $spotsLeft }} left</span>
                            @else
                                <span class="text-xs text-base-content/40">{{ $spotsLeft }} spots</span>
                            @endif
                            <div class="flex gap-1">
                                <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id]) }}"
                                   class="btn btn-ghost btn-xs">
                                    Info
                                </a>
                                <a href="{{ route('booking.select-class.filter', ['subdomain' => $host->subdomain, 'classPlanId' => $session->class_plan_id]) }}"
                                   class="btn btn-primary btn-sm">
                                    Book
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Instructors --}}
    @if(($bookingSettings['show_instructors'] ?? true) && $instructors->isNotEmpty())
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Meet Our Instructors</h2>
                <p class="text-base-content/60 mt-1">Expert guidance for your fitness journey</p>
            </div>
            @if($instructors->count() > 4)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                View All <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
            </a>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($instructors->take(4) as $instructor)
            @php
                $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
            @endphp
            <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
               class="card bg-base-100 shadow border border-base-200 hover:shadow-lg hover:border-primary/50 transition-all">
                <div class="card-body items-center text-center p-5">
                    @if($instructor->photo_url && ($bookingSettings['show_instructor_photos'] ?? true))
                    <div class="avatar">
                        <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                        </div>
                    </div>
                    @else
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content w-24 rounded-full">
                            <span class="text-3xl">{{ $initials }}</span>
                        </div>
                    </div>
                    @endif

                    <h3 class="font-semibold mt-4">{{ $instructor->name }}</h3>
                    @if($instructor->specialties)
                    <p class="text-xs text-base-content/60">
                        {{ is_array($instructor->specialties) ? implode(', ', array_slice($instructor->specialties, 0, 2)) : $instructor->specialties }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Contact --}}
    @if($host->phone || $host->studio_email || $host->address)
    <section>
        <div class="card bg-base-200 border border-base-300">
            <div class="card-body">
                <h2 class="card-title mb-6">
                    <span class="icon-[tabler--message-circle] size-6 text-primary"></span>
                    Get in Touch
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($host->phone)
                    <a href="tel:{{ $host->phone }}" class="flex items-center gap-4 p-4 rounded-xl bg-base-100 hover:bg-primary/5 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--phone] size-6 text-primary"></span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/50 uppercase font-medium">Phone</p>
                            <p class="font-semibold">{{ $host->phone }}</p>
                        </div>
                    </a>
                    @endif

                    @if($host->studio_email)
                    <a href="mailto:{{ $host->studio_email }}" class="flex items-center gap-4 p-4 rounded-xl bg-base-100 hover:bg-primary/5 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--mail] size-6 text-primary"></span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/50 uppercase font-medium">Email</p>
                            <p class="font-semibold truncate">{{ $host->studio_email }}</p>
                        </div>
                    </a>
                    @endif

                    @if($host->address)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-base-100">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--map-pin] size-6 text-primary"></span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/50 uppercase font-medium">Location</p>
                            <p class="font-semibold">{{ is_array($host->address) ? ($host->address['street'] ?? $host->address['city'] ?? '') : $host->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

</div>

{{-- Toast --}}
@if(session('success'))
<div class="toast toast-top toast-center z-50">
    <div class="alert alert-success"><span class="icon-[tabler--check] size-5"></span> {{ session('success') }}</div>
</div>
<script>setTimeout(() => document.querySelector('.toast')?.remove(), 4000);</script>
@endif

@endsection
