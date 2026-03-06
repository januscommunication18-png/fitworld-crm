<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\OneOnOneBookingCancelledMail;
use App\Models\BookingProfile;
use App\Models\Instructor;
use App\Models\OneOnOneBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OneOnOneBookingController extends Controller
{
    /**
     * Display the list of 1:1 bookings.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // For owners, show all bookings; for instructors, show only their own
        if ($isOwner) {
            // Owner sees all bookings for the host
            $query = OneOnOneBooking::where('host_id', $host->id)
                ->with(['bookingProfile.instructor']);
            $instructor = null;
            $profile = null;

            // Allow filtering by instructor
            if ($request->has('instructor_id') && $request->instructor_id) {
                $query->whereHas('bookingProfile', function ($q) use ($request) {
                    $q->where('instructor_id', $request->instructor_id);
                });
            }

            // Get all instructors with booking profiles for the filter dropdown
            $instructorsWithProfiles = Instructor::where('host_id', $host->id)
                ->whereHas('bookingProfile', function ($q) {
                    $q->where('is_enabled', true);
                })
                ->get();
        } else {
            // Non-owner: show only their own bookings
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor) {
                return redirect()->route('dashboard')
                    ->with('error', 'You are not associated with an instructor profile.');
            }

            $profile = BookingProfile::where('host_id', $host->id)
                ->where('instructor_id', $instructor->id)
                ->first();

            if (!$profile) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have 1:1 booking access.');
            }

            $query = OneOnOneBooking::where('booking_profile_id', $profile->id);
            $instructorsWithProfiles = collect();
        }

        // Filter by status
        $status = $request->get('status', 'upcoming');
        if ($status === 'upcoming') {
            $query->whereIn('status', [
                OneOnOneBooking::STATUS_CONFIRMED,
            ])->where('start_time', '>=', now());
        } elseif ($status === 'past') {
            $query->where(function ($q) {
                $q->where('start_time', '<', now())
                    ->orWhereIn('status', [
                        OneOnOneBooking::STATUS_COMPLETED,
                        OneOnOneBooking::STATUS_NO_SHOW,
                    ]);
            });
        } elseif ($status === 'cancelled') {
            $query->where('status', OneOnOneBooking::STATUS_CANCELLED);
        }

        $bookings = $query->orderBy('start_time', $status === 'upcoming' ? 'asc' : 'desc')
            ->paginate(20);

        return view('host.one-on-one.index', [
            'bookings' => $bookings,
            'profile' => $profile,
            'instructor' => $instructor,
            'currentStatus' => $status,
            'isOwner' => $isOwner,
            'instructorsWithProfiles' => $instructorsWithProfiles ?? collect(),
            'selectedInstructorId' => $request->instructor_id,
        ]);
    }

    /**
     * Display a specific booking.
     */
    public function show(OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can view any booking, others can only view their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        return view('host.one-on-one.show', [
            'booking' => $booking,
            'profile' => $booking->bookingProfile,
            'instructor' => $booking->bookingProfile->instructor,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can cancel any booking, others can only cancel their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        if ($booking->status !== OneOnOneBooking::STATUS_CONFIRMED) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->cancel('host', $validated['reason'] ?? null);

        // Send cancellation email to guest
        try {
            Mail::to($booking->guest_email)->send(
                new OneOnOneBookingCancelledMail($booking, 'the host')
            );
        } catch (\Exception $e) {
            // Log but don't fail
            \Log::error('Failed to send 1:1 booking cancellation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking has been cancelled.',
            ]);
        }

        return redirect()->route('one-on-one.index')
            ->with('success', 'Booking has been cancelled.');
    }

    /**
     * Mark a booking as completed.
     */
    public function complete(OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can complete any booking, others can only complete their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        if ($booking->status !== OneOnOneBooking::STATUS_CONFIRMED) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be marked as completed.',
            ], 400);
        }

        $booking->markComplete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking has been marked as completed.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Booking has been marked as completed.');
    }

    /**
     * Mark a booking as no-show.
     */
    public function noShow(OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can mark any booking as no-show, others can only mark their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        if ($booking->status !== OneOnOneBooking::STATUS_CONFIRMED) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be marked as no-show.',
            ], 400);
        }

        $booking->markNoShow();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking has been marked as no-show.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Booking has been marked as no-show.');
    }
}
