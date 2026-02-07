<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Show the invitation acceptance page
     */
    public function show(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->first();

        if (!$invitation) {
            return view('auth.invitation-invalid', [
                'error' => 'This invitation link is invalid.',
            ]);
        }

        if ($invitation->status === TeamInvitation::STATUS_ACCEPTED) {
            return view('auth.invitation-invalid', [
                'error' => 'This invitation has already been accepted.',
            ]);
        }

        if ($invitation->status === TeamInvitation::STATUS_REVOKED) {
            return view('auth.invitation-invalid', [
                'error' => 'This invitation has been revoked.',
            ]);
        }

        if ($invitation->isExpired()) {
            return view('auth.invitation-invalid', [
                'error' => 'This invitation has expired. Please ask to be invited again.',
            ]);
        }

        // Check if there's an existing user with this email
        $existingUser = User::where('email', $invitation->email)->first();

        return view('auth.invitation-accept', [
            'invitation' => $invitation,
            'existingUser' => $existingUser,
            'host' => $invitation->host,
        ]);
    }

    /**
     * Accept the invitation
     */
    public function accept(Request $request, string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isPending()) {
            return redirect()->route('login')
                ->with('error', 'This invitation is no longer valid.');
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            // Existing user - verify password and link to host
            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors(['password' => 'The password is incorrect.']);
            }

            // Update user to be linked to this host
            $existingUser->update([
                'host_id' => $invitation->host_id,
                'role' => $invitation->role,
                'permissions' => $invitation->permissions,
                'status' => User::STATUS_ACTIVE,
                'instructor_id' => $invitation->instructor_id,
                'is_instructor' => $invitation->role === 'instructor',
                'last_login_at' => now(),
            ]);

            $user = $existingUser;
        } else {
            // New user - create account
            $request->validate([
                'first_name' => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/'],
                'last_name' => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'host_id' => $invitation->host_id,
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
                'last_login_at' => now(),
            ]);
        }

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$invitation->host->studio_name}!");
    }
}
