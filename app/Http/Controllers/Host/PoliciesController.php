<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\Request;

class PoliciesController extends Controller
{
    /**
     * Show policies settings form
     */
    public function index()
    {
        $host = auth()->user()->host;
        $policies = array_merge(Host::defaultPolicies(), $host->policies ?? []);

        return view('host.settings.locations.policies', [
            'host' => $host,
            'policies' => $policies,
        ]);
    }

    /**
     * Update policies settings
     */
    public function update(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            // Cancellation Policy
            'allow_cancellations' => 'boolean',
            'cancellation_window_hours' => 'required|integer|min:0|max:168',
            'cancellation_fee' => 'nullable|numeric|min:0',
            'late_cancellation_handling' => 'required|in:mark_late,charge_fee,deduct_credit',

            // No-Show Policy
            'no_show_fee' => 'nullable|numeric|min:0',
            'no_show_handling' => 'required|in:no_action,charge_fee,deduct_credit,strike',
            'no_show_grace_period_minutes' => 'required|integer|min:0|max:60',

            // Waitlist Policy
            'enable_waitlist' => 'boolean',
            'waitlist_auto_promote' => 'boolean',
            'waitlist_promotion_window_minutes' => 'required|integer|min:0|max:1440',
            'waitlist_notify_on_promotion' => 'boolean',
            'waitlist_hold_spot_minutes' => 'required|integer|min:0|max:60',

            // Booking Limits
            'max_bookings_per_class' => 'required|integer|min:1|max:10',
            'max_active_bookings' => 'nullable|integer|min:1|max:100',
            'allow_booking_without_payment' => 'boolean',
            'booking_earliest_days' => 'required|integer|min:1|max:365',
            'booking_latest_minutes' => 'required|integer|min:0|max:1440',

            // Studio Rules
            'house_rules' => 'nullable|string|max:5000',
            'liability_waiver_url' => 'nullable|url|max:500',
            'arrival_instructions' => 'nullable|string|max:2000',
        ]);

        // Convert checkbox values to booleans
        $booleanFields = [
            'allow_cancellations',
            'enable_waitlist',
            'waitlist_auto_promote',
            'waitlist_notify_on_promotion',
            'allow_booking_without_payment',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        // Merge with existing policies
        $currentPolicies = $host->policies ?? [];
        $host->policies = array_merge($currentPolicies, $validated);
        $host->save();

        return redirect()->route('settings.locations.policies')
            ->with('success', 'Policies updated successfully');
    }
}
