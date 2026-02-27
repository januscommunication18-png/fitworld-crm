<?php

namespace App\Services\Schedule;

use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Models\Location;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SpaceRentalConflictChecker
{
    /**
     * Check if a space rental conflicts with any existing bookings
     * This includes class sessions, service slots, and other space rentals
     */
    public function hasConflict(
        int $hostId,
        ?int $locationId,
        ?int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeRentalId = null
    ): array {
        $conflicts = [];

        // If renting a specific room, check that room only
        if ($roomId) {
            $conflicts = array_merge(
                $conflicts,
                $this->checkRoomConflicts($hostId, $roomId, $startTime, $endTime, $excludeRentalId)
            );
        }
        // If renting entire location, check ALL rooms in that location
        elseif ($locationId) {
            $conflicts = array_merge(
                $conflicts,
                $this->checkLocationConflicts($hostId, $locationId, $startTime, $endTime, $excludeRentalId)
            );
        }

        return $conflicts;
    }

    /**
     * Check conflicts for a specific room
     */
    protected function checkRoomConflicts(
        int $hostId,
        int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeRentalId = null
    ): array {
        $conflicts = [];

        // Check class sessions in this room
        $sessionConflict = $this->checkClassSessionConflict($hostId, $roomId, $startTime, $endTime);
        if ($sessionConflict) {
            $conflicts['class_sessions'][] = $sessionConflict;
        }

        // Check service slots in this room
        $slotConflict = $this->checkServiceSlotConflict($hostId, $roomId, $startTime, $endTime);
        if ($slotConflict) {
            $conflicts['service_slots'][] = $slotConflict;
        }

        // Check other space rentals for this room
        $rentalConflict = $this->checkSpaceRentalConflict($hostId, null, $roomId, $startTime, $endTime, $excludeRentalId);
        if ($rentalConflict) {
            $conflicts['space_rentals'][] = $rentalConflict;
        }

        return $conflicts;
    }

    /**
     * Check conflicts for an entire location (all rooms)
     */
    protected function checkLocationConflicts(
        int $hostId,
        int $locationId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeRentalId = null
    ): array {
        $conflicts = [];

        // Get all rooms in this location
        $location = Location::with('rooms')->find($locationId);
        if (!$location) {
            return $conflicts;
        }

        // Check for any class sessions at this location
        $sessionConflicts = $this->checkLocationClassSessionConflicts($hostId, $locationId, $startTime, $endTime);
        if ($sessionConflicts->isNotEmpty()) {
            $conflicts['class_sessions'] = $sessionConflicts->all();
        }

        // Check for any service slots at this location
        $slotConflicts = $this->checkLocationServiceSlotConflicts($hostId, $locationId, $startTime, $endTime);
        if ($slotConflicts->isNotEmpty()) {
            $conflicts['service_slots'] = $slotConflicts->all();
        }

        // Check for any space rentals at this location (full location or individual rooms)
        $rentalConflict = $this->checkSpaceRentalConflict($hostId, $locationId, null, $startTime, $endTime, $excludeRentalId);
        if ($rentalConflict) {
            $conflicts['space_rentals'][] = $rentalConflict;
        }

        // Also check all individual rooms
        foreach ($location->rooms as $room) {
            $roomRentalConflict = $this->checkSpaceRentalConflict($hostId, null, $room->id, $startTime, $endTime, $excludeRentalId);
            if ($roomRentalConflict) {
                $conflicts['space_rentals'][] = $roomRentalConflict;
            }
        }

        return $conflicts;
    }

    /**
     * Check if a class session conflicts with the space rental time
     */
    public function checkClassSessionConflict(
        int $hostId,
        int $roomId,
        Carbon $startTime,
        Carbon $endTime
    ): ?ClassSession {
        return ClassSession::where('host_id', $hostId)
            ->where('room_id', $roomId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->first();
    }

    /**
     * Check if any class sessions at a location conflict
     */
    public function checkLocationClassSessionConflicts(
        int $hostId,
        int $locationId,
        Carbon $startTime,
        Carbon $endTime
    ): Collection {
        return ClassSession::where('host_id', $hostId)
            ->where('location_id', $locationId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->get();
    }

    /**
     * Check if a service slot conflicts with the space rental time
     */
    public function checkServiceSlotConflict(
        int $hostId,
        int $roomId,
        Carbon $startTime,
        Carbon $endTime
    ): ?ServiceSlot {
        return ServiceSlot::where('host_id', $hostId)
            ->where('room_id', $roomId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->first();
    }

    /**
     * Check if any service slots at a location conflict
     */
    public function checkLocationServiceSlotConflicts(
        int $hostId,
        int $locationId,
        Carbon $startTime,
        Carbon $endTime
    ): Collection {
        return ServiceSlot::where('host_id', $hostId)
            ->where('location_id', $locationId)
            ->notCancelled()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->get();
    }

    /**
     * Check if another space rental conflicts
     */
    public function checkSpaceRentalConflict(
        int $hostId,
        ?int $locationId,
        ?int $roomId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeRentalId = null
    ): ?SpaceRental {
        $query = SpaceRental::where('host_id', $hostId)
            ->whereIn('status', [
                SpaceRental::STATUS_PENDING,
                SpaceRental::STATUS_CONFIRMED,
                SpaceRental::STATUS_IN_PROGRESS,
            ])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        // Filter by config's location or room
        $query->whereHas('config', function ($q) use ($locationId, $roomId) {
            if ($roomId) {
                $q->where('room_id', $roomId);
            } elseif ($locationId) {
                $q->where(function ($inner) use ($locationId) {
                    // Match location-level rentals
                    $inner->where('rentable_type', SpaceRentalConfig::TYPE_LOCATION)
                          ->where('location_id', $locationId);
                })->orWhere(function ($inner) use ($locationId) {
                    // Or match any room-level rentals at this location
                    $inner->where('rentable_type', SpaceRentalConfig::TYPE_ROOM)
                          ->whereHas('room', fn($r) => $r->where('location_id', $locationId));
                });
            }
        });

        if ($excludeRentalId) {
            $query->where('id', '!=', $excludeRentalId);
        }

        return $query->first();
    }

    /**
     * Get available time slots for a space rental config on a given date
     */
    public function getAvailableSlots(
        SpaceRentalConfig $config,
        Carbon $date,
        int $minimumHours = null
    ): array {
        $minimumHours = $minimumHours ?? $config->minimum_hours;
        $hostId = $config->host_id;
        $locationId = $config->location_id;
        $roomId = $config->room_id;

        // Define working hours (9 AM - 9 PM by default)
        $dayStart = $date->copy()->setTime(9, 0);
        $dayEnd = $date->copy()->setTime(21, 0);

        // Get all conflicting events for the day
        $blockedPeriods = $this->getBlockedPeriods(
            $hostId,
            $config->isLocationType() ? $locationId : null,
            $config->isRoomType() ? $roomId : null,
            $dayStart,
            $dayEnd
        );

        // Sort blocked periods by start time
        usort($blockedPeriods, fn($a, $b) => $a['start']->timestamp - $b['start']->timestamp);

        // Find available slots
        $availableSlots = [];
        $currentTime = $dayStart->copy();

        foreach ($blockedPeriods as $blocked) {
            $blockedStart = $blocked['start'];
            $blockedEnd = $blocked['end'];

            // If there's time before this blocked period
            if ($currentTime->lt($blockedStart)) {
                $duration = $currentTime->diffInMinutes($blockedStart) / 60;
                if ($duration >= $minimumHours) {
                    $availableSlots[] = [
                        'start' => $currentTime->copy(),
                        'end' => $blockedStart->copy(),
                        'hours' => $duration,
                    ];
                }
            }

            // Move current time to end of blocked period
            if ($blockedEnd->gt($currentTime)) {
                $currentTime = $blockedEnd->copy();
            }
        }

        // Check remaining time after last blocked period
        if ($currentTime->lt($dayEnd)) {
            $duration = $currentTime->diffInMinutes($dayEnd) / 60;
            if ($duration >= $minimumHours) {
                $availableSlots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $dayEnd->copy(),
                    'hours' => $duration,
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * Get all blocked periods for conflict checking
     */
    protected function getBlockedPeriods(
        int $hostId,
        ?int $locationId,
        ?int $roomId,
        Carbon $startTime,
        Carbon $endTime
    ): array {
        $periods = [];

        if ($roomId) {
            // Get class sessions in this room
            $sessions = ClassSession::where('host_id', $hostId)
                ->where('room_id', $roomId)
                ->notCancelled()
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->get();

            foreach ($sessions as $session) {
                $periods[] = [
                    'start' => $session->start_time,
                    'end' => $session->end_time,
                    'type' => 'class_session',
                    'item' => $session,
                ];
            }

            // Get service slots in this room
            $slots = ServiceSlot::where('host_id', $hostId)
                ->where('room_id', $roomId)
                ->notCancelled()
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->get();

            foreach ($slots as $slot) {
                $periods[] = [
                    'start' => $slot->start_time,
                    'end' => $slot->end_time,
                    'type' => 'service_slot',
                    'item' => $slot,
                ];
            }
        }

        if ($locationId) {
            // Get all sessions at this location
            $sessions = ClassSession::where('host_id', $hostId)
                ->where('location_id', $locationId)
                ->notCancelled()
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->get();

            foreach ($sessions as $session) {
                $periods[] = [
                    'start' => $session->start_time,
                    'end' => $session->end_time,
                    'type' => 'class_session',
                    'item' => $session,
                ];
            }

            // Get all service slots at this location
            $slots = ServiceSlot::where('host_id', $hostId)
                ->where('location_id', $locationId)
                ->notCancelled()
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->get();

            foreach ($slots as $slot) {
                $periods[] = [
                    'start' => $slot->start_time,
                    'end' => $slot->end_time,
                    'type' => 'service_slot',
                    'item' => $slot,
                ];
            }
        }

        // Get existing space rentals
        $query = SpaceRental::where('host_id', $hostId)
            ->whereIn('status', [
                SpaceRental::STATUS_PENDING,
                SpaceRental::STATUS_CONFIRMED,
                SpaceRental::STATUS_IN_PROGRESS,
            ])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($roomId) {
            $query->whereHas('config', fn($c) => $c->where('room_id', $roomId));
        } elseif ($locationId) {
            $query->whereHas('config', function ($c) use ($locationId) {
                $c->where(function ($inner) use ($locationId) {
                    $inner->where('rentable_type', SpaceRentalConfig::TYPE_LOCATION)
                          ->where('location_id', $locationId);
                })->orWhere(function ($inner) use ($locationId) {
                    $inner->where('rentable_type', SpaceRentalConfig::TYPE_ROOM)
                          ->whereHas('room', fn($r) => $r->where('location_id', $locationId));
                });
            });
        }

        $rentals = $query->get();

        foreach ($rentals as $rental) {
            $periods[] = [
                'start' => $rental->start_time,
                'end' => $rental->end_time,
                'type' => 'space_rental',
                'item' => $rental,
            ];
        }

        return $periods;
    }

    /**
     * Format conflict message for display
     */
    public function formatConflictMessage(array $conflicts): string
    {
        $messages = [];

        if (!empty($conflicts['class_sessions'])) {
            foreach ($conflicts['class_sessions'] as $session) {
                $messages[] = sprintf(
                    'Class "%s" on %s (%s)',
                    $session->display_title ?? 'Untitled',
                    $session->start_time->format('M j'),
                    $session->formatted_time_range ?? $session->start_time->format('g:i A')
                );
            }
        }

        if (!empty($conflicts['service_slots'])) {
            foreach ($conflicts['service_slots'] as $slot) {
                $messages[] = sprintf(
                    'Service "%s" on %s (%s)',
                    $slot->servicePlan?->name ?? 'Service',
                    $slot->start_time->format('M j'),
                    $slot->formatted_time_range ?? $slot->start_time->format('g:i A')
                );
            }
        }

        if (!empty($conflicts['space_rentals'])) {
            foreach ($conflicts['space_rentals'] as $rental) {
                $messages[] = sprintf(
                    'Space rental #%s on %s (%s)',
                    $rental->reference_number,
                    $rental->start_time->format('M j'),
                    $rental->formatted_time_range
                );
            }
        }

        if (empty($messages)) {
            return '';
        }

        return 'Conflicts with: ' . implode(', ', $messages);
    }
}
