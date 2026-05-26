<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\QuestionnaireResponse;
use App\Mail\BookingConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
     * Display all bookings — series purchases are collapsed into a single
     * row per purchase (the lowest-id booking in the series acts as the
     * representative; aggregate counts/date range come along in $seriesAggregates).
     */
    public function index(Request $request)
    {
        $viewOwnOnly = $this->authorizeBookingsRead();
        $host = auth()->user()->currentHost();

        // Base scope shared by representative selection and aggregate counts.
        $applyFilters = function ($q) use ($request, $viewOwnOnly, $host) {
            $q->where('host_id', $host->id);
            if ($viewOwnOnly) {
                $this->scopeToOwnBookings($q, $host->id);
            }
            if ($request->filled('status'))  $q->where('status', $request->status);
            if ($request->filled('source'))  $q->where('booking_source', $request->source);
            if ($request->filled('payment')) $q->where('payment_method', $request->payment);
            if ($request->filled('search')) {
                $search = $request->search;
                $q->whereHas('client', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            }
        };

        [$paginator, $seriesAggregates] = $this->paginateGroupedBookings($applyFilters, $request, 25);

        // Eager-load the individual session bookings for every series visible
        // on this page so the per-series drawer can list them without N+1.
        $seriesIds = collect($paginator->items())->pluck('series_id')->filter()->unique()->values();
        $seriesSessions = [];
        if ($seriesIds->isNotEmpty()) {
            $seriesSessions = Booking::with(['bookable.primaryInstructor', 'bookable.room.location'])
                ->whereIn('series_id', $seriesIds)
                ->get()
                ->groupBy('series_id')
                ->map(fn ($coll) => $coll->sortBy(fn ($b) => optional($b->bookable)->start_time)->values())
                ->all();
        }

        return view('host.bookings.index', [
            'bookings' => $paginator,
            'seriesAggregates' => $seriesAggregates,
            'seriesSessions' => $seriesSessions,
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
     * Build a paginator where each row is either:
     *   - one Booking (singleton, series_id is null), OR
     *   - the lowest-id Booking in a series (representative for the group)
     *
     * Returns [$paginator, $seriesAggregates]. $seriesAggregates is keyed by
     * series_id and contains total / per-status counts and first/last session
     * timestamps so the view can render "50 sessions · May 27 – Jul 23".
     */
    protected function paginateGroupedBookings(\Closure $applyFilters, Request $request, int $perPage = 25): array
    {
        // Step 1 — pull the representative id for every unique purchase. We
        // group by COALESCE(series_id, "b:" || id) so singletons each get a
        // unique key. MIN(id) is the row we'll render.
        $repQuery = Booking::query();
        $applyFilters($repQuery);
        $rows = $repQuery
            ->selectRaw('MIN(id) as rep_id, COALESCE(series_id, CONCAT("b:", id)) as purchase_key')
            ->groupBy('purchase_key')
            ->orderByDesc('rep_id')
            ->get();

        $total = $rows->count();
        $page = max(1, (int) $request->get('page', 1));
        $repIds = $rows->forPage($page, $perPage)->pluck('rep_id')->all();

        // Step 2 — load the actual Booking rows with relations, preserving order.
        $bookings = Booking::with(['client', 'bookable', 'createdBy'])
            ->whereIn('id', $repIds)
            ->get()
            ->sortByDesc('id')
            ->values();

        // Step 3 — aggregate stats for every series_id on this page.
        $seriesIds = $bookings->pluck('series_id')->filter()->unique()->values();
        $seriesAggregates = [];

        if ($seriesIds->isNotEmpty()) {
            $statusRows = Booking::query()
                ->whereIn('series_id', $seriesIds)
                ->groupBy('series_id')
                ->selectRaw('series_id,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as waitlisted,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as no_show,
                    SUM(price_paid) as total_paid', [
                        Booking::STATUS_CONFIRMED,
                        Booking::STATUS_CANCELLED,
                        Booking::STATUS_WAITLISTED,
                        Booking::STATUS_COMPLETED,
                        Booking::STATUS_NO_SHOW,
                    ])
                ->get()
                ->keyBy('series_id');

            $rangeRows = DB::table('bookings')
                ->join('class_sessions', function ($j) {
                    $j->on('bookings.bookable_id', '=', 'class_sessions.id')
                      ->where('bookings.bookable_type', '=', ClassSession::class);
                })
                ->whereIn('bookings.series_id', $seriesIds)
                ->groupBy('bookings.series_id')
                ->selectRaw('bookings.series_id,
                    MIN(class_sessions.start_time) as first_session_at,
                    MAX(class_sessions.start_time) as last_session_at')
                ->get()
                ->keyBy('series_id');

            foreach ($statusRows as $sid => $row) {
                $range = $rangeRows->get($sid);
                $seriesAggregates[$sid] = [
                    'total' => (int) $row->total,
                    'confirmed' => (int) $row->confirmed,
                    'cancelled' => (int) $row->cancelled,
                    'waitlisted' => (int) $row->waitlisted,
                    'completed' => (int) $row->completed,
                    'no_show' => (int) $row->no_show,
                    'total_paid' => (float) $row->total_paid,
                    'first_session_at' => $range?->first_session_at,
                    'last_session_at' => $range?->last_session_at,
                ];
            }
        }

        $paginator = new LengthAwarePaginator(
            $bookings,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [$paginator, $seriesAggregates];
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

        // For a series booking the per-row price_paid is the per-session split
        // (or null on rows whose price wasn't backfilled at confirm time). Surface
        // the *whole-series* totals so the Payment block can show "$420 for series".
        $seriesTotalPaid = null;
        $seriesSessionCount = null;
        $linkedTransaction = null;
        if ($booking->series_id) {
            $seriesSessionCount = Booking::where('series_id', $booking->series_id)->count();
            $seriesTotalPaid = (float) Booking::where('series_id', $booking->series_id)->sum('price_paid');
            $txId = (int) str_replace('TX-', '', $booking->series_id);
            $linkedTransaction = $txId ? \App\Models\Transaction::find($txId) : null;
            // If the per-row backfill never ran, fall back to the linked
            // transaction's total_amount so the series total isn't itself $0.
            if ($seriesTotalPaid <= 0 && $linkedTransaction) {
                $seriesTotalPaid = (float) $linkedTransaction->total_amount;
            }
        } else {
            // Single bookings: transaction points back via transaction.booking_id.
            $linkedTransaction = \App\Models\Transaction::where('booking_id', $booking->id)->first();
        }

        return view('host.bookings.show', [
            'booking' => $booking,
            'seriesTotalPaid' => $seriesTotalPaid,
            'seriesSessionCount' => $seriesSessionCount,
            'linkedTransaction' => $linkedTransaction,
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

    /**
     * Reactivate a cancelled booking — clears the cancellation fields and
     * restores status to confirmed. Useful when a cancel was done in error.
     * Does not re-trigger booking-confirmation emails, does not re-check
     * session capacity — the host is acting deliberately.
     */
    public function reactivate(Request $request, Booking $booking)
    {
        $host = auth()->user()->currentHost();

        if ($booking->host_id !== $host->id) {
            abort(403);
        }

        if (!auth()->user()->hasPermission('bookings.cancel')) {
            abort(403, 'You do not have permission to reactivate bookings.');
        }

        if ($booking->status !== Booking::STATUS_CANCELLED) {
            return back()->with('error', 'Only cancelled bookings can be reactivated.');
        }

        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'cancelled_at' => null,
            'cancelled_by_user_id' => null,
            'cancellation_reason' => null,
            'cancellation_notes' => null,
            'is_late_cancellation' => false,
        ]);

        return back()->with('success', 'Booking reactivated and set to Confirmed.');
    }
}
