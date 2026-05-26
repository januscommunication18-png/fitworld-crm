@extends('layouts.subdomain')

@section('title', 'My Schedule — ' . $host->studio_name)

@section('content')
<x-portal-shell :host="$host" :member="$member">
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
                        <button type="button"
                                onclick="openMyScheduleDrawer('booking-{{ $booking->id }}')"
                                class="card bg-base-100 w-full text-left hover:shadow-md transition-shadow cursor-pointer">
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
                                        @if($booking->checked_in_at)
                                            <div class="mt-2 inline-flex items-center gap-1 text-xs text-success font-medium">
                                                <span class="icon-[tabler--circle-check-filled] size-4"></span>
                                                Checked in
                                            </div>
                                        @elseif($booking->canSelfCheckIn())
                                            <form action="{{ route('member.portal.self-checkin', ['subdomain' => $host->subdomain, 'booking' => $booking->id]) }}"
                                                  method="POST"
                                                  class="mt-2"
                                                  onclick="event.stopPropagation();">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs"
                                                        onclick="event.stopPropagation();">
                                                    <span class="icon-[tabler--check] size-3"></span>
                                                    Check In
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>

                {{-- Booking detail drawers --}}
                @foreach($bookings as $booking)
                    @include('subdomain.member.portal.partials.booking-drawer', ['booking' => $booking])
                @endforeach
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

{{-- Drawer backdrop --}}
<div id="my-schedule-backdrop"
     class="fixed inset-0 bg-black/50 z-40 hidden"
     onclick="closeAllMyScheduleDrawers()"></div>

{{-- Cancel Booking Confirmation Modal (shared, one per page) --}}
<div id="my-schedule-cancel-modal" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60" onclick="closeMyScheduleCancelModal()"></div>
    <div class="relative max-w-md w-full bg-base-100 rounded-2xl shadow-xl">
        <div class="p-5 border-b border-base-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-error/10 text-error flex items-center justify-center shrink-0">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
            </div>
            <div>
                <h3 class="font-semibold text-lg leading-tight">Cancel this booking?</h3>
                <p class="text-sm text-base-content/60 mt-0.5" id="my-schedule-cancel-target">—</p>
            </div>
        </div>
        <form id="my-schedule-cancel-form" action="" method="POST" class="p-5">
            @csrf
            <p class="text-sm text-base-content/70 mb-3">
                This will release your spot. Depending on the studio's cancellation policy, you may not be able to rebook this session.
            </p>
            <label class="label-text text-sm" for="my-schedule-cancel-reason">Reason (optional)</label>
            <textarea id="my-schedule-cancel-reason" name="reason" rows="2"
                      class="textarea textarea-bordered w-full mt-1"
                      placeholder="e.g. Schedule conflict"></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn btn-ghost" onclick="closeMyScheduleCancelModal()">Keep Booking</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--x] size-4"></span>
                    Cancel Booking
                </button>
            </div>
        </form>
    </div>
</div>
</x-portal-shell>

<script>
function openMyScheduleDrawer(id) {
    var drawer = document.getElementById('drawer-' + id);
    if (!drawer) return;
    // Close any other open drawers first
    document.querySelectorAll('[id^="drawer-booking-"]').forEach(function (d) {
        if (d !== drawer) {
            d.classList.add('translate-x-full', 'hidden');
        }
    });
    var backdrop = document.getElementById('my-schedule-backdrop');
    if (backdrop) backdrop.classList.remove('hidden');
    drawer.classList.remove('hidden');
    setTimeout(function () { drawer.classList.remove('translate-x-full'); }, 10);
    document.body.style.overflow = 'hidden';
}
function closeMyScheduleDrawer(id) {
    var drawer = document.getElementById('drawer-' + id);
    if (!drawer) return;
    drawer.classList.add('translate-x-full');
    setTimeout(function () { drawer.classList.add('hidden'); }, 300);
    var backdrop = document.getElementById('my-schedule-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = '';
}
function closeAllMyScheduleDrawers() {
    document.querySelectorAll('[id^="drawer-booking-"]').forEach(function (d) {
        d.classList.add('translate-x-full');
        setTimeout(function () { d.classList.add('hidden'); }, 300);
    });
    var backdrop = document.getElementById('my-schedule-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeMyScheduleCancelModal();
        closeAllMyScheduleDrawers();
    }
});

function openMyScheduleCancelModal(bookingId, titleText, subdomain) {
    var modal = document.getElementById('my-schedule-cancel-modal');
    var form  = document.getElementById('my-schedule-cancel-form');
    var label = document.getElementById('my-schedule-cancel-target');
    var reason = document.getElementById('my-schedule-cancel-reason');
    if (!modal || !form) return;

    form.action = '/portal/my-schedule/' + encodeURIComponent(bookingId) + '/cancel';
    if (label) label.textContent = titleText || 'Booking';
    if (reason) reason.value = '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeMyScheduleCancelModal() {
    var modal = document.getElementById('my-schedule-cancel-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    // Restore body scroll only if no drawer is also open
    var anyDrawerOpen = document.querySelector('[id^="drawer-booking-"]:not(.hidden)');
    if (!anyDrawerOpen) {
        document.body.style.overflow = '';
    }
}
</script>
@endsection
