<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Determine instructor_id (auto-create if needed for instructor role)
        $instructorId = $invitation->instructor_id;

        if ($existingUser) {
            // Existing user - verify password and link to host
            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors(['password' => 'The password is incorrect.']);
            }

            // Auto-create instructor record if role is instructor and no instructor_id
            if ($invitation->role === 'instructor' && !$instructorId) {
                $instructorId = $this->ensureInstructorRecord(
                    $invitation->host_id,
                    $existingUser->full_name,
                    $invitation->email
                );
            }

            // Update user to be linked to this host (for backwards compatibility)
            $existingUser->update([
                'host_id' => $invitation->host_id,
                'role' => $invitation->role,
                'permissions' => $invitation->permissions,
                'status' => User::STATUS_ACTIVE,
                'instructor_id' => $instructorId,
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

            // Auto-create instructor record if role is instructor and no instructor_id
            if ($invitation->role === 'instructor' && !$instructorId) {
                $fullName = trim($request->first_name . ' ' . $request->last_name);
                $instructorId = $this->ensureInstructorRecord(
                    $invitation->host_id,
                    $fullName,
                    $invitation->email
                );
            }

            $user = User::create([
                'host_id' => $invitation->host_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'role' => $invitation->role,
                'permissions' => $invitation->permissions,
                'status' => User::STATUS_ACTIVE,
                'instructor_id' => $instructorId,
                'is_instructor' => $invitation->role === 'instructor',
                'email_verified_at' => now(), // Auto-verify since they received the email
                'last_login_at' => now(),
            ]);
        }

        // Link the instructor record to the user (bidirectional)
        if ($instructorId) {
            $instructor = Instructor::find($instructorId);
            $updateData = [
                'user_id' => $user->id,
                'name' => $user->full_name, // Update name from user's actual name
            ];
            // Only set to active if profile is complete (has required employment details)
            if ($instructor && $instructor->isProfileComplete()) {
                $updateData['status'] = Instructor::STATUS_ACTIVE;
                $updateData['is_active'] = true;
            }
            Instructor::where('id', $instructorId)->update($updateData);
        }

        // Add to host_user pivot table for multi-studio support
        $existingMembership = DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $invitation->host_id)
            ->exists();

        if (!$existingMembership) {
            $hasOtherHosts = DB::table('host_user')
                ->where('user_id', $user->id)
                ->exists();

            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $invitation->host_id,
                'role' => $invitation->role,
                'permissions' => json_encode($invitation->permissions),
                'instructor_id' => $instructorId,
                'is_primary' => !$hasOtherHosts,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Update existing membership with instructor_id if needed
            if ($instructorId) {
                DB::table('host_user')
                    ->where('user_id', $user->id)
                    ->where('host_id', $invitation->host_id)
                    ->update([
                        'instructor_id' => $instructorId,
                        'updated_at' => now(),
                    ]);
            }
        }

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        // Set the current host in session
        $user->setCurrentHost($invitation->host);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$invitation->host->studio_name}!");
    }

    /**
     * Ensure an instructor record exists for the given host/email
     * Returns the instructor_id
     */
    private function ensureInstructorRecord(int $hostId, string $name, string $email): int
    {
        // Check if instructor already exists with this email
        $existingInstructor = Instructor::where('host_id', $hostId)
            ->where('email', $email)
            ->first();

        if ($existingInstructor) {
            return $existingInstructor->id;
        }

        // Create new instructor record - inactive until employment details are filled
        $instructor = Instructor::create([
            'host_id' => $hostId,
            'name' => $name,
            'email' => $email,
            'is_active' => false, // Inactive until employment details are filled
            'is_visible' => false,
            'status' => Instructor::STATUS_PENDING,
        ]);

        return $instructor->id;
    }
}
