<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Redirect /settings to first accessible settings page based on permissions
    public function index()
    {
        $user = auth()->user();

        // Check permissions in priority order and redirect to first accessible page
        if ($user->hasPermission('studio.profile')) {
            return redirect()->route('settings.studio.profile');
        }

        if ($user->hasPermission('studio.locations')) {
            return redirect()->route('settings.locations.index');
        }

        if ($user->hasPermission('team.view') || $user->hasPermission('team.manage')) {
            return redirect()->route('settings.team.users');
        }

        if ($user->hasPermission('team.instructors')) {
            return redirect()->route('settings.team.instructors');
        }

        if ($user->hasPermission('team.permissions')) {
            return redirect()->route('settings.team.permissions');
        }

        if ($user->hasPermission('payments.stripe')) {
            return redirect()->route('settings.payments.settings');
        }

        if ($user->hasPermission('billing.plan')) {
            return redirect()->route('settings.billing.plan');
        }

        if ($user->hasPermission('billing.invoices')) {
            return redirect()->route('settings.billing.invoices');
        }

        // Everyone can access their own profile
        return redirect()->route('settings.profile');
    }

    // ─────────────────────────────────────────────────────────────
    // My Profile (accessible to all authenticated users)
    // ─────────────────────────────────────────────────────────────

    public function myProfile()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Get instructor profile if user is linked to one
        $instructor = null;
        if ($user->instructor_id) {
            $instructor = $user->instructor;
        }

        // Get the user's role and permissions for current host
        $membership = $user->hosts()->where('hosts.id', $host->id)->first();
        $role = $membership?->pivot?->role ?? $user->role;
        $permissions = $membership?->pivot?->permissions ?? $user->permissions;

        return view('host.settings.profile.index', compact('user', 'host', 'instructor', 'role', 'permissions'));
    }

    public function updateMyProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[^\d]*$/',
            'last_name' => 'required|string|max:255|regex:/^[^\d]*$/',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
        ], [
            'first_name.regex' => 'First name cannot contain numbers.',
            'last_name.regex' => 'Last name cannot contain numbers.',
        ]);

        $user->update($validated);

        // If user has linked instructor profile, update that too
        if ($user->instructor_id && $user->instructor) {
            $user->instructor->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->instructor->phone,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    public function updateMyPassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function uploadMyPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = auth()->user();

        // Store the file
        $path = $request->file('photo')->store('profile-photos', 'public');

        // Delete old photo if exists
        if ($user->profile_photo && \Storage::disk('public')->exists($user->profile_photo)) {
            \Storage::disk('public')->delete($user->profile_photo);
        }

        $user->update(['profile_photo' => $path]);

        // Also update instructor photo if linked
        if ($user->instructor_id && $user->instructor) {
            if ($user->instructor->profile_photo && \Storage::disk('public')->exists($user->instructor->profile_photo)) {
                \Storage::disk('public')->delete($user->instructor->profile_photo);
            }
            $user->instructor->update(['profile_photo' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully.',
            'photo_url' => asset('storage/' . $path),
        ]);
    }

    public function removeMyPhoto()
    {
        $user = auth()->user();

        if ($user->profile_photo && \Storage::disk('public')->exists($user->profile_photo)) {
            \Storage::disk('public')->delete($user->profile_photo);
        }

        $user->update(['profile_photo' => null]);

        // Also remove instructor photo if linked
        if ($user->instructor_id && $user->instructor) {
            $user->instructor->update(['profile_photo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo removed successfully.',
        ]);
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
        if ($host->logo_path && \Storage::disk(config('filesystems.uploads'))->exists($host->logo_path)) {
            \Storage::disk(config('filesystems.uploads'))->delete($host->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos/' . $host->id, config('filesystems.uploads'));
        $host->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'logo_url' => \Storage::disk(config('filesystems.uploads'))->url($path),
        ]);
    }

    public function uploadStudioCover(Request $request)
    {
        $request->validate([
            'cover' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Delete old cover if exists
        if ($host->cover_image_path && \Storage::disk(config('filesystems.uploads'))->exists($host->cover_image_path)) {
            \Storage::disk(config('filesystems.uploads'))->delete($host->cover_image_path);
        }

        // Store new cover
        $path = $request->file('cover')->store('covers/' . $host->id, config('filesystems.uploads'));
        $host->update(['cover_image_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Cover image uploaded successfully',
            'cover_url' => \Storage::disk(config('filesystems.uploads'))->url($path),
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
            'currencies' => 'nullable|array',
            'currencies.*' => 'string|size:3',
        ]);

        $host->update(['currencies' => $validated['currencies'] ?? []]);

        return response()->json([
            'success' => true,
            'message' => 'Currencies updated successfully',
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
        $host = auth()->user()->host;
        return view('host.settings.payments.settings', compact('host'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'accept_cards' => 'boolean',
            'accept_cash' => 'boolean',
            'currency' => 'nullable|string|size:3',
            'send_receipts' => 'boolean',
            'receipt_footer' => 'nullable|string|max:500',
        ]);

        $host->update(['payment_settings' => $validated]);

        return response()->json([
            'success' => true,
            'message' => 'Payment settings updated successfully',
        ]);
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
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

        return view('host.settings.advanced.export');
    }

    public function auditLogs()
    {
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

        return view('host.settings.advanced.audit');
    }

    public function dangerZone()
    {
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

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
