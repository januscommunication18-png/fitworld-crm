<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\Instructor;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Models\UserNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
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

        // Get instructors without login (no user_id)
        $instructorsWithoutLoginQuery = $host->instructors()
            ->whereNull('user_id')
            ->orderBy('name');

        if ($search) {
            $instructorsWithoutLoginQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $instructorsWithoutLogin = $instructorsWithoutLoginQuery->get();

        // Get pending invitations (shown inline with users)
        $invitationsQuery = TeamInvitation::where('host_id', $host->id)
            ->whereIn('status', [TeamInvitation::STATUS_PENDING, TeamInvitation::STATUS_EXPIRED])
            ->with('invitedBy')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $invitationsQuery->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $invitations = $invitationsQuery->get();

        // Get role counts from pivot table (unaffected by search/pagination)
        $roleCounts = $host->teamMembers()
            ->selectRaw('host_user.role, count(*) as count')
            ->groupBy('host_user.role')
            ->pluck('count', 'role')
            ->toArray();

        // Add instructors without login to the instructor count
        $instructorsWithoutLoginCount = $host->instructors()->whereNull('user_id')->count();

        return view('host.settings.team.users.index', [
            'users' => $users,
            'instructorsWithoutLogin' => $instructorsWithoutLogin,
            'invitations' => $invitations,
            'roles' => User::getRoles(),
            'statuses' => User::getStatuses(),
            'search' => $search,
            'roleCounts' => $roleCounts,
            'instructorsWithoutLoginCount' => $instructorsWithoutLoginCount,
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
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
        ]);
    }

    /**
     * Show user profile
     */
    public function showUser(Request $request, User $user)
    {
        $this->authorizeUser($user);

        $host = auth()->user()->currentHost();

        // Reload user through host relationship to get pivot data
        $userWithPivot = $host->teamMembers()
            ->withTrashed()
            ->where('users.id', $user->id)
            ->first();

        if ($userWithPivot) {
            $user = $userWithPivot;
        }

        // Eager load notes with author and certifications
        $user->load([
            'notes' => function ($query) use ($host) {
                $query->where('host_id', $host->id)->with('author')->orderBy('created_at', 'desc');
            },
            'certifications' => function ($query) use ($host) {
                $query->where('host_id', $host->id)->orderBy('expire_date');
            }
        ]);

        // Get permissions labels
        $allPermissions = [];
        foreach (User::getAllPermissions() as $category => $permissions) {
            foreach ($permissions as $key => $label) {
                $allPermissions[$key] = $label;
            }
        }

        // Get user's role from pivot or user model
        $userRole = $user->pivot->role ?? $user->role;

        // Get user's permissions from pivot or user model
        $userPermissions = $user->getPermissionsForHost($host) ?? $user->permissions;

        // Get linked instructor if this is an instructor role
        $instructor = null;
        if ($userRole === User::ROLE_INSTRUCTOR) {
            $instructor = $user->instructor ?? Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();
        }

        // Get tab from query string
        $tab = $request->get('tab', 'overview');
        if (!in_array($tab, ['overview', 'notes', 'billing'])) {
            $tab = 'overview';
        }

        return view('host.settings.team.users.show', [
            'user' => $user,
            'userPermissions' => $userPermissions,
            'allPermissions' => $allPermissions,
            'instructor' => $instructor,
            'tab' => $tab,
            'noteTypes' => UserNote::getNoteTypes(),
        ]);
    }

    /**
     * Store a note for a user
     */
    public function storeUserNote(Request $request, User $user)
    {
        $this->authorizeUser($user);

        $validated = $request->validate([
            'note_type' => 'required|in:note,performance,hr,incident,system',
            'content' => 'required|string|max:5000',
            'is_visible_to_user' => 'boolean',
        ]);

        $host = auth()->user()->currentHost();

        $note = UserNote::create([
            'subject_user_id' => $user->id,
            'host_id' => $host->id,
            'author_id' => auth()->id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
            'is_visible_to_user' => $validated['is_visible_to_user'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'note' => $note->load('author'),
        ]);
    }

    /**
     * Delete a user note
     */
    public function deleteUserNote(UserNote $note)
    {
        $host = auth()->user()->currentHost();

        // Verify the note belongs to this host
        if ($note->host_id !== $host->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update user profile (bio, social links)
     */
    public function updateUserProfile(Request $request, User $user)
    {
        $this->authorizeUser($user);

        $validated = $request->validate([
            'bio' => 'nullable|string|max:2000',
            'social_links' => 'nullable|array',
            'social_links.instagram' => 'nullable|url|max:255',
            'social_links.facebook' => 'nullable|url|max:255',
            'social_links.twitter' => 'nullable|url|max:255',
            'social_links.linkedin' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
        ]);

        // Update only the fields that were sent
        $updateData = [];

        if ($request->has('bio')) {
            $updateData['bio'] = $validated['bio'];
        }

        if ($request->has('social_links')) {
            // Filter out empty values
            $socialLinks = array_filter($validated['social_links'] ?? [], fn($v) => !empty($v));
            $updateData['social_links'] = !empty($socialLinks) ? $socialLinks : null;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json(['success' => true]);
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

        $host = auth()->user()->currentHost();

        // Get the instructor record if this user is an instructor
        $instructor = null;
        if ($user->role === User::ROLE_INSTRUCTOR || $user->is_instructor) {
            $instructor = Instructor::where('host_id', $host->id)
                ->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('email', $user->email);
                })
                ->first();
        }

        // Load certifications
        $certifications = \App\Models\StudioCertification::where('host_id', $host->id)
            ->where('user_id', $user->id)
            ->get();

        return view('host.settings.team.users.edit', [
            'user' => $user,
            'instructor' => $instructor,
            'certifications' => $certifications,
            'roles' => User::getRoles(),
            'groupedPermissions' => User::getAllPermissions(),
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
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
            // Profile fields
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications_text' => 'nullable|string|max:1000',
            // Employment Details
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99',
            'compensation_notes' => 'nullable|string|max:1000',
            // Workload
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            // Working Days
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            // Availability
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i',
            'availability_by_day' => 'nullable|array',
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
                // Create new instructor record
                $instructor = Instructor::create([
                    'host_id' => $host->id,
                    'user_id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'is_active' => true,
                    'is_visible' => false,
                    'status' => Instructor::STATUS_ACTIVE,
                ]);
                $instructorId = $instructor->id;
            }
        }

        // Update user profile fields
        $user->update([
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? null,
            'instructor_id' => $instructorId,
            'is_instructor' => $isNowInstructor,
            'phone' => $validated['phone'] ?? $user->phone,
            'bio' => $validated['bio'] ?? $user->bio,
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

        // If user has instructor role, update their instructor profile
        if ($instructorId) {
            $instructor = Instructor::find($instructorId);
            if ($instructor && $instructor->host_id === $host->id) {
                $profileComplete = !empty($validated['employment_type']) && !empty($validated['rate_type']);

                $instructor->update([
                    'phone' => $validated['phone'] ?? $instructor->phone,
                    'bio' => $validated['bio'] ?? $instructor->bio,
                    'specialties' => $validated['specialties'] ?? $instructor->specialties,
                    'certifications' => $validated['certifications_text'] ?? $instructor->certifications,
                    'employment_type' => $validated['employment_type'] ?? $instructor->employment_type,
                    'rate_type' => $validated['rate_type'] ?? $instructor->rate_type,
                    'rate_amount' => $validated['rate_amount'] ?? $instructor->rate_amount,
                    'compensation_notes' => $validated['compensation_notes'] ?? $instructor->compensation_notes,
                    'hours_per_week' => $validated['hours_per_week'] ?? $instructor->hours_per_week,
                    'max_classes_per_week' => $validated['max_classes_per_week'] ?? $instructor->max_classes_per_week,
                    'working_days' => $validated['working_days'] ?? $instructor->working_days,
                    'availability_default_from' => $validated['availability_default_from'] ?? $instructor->availability_default_from,
                    'availability_default_to' => $validated['availability_default_to'] ?? $instructor->availability_default_to,
                    'availability_by_day' => $validated['availability_by_day'] ?? $instructor->availability_by_day,
                    'is_active' => $profileComplete ? true : $instructor->is_active,
                    'status' => $profileComplete ? Instructor::STATUS_ACTIVE : $instructor->status,
                ]);
            }
        }

        $successMessage = 'Team member updated successfully.';
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
        $sendInvite = $request->boolean('send_invite');
        $isQuickInvite = $request->boolean('quick_invite');

        // Build validation rules based on whether we're sending invite or not
        $rules = [
            'role' => 'required|in:admin,staff,instructor',
            'permissions' => 'nullable|array',
            'send_invite' => 'boolean',
            'quick_invite' => 'boolean',
            // Profile fields
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications' => 'nullable|string|max:1000',
            // Employment Details
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99',
            'compensation_notes' => 'nullable|string|max:1000',
            // Workload
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            // Working Days
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            // Availability
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i',
            'availability_by_day' => 'nullable|array',
        ];

        // For quick invites, first_name and last_name are optional (derived from email)
        if ($isQuickInvite) {
            $rules['first_name'] = 'nullable|string|max:255';
            $rules['last_name'] = 'nullable|string|max:255';
        } else {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
        }

        if ($sendInvite) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users', 'email'),
                Rule::unique('team_invitations', 'email')
                    ->where('host_id', $host->id)
                    ->where('status', TeamInvitation::STATUS_PENDING),
            ];
        } else {
            $rules['email'] = 'nullable|email';
        }

        $validated = $request->validate($rules, [
            'email.unique' => 'This email is already registered or has a pending invitation.',
        ]);

        // For quick invites, derive name from email if not provided
        if ($isQuickInvite && empty($validated['first_name'])) {
            $emailParts = explode('@', $validated['email']);
            $namePart = $emailParts[0];
            // Convert email prefix to name (e.g., "john.doe" -> "John Doe")
            $namePart = str_replace(['.', '_', '-'], ' ', $namePart);
            $nameParts = explode(' ', ucwords($namePart));
            $validated['first_name'] = $nameParts[0] ?? 'Team';
            $validated['last_name'] = $nameParts[1] ?? 'Member';
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $email = $validated['email'] ?? null;

        // If NOT sending invite, create team member directly without login capability
        if (!$sendInvite) {
            return $this->createTeamMemberWithoutLogin($host, $validated, $fullName, $email, $request);
        }

        // Otherwise, send invitation (existing flow)
        $instructorId = null;

        // Auto-create instructor record when inviting with instructor role
        if ($validated['role'] === User::ROLE_INSTRUCTOR) {
            // Check if instructor already exists with this email
            $existingInstructor = Instructor::where('host_id', $host->id)
                ->where('email', $email)
                ->first();

            if ($existingInstructor) {
                $instructorId = $existingInstructor->id;
                // Update existing instructor with new details
                $existingInstructor->update($this->extractInstructorData($validated, $fullName, $email));
            } else {
                // Create a full instructor record with all details
                $instructorData = $this->extractInstructorData($validated, $fullName, $email);
                $instructorData['host_id'] = $host->id;
                $instructor = Instructor::create($instructorData);
                $instructorId = $instructor->id;
            }
        }

        $invitation = TeamInvitation::create([
            'host_id' => $host->id,
            'email' => $email,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
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
            ->with('success', 'Invitation sent to ' . $email);
    }

    /**
     * Extract instructor-specific data from validated form data
     */
    protected function extractInstructorData(array $validated, string $fullName, ?string $email): array
    {
        return [
            'name' => $fullName,
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'specialties' => $validated['specialties'] ?? null,
            'certifications' => $validated['certifications'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'rate_type' => $validated['rate_type'] ?? null,
            'rate_amount' => $validated['rate_amount'] ?? null,
            'compensation_notes' => $validated['compensation_notes'] ?? null,
            'hours_per_week' => $validated['hours_per_week'] ?? null,
            'max_classes_per_week' => $validated['max_classes_per_week'] ?? null,
            'working_days' => $validated['working_days'] ?? null,
            'availability_default_from' => $validated['availability_default_from'] ?? null,
            'availability_default_to' => $validated['availability_default_to'] ?? null,
            'availability_by_day' => $validated['availability_by_day'] ?? null,
            'is_active' => true,
            'is_visible' => false,
            'status' => Instructor::STATUS_ACTIVE,
        ];
    }

    /**
     * Create a team member without login capability
     */
    protected function createTeamMemberWithoutLogin($host, array $validated, string $fullName, ?string $email, Request $request)
    {
        // For instructor role, create instructor record with all details
        if ($validated['role'] === User::ROLE_INSTRUCTOR) {
            // Check if instructor already exists with this email (if provided)
            $existingInstructor = null;
            if ($email) {
                $existingInstructor = Instructor::where('host_id', $host->id)
                    ->where('email', $email)
                    ->first();
            }

            if ($existingInstructor) {
                // Update existing instructor with new details
                $existingInstructor->update($this->extractInstructorData($validated, $fullName, $email));
                return redirect()->route('instructors.show', $existingInstructor)
                    ->with('success', 'Instructor "' . $fullName . '" updated.');
            }

            // Create instructor record without user link, with all details
            $instructorData = $this->extractInstructorData($validated, $fullName, $email);
            $instructorData['host_id'] = $host->id;
            $instructor = Instructor::create($instructorData);

            $message = $instructor->isProfileComplete()
                ? 'Instructor "' . $fullName . '" added and activated.'
                : 'Instructor "' . $fullName . '" added. Complete employment details to activate.';

            return redirect()->route('instructors.show', $instructor)
                ->with('success', $message);
        }

        // For admin/staff without login - create user without password
        // Use a placeholder email if none provided (ensures unique constraint is met)
        $userEmail = $email ?? $this->generatePlaceholderEmail($host, $fullName);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $userEmail,
            'password' => null, // No password = cannot login
            'host_id' => $host->id,
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? User::getDefaultPermissionsForRole($validated['role']),
            'email_verified_at' => null,
        ]);

        // Attach to host via pivot table
        $host->teamMembers()->attach($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? User::getDefaultPermissionsForRole($validated['role'])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('settings.team.users')
            ->with('success', 'Team member "' . $fullName . '" added without login access.');
    }

    /**
     * Generate a placeholder email for users without email
     */
    protected function generatePlaceholderEmail($host, string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        return $slug . '-' . $host->id . '-' . time() . '@nologin.local';
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
                // Create new instructor record
                $instructor = Instructor::create([
                    'host_id' => $host->id,
                    'user_id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'is_active' => true,
                    'is_visible' => false,
                    'status' => Instructor::STATUS_ACTIVE,
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
     * Send password reset email to user
     */
    public function resetUserPassword(User $user)
    {
        $this->authorizeUser($user);

        if ($user->isOwner()) {
            return response()->json(['message' => 'Cannot reset owner password.'], 403);
        }

        if (!$user->password) {
            return response()->json(['message' => 'User does not have login access.'], 400);
        }

        // Send password reset email
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset email sent to ' . $user->email]);
        }

        return response()->json(['message' => 'Failed to send password reset email.'], 500);
    }

    /**
     * Send login invitation to existing team member without login
     */
    public function sendUserInvite(User $user)
    {
        $this->authorizeUser($user);
        $host = auth()->user()->currentHost();

        if ($user->password) {
            return back()->with('error', 'User already has login access.');
        }

        if (!$user->email || str_contains($user->email, '@nologin.local')) {
            return back()->with('error', 'User does not have a valid email address.');
        }

        // Check for existing pending invitation
        $existingInvite = TeamInvitation::where('host_id', $host->id)
            ->where('email', $user->email)
            ->where('status', TeamInvitation::STATUS_PENDING)
            ->first();

        if ($existingInvite) {
            return back()->with('error', 'An invitation is already pending for this email.');
        }

        // Get user's role from pivot
        $userRole = $user->pivot->role ?? $user->role;

        // Create invitation
        $invitation = TeamInvitation::create([
            'host_id' => $host->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $userRole,
            'permissions' => $user->permissions,
            'instructor_id' => $user->instructor_id,
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

        return back()->with('success', 'Login invitation sent to ' . $user->email);
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

        $host = auth()->user()->currentHost();
        $path = $request->file('photo')->storePublicly($host->getStoragePath('instructors'), config('filesystems.uploads'));
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

    /**
     * Store a certification for a user
     */
    public function storeUserCertification(Request $request, User $user)
    {
        $this->authorizeUser($user);

        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'certification_name' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'remove_file' => 'nullable|boolean',
        ]);

        // Update existing or create new
        if (!empty($validated['id'])) {
            $certification = \App\Models\StudioCertification::where('id', $validated['id'])
                ->where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $certification = new \App\Models\StudioCertification();
            $certification->host_id = $host->id;
            $certification->user_id = $user->id;
        }

        $certification->name = $validated['name'];
        $certification->certification_name = $validated['certification_name'] ?? null;
        $certification->expire_date = $validated['expire_date'] ?? null;
        $certification->reminder_days = $validated['reminder_days'] ?? null;
        $certification->notes = $validated['notes'] ?? null;

        // Handle file removal
        if ($request->boolean('remove_file') && $certification->file_path) {
            Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            $certification->file_path = null;
            $certification->file_name = null;
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($certification->file_path) {
                Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            }

            $file = $request->file('file');
            $path = $file->storePublicly($host->getStoragePath('certifications/users'), config('filesystems.uploads'));
            $certification->file_path = $path;
            $certification->file_name = $file->getClientOriginalName();
        }

        $certification->save();

        return response()->json([
            'success' => true,
            'certification' => $this->formatUserCertification($certification),
        ]);
    }

    /**
     * Get a certification for a user
     */
    public function getUserCertification(User $user, \App\Models\StudioCertification $certification)
    {
        $this->authorizeUser($user);

        $host = auth()->user()->currentHost();

        if ($certification->host_id !== $host->id || $certification->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'certification' => $this->formatUserCertification($certification),
        ]);
    }

    /**
     * Delete a certification for a user
     */
    public function deleteUserCertification(User $user, \App\Models\StudioCertification $certification)
    {
        $this->authorizeUser($user);

        $host = auth()->user()->currentHost();

        if ($certification->host_id !== $host->id || $certification->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete file if exists
        if ($certification->file_path) {
            Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
        }

        $certification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Format certification for JSON response
     */
    protected function formatUserCertification(\App\Models\StudioCertification $certification): array
    {
        return [
            'id' => $certification->id,
            'name' => $certification->name,
            'certification_name' => $certification->certification_name,
            'expire_date' => $certification->expire_date?->format('Y-m-d'),
            'expire_date_formatted' => $certification->expire_date?->format('M d, Y'),
            'reminder_days' => $certification->reminder_days,
            'notes' => $certification->notes,
            'file_name' => $certification->file_name,
            'file_url' => $certification->file_url,
            'status_label' => $certification->status_label,
            'status_badge_class' => $certification->status_badge_class,
            'days_until_expiry' => $certification->days_until_expiry,
        ];
    }
}
