<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\OneOnOneBookingCancelledMail;
use App\Models\BookingProfile;
use App\Models\Feature;
use App\Models\Instructor;
use App\Models\OneOnOneBooking;
use App\Models\OneOnOneInvite;
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
            $showConfiguration = false;

            // Check if owner has their own instructor record and booking profile
            $instructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();
            $profile = $instructor
                ? BookingProfile::where('host_id', $host->id)
                    ->where('instructor_id', $instructor->id)
                    ->first()
                : null;

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
        $bookings = collect();
        $invites = collect();
        $myInvites = collect();

        // Handle "My Invites" tab - available for all members with booking access
        if ($status === 'my-invites') {
            // For owners/admins: show all invites
            // For regular instructors: show only their own sent invites
            $inviteQuery = OneOnOneInvite::where('host_id', $host->id)
                ->with(['instructor', 'sentBy']);

            if (!$isOwner && $instructor) {
                // Regular instructor sees only their own sent invites (as sender or as the instructor)
                $inviteQuery->where(function ($q) use ($user, $instructor) {
                    $q->where('sent_by_user_id', $user->id)
                        ->orWhere('instructor_id', $instructor->id);
                });
            }

            $myInvites = $inviteQuery->orderBy('sent_at', 'desc')->paginate(20);
        } elseif ($status === 'invites' && $isOwner) {
            // Legacy invites tab for owners (all invites)
            $invites = OneOnOneInvite::where('host_id', $host->id)
                ->with(['instructor', 'sentBy'])
                ->orderBy('sent_at', 'desc')
                ->paginate(20);
        } else {
            // Regular booking filters
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
        }

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
            'invites' => $invites,
            'myInvites' => $myInvites,
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

    /**
     * Send a booking invite to a client.
     */
    public function sendInvite(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Get the user's instructor record (if they have one)
        $userInstructor = Instructor::where('host_id', $host->id)
            ->where('user_id', $user->id)
            ->first();

        // Allow owners to send invites, or instructors to send for themselves
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'email' => 'required|email',
            'client_name' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|in:15,30,45,60',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);

        // Get the instructor and verify they belong to this host
        $instructor = Instructor::where('host_id', $host->id)
            ->where('id', $validated['instructor_id'])
            ->first();

        if (!$instructor) {
            return response()->json([
                'success' => false,
                'message' => 'Instructor not found.',
            ], 404);
        }

        // Check permissions: owner can send for any instructor, instructor can only send for themselves
        $canSend = $isOwner || ($userInstructor && $userInstructor->id === $instructor->id);
        if (!$canSend) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to send invites.',
            ], 403);
        }

        // Check if instructor has a booking profile
        $profile = $instructor->bookingProfile;
        if (!$profile || !$profile->is_enabled || !$profile->is_setup_complete) {
            return response()->json([
                'success' => false,
                'message' => 'This instructor does not have 1:1 booking enabled.',
            ], 400);
        }

        // Validate duration is in allowed durations
        $duration = $validated['duration'] ?? $profile->default_duration ?? 30;
        if (!in_array($duration, $profile->allowed_durations ?? [30, 60])) {
            $duration = $profile->default_duration ?? $profile->allowed_durations[0] ?? 30;
        }

        // Build scheduled datetime if provided
        $scheduledAt = null;
        if (!empty($validated['scheduled_date']) && !empty($validated['scheduled_time'])) {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        }

        // Send the invite email
        try {
            Mail::to($validated['email'])->send(
                new \App\Mail\OneOnOneBookingInviteMail(
                    $instructor,
                    $host,
                    $validated['client_name'] ?? null,
                    $validated['email'],
                    $duration,
                    $scheduledAt
                )
            );

            // Store the invite
            OneOnOneInvite::create([
                'host_id' => $host->id,
                'instructor_id' => $instructor->id,
                'sent_by_user_id' => $user->id,
                'email' => $validated['email'],
                'client_name' => $validated['client_name'] ?? null,
                'duration' => $duration,
                'scheduled_at' => $scheduledAt,
                'sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invite sent successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send 1:1 booking invite email', [
                'instructor_id' => $instructor->id,
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send invite email.',
            ], 500);
        }
    }

    /**
     * Resend an existing invite.
     */
    public function resendInvite(OneOnOneInvite $invite)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $isOwner = $user->isOwner($host);

        // Verify invite belongs to this host
        if ($invite->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invite not found.',
            ], 404);
        }

        // Get user's instructor record
        $userInstructor = Instructor::where('host_id', $host->id)
            ->where('user_id', $user->id)
            ->first();

        // Permission check: owners can resend any, instructors can only resend their own
        $canResend = $isOwner ||
            ($invite->sent_by_user_id === $user->id) ||
            ($userInstructor && $invite->instructor_id === $userInstructor->id);

        if (!$canResend) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to resend this invite.',
            ], 403);
        }

        try {
            Mail::to($invite->email)->send(
                new \App\Mail\OneOnOneBookingInviteMail(
                    $invite->instructor,
                    $host,
                    $invite->client_name,
                    $invite->email,
                    $invite->duration,
                    $invite->scheduled_at
                )
            );

            // Update sent_at
            $invite->update(['sent_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Invite resent successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to resend 1:1 booking invite email', [
                'invite_id' => $invite->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invite.',
            ], 500);
        }
    }
}
