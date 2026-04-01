<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\BillingCredit;
use Illuminate\Http\Request;

class BillingCreditController extends Controller
{
    public function cancelPreview(BillingCredit $billingCredit)
    {
        $host = auth()->user()->currentHost();

        if ($billingCredit->host_id !== $host->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($billingCredit->status !== BillingCredit::STATUS_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'This billing credit is not active.'], 422);
        }

        $breakdown = $billingCredit->calculateCancellationBreakdown();

        return response()->json([
            'success' => true,
            'credit' => [
                'id' => $billingCredit->id,
                'source_name' => $billingCredit->getSourceName(),
                'billing_period' => $billingCredit->billing_period,
                'amount_paid' => (float) $billingCredit->amount_paid,
                'registration_fee_paid' => (float) $billingCredit->registration_fee_paid,
                'credit_remaining' => (float) $billingCredit->credit_remaining,
                'start_date' => $billingCredit->start_date->format('M d, Y'),
                'end_date' => $billingCredit->end_date->format('M d, Y'),
            ],
            'breakdown' => $breakdown,
        ]);
    }

    public function cancel(Request $request, BillingCredit $billingCredit)
    {
        $host = auth()->user()->currentHost();

        if ($billingCredit->host_id !== $host->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($billingCredit->status !== BillingCredit::STATUS_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'This billing credit is not active.'], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $breakdown = $billingCredit->cancel(auth()->id(), $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Billing credit cancelled.',
            'breakdown' => $breakdown,
        ]);
    }
}
