@extends('layouts.subdomain')

@section('title', 'My Schedule — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">My Schedule</h1>
                <a href="{{ route('booking.select-type', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Book Now
                </a>
            </div>

            {{-- Filter Tabs --}}
            <div class="tabs tabs-boxed bg-base-100 w-fit mb-6">
                <a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain, 'filter' => 'upcoming']) }}"
                   class="tab {{ $filter === 'upcoming' ? 'tab-active' : '' }}">
                    Upcoming
                </a>
                <a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain, 'filter' => 'past']) }}"
                   class="tab {{ $filter === 'past' ? 'tab-active' : '' }}">
                    Past
                </a>
                <a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain, 'filter' => 'all']) }}"
                   class="tab {{ $filter === 'all' ? 'tab-active' : '' }}">
                    All
                </a>
            </div>

            @if($bookings->count() > 0)
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        @php $bookable = $booking->bookable; @endphp
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <div class="flex items-start gap-4">
                                    @if($bookable && $bookable->start_time)
                                    <div class="text-center min-w-[60px] p-3 bg-primary/10 rounded-lg">
                                        <p class="text-2xl font-bold text-primary">{{ $bookable->start_time->format('j') }}</p>
                                        <p class="text-xs text-primary uppercase">{{ $bookable->start_time->format('M') }}</p>
                                    </div>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg">
                                            {{ $bookable?->display_title ?? $bookable?->classPlan?->name ?? $bookable?->servicePlan?->name ?? 'Booking' }}
                                        </h3>
                                        @if($bookable && $bookable->start_time)
                                        <p class="text-base-content/60 mt-1">
                                            <span class="icon-[tabler--clock] size-4 inline-block align-text-bottom mr-1"></span>
                                            {{ $bookable->start_time->format('l, g:i A') }}
                                            @if($bookable->end_time)
                                                - {{ $bookable->end_time->format('g:i A') }}
                                            @endif
                                        </p>
                                        @endif
                                        @if($bookable?->primaryInstructor || $bookable?->instructor)
                                        <p class="text-base-content/60">
                                            <span class="icon-[tabler--user] size-4 inline-block align-text-bottom mr-1"></span>
                                            {{ $bookable->primaryInstructor?->name ?? $bookable->instructor?->name }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="badge {{
                                            $booking->status === 'confirmed' ? 'badge-success' :
                                            ($booking->status === 'waitlisted' ? 'badge-warning' :
                                            ($booking->status === 'cancelled' ? 'badge-error' : 'badge-neutral'))
                                        }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                        <p class="text-xs text-base-content/50 mt-2">
                                            Booked {{ $booking->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Classes Scheduled</h3>
                        <p class="text-base-content/60 mt-2">
                            @if($filter === 'upcoming')
                                You don't have any upcoming classes.
                            @elseif($filter === 'past')
                                You don't have any past classes.
                            @else
                                You haven't booked any classes yet.
                            @endif
                        </p>
                        <a href="{{ route('booking.select-type', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary mt-4">
                            Book Your First Class
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
