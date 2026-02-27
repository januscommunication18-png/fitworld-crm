<?php

namespace App\Services;

use App\Models\Host;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Models\User;
use App\Services\Schedule\SpaceRentalConflictChecker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpaceRentalService
{
    public function __construct(
        protected SpaceRentalConflictChecker $conflictChecker,
        protected TaxService $taxService
    ) {}

    /**
     * Create a new space rental
     */
    public function createRental(array $data, User $createdBy): SpaceRental
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $config = SpaceRentalConfig::findOrFail($data['space_rental_config_id']);
            $host = $config->host;

            // Calculate pricing
            $pricing = $this->calculatePricing(
                $config,
                (float) $data['hours_booked'],
                $config->location,
                $host->default_currency
            );

            // Determine deposit status
            $depositAmount = $config->getDepositForCurrency($host->default_currency) ?? 0;
            $depositStatus = $depositAmount > 0
                ? SpaceRental::DEPOSIT_PENDING
                : SpaceRental::DEPOSIT_NOT_REQUIRED;

            // Create the rental
            $rental = SpaceRental::create([
                'host_id' => $host->id,
                'space_rental_config_id' => $config->id,
                'client_id' => $data['client_id'] ?? null,
                'external_client_name' => $data['external_client_name'] ?? null,
                'external_client_email' => $data['external_client_email'] ?? null,
                'external_client_phone' => $data['external_client_phone'] ?? null,
                'external_client_company' => $data['external_client_company'] ?? null,
                'purpose' => $data['purpose'],
                'purpose_notes' => $data['purpose_notes'] ?? null,
                'start_time' => Carbon::parse($data['start_time']),
                'end_time' => Carbon::parse($data['end_time']),
                'hourly_rate' => $pricing['hourly_rate'],
                'hours_booked' => $data['hours_booked'],
                'subtotal' => $pricing['subtotal'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total'],
                'deposit_amount' => $depositAmount,
                'currency' => $host->default_currency,
                'status' => $data['status'] ?? SpaceRental::STATUS_DRAFT,
                'deposit_status' => $depositStatus,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by_user_id' => $createdBy->id,
            ]);

            return $rental->fresh(['config', 'client', 'createdBy']);
        });
    }

    /**
     * Update an existing space rental
     */
    public function updateRental(SpaceRental $rental, array $data, User $updatedBy): SpaceRental
    {
        return DB::transaction(function () use ($rental, $data, $updatedBy) {
            $config = $rental->config;
            $host = $rental->host;

            // If hours changed, recalculate pricing
            if (isset($data['hours_booked']) && $data['hours_booked'] != $rental->hours_booked) {
                $pricing = $this->calculatePricing(
                    $config,
                    (float) $data['hours_booked'],
                    $config->location,
                    $rental->currency
                );

                $data['hourly_rate'] = $pricing['hourly_rate'];
                $data['subtotal'] = $pricing['subtotal'];
                $data['tax_amount'] = $pricing['tax_amount'];
                $data['total_amount'] = $pricing['total'];
            }

            // Parse datetime fields if provided
            if (isset($data['start_time'])) {
                $data['start_time'] = Carbon::parse($data['start_time']);
            }
            if (isset($data['end_time'])) {
                $data['end_time'] = Carbon::parse($data['end_time']);
            }

            $rental->update($data);

            return $rental->fresh(['config', 'client']);
        });
    }

    /**
     * Calculate pricing for a space rental
     */
    public function calculatePricing(
        SpaceRentalConfig $config,
        float $hours,
        $location = null,
        ?string $currency = null
    ): array {
        $currency = $currency ?? $config->host?->default_currency ?? 'USD';
        $hourlyRate = $config->getHourlyRateForCurrency($currency) ?? 0;

        $subtotal = $hourlyRate * $hours;

        // Calculate tax using TaxService
        $taxCalculation = $this->taxService->calculateTax(
            $config->host,
            $subtotal,
            'space_rental', // Service type
            $location ?? $config->location
        );

        return [
            'hourly_rate' => $hourlyRate,
            'hours' => $hours,
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxCalculation->totalTax, 2),
            'total' => round($taxCalculation->total, 2),
            'currency' => $currency,
            'tax_components' => $taxCalculation->components,
        ];
    }

    /**
     * Confirm a rental
     */
    public function confirmRental(SpaceRental $rental, User $confirmedBy): SpaceRental
    {
        if (!$rental->canBeConfirmed()) {
            throw new \Exception('This rental cannot be confirmed in its current state.');
        }

        $rental->confirm($confirmedBy);

        return $rental->fresh();
    }

    /**
     * Start a rental (mark as in progress)
     */
    public function startRental(SpaceRental $rental, User $user): SpaceRental
    {
        if (!$rental->canBeStarted()) {
            throw new \Exception('This rental cannot be started. It must be confirmed first.');
        }

        // Check if waiver is required and signed
        if ($rental->isWaiverPending()) {
            throw new \Exception('Waiver must be signed before starting the rental.');
        }

        // Check if deposit is required and paid
        if ($rental->isDepositPending()) {
            throw new \Exception('Deposit must be paid before starting the rental.');
        }

        $rental->startRental($user);

        return $rental->fresh();
    }

    /**
     * Complete a rental
     */
    public function completeRental(
        SpaceRental $rental,
        User $completedBy,
        bool $hasDamage = false,
        ?string $damageNotes = null,
        float $damageCharge = 0
    ): SpaceRental {
        if (!$rental->canBeCompleted()) {
            throw new \Exception('This rental cannot be completed. It must be in progress first.');
        }

        $rental->complete($completedBy, $hasDamage, $damageNotes, $damageCharge);

        return $rental->fresh();
    }

    /**
     * Cancel a rental
     */
    public function cancelRental(SpaceRental $rental, User $cancelledBy, ?string $reason = null): SpaceRental
    {
        if (!$rental->canBeCancelled()) {
            throw new \Exception('This rental cannot be cancelled in its current state.');
        }

        $rental->cancel($cancelledBy, $reason);

        return $rental->fresh();
    }

    /**
     * Process deposit payment
     */
    public function recordDepositPayment(SpaceRental $rental): SpaceRental
    {
        if (!$rental->requiresDeposit()) {
            throw new \Exception('This rental does not require a deposit.');
        }

        $rental->recordDepositPayment();

        return $rental->fresh();
    }

    /**
     * Process deposit refund
     */
    public function processDepositRefund(
        SpaceRental $rental,
        float $refundAmount,
        ?string $reason = null
    ): SpaceRental {
        if ($rental->deposit_status !== SpaceRental::DEPOSIT_PAID) {
            throw new \Exception('Cannot refund a deposit that has not been paid.');
        }

        if ($refundAmount > $rental->deposit_amount) {
            throw new \Exception('Refund amount cannot exceed the deposit amount.');
        }

        $rental->refundDeposit($refundAmount, $reason);

        return $rental->fresh();
    }

    /**
     * Forfeit deposit (typically due to damage)
     */
    public function forfeitDeposit(SpaceRental $rental, ?string $reason = null): SpaceRental
    {
        if ($rental->deposit_status !== SpaceRental::DEPOSIT_PAID) {
            throw new \Exception('Cannot forfeit a deposit that has not been paid.');
        }

        $rental->forfeitDeposit($reason);

        return $rental->fresh();
    }

    /**
     * Record waiver signature
     */
    public function signWaiver(SpaceRental $rental, string $signerName, ?string $ip = null): SpaceRental
    {
        if (!$rental->requiresWaiver()) {
            throw new \Exception('This rental does not require a waiver.');
        }

        $rental->markWaiverSigned($signerName, $ip);

        return $rental->fresh();
    }

    /**
     * Get upcoming rentals for a host
     */
    public function getUpcomingRentals(Host $host, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $startDate = $startDate ?? now();
        $endDate = $endDate ?? now()->addDays(30);

        return SpaceRental::where('host_id', $host->id)
            ->whereIn('status', [
                SpaceRental::STATUS_CONFIRMED,
                SpaceRental::STATUS_PENDING,
            ])
            ->where('start_time', '>=', $startDate)
            ->where('start_time', '<=', $endDate)
            ->orderBy('start_time')
            ->with(['config', 'client'])
            ->get();
    }

    /**
     * Get rentals needing attention (waiver or deposit pending)
     */
    public function getRentalsNeedingAttention(Host $host)
    {
        return SpaceRental::where('host_id', $host->id)
            ->needsAttention()
            ->orderBy('start_time')
            ->with(['config', 'client'])
            ->get();
    }

    /**
     * Check for conflicts before creating/updating a rental
     */
    public function checkConflicts(
        SpaceRentalConfig $config,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeRentalId = null
    ): array {
        return $this->conflictChecker->hasConflict(
            $config->host_id,
            $config->isLocationType() ? $config->location_id : null,
            $config->isRoomType() ? $config->room_id : null,
            $startTime,
            $endTime,
            $excludeRentalId
        );
    }

    /**
     * Get available time slots for a config on a date
     */
    public function getAvailableSlots(SpaceRentalConfig $config, Carbon $date): array
    {
        return $this->conflictChecker->getAvailableSlots($config, $date);
    }
}
