@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Schedule')

@section('content')
<div class="w-full max-w-4xl space-y-8">

    {{-- Navigation Tabs --}}
    <div class="flex justify-center">
        <div class="tabs tabs-boxed bg-base-100">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--home] size-4 mr-2"></span>
                Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
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

    {{-- Schedule by Date --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--calendar] size-5"></span>
                Class Schedule
            </h2>

            @if($sessionsByDate->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto mb-4"></span>
                    <p class="text-base-content/60">No classes scheduled for the next 30 days.</p>
                </div>
            @else
                <div class="space-y-6 mt-4">
                    @foreach($sessionsByDate as $date => $sessions)
                    <div>
                        <h3 class="font-semibold text-sm text-base-content/60 uppercase tracking-wider mb-3">
                            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                        </h3>
                        <div class="divide-y divide-base-200 border border-base-200 rounded-lg">
                            @foreach($sessions as $session)
                            <div class="p-4 hover:bg-base-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    {{-- Time --}}
                                    <div class="text-center min-w-[70px]">
                                        <div class="font-bold">{{ $session->starts_at->format('g:i A') }}</div>
                                        <div class="text-xs text-base-content/50">{{ $session->duration_minutes }} min</div>
                                    </div>

                                    {{-- Class Info --}}
                                    <div class="flex-1">
                                        <h4 class="font-semibold">{{ $session->classPlan->name ?? 'Class' }}</h4>
                                        <p class="text-sm text-base-content/60">
                                            @if($session->instructor)
                                                with {{ $session->instructor->full_name }}
                                            @endif
                                            @if($session->room)
                                                <span class="mx-1">&bull;</span>
                                                {{ $session->room->name }}
                                            @endif
                                        </p>
                                    </div>

                                    {{-- Spots & Action --}}
                                    <div class="text-right">
                                        @php
                                            $spotsLeft = $session->capacity - ($session->bookings_count ?? 0);
                                        @endphp
                                        @if($spotsLeft > 0)
                                            <span class="badge badge-success badge-sm mb-2">
                                                {{ $spotsLeft }} spots
                                            </span>
                                        @else
                                            <span class="badge badge-warning badge-sm mb-2">Full</span>
                                        @endif
                                        <br>
                                        <a href="{{ route('subdomain.class', ['subdomain' => $host->subdomain, 'classSession' => $session->id]) }}"
                                           class="btn btn-primary btn-sm">
                                            @if($spotsLeft > 0)
                                                Book
                                            @else
                                                Waitlist
                                            @endif
                                        </a>
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
    </div>

</div>
@endsection
