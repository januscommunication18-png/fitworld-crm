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
     * Update policies settings (section-based)
     */
    public function update(Request $request)
    {
        $host = auth()->user()->host;
        $section = $request->input('section', 'all');

        $rules = $this->validationRules($section);
        $validated = $request->validate($rules);

        // Convert checkbox values to booleans
        $booleanFields = [
            'allow_cancellations',
            'enable_waitlist',
            'waitlist_auto_promote',
            'waitlist_notify_on_promotion',
            'allow_booking_without_payment',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $rules)) {
                $validated[$field] = $request->boolean($field);
            }
        }

        // Handle legal pages (stored as separate columns)
        if ($section === 'legal') {
            $host->terms_of_service = $validated['terms_of_service'] ?? null;
            $host->privacy_policy = $validated['privacy_policy'] ?? null;
            $host->save();
        } else {
            // Remove non-policy fields
            unset($validated['section']);

            $currentPolicies = $host->policies ?? [];
            $host->policies = array_merge($currentPolicies, $validated);
            $host->save();
        }

        $sectionLabels = [
            'cancellation' => 'Cancellation policy',
            'noshow' => 'No-show policy',
            'waitlist' => 'Waitlist policy',
            'booking_limits' => 'Booking limits',
            'legal' => 'Legal pages',
            'rules' => 'Studio rules',
        ];

        $label = $sectionLabels[$section] ?? 'Policies';

        return redirect()->route('settings.locations.policies')
            ->with('success', "{$label} updated successfully");
    }

    private function validationRules(string $section): array
    {
        $rules = ['section' => 'nullable|string'];

        return match ($section) {
            'cancellation' => $rules + [
                'allow_cancellations' => 'boolean',
                'cancellation_window_hours' => 'required|integer|min:0|max:168',
                'cancellation_fees' => 'nullable|array',
                'cancellation_fees.*' => 'nullable|numeric|min:0',
                'late_cancellation_handling' => 'required|in:mark_late,charge_fee,deduct_credit',
            ],
            'noshow' => $rules + [
                'no_show_fees' => 'nullable|array',
                'no_show_fees.*' => 'nullable|numeric|min:0',
                'no_show_handling' => 'required|in:no_action,charge_fee,deduct_credit,strike',
                'no_show_grace_period_minutes' => 'required|integer|min:0|max:60',
            ],
            'waitlist' => $rules + [
                'enable_waitlist' => 'boolean',
                'waitlist_auto_promote' => 'boolean',
                'waitlist_promotion_window_minutes' => 'required|integer|min:0|max:1440',
                'waitlist_notify_on_promotion' => 'boolean',
                'waitlist_hold_spot_minutes' => 'required|integer|min:0|max:60',
            ],
            'booking_limits' => $rules + [
                'max_bookings_per_class' => 'required|integer|min:1|max:10',
                'max_active_bookings' => 'nullable|integer|min:1|max:100',
                'allow_booking_without_payment' => 'boolean',
                'booking_earliest_days' => 'required|integer|min:1|max:365',
                'booking_latest_minutes' => 'required|integer|min:0|max:1440',
            ],
            'legal' => $rules + [
                'terms_of_service' => 'nullable|string|max:100000',
                'privacy_policy' => 'nullable|string|max:100000',
            ],
            'rules' => $rules + [
                'house_rules' => 'nullable|string|max:5000',
                'liability_waiver_url' => 'nullable|url|max:500',
                'arrival_instructions' => 'nullable|string|max:2000',
            ],
            default => $rules,
        };
    }
}
