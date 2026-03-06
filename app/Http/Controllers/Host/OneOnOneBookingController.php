<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\OneOnOneBookingCancelledMail;
use App\Models\BookingProfile;
use App\Models\Feature;
use App\Models\Instructor;
use App\Models\OneOnOneBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OneOnOneBookingController extends Controller
{
    /**
     * Display the list of 1:1 bookings or configuration if setup not complete.
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
            $showConfiguration = false;

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

            // Check if setup is complete - if not, show configuration
            $showConfiguration = !$profile->is_setup_complete;

            $query = OneOnOneBooking::where('booking_profile_id', $profile->id);
            $instructorsWithProfiles = collect();
        }

        // Filter by status
        $status = $request->get('status', 'pending');
        if ($status === 'pending') {
            $query->where('status', OneOnOneBooking::STATUS_PENDING)
                ->where('start_time', '>=', now());
        } elseif ($status === 'upcoming') {
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
            $query->whereIn('status', [
                OneOnOneBooking::STATUS_CANCELLED,
                OneOnOneBooking::STATUS_DECLINED,
            ]);
        }

        $bookings = $query->orderBy('start_time', $status === 'upcoming' ? 'asc' : 'desc')
            ->paginate(20);

        // Build view data
        $viewData = [
            'bookings' => $bookings,
            'profile' => $profile,
            'instructor' => $instructor,
            'currentStatus' => $status,
            'isOwner' => $isOwner,
            'instructorsWithProfiles' => $instructorsWithProfiles ?? collect(),
            'selectedInstructorId' => $request->instructor_id,
            'showConfiguration' => $showConfiguration,
            'host' => $host,
        ];

        // If showing configuration, add setup data
        if ($showConfiguration && $profile) {
            $viewData['meetingTypes'] = BookingProfile::getMeetingTypes();
            $viewData['durationOptions'] = BookingProfile::getDurationOptions();
            $viewData['dayOptions'] = BookingProfile::getDayOptions();
            $viewData['studioSettings'] = $this->getStudioSettings($host);
        }

        return view('host.one-on-one.index', $viewData);
    }

    /**
     * Get studio-level settings for 1:1 bookings from feature config.
     */
    protected function getStudioSettings($host): array
    {
        $defaults = [
            'buffer_before' => 10,
            'buffer_after' => 10,
            'min_notice_hours' => 24,
            'max_advance_days' => 60,
            'allow_reschedule' => true,
            'reschedule_cutoff_hours' => 24,
            'allow_cancel' => true,
            'cancel_cutoff_hours' => 24,
        ];

        try {
            $hostFeature = $host->features()
                ->where('slug', 'online-1on1-meeting')
                ->first();

            if ($hostFeature && $hostFeature->pivot->config) {
                $config = $hostFeature->pivot->config;
                if (is_string($config)) {
                    $config = json_decode($config, true) ?? [];
                }
                if (is_array($config)) {
                    return array_merge($defaults, $config);
                }
            }

            // Fall back to feature default config
            $feature = Feature::where('slug', 'online-1on1-meeting')->first();
            if ($feature && $feature->default_config) {
                $defaultConfig = $feature->default_config;
                if (is_string($defaultConfig)) {
                    $defaultConfig = json_decode($defaultConfig, true) ?? [];
                }
                if (is_array($defaultConfig)) {
                    return array_merge($defaults, $defaultConfig);
                }
            }
        } catch (\Exception $e) {
            // Log but continue with defaults
        }

        return $defaults;
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

    /**
     * Accept a pending booking.
     */
    public function accept(OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can accept any booking, others can only accept their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        if ($booking->status !== OneOnOneBooking::STATUS_PENDING) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be accepted.',
                ], 400);
            }
            return redirect()->back()->with('error', 'This booking cannot be accepted.');
        }

        $booking->accept();

        // Send confirmation email to guest
        try {
            Mail::to($booking->guest_email)->send(
                new \App\Mail\OneOnOneBookingConfirmationMail($booking)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send 1:1 booking confirmation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking has been accepted and confirmed.',
            ]);
        }

        return redirect()->route('one-on-one.index')
            ->with('success', 'Booking has been accepted. A confirmation email has been sent to the guest.');
    }

    /**
     * Decline a pending booking.
     */
    public function decline(Request $request, OneOnOneBooking $booking)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify ownership
        if ($booking->host_id !== $host->id) {
            abort(404);
        }

        // Owners can decline any booking, others can only decline their own
        if (!$isOwner) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$instructor || $booking->bookingProfile->instructor_id !== $instructor->id) {
                abort(403, 'You do not have access to this booking.');
            }
        }

        if ($booking->status !== OneOnOneBooking::STATUS_PENDING) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be declined.',
                ], 400);
            }
            return redirect()->back()->with('error', 'This booking cannot be declined.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->decline($validated['reason'] ?? null);

        // Send declined email to guest
        try {
            Mail::to($booking->guest_email)->send(
                new \App\Mail\OneOnOneBookingDeclinedMail($booking)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send 1:1 booking declined email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking has been declined.',
            ]);
        }

        return redirect()->route('one-on-one.index')
            ->with('success', 'Booking has been declined.');
    }
}
