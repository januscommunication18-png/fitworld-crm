<?php

namespace App\Services\Schedule;

use App\Models\Host;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    protected ConflictChecker $conflictChecker;

    public function __construct(ConflictChecker $conflictChecker)
    {
        $this->conflictChecker = $conflictChecker;
    }

    /**
     * Get combined class sessions + service slots for a date range
     *
     * @param Host $host
     * @param Carbon $start
     * @param Carbon $end
     * @param array $filters ['type', 'location_id', 'instructor_id', 'status']
     * @return Collection
     */
    public function getScheduleItems(Host $host, Carbon $start, Carbon $end, array $filters = []): Collection
    {
        $type = $filters['type'] ?? 'both';
        $locationId = $filters['location_id'] ?? null;
        $instructorId = $filters['instructor_id'] ?? null;
        $status = $filters['status'] ?? null;

        $items = collect();

        // Get class sessions
        if ($type === 'both' || $type === 'classes') {
            $sessionsQuery = $host->classSessions()
                ->with(['classPlan', 'primaryInstructor', 'location', 'room'])
                ->forDateRange($start, $end);

            if ($locationId) {
                $sessionsQuery->forLocation($locationId);
            }

            if ($instructorId) {
                $sessionsQuery->forInstructor($instructorId);
            }

            if ($status) {
                if ($status === 'active') {
                    $sessionsQuery->notCancelled();
                } else {
                    $sessionsQuery->where('status', $status);
                }
            }

            $sessions = $sessionsQuery->orderBy('start_time')->get();

            // Add type identifier and normalize
            $sessions = $sessions->map(function ($session) {
                $session->schedule_type = 'class';
                $session->schedule_title = $session->display_title;
                $session->schedule_instructor = $session->primaryInstructor;
                $session->schedule_plan = $session->classPlan;
                $session->has_conflict = $this->checkSessionConflicts($session);
                return $session;
            });

            $items = $items->merge($sessions);
        }

        // Get service slots
        if ($type === 'both' || $type === 'services') {
            $slotsQuery = $host->serviceSlots()
                ->with(['servicePlan', 'instructor', 'location', 'room'])
                ->forDateRange($start, $end);

            if ($locationId) {
                $slotsQuery->where('location_id', $locationId);
            }

            if ($instructorId) {
                $slotsQuery->forInstructor($instructorId);
            }

            if ($status) {
                if ($status === 'active') {
                    $slotsQuery->notCancelled();
                } else {
                    $slotsQuery->where('status', $status);
                }
            }

            $slots = $slotsQuery->orderBy('start_time')->get();

            // Add type identifier and normalize
            $slots = $slots->map(function ($slot) {
                $slot->schedule_type = 'service';
                $slot->schedule_title = $slot->servicePlan?->name ?? 'Service Slot';
                $slot->schedule_instructor = $slot->instructor;
                $slot->schedule_plan = $slot->servicePlan;
                $slot->has_conflict = $this->checkSlotConflicts($slot);
                return $slot;
            });

            $items = $items->merge($slots);
        }

        // Sort combined items by start_time
        return $items->sortBy('start_time')->values();
    }

    /**
     * Get schedule grouped by location for Today view
     *
     * @param Host $host
     * @param Carbon $date
     * @param array $filters
     * @return Collection
     */
    public function getScheduleByLocation(Host $host, Carbon $date, array $filters = []): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $items = $this->getScheduleItems($host, $startOfDay, $endOfDay, $filters);

        // Group by location
        $grouped = $items->groupBy(function ($item) {
            return $item->location_id ?? 0;
        });

        // Sort within each group by time and add location info
        return $grouped->map(function ($locationItems, $locationId) {
            $location = $locationItems->first()?->location;
            return [
                'location' => $location,
                'location_id' => $locationId,
                'location_name' => $location?->name ?? 'No Location',
                'items' => $locationItems->sortBy('start_time')->values(),
                'count' => $locationItems->count(),
            ];
        })->sortBy('location_name')->values();
    }

    /**
     * Get schedule grouped by date for List view
     *
     * @param Host $host
     * @param Carbon $start
     * @param Carbon $end
     * @param array $filters
     * @return Collection
     */
    public function getScheduleByDate(Host $host, Carbon $start, Carbon $end, array $filters = []): Collection
    {
        $items = $this->getScheduleItems($host, $start, $end, $filters);

        // Group by date
        $grouped = $items->groupBy(function ($item) {
            return $item->start_time->format('Y-m-d');
        });

        // Sort within each group by time
        return $grouped->map(function ($dateItems, $date) {
            return [
                'date' => Carbon::parse($date),
                'items' => $dateItems->sortBy('start_time')->values(),
                'count' => $dateItems->count(),
                'classes_count' => $dateItems->where('schedule_type', 'class')->count(),
                'services_count' => $dateItems->where('schedule_type', 'service')->count(),
            ];
        })->sortKeys()->values();
    }

    /**
     * Format events for FullCalendar JSON
     *
     * @param Collection $items
     * @return array
     */
    public function formatForCalendar(Collection $items): array
    {
        return $items->map(function ($item) {
            $isClass = $item->schedule_type === 'class';

            $event = [
                'id' => $item->schedule_type . '_' . $item->id,
                'resourceId' => $item->schedule_type,
                'title' => $item->schedule_title,
                'start' => $item->start_time->toIso8601String(),
                'end' => $item->end_time->toIso8601String(),
                'type' => $item->schedule_type,
                'status' => $item->status,
                'backgroundColor' => $this->getEventColor($item),
                'borderColor' => $this->getEventBorderColor($item),
                'textColor' => '#fff',
                'extendedProps' => [
                    'type' => $item->schedule_type,
                    'model_id' => $item->id,
                    'status' => $item->status,
                    'instructor' => $item->schedule_instructor?->name ?? null,
                    'instructor_id' => $item->schedule_instructor?->id ?? null,
                    'location' => $item->location?->name ?? null,
                    'location_id' => $item->location_id,
                    'room' => $item->room?->name ?? null,
                    'room_id' => $item->room_id,
                    'price' => $item->getEffectivePrice(),
                    'has_conflict' => $item->has_conflict ?? false,
                ],
            ];

            // Add class-specific data
            if ($isClass) {
                $event['extendedProps']['capacity'] = $item->getEffectiveCapacity();
                $event['extendedProps']['booked'] = 0; // TODO: Get actual booking count
                $event['extendedProps']['class_plan_id'] = $item->class_plan_id;
                $event['extendedProps']['duration'] = $item->duration_minutes;
                $event['extendedProps']['view_url'] = route('class-sessions.show', $item);
                $event['extendedProps']['edit_url'] = route('class-sessions.edit', $item);
            } else {
                $event['extendedProps']['service_plan_id'] = $item->service_plan_id;
                $event['extendedProps']['duration'] = $item->duration_minutes;
                $event['extendedProps']['view_url'] = route('service-slots.show', $item);
                $event['extendedProps']['edit_url'] = route('service-slots.edit', $item);
            }

            return $event;
        })->values()->toArray();
    }

    /**
     * Get event color based on type and status
     */
    protected function getEventColor($item): string
    {
        $isClass = $item->schedule_type === 'class';

        // Cancelled events are gray
        if ($item->status === ClassSession::STATUS_CANCELLED || $item->status === ServiceSlot::STATUS_CANCELLED) {
            return '#6b7280'; // gray
        }

        // Draft events are amber
        if ($item->status === ClassSession::STATUS_DRAFT || $item->status === ServiceSlot::STATUS_DRAFT) {
            return '#f59e0b'; // amber
        }

        // Booked services are different color
        if (!$isClass && $item->status === ServiceSlot::STATUS_BOOKED) {
            return '#8b5cf6'; // purple
        }

        // Blocked services
        if (!$isClass && $item->status === ServiceSlot::STATUS_BLOCKED) {
            return '#6b7280'; // gray
        }

        // Default colors by type
        return $isClass ? '#6366f1' : '#10b981'; // indigo for classes, emerald for services
    }

    /**
     * Get event border color for conflict warning
     */
    protected function getEventBorderColor($item): string
    {
        if ($item->has_conflict ?? false) {
            return '#ef4444'; // red border for conflicts
        }

        return $this->getEventColor($item);
    }

    /**
     * Check if a class session has conflicts
     */
    protected function checkSessionConflicts(ClassSession $session): bool
    {
        // Check instructor conflict
        if ($session->primary_instructor_id) {
            $conflicts = $this->conflictChecker->hasInstructorConflict(
                $session->primary_instructor_id,
                $session->start_time,
                $session->end_time,
                $session->host_id,
                $session->id, // exclude this session
                null
            );

            if (!empty($conflicts)) {
                return true;
            }
        }

        // Check room conflict
        if ($session->room_id) {
            $conflicts = $this->conflictChecker->hasRoomConflict(
                $session->room_id,
                $session->start_time,
                $session->end_time,
                $session->host_id,
                $session->id,
                null
            );

            if (!empty($conflicts)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a service slot has conflicts
     */
    protected function checkSlotConflicts(ServiceSlot $slot): bool
    {
        // Check instructor conflict
        if ($slot->instructor_id) {
            $conflicts = $this->conflictChecker->hasInstructorConflict(
                $slot->instructor_id,
                $slot->start_time,
                $slot->end_time,
                $slot->host_id,
                null,
                $slot->id // exclude this slot
            );

            if (!empty($conflicts)) {
                return true;
            }
        }

        // Check room conflict
        if ($slot->room_id) {
            $conflicts = $this->conflictChecker->hasRoomConflict(
                $slot->room_id,
                $slot->start_time,
                $slot->end_time,
                $slot->host_id,
                null,
                $slot->id
            );

            if (!empty($conflicts)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get statistics for a date range
     */
    public function getStats(Host $host, Carbon $start, Carbon $end): array
    {
        $items = $this->getScheduleItems($host, $start, $end, ['status' => 'active']);

        $classes = $items->where('schedule_type', 'class');
        $services = $items->where('schedule_type', 'service');

        return [
            'total' => $items->count(),
            'classes' => $classes->count(),
            'services' => $services->count(),
            'with_conflicts' => $items->where('has_conflict', true)->count(),
            'published_classes' => $classes->where('status', ClassSession::STATUS_PUBLISHED)->count(),
            'draft_classes' => $classes->where('status', ClassSession::STATUS_DRAFT)->count(),
            'available_slots' => $services->where('status', ServiceSlot::STATUS_AVAILABLE)->count(),
            'booked_slots' => $services->where('status', ServiceSlot::STATUS_BOOKED)->count(),
        ];
    }

    /**
     * Get upcoming items (next N items from now)
     */
    public function getUpcoming(Host $host, int $limit = 5, array $filters = []): Collection
    {
        $filters['status'] = $filters['status'] ?? 'active';
        $items = $this->getScheduleItems($host, now(), now()->addMonths(3), $filters);

        return $items->take($limit);
    }
}
