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
     * Display all bookings
     */
    public function index(Request $request)
    {
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->orderBy('booked_at', 'desc');

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
        ]);
    }

    /**
     * Display upcoming bookings
     */
    public function upcoming(Request $request)
    {
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED])
            ->whereHasMorph('bookable', [ClassSession::class], function ($q) {
                $q->where('start_time', '>=', now());
            })
            ->orderBy('booked_at', 'desc');

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
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->cancelled()
            ->orderBy('cancelled_at', 'desc');

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
        $host = auth()->user()->currentHost();

        $query = Booking::forHost($host->id)
            ->with(['client', 'bookable', 'createdBy'])
            ->noShow()
            ->orderBy('booked_at', 'desc');

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
