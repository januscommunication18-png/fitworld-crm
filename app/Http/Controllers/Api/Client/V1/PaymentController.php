<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClassPassPurchase;
use App\Models\CustomerMembership;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * The signed-in client's billing overview: active plans (memberships and
     * class passes) plus their payment history at this studio.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $memberships = $client->customerMemberships()
            ->where('host_id', $client->host_id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->get()
            ->map(fn (CustomerMembership $m) => [
                'kind' => 'membership',
                'name' => $m->membershipPlan?->name ?? 'Membership',
                'credits_remaining' => $m->credits_remaining,
                'credits_total' => $m->credits_per_period,
                'renews_on' => $m->current_period_end?->toDateString(),
                'expires_on' => $m->expires_at?->toDateString(),
            ]);

        $passes = $client->classPassPurchases()
            ->where('host_id', $client->host_id)
            ->usable()
            ->with('classPass')
            ->get()
            ->map(fn (ClassPassPurchase $p) => [
                'kind' => 'pass',
                'name' => $p->classPass?->name ?? 'Class pass',
                'credits_remaining' => $p->classes_remaining,
                'credits_total' => $p->classes_total,
                'renews_on' => null,
                'expires_on' => $p->expires_at?->toDateString(),
            ]);

        $typeLabels = [
            Transaction::TYPE_CLASS_BOOKING => 'Class booking',
            Transaction::TYPE_SERVICE_BOOKING => 'Session booking',
            Transaction::TYPE_MEMBERSHIP_PURCHASE => 'Membership',
            Transaction::TYPE_CLASS_PACK_PURCHASE => 'Class pass',
            Transaction::TYPE_RENTAL => 'Rental',
            Transaction::TYPE_EVENT_REGISTRATION => 'Event',
        ];

        $payments = Transaction::query()
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn (Transaction $t) => [
                'id' => $t->id,
                'description' => $t->description ?: ($typeLabels[$t->type] ?? 'Payment'),
                'type_label' => $typeLabels[$t->type] ?? 'Payment',
                'amount' => (float) $t->total_amount,
                'currency' => $t->currency,
                'status' => $t->status,
                'payment_method' => $t->payment_method,
                'date' => $t->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => [
            'plans' => $memberships->concat($passes)->values(),
            'payments' => $payments,
        ]]);
    }
}