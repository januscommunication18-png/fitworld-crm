<?php

namespace App\Http\Controllers\Backoffice\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetMail;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Show the change password form
     */
    public function showChangePassword(): View
    {
        return view('backoffice.auth.change-password');
    }

    /**
     * Handle password change
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[0-9]/',      // at least one number
                'regex:/[@$!%*?&#]/', // at least one special character
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character (@$!%*?&#).',
        ]);

        $admin = Auth::guard('admin')->user();

        // Verify current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Ensure new password is different
        if (Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'New password must be different from current password.']);
        }

        // Update password
        $admin->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('backoffice.dashboard')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Handle forgot password - generate random password and send email
     */
    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);
        $admin = AdminUser::where('email', $email)->first();

        // Always show same message for security (don't reveal if email exists)
        $successMessage = 'If this email is registered, you will receive a new password.';

        if (!$admin) {
            return redirect()->route('backoffice.login')
                ->with('info', $successMessage);
        }

        if (!$admin->isActive()) {
            return redirect()->route('backoffice.login')
                ->with('info', $successMessage);
        }

        // Generate random password (12 characters with required complexity)
        $newPassword = $this->generateSecurePassword();

        // Update admin with new password and force change flag
        $admin->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        // Send email with new password
        Mail::to($email)->send(new AdminPasswordResetMail($newPassword, $admin->first_name));

        return redirect()->route('backoffice.login')
            ->with('success', 'A new password has been sent to your email.');
    }

    /**
     * Generate a secure random password that meets requirements
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@$!%*?&#';

        // Ensure at least one of each required type
        $password = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill remaining characters
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }
}
