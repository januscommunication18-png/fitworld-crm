<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Concerns\HasAttendeeRoster;
use App\Http\Resources\Api\V1\ClassSessionDetailResource;
use App\Http\Resources\Api\V1\ClassSessionResource;
use App\Models\Booking;
use App\Models\ClassSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mobile class-sessions (schedule) listing. Host resolved by `studio.context`.
 *
 * Supports range=today|week|month|all (default today) anchored on `date`,
 * plus status/class_plan/instructor/location filters.
 */
class ClassSessionController extends Controller
{
    use HasAttendeeRoster;

    /** How many attendees to embed in the detail payload before paginating. */
    private const ATTENDEE_PREVIEW = 8;

    public function index(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');
        $range = $request->input('range', 'today');
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        $query = ClassSession::where('host_id', $host->id)
            ->whereNotNull('class_plan_id')
            ->with(['classPlan', 'primaryInstructor', 'location', 'room'])
            ->withCount(['bookings' => fn ($q) => $q->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_COMPLETED,
            ])])
            ->orderBy('start_time');

        [$start, $end] = match ($range) {
            'week' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'all' => [null, null],
            default => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };

        if ($start && $end) {
            $query->whereBetween('start_time', [$start, $end]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_plan_id')) {
            $query->where('class_plan_id', $request->class_plan_id);
        }

        if ($request->filled('instructor_id')) {
            $query->where('primary_instructor_id', $request->instructor_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        return ClassSessionResource::collection(
            $query->paginate(50)->withQueryString()
        );
    }

    /**
     * Full detail for one class or membership session (shared model). Roster
     * stats are aggregate counts; only a small attendee preview is embedded —
     * the full roster is paginated via [bookings].
     */
    public function show(Request $request, int $id): ClassSessionDetailResource
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        abort_unless(
            $user->hasPermission('schedule.view', $host) || $user->hasPermission('schedule.view_own', $host),
            403,
            'You do not have permission to view the schedule.'
        );

        $attended = [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED];

        $session = ClassSession::where('host_id', $host->id)
            ->with([
                'classPlan',
                'primaryInstructor',
                'backupInstructors',
                'location',
                'room',
                'membershipPlans',
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

        return new ClassSessionDetailResource($session);
    }

    /**
     * Paginated, searchable attendee roster for one class/membership session.
     */
    public function bookings(Request $request, int $id): AnonymousResourceCollection
    {
        return $this->rosterFor($request, ClassSession::class, $id);
    }
}
