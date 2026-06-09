<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SpaceRentalDetailResource;
use App\Models\SpaceRental;
use Illuminate\Http\Request;

/**
 * Mobile space-rental detail. Listing is served by the unified
 * `ScheduleController` feed; this exposes the tap-through detail.
 */
class SpaceRentalController extends Controller
{
    public function show(Request $request, int $id): SpaceRentalDetailResource
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        // Rentals aren't instructor-scoped, so they require full schedule view.
        abort_unless(
            $user->hasPermission('schedule.view', $host),
            403,
            'You do not have permission to view space rentals.'
        );

        $rental = SpaceRental::where('host_id', $host->id)
            ->with([
                'config.location',
                'config.room',
                'client',
                'createdBy',
                'confirmedBy',
                'completedBy',
                'cancelledBy',
                'statusLogs.updatedByUser',
            ])
            ->findOrFail($id);

        return new SpaceRentalDetailResource($rental);
    }
}