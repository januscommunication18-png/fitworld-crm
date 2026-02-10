<?php

namespace App\Services\Schedule;

use App\Models\Instructor;
use App\Models\ClassSession;
use Carbon\Carbon;

class AvailabilityChecker
{
    /**
     * Check if instructor is available for a class session
     * Returns array of warnings (empty if no issues)
     */
    public function checkAvailability(
        Instructor $instructor,
        Carbon $startTime,
        Carbon $endTime
    ): array {
        $warnings = [];
        $dayOfWeek = (int) $startTime->dayOfWeek; // 0=Sunday, 6=Saturday

        // Check working days
        if (!$instructor->worksOnDay($dayOfWeek)) {
            $dayName = Instructor::getDayOptions()[$dayOfWeek];
            $warnings[] = [
                'type' => 'working_day',
                'message' => "{$instructor->name} doesn't normally work on {$dayName}s.",
            ];
        }

        // Check availability window
        $timeFrom = $startTime->format('H:i');
        $timeTo = $endTime->format('H:i');

        if (!$instructor->isWithinAvailability($dayOfWeek, $timeFrom, $timeTo)) {
            $availability = $instructor->getAvailabilityForDay($dayOfWeek);
            if ($availability) {
                $availWindow = "{$availability['from']} - {$availability['to']}";
                $warnings[] = [
                    'type' => 'availability_window',
                    'message' => "{$instructor->name}'s availability is {$availWindow}. This session is {$timeFrom} - {$timeTo}.",
                ];
            }
        }

        return $warnings;
    }

    /**
     * Check workload limits (weekly hours / max classes)
     */
    public function checkWorkload(
        Instructor $instructor,
        Carbon $sessionStart,
        int $durationMinutes,
        int $hostId,
        ?int $excludeSessionId = null
    ): array {
        $warnings = [];

        // Get week boundaries (Monday to Sunday)
        $weekStart = $sessionStart->copy()->startOfWeek();
        $weekEnd = $sessionStart->copy()->endOfWeek();

        // Count existing sessions this week
        $query = $instructor->primarySessions()
            ->where('host_id', $hostId)
            ->notCancelled()
            ->whereBetween('start_time', [$weekStart, $weekEnd]);

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $existingSessions = $query->get();
        $currentClassCount = $existingSessions->count();
        $currentMinutes = $existingSessions->sum('duration_minutes');

        // Check max classes per week
        if ($instructor->max_classes_per_week !== null) {
            $newTotal = $currentClassCount + 1;
            if ($newTotal > $instructor->max_classes_per_week) {
                $warnings[] = [
                    'type' => 'max_classes',
                    'message' => "{$instructor->name} has a limit of {$instructor->max_classes_per_week} classes/week. This would be class #{$newTotal}.",
                ];
            }
        }

        // Check hours per week
        if ($instructor->hours_per_week !== null) {
            $newTotalMinutes = $currentMinutes + $durationMinutes;
            $newTotalHours = $newTotalMinutes / 60;
            if ($newTotalHours > (float) $instructor->hours_per_week) {
                $hoursFormatted = number_format($newTotalHours, 1);
                $warnings[] = [
                    'type' => 'max_hours',
                    'message' => "{$instructor->name} has a limit of {$instructor->hours_per_week} hours/week. This would total {$hoursFormatted} hours.",
                ];
            }
        }

        return $warnings;
    }

    /**
     * Check all availability constraints for an instructor
     */
    public function checkAll(
        Instructor $instructor,
        Carbon $startTime,
        Carbon $endTime,
        int $hostId,
        ?int $excludeSessionId = null
    ): array {
        $availabilityWarnings = $this->checkAvailability($instructor, $startTime, $endTime);

        $durationMinutes = $startTime->diffInMinutes($endTime);
        $workloadWarnings = $this->checkWorkload(
            $instructor,
            $startTime,
            $durationMinutes,
            $hostId,
            $excludeSessionId
        );

        return array_merge($availabilityWarnings, $workloadWarnings);
    }
}
