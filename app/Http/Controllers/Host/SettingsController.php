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
            'logo' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
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

    public function uploadStudioCover(Request $request)
    {
        $request->validate([
            'cover' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Delete old cover if exists
        if ($host->cover_image_path && \Storage::disk('public')->exists($host->cover_image_path)) {
            \Storage::disk('public')->delete($host->cover_image_path);
        }

        // Store new cover
        $path = $request->file('cover')->store('covers/' . $host->id, 'public');
        $host->update(['cover_image_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Cover image uploaded successfully',
            'cover_url' => \Storage::url($path),
        ]);
    }

    public function updateStudioContact(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'studio_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
        ]);
    }

    public function updateStudioSocial(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'social_links' => 'nullable|array',
            'social_links.instagram' => 'nullable|url|max:255',
            'social_links.facebook' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
            'social_links.tiktok' => 'nullable|url|max:255',
        ]);

        $host->update(['social_links' => $validated['social_links'] ?? []]);

        return response()->json([
            'success' => true,
            'message' => 'Social links updated successfully',
        ]);
    }

    public function updateStudioAmenities(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'amenities' => 'nullable|array',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Amenities updated successfully',
        ]);
    }

    public function updateStudioCurrency(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'currency' => 'required|string|size:3',
            'confirmed' => 'sometimes|boolean',
        ]);

        // Check if studio has financial data and confirmation is required
        if ($host->hasFinancialData() && !($request->confirmed ?? false)) {
            return response()->json([
                'success' => false,
                'requires_confirmation' => true,
                'message' => 'Currency change requires confirmation due to existing financial data.',
            ]);
        }

        $host->update(['currency' => $validated['currency']]);

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully',
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
