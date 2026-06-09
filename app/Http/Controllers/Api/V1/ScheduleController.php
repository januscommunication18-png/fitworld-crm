<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Instructor;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile "Schedule" hub. Returns a single normalized, chronologically merged
 * feed across the four schedule sources (class sessions, service slots,
 * membership sessions, space rentals). Host resolved by `studio.context`.
 *
 * Query params:
 *   type  = all|class|service|membership|rental   (default all)
 *   range = today|week|month|all                  (default today)
 *   date  = anchor date for the range             (default now)
 *
 * Each item is normalized to:
 *   { id, type, type_label, title, subtitle, start_time, end_time,
 *     status, status_label, metric }
 * so the mobile "All" tab can render one timeline; type tabs reuse the same
 * shape filtered to a single source.
 */
class ScheduleController extends Controller
{
    private const TYPE_LABELS = [
        'class' => 'Class',
        'service' => 'Service',
        'membership' => 'Membership',
        'rental' => 'Rental',
    ];

    public function index(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();

        $hasFull = $user->hasPermission('schedule.view', $host);
        $hasOwn = $user->hasPermission('schedule.view_own', $host);
        abort_unless($hasFull || $hasOwn, 403, 'You do not have permission to view the schedule.');
        $viewOwnOnly = ! $hasFull && $hasOwn;

        $type = (string) $request->input('type', 'all');
        $range = (string) $request->input('range', 'today');
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        [$start, $end] = match ($range) {
            'week' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'all' => [null, null],
            default => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };

        // Instructor ids the user teaches — only needed when scoped to "own".
        $ownInstructorIds = $viewOwnOnly
            ? Instructor::where('host_id', $host->id)->where('user_id', $user->id)->pluck('id')->all()
            : [];

        $items = collect();

        // Class & membership sessions (same table; membership = no class plan).
        if (in_array($type, ['all', 'class', 'membership'], true)) {
            $q = ClassSession::where('host_id', $host->id)
                ->with(['classPlan', 'primaryInstructor', 'location', 'membershipPlans'])
                ->withCount(['bookings' => fn ($b) => $b->whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                ])]);

            if ($type === 'class') {
                $q->whereNotNull('class_plan_id');
            } elseif ($type === 'membership') {
                $q->whereNull('class_plan_id');
            }
            if ($start) {
                $q->whereBetween('start_time', [$start, $end]);
            }
            if ($viewOwnOnly) {
                $this->scopeSessionsToOwn($q, $ownInstructorIds);
            }

            foreach ($q->get() as $s) {
                $items->push($this->normalizeSession($s));
            }
        }

        // Service slots.
        if (in_array($type, ['all', 'service'], true)) {
            $q = ServiceSlot::where('host_id', $host->id)
                ->with(['servicePlan', 'instructor', 'location']);
            if ($start) {
                $q->whereBetween('start_time', [$start, $end]);
            }
            if ($viewOwnOnly) {
                $q->whereIn('instructor_id', $ownInstructorIds ?: [0]);
            }

            foreach ($q->get() as $slot) {
                $items->push($this->normalizeSlot($slot));
            }
        }

        // Space rentals — not instructor-scoped; hidden from "own-only" staff.
        if (in_array($type, ['all', 'rental'], true) && ! $viewOwnOnly) {
            $q = SpaceRental::where('host_id', $host->id)
                ->with(['config.location', 'config.room', 'client']);
            if ($start) {
                $q->whereBetween('start_time', [$start, $end]);
            }

            foreach ($q->get() as $rental) {
                $items->push($this->normalizeRental($rental));
            }
        }

        $items = $items
            ->sortBy(fn ($i) => $i['start_time'] ?? '')
            ->values();

        return response()->json(['data' => $items]);
    }

    private function normalizeSession(ClassSession $s): array
    {
        $statuses = ClassSession::getStatuses();
        $isMembership = $s->class_plan_id === null;
        $capacity = (int) $s->capacity;
        $booked = (int) ($s->bookings_count ?? 0);

        return [
            'id' => $s->id,
            'type' => $isMembership ? 'membership' : 'class',
            'type_label' => $isMembership ? self::TYPE_LABELS['membership'] : self::TYPE_LABELS['class'],
            'title' => $s->classPlan?->name
                ?? $s->membershipPlans->first()?->name
                ?? $s->title
                ?? ($isMembership ? 'Membership Session' : 'Class'),
            'subtitle' => $this->joinParts([$s->primaryInstructor?->name, $s->location?->name]),
            'start_time' => $s->start_time?->toIso8601String(),
            'end_time' => $s->end_time?->toIso8601String(),
            'status' => $s->status,
            'status_label' => $statuses[$s->status] ?? $s->status,
            'metric' => $capacity > 0 ? "{$booked}/{$capacity}" : null,
        ];
    }

    private function normalizeSlot(ServiceSlot $slot): array
    {
        $statuses = ServiceSlot::getStatuses();
        $price = $slot->getEffectivePrice();

        return [
            'id' => $slot->id,
            'type' => 'service',
            'type_label' => self::TYPE_LABELS['service'],
            'title' => $slot->servicePlan?->name ?? $slot->title ?? 'Service',
            'subtitle' => $this->joinParts([$slot->instructor?->name, $slot->location?->name]),
            'start_time' => $slot->start_time?->toIso8601String(),
            'end_time' => $slot->end_time?->toIso8601String(),
            'status' => $slot->status,
            'status_label' => $statuses[$slot->status] ?? $slot->status,
            'metric' => $price !== null ? '$'.number_format($price, 2) : 'Free',
        ];
    }

    private function normalizeRental(SpaceRental $rental): array
    {
        $statuses = SpaceRental::getStatuses();

        return [
            'id' => $rental->id,
            'type' => 'rental',
            'type_label' => self::TYPE_LABELS['rental'],
            'title' => $rental->config?->name ?? 'Space Rental',
            'subtitle' => $this->joinParts([$rental->client_name, $rental->formatted_purpose]),
            'start_time' => $rental->start_time?->toIso8601String(),
            'end_time' => $rental->end_time?->toIso8601String(),
            'status' => $rental->status,
            'status_label' => $statuses[$rental->status] ?? $rental->status,
            'metric' => $rental->total_amount !== null
                ? '$'.number_format((float) $rental->total_amount, 2)
                : null,
        ];
    }

    /**
     * Restrict a ClassSession query to sessions the given instructors teach
     * (primary, backup, or additional backup).
     */
    private function scopeSessionsToOwn($query, array $instructorIds): void
    {
        $ids = $instructorIds ?: [0];
        $query->where(function ($q) use ($ids) {
            $q->whereIn('primary_instructor_id', $ids)
                ->orWhereIn('backup_instructor_id', $ids)
                ->orWhereHas('backupInstructors', fn ($q2) => $q2->whereIn('instructors.id', $ids));
        });
    }

    private function joinParts(array $parts): ?string
    {
        $clean = array_values(array_filter($parts, fn ($p) => $p !== null && $p !== ''));

        return $clean ? implode(' · ', $clean) : null;
    }
}