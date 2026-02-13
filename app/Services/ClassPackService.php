<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassPack;
use App\Models\ClassPackPurchase;
use App\Models\ClassPlan;
use App\Models\Client;
use App\Models\Host;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassPackService
{
    protected AuditService $auditService;
    protected PaymentService $paymentService;

    public function __construct(AuditService $auditService, PaymentService $paymentService)
    {
        $this->auditService = $auditService;
        $this->paymentService = $paymentService;
    }

    /**
     * Purchase a class pack
     */
    public function purchasePack(
        Host $host,
        Client $client,
        ClassPack $pack,
        array $options = []
    ): ClassPackPurchase {
        return DB::transaction(function () use ($host, $client, $pack, $options) {
            $now = now();
            $expiresAt = $pack->calculateExpirationDate($now);

            $purchase = ClassPackPurchase::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'class_pack_id' => $pack->id,
                'classes_remaining' => $pack->class_count,
                'classes_total' => $pack->class_count,
                'purchased_at' => $now,
                'expires_at' => $expiresAt,
                'created_by_user_id' => auth()->id(),
            ]);

            // Create payment record if price > 0 and payment info provided
            if ($pack->price > 0 && isset($options['manual_method'])) {
                $payment = $this->paymentService->processManualPayment(
                    $host,
                    $client,
                    $pack->price,
                    $options['manual_method'],
                    null,
                    $purchase,
                    $options['payment_notes'] ?? null
                );

                $purchase->update(['payment_id' => $payment->id]);
            }

            $this->auditService->logPackPurchased($purchase);

            return $purchase;
        });
    }

    /**
     * Check if a pack purchase is eligible for a class plan
     */
    public function checkEligibility(ClassPackPurchase $purchase, ClassPlan $classPlan): bool
    {
        // Must have credits remaining
        if (!$purchase->has_credits) {
            return false;
        }

        // Must not be expired
        if ($purchase->is_expired) {
            return false;
        }

        // Check if pack covers this class plan
        return $purchase->classPack->coversClassPlan($classPlan);
    }

    /**
     * Deduct a credit from pack purchase
     */
    public function deductCredit(ClassPackPurchase $purchase, Booking $booking): bool
    {
        if (!$purchase->has_credits) {
            return false;
        }

        $result = $purchase->deductCredit();

        if ($result) {
            $this->auditService->logPackCreditUsed($purchase, $booking);
        }

        return $result;
    }

    /**
     * Restore a credit to pack purchase (e.g., when booking cancelled)
     */
    public function restoreCredit(ClassPackPurchase $purchase, ?Booking $booking = null): void
    {
        $purchase->restoreCredit();
        $this->auditService->logPackCreditRestored($purchase, $booking);
    }

    /**
     * Get all usable pack purchases for a client
     */
    public function getUsablePacks(Client $client): Collection
    {
        return $client->classPackPurchases()
            ->usable()
            ->with('classPack')
            ->orderBy('expires_at', 'asc') // Prioritize packs expiring soonest
            ->get();
    }

    /**
     * Get eligible pack purchase for a specific class plan
     */
    public function getEligiblePackForClass(Client $client, ClassPlan $classPlan): ?ClassPackPurchase
    {
        $packs = $this->getUsablePacks($client);

        foreach ($packs as $pack) {
            if ($this->checkEligibility($pack, $classPlan)) {
                return $pack;
            }
        }

        return null;
    }

    /**
     * Get total remaining credits across all packs for a client
     */
    public function getTotalRemainingCredits(Client $client): int
    {
        return $client->classPackPurchases()
            ->usable()
            ->sum('classes_remaining');
    }

    /**
     * Get available class packs for purchase
     */
    public function getAvailablePacks(Host $host): Collection
    {
        return $host->classPacks()
            ->active()
            ->visible()
            ->ordered()
            ->get();
    }

    /**
     * Check if client has any usable pack
     */
    public function hasUsablePack(Client $client): bool
    {
        return $client->classPackPurchases()
            ->usable()
            ->exists();
    }

    /**
     * Get pack purchase summary for a client
     */
    public function getPackSummary(Client $client): array
    {
        $packs = $this->getUsablePacks($client);

        return [
            'total_packs' => $packs->count(),
            'total_credits' => $packs->sum('classes_remaining'),
            'packs' => $packs->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'name' => $pack->classPack->name,
                    'classes_remaining' => $pack->classes_remaining,
                    'classes_total' => $pack->classes_total,
                    'usage_percent' => $pack->usage_percent,
                    'expires_at' => $pack->expires_at?->format('M j, Y'),
                    'days_until_expiration' => $pack->days_until_expiration,
                    'is_expiring_soon' => $pack->days_until_expiration !== null && $pack->days_until_expiration <= 7,
                ];
            })->toArray(),
        ];
    }
}
