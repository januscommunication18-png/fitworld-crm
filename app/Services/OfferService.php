<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Host;
use App\Models\Offer;
use App\Models\OfferRedemption;
use Illuminate\Support\Collection;

class OfferService
{
    /**
     * Validate a promo code for a client and booking type.
     */
    public function validatePromoCode(
        Host $host,
        string $code,
        ?Client $client = null,
        ?string $appliesTo = null,
        ?float $originalPrice = null
    ): array {
        $offer = Offer::where('host_id', $host->id)
            ->where('code', strtoupper($code))
            ->where('status', 'active')
            ->first();

        if (!$offer) {
            return [
                'valid' => false,
                'error' => 'Invalid promo code.',
                'offer' => null,
            ];
        }

        return $this->validateOffer($offer, $client, $appliesTo, $originalPrice);
    }

    /**
     * Validate an offer for a client.
     */
    public function validateOffer(
        Offer $offer,
        ?Client $client = null,
        ?string $appliesTo = null,
        ?float $originalPrice = null
    ): array {
        // Check if offer is active
        if ($offer->status !== 'active') {
            return [
                'valid' => false,
                'error' => 'This offer is no longer active.',
                'offer' => null,
            ];
        }

        // Check date validity
        if ($offer->start_date && $offer->start_date->isFuture()) {
            return [
                'valid' => false,
                'error' => 'This offer is not yet available.',
                'offer' => null,
            ];
        }

        if ($offer->end_date && $offer->end_date->isPast()) {
            return [
                'valid' => false,
                'error' => 'This offer has expired.',
                'offer' => null,
            ];
        }

        // Check total usage limit
        if ($offer->total_usage_limit && $offer->total_redemptions >= $offer->total_usage_limit) {
            return [
                'valid' => false,
                'error' => 'This offer has reached its usage limit.',
                'offer' => null,
            ];
        }

        // Check first X users limit
        if ($offer->first_x_users && $offer->total_redemptions >= $offer->first_x_users) {
            return [
                'valid' => false,
                'error' => 'This offer is no longer available.',
                'offer' => null,
            ];
        }

        // Check per-member limit if client provided
        if ($client && $offer->per_member_limit) {
            $clientRedemptions = OfferRedemption::where('offer_id', $offer->id)
                ->where('client_id', $client->id)
                ->whereIn('status', ['applied', 'completed'])
                ->count();

            if ($clientRedemptions >= $offer->per_member_limit) {
                return [
                    'valid' => false,
                    'error' => 'You have already used this offer the maximum number of times.',
                    'offer' => null,
                ];
            }
        }

        // Check applies_to compatibility
        if ($appliesTo && $offer->applies_to !== 'all') {
            $validTypes = $this->getAppliesTo($offer->applies_to);
            if (!in_array($appliesTo, $validTypes)) {
                return [
                    'valid' => false,
                    'error' => 'This offer cannot be applied to this type of purchase.',
                    'offer' => null,
                ];
            }
        }

        // Check target audience
        if ($client && !$this->isClientEligible($offer, $client)) {
            return [
                'valid' => false,
                'error' => 'You are not eligible for this offer.',
                'offer' => null,
            ];
        }

        // Check channel restrictions
        // (This would be passed from the context - online, front_desk, app)

        // Calculate discount
        $discount = $this->calculateDiscount($offer, $originalPrice ?? 0);

        return [
            'valid' => true,
            'error' => null,
            'offer' => $offer,
            'discount_amount' => $discount,
            'discount_display' => $offer->getFormattedDiscount(),
        ];
    }

    /**
     * Check if a client is eligible for an offer based on target audience.
     */
    public function isClientEligible(Offer $offer, ?Client $client): bool
    {
        if (!$client) {
            // For guest checkouts, only allow "all_members" or "new_members"
            return in_array($offer->target_audience, ['all_members', 'new_members']);
        }

        switch ($offer->target_audience) {
            case 'all_members':
                return true;

            case 'new_members':
                // Client created in last 30 days
                return $client->created_at->isAfter(now()->subDays(30));

            case 'inactive_members':
                // No booking in last 30 days
                $lastBooking = $client->bookings()
                    ->where('status', 'completed')
                    ->latest('start_time')
                    ->first();
                return !$lastBooking || $lastBooking->start_time->isBefore(now()->subDays(30));

            case 'high_spenders':
                // Total spend > $500
                $totalSpend = $client->transactions()
                    ->where('status', 'completed')
                    ->sum('amount') / 100;
                return $totalSpend >= 500;

            case 'vip_tier':
                // Has VIP tier score
                return $client->score && $client->score->loyalty_tier === 'vip';

            case 'specific_segment':
                // Check if client is in the specified segment
                if (!$offer->segment_id) {
                    return true;
                }
                return $client->segments()
                    ->where('segments.id', $offer->segment_id)
                    ->exists();

            default:
                return true;
        }
    }

    /**
     * Calculate the discount amount for an offer.
     */
    public function calculateDiscount(Offer $offer, float $originalPrice): float
    {
        if ($originalPrice <= 0) {
            return 0;
        }

        switch ($offer->discount_type) {
            case 'percentage':
                $discount = $originalPrice * ($offer->discount_value / 100);
                break;

            case 'fixed_amount':
                $discount = min($offer->discount_value, $originalPrice);
                break;

            case 'free_class':
                // Full price off for free class
                $discount = $originalPrice;
                break;

            default:
                $discount = 0;
        }

        return round($discount, 2);
    }

    /**
     * Get auto-apply offers for a client and booking type.
     */
    public function getAutoApplyOffers(
        Host $host,
        ?Client $client = null,
        ?string $appliesTo = null,
        ?float $originalPrice = null
    ): Collection {
        $offers = Offer::where('host_id', $host->id)
            ->where('status', 'active')
            ->where('auto_apply', true)
            ->where('require_code', false)
            ->get();

        $validOffers = collect();

        foreach ($offers as $offer) {
            $validation = $this->validateOffer($offer, $client, $appliesTo, $originalPrice);
            if ($validation['valid']) {
                $validOffers->push([
                    'offer' => $offer,
                    'discount_amount' => $validation['discount_amount'],
                ]);
            }
        }

        return $validOffers;
    }

    /**
     * Get the best auto-apply offer (highest discount).
     */
    public function getBestAutoApplyOffer(
        Host $host,
        ?Client $client = null,
        ?string $appliesTo = null,
        ?float $originalPrice = null
    ): ?array {
        $offers = $this->getAutoApplyOffers($host, $client, $appliesTo, $originalPrice);

        if ($offers->isEmpty()) {
            return null;
        }

        // Return offer with highest discount
        return $offers->sortByDesc('discount_amount')->first();
    }

    /**
     * Record an offer redemption.
     */
    public function recordRedemption(
        Offer $offer,
        Client $client,
        float $originalPrice,
        float $discountAmount,
        string $channel = 'online',
        ?string $promoCodeUsed = null,
        ?int $appliedBy = null,
        ?string $redeemableType = null,
        ?int $redeemableId = null
    ): OfferRedemption {
        $redemption = OfferRedemption::create([
            'host_id' => $offer->host_id,
            'offer_id' => $offer->id,
            'client_id' => $client->id,
            'redeemable_type' => $redeemableType,
            'redeemable_id' => $redeemableId,
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $originalPrice - $discountAmount,
            'currency' => $offer->host->default_currency ?? 'USD',
            'channel' => $channel,
            'promo_code_used' => $promoCodeUsed,
            'status' => 'applied',
            'applied_by' => $appliedBy,
        ]);

        // Update offer statistics
        $offer->increment('total_redemptions');
        $offer->increment('total_discount_given', $discountAmount);
        $offer->increment('total_revenue_generated', $originalPrice - $discountAmount);

        // Check if client is new (for new_members_acquired stat)
        if ($client->created_at->isAfter(now()->subDays(30))) {
            $offer->increment('new_members_acquired');
        }

        // Check if total usage limit reached
        if ($offer->auto_stop_on_limit && $offer->total_usage_limit) {
            if ($offer->total_redemptions >= $offer->total_usage_limit) {
                $offer->update(['status' => 'paused']);
            }
        }

        return $redemption;
    }

    /**
     * Mark redemption as completed (after payment success).
     */
    public function completeRedemption(OfferRedemption $redemption, ?int $paymentId = null, ?int $invoiceId = null): void
    {
        $redemption->update([
            'status' => 'completed',
            'completed_at' => now(),
            'payment_id' => $paymentId,
            'invoice_id' => $invoiceId,
        ]);
    }

    /**
     * Void a redemption (if payment fails or booking cancelled).
     */
    public function voidRedemption(OfferRedemption $redemption): void
    {
        $offer = $redemption->offer;

        // Reverse the statistics
        $offer->decrement('total_redemptions');
        $offer->decrement('total_discount_given', $redemption->discount_amount);
        $offer->decrement('total_revenue_generated', $redemption->final_price);

        $redemption->update([
            'status' => 'voided',
        ]);
    }

    /**
     * Get compatible applies_to values.
     */
    protected function getAppliesTo(string $appliesTo): array
    {
        $mapping = [
            'all' => ['classes', 'services', 'memberships', 'retail', 'class_packs'],
            'classes' => ['classes'],
            'services' => ['services'],
            'memberships' => ['memberships'],
            'retail' => ['retail'],
            'class_packs' => ['class_packs'],
        ];

        return $mapping[$appliesTo] ?? [$appliesTo];
    }

    /**
     * Get all available offers for display (non-hidden, active).
     */
    public function getAvailableOffers(Host $host, ?Client $client = null): Collection
    {
        $offers = Offer::where('host_id', $host->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->get();

        // Filter by eligibility if client provided
        if ($client) {
            $offers = $offers->filter(fn($offer) => $this->isClientEligible($offer, $client));
        }

        return $offers;
    }
}
