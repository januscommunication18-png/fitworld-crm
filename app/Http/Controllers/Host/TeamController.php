<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\Instructor;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Show Users & Roles page
     */
    public function users(Request $request)
    {
        $host = auth()->user()->currentHost();
        $search = $request->get('search');

        // Use teamMembers() to get users from pivot table (supports multi-studio)
        $usersQuery = $host->teamMembers()
            ->withTrashed()
            ->orderByRaw("FIELD(host_user.role, 'owner', 'admin', 'staff', 'instructor')")
            ->orderBy('first_name');

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(10)->withQueryString();

        $invitationsQuery = TeamInvitation::where('host_id', $host->id)
            ->whereIn('status', [TeamInvitation::STATUS_PENDING, TeamInvitation::STATUS_EXPIRED])
            ->with('invitedBy')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $invitationsQuery->where('email', 'like', "%{$search}%");
        }

        $invitations = $invitationsQuery->paginate(10, ['*'], 'invitations_page')->withQueryString();

        // Get role counts from pivot table (unaffected by search/pagination)
        $roleCounts = $host->teamMembers()
            ->selectRaw('host_user.role, count(*) as count')
            ->groupBy('host_user.role')
            ->pluck('count', 'role')
            ->toArray();

        return view('host.settings.team.users.index', [
            'users' => $users,
            'invitations' => $invitations,
            'roles' => User::getRoles(),
            'statuses' => User::getStatuses(),
            'search' => $search,
            'roleCounts' => $roleCounts,
        ]);
    }

    /**
     * Show invite user form
     */
    public function showInvite()
    {
        return view('host.settings.team.users.invite', [
            'roles' => User::getRoles(),
            'groupedPermissions' => User::getAllPermissions(),
        ]);
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return redirect()->route('settings.team.users')
                ->with('error', 'Cannot edit the owner.');
        }

        return view('host.settings.team.users.edit', [
            'user' => $user,
            'roles' => User::getRoles(),
            'groupedPermissions' => User::getAllPermissions(),
        ]);
    }

    /**
     * Update user role and permissions
     */
    public function updateUser(Request $request, User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return back()->with('error', 'Cannot modify the owner.');
        }

        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,instructor',
            'permissions' => 'nullable|array',
        ]);

        $instructorId = $user->instructor_id;
        $wasInstructor = $user->role === User::ROLE_INSTRUCTOR || $user->is_instructor;
        $isNowInstructor = $validated['role'] === User::ROLE_INSTRUCTOR;

        // Auto-create instructor record when role changes to instructor
        if ($isNowInstructor && !$instructorId) {
            // Check if instructor already exists with this email
            $existingInstructor = Instructor::where('host_id', $host->id)
                ->where('email', $user->email)
                ->first();

            if ($existingInstructor) {
                $instructorId = $existingInstructor->id;
                // Link user to instructor
                $existingInstructor->update(['user_id' => $user->id]);
            } else {
                // Create new instructor record - inactive until employment details are filled
                $instructor = Instructor::create([
                    'host_id' => $host->id,
                    'user_id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'is_active' => false, // Inactive until employment details are filled
                    'is_visible' => false,
                    'status' => Instructor::STATUS_PENDING,
                ]);
                $instructorId = $instructor->id;
            }
        }

        // Update user
        $user->update([
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? null,
            'instructor_id' => $instructorId,
            'is_instructor' => $isNowInstructor,
        ]);

        // Update host_user pivot table
        \DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $host->id)
            ->update([
                'role' => $validated['role'],
                'permissions' => json_encode($validated['permissions'] ?? null),
                'instructor_id' => $instructorId,
                'updated_at' => now(),
            ]);

        $successMessage = 'User updated successfully.';
        if ($isNowInstructor && !$wasInstructor) {
            $successMessage .= ' Instructor profile needs to be completed before they can be assigned to classes.';
        }

        return redirect()->route('settings.team.users')
            ->with('success', $successMessage);
    }

    /**
     * Invite a new team member
     */
    public function invite(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where('host_id', $host->id),
                Rule::unique('team_invitations', 'email')
                    ->where('host_id', $host->id)
                    ->where('status', TeamInvitation::STATUS_PENDING),
            ],
            'role' => 'required|in:admin,staff,instructor',
            'permissions' => 'nullable|array',
            'instructor_id' => 'nullable|exists:instructors,id',
        ], [
            'email.unique' => 'This email is already registered or has a pending invitation.',
        ]);

        $instructorId = $validated['instructor_id'] ?? null;

        // Auto-create instructor record when inviting with instructor role
        if ($validated['role'] === User::ROLE_INSTRUCTOR && !$instructorId) {
            // Check if instructor already exists with this email
            $existingInstructor = Instructor::where('host_id', $host->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existingInstructor) {
                $instructorId = $existingInstructor->id;
            } else {
                // Create a basic instructor record - inactive until profile is completed
                $instructor = Instructor::create([
                    'host_id' => $host->id,
                    'name' => $validated['email'], // Use email as placeholder name
                    'email' => $validated['email'],
                    'is_active' => false, // Inactive until employment details are filled
                    'is_visible' => false,
                    'status' => Instructor::STATUS_PENDING,
                ]);
                $instructorId = $instructor->id;
            }
        }

        $invitation = TeamInvitation::create([
            'host_id' => $host->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? null,
            'instructor_id' => $instructorId,
            'token' => TeamInvitation::generateToken(),
            'status' => TeamInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
            'invited_by' => auth()->id(),
        ]);

        // Send invitation email
        Mail::to($invitation->email)->send(new TeamInvitationMail(
            $invitation,
            $host->studio_name ?? 'Our Studio',
            auth()->user()->full_name
        ));

        return redirect()->route('settings.team.users')
            ->with('success', 'Invitation sent to ' . $validated['email']);
    }

    /**
     * Resend invitation
     */
    public function resendInvite(TeamInvitation $invitation)
    {
        $this->authorizeInvitation($invitation);

        if (!$invitation->canResend()) {
            return back()->with('error', 'Cannot resend this invitation.');
        }

        $invitation->regenerate();

        // Send invitation email
        $host = auth()->user()->currentHost();
        Mail::to($invitation->email)->send(new TeamInvitationMail(
            $invitation,
            $host->studio_name ?? 'Our Studio',
            auth()->user()->full_name
        ));

        return back()->with('success', 'Invitation resent to ' . $invitation->email);
    }

    /**
     * Revoke invitation
     */
    public function revokeInvite(TeamInvitation $invitation)
    {
        $this->authorizeInvitation($invitation);

        $invitation->revoke();

        return back()->with('success', 'Invitation revoked.');
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, User $user)
    {
        $this->authorizeUser($user);

        // Cannot change owner role
        if ($user->isOwner()) {
            return back()->with('error', 'Cannot change the owner\'s role.');
        }

        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,instructor',
        ]);

        $instructorId = $user->instructor_id;
        $isNowInstructor = $validated['role'] === User::ROLE_INSTRUCTOR;

        // Auto-create instructor record when role changes to instructor
        if ($isNowInstructor && !$instructorId) {
            // Check if instructor already exists with this email
            $existingInstructor = Instructor::where('host_id', $host->id)
                ->where('email', $user->email)
                ->first();

            if ($existingInstructor) {
                $instructorId = $existingInstructor->id;
                // Link user to instructor
                $existingInstructor->update(['user_id' => $user->id]);
            } else {
                // Create new instructor record - inactive until employment details are filled
                $instructor = Instructor::create([
                    'host_id' => $host->id,
                    'user_id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'is_active' => false, // Inactive until employment details are filled
                    'is_visible' => false,
                    'status' => Instructor::STATUS_PENDING,
                ]);
                $instructorId = $instructor->id;
            }
        }

        // Update user
        $user->update([
            'role' => $validated['role'],
            'instructor_id' => $instructorId,
            'is_instructor' => $isNowInstructor,
        ]);

        // Update host_user pivot table
        \DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $host->id)
            ->update([
                'role' => $validated['role'],
                'instructor_id' => $instructorId,
                'updated_at' => now(),
            ]);

        $successMessage = 'Role updated for ' . $user->full_name;
        if ($isNowInstructor && !$instructorId) {
            $successMessage .= '. Instructor profile needs to be completed.';
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Deactivate user
     */
    public function deactivate(User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return back()->with('error', 'Cannot deactivate the owner.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate yourself.');
        }

        $user->update(['status' => User::STATUS_DEACTIVATED]);

        return back()->with('success', $user->full_name . ' has been deactivated.');
    }

    /**
     * Reactivate user
     */
    public function reactivate(User $user)
    {
        $this->authorizeUser($user);

        $user->update(['status' => User::STATUS_ACTIVE]);

        return back()->with('success', $user->full_name . ' has been reactivated.');
    }

    /**
     * Suspend user
     */
    public function suspend(User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return back()->with('error', 'Cannot suspend the owner.');
        }

        $user->update(['status' => User::STATUS_SUSPENDED]);

        return back()->with('success', $user->full_name . ' has been suspended.');
    }

    /**
     * Remove user (soft delete)
     */
    public function remove(User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return back()->with('error', 'Cannot remove the owner.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot remove yourself.');
        }

        $user->delete();

        return back()->with('success', $user->full_name . ' has been removed from the team.');
    }

    /**
     * Show Instructors page
     */
    public function instructors(Request $request)
    {
        $host = auth()->user()->currentHost();
        $search = $request->get('search');

        $instructorsQuery = $host->instructors()
            ->with('user')
            ->orderBy('name');

        if ($search) {
            $instructorsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $instructors = $instructorsQuery->paginate(10)->withQueryString();

        // Get pending invitations for instructors
        $pendingInvitationsQuery = TeamInvitation::where('host_id', $host->id)
            ->whereNotNull('instructor_id')
            ->whereIn('status', [TeamInvitation::STATUS_PENDING, TeamInvitation::STATUS_EXPIRED])
            ->with(['invitedBy', 'instructor'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $pendingInvitationsQuery->where('email', 'like', "%{$search}%");
        }

        $pendingInvitations = $pendingInvitationsQuery->paginate(10, ['*'], 'invitations_page')->withQueryString();

        // Get status counts (unaffected by search/pagination)
        $statusCounts = [
            'active' => $host->instructors()->where('is_active', true)->count(),
            'inactive' => $host->instructors()->where('is_active', false)->count(),
            'with_account' => $host->instructors()->whereNotNull('user_id')->count(),
            'pending_invite' => TeamInvitation::where('host_id', $host->id)
                ->whereNotNull('instructor_id')
                ->where('status', TeamInvitation::STATUS_PENDING)
                ->count(),
        ];

        return view('host.settings.team.instructors.index', [
            'instructors' => $instructors,
            'pendingInvitations' => $pendingInvitations,
            'statusCounts' => $statusCounts,
            'search' => $search,
        ]);
    }

    /**
     * Show create instructor form
     */
    public function createInstructor()
    {
        return view('host.settings.team.instructors.create', [
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
        ]);
    }

    /**
     * Show edit instructor form
     */
    public function editInstructor(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $missingFields = $instructor->isProfileComplete() ? [] : $instructor->getMissingProfileFields();

        return view('host.settings.team.instructors.edit', [
            'instructor' => $instructor,
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
            'missingFields' => $missingFields,
        ]);
    }

    /**
     * Store new instructor
     */
    public function storeInstructor(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications' => 'nullable|string|max:1000',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            // Employment Details
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99|required_with:rate_type',
            'compensation_notes' => 'nullable|string|max:1000',
            // Workload & Allocation
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            // Working Days
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            // Availability
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i|after:availability_default_from',
            'availability_by_day' => 'nullable|array',
        ]);

        $validated['host_id'] = $host->id;
        $validated['is_visible'] = $request->boolean('is_visible');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['status'] = Instructor::STATUS_ACTIVE;

        // Check if a user already exists with this email (auto-link)
        $existingUser = null;
        if (!empty($validated['email'])) {
            $existingUser = $host->teamMembers()
                ->where('email', $validated['email'])
                ->first();

            if ($existingUser) {
                $validated['user_id'] = $existingUser->id;
            }
        }

        $instructor = Instructor::create($validated);

        $successMessage = 'Instructor added successfully.';

        // If user was auto-linked, update user's instructor_id and role
        if ($existingUser) {
            $existingUser->update([
                'instructor_id' => $instructor->id,
                'role' => User::ROLE_INSTRUCTOR,
                'is_instructor' => true,
            ]);

            // Update host_user pivot table
            \DB::table('host_user')
                ->where('user_id', $existingUser->id)
                ->where('host_id', $host->id)
                ->update([
                    'role' => User::ROLE_INSTRUCTOR,
                    'instructor_id' => $instructor->id,
                    'updated_at' => now(),
                ]);

            $successMessage = 'Instructor added and linked to existing user ' . $existingUser->full_name . '.';
        } elseif (!empty($validated['email'])) {
            // Auto-send invitation if email is provided and no existing user
            $invitation = TeamInvitation::create([
                'host_id' => $host->id,
                'email' => $validated['email'],
                'role' => User::ROLE_INSTRUCTOR,
                'instructor_id' => $instructor->id,
                'token' => TeamInvitation::generateToken(),
                'status' => TeamInvitation::STATUS_PENDING,
                'expires_at' => now()->addDays(7),
                'invited_by' => auth()->id(),
            ]);

            // Send invitation email
            Mail::to($invitation->email)->send(new TeamInvitationMail(
                $invitation,
                $host->studio_name ?? 'Our Studio',
                auth()->user()->full_name
            ));

            $successMessage = 'Instructor added and invitation sent to ' . $validated['email'] . '.';
        }

        return redirect()->route('settings.team.instructors')
            ->with('success', $successMessage);
    }

    /**
     * Update instructor
     */
    public function updateInstructor(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $wasPending = $instructor->status === Instructor::STATUS_PENDING;
        $wasInactive = !$instructor->is_active;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications' => 'nullable|string|max:1000',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            // Employment Details
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99|required_with:rate_type',
            'compensation_notes' => 'nullable|string|max:1000',
            // Workload & Allocation
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            // Working Days
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            // Availability
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i|after:availability_default_from',
            'availability_by_day' => 'nullable|array',
        ]);

        $validated['is_visible'] = $request->boolean('is_visible');
        $validated['is_active'] = $request->boolean('is_active');

        $instructor->update($validated);

        // Refresh the model to check profile completeness with updated values
        $instructor->refresh();

        // Auto-activate if profile is now complete and was pending
        $successMessage = 'Instructor updated successfully.';
        if ($instructor->isProfileComplete()) {
            if ($wasPending || ($wasInactive && $instructor->status === Instructor::STATUS_PENDING)) {
                $instructor->update([
                    'status' => Instructor::STATUS_ACTIVE,
                    'is_active' => true,
                ]);
                $successMessage = 'Instructor profile completed and activated successfully.';
            }
        } else {
            // Profile incomplete - show what's missing
            $missing = $instructor->getMissingProfileFields();
            if (!empty($missing) && $instructor->status === Instructor::STATUS_PENDING) {
                $successMessage = 'Instructor updated. To activate, please complete: ' . implode(', ', $missing) . '.';
            }
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Upload instructor photo
     */
    public function uploadInstructorPhoto(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        // Delete old photo (try-catch for cloud storage compatibility)
        if ($instructor->photo_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        $path = $request->file('photo')->storePublicly('instructors/' . $instructor->id, config('filesystems.uploads'));
        $instructor->update(['photo_path' => $path]);

        return response()->json([
            'success' => true,
            'path' => Storage::disk(config('filesystems.uploads'))->url($path),
        ]);
    }

    /**
     * Remove instructor photo
     */
    public function removeInstructorPhoto(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        if ($instructor->photo_path && Storage::disk(config('filesystems.uploads'))->exists($instructor->photo_path)) {
            Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
        }

        $instructor->update(['photo_path' => null]);

        return response()->json(['success' => true]);
    }

    /**
     * Send invite to instructor
     */
    public function inviteInstructor(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        if ($instructor->hasAccount()) {
            return back()->with('error', 'Instructor already has an account.');
        }

        if (!$instructor->email) {
            return back()->with('error', 'Instructor must have an email to receive an invitation.');
        }

        if ($instructor->hasPendingInvitation()) {
            return back()->with('error', 'Instructor already has a pending invitation.');
        }

        $invitation = TeamInvitation::create([
            'host_id' => $instructor->host_id,
            'email' => $instructor->email,
            'role' => User::ROLE_INSTRUCTOR,
            'instructor_id' => $instructor->id,
            'token' => TeamInvitation::generateToken(),
            'status' => TeamInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
            'invited_by' => auth()->id(),
        ]);

        // Send invitation email
        $host = auth()->user()->currentHost();
        Mail::to($invitation->email)->send(new TeamInvitationMail(
            $invitation,
            $host->studio_name ?? 'Our Studio',
            auth()->user()->full_name
        ));

        return back()->with('success', 'Invitation sent to ' . $instructor->email);
    }

    /**
     * Delete instructor
     */
    public function deleteInstructor(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        if (!$instructor->canDeactivate()) {
            return back()->with('error', 'Cannot delete instructor with scheduled classes. Please reassign classes first.');
        }

        // Delete photo if exists
        if ($instructor->photo_path && Storage::disk(config('filesystems.uploads'))->exists($instructor->photo_path)) {
            Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
        }

        // Unlink user if linked
        if ($instructor->user_id) {
            User::where('id', $instructor->user_id)->update(['instructor_id' => null]);
        }

        $instructor->delete();

        return back()->with('success', 'Instructor deleted successfully.');
    }

    /**
     * Show Permissions page
     */
    public function permissions(Request $request)
    {
        $host = auth()->user()->currentHost();

        // Include all users with login accounts (including soft-deleted)
        // No pagination - real-time client-side search
        // Use teamMembers() to get users from pivot table (supports multi-studio)
        $users = $host->teamMembers()
            ->withTrashed()
            ->where('host_user.role', '!=', User::ROLE_OWNER)
            ->orderByRaw("FIELD(host_user.role, 'admin', 'staff', 'instructor')")
            ->orderBy('first_name')
            ->get();

        // Get grouped permissions for the edit page
        $groupedPermissions = User::getAllPermissions();

        // Flatten nested permissions array for the table
        $allPermissions = [];
        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $key => $label) {
                $allPermissions[$key] = $label;
            }
        }

        // Get role counts from pivot
        $roleCounts = $users->groupBy(fn($user) => $user->pivot->role)->map->count()->toArray();

        return view('host.settings.team.permissions.index', [
            'users' => $users,
            'allPermissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
            'roles' => User::getRoles(),
            'roleCounts' => $roleCounts,
        ]);
    }

    /**
     * Show edit permissions form
     */
    public function editPermissions(int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return redirect()->route('settings.team.permissions')
                ->with('error', 'Cannot edit owner permissions.');
        }

        if ($user->trashed()) {
            return redirect()->route('settings.team.permissions')
                ->with('error', 'Cannot edit permissions for removed users.');
        }

        return view('host.settings.team.permissions.edit', [
            'user' => $user,
            'groupedPermissions' => User::getAllPermissions(),
        ]);
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return back()->with('error', 'Cannot modify owner permissions.');
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
        ]);

        $user->update(['permissions' => $validated['permissions'] ?? null]);

        return back()->with('success', 'Permissions updated for ' . $user->full_name);
    }

    /**
     * Authorize invitation belongs to host
     */
    private function authorizeInvitation(TeamInvitation $invitation): void
    {
        $currentHost = auth()->user()->currentHost();
        if (!$currentHost || $invitation->host_id !== $currentHost->id) {
            abort(403);
        }
    }

    /**
     * Authorize user belongs to host (checks pivot table for multi-studio support)
     */
    private function authorizeUser(User $user): void
    {
        $currentHost = auth()->user()->currentHost();
        if (!$currentHost) {
            abort(403);
        }

        // Check if user belongs to the current host via pivot table
        $belongsToHost = \DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $currentHost->id)
            ->exists();

        if (!$belongsToHost) {
            abort(403);
        }
    }

    /**
     * Authorize instructor belongs to host
     */
    private function authorizeInstructor(Instructor $instructor): void
    {
        $currentHost = auth()->user()->currentHost();
        if (!$currentHost || $instructor->host_id !== $currentHost->id) {
            abort(403);
        }
    }
}
