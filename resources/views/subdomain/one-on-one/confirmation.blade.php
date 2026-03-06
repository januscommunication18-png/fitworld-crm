@extends('layouts.subdomain')

@section('title', 'Meeting Confirmed — ' . $host->studio_name)

@section('content')
@include('subdomain.partials.navbar')

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Main Content --}}
    <div class="flex-1 py-8 md:py-12">
        <div class="container-fixed">
            <div class="max-w-2xl mx-auto">

                {{-- Success Hero --}}
                <div class="text-center mb-8">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 rounded-full bg-success/20 flex items-center justify-center animate-pulse">
                            <div class="w-20 h-20 rounded-full bg-success flex items-center justify-center">
                                <span class="icon-[tabler--check] size-10 text-success-content"></span>
                            </div>
                        </div>
                        <div class="absolute -right-1 -bottom-1 w-8 h-8 rounded-full bg-base-100 shadow-lg flex items-center justify-center">
                            <span class="icon-[tabler--confetti] size-5 text-warning"></span>
                        </div>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-success mb-2">Meeting Confirmed!</h1>
                    <p class="text-base-content/60 text-lg">Your 1:1 meeting has been scheduled.</p>
                </div>

                {{-- Meeting Details Card --}}
                <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-primary/20 mb-6 shadow-lg">
                    <div class="card-body">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center shrink-0">
                                @php
                                    $typeIcon = match($booking->meeting_type) {
                                        'in_person' => 'icon-[tabler--map-pin]',
                                        'phone' => 'icon-[tabler--phone]',
                                        'video' => 'icon-[tabler--video]',
                                        default => 'icon-[tabler--calendar-user]',
                                    };
                                @endphp
                                <span class="{{ $typeIcon }} size-8 text-primary"></span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-bold">1:1 Meeting with {{ $profile->display_name ?? $instructor->name }}</h2>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-base-content/70">
                                    <span class="flex items-center gap-1.5">
                                        <span class="icon-[tabler--calendar] size-4"></span>
                                        {{ $booking->start_time->format('l, F j, Y') }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        {{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="icon-[tabler--hourglass] size-4"></span>
                                        {{ $booking->duration_minutes }} min
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Meeting Type Details --}}
                        @if($booking->meeting_type === 'in_person' && $profile->in_person_location)
                        <div class="mt-4 p-4 bg-base-100 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--map-pin] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <label class="text-sm text-base-content/60">Location</label>
                                    <p class="font-medium">{{ $profile->in_person_location }}</p>
                                </div>
                            </div>
                        </div>
                        @elseif($booking->meeting_type === 'video' && $profile->video_link)
                        <div class="mt-4 p-4 bg-base-100 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--video] size-5 text-primary mt-0.5"></span>
                                <div class="flex-1">
                                    <label class="text-sm text-base-content/60">Video Meeting Link</label>
                                    <a href="{{ $profile->video_link }}" target="_blank" class="block font-medium text-primary hover:underline break-all">
                                        {{ $profile->video_link }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @elseif($booking->meeting_type === 'phone' && $profile->phone_number)
                        <div class="mt-4 p-4 bg-base-100 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--phone] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone Number</label>
                                    <p class="font-medium">{{ $profile->phone_number }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Email Confirmation --}}
                <div class="card bg-base-100 shadow-lg mb-6">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--mail-check] size-6 text-success"></span>
                            </div>
                            <div>
                                <h4 class="font-semibold">Confirmation Sent</h4>
                                <p class="text-sm text-base-content/60">
                                    We've sent a confirmation email to <strong>{{ $booking->guest_email }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Manage Booking CTA --}}
                <div class="card bg-base-100 border border-base-200 shadow-lg mb-6">
                    <div class="card-body text-center py-8">
                        <div class="w-14 h-14 rounded-2xl bg-info/10 flex items-center justify-center mx-auto mb-4">
                            <span class="icon-[tabler--settings] size-7 text-info"></span>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Need to Make Changes?</h3>
                        <p class="text-base-content/60 mb-4 max-w-md mx-auto">
                            You can reschedule or cancel your meeting at any time.
                        </p>
                        <a href="{{ route('subdomain.meeting.manage', ['subdomain' => $host->subdomain, 'token' => $booking->manage_token]) }}" class="btn btn-info btn-soft gap-2">
                            <span class="icon-[tabler--edit] size-5"></span>
                            Manage My Booking
                        </a>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-outline btn-lg gap-2">
                        <span class="icon-[tabler--home] size-5"></span>
                        Back to Home
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endsection
