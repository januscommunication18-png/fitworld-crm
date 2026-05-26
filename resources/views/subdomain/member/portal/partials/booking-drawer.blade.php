{{-- Member portal booking-detail drawer --}}
{{-- Required vars: $booking, $host --}}

@php
    $bookable = $booking->bookable;
    $classPlan = $bookable?->classPlan ?? null;
    $titleText = $bookable?->display_title ?? $classPlan?->name ?? $bookable?->servicePlan?->name ?? 'Booking';
    $instructorName = $bookable?->primaryInstructor?->name ?? $bookable?->instructor?->name ?? null;
    $locationName = $bookable?->location?->name ?? $bookable?->room?->location?->name ?? null;
    $planColor = $classPlan?->color ?? '#6366f1';
    $planInstructors = $classPlan?->instructors ?? collect();
@endphp

<div id="drawer-booking-{{ $booking->id }}"
     class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden flex flex-col"
     role="dialog" tabindex="-1">
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Booking Details</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm"
                aria-label="Close"
                onclick="closeMyScheduleDrawer('booking-{{ $booking->id }}')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-5">
        {{-- Hero: image + title + badges + when/where --}}
        <div class="flex items-start gap-4">
            @if($classPlan?->image_url)
                <img src="{{ $classPlan->image_url }}" alt="{{ $titleText }}"
                     class="w-20 h-20 rounded-2xl object-cover border border-base-300 shrink-0">
            @else
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center shrink-0"
                     style="background-color: {{ $planColor }}15;">
                    <span class="icon-[tabler--yoga] size-10" style="color: {{ $planColor }};"></span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold leading-tight">{{ $titleText }}</h2>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="badge {{
                        $booking->status === 'confirmed' ? 'badge-success' :
                        ($booking->status === 'waitlisted' ? 'badge-warning' :
                        ($booking->status === 'cancelled' ? 'badge-error' : 'badge-neutral'))
                    }} badge-soft">{{ ucfirst($booking->status) }}</span>
                    @if($classPlan?->category)
                        <span class="badge badge-ghost">{{ $classPlan->category }}</span>
                    @endif
                    @if($classPlan?->difficulty_level)
                        <span class="badge {{ $classPlan->getDifficultyBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $classPlan->difficulty_level)) }}
                        </span>
                    @endif
                </div>
                @if($bookable && $bookable->start_time)
                    <p class="text-sm text-base-content/70 mt-2">
                        <span class="icon-[tabler--calendar] size-4 inline-block align-text-bottom mr-1"></span>
                        {{ $bookable->start_time->format('l, F j, Y') }}
                    </p>
                    <p class="text-sm text-base-content/70">
                        <span class="icon-[tabler--clock] size-4 inline-block align-text-bottom mr-1"></span>
                        {{ $bookable->start_time->format('g:i A') }}@if($bookable->end_time) – {{ $bookable->end_time->format('g:i A') }}@endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Quick facts from the class plan --}}
        @if($classPlan && ($classPlan->default_duration_minutes || $classPlan->default_capacity || $classPlan->difficulty_level || $classPlan->category))
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @if($classPlan->default_duration_minutes)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--clock] size-4"></span> Duration
                        </div>
                        <div class="font-semibold mt-0.5">{{ $classPlan->formatted_duration }}</div>
                    </div>
                @endif
                @if($classPlan->default_capacity)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--users] size-4"></span> Capacity
                        </div>
                        <div class="font-semibold mt-0.5">
                            @if($classPlan->min_capacity)
                                {{ $classPlan->min_capacity }}–{{ $classPlan->default_capacity }}
                            @else
                                Max {{ $classPlan->default_capacity }}
                            @endif
                        </div>
                    </div>
                @endif
                @if($classPlan->difficulty_level)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--barbell] size-4"></span> Level
                        </div>
                        <div class="font-semibold mt-0.5">{{ ucfirst(str_replace('_', ' ', $classPlan->difficulty_level)) }}</div>
                    </div>
                @endif
                @if($classPlan->category)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--category] size-4"></span> Category
                        </div>
                        <div class="font-semibold mt-0.5 truncate">{{ $classPlan->category }}</div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Description --}}
        @if($classPlan?->description)
            <section>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-base-content/60 mb-2">About this class</h3>
                <div class="prose prose-sm max-w-none text-base-content/80 whitespace-pre-line">{{ $classPlan->description }}</div>
            </section>
        @endif

        {{-- Instructors --}}
        @if($planInstructors->isNotEmpty())
            <section>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-base-content/60 mb-2">Taught by</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($planInstructors as $inst)
                        @php
                            $initials = collect(explode(' ', $inst->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-base-100 border border-base-200">
                            @if($inst->photo_url ?? null)
                                <img src="{{ $inst->photo_url }}" alt="{{ $inst->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="avatar placeholder">
                                    <div class="bg-primary/10 text-primary w-10 h-10 rounded-full">
                                        <span class="text-sm font-semibold">{{ $initials }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-medium truncate">{{ $inst->name }}</div>
                                @if($inst->title ?? null)
                                    <div class="text-xs text-base-content/60 truncate">{{ $inst->title }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Booking-specific table --}}
        <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-base-content/60 mb-2">Your booking</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-base-content/60 w-36">Booking ID</td>
                            <td class="font-medium">#{{ $booking->id }}</td>
                        </tr>
                        @if($instructorName)
                            <tr>
                                <td class="text-base-content/60">This session</td>
                                <td>{{ $instructorName }}</td>
                            </tr>
                        @endif
                        @if($locationName)
                            <tr>
                                <td class="text-base-content/60">Location</td>
                                <td>{{ $locationName }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-base-content/60">Booked on</td>
                            <td>{{ $booking->created_at->format('M j, Y') }}</td>
                        </tr>
                        @if($booking->isCheckedIn())
                            <tr>
                                <td class="text-base-content/60">Checked in</td>
                                <td class="text-success">
                                    <span class="icon-[tabler--check] size-4 inline-block align-text-bottom"></span>
                                    {{ $booking->checked_in_at?->format('M j, g:i A') }}
                                </td>
                            </tr>
                        @endif
                        @if($booking->status === 'cancelled')
                            <tr>
                                <td class="text-base-content/60">Cancelled on</td>
                                <td>{{ $booking->cancelled_at?->format('M j, Y g:i A') }}</td>
                            </tr>
                            @if($booking->cancellation_reason)
                                <tr>
                                    <td class="text-base-content/60">Reason</td>
                                    <td>{{ $booking->cancellation_reason }}</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Footer / Actions --}}
    @php
        $checkinState = $booking->selfCheckInState();
        $hasCheckin = $checkinState['allowed'];
        $hasCancel = $booking->canBeCancelled();
        $hasCheckinBlock = $hasCheckin
            || $booking->checked_in_at
            || in_array($checkinState['reason'] ?? null, ['too_early', 'too_late']);
    @endphp
    @if($hasCheckinBlock || $hasCancel)
        <div class="p-4 border-t border-base-200 bg-base-100 grid {{ ($hasCheckinBlock && $hasCancel) ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
            @if($booking->checked_in_at)
                <div class="alert alert-success py-2 px-3 text-sm">
                    <span class="icon-[tabler--circle-check-filled] size-5"></span>
                    Checked in {{ $booking->checked_in_at->diffForHumans() }}
                </div>
            @elseif($hasCheckin)
                <form action="{{ route('member.portal.self-checkin', ['subdomain' => $host->subdomain, 'booking' => $booking->id]) }}"
                      method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block">
                        <span class="icon-[tabler--check] size-4"></span>
                        Check In
                    </button>
                </form>
            @elseif(($checkinState['reason'] ?? null) === 'too_early')
                <div class="alert alert-info py-2 px-3 text-sm">
                    <span class="icon-[tabler--clock] size-5"></span>
                    Check-in opens {{ $checkinState['opens_at']->diffForHumans() }}
                </div>
            @elseif(($checkinState['reason'] ?? null) === 'too_late')
                <div class="alert alert-warning py-2 px-3 text-sm">
                    <span class="icon-[tabler--clock-x] size-5"></span>
                    Check-in window closed
                </div>
            @endif

            @if($hasCancel)
                <button type="button"
                        class="btn btn-error btn-block"
                        onclick="openMyScheduleCancelModal({{ $booking->id }}, '{{ addslashes($titleText) }}', '{{ $host->subdomain }}')">
                    <span class="icon-[tabler--x] size-4"></span>
                    Cancel Booking
                </button>
            @endif
        </div>
    @endif
</div>
