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

    // ─────────────────────────────────────────────────────────────
    // Developer Tools (only in local/development)
    // ─────────────────────────────────────────────────────────────

    public function emailLogs()
    {
        // Only allow in local environment
        if (!app()->environment('local')) {
            abort(404);
        }

        $emails = [];
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);

            // Match email log entries - Laravel logs emails with "local.DEBUG: From:"
            // Pattern: [timestamp] local.DEBUG: From: ... followed by email content until next log entry
            preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\][^\[]*?local\.DEBUG: From:([^\n]+)\nTo:([^\n]+)\nSubject:([^\n]+)(.*?)(?=\[\d{4}-\d{2}-\d{2}|\z)/s', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $timestamp = $match[1];
                $from = trim($match[2]);
                $to = trim($match[3]);
                $subject = trim($match[4]);
                $body = trim($match[5]);

                // Extract HTML content from multipart email
                // Look for Content-Type: text/html section
                $html = '';
                if (preg_match('/Content-Type:\s*text\/html[^\n]*\n(?:Content-Transfer-Encoding:[^\n]*\n)?\n(<!DOCTYPE.*?<\/html>)/si', $body, $htmlMatch)) {
                    $html = $htmlMatch[1];
                } elseif (preg_match('/(<!DOCTYPE.*?<\/html>)/si', $body, $htmlMatch)) {
                    // Fallback: just find the HTML document
                    $html = $htmlMatch[1];
                }

                // Extract plain text for preview
                $plainBody = '';
                if (preg_match('/Content-Type:\s*text\/plain[^\n]*\n(?:Content-Transfer-Encoding:[^\n]*\n)?\n(.*?)(?=--[a-zA-Z0-9_]+|$)/si', $body, $textMatch)) {
                    $plainBody = trim($textMatch[1]);
                    $plainBody = preg_replace('/\s+/', ' ', $plainBody);
                    $plainBody = substr($plainBody, 0, 200) . (strlen($plainBody) > 200 ? '...' : '');
                }

                $emails[] = [
                    'timestamp' => $timestamp,
                    'to' => $to,
                    'from' => $from,
                    'subject' => $subject,
                    'body' => $plainBody,
                    'html' => $html,
                ];
            }

            // Reverse to show newest first
            $emails = array_reverse($emails);
        }

        return view('host.settings.advanced.email-logs', compact('emails'));
    }

    public function clearEmailLogs()
    {
        // Only allow in local environment
        if (!app()->environment('local')) {
            abort(404);
        }

        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return back()->with('success', 'Email logs cleared.');
    }
}
