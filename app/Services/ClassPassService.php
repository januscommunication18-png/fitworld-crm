<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassPass;
use App\Models\ClassPassPurchase;
use App\Models\ClassPassCreditLog;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\Host;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassPassService
{
    protected AuditService $auditService;
    protected PaymentService $paymentService;

    public function __construct(AuditService $auditService, PaymentService $paymentService)
    {
        $this->auditService = $auditService;
        $this->paymentService = $paymentService;
    }

    // ===========================================
    // PURCHASE METHODS
    // ===========================================

    /**
     * Purchase a class pass
     */
    public function purchasePass(
        Host $host,
        Client $client,
        ClassPass $pass,
        array $options = []
    ): ClassPassPurchase {
        return DB::transaction(function () use ($host, $client, $pass, $options) {
            $now = now();
            $activationType = $pass->activation_type;

            // Determine activation and expiration
            $activatedAt = null;
            $expiresAt = null;

            if ($activationType === ClassPass::ACTIVATION_ON_PURCHASE) {
                $activatedAt = $now;
                $expiresAt = $pass->calculateExpirationDate($now);
            }
            // For ACTIVATION_ON_FIRST_BOOKING, leave activated_at and expires_at null

            $purchase = ClassPassPurchase::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'class_pass_id' => $pass->id,
                'classes_remaining' => $pass->class_count,
                'classes_total' => $pass->class_count,
                'credits_used' => 0,
                'purchased_at' => $now,
                'activated_at' => $activatedAt,
                'activation_type' => $activationType,
                'expires_at' => $expiresAt,
                'created_by_user_id' => auth()->id(),
            ]);

            // Create payment record if price > 0 and payment info provided
            if ($pass->price > 0 && isset($options['manual_method'])) {
                $payment = $this->paymentService->processManualPayment(
                    $host,
                    $client,
                    $pass->price,
                    $options['manual_method'],
                    null,
                    $purchase,
                    $options['payment_notes'] ?? null
                );

                $purchase->update(['payment_id' => $payment->id]);
            }

            // Handle recurring setup if applicable
            if ($pass->is_recurring && isset($options['stripe_subscription_id'])) {
                $purchase->update(['stripe_subscription_id' => $options['stripe_subscription_id']]);
            }

            $this->auditService->logPackPurchased($purchase);

            return $purchase;
        });
    }

    /**
     * Gift a pass to another client
     */
    public function giftPass(
        Host $host,
        Client $fromClient,
        Client $toClient,
        ClassPass $pass,
        array $options = []
    ): ClassPassPurchase {
        if (!$pass->allow_gifting) {
            throw new \Exception('This pass does not allow gifting.');
        }

        // Purchase is made by fromClient but assigned to toClient
        $options['gifted_by_client_id'] = $fromClient->id;

        return $this->purchasePass($host, $toClient, $pass, $options);
    }

    // ===========================================
    // ACTIVATION METHODS
    // ===========================================

    /**
     * Activate a pass (called on first booking if activation_type is on_first_booking)
     */
    public function activatePass(ClassPassPurchase $purchase, string $trigger = 'booking'): void
    {
        if ($purchase->is_activated) {
            return;
        }

        $purchase->activate();

        $this->auditService->log(
            $purchase->host_id,
            'pass_activated',
            $purchase,
            [
                'context' => [
                    'client_id' => $purchase->client_id,
                    'trigger' => $trigger,
                    'activated_at' => $purchase->activated_at?->toISOString(),
                    'expires_at' => $purchase->expires_at?->toISOString(),
                ],
            ]
        );
    }

    /**
     * Manually activate a pass (admin action)
     */
    public function manuallyActivatePass(ClassPassPurchase $purchase): void
    {
        if ($purchase->is_activated) {
            throw new \Exception('This pass is already activated.');
        }

        $this->activatePass($purchase, 'manual_admin');
    }

    /**
     * Check and activate pass on first booking if needed
     */
    public function checkActivationOnFirstBooking(ClassPassPurchase $purchase): void
    {
        if ($purchase->is_pending_activation) {
            $this->activatePass($purchase, 'first_booking');
        }
    }

    // ===========================================
    // ELIGIBILITY & CREDIT CALCULATION
    // ===========================================

    /**
     * Check if a pass purchase is eligible for a class session
     */
    public function checkEligibilityForSession(ClassPassPurchase $purchase, ClassSession $session): bool
    {
        // Must be usable (has credits, not expired, not frozen, activated)
        if (!$purchase->is_usable && !$purchase->is_pending_activation) {
            return false;
        }

        // Check if pass covers this session
        return $purchase->classPass->coversClassSession($session);
    }

    /**
     * Check if a pass purchase is eligible for a class plan
     */
    public function checkEligibility(ClassPassPurchase $purchase, ClassPlan $classPlan): bool
    {
        // Must be usable
        if (!$purchase->is_usable && !$purchase->is_pending_activation) {
            return false;
        }

        // Check if pass covers this class plan
        return $purchase->classPass->coversClassPlan($classPlan);
    }

    /**
     * Calculate credits required for a class session
     */
    public function calculateCreditsForSession(ClassPassPurchase $purchase, ClassSession $session): int
    {
        return $purchase->classPass->calculateCreditsForSession($session);
    }

    /**
     * Check if purchase has enough credits for a session
     */
    public function hasEnoughCreditsForSession(ClassPassPurchase $purchase, ClassSession $session): bool
    {
        $creditsRequired = $this->calculateCreditsForSession($purchase, $session);
        return $purchase->classes_remaining >= $creditsRequired;
    }

    // ===========================================
    // CREDIT MANAGEMENT
    // ===========================================

    /**
     * Deduct credits for a booking
     */
    public function deductCredits(
        ClassPassPurchase $purchase,
        Booking $booking,
        ?int $credits = null
    ): bool {
        // Activate if pending
        $this->checkActivationOnFirstBooking($purchase);

        if (!$purchase->has_credits) {
            return false;
        }

        // Calculate credits if not provided
        if ($credits === null && $booking->bookable instanceof ClassSession) {
            $credits = $this->calculateCreditsForSession($purchase, $booking->bookable);
        }

        $credits = $credits ?? 1;

        if ($purchase->classes_remaining < $credits) {
            return false;
        }

        $result = $purchase->deductCredits($credits, $booking, auth()->id());

        if ($result) {
            $this->auditService->logPackCreditUsed($purchase, $booking);
        }

        return $result;
    }

    /**
     * Restore credits (e.g., when booking cancelled)
     */
    public function restoreCredits(
        ClassPassPurchase $purchase,
        ?Booking $booking = null,
        ?int $credits = null
    ): void {
        // Calculate credits if not provided and booking exists
        if ($credits === null && $booking && $booking->bookable instanceof ClassSession) {
            $credits = $this->calculateCreditsForSession($purchase, $booking->bookable);
        }

        $credits = $credits ?? 1;

        $purchase->restoreCredits($credits, $booking, auth()->id());
        $this->auditService->logPackCreditRestored($purchase, $booking);
    }

    /**
     * Admin credit adjustment
     */
    public function adjustCredits(
        ClassPassPurchase $purchase,
        int $adjustment,
        string $reason
    ): void {
        $purchase->adjustCredits($adjustment, $reason, auth()->id());

        $this->auditService->log(
            'class_pass_purchase',
            $purchase->id,
            'credits_adjusted',
            "Credits adjusted by {$adjustment}: {$reason}",
            null,
            auth()->id()
        );
    }

    // ===========================================
    // FREEZE / UNFREEZE METHODS
    // ===========================================

    /**
     * Freeze a pass
     */
    public function freezePass(ClassPassPurchase $purchase, ?int $days = null, ?string $reason = null): bool
    {
        if (!$purchase->can_freeze) {
            return false;
        }

        $result = $purchase->freeze($days, auth()->id());

        if ($result) {
            $this->auditService->log(
                'class_pass_purchase',
                $purchase->id,
                'pass_frozen',
                $reason ?? 'Pass frozen by admin',
                null,
                auth()->id()
            );
        }

        return $result;
    }

    /**
     * Unfreeze a pass
     */
    public function unfreezePass(ClassPassPurchase $purchase): bool
    {
        if (!$purchase->is_frozen) {
            return false;
        }

        $result = $purchase->unfreeze(auth()->id());

        if ($result) {
            $this->auditService->log(
                'class_pass_purchase',
                $purchase->id,
                'pass_unfrozen',
                'Pass unfrozen',
                null,
                auth()->id()
            );
        }

        return $result;
    }

    // ===========================================
    // EXTENSION METHODS
    // ===========================================

    /**
     * Extend a pass expiration
     */
    public function extendPass(ClassPassPurchase $purchase, int $days, ?string $reason = null): void
    {
        if (!$purchase->classPass->allow_admin_extension) {
            throw new \Exception('This pass does not allow admin extensions.');
        }

        $purchase->extend($days, auth()->id());

        $this->auditService->log(
            'class_pass_purchase',
            $purchase->id,
            'pass_extended',
            $reason ?? "Pass extended by {$days} days",
            null,
            auth()->id()
        );
    }

    // ===========================================
    // TRANSFER METHODS
    // ===========================================

    /**
     * Transfer a pass to another client
     */
    public function transferPass(ClassPassPurchase $purchase, Client $targetClient): ?ClassPassPurchase
    {
        if (!$purchase->can_transfer) {
            return null;
        }

        $newPurchase = $purchase->transferTo($targetClient, auth()->id());

        if ($newPurchase) {
            $this->auditService->log(
                'class_pass_purchase',
                $purchase->id,
                'pass_transferred_out',
                "Pass transferred to client #{$targetClient->id}",
                null,
                auth()->id()
            );

            $this->auditService->log(
                'class_pass_purchase',
                $newPurchase->id,
                'pass_transferred_in',
                "Pass received from purchase #{$purchase->id}",
                null,
                auth()->id()
            );
        }

        return $newPurchase;
    }

    // ===========================================
    // RENEWAL & ROLLOVER METHODS
    // ===========================================

    /**
     * Process renewal for a recurring pass
     */
    public function processRenewal(ClassPassPurchase $purchase): ClassPassPurchase
    {
        $pass = $purchase->classPass;

        if (!$pass->is_recurring) {
            throw new \Exception('This pass is not set up for recurring renewal.');
        }

        return DB::transaction(function () use ($purchase, $pass) {
            // Calculate rollover credits
            $rolloverCredits = 0;
            if ($pass->rollover_enabled) {
                $rolloverCredits = $purchase->processRollover(
                    $pass->max_rollover_credits,
                    $pass->max_rollover_periods
                );
            }

            // Create new purchase for next period
            $newPurchase = ClassPassPurchase::create([
                'host_id' => $purchase->host_id,
                'client_id' => $purchase->client_id,
                'class_pass_id' => $pass->id,
                'classes_remaining' => $pass->class_count + $rolloverCredits,
                'classes_total' => $pass->class_count,
                'rollover_credits' => $rolloverCredits,
                'renewal_count' => $purchase->renewal_count + 1,
                'credits_used' => 0,
                'purchased_at' => now(),
                'activated_at' => now(),
                'activation_type' => ClassPass::ACTIVATION_ON_PURCHASE,
                'expires_at' => $pass->calculateExpirationDate(now()),
                'stripe_subscription_id' => $purchase->stripe_subscription_id,
                'created_by_user_id' => $purchase->created_by_user_id,
            ]);

            // Mark old purchase as renewed
            $purchase->update(['classes_remaining' => 0]);

            // Log rollover if applicable
            if ($rolloverCredits > 0) {
                $newPurchase->creditLogs()->create([
                    'credits_change' => $rolloverCredits,
                    'credit_type' => ClassPassCreditLog::TYPE_ROLLOVER,
                    'notes' => "Rolled over {$rolloverCredits} credits from previous period",
                    'created_by_user_id' => null,
                ]);
            }

            $this->auditService->log(
                'class_pass_purchase',
                $newPurchase->id,
                'pass_renewed',
                "Pass renewed from purchase #{$purchase->id}" . ($rolloverCredits > 0 ? " with {$rolloverCredits} rollover credits" : ''),
                null,
                null
            );

            return $newPurchase;
        });
    }

    // ===========================================
    // QUERY METHODS
    // ===========================================

    /**
     * Get all usable pass purchases for a client
     */
    public function getUsablePasses(Client $client): Collection
    {
        return $client->classPassPurchases()
            ->usable()
            ->with('classPass')
            ->orderBy('expires_at', 'asc') // Prioritize passes expiring soonest
            ->get();
    }

    /**
     * Get eligible pass purchase for a specific class session
     */
    public function getEligiblePassForSession(Client $client, ClassSession $session): ?ClassPassPurchase
    {
        $passes = $this->getUsablePasses($client);

        foreach ($passes as $pass) {
            if ($this->checkEligibilityForSession($pass, $session) && $this->hasEnoughCreditsForSession($pass, $session)) {
                return $pass;
            }
        }

        return null;
    }

    /**
     * Get eligible pass purchase for a specific class plan
     */
    public function getEligiblePassForClass(Client $client, ClassPlan $classPlan): ?ClassPassPurchase
    {
        $passes = $this->getUsablePasses($client);

        foreach ($passes as $pass) {
            if ($this->checkEligibility($pass, $classPlan)) {
                return $pass;
            }
        }

        return null;
    }

    /**
     * Get total remaining credits across all passes for a client
     */
    public function getTotalRemainingCredits(Client $client): int
    {
        return $client->classPassPurchases()
            ->usable()
            ->sum('classes_remaining');
    }

    /**
     * Get available class passes for purchase
     */
    public function getAvailablePasses(Host $host): Collection
    {
        return $host->classPasses()
            ->active()
            ->visible()
            ->ordered()
            ->get();
    }

    /**
     * Get eligible pass purchases for a client (for walk-in booking payment options)
     * Returns array of passes with details for the payment method selection UI
     * Includes both usable passes AND passes pending activation (on_first_booking)
     */
    public function getEligiblePasses(Client $client, ?int $classPlanId = null): array
    {
        // Get both usable passes AND passes pending activation
        $passes = $client->classPassPurchases()
            ->with('classPass')
            ->where('classes_remaining', '>', 0)
            ->where('is_frozen', false)
            ->whereNull('transferred_to_client_id')
            ->where(function ($query) {
                // Either activated and not expired
                $query->where(function ($q) {
                    $q->whereNotNull('activated_at')
                      ->where(function ($q2) {
                          $q2->whereNull('expires_at')
                             ->orWhere('expires_at', '>', now());
                      });
                })
                // Or pending activation (on_first_booking)
                ->orWhere(function ($q) {
                    $q->whereNull('activated_at')
                      ->where('activation_type', ClassPassPurchase::ACTIVATION_ON_FIRST_BOOKING);
                });
            })
            ->orderBy('expires_at', 'asc')
            ->get();

        // If a class plan is specified, filter to only eligible passes
        if ($classPlanId) {
            $classPlan = ClassPlan::find($classPlanId);
            if ($classPlan) {
                $passes = $passes->filter(function ($purchase) use ($classPlan) {
                    return $purchase->classPass->coversClassPlan($classPlan);
                });
            }
        }

        // Transform to array format for UI
        return $passes->map(function ($purchase) {
            $statusText = $purchase->is_pending_activation
                ? 'Ready to activate'
                : $purchase->status_label;

            return [
                'id' => $purchase->id,
                'name' => $purchase->classPass->name,
                'classes_remaining' => $purchase->classes_remaining,
                'classes_total' => $purchase->classes_total,
                'expires_at' => $purchase->expires_at?->format('M j, Y'),
                'days_until_expiration' => $purchase->days_until_expiration,
                'is_expiring_soon' => $purchase->is_expiring_soon,
                'is_pending_activation' => $purchase->is_pending_activation,
                'status' => $statusText,
            ];
        })->values()->toArray();
    }

    /**
     * Check if client has any usable pass
     */
    public function hasUsablePass(Client $client): bool
    {
        return $client->classPassPurchases()
            ->usable()
            ->exists();
    }

    /**
     * Get pass purchase summary for a client
     */
    public function getPassSummary(Client $client): array
    {
        $passes = $this->getUsablePasses($client);

        return [
            'total_passes' => $passes->count(),
            'total_credits' => $passes->sum('classes_remaining'),
            'passes' => $passes->map(function ($pass) {
                return [
                    'id' => $pass->id,
                    'name' => $pass->classPass->name,
                    'classes_remaining' => $pass->classes_remaining,
                    'classes_total' => $pass->classes_total,
                    'rollover_credits' => $pass->rollover_credits,
                    'usage_percent' => $pass->usage_percent,
                    'expires_at' => $pass->expires_at?->format('M j, Y'),
                    'days_until_expiration' => $pass->days_until_expiration,
                    'is_expiring_soon' => $pass->is_expiring_soon,
                    'is_frozen' => $pass->is_frozen,
                    'is_pending_activation' => $pass->is_pending_activation,
                    'status' => $pass->status,
                    'status_label' => $pass->status_label,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get expiring passes for a host (for notifications/reports)
     */
    public function getExpiringPasses(Host $host, int $daysAhead = 7): Collection
    {
        return ClassPassPurchase::forHost($host->id)
            ->usable()
            ->expiringSoon($daysAhead)
            ->with(['client', 'classPass'])
            ->orderBy('expires_at', 'asc')
            ->get();
    }

    /**
     * Get frozen passes for a host
     */
    public function getFrozenPasses(Host $host): Collection
    {
        return ClassPassPurchase::forHost($host->id)
            ->frozen()
            ->with(['client', 'classPass'])
            ->orderBy('frozen_at', 'desc')
            ->get();
    }

    // ===========================================
    // ANALYTICS & REPORTING
    // ===========================================

    /**
     * Get pass analytics for a host
     */
    public function getPassAnalytics(Host $host): array
    {
        $activePasses = ClassPassPurchase::forHost($host->id)->usable()->count();
        $totalCreditsRemaining = ClassPassPurchase::forHost($host->id)->usable()->sum('classes_remaining');
        $expiringSoon = ClassPassPurchase::forHost($host->id)->usable()->expiringSoon(7)->count();
        $frozenCount = ClassPassPurchase::forHost($host->id)->frozen()->count();

        // Calculate breakage rate (unused credits that expired)
        $expiredPasses = ClassPassPurchase::forHost($host->id)
            ->expired()
            ->where('classes_remaining', '>', 0)
            ->get();

        $totalExpiredCredits = $expiredPasses->sum('classes_remaining');
        $totalExpiredOriginal = $expiredPasses->sum('classes_total');
        $breakageRate = $totalExpiredOriginal > 0
            ? round(($totalExpiredCredits / $totalExpiredOriginal) * 100, 1)
            : 0;

        // Revenue by pass type
        $revenueByPassType = ClassPass::forHost($host->id)
            ->active()
            ->withCount('purchases')
            ->get()
            ->map(function ($pass) {
                return [
                    'id' => $pass->id,
                    'name' => $pass->name,
                    'purchases_count' => $pass->purchases_count,
                    'estimated_revenue' => $pass->purchases_count * ($pass->price ?? 0),
                ];
            });

        return [
            'active_passes' => $activePasses,
            'total_credits_remaining' => $totalCreditsRemaining,
            'expiring_soon' => $expiringSoon,
            'frozen_count' => $frozenCount,
            'breakage_rate' => $breakageRate,
            'revenue_by_type' => $revenueByPassType,
        ];
    }

    /**
     * Get credit log for a purchase
     */
    public function getCreditLog(ClassPassPurchase $purchase, int $limit = 50): Collection
    {
        return $purchase->creditLogs()
            ->with(['booking', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // ===========================================
    // BACKWARD COMPATIBILITY (Deprecated methods)
    // ===========================================

    /**
     * @deprecated Use getUsablePasses() instead
     */
    public function getUsablePacks(Client $client): Collection
    {
        return $this->getUsablePasses($client);
    }

    /**
     * @deprecated Use getEligiblePassForClass() instead
     */
    public function getEligiblePackForClass(Client $client, ClassPlan $classPlan): ?ClassPassPurchase
    {
        return $this->getEligiblePassForClass($client, $classPlan);
    }

    /**
     * @deprecated Use getAvailablePasses() instead
     */
    public function getAvailablePacks(Host $host): Collection
    {
        return $this->getAvailablePasses($host);
    }

    /**
     * @deprecated Use hasUsablePass() instead
     */
    public function hasUsablePack(Client $client): bool
    {
        return $this->hasUsablePass($client);
    }

    /**
     * @deprecated Use getPassSummary() instead
     */
    public function getPackSummary(Client $client): array
    {
        return $this->getPassSummary($client);
    }

    /**
     * @deprecated Use deductCredits() instead
     */
    public function deductCredit(ClassPassPurchase $purchase, Booking $booking): bool
    {
        return $this->deductCredits($purchase, $booking, 1);
    }

    /**
     * @deprecated Use restoreCredits() instead
     */
    public function restoreCredit(ClassPassPurchase $purchase, ?Booking $booking = null): void
    {
        $this->restoreCredits($purchase, $booking, 1);
    }

    /**
     * @deprecated Use purchasePass() instead
     */
    public function purchasePack(
        Host $host,
        Client $client,
        $pack,
        array $options = []
    ): ClassPassPurchase {
        // Handle both ClassPack and ClassPass models
        if ($pack instanceof \App\Models\ClassPack) {
            $pack = ClassPass::find($pack->id);
        }
        return $this->purchasePass($host, $client, $pack, $options);
    }
}
