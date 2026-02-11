@extends('layouts.subdomain')

@section('title', ($session->title ?? $session->classPlan->name ?? 'Class') . ' — ' . $host->studio_name)

@section('content')
@php
    $logoUrl = $host->logo_path ? Storage::disk(config('filesystems.uploads'))->url($host->logo_path) : null;
    $instructorPhotoUrl = $session->primaryInstructor?->photo_path
        ? Storage::disk(config('filesystems.uploads'))->url($session->primaryInstructor->photo_path)
        : null;
    $instructorInitials = $session->primaryInstructor
        ? collect(explode(' ', $session->primaryInstructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('')
        : '';
@endphp

{{-- Header --}}
<div class="bg-base-200 border-b border-base-300">
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center gap-4">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $host->studio_name }}" class="h-10 w-auto">
            @else
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building-community] size-5 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="font-bold text-lg text-base-content">{{ $host->studio_name }}</h1>
                <p class="text-sm text-base-content/60">Class Details</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="max-w-5xl mx-auto w-full px-4 py-8 space-y-6">

    {{-- Back Link --}}
    <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-primary transition-colors">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Schedule
    </a>

    {{-- Class Details Card --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Date/Time Badge --}}
                <div class="shrink-0 flex flex-col items-center">
                    <div class="w-24 h-24 rounded-2xl bg-primary text-primary-content flex flex-col items-center justify-center">
                        <span class="text-sm font-medium uppercase">{{ $session->start_time->format('M') }}</span>
                        <span class="text-3xl font-bold leading-none">{{ $session->start_time->format('j') }}</span>
                        <span class="text-sm">{{ $session->start_time->format('D') }}</span>
                    </div>
                    <div class="text-center mt-2">
                        <div class="text-lg font-bold text-base-content">{{ $session->start_time->format('g:i A') }}</div>
                        <div class="text-sm text-base-content/60">{{ $session->duration_minutes }} min</div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="flex-1">
                    <h2 class="text-2xl md:text-3xl font-bold text-base-content">
                        {{ $session->title ?? ($session->classPlan->name ?? 'Class') }}
                    </h2>

                    @if($session->classPlan?->description)
                        <p class="text-base-content/70 mt-3 leading-relaxed">{{ $session->classPlan->description }}</p>
                    @endif

                    {{-- Meta Info --}}
                    <div class="flex flex-wrap gap-4 mt-4">
                        @if($session->room)
                        <div class="flex items-center gap-2 text-base-content/60">
                            <span class="icon-[tabler--map-pin] size-5"></span>
                            <span>{{ $session->room->name }}@if($session->room->location), {{ $session->room->location->name }}@endif</span>
                        </div>
                        @endif

                        @php
                            $spotsLeft = $session->capacity - ($session->bookings_count ?? 0);
                        @endphp
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--users] size-5 text-base-content/60"></span>
                            @if($spotsLeft > 0)
                                <span class="badge badge-success">{{ $spotsLeft }} spots available</span>
                            @else
                                <span class="badge badge-error">Class Full</span>
                            @endif
                        </div>

                        @if($session->price && $session->price > 0)
                        <div class="flex items-center gap-2 text-base-content/60">
                            <span class="icon-[tabler--currency-dollar] size-5"></span>
                            <span class="font-semibold">${{ number_format($session->price, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Book Button --}}
                    <div class="mt-6">
                        <button type="button" class="btn btn-primary btn-lg" disabled>
                            <span class="icon-[tabler--clock] size-5"></span>
                            Booking Coming Soon
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructor Info --}}
    @if($session->primaryInstructor)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2 mb-4">
                <span class="icon-[tabler--user] size-5 text-primary"></span>
                Your Instructor
            </h3>

            <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $session->primaryInstructor->id]) }}"
               class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors">
                @if($instructorPhotoUrl)
                    <img src="{{ $instructorPhotoUrl }}" alt="{{ $session->primaryInstructor->name }}"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-base-200">
                @else
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center ring-2 ring-base-200">
                        <span class="text-xl font-bold text-primary">{{ $instructorInitials }}</span>
                    </div>
                @endif
                <div class="flex-1">
                    <h4 class="font-semibold text-base-content">{{ $session->primaryInstructor->name }}</h4>
                    @if($session->primaryInstructor->specialties)
                        <p class="text-sm text-base-content/60">
                            {{ is_array($session->primaryInstructor->specialties) ? implode(', ', array_slice($session->primaryInstructor->specialties, 0, 3)) : $session->primaryInstructor->specialties }}
                        </p>
                    @endif
                </div>
                <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
            </a>
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($session->notes)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2 mb-3">
                <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                Class Notes
            </h3>
            <p class="text-base-content/70">{{ $session->notes }}</p>
        </div>
    </div>
    @endif

</div>
@endsection
