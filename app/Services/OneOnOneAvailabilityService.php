<?php

namespace App\Services;

use App\Models\BookingProfile;
use App\Models\ClassSession;
use App\Models\OneOnOneBooking;
use App\Models\ServiceSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OneOnOneAvailabilityService
{
    /**
     * Get available time slots for a booking profile on a specific date
     */
    public function getAvailableSlots(
        BookingProfile $profile,
        Carbon $date,
        int $durationMinutes
    ): Collection {
        // Check if profile works on this day
        if (!$profile->worksOnDay($date->dayOfWeek)) {
            return collect();
        }

        // Get working hours for this day
        $hours = $profile->getAvailabilityForDay($date->dayOfWeek);
        if (!$hours) {
            return collect();
        }

        // Check min notice
        $minNoticeTime = now()->addHours($profile->min_notice_hours);

        // Check max advance days
        $maxAdvanceDate = now()->addDays($profile->max_advance_days)->endOfDay();

        if ($date->isAfter($maxAdvanceDate)) {
            return collect();
        }

        // Generate all possible slots
        $slots = $this->generateTimeSlots(
            $date,
            $hours['from'],
            $hours['to'],
            $durationMinutes,
            $profile->buffer_before,
            $profile->buffer_after
        );

        // Get blocked times
        $blockedTimes = $this->getBlockedTimes($profile, $date);

        // Remove blocked slots
        $slots = $this->removeBlockedSlots($slots, $blockedTimes, $durationMinutes);

        // Apply min notice filter
        $slots = $slots->filter(function ($slot) use ($minNoticeTime) {
            return $slot['start']->isAfter($minNoticeTime);
        });

        // Check daily max meetings limit
        if ($profile->daily_max_meetings) {
            $existingCount = $this->getBookingCountForDate($profile, $date);
            if ($existingCount >= $profile->daily_max_meetings) {
                return collect();
            }

            // Limit available slots if we're approaching the limit
            $remainingSlots = $profile->daily_max_meetings - $existingCount;
            $slots = $slots->take($remainingSlots);
        }

        // Format slots for output
        return $slots->map(function ($slot) {
            return [
                'start' => $slot['start']->format('H:i'),
                'end' => $slot['end']->format('H:i'),
                'start_datetime' => $slot['start']->toIso8601String(),
                'end_datetime' => $slot['end']->toIso8601String(),
                'formatted' => $slot['start']->format('g:i A'),
            ];
        })->values();
    }

    /**
     * Get available dates for the next N days
     */
    public function getAvailableDates(
        BookingProfile $profile,
        int $days = 30
    ): Collection {
        $availableDates = collect();
        $startDate = now()->addHours($profile->min_notice_hours)->startOfDay();
        $endDate = now()->addDays(min($days, $profile->max_advance_days))->endOfDay();

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($profile->worksOnDay($currentDate->dayOfWeek)) {
                // Check if there are any available slots
                $slots = $this->getAvailableSlots(
                    $profile,
                    $currentDate->copy(),
                    $profile->default_duration
                );

                if ($slots->isNotEmpty()) {
                    $availableDates->push([
                        'date' => $currentDate->format('Y-m-d'),
                        'formatted' => $currentDate->format('D, M j'),
                        'day_name' => $currentDate->format('l'),
                        'slot_count' => $slots->count(),
                    ]);
                }
            }

            $currentDate->addDay();
        }

        return $availableDates;
    }

    /**
     * Check if a specific time slot is available
     */
    public function isSlotAvailable(
        BookingProfile $profile,
        Carbon $startTime,
        int $durationMinutes
    ): bool {
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        // Check if it's within working hours
        $hours = $profile->getAvailabilityForDay($startTime->dayOfWeek);
        if (!$hours) {
            return false;
        }

        $dayStart = $startTime->copy()->setTimeFromTimeString($hours['from']);
        $dayEnd = $startTime->copy()->setTimeFromTimeString($hours['to']);

        if ($startTime->lt($dayStart) || $endTime->gt($dayEnd)) {
            return false;
        }

        // Check min notice
        if ($startTime->lt(now()->addHours($profile->min_notice_hours))) {
            return false;
        }

        // Check max advance days
        if ($startTime->gt(now()->addDays($profile->max_advance_days)->endOfDay())) {
            return false;
        }

        // Check for conflicts
        $blockedTimes = $this->getBlockedTimes($profile, $startTime->copy()->startOfDay());
        $bufferStart = $startTime->copy()->subMinutes($profile->buffer_before);
        $bufferEnd = $endTime->copy()->addMinutes($profile->buffer_after);

        foreach ($blockedTimes as $blocked) {
            if ($this->timesOverlap($bufferStart, $bufferEnd, $blocked['start'], $blocked['end'])) {
                return false;
            }
        }

        // Check daily max
        if ($profile->daily_max_meetings) {
            $existingCount = $this->getBookingCountForDate($profile, $startTime);
            if ($existingCount >= $profile->daily_max_meetings) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate time slots for a given day
     */
    private function generateTimeSlots(
        Carbon $date,
        string $startTime,
        string $endTime,
        int $durationMinutes,
        int $bufferBefore,
        int $bufferAfter
    ): Collection {
        $slots = collect();
        $slotInterval = 15; // Generate slots every 15 minutes

        $dayStart = $date->copy()->setTimeFromTimeString($startTime);
        $dayEnd = $date->copy()->setTimeFromTimeString($endTime);

        $current = $dayStart->copy();

        while ($current->copy()->addMinutes($durationMinutes)->lte($dayEnd)) {
            $slots->push([
                'start' => $current->copy(),
                'end' => $current->copy()->addMinutes($durationMinutes),
            ]);

            $current->addMinutes($slotInterval);
        }

        return $slots;
    }

    /**
     * Get all blocked time periods for a profile on a date
     */
    private function getBlockedTimes(BookingProfile $profile, Carbon $date): Collection
    {
        $blocked = collect();

        // 1. Existing 1:1 bookings for this profile
        $existingBookings = OneOnOneBooking::where('booking_profile_id', $profile->id)
            ->where('status', OneOnOneBooking::STATUS_CONFIRMED)
            ->whereDate('start_time', $date)
            ->get();

        foreach ($existingBookings as $booking) {
            $blocked->push([
                'start' => $booking->start_time->copy()->subMinutes($profile->buffer_before),
                'end' => $booking->end_time->copy()->addMinutes($profile->buffer_after),
                'type' => 'one_on_one',
            ]);
        }

        // 2. If profile is linked to instructor, check their schedule
        if ($profile->instructor_id) {
            // Class sessions where instructor is primary
            $classSessions = ClassSession::where('primary_instructor_id', $profile->instructor_id)
                ->where('host_id', $profile->host_id)
                ->whereDate('start_time', $date)
                ->whereIn('status', [ClassSession::STATUS_PUBLISHED, ClassSession::STATUS_COMPLETED])
                ->get();

            foreach ($classSessions as $session) {
                $blocked->push([
                    'start' => $session->start_time,
                    'end' => $session->end_time,
                    'type' => 'class_session',
                ]);
            }

            // Service slots
            if (class_exists(ServiceSlot::class)) {
                $serviceSlots = ServiceSlot::where('instructor_id', $profile->instructor_id)
                    ->where('host_id', $profile->host_id)
                    ->whereDate('start_time', $date)
                    ->where('status', 'booked')
                    ->get();

                foreach ($serviceSlots as $slot) {
                    $blocked->push([
                        'start' => $slot->start_time,
                        'end' => $slot->end_time,
                        'type' => 'service_slot',
                    ]);
                }
            }
        }

        return $blocked;
    }

    /**
     * Remove slots that conflict with blocked times
     */
    private function removeBlockedSlots(
        Collection $slots,
        Collection $blockedTimes,
        int $durationMinutes
    ): Collection {
        return $slots->filter(function ($slot) use ($blockedTimes) {
            foreach ($blockedTimes as $blocked) {
                if ($this->timesOverlap($slot['start'], $slot['end'], $blocked['start'], $blocked['end'])) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Check if two time ranges overlap
     */
    private function timesOverlap(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2): bool
    {
        return $start1->lt($end2) && $end1->gt($start2);
    }

    /**
     * Get the count of bookings for a profile on a specific date
     */
    private function getBookingCountForDate(BookingProfile $profile, Carbon $date): int
    {
        return OneOnOneBooking::where('booking_profile_id', $profile->id)
            ->where('status', OneOnOneBooking::STATUS_CONFIRMED)
            ->whereDate('start_time', $date)
            ->count();
    }
}
