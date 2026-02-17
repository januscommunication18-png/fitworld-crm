<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\ClassPackPurchase;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\Payment;
use App\Models\ServiceSlot;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\QuestionnaireVersion;
use App\Mail\BookingConfirmationMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingService
{
    protected AuditService $auditService;
    protected PaymentService $paymentService;
    protected MembershipService $membershipService;
    protected ClassPackService $classPackService;

    public function __construct(
        AuditService $auditService,
        PaymentService $paymentService,
        MembershipService $membershipService,
        ClassPackService $classPackService
    ) {
        $this->auditService = $auditService;
        $this->paymentService = $paymentService;
        $this->membershipService = $membershipService;
        $this->classPackService = $classPackService;
    }

    /**
     * Create a walk-in booking for a class session
     */
    public function createWalkInClassBooking(
        Host $host,
        Client $client,
        ClassSession $session,
        array $options = []
    ): Booking {
        return DB::transaction(function () use ($host, $client, $session, $options) {
            // Validate capacity
            $capacityCheck = $this->validateCapacity($session);
            if (!$capacityCheck['available'] && !($options['capacity_override'] ?? false)) {
                throw new \Exception('Class is at capacity. Use capacity_override option to override.');
            }

            // Check for duplicate booking
            if ($this->hasExistingBooking($client, $session)) {
                throw new \Exception('Client already has a booking for this class.');
            }

            // Create booking
            $booking = Booking::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'bookable_type' => ClassSession::class,
                'bookable_id' => $session->id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                'intake_status' => $options['intake_status'] ?? Booking::INTAKE_NOT_REQUIRED,
                'intake_waived_by' => $options['intake_waived_by'] ?? null,
                'intake_waived_reason' => $options['intake_waived_reason'] ?? null,
                'capacity_override' => $options['capacity_override'] ?? false,
                'capacity_override_reason' => $options['capacity_override_reason'] ?? null,
                'created_by_user_id' => auth()->id(),
                'payment_method' => $options['payment_method'] ?? null,
                'price_paid' => $options['price_paid'] ?? 0,
                'booked_at' => now(),
                'checked_in_at' => ($options['check_in_now'] ?? false) ? now() : null,
                'checked_in_by_user_id' => ($options['check_in_now'] ?? false) ? auth()->id() : null,
                'checked_in_method' => ($options['check_in_now'] ?? false) ? Booking::CHECKIN_STAFF : null,
            ]);

            // Process payment based on method
            $this->processBookingPayment($booking, $host, $client, $session->classPlan, $options);

            // Log capacity override if applicable
            if ($booking->capacity_override) {
                $this->auditService->logCapacityOverride($booking, $booking->capacity_override_reason ?? 'Walk-in override');
            }

            // Log intake waiver if applicable
            if ($booking->intake_status === Booking::INTAKE_WAIVED) {
                $this->auditService->logIntakeWaive($booking, $booking->intake_waived_reason ?? 'Walk-in waiver');
            }

            // Log booking creation
            $this->auditService->logBookingCreated($booking, [
                'class_session_id' => $session->id,
                'class_plan_id' => $session->class_plan_id,
            ]);

            // Update client visit stats
            $client->increment('total_classes_attended');
            $client->update(['last_visit_at' => now()]);

            // Handle intake form / questionnaire responses
            $questionnaireResponses = [];
            if (($options['send_intake_form'] ?? false) && !empty($options['questionnaire_ids'])) {
                $questionnaireResponses = $this->createQuestionnaireResponses(
                    $host,
                    $client,
                    $booking,
                    $options['questionnaire_ids']
                );

                // Update booking intake status if questionnaires were sent
                if (count($questionnaireResponses) > 0) {
                    $booking->update(['intake_status' => Booking::INTAKE_PENDING]);
                }
            }

            // Send booking confirmation email if client has email
            if ($client->email && ($options['send_confirmation_email'] ?? true)) {
                $this->sendBookingConfirmationEmail($booking, $questionnaireResponses);
            }

            return $booking->load(['client', 'bookable', 'payments']);
        });
    }

    /**
     * Create questionnaire responses for a booking
     *
     * @param Host $host
     * @param Client $client
     * @param Booking $booking
     * @param array $questionnaireIds
     * @return array<QuestionnaireResponse>
     */
    protected function createQuestionnaireResponses(
        Host $host,
        Client $client,
        Booking $booking,
        array $questionnaireIds
    ): array {
        $responses = [];

        foreach ($questionnaireIds as $questionnaireId) {
            $questionnaire = Questionnaire::where('host_id', $host->id)
                ->find($questionnaireId);

            if (!$questionnaire) {
                continue;
            }

            // Get the active version
            $version = $questionnaire->activeVersion;
            if (!$version) {
                continue;
            }

            // Create response record
            $response = QuestionnaireResponse::create([
                'questionnaire_version_id' => $version->id,
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking->id,
                'status' => QuestionnaireResponse::STATUS_PENDING,
                'current_step' => 1,
            ]);

            $responses[] = $response;
        }

        return $responses;
    }

    /**
     * Send booking confirmation email
     *
     * @param Booking $booking
     * @param array<QuestionnaireResponse> $questionnaireResponses
     */
    protected function sendBookingConfirmationEmail(Booking $booking, array $questionnaireResponses = []): void
    {
        $client = $booking->client;

        if (!$client || !$client->email) {
            return;
        }

        // Load relationships for the email (keep as models, not arrays)
        $responses = collect($questionnaireResponses)->map(function ($response) {
            return $response->load('version.questionnaire');
        })->all();

        try {
            Mail::to($client->email)
                ->send(new BookingConfirmationMail($booking, $responses));
        } catch (\Exception $e) {
            \Log::error('Failed to send booking confirmation email', [
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a walk-in booking for a service slot
     */
    public function createWalkInServiceBooking(
        Host $host,
        Client $client,
        ServiceSlot $slot,
        array $options = []
    ): Booking {
        return DB::transaction(function () use ($host, $client, $slot, $options) {
            // Check if slot is available
            if (!$slot->isAvailable()) {
                throw new \Exception('Service slot is not available.');
            }

            // Check for duplicate booking
            if ($this->hasExistingBooking($client, $slot)) {
                throw new \Exception('Client already has a booking for this service slot.');
            }

            // Create booking
            $booking = Booking::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'bookable_type' => ServiceSlot::class,
                'bookable_id' => $slot->id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                'intake_status' => $options['intake_status'] ?? Booking::INTAKE_NOT_REQUIRED,
                'intake_waived_by' => $options['intake_waived_by'] ?? null,
                'intake_waived_reason' => $options['intake_waived_reason'] ?? null,
                'capacity_override' => false,
                'created_by_user_id' => auth()->id(),
                'payment_method' => $options['payment_method'] ?? null,
                'price_paid' => $options['price_paid'] ?? $slot->servicePlan->price ?? 0,
                'booked_at' => now(),
                'checked_in_at' => ($options['check_in_now'] ?? false) ? now() : null,
                'checked_in_by_user_id' => ($options['check_in_now'] ?? false) ? auth()->id() : null,
                'checked_in_method' => ($options['check_in_now'] ?? false) ? Booking::CHECKIN_STAFF : null,
            ]);

            // Process payment
            $this->processServiceBookingPayment($booking, $host, $client, $slot, $options);

            // Log intake waiver if applicable
            if ($booking->intake_status === Booking::INTAKE_WAIVED) {
                $this->auditService->logIntakeWaive($booking, $booking->intake_waived_reason ?? 'Walk-in waiver');
            }

            // Log booking creation
            $this->auditService->logBookingCreated($booking, [
                'service_slot_id' => $slot->id,
                'service_plan_id' => $slot->service_plan_id,
            ]);

            // Update client visit stats
            $client->increment('total_services_booked');
            $client->update(['last_visit_at' => now()]);

            // Mark slot as booked
            $slot->update(['status' => 'booked']);

            return $booking->load(['client', 'bookable', 'payments']);
        });
    }

    /**
     * Create an online booking
     */
    public function createOnlineBooking(
        Host $host,
        Client $client,
        Model $bookable,
        array $options = []
    ): Booking {
        return DB::transaction(function () use ($host, $client, $bookable, $options) {
            $booking = Booking::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'bookable_type' => get_class($bookable),
                'bookable_id' => $bookable->id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_ONLINE,
                'intake_status' => $options['intake_status'] ?? Booking::INTAKE_NOT_REQUIRED,
                'payment_method' => $options['payment_method'] ?? null,
                'price_paid' => $options['price_paid'] ?? 0,
                'booked_at' => now(),
            ]);

            $this->auditService->logBookingCreated($booking);

            return $booking;
        });
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Restore credits if applicable
            if ($booking->customer_membership_id) {
                $membership = $booking->customerMembership;
                if ($membership) {
                    $this->membershipService->restoreCredit($membership);
                }
            }

            if ($booking->class_pack_purchase_id) {
                $packPurchase = $booking->classPackPurchase;
                if ($packPurchase) {
                    $this->classPackService->restoreCredit($packPurchase, $booking);
                }
            }

            $this->auditService->logBookingCancelled($booking, $reason);

            return $booking->fresh();
        });
    }

    /**
     * Check in a client
     */
    public function checkIn(Booking $booking, ?int $checkedInByUserId = null, string $method = Booking::CHECKIN_STAFF): Booking
    {
        return DB::transaction(function () use ($booking, $checkedInByUserId, $method) {
            $booking->update([
                'checked_in_at' => now(),
                'checked_in_by_user_id' => $checkedInByUserId ?? auth()->id(),
                'checked_in_method' => $method,
            ]);

            $this->auditService->logBookingCheckedIn($booking);

            return $booking->fresh();
        });
    }

    /**
     * Validate capacity for a class session
     */
    public function validateCapacity(ClassSession $session): array
    {
        $capacity = $session->capacity ?? $session->classPlan->default_capacity ?? 999;
        $bookedCount = $session->bookings()->confirmed()->count();
        $spotsRemaining = $capacity - $bookedCount;

        return [
            'capacity' => $capacity,
            'booked' => $bookedCount,
            'remaining' => max(0, $spotsRemaining),
            'available' => $spotsRemaining > 0,
        ];
    }

    /**
     * Override capacity for a booking
     */
    public function overrideCapacity(Booking $booking, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'capacity_override' => true,
                'capacity_override_reason' => $reason,
            ]);

            $this->auditService->logCapacityOverride($booking, $reason);

            return $booking->fresh();
        });
    }

    /**
     * Check if client has existing booking for the bookable
     */
    public function hasExistingBooking(Client $client, Model $bookable): bool
    {
        return Booking::where('client_id', $client->id)
            ->where('bookable_type', get_class($bookable))
            ->where('bookable_id', $bookable->id)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->exists();
    }

    /**
     * Process payment for a class booking
     */
    protected function processBookingPayment(
        Booking $booking,
        Host $host,
        Client $client,
        ClassPlan $classPlan,
        array $options
    ): void {
        $paymentMethod = $options['payment_method'] ?? null;

        switch ($paymentMethod) {
            case Booking::PAYMENT_MEMBERSHIP:
                $membershipId = $options['customer_membership_id'] ?? null;
                $membership = $membershipId
                    ? CustomerMembership::find($membershipId)
                    : $this->membershipService->getEligibleMembershipForClass($client, $classPlan);

                if ($membership && $this->membershipService->deductCredit($membership)) {
                    $booking->update(['customer_membership_id' => $membership->id]);
                    $this->paymentService->processMembershipPayment($host, $client, $membership, $booking);
                } else {
                    throw new \Exception('No eligible membership credits available.');
                }
                break;

            case Booking::PAYMENT_PACK:
                $packId = $options['class_pack_purchase_id'] ?? null;
                $packPurchase = $packId
                    ? ClassPackPurchase::find($packId)
                    : $this->classPackService->getEligiblePackForClass($client, $classPlan);

                if ($packPurchase && $this->classPackService->deductCredit($packPurchase, $booking)) {
                    $booking->update(['class_pack_purchase_id' => $packPurchase->id]);
                    $this->paymentService->processPackPayment($host, $client, $packPurchase, $booking);
                } else {
                    throw new \Exception('No eligible pack credits available.');
                }
                break;

            case Booking::PAYMENT_MANUAL:
            case Booking::PAYMENT_CASH:
                $amount = $options['price_paid'] ?? $classPlan->drop_in_price ?? 0;
                $manualMethod = $options['manual_method'] ?? Payment::MANUAL_CASH;
                $this->paymentService->processManualPayment(
                    $host,
                    $client,
                    $amount,
                    $manualMethod,
                    $booking,
                    null,
                    $options['payment_notes'] ?? null
                );
                break;

            case Booking::PAYMENT_COMP:
                $this->paymentService->processCompPayment(
                    $host,
                    $client,
                    $booking,
                    $options['payment_notes'] ?? 'Complimentary walk-in'
                );
                break;

            case Booking::PAYMENT_STRIPE:
                // Stripe is handled separately via webhooks
                break;
        }
    }

    /**
     * Process payment for a service booking
     */
    protected function processServiceBookingPayment(
        Booking $booking,
        Host $host,
        Client $client,
        ServiceSlot $slot,
        array $options
    ): void {
        $paymentMethod = $options['payment_method'] ?? null;
        $amount = $options['price_paid'] ?? $slot->servicePlan->price ?? 0;

        switch ($paymentMethod) {
            case Booking::PAYMENT_MANUAL:
            case Booking::PAYMENT_CASH:
                $manualMethod = $options['manual_method'] ?? Payment::MANUAL_CASH;
                $this->paymentService->processManualPayment(
                    $host,
                    $client,
                    $amount,
                    $manualMethod,
                    $booking,
                    null,
                    $options['payment_notes'] ?? null
                );
                break;

            case Booking::PAYMENT_COMP:
                $this->paymentService->processCompPayment(
                    $host,
                    $client,
                    $booking,
                    $options['payment_notes'] ?? 'Complimentary service'
                );
                break;

            case Booking::PAYMENT_STRIPE:
                // Stripe is handled separately
                break;
        }
    }

    /**
     * Get today's bookings for a host
     */
    public function getTodaysBookings(Host $host): \Illuminate\Database\Eloquent\Collection
    {
        return Booking::forHost($host->id)
            ->with(['client', 'bookable'])
            ->whereDate('booked_at', today())
            ->confirmed()
            ->orderBy('booked_at')
            ->get();
    }

    /**
     * Get booking statistics for a host
     */
    public function getBookingStats(Host $host, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = Booking::forHost($host->id);

        if ($startDate) {
            $query->where('booked_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('booked_at', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'online' => (clone $query)->online()->count(),
            'walk_in' => (clone $query)->walkIn()->count(),
            'confirmed' => (clone $query)->confirmed()->count(),
            'cancelled' => (clone $query)->cancelled()->count(),
            'no_show' => (clone $query)->noShow()->count(),
            'completed' => (clone $query)->completed()->count(),
        ];
    }
}
