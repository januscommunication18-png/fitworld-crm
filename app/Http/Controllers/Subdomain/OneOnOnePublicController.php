<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Mail\OneOnOneBookingConfirmationMail;
use App\Mail\OneOnOneBookingRescheduledMail;
use App\Mail\OneOnOneBookingCancelledMail;
use App\Mail\OneOnOneNewBookingMail;
use App\Models\BookingProfile;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\OneOnOneBooking;
use App\Services\OneOnOneAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OneOnOnePublicController extends Controller
{
    protected OneOnOneAvailabilityService $availabilityService;

    public function __construct(OneOnOneAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get the host from the request attributes.
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Show the booking page/modal for an instructor.
     */
    public function showBookingPage(Request $request, string $subdomain, Instructor $instructor)
    {
        $host = $this->getHost($request);

        // Verify instructor belongs to this host
        if ($instructor->host_id !== $host->id) {
            abort(404);
        }

        $profile = BookingProfile::where('host_id', $host->id)
            ->where('instructor_id', $instructor->id)
            ->where('is_enabled', true)
            ->where('is_setup_complete', true)
            ->first();

        if (!$profile) {
            abort(404, 'This instructor is not currently accepting 1:1 bookings.');
        }

        // Calculate min and max booking dates
        $minDate = now()->addHours($profile->min_notice_hours ?? 24)->startOfDay();
        $maxDate = now()->addDays($profile->max_advance_days ?? 60);

        return view('subdomain.one-on-one.book', [
            'host' => $host,
            'instructor' => $instructor,
            'profile' => $profile,
            'allowedDurations' => $profile->allowed_durations ?? [30],
            'meetingTypes' => $profile->meeting_types ?? ['in_person'],
            'meetingTypeLabels' => BookingProfile::getMeetingTypes(),
            'durationOptions' => BookingProfile::getDurationOptions(),
            'minDate' => $minDate->format('Y-m-d'),
            'maxDate' => $maxDate->format('Y-m-d'),
        ]);
    }

    /**
     * Get available time slots for a specific date and duration.
     */
    public function getAvailability(Request $request, string $subdomain, Instructor $instructor)
    {
        $host = $this->getHost($request);

        // Verify instructor belongs to this host
        if ($instructor->host_id !== $host->id) {
            abort(404);
        }

        $profile = $instructor->bookingProfile;

        if (!$profile || !$profile->canAcceptBookings()) {
            return response()->json([
                'success' => false,
                'message' => 'This instructor is not accepting bookings.',
            ], 400);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|in:15,30,45,60',
        ]);

        $date = Carbon::parse($validated['date']);
        $duration = (int) $validated['duration'];

        // Check if duration is allowed
        if (!in_array($duration, $profile->allowed_durations ?? [30])) {
            return response()->json([
                'success' => false,
                'message' => 'This duration is not available.',
            ], 400);
        }

        // Check if date is within booking window
        $minNoticeDate = now()->addHours($profile->min_notice_hours ?? 24);
        $maxAdvanceDate = now()->addDays($profile->max_advance_days ?? 60);

        if ($date->lt($minNoticeDate->startOfDay())) {
            return response()->json([
                'success' => false,
                'message' => 'This date requires more advance notice.',
                'slots' => [],
            ]);
        }

        if ($date->gt($maxAdvanceDate)) {
            return response()->json([
                'success' => false,
                'message' => 'This date is too far in advance.',
                'slots' => [],
            ]);
        }

        // Get available slots
        $slots = $this->availabilityService->getAvailableSlots($profile, $date, $duration);

        return response()->json([
            'success' => true,
            'date' => $date->format('Y-m-d'),
            'duration' => $duration,
            'slots' => $slots->map(fn($slot) => [
                'time' => $slot['start'],
                'display' => $slot['formatted'],
            ])->values(),
        ]);
    }

    /**
     * Store a new booking.
     */
    public function storeBooking(Request $request, string $subdomain, Instructor $instructor)
    {
        $host = $this->getHost($request);

        // Verify instructor belongs to this host
        if ($instructor->host_id !== $host->id) {
            abort(404);
        }

        $profile = $instructor->bookingProfile;

        if (!$profile || !$profile->canAcceptBookings()) {
            return response()->json([
                'success' => false,
                'message' => 'This instructor is not accepting bookings.',
            ], 400);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|integer|in:15,30,45,60',
            'meeting_type' => 'required|in:in_person,phone,video',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:100',
        ]);

        // Check if duration is allowed
        if (!in_array((int) $validated['duration'], $profile->allowed_durations ?? [30])) {
            return response()->json([
                'success' => false,
                'message' => 'This duration is not available.',
            ], 400);
        }

        // Check if meeting type is allowed
        if (!in_array($validated['meeting_type'], $profile->meeting_types ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'This meeting type is not available.',
            ], 400);
        }

        // Parse start time
        $startTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $endTime = $startTime->copy()->addMinutes((int) $validated['duration']);

        // Verify the slot is still available
        $availableSlots = $this->availabilityService->getAvailableSlots(
            $profile,
            $startTime->copy()->startOfDay(),
            (int) $validated['duration']
        );

        $slotAvailable = $availableSlots->contains(fn($slot) => $slot['start'] === $validated['time']);

        if (!$slotAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is no longer available. Please select another time.',
            ], 400);
        }

        // Create the booking as pending (requires acceptance)
        $booking = OneOnOneBooking::create([
            'host_id' => $host->id,
            'booking_profile_id' => $profile->id,
            'guest_first_name' => $validated['first_name'],
            'guest_last_name' => $validated['last_name'],
            'guest_email' => $validated['email'],
            'guest_phone' => $validated['phone'] ?? null,
            'guest_notes' => $validated['notes'] ?? null,
            'meeting_type' => $validated['meeting_type'],
            'duration_minutes' => (int) $validated['duration'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'timezone' => $validated['timezone'] ?? config('app.timezone'),
            'status' => OneOnOneBooking::STATUS_PENDING,
            'confirmation_token' => Str::random(32),
            'manage_token' => Str::random(32),
            'booked_at' => now(),
        ]);

        // Send notification emails
        try {
            // Email to guest - pending confirmation
            Mail::to($booking->guest_email)->send(
                new \App\Mail\OneOnOneBookingPendingMail($booking)
            );

            // Email to host/instructor - booking request to accept/decline
            $instructorUser = $instructor->user;
            if ($instructorUser) {
                Mail::to($instructorUser->email)->send(
                    new \App\Mail\OneOnOneBookingRequestMail($booking)
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send 1:1 booking notification emails', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your booking request has been submitted! You will receive an email once it is confirmed.',
            'confirmation_url' => route('subdomain.meeting.confirmation', [
                'subdomain' => $host->subdomain,
                'token' => $booking->confirmation_token,
            ]),
            'manage_url' => route('subdomain.meeting.manage', [
                'subdomain' => $host->subdomain,
                'token' => $booking->manage_token,
            ]),
        ]);
    }

    /**
     * Show booking confirmation page.
     */
    public function confirmation(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('confirmation_token', $token)
            ->with(['bookingProfile.instructor', 'bookingProfile.host'])
            ->firstOrFail();

        return view('subdomain.one-on-one.confirmation', [
            'host' => $host,
            'booking' => $booking,
            'profile' => $booking->bookingProfile,
            'instructor' => $booking->bookingProfile->instructor,
        ]);
    }

    /**
     * Show guest management page.
     */
    public function manage(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('manage_token', $token)
            ->with(['bookingProfile.instructor', 'bookingProfile.host'])
            ->firstOrFail();

        return view('subdomain.one-on-one.manage', [
            'host' => $host,
            'booking' => $booking,
            'profile' => $booking->bookingProfile,
            'instructor' => $booking->bookingProfile->instructor,
            'canReschedule' => $booking->canBeRescheduled(),
            'canCancel' => $booking->canBeCancelled(),
        ]);
    }

    /**
     * Get availability for rescheduling.
     */
    public function rescheduleAvailability(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('manage_token', $token)
            ->with('bookingProfile')
            ->firstOrFail();

        if (!$booking->canBeRescheduled()) {
            return response()->json([
                'success' => false,
                'message' => 'This booking can no longer be rescheduled.',
            ], 400);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($validated['date']);
        $profile = $booking->bookingProfile;

        $slots = $this->availabilityService->getAvailableSlots(
            $profile,
            $date,
            $booking->duration_minutes
        );

        return response()->json([
            'success' => true,
            'date' => $date->format('Y-m-d'),
            'duration' => $booking->duration_minutes,
            'slots' => $slots->map(fn($slot) => [
                'time' => $slot['start'],
                'display' => $slot['formatted'],
            ])->values(),
        ]);
    }

    /**
     * Reschedule a booking.
     */
    public function reschedule(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('manage_token', $token)
            ->with('bookingProfile')
            ->firstOrFail();

        if (!$booking->canBeRescheduled()) {
            return response()->json([
                'success' => false,
                'message' => 'This booking can no longer be rescheduled.',
            ], 400);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        $profile = $booking->bookingProfile;
        $newStartTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $newEndTime = $newStartTime->copy()->addMinutes($booking->duration_minutes);

        // Verify the slot is available
        $availableSlots = $this->availabilityService->getAvailableSlots(
            $profile,
            $newStartTime->copy()->startOfDay(),
            $booking->duration_minutes
        );

        $slotAvailable = $availableSlots->contains(fn($slot) => $slot['start'] === $validated['time']);

        if (!$slotAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is no longer available.',
            ], 400);
        }

        // Store old times for email
        $oldStartTime = $booking->start_time;
        $oldEndTime = $booking->end_time;

        // Create new booking record for history
        $newBooking = $booking->replicate();
        $newBooking->start_time = $newStartTime;
        $newBooking->end_time = $newEndTime;
        $newBooking->confirmation_token = Str::random(32);
        $newBooking->manage_token = Str::random(32);
        $newBooking->rescheduled_from_id = $booking->id;
        $newBooking->reschedule_count = $booking->reschedule_count + 1;
        $newBooking->save();

        // Cancel the old booking
        $booking->cancel('guest', 'Rescheduled to ' . $newStartTime->format('M j, Y g:i A'));

        // Send reschedule email
        try {
            Mail::to($newBooking->guest_email)->send(
                new OneOnOneBookingRescheduledMail($newBooking, $oldStartTime, $oldEndTime)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule email', [
                'booking_id' => $newBooking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your meeting has been rescheduled.',
            'manage_url' => route('subdomain.meeting.manage', [
                'subdomain' => $host->subdomain,
                'token' => $newBooking->manage_token,
            ]),
        ]);
    }

    /**
     * Cancel a booking by guest.
     */
    public function cancelByGuest(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('manage_token', $token)
            ->firstOrFail();

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This booking can no longer be cancelled.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->cancel('guest', $validated['reason'] ?? null);

        // Send cancellation email
        try {
            Mail::to($booking->guest_email)->send(
                new OneOnOneBookingCancelledMail($booking, 'you')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send cancellation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your meeting has been cancelled.',
            ]);
        }

        return redirect()->route('subdomain.meeting.cancelled', [
            'subdomain' => $host->subdomain,
            'token' => $token,
        ]);
    }

    /**
     * Show cancellation confirmed page.
     */
    public function cancelled(Request $request, string $subdomain, $token)
    {
        $host = $this->getHost($request);

        $booking = OneOnOneBooking::where('host_id', $host->id)
            ->where('manage_token', $token)
            ->with('bookingProfile.instructor')
            ->firstOrFail();

        return view('subdomain.one-on-one.cancelled', [
            'host' => $host,
            'booking' => $booking,
            'instructor' => $booking->bookingProfile->instructor,
        ]);
    }
}
