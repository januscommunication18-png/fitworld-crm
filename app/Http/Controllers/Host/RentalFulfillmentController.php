<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\RentalBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RentalFulfillmentController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $status = $request->get('status');
        $search = $request->get('search');

        $query = $host->rentalBookings()
            ->with(['rentalItem', 'client', 'transaction'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('fulfillment_status', $status);
        }

        // Search by request_id, client name, or item name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('rentalItem', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => $host->rentalBookings()->count(),
            'pending' => $host->rentalBookings()->pending()->count(),
            'prepared' => $host->rentalBookings()->prepared()->count(),
            'handed_out' => $host->rentalBookings()->handedOut()->count(),
            'overdue' => $host->rentalBookings()->overdue()->count(),
            'returned' => $host->rentalBookings()->where('fulfillment_status', 'returned')->count(),
        ];

        $statuses = RentalBooking::getStatuses();

        return view('host.rentals.fulfillment.index', compact(
            'bookings',
            'status',
            'search',
            'stats',
            'statuses'
        ));
    }

    public function show(RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        $booking->load([
            'rentalItem',
            'client',
            'transaction',
            'statusLogs.user',
            'bookable',
        ]);

        $statuses = RentalBooking::getStatuses();

        return view('host.rentals.fulfillment.show', compact('booking', 'statuses'));
    }

    public function updateStatus(Request $request, RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        $request->validate([
            'status' => 'required|in:pending,prepared,handed_out,returned,lost',
            'notes' => 'nullable|string|max:1000',
            'condition' => 'required_if:status,returned|nullable|in:good,damaged',
            'damage_charge' => 'nullable|numeric|min:0',
        ]);

        $newStatus = $request->input('status');
        $notes = $request->input('notes');
        $user = auth()->user();

        // Handle specific status transitions
        switch ($newStatus) {
            case RentalBooking::STATUS_PREPARED:
                if (!$booking->isPending()) {
                    return back()->with('error', 'Can only prepare from pending status.');
                }
                $booking->markPrepared($user, $notes);
                break;

            case RentalBooking::STATUS_HANDED_OUT:
                if (!$booking->isPrepared()) {
                    return back()->with('error', 'Can only hand out from prepared status.');
                }
                $booking->markHandedOut($user, $notes);
                break;

            case RentalBooking::STATUS_RETURNED:
                if (!$booking->isHandedOut()) {
                    return back()->with('error', 'Can only return items that are currently out.');
                }
                $condition = $request->input('condition', 'good');
                $damageCharge = (float) $request->input('damage_charge', 0);
                $booking->markReturned($user, $condition, $notes, $damageCharge);
                break;

            case RentalBooking::STATUS_LOST:
                if (!$booking->isHandedOut()) {
                    return back()->with('error', 'Can only mark as lost items that are currently out.');
                }
                $booking->markLost($user, $notes);
                break;

            default:
                return back()->with('error', 'Invalid status transition.');
        }

        return back()->with('success', 'Status updated successfully.');
    }

    // Legacy methods for quick actions (kept for backwards compatibility)
    public function prepare(RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        if (!$booking->isPending()) {
            return back()->with('error', 'This booking cannot be marked as prepared.');
        }

        $booking->markPrepared(auth()->user());

        return back()->with('success', 'Rental marked as prepared.');
    }

    public function handOut(RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        if (!$booking->isPrepared()) {
            return back()->with('error', 'This booking must be prepared first.');
        }

        $booking->markHandedOut(auth()->user());

        return back()->with('success', 'Rental handed out successfully.');
    }

    public function return(Request $request, RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        if (!$booking->isHandedOut()) {
            return back()->with('error', 'This booking is not currently out.');
        }

        $request->validate([
            'condition' => 'required|in:good,damaged',
            'damage_notes' => 'required_if:condition,damaged|nullable|string|max:500',
            'damage_charge' => 'nullable|numeric|min:0',
        ]);

        $booking->markReturned(
            auth()->user(),
            $request->input('condition'),
            $request->input('damage_notes'),
            (float) $request->input('damage_charge', 0)
        );

        $message = $request->input('condition') === 'good'
            ? 'Item returned in good condition.'
            : 'Item returned with damage noted.';

        return back()->with('success', $message);
    }

    public function lost(Request $request, RentalBooking $booking)
    {
        $this->authorizeHost($booking);

        if (!$booking->isHandedOut()) {
            return back()->with('error', 'This booking is not currently out.');
        }

        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $booking->markLost(auth()->user(), $request->input('notes'));

        return back()->with('success', 'Item marked as lost. Inventory has been adjusted.');
    }

    private function authorizeHost(RentalBooking $booking): void
    {
        if ($booking->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
