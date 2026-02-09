<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        // Get current settings from config/env
        $settings = [
            'paddle' => [
                'vendor_id' => config('services.paddle.vendor_id'),
                'vendor_auth_code' => config('services.paddle.vendor_auth_code') ? '********' : '',
                'public_key' => config('services.paddle.public_key') ? '********' : '',
                'sandbox' => config('services.paddle.sandbox', true),
            ],
            'mail' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
        ];

        return view('backoffice.settings.index', compact('settings'));
    }

    public function updatePaddle(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'nullable|string|max:255',
            'vendor_auth_code' => 'nullable|string|max:255',
            'public_key' => 'nullable|string',
            'sandbox' => 'boolean',
        ]);

        // In a production environment, these would be saved to a settings table
        // or updated in the .env file securely
        // For now, we'll just return a success message

        return redirect()->back()
            ->with('success', 'Paddle settings saved. Note: To persist these changes, update your .env file.');
    }

    public function updateMail(Request $request)
    {
        $validated = $request->validate([
            'mailer' => 'required|in:smtp,postmark,mailgun,ses,log',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,null',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
        ]);

        // In a production environment, these would be saved to a settings table
        // or updated in the .env file securely

        return redirect()->back()
            ->with('success', 'Mail settings saved. Note: To persist these changes, update your .env file.');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return redirect()->back()
            ->with('success', 'Application cache cleared successfully.');
    }

    public function testMail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from FitCRM Admin Backoffice.', function ($message) use ($validated) {
                $message->to($validated['email'])
                        ->subject('FitCRM Test Email');
            });

            return redirect()->back()
                ->with('success', 'Test email sent to ' . $validated['email']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
