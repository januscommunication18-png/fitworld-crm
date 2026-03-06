@extends('layouts.subdomain')

@section('title', 'Meeting Cancelled — ' . $host->studio_name)

@section('content')
@include('subdomain.partials.navbar')

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Main Content --}}
    <div class="flex-1 py-8 md:py-12">
        <div class="container-fixed">
            <div class="max-w-2xl mx-auto text-center">

                {{-- Cancelled Icon --}}
                <div class="w-24 h-24 rounded-full bg-error/20 flex items-center justify-center mx-auto mb-6">
                    <span class="icon-[tabler--calendar-off] size-12 text-error"></span>
                </div>

                <h1 class="text-3xl font-bold mb-2">Meeting Cancelled</h1>
                <p class="text-base-content/60 text-lg mb-8">Your meeting has been cancelled successfully.</p>

                {{-- Original Meeting Details --}}
                <div class="card bg-base-100 shadow-lg mb-8 text-left">
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Cancelled Meeting Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="space-y-1">
                                <label class="text-base-content/60">With</label>
                                <p class="font-medium">{{ $instructor->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-base-content/60">Original Date</label>
                                <p class="font-medium line-through">{{ $booking->start_time->format('l, F j, Y') }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-base-content/60">Original Time</label>
                                <p class="font-medium line-through">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-base-content/60">Cancelled</label>
                                <p class="font-medium">{{ $booking->cancelled_at?->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rebook CTA --}}
                <div class="card bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 mb-8">
                    <div class="card-body text-center py-8">
                        <div class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center mx-auto mb-4">
                            <span class="icon-[tabler--calendar-plus] size-8 text-primary"></span>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Want to Book Again?</h3>
                        <p class="text-base-content/60 mb-4">
                            You can schedule a new meeting with {{ $instructor->name }} at any time.
                        </p>
                        <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}" class="btn btn-primary gap-2">
                            <span class="icon-[tabler--calendar-plus] size-5"></span>
                            Book a New Meeting
                        </a>
                    </div>
                </div>

                {{-- Back to Home --}}
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost gap-2">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back to Home
                </a>

            </div>
        </div>
    </div>
</div>
@endsection
