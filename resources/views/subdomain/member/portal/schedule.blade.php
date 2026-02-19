@extends('layouts.subdomain')

@section('title', 'Classes — ' . $host->studio_name)

@section('content')
@php
    // Helper function to check if a class plan is covered by any active membership
    $isCoveredByMembership = function($classPlan) use ($activeMemberships) {
        if (!$classPlan) return null;
        foreach ($activeMemberships as $membership) {
            if ($membership->canUseForClassPlan($classPlan) && $membership->hasAvailableCredits()) {
                return $membership;
            }
        }
        return null;
    };
@endphp

<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            {{-- Active Membership Banner --}}
            @if($activeMemberships->count() > 0)
            <div class="alert bg-success/10 text-success border-success/20 mb-6">
                <span class="icon-[tabler--id-badge-2] size-5"></span>
                <div>
                    <h4 class="font-semibold">Active Membership</h4>
                    <p class="text-sm opacity-80">
                        @foreach($activeMemberships as $membership)
                            <span class="font-medium">{{ $membership->membershipPlan->name }}</span>
                            @if(!$membership->is_unlimited)
                                ({{ $membership->credits_remaining }} credits remaining)
                            @else
                                (Unlimited)
                            @endif
                            @if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Booking</h1>
            </div>

            {{-- Tabs --}}
            <div class="tabs tabs-boxed bg-base-100 w-fit mb-6">
                <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    All
                </a>
                <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}"
                   class="tab tab-active">
                    Classes
                </a>
                <a href="{{ route('member.portal.services', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    Services
                </a>
                <a href="{{ route('member.portal.memberships', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    Memberships
                </a>
            </div>

            @if($sessionsByDate->count() > 0)
                @foreach($sessionsByDate as $date => $sessions)
                <div class="mb-6">
                    <h2 class="font-semibold text-lg mb-3 flex items-center gap-2">
                        <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h2>

                    <div class="space-y-3">
                        @foreach($sessions as $session)
                        @php
                            $coveringMembership = $isCoveredByMembership($session->classPlan);
                        @endphp
                        <div class="card bg-base-100 {{ $coveringMembership ? 'border-l-4 border-l-success' : '' }}">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-4">
                                    <div class="text-center min-w-[60px]">
                                        <p class="text-lg font-bold {{ $coveringMembership ? 'text-success' : '' }}">{{ $session->start_time->format('g:i') }}</p>
                                        <p class="text-xs text-base-content/60">{{ $session->start_time->format('A') }}</p>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium">{{ $session->display_title ?? $session->classPlan?->name }}</p>
                                        <p class="text-sm text-base-content/60">
                                            {{ $session->primaryInstructor?->name ?? 'TBA' }}
                                            @if($session->room?->location)
                                                • {{ $session->room->location->name }}
                                            @endif
                                            • {{ $session->duration_minutes ?? 60 }} min
                                        </p>
                                        @if($coveringMembership)
                                        <span class="badge badge-success badge-sm mt-1 gap-1">
                                            <span class="icon-[tabler--id-badge-2] size-3"></span>
                                            Included in {{ $coveringMembership->membershipPlan->name }}
                                        </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if(in_array($session->id, $bookedSessionIds))
                                            <span class="badge badge-success">Booked</span>
                                        @elseif($session->is_full)
                                            @if($session->allow_waitlist)
                                                <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                                                   class="btn btn-warning btn-sm">
                                                    Join Waitlist
                                                </a>
                                            @else
                                                <span class="badge badge-error">Full</span>
                                            @endif
                                        @else
                                            <span class="text-sm text-base-content/60 hidden sm:inline">
                                                {{ $session->spots_remaining }} spots
                                            </span>
                                            <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id]) }}"
                                               class="btn btn-ghost btn-sm">
                                                Request Info
                                            </a>
                                            <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn {{ $coveringMembership ? 'btn-success' : 'btn-primary' }} btn-sm">
                                                    {{ $coveringMembership ? 'Book Free' : 'Book Now' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Classes Scheduled</h3>
                        <p class="text-base-content/60 mt-2">
                            There are no classes scheduled in the next 2 weeks.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
