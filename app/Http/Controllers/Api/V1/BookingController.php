<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BookingDetailResource;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Instructor;
use App\Models\ServiceSlot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Mobile bookings listing. Host resolved by `studio.context` middleware.
 */
class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        // Permission scoping — mirrors the host web BookingController:
        //   bookings.view      → all bookings
        //   bookings.view_own  → only the instructor's own class sessions
        //   neither            → 403
        $hasFull = $user->hasPermission('bookings.view', $host);
        $hasOwn = $user->hasPermission('bookings.view_own', $host);
        abort_unless($hasFull || $hasOwn, 403, 'You do not have permission to view bookings.');
        $viewOwnOnly = ! $hasFull && $hasOwn;

        // Page size — client-controlled (e.g. 5 for testing), capped at 100.
        $perPage = (int) $request->get('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        // Shared filter scope (used for both representative selection below).
        $applyFilters = function ($q) use ($request, $viewOwnOnly, $host, $user) {
            $q->where('host_id', $host->id);
            if ($viewOwnOnly) {
                $this->scopeToOwnBookings($q, $host->id, (int) $user->id);
            }
            // Tab filters — mirror the host web tabs (All / Upcoming /
            // Cancellations / No-Shows).
            $this->applyTabFilter($q, (string) $request->get('filter', ''));
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            if ($request->filled('source')) {
                $q->where('booking_source', $request->source);
            }
            if ($request->filled('payment')) {
                $q->where('payment_method', $request->payment);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $like = '%'.$search.'%';
                $q->where(function ($outer) use ($search, $like) {
                    // Client — name / email (mirrors the client listing search).
                    $outer->whereHas('client', function ($q2) use ($search, $like) {
                        $q2->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ['%'.strtolower($search).'%']);
                    })
                        // Booked class session — title / plan name / location.
                        ->orWhereHasMorph('bookable', [ClassSession::class], function ($q2) use ($like) {
                            $q2->where('title', 'like', $like)
                                ->orWhereHas('classPlan', fn ($q3) => $q3->where('name', 'like', $like))
                                ->orWhereHas('location', function ($q3) use ($like) {
                                    $q3->where('name', 'like', $like)
                                        ->orWhere('city', 'like', $like)
                                        ->orWhere('state', 'like', $like);
                                });
                        })
                        // Booked service slot — title / plan name / location.
                        ->orWhereHasMorph('bookable', [ServiceSlot::class], function ($q2) use ($like) {
                            $q2->where('title', 'like', $like)
                                ->orWhereHas('servicePlan', fn ($q3) => $q3->where('name', 'like', $like))
                                ->orWhereHas('location', function ($q3) use ($like) {
                                    $q3->where('name', 'like', $like)
                                        ->orWhere('city', 'like', $like)
                                        ->orWhere('state', 'like', $like);
                                });
                        });
                });
            }
        };

        // Only the "All" tab collapses a series into one representative row.
        // Upcoming / Cancellations / No-Shows list every individual session row,
        // matching the host web (where those tabs don't group by series).
        $filter = (string) $request->get('filter', '');
        if ($filter !== '' && $filter !== 'all') {
            $query = Booking::with(['client', 'bookable' => fn (MorphTo $m) => $m->morphWith([
                ClassSession::class => ['classPlan'],
            ])]);
            $applyFilters($query);
            $query->latest('booked_at')->orderByDesc('id');

            return BookingResource::collection(
                $query->paginate($perPage)->withQueryString()
            );
        }

        // "All" tab — one row per purchase: a series collapses into its
        // lowest-id booking, mirroring the host web "All Bookings" grouping.
        $repQuery = Booking::query();
        $applyFilters($repQuery);
        $repRows = $repQuery
            ->selectRaw('MIN(id) as rep_id, COALESCE(series_id, CONCAT("b:", id)) as purchase_key')
            ->groupBy('purchase_key')
            ->orderByDesc('rep_id')
            ->get();

        $total = $repRows->count();
        $page = max(1, (int) $request->get('page', 1));
        $repIds = $repRows->forPage($page, $perPage)->pluck('rep_id')->all();

        $bookings = Booking::with(['client', 'bookable' => fn (MorphTo $m) => $m->morphWith([
            ClassSession::class => ['classPlan'],
        ])])
            ->whereIn('id', $repIds)
            ->get()
            ->sortByDesc('id')
            ->values();

        $this->attachSeriesAggregates($bookings);

        $paginator = new LengthAwarePaginator(
            $bookings,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return BookingResource::collection($paginator);
    }

    /**
     * Every individual session booking inside one series, oldest session first.
     * Powers the mobile "sessions in this series" screen.
     */
    public function series(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        $hasFull = $user->hasPermission('bookings.view', $host);
        $hasOwn = $user->hasPermission('bookings.view_own', $host);
        abort_unless($hasFull || $hasOwn, 403, 'You do not have permission to view bookings.');

        $seriesId = trim((string) $request->get('series_id', ''));
        abort_if($seriesId === '', 422, 'series_id is required.');

        $query = Booking::where('host_id', $host->id)
            ->where('series_id', $seriesId)
            ->with(['bookable' => fn (MorphTo $m) => $m->morphWith([
                ClassSession::class => ['classPlan', 'primaryInstructor', 'location'],
                ServiceSlot::class => ['servicePlan', 'instructor', 'location'],
            ])]);

        if (! $hasFull && $hasOwn) {
            $this->scopeToOwnBookings($query, $host->id, (int) $user->id);
        }

        $statuses = Booking::getStatuses();
        $sessions = $query->get()
            ->sortBy(fn ($b) => optional($b->bookable)->start_time)
            ->values()
            ->map(function ($b) use ($statuses) {
                $bk = $b->bookable;
                $instructor = $bk?->primaryInstructor ?? $bk?->instructor ?? null;

                return [
                    'id' => $b->id,
                    'status' => $b->status,
                    'status_label' => $statuses[$b->status] ?? $b->status,
                    'name' => $bk?->classPlan?->name ?? $bk?->servicePlan?->name ?? $bk?->title ?? $bk?->name ?? 'Session',
                    'start_time' => $bk?->start_time?->toIso8601String(),
                    'end_time' => $bk?->end_time?->toIso8601String(),
                    'instructor' => $instructor?->name,
                    'location' => $bk?->location?->name,
                    'checked_in_at' => $b->checked_in_at?->toIso8601String(),
                ];
            });

        return response()->json(['data' => $sessions]);
    }

    /**
     * Attach per-series aggregate counts and the session date-range to each
     * representative booking that belongs to a series.
     */
    private function attachSeriesAggregates($bookings): void
    {
        $seriesIds = $bookings->pluck('series_id')->filter()->unique()->values();
        if ($seriesIds->isEmpty()) {
            return;
        }

        $statusRows = Booking::whereIn('series_id', $seriesIds)
            ->groupBy('series_id')
            ->selectRaw('series_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                SUM(price_paid) as total_paid', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CANCELLED,
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

        foreach ($bookings as $b) {
            if (! $b->series_id) {
                continue;
            }
            $row = $statusRows->get($b->series_id);
            $range = $rangeRows->get($b->series_id);
            if ($row) {
                $b->setAttribute('series_total', (int) $row->total);
                $b->setAttribute('series_confirmed', (int) $row->confirmed);
                $b->setAttribute('series_cancelled', (int) $row->cancelled);
                $b->setAttribute('series_total_paid', (float) $row->total_paid);
            }
            if ($range) {
                $b->setAttribute('series_first_at', $range->first_session_at);
                $b->setAttribute('series_last_at', $range->last_session_at);
            }
        }
    }

    /**
     * Full booking detail for the mobile detail screen.
     */
    public function show(Request $request, int $id): BookingDetailResource
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        $hasFull = $user->hasPermission('bookings.view', $host);
        $hasOwn = $user->hasPermission('bookings.view_own', $host);
        abort_unless($hasFull || $hasOwn, 403, 'You do not have permission to view bookings.');

        $query = Booking::where('host_id', $host->id)
            ->with([
                'client',
                'bookable' => fn (MorphTo $m) => $m->morphWith([
                    ClassSession::class => ['classPlan', 'primaryInstructor', 'location'],
                    ServiceSlot::class => ['servicePlan', 'instructor', 'location'],
                ]),
                'createdBy',
                'cancelledBy',
            ]);

        if (! $hasFull && $hasOwn) {
            $this->scopeToOwnBookings($query, $host->id, (int) $user->id);
        }

        return new BookingDetailResource($query->findOrFail($id));
    }

    /**
     * Apply the listing tab filter (all/upcoming/cancelled/no-shows).
     */
    private function applyTabFilter($query, string $filter): void
    {
        switch ($filter) {
            case 'upcoming':
                $query->where('status', Booking::STATUS_CONFIRMED)
                    ->whereHasMorph('bookable', [ClassSession::class], function ($q) {
                        $q->where('start_time', '>=', now());
                    });
                break;
            case 'cancelled':
                $query->where('status', Booking::STATUS_CANCELLED);
                break;
            case 'no_show':
            case 'no-shows':
                $query->where('status', Booking::STATUS_NO_SHOW);
                break;
        }
    }

    /**
     * Restrict the query to bookings for class sessions the given user teaches
     * (primary, backup, or additional backup instructor).
     */
    private function scopeToOwnBookings($query, int $hostId, int $userId): void
    {
        $myInstructorIds = Instructor::where('host_id', $hostId)
            ->where('user_id', $userId)
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
}
