<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Http\Resources\Api\V1\BookingAttendeeResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Shared paginated + searchable attendee roster for a bookable (class session
 * or service slot). Keeps the detail payload small while still letting the app
 * page through hundreds of bookings.
 */
trait HasAttendeeRoster
{
    protected function rosterFor(Request $request, string $bookableType, int $id): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        abort_unless(
            $user->hasPermission('schedule.view', $host) || $user->hasPermission('schedule.view_own', $host),
            403,
            'You do not have permission to view the schedule.'
        );

        $perPage = (int) $request->get('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $query = Booking::where('host_id', $host->id)
            ->where('bookable_type', $bookableType)
            ->where('bookable_id', $id)
            ->with('client')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($c) use ($search) {
                $c->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        return BookingAttendeeResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }
}