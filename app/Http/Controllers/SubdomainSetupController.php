<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SubdomainSetupController extends Controller
{
    /**
     * Show the branded invite setup page
     */
    public function showInvite(Request $request, string $subdomain, string $token)
    {
        $host = $request->attributes->get('subdomain_host');
        $invitation = TeamInvitation::where('token', $token)->first();

        // Token doesn't exist
        if (!$invitation) {
            return view('subdomain.errors.invalid', [
                'host' => $host,
            ]);
        }

        // Token belongs to a different studio
        if ($invitation->host_id !== $host->id) {
            $correctHost = $invitation->host;
            $bookingDomain = config('app.booking_domain');
            $correctUrl = "https://{$correctHost->subdomain}.{$bookingDomain}/setup/invite/{$token}";

            return view('subdomain.errors.wrong-studio', [
                'host' => $host,
                'correctHost' => $correctHost,
                'correctUrl' => $correctUrl,
            ]);
        }

        // Already accepted
        if ($invitation->status === TeamInvitation::STATUS_ACCEPTED) {
            return view('subdomain.errors.invalid', [
                'host' => $host,
                'error' => 'This invitation has already been accepted.',
            ]);
        }

        // Revoked
        if ($invitation->status === TeamInvitation::STATUS_REVOKED) {
            return view('subdomain.errors.invalid', [
                'host' => $host,
                'error' => 'This invitation has been revoked.',
            ]);
        }

        // Expired
        if ($invitation->isExpired()) {
            return view('subdomain.errors.expired', [
                'host' => $host,
                'invitation' => $invitation,
            ]);
        }

        // Check if there's an existing user with this email
        $existingUser = User::where('email', $invitation->email)->first();

        // Check if user is already a member of this studio
        if ($existingUser) {
            $alreadyMember = DB::table('host_user')
                ->where('user_id', $existingUser->id)
                ->where('host_id', $host->id)
                ->exists();

            if ($alreadyMember) {
                return view('subdomain.errors.already-member', [
                    'host' => $host,
                    'user' => $existingUser,
                ]);
            }
        }

        return view('subdomain.invite-setup', [
            'host' => $host,
            'invitation' => $invitation,
            'existingUser' => $existingUser,
        ]);
    }

    /**
     * Accept the invitation and create/link user account
     */
    public function acceptInvite(Request $request, string $subdomain, string $token)
    {
        $host = $request->attributes->get('subdomain_host');
        $invitation = TeamInvitation::where('token', $token)->first();

        // Validate invitation
        if (!$invitation || !$invitation->isPending() || $invitation->host_id !== $host->id) {
            return redirect()->back()
                ->with('error', 'This invitation is no longer valid.');
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            // Existing user - verify password
            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors(['password' => 'The password is incorrect.']);
            }

            $user = $existingUser;
        } else {
            // New user - create account
            $request->validate([
                'first_name' => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/'],
                'last_name' => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'host_id' => $host->id, // Set primary host
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'role' => $invitation->role,
                'permissions' => $invitation->permissions,
                'status' => User::STATUS_ACTIVE,
                'instructor_id' => $invitation->instructor_id,
                'is_instructor' => $invitation->role === 'instructor',
                'email_verified_at' => now(), // Auto-verify since they received the email
            ]);
        }

        // Check if already a member (shouldn't happen due to earlier check, but just in case)
        $existingMembership = DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $host->id)
            ->exists();

        if (!$existingMembership) {
            // Determine if this should be primary
            $hasOtherHosts = DB::table('host_user')
                ->where('user_id', $user->id)
                ->exists();

            // Add to host_user pivot table
            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $host->id,
                'role' => $invitation->role,
                'permissions' => json_encode($invitation->permissions),
                'instructor_id' => $invitation->instructor_id,
                'is_primary' => !$hasOtherHosts, // Primary only if first host
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Log the user in
        Auth::login($user);

        // Set the current host in session
        $user->setCurrentHost($host);

        // Redirect to dashboard on main app
        $appUrl = config('app.url');

        return redirect($appUrl . '/dashboard')
            ->with('success', "Welcome to {$host->studio_name}!");
    }
}
