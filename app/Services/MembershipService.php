<?php

namespace App\Services;

use App\Events\MembershipActivated;
use App\Models\ClassPlan;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    protected AuditService $auditService;
    protected PaymentService $paymentService;
    protected ?ScheduledMembershipService $scheduledMembershipService = null;

    public function __construct(AuditService $auditService, PaymentService $paymentService)
    {
        $this->auditService = $auditService;
        $this->paymentService = $paymentService;
    }

    /**
     * Set the scheduled membership service (to avoid circular dependency)
     */
    public function setScheduledMembershipService(ScheduledMembershipService $service): void
    {
        $this->scheduledMembershipService = $service;
    }

    /**
     * Get the scheduled membership service
     */
    protected function getScheduledMembershipService(): ScheduledMembershipService
    {
        if (!$this->scheduledMembershipService) {
            $this->scheduledMembershipService = app(ScheduledMembershipService::class);
        }
        return $this->scheduledMembershipService;
    }

    /**
     * Create a manual membership (cash/check payment)
     */
    public function createManualMembership(
        Host $host,
        Client $client,
        MembershipPlan $plan,
        array $options = []
    ): CustomerMembership {
        return DB::transaction(function () use ($host, $client, $plan, $options) {
            $now = now();
            $periodEnd = $plan->interval === MembershipPlan::INTERVAL_MONTHLY
                ? $now->copy()->addMonth()
                : $now->copy()->addYear();

            $membership = CustomerMembership::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'membership_plan_id' => $plan->id,
                'status' => CustomerMembership::STATUS_ACTIVE,
                'payment_method' => CustomerMembership::PAYMENT_MANUAL,
                'credits_remaining' => $plan->isCredits() ? $plan->credits_per_cycle : null,
                'credits_per_period' => $plan->isCredits() ? $plan->credits_per_cycle : null,
                'current_period_start' => $options['start_date'] ?? $now,
                'current_period_end' => $options['end_date'] ?? $periodEnd,
                'started_at' => $now,
                'expires_at' => $options['expires_at'] ?? null,
                'created_by_user_id' => auth()->id(),
            ]);

            // Update client status
            $client->update([
                'status' => Client::STATUS_MEMBER,
                'membership_status' => Client::MEMBERSHIP_ACTIVE,
                'membership_plan_id' => $plan->id,
                'membership_start_date' => $membership->current_period_start,
                'membership_renewal_date' => $membership->current_period_end,
            ]);

            // Create payment record for the membership purchase
            if ($plan->price > 0 && isset($options['manual_method'])) {
                $this->paymentService->processManualPayment(
                    $host,
                    $client,
                    $plan->price,
                    $options['manual_method'],
                    null,
                    $membership,
                    $options['payment_notes'] ?? null
                );
            }

            $this->auditService->logMembershipCreated($membership);

            // Dispatch event for auto-enrollment into scheduled classes
            MembershipActivated::dispatch($membership);

            return $membership;
        });
    }

    /**
     * Check if a membership is eligible for a class plan
     */
    public function checkEligibility(CustomerMembership $membership, ClassPlan $classPlan): bool
    {
        // Must be active
        if (!$membership->is_active) {
            return false;
        }

        // Must not be expired
        if ($membership->is_expired) {
            return false;
        }

        // Must have credits (if credit-based)
        if (!$membership->hasAvailableCredits()) {
            return false;
        }

        // Check if membership plan covers this class
        return $membership->membershipPlan->coversClassPlan($classPlan);
    }

    /**
     * Deduct a credit from membership
     */
    public function deductCredit(CustomerMembership $membership): bool
    {
        if (!$membership->hasAvailableCredits()) {
            return false;
        }

        return $membership->deductCredit();
    }

    /**
     * Restore a credit to membership (e.g., when booking cancelled)
     */
    public function restoreCredit(CustomerMembership $membership): void
    {
        $membership->restoreCredit();
    }

    /**
     * Pause a membership
     */
    public function pauseMembership(CustomerMembership $membership, ?string $reason = null): CustomerMembership
    {
        return DB::transaction(function () use ($membership, $reason) {
            $membership->pause();

            // Update client status
            $membership->client->update([
                'membership_status' => Client::MEMBERSHIP_PAUSED,
            ]);

            // Cancel future scheduled enrollments
            if ($membership->membershipPlan?->has_scheduled_class) {
                $this->getScheduledMembershipService()->removeScheduledEnrollments(
                    $membership,
                    'Membership paused'
                );
            }

            $this->auditService->logMembershipPaused($membership, $reason);

            return $membership->fresh();
        });
    }

    /**
     * Resume a paused membership
     */
    public function resumeMembership(CustomerMembership $membership): CustomerMembership
    {
        return DB::transaction(function () use ($membership) {
            $membership->resume();

            // Update client status
            $membership->client->update([
                'membership_status' => Client::MEMBERSHIP_ACTIVE,
            ]);

            $this->auditService->logMembershipResumed($membership);

            // Dispatch event to re-enroll into scheduled classes
            MembershipActivated::dispatch($membership);

            return $membership->fresh();
        });
    }

    /**
     * Cancel a membership
     */
    public function cancelMembership(CustomerMembership $membership, ?string $reason = null): CustomerMembership
    {
        return DB::transaction(function () use ($membership, $reason) {
            $membership->cancel();

            // Update client status
            $membership->client->update([
                'status' => Client::STATUS_CLIENT,
                'membership_status' => Client::MEMBERSHIP_CANCELLED,
            ]);

            // Cancel future scheduled enrollments
            if ($membership->membershipPlan?->has_scheduled_class) {
                $this->getScheduledMembershipService()->removeScheduledEnrollments(
                    $membership,
                    $reason ?? 'Membership cancelled'
                );
            }

            $this->auditService->logMembershipCancelled($membership, $reason);

            return $membership->fresh();
        });
    }

    /**
     * Renew credits for a new billing period
     */
    public function renewCredits(CustomerMembership $membership): CustomerMembership
    {
        return DB::transaction(function () use ($membership) {
            $membership->renewCredits();

            // Update client renewal date
            $membership->client->update([
                'membership_renewal_date' => $membership->current_period_end,
            ]);

            return $membership->fresh();
        });
    }

    /**
     * Get active membership for a client
     */
    public function getActiveMembership(Client $client): ?CustomerMembership
    {
        return $client->customerMemberships()
            ->active()
            ->notExpired()
            ->with('membershipPlan')
            ->latest()
            ->first();
    }

    /**
     * Get eligible membership for a specific class plan
     */
    public function getEligibleMembershipForClass(Client $client, ClassPlan $classPlan): ?CustomerMembership
    {
        $memberships = $client->customerMemberships()
            ->active()
            ->notExpired()
            ->withCredits()
            ->with('membershipPlan.classPlans')
            ->get();

        foreach ($memberships as $membership) {
            if ($this->checkEligibility($membership, $classPlan)) {
                return $membership;
            }
        }

        return null;
    }

    /**
     * Check if client has any active membership
     */
    public function hasActiveMembership(Client $client): bool
    {
        return $client->customerMemberships()
            ->active()
            ->notExpired()
            ->exists();
    }
}
