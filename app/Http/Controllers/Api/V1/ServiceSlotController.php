<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HasAttendeeRoster;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ServiceSlotDetailResource;
use App\Models\Booking;
use App\Models\ServiceSlot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mobile service-slot detail. Listing is served by the unified
 * `ScheduleController` feed; this exposes the tap-through detail and the
 * paginated attendee roster.
 */
class ServiceSlotController extends Controller
{
    use HasAttendeeRoster;

    /** How many attendees to embed in the detail payload before paginating. */
    private const ATTENDEE_PREVIEW = 8;

    public function show(Request $request, int $id): ServiceSlotDetailResource
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        abort_unless(
            $user->hasPermission('schedule.view', $host) || $user->hasPermission('schedule.view_own', $host),
            403,
            'You do not have permission to view the schedule.'
        );

        $attended = [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED];

        $slot = ServiceSlot::where('host_id', $host->id)
            ->with([
                'servicePlan',
                'instructor',
                'location',
                'room',
                'bookings' => fn ($q) => $q->with('client')->latest('id')->limit(self::ATTENDEE_PREVIEW),
            ])
            ->withCount([
                'bookings as total_count',
                'bookings as booked_count' => fn ($q) => $q->whereIn('status', $attended),
                'bookings as checked_in_count' => fn ($q) => $q->whereIn('status', $attended)->whereNotNull('checked_in_at'),
                'bookings as intake_completed_count' => fn ($q) => $q->where('intake_status', 'completed'),
                'bookings as intake_pending_count' => fn ($q) => $q->where('intake_status', 'pending'),
                'bookings as cancelled_count' => fn ($q) => $q->where('status', Booking::STATUS_CANCELLED),
            ])
            ->findOrFail($id);

        return new ServiceSlotDetailResource($slot);
    }

    /**
     * Paginated, searchable attendee roster for one service slot.
     */
    public function bookings(Request $request, int $id): AnonymousResourceCollection
    {
        return $this->rosterFor($request, ServiceSlot::class, $id);
    }
}