<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * Buy a membership plan from the app for a prepaid billing period.
     * Manual payments follow the web flow: the transaction stays pending and
     * the membership activates when the studio confirms payment. Free plans
     * activate immediately.
     */
    public function purchase(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'membership_plan_id' => ['required', 'integer'],
            'months' => ['required', 'integer', 'min:1', 'max:12'],
            'payment_method' => ['required', 'string',
                'in:cash,venmo,zelle,paypal,cash_app,bank_transfer'],
        ]);

        $plan = MembershipPlan::query()
            ->where('host_id', $client->host_id)
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->find($validated['membership_plan_id']);

        if (! $plan) {
            return response()->json(['message' => 'This membership is no longer available.'], 422);
        }

        $alreadyActive = $client->customerMemberships()
            ->where('membership_plan_id', $plan->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->exists();
        if ($alreadyActive) {
            return response()->json(['message' => 'You already have this membership.'], 422);
        }

        $pendingPurchase = Transaction::query()
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->where('type', Transaction::TYPE_MEMBERSHIP_PURCHASE)
            ->where('purchasable_type', MembershipPlan::class)
            ->where('purchasable_id', $plan->id)
            ->where('status', Transaction::STATUS_PENDING)
            ->exists();
        if ($pendingPurchase) {
            return response()->json(['message' =>
                'You already requested this membership — pay at the studio to activate it.'], 422);
        }

        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';
        $months = $validated['months'];
        $base = $plan->getPriceForCurrency($currency) ?? 0;
        $discounted = $plan->getBillingPeriodTotalForCurrency($months, $currency);
        $total = $plan->interval === MembershipPlan::INTERVAL_YEARLY
            ? $base
            : ($discounted > 0 ? $discounted : $base * $months);

        $isFree = $total <= 0;

        $transaction = Transaction::create([
            'host_id' => $client->host_id,
            'client_id' => $client->id,
            'type' => Transaction::TYPE_MEMBERSHIP_PURCHASE,
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'subtotal' => $total,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $total,
            'currency' => $currency,
            'status' => $isFree ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
            'payment_method' => $isFree ? Transaction::METHOD_COMP : Transaction::METHOD_MANUAL,
            'manual_method' => $isFree ? null : $validated['payment_method'],
            'paid_at' => $isFree ? now() : null,
            'metadata' => [
                'billing_period' => "{$months} months",
                'source' => 'client_app',
            ],
        ]);

        if ($isFree) {
            app(TransactionService::class)->activateMembershipPurchase($transaction);
        }

        return response()->json(['data' => [
            'status' => $isFree ? 'active' : 'pending',
            'total' => (float) $total,
            'currency' => $currency,
            'months' => $months,
            'plan' => $plan->name,
        ]], 201);
    }
}
