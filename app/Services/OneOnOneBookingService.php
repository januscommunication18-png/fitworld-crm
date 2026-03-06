<?php

namespace App\Services;

use App\Models\BookingProfile;
use App\Models\Client;
use App\Models\OneOnOneBooking;
use App\Mail\OneOnOneBookingConfirmationMail;
use App\Mail\OneOnOneNewBookingMail;
use App\Mail\OneOnOneBookingCancelledMail;
use App\Mail\OneOnOneBookingRescheduledMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Exception;

class OneOnOneBookingService
{
    public function __construct(
        private OneOnOneAvailabilityService $availabilityService
    ) {}

    /**
     * Create a new 1:1 booking
     */
    public function createBooking(
        BookingProfile $profile,
        array $guestData,
        Carbon $startTime,
        int $durationMinutes,
        string $meetingType,
        string $timezone
    ): OneOnOneBooking {
        // Verify slot is still available
        if (!$this->availabilityService->isSlotAvailable($profile, $startTime, $durationMinutes)) {
            throw new Exception('This time slot is no longer available.');
        }

        // Verify meeting type is allowed
        if (!in_array($meetingType, $profile->meeting_types ?? [])) {
            throw new Exception('This meeting type is not available.');
        }

        // Verify duration is allowed
        if (!in_array($durationMinutes, $profile->allowed_durations ?? [])) {
            throw new Exception('This meeting duration is not available.');
        }

        // Find or create client
        $client = $this->findOrCreateClient($profile->host_id, $guestData);

        // Create the booking
        $booking = OneOnOneBooking::create([
            'host_id' => $profile->host_id,
            'booking_profile_id' => $profile->id,
            'client_id' => $client?->id,
            'guest_first_name' => $guestData['first_name'],
            'guest_last_name' => $guestData['last_name'],
            'guest_email' => $guestData['email'],
            'guest_phone' => $guestData['phone'] ?? null,
            'guest_notes' => $guestData['notes'] ?? null,
            'meeting_type' => $meetingType,
            'duration_minutes' => $durationMinutes,
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes($durationMinutes),
            'timezone' => $timezone,
            'status' => OneOnOneBooking::STATUS_CONFIRMED,
        ]);

        // Send confirmation emails
        $this->sendConfirmationEmails($booking);

        return $booking;
    }

    /**
     * Reschedule a booking to a new time
     */
    public function rescheduleBooking(
        OneOnOneBooking $booking,
        Carbon $newStartTime,
        int $newDuration
    ): OneOnOneBooking {
        if (!$booking->canBeRescheduled()) {
            throw new Exception('This booking cannot be rescheduled.');
        }

        $profile = $booking->bookingProfile;

        // Verify new slot is available
        if (!$this->availabilityService->isSlotAvailable($profile, $newStartTime, $newDuration)) {
            throw new Exception('The new time slot is not available.');
        }

        // Create a new booking for the new time
        $newBooking = OneOnOneBooking::create([
            'host_id' => $booking->host_id,
            'booking_profile_id' => $booking->booking_profile_id,
            'client_id' => $booking->client_id,
            'guest_first_name' => $booking->guest_first_name,
            'guest_last_name' => $booking->guest_last_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'guest_notes' => $booking->guest_notes,
            'meeting_type' => $booking->meeting_type,
            'duration_minutes' => $newDuration,
            'start_time' => $newStartTime,
            'end_time' => $newStartTime->copy()->addMinutes($newDuration),
            'timezone' => $booking->timezone,
            'status' => OneOnOneBooking::STATUS_CONFIRMED,
            'rescheduled_from_id' => $booking->id,
            'reschedule_count' => $booking->reschedule_count + 1,
        ]);

        // Cancel the original booking
        $booking->cancel(OneOnOneBooking::CANCELLED_BY_GUEST, 'Rescheduled to new time');

        // Send reschedule notification
        $this->sendRescheduleNotification($newBooking, $booking);

        return $newBooking;
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(
        OneOnOneBooking $booking,
        string $cancelledBy,
        ?string $reason = null
    ): bool {
        if ($cancelledBy === OneOnOneBooking::CANCELLED_BY_GUEST && !$booking->canBeCancelled()) {
            throw new Exception('This booking can no longer be cancelled.');
        }

        $result = $booking->cancel($cancelledBy, $reason);

        if ($result) {
            $this->sendCancellationNotification($booking);
        }

        return $result;
    }

    /**
     * Mark booking as completed
     */
    public function completeBooking(OneOnOneBooking $booking): bool
    {
        return $booking->markComplete();
    }

    /**
     * Mark booking as no-show
     */
    public function markNoShow(OneOnOneBooking $booking): bool
    {
        return $booking->markNoShow();
    }

    /**
     * Find existing client or create as lead
     */
    private function findOrCreateClient(int $hostId, array $guestData): ?Client
    {
        try {
            $client = Client::where('host_id', $hostId)
                ->where('email', $guestData['email'])
                ->first();

            if (!$client) {
                $client = Client::create([
                    'host_id' => $hostId,
                    'first_name' => $guestData['first_name'],
                    'last_name' => $guestData['last_name'],
                    'email' => $guestData['email'],
                    'phone' => $guestData['phone'] ?? null,
                    'status' => Client::STATUS_LEAD ?? 'lead',
                    'lead_source' => 'one_on_one_booking',
                ]);
            }

            return $client;
        } catch (Exception $e) {
            // If client creation fails, proceed without linking
            return null;
        }
    }

    /**
     * Send confirmation emails to guest and host
     */
    private function sendConfirmationEmails(OneOnOneBooking $booking): void
    {
        try {
            // To guest
            if (class_exists(OneOnOneBookingConfirmationMail::class)) {
                Mail::to($booking->guest_email)
                    ->queue(new OneOnOneBookingConfirmationMail($booking));
            }

            // To host/instructor
            if (class_exists(OneOnOneNewBookingMail::class)) {
                $profile = $booking->bookingProfile;
                $instructor = $profile->instructor;

                if ($instructor && $instructor->email) {
                    Mail::to($instructor->email)
                        ->queue(new OneOnOneNewBookingMail($booking));
                }
            }
        } catch (Exception $e) {
            // Log error but don't fail the booking
            \Log::error('Failed to send 1:1 booking confirmation emails: ' . $e->getMessage());
        }
    }

    /**
     * Send reschedule notification
     */
    private function sendRescheduleNotification(OneOnOneBooking $newBooking, OneOnOneBooking $oldBooking): void
    {
        try {
            if (class_exists(OneOnOneBookingRescheduledMail::class)) {
                Mail::to($newBooking->guest_email)
                    ->queue(new OneOnOneBookingRescheduledMail($newBooking, $oldBooking));
            }
        } catch (Exception $e) {
            \Log::error('Failed to send 1:1 booking reschedule notification: ' . $e->getMessage());
        }
    }

    /**
     * Send cancellation notification
     */
    private function sendCancellationNotification(OneOnOneBooking $booking): void
    {
        try {
            if (class_exists(OneOnOneBookingCancelledMail::class)) {
                Mail::to($booking->guest_email)
                    ->queue(new OneOnOneBookingCancelledMail($booking));
            }
        } catch (Exception $e) {
            \Log::error('Failed to send 1:1 booking cancellation notification: ' . $e->getMessage());
        }
    }
}
