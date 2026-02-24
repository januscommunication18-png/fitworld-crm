<?php

namespace App\Services\Schedule;

use App\Models\ClassSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurrenceService
{
    /**
     * Generate occurrence dates from a simple weekly recurrence rule
     */
    public function generateOccurrences(
        Carbon $startDate,
        array $daysOfWeek,
        string $endType,
        int|Carbon|null $endValue = null,
        int $maxOccurrences = 52
    ): Collection {
        $occurrences = collect();
        $current = $startDate->copy();
        $count = 0;

        // Determine end condition
        $endDate = null;
        $occurrenceLimit = $maxOccurrences;

        if ($endType === 'after' && is_int($endValue)) {
            $occurrenceLimit = $endValue;
        } elseif ($endType === 'on' && $endValue instanceof Carbon) {
            $endDate = $endValue;
        }

        // Map day names to Carbon day numbers and names
        $dayNameToNumber = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $dayNumberToName = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        // Generate up to a year of occurrences
        $yearFromNow = $startDate->copy()->addYear();

        while ($count < $occurrenceLimit) {
            foreach ($daysOfWeek as $day) {
                // Convert day to number (supports numeric, string numbers, and day names)
                if (is_numeric($day)) {
                    $dayNumber = (int) $day;
                } else {
                    $dayNumber = $dayNameToNumber[strtolower($day)] ?? false;
                }

                if ($dayNumber === false) {
                    continue;
                }

                $occurrence = $current->copy();

                // Adjust to the target day of week
                if ($occurrence->dayOfWeek !== $dayNumber) {
                    $occurrence = $occurrence->next($dayNumberToName[$dayNumber]);
                }

                // Skip if before start date
                if ($occurrence->lt($startDate)) {
                    continue;
                }

                // Check end conditions
                if ($endDate && $occurrence->gt($endDate)) {
                    return $occurrences;
                }

                if ($occurrence->gt($yearFromNow)) {
                    return $occurrences;
                }

                if ($count >= $occurrenceLimit) {
                    return $occurrences;
                }

                $occurrences->push($occurrence->copy());
                $count++;
            }

            // Move to next week
            $current->addWeek();

            // Safety check
            if ($current->gt($yearFromNow)) {
                break;
            }
        }

        return $occurrences->unique(fn ($date) => $date->format('Y-m-d'))->sortBy(fn ($date) => $date);
    }

    /**
     * Create recurring sessions from a parent session
     */
    public function createRecurringSessions(
        ClassSession $parentSession,
        array $daysOfWeek,
        string $endType,
        int|Carbon|null $endValue = null
    ): Collection {
        \Log::info('createRecurringSessions called', [
            'parent_start_time' => $parentSession->start_time,
            'daysOfWeek' => $daysOfWeek,
            'endType' => $endType,
            'endValue' => $endValue,
        ]);

        $occurrences = $this->generateOccurrences(
            $parentSession->start_time,
            $daysOfWeek,
            $endType,
            $endValue
        );

        \Log::info('Occurrences generated', [
            'count' => $occurrences->count(),
            'dates' => $occurrences->map(fn($d) => $d->format('Y-m-d'))->toArray(),
        ]);

        $sessions = collect();

        // Get backup instructors from parent to copy
        $backupInstructorIds = $parentSession->backupInstructors->pluck('id')->toArray();

        // Skip the first occurrence (it's the parent)
        foreach ($occurrences->skip(1) as $date) {
            $startTime = $date->copy()->setTimeFrom($parentSession->start_time);
            $endTime = $startTime->copy()->addMinutes((int) $parentSession->duration_minutes);

            $session = ClassSession::create([
                'host_id' => $parentSession->host_id,
                'class_plan_id' => $parentSession->class_plan_id,
                'primary_instructor_id' => $parentSession->primary_instructor_id,
                'location_id' => $parentSession->location_id,
                'room_id' => $parentSession->room_id,
                'title' => $parentSession->title,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $parentSession->duration_minutes,
                'capacity' => $parentSession->capacity,
                'price' => $parentSession->price,
                'status' => $parentSession->status,
                'recurrence_parent_id' => $parentSession->id,
                'notes' => $parentSession->notes,
            ]);

            // Copy backup instructors
            if (!empty($backupInstructorIds)) {
                $session->syncBackupInstructors($backupInstructorIds);
            }

            $sessions->push($session);
        }

        return $sessions;
    }

    /**
     * Update series from a specific session onwards
     */
    public function updateSeriesFromDate(ClassSession $session, array $data): int
    {
        $parentId = $session->recurrence_parent_id ?? $session->id;

        $updated = ClassSession::where(function ($query) use ($parentId, $session) {
            $query->where('recurrence_parent_id', $parentId)
                  ->orWhere('id', $parentId);
        })
        ->where('start_time', '>=', $session->start_time)
        ->notCancelled()
        ->update($data);

        return $updated;
    }

    /**
     * Delete series from a specific session onwards
     */
    public function deleteSeriesFromDate(ClassSession $session): int
    {
        $parentId = $session->recurrence_parent_id ?? $session->id;

        $deleted = ClassSession::where(function ($query) use ($parentId, $session) {
            $query->where('recurrence_parent_id', $parentId)
                  ->orWhere('id', $parentId);
        })
        ->where('start_time', '>=', $session->start_time)
        ->where('status', '!=', ClassSession::STATUS_PUBLISHED)
        ->delete();

        return $deleted;
    }

    /**
     * Cancel series from a specific session onwards
     */
    public function cancelSeriesFromDate(ClassSession $session, ?string $reason = null): int
    {
        $parentId = $session->recurrence_parent_id ?? $session->id;

        $cancelled = ClassSession::where(function ($query) use ($parentId, $session) {
            $query->where('recurrence_parent_id', $parentId)
                  ->orWhere('id', $parentId);
        })
        ->where('start_time', '>=', $session->start_time)
        ->notCancelled()
        ->update([
            'status' => ClassSession::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $cancelled;
    }

    /**
     * Build recurrence rule string for storage
     */
    public function buildRecurrenceRule(
        array $daysOfWeek,
        string $endType,
        int|Carbon|null $endValue = null
    ): string {
        // Map day names to iCal day codes
        $dayMap = [
            'sunday' => 'SU',
            'monday' => 'MO',
            'tuesday' => 'TU',
            'wednesday' => 'WE',
            'thursday' => 'TH',
            'friday' => 'FR',
            'saturday' => 'SA',
            0 => 'SU',
            1 => 'MO',
            2 => 'TU',
            3 => 'WE',
            4 => 'TH',
            5 => 'FR',
            6 => 'SA',
        ];
        $byDays = array_map(function ($day) use ($dayMap) {
            // Convert string numbers to integers for proper lookup
            $key = is_numeric($day) ? (int) $day : strtolower($day);
            return $dayMap[$key] ?? 'MO';
        }, $daysOfWeek);

        $rule = 'FREQ=WEEKLY;BYDAY=' . implode(',', $byDays);

        if ($endType === 'after' && is_int($endValue)) {
            $rule .= ';COUNT=' . $endValue;
        } elseif ($endType === 'on' && $endValue instanceof Carbon) {
            $rule .= ';UNTIL=' . $endValue->format('Ymd\THis\Z');
        }

        return $rule;
    }

    /**
     * Parse recurrence rule string
     */
    public function parseRecurrenceRule(string $rule): array
    {
        $parts = explode(';', $rule);
        $result = [
            'frequency' => 'weekly',
            'days_of_week' => [],
            'end_type' => 'never',
            'end_value' => null,
        ];

        $dayMap = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];

        foreach ($parts as $part) {
            if (str_starts_with($part, 'BYDAY=')) {
                $days = explode(',', substr($part, 6));
                $result['days_of_week'] = array_map(fn ($d) => $dayMap[$d] ?? 0, $days);
            } elseif (str_starts_with($part, 'COUNT=')) {
                $result['end_type'] = 'after';
                $result['end_value'] = (int) substr($part, 6);
            } elseif (str_starts_with($part, 'UNTIL=')) {
                $result['end_type'] = 'on';
                $result['end_value'] = Carbon::parse(substr($part, 6));
            }
        }

        return $result;
    }
}
