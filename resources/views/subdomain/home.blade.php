@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Book a Class')

@section('content')
<div class="w-full max-w-4xl space-y-8">

    {{-- Hero/Welcome Section --}}
    @if($bookingSettings['about_text'] ?? $host->about)
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-base-content/70">{{ $bookingSettings['about_text'] ?? $host->about }}</p>
    </div>
    @endif

    {{-- Navigation Tabs --}}
    <div class="flex justify-center">
        <div class="tabs tabs-boxed bg-base-100">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--home] size-4 mr-2"></span>
                Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 mr-2"></span>
                Full Schedule
            </a>
            @if($bookingSettings['show_instructors'] ?? true)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--users] size-4 mr-2"></span>
                Instructors
            </a>
            @endif
        </div>
    </div>

    {{-- Upcoming Classes --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--calendar-event] size-5"></span>
                Upcoming Classes
            </h2>

            @if($upcomingSessions->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-4"></span>
                    <p class="text-base-content/60">No upcoming classes scheduled.</p>
                    <p class="text-sm text-base-content/40 mt-1">Check back soon for new classes!</p>
                </div>
            @else
                <div class="divide-y divide-base-200">
                    @foreach($upcomingSessions as $session)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start gap-4">
                            {{-- Date/Time --}}
                            <div class="text-center min-w-[60px]">
                                <div class="text-xs text-base-content/60 uppercase">
                                    {{ $session->starts_at->format('D') }}
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ $session->starts_at->format('j') }}
                                </div>
                                <div class="text-xs text-base-content/60">
                                    {{ $session->starts_at->format('M') }}
                                </div>
                            </div>

                            {{-- Class Details --}}
                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-semibold">{{ $session->classPlan->name ?? 'Class' }}</h3>
                                        <p class="text-sm text-base-content/60">
                                            {{ $session->starts_at->format('g:i A') }} - {{ $session->ends_at->format('g:i A') }}
                                            @if($session->instructor)
                                                <span class="mx-1">&bull;</span>
                                                {{ $session->instructor->full_name }}
                                            @endif
                                        </p>
                                        @if($session->room)
                                        <p class="text-xs text-base-content/50 mt-1">
                                            <span class="icon-[tabler--map-pin] size-3 inline"></span>
                                            {{ $session->room->name }}
                                            @if($session->room->location)
                                                - {{ $session->room->location->name }}
                                            @endif
                                        </p>
                                        @endif
                                    </div>

                                    {{-- Spots Available --}}
                                    <div class="text-right">
                                        @php
                                            $spotsLeft = $session->capacity - ($session->bookings_count ?? 0);
                                        @endphp
                                        @if($spotsLeft > 0)
                                            <span class="badge badge-success badge-sm">
                                                {{ $spotsLeft }} spot{{ $spotsLeft !== 1 ? 's' : '' }} left
                                            </span>
                                        @else
                                            <span class="badge badge-warning badge-sm">Full</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Book Button --}}
                                <div class="mt-3">
                                    <a href="{{ route('subdomain.class', ['subdomain' => $host->subdomain, 'classSession' => $session->id]) }}"
                                       class="btn btn-primary btn-sm">
                                        @if($spotsLeft > 0)
                                            Book Now
                                        @else
                                            Join Waitlist
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pt-4 text-center">
                    <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        View Full Schedule
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Instructors Section --}}
    @if(($bookingSettings['show_instructors'] ?? true) && $instructors->isNotEmpty())
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--users] size-5"></span>
                Our Instructors
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                @foreach($instructors->take(4) as $instructor)
                <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                   class="flex flex-col items-center p-4 rounded-lg hover:bg-base-200 transition-colors">
                    @if($instructor->photo_url && ($bookingSettings['show_instructor_photos'] ?? true))
                        <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->full_name }}"
                             class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-lg font-bold text-primary">{{ $instructor->initials }}</span>
                        </div>
                    @endif
                    <span class="mt-2 font-medium text-sm text-center">{{ $instructor->full_name }}</span>
                    @if($instructor->specialties)
                        <span class="text-xs text-base-content/60 text-center">
                            {{ is_array($instructor->specialties) ? implode(', ', array_slice($instructor->specialties, 0, 2)) : $instructor->specialties }}
                        </span>
                    @endif
                </a>
                @endforeach
            </div>

            @if($instructors->count() > 4)
            <div class="pt-4 text-center">
                <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    View All Instructors
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Contact/Location Info --}}
    @if($host->phone || $host->studio_email || $host->address)
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--info-circle] size-5"></span>
                Contact Us
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @if($host->phone)
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--phone] size-5 text-base-content/60"></span>
                    <div>
                        <p class="text-xs text-base-content/60">Phone</p>
                        <a href="tel:{{ $host->phone }}" class="link link-primary">{{ $host->phone }}</a>
                    </div>
                </div>
                @endif

                @if($host->studio_email)
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--mail] size-5 text-base-content/60"></span>
                    <div>
                        <p class="text-xs text-base-content/60">Email</p>
                        <a href="mailto:{{ $host->studio_email }}" class="link link-primary">{{ $host->studio_email }}</a>
                    </div>
                </div>
                @endif

                @if($host->address)
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--map-pin] size-5 text-base-content/60"></span>
                    <div>
                        <p class="text-xs text-base-content/60">Location</p>
                        <p>{{ is_array($host->address) ? ($host->address['street'] ?? '') : $host->address }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
