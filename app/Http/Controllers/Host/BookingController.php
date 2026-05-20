<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\QuestionnaireResponse;
use App\Mail\BookingConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Require bookings.view OR bookings.view_own. Returns true if the user is
     * restricted to own-only (only view_own granted). 403s if neither.
     */
    private function authorizeBookingsRead(): bool
    {
        $user = auth()->user();
        $hasFull = $user->hasPermission('bookings.view');
        $hasOwn = $user->hasPermission('bookings.view_own');

        if (!$hasFull && !$hasOwn) {
            abort(403, 'You do not have permission to view bookings.');
        }

        return !$hasFull && $hasOwn;
    }

    /**
     * Restrict a Booking query to bookings on class sessions assigned to the
     * current user as primary or backup instructor.
     */
    private function scopeToOwnBookings($query, int $hostId): void
    {
        $myInstructorIds = \App\Models\Instructor::where('host_id', $hostId)
            ->where('user_id', auth()->id())
            ->pluck('id')
            ->all();

        if (empty($myInstructorIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($myInstructorIds) {
            $q->whereIn('primary_instructor_id', $myInstructorIds)
              ->orWhereIn('backup_instructor_id', $myInstructorIds)
              ->orWhereHas('backupInstructors', function ($q2) use ($myInstructorIds) {
                  $q2->whereIn('instructors.id', $myInstructorIds);
              });
        });
    }

    /**
     * For the show/resendIntake actions: is the given booking on a session the
     * current user instructs? Returns false for non-class-session bookings.
     */
    private function bookingAssignedToUser(Booking $booking): bool
    {
        $bookable = $booking->bookable;
        if (!$bookable instanceof ClassSession) {
            return false;
        }

        $myInstructorIds = \App\Models\Instructor::where('host_id', $booking->host_id)
            ->where('user_id', auth()->id())
            ->pluck('id')
            ->all();

        if (empty($myInstructorIds)) {
            return false;
        }

        if (in_array($bookable->primary_instructor_id, $myInstructorIds, true)) {
            return true;
        }

        if (in_array($bookable->backup_instructor_id, $myInstructorIds, true)) {
            return true;
        }

        return $bookable->backupInstructors()
            ->whereIn('instructors.id', $myInstructorIds)
            ->exists();
    }

    /**
     * Display all bookings
     */
    public function index(Request $request)
    {
        $viewOwnOnly = $this->authorizeBookingsRead();
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->orderBy('booked_at', 'desc');

        if ($viewOwnOnly) {
            $this->scopeToOwnBookings($query, $host->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('booking_source', $request->source);
        }

        if ($request->filled('payment')) {
            $query->where('payment_method', $request->payment);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('host.bookings.index', [
            'bookings' => $bookings,
            'title' => 'All Bookings',
            'filter' => null,
            'statuses' => Booking::getStatuses(),
            'sources' => Booking::getBookingSources(),
            'paymentMethods' => Booking::getPaymentMethods(),
            'classPlansCount' => $host->classPlans()->where('is_active', true)->count(),
            'servicePlansCount' => $host->servicePlans()->where('is_active', true)->count(),
            'membershipPlansCount' => $host->membershipPlans()->active()->count(),
            'spaceRentalConfigsCount' => $host->spaceRentalConfigs()->active()->count(),
        ]);
    }

    /**
     * Display upcoming bookings
     */
    public function upcoming(Request $request)
    {
        $viewOwnOnly = $this->authorizeBookingsRead();
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED])
            ->whereHasMorph('bookable', [ClassSession::class], function ($q) {
                $q->where('start_time', '>=', now());
            })
            ->orderBy('booked_at', 'desc');

        if ($viewOwnOnly) {
            $this->scopeToOwnBookings($query, $host->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('host.bookings.index', [
            'bookings' => $bookings,
            'title' => 'Upcoming Bookings',
            'filter' => 'upcoming',
            'statuses' => Booking::getStatuses(),
            'sources' => Booking::getBookingSources(),
            'paymentMethods' => Booking::getPaymentMethods(),
        ]);
    }

    /**
     * Display cancelled bookings
     */
    public function cancelled(Request $request)
    {
        $viewOwnOnly = $this->authorizeBookingsRead();
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->cancelled()
            ->orderBy('cancelled_at', 'desc');

        if ($viewOwnOnly) {
            $this->scopeToOwnBookings($query, $host->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('host.bookings.index', [
            'bookings' => $bookings,
            'title' => 'Cancellations',
            'filter' => 'cancelled',
            'statuses' => Booking::getStatuses(),
            'sources' => Booking::getBookingSources(),
            'paymentMethods' => Booking::getPaymentMethods(),
        ]);
    }

    /**
     * Display no-show bookings
     */
    public function noShows(Request $request)
    {
        $viewOwnOnly = $this->authorizeBookingsRead();
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->noShow()
            ->orderBy('booked_at', 'desc');

        if ($viewOwnOnly) {
            $this->scopeToOwnBookings($query, $host->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('host.bookings.index', [
            'bookings' => $bookings,
            'title' => 'No-Shows',
            'filter' => 'no-shows',
            'statuses' => Booking::getStatuses(),
            'sources' => Booking::getBookingSources(),
            'paymentMethods' => Booking::getPaymentMethods(),
        ]);
    }

    /**
     * Show a specific booking
     */
    public function show(Booking $booking)
    {
        $host = auth()->user()->currentHost();

        if ($booking->host_id !== $host->id) {
            abort(403);
        }

        $viewOwnOnly = $this->authorizeBookingsRead();
        if ($viewOwnOnly && !$this->bookingAssignedToUser($booking)) {
            abort(403, 'You do not have permission to view this booking.');
        }

        $booking->load(['client', 'bookable', 'createdBy', 'cancelledBy', 'customerMembership', 'classPackPurchase', 'payments']);

        return view('host.bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Resend intake form email to client
     */
    public function resendIntake(Booking $booking)
    {
        $host = auth()->user()->currentHost();

        if ($booking->host_id !== $host->id) {
            abort(403);
        }

        $viewOwnOnly = $this->authorizeBookingsRead();
        if ($viewOwnOnly && !$this->bookingAssignedToUser($booking)) {
            abort(403, 'You do not have permission to act on this booking.');
        }

        $client = $booking->client;

        if (!$client || !$client->email) {
            return back()->with('error', 'Client does not have an email address.');
        }

        // Get pending questionnaire responses for this booking
        $responses = QuestionnaireResponse::where('booking_id', $booking->id)
            ->incomplete()
            ->with('version.questionnaire')
            ->get()
            ->toArray();

        if (empty($responses)) {
            return back()->with('error', 'No pending intake forms found for this booking.');
        }

        try {
            Mail::to($client->email)
                ->send(new BookingConfirmationMail($booking, $responses));

            return back()->with('success', 'Intake form email resent successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to resend intake email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send email. Please try again.');
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        $host = auth()->user()->currentHost();

        if ($booking->host_id !== $host->id) {
            abort(403);
        }

        if (!auth()->user()->hasPermission('bookings.cancel')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel bookings.',
            ], 403);
        }

        // If the user only has view_own, they can only cancel bookings on their own sessions.
        $user = auth()->user();
        if (!$user->hasPermission('bookings.view') && $user->hasPermission('bookings.view_own')
            && !$this->bookingAssignedToUser($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel bookings on sessions you teach.',
            ], 403);
        }

        // Check if booking can be cancelled
        if (!$booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Cancel the booking
        $booking->cancel(
            $validated['reason'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
        ]);
    }
}
