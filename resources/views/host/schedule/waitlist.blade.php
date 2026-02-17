@extends('layouts.dashboard')

@section('title', 'Waitlist')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Waitlist</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Waitlist</h1>
        <p class="text-base-content/60 mt-1">Manage waitlists for fully booked classes.</p>
    </div>

    {{-- Sub Navigation --}}
    @include('host.schedule.partials.sub-nav')

    {{-- Coming Soon Card --}}
    <div class="card bg-base-100">
        <div class="card-body text-center py-16">
            <div class="w-20 h-20 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-6">
                <span class="icon-[tabler--clock] size-10 text-warning"></span>
            </div>
            <h2 class="text-2xl font-bold mb-2">Coming Soon</h2>
            <p class="text-base-content/60 max-w-md mx-auto mb-6">
                The Waitlist feature is currently in development. Soon you'll be able to automatically manage waitlists when classes fill up.
            </p>
            <div class="flex flex-col items-center gap-4">
                <div class="bg-base-200/50 rounded-xl p-4 max-w-sm">
                    <h3 class="font-semibold mb-2">What's coming:</h3>
                    <ul class="text-sm text-base-content/70 space-y-2 text-left">
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Automatic waitlist when classes are full
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Notify clients when spots open up
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            First-come, first-served or priority-based
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Auto-convert waitlist to booking on cancellation
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
