<?php

namespace App\Services\Schedule;

use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\Room;
use App\Models\Instructor;
use Carbon\Carbon;

class ConflictChecker
{
    /**
     * Check if an instructor has a conflict for class sessions
     */
    public function checkInstructorSessionConflict(
        int $instructorId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null
    ): ?ClassSession {
        $query = ClassSession::where('host_id', $hostId)
            ->notCancelled()
            ->where(function ($q) use ($instructorId) {
                $q->where('primary_instructor_id', $instructorId)
                  ->orWhere('backup_instructor_id', $instructorId);
            })
            ->where(function ($q) use ($startTime, $endTime) {
                // Check for overlapping time ranges
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        return $query->first();
    }

    /**
     * Check if an instructor has a conflict with service slots
     */
    public function checkInstructorSlotConflict(
        int $instructorId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSlotId = null
    ): ?ServiceSlot {
        $query = ServiceSlot::where('host_id', $hostId)
            ->where('instructor_id', $instructorId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->first();
    }

    /**
     * Check for any instructor conflict (sessions + slots)
     */
    public function hasInstructorConflict(
        int $instructorId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null,
        ?int $excludeSlotId = null
    ): array {
        $conflicts = [];

        $sessionConflict = $this->checkInstructorSessionConflict(
            $instructorId,
            $startTime,
            $endTime,
            $hostId,
            $excludeSessionId
        );

        if ($sessionConflict) {
            $conflicts['session'] = $sessionConflict;
        }

        $slotConflict = $this->checkInstructorSlotConflict(
            $instructorId,
            $startTime,
            $endTime,
            $hostId,
            $excludeSlotId
        );

        if ($slotConflict) {
            $conflicts['slot'] = $slotConflict;
        }

        return $conflicts;
    }

    /**
     * Check if a room has a conflict for class sessions
     */
    public function checkRoomSessionConflict(
        int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null
    ): ?ClassSession {
        $query = ClassSession::where('host_id', $hostId)
            ->where('room_id', $roomId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        return $query->first();
    }

    /**
     * Check if a room has a conflict with service slots
     */
    public function checkRoomSlotConflict(
        int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSlotId = null
    ): ?ServiceSlot {
        $query = ServiceSlot::where('host_id', $hostId)
            ->where('room_id', $roomId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->first();
    }

    /**
     * Check for any room conflict (sessions + slots)
     */
    public function hasRoomConflict(
        int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null,
        ?int $excludeSlotId = null
    ): array {
        $conflicts = [];

        $sessionConflict = $this->checkRoomSessionConflict(
            $roomId,
            $startTime,
            $endTime,
            $hostId,
            $excludeSessionId
        );

        if ($sessionConflict) {
            $conflicts['session'] = $sessionConflict;
        }

        $slotConflict = $this->checkRoomSlotConflict(
            $roomId,
            $startTime,
            $endTime,
            $hostId,
            $excludeSlotId
        );

        if ($slotConflict) {
            $conflicts['slot'] = $slotConflict;
        }

        return $conflicts;
    }

    /**
     * Check if backup instructor has a conflict (soft warning only)
     */
    public function checkBackupConflict(
        int $backupInstructorId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null
    ): bool {
        $conflicts = $this->hasInstructorConflict(
            $backupInstructorId,
            $startTime,
            $endTime,
            $hostId,
            $excludeSessionId
        );

        return !empty($conflicts);
    }

    /**
     * Validate if room capacity meets requirements
     */
    public function validateCapacity(int $roomId, int $requestedCapacity): bool
    {
        $room = Room::find($roomId);

        if (!$room) {
            return false;
        }

        return $room->capacity >= $requestedCapacity;
    }

    /**
     * Get room capacity
     */
    public function getRoomCapacity(int $roomId): ?int
    {
        $room = Room::find($roomId);
        return $room?->capacity;
    }

    /**
     * Format conflict message
     */
    public function formatConflictMessage(array $conflicts, string $type = 'instructor'): string
    {
        $messages = [];

        if (isset($conflicts['session'])) {
            $session = $conflicts['session'];
            $messages[] = sprintf(
                'Class session "%s" on %s (%s)',
                $session->display_title,
                $session->start_time->format('M j'),
                $session->formatted_time_range
            );
        }

        if (isset($conflicts['slot'])) {
            $slot = $conflicts['slot'];
            $messages[] = sprintf(
                'Service slot for "%s" on %s (%s)',
                $slot->servicePlan->name,
                $slot->start_time->format('M j'),
                $slot->formatted_time_range
            );
        }

        if (empty($messages)) {
            return '';
        }

        $prefix = $type === 'instructor'
            ? 'Instructor has conflicting schedule: '
            : 'Room is already booked: ';

        return $prefix . implode(', ', $messages);
    }

    /**
     * Check instructor availability (working days + time windows + workload)
     * Returns warnings (soft errors that can be overridden)
     */
    public function checkInstructorAvailability(
        int $instructorId,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null
    ): array {
        $instructor = Instructor::find($instructorId);
        if (!$instructor) {
            return [];
        }

        $availabilityChecker = app(AvailabilityChecker::class);

        return $availabilityChecker->checkAll(
            $instructor,
            $startTime,
            $endTime,
            $hostId,
            $excludeSessionId
        );
    }
}
