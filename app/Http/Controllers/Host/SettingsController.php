<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Redirect /settings to studio profile
    public function index()
    {
        return redirect()->route('settings.studio.profile');
    }

    // ─────────────────────────────────────────────────────────────
    // Studio
    // ─────────────────────────────────────────────────────────────

    public function studioProfile()
    {
        $host = auth()->user()->host;
        return view('host.settings.studio.profile', compact('host'));
    }

    public function updateStudioProfile(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'studio_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'timezone' => 'required|string|max:100',
            'studio_types' => 'nullable|array',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Studio profile updated successfully',
            'data' => $host->fresh(),
        ]);
    }

    public function updateStudioAbout(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'about' => 'nullable|string|max:2000',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Studio description updated successfully',
        ]);
    }

    public function uploadStudioLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $host = auth()->user()->host;

        // Delete old logo if exists
        if ($host->logo_path && \Storage::disk('public')->exists($host->logo_path)) {
            \Storage::disk('public')->delete($host->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos/' . $host->id, 'public');
        $host->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'logo_url' => \Storage::url($path),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Locations
    // ─────────────────────────────────────────────────────────────

    public function rooms()
    {
        return view('host.settings.locations.rooms');
    }

    public function bookingPage()
    {
        return view('host.settings.locations.booking-page');
    }

    public function policies()
    {
        return view('host.settings.locations.policies');
    }

    // ─────────────────────────────────────────────────────────────
    // Team
    // ─────────────────────────────────────────────────────────────

    public function users()
    {
        return view('host.settings.team.users');
    }

    public function instructors()
    {
        return view('host.settings.team.instructors');
    }

    public function permissions()
    {
        return view('host.settings.team.permissions');
    }

    // ─────────────────────────────────────────────────────────────
    // Payments
    // ─────────────────────────────────────────────────────────────

    public function paymentSettings()
    {
        return view('host.settings.payments.settings');
    }

    public function taxSettings()
    {
        return view('host.settings.payments.tax');
    }

    public function payoutPreferences()
    {
        return view('host.settings.payments.payouts');
    }

    // ─────────────────────────────────────────────────────────────
    // Notifications
    // ─────────────────────────────────────────────────────────────

    public function emailNotifications()
    {
        return view('host.settings.notifications.email');
    }

    public function smsNotifications()
    {
        return view('host.settings.notifications.sms');
    }

    public function automationRules()
    {
        return view('host.settings.notifications.automation');
    }

    // ─────────────────────────────────────────────────────────────
    // Integrations
    // ─────────────────────────────────────────────────────────────

    public function stripeIntegration()
    {
        return view('host.settings.integrations.stripe');
    }

    public function fitNearYouIntegration()
    {
        return view('host.settings.integrations.fitnearyou');
    }

    public function calendarSync()
    {
        return view('host.settings.integrations.calendar');
    }

    public function paypalIntegration()
    {
        return view('host.settings.integrations.paypal');
    }

    public function cashAppIntegration()
    {
        return view('host.settings.integrations.cashapp');
    }

    public function venmoIntegration()
    {
        return view('host.settings.integrations.venmo');
    }

    // ─────────────────────────────────────────────────────────────
    // Plans & Billing
    // ─────────────────────────────────────────────────────────────

    public function currentPlan()
    {
        return view('host.settings.billing.plan');
    }

    public function usage()
    {
        return view('host.settings.billing.usage');
    }

    public function invoices()
    {
        return view('host.settings.billing.invoices');
    }

    // ─────────────────────────────────────────────────────────────
    // Advanced
    // ─────────────────────────────────────────────────────────────

    public function dataExport()
    {
        return view('host.settings.advanced.export');
    }

    public function auditLogs()
    {
        return view('host.settings.advanced.audit');
    }

    public function dangerZone()
    {
        return view('host.settings.advanced.danger');
    }
}
