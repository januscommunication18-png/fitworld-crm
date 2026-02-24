<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\Instructor;
use App\Models\InstructorNote;
use App\Models\InstructorActionLog;
use App\Models\ClassSession;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{
    /**
     * Authorize that the instructor belongs to the current host
     */
    private function authorizeInstructor(Instructor $instructor): void
    {
        if ($instructor->host_id !== auth()->user()->host_id) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Auto-link instructors to users with matching email (fix data inconsistencies)
     */
    private function autoLinkInstructorsToUsers($host): void
    {
        // Get instructors without user_id but with email
        $unlinkedInstructors = $host->instructors()
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->get();

        foreach ($unlinkedInstructors as $instructor) {
            // Find user with matching email that has a password (has login)
            $user = User::where('email', $instructor->email)
                ->whereNotNull('password')
                ->first();

            if ($user) {
                // Link the instructor to the user
                $instructor->update(['user_id' => $user->id]);

                // Also update user's instructor_id if not set
                if (!$user->instructor_id) {
                    $user->update(['instructor_id' => $instructor->id]);
                }
            }
        }
    }

    /**
     * Authorize that the note belongs to the current host
     */
    private function authorizeNote(InstructorNote $note): void
    {
        if ($note->host_id !== auth()->user()->host_id) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display instructor list with filters and view toggle
     */
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $view = $request->get('view', 'list');

        // Auto-fix: Link any unlinked instructors to existing users with matching email
        $this->autoLinkInstructorsToUsers($host);

        $query = $host->instructors()->with(['user', 'invitation']);

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Employment type filter
        if ($employmentType = $request->get('employment_type')) {
            $query->where('employment_type', $employmentType);
        }

        // Rate type filter
        if ($rateType = $request->get('rate_type')) {
            $query->where('rate_type', $rateType);
        }

        $instructors = $query->orderBy('name')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => $host->instructors()->count(),
            'active' => $host->instructors()->where('is_active', true)->count(),
            'inactive' => $host->instructors()->where('is_active', false)->count(),
            'with_login' => $host->instructors()->whereNotNull('user_id')->count(),
            'no_login' => $host->instructors()->whereNull('user_id')->count(),
        ];

        return view('host.instructors.index', [
            'instructors' => $instructors,
            'stats' => $stats,
            'view' => $view,
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
        ]);
    }

    /**
     * Show create instructor form
     */
    public function create()
    {
        $host = auth()->user()->currentHost();

        // Get users with instructor role from the pivot table
        // These are users who need their instructor profile completed
        $availableUsers = $host->teamMembers()
            ->wherePivot('role', User::ROLE_INSTRUCTOR)
            ->get()
            ->filter(function ($user) use ($host) {
                // Include if:
                // 1. No instructor record exists for this host
                // 2. OR instructor record exists but is pending (needs completion)
                $instructor = Instructor::where('host_id', $host->id)
                    ->where('user_id', $user->id)
                    ->first();

                return !$instructor || $instructor->status === Instructor::STATUS_PENDING;
            })
            ->sortBy('first_name')
            ->values();

        return view('host.instructors.create', [
            'availableUsers' => $availableUsers,
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
        ]);
    }

    /**
     * Store new instructor
     */
    public function store(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications' => 'nullable|string|max:1000',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99|required_with:rate_type',
            'compensation_notes' => 'nullable|string|max:1000',
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i|after:availability_default_from',
            'availability_by_day' => 'nullable|array',
            'send_invite' => 'boolean',
        ]);

        // If linking to existing user
        $linkedUser = null;
        if (!empty($validated['user_id'])) {
            $linkedUser = User::find($validated['user_id']);

            // Check if user already has an instructor record
            $existingInstructor = Instructor::where('host_id', $host->id)
                ->where('user_id', $linkedUser->id)
                ->first();

            if ($existingInstructor) {
                // Update existing pending instructor instead of creating new
                $existingInstructor->update([
                    'name' => $linkedUser->full_name,
                    'email' => $linkedUser->email,
                    'phone' => $validated['phone'] ?? $existingInstructor->phone,
                    'bio' => $validated['bio'] ?? $existingInstructor->bio,
                    'specialties' => $validated['specialties'] ?? $existingInstructor->specialties,
                    'certifications' => $validated['certifications'] ?? $existingInstructor->certifications,
                    'is_visible' => $request->boolean('is_visible'),
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
                ]);

                // Check if profile is now complete
                $existingInstructor->refresh();
                if ($existingInstructor->isProfileComplete()) {
                    $existingInstructor->update([
                        'is_active' => true,
                        'status' => Instructor::STATUS_ACTIVE,
                    ]);
                }

                return redirect()->route('instructors.show', $existingInstructor)
                    ->with('success', 'Instructor profile updated for ' . $linkedUser->full_name);
            }

            // Create new instructor linked to user
            $validated['name'] = $linkedUser->full_name;
            $validated['email'] = $linkedUser->email;
            $validated['user_id'] = $linkedUser->id;
        }

        $validated['host_id'] = $host->id;
        $validated['is_visible'] = $request->boolean('is_visible');

        // Determine active status based on profile completeness
        $isProfileComplete = !empty($validated['employment_type'])
            && !empty($validated['rate_type'])
            && !empty($validated['rate_amount'])
            && !empty($validated['working_days'])
            && (!empty($validated['availability_default_from']) || !empty($validated['availability_by_day']));

        $validated['is_active'] = $isProfileComplete;
        $validated['status'] = $isProfileComplete ? Instructor::STATUS_ACTIVE : Instructor::STATUS_PENDING;

        $instructor = Instructor::create($validated);

        // Link back to user if provided
        if ($linkedUser) {
            $linkedUser->update(['instructor_id' => $instructor->id]);
            DB::table('host_user')
                ->where('user_id', $linkedUser->id)
                ->where('host_id', $host->id)
                ->update(['instructor_id' => $instructor->id, 'updated_at' => now()]);
        }

        $successMessage = 'Instructor added successfully.';

        // Send invitation if requested and email provided (for new instructors without user)
        if (!$linkedUser && !empty($validated['email']) && $request->boolean('send_invite')) {
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

            Mail::to($invitation->email)->send(new TeamInvitationMail(
                $invitation,
                $host->studio_name ?? 'Our Studio',
                auth()->user()->full_name
            ));

            $successMessage = 'Instructor added and invitation sent to ' . $validated['email'] . '.';
        }

        if (!$isProfileComplete) {
            $successMessage .= ' Complete the profile to activate this instructor.';
        }

        return redirect()->route('instructors.show', $instructor)
            ->with('success', $successMessage);
    }

    /**
     * Show edit instructor form
     */
    public function edit(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $missingFields = $instructor->isProfileComplete() ? [] : $instructor->getMissingProfileFields();

        return view('host.instructors.edit', [
            'instructor' => $instructor,
            'specialties' => Instructor::getCommonSpecialties(),
            'employmentTypes' => Instructor::getEmploymentTypes(),
            'rateTypes' => Instructor::getRateTypes(),
            'dayOptions' => Instructor::getDayOptions(),
            'missingFields' => $missingFields,
        ]);
    }

    /**
     * Update instructor
     */
    public function update(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $wasPending = $instructor->status === Instructor::STATUS_PENDING;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'specialties' => 'nullable|array',
            'certifications' => 'nullable|string|max:1000',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'employment_type' => ['nullable', Rule::in(array_keys(Instructor::getEmploymentTypes()))],
            'rate_type' => ['nullable', Rule::in(array_keys(Instructor::getRateTypes()))],
            'rate_amount' => 'nullable|numeric|min:0|max:99999.99|required_with:rate_type',
            'compensation_notes' => 'nullable|string|max:1000',
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'max_classes_per_week' => 'nullable|integer|min:0|max:100',
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:0,6',
            'availability_default_from' => 'nullable|date_format:H:i',
            'availability_default_to' => 'nullable|date_format:H:i|after:availability_default_from',
            'availability_by_day' => 'nullable|array',
        ]);

        $validated['is_visible'] = $request->boolean('is_visible');
        $validated['is_active'] = $request->boolean('is_active');

        $instructor->update($validated);
        $instructor->refresh();

        // Auto-activate if profile is now complete
        $successMessage = 'Instructor updated successfully.';
        if ($instructor->isProfileComplete()) {
            if ($wasPending) {
                $instructor->update([
                    'status' => Instructor::STATUS_ACTIVE,
                    'is_active' => true,
                ]);
                $successMessage = 'Instructor profile completed and activated successfully.';
            }
        } else {
            $missing = $instructor->getMissingProfileFields();
            if (!empty($missing) && $instructor->status === Instructor::STATUS_PENDING) {
                $successMessage = 'Instructor updated. To activate, complete: ' . implode(', ', $missing) . '.';
            }
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Upload instructor photo
     */
    public function uploadPhoto(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        // Delete old photo
        if ($instructor->photo_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors
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
    public function removePhoto(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        if ($instructor->photo_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors
            }
        }

        $instructor->update(['photo_path' => null]);

        return response()->json(['success' => true]);
    }

    /**
     * Send invitation to instructor
     */
    public function sendInvite(Instructor $instructor)
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

        $host = auth()->user()->currentHost();

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
    public function destroy(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        // Check for future sessions
        $futureSessions = ClassSession::where('primary_instructor_id', $instructor->id)
            ->where('start_time', '>', now())
            ->where('status', '!=', ClassSession::STATUS_CANCELLED)
            ->count();

        if ($futureSessions > 0) {
            return back()->with('error', 'Cannot delete instructor with ' . $futureSessions . ' upcoming session(s). Please reassign classes first.');
        }

        // Delete photo if exists
        if ($instructor->photo_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($instructor->photo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors
            }
        }

        // Unlink user if linked
        if ($instructor->user_id) {
            User::where('id', $instructor->user_id)->update(['instructor_id' => null]);
            DB::table('host_user')
                ->where('user_id', $instructor->user_id)
                ->where('host_id', $instructor->host_id)
                ->update(['instructor_id' => null, 'updated_at' => now()]);
        }

        $instructor->delete();

        return redirect()->route('instructors.index')
            ->with('success', 'Instructor deleted successfully.');
    }

    /**
     * Display instructor profile with tabs
     */
    public function show(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $instructor->load(['user', 'invitation', 'servicePlans', 'notes.author', 'studioCertifications', 'actionLogs' => function ($q) {
            $q->latest()->limit(10);
        }]);

        $tab = $request->get('tab', 'overview');

        // Get schedule data for schedule tab
        $upcomingSessions = ClassSession::where(function ($q) use ($instructor) {
            $q->where('primary_instructor_id', $instructor->id)
                ->orWhere('backup_instructor_id', $instructor->id);
        })
            ->where('start_time', '>', now())
            ->where('status', '!=', ClassSession::STATUS_CANCELLED)
            ->with(['classPlan', 'location'])
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Get recent past sessions
        $recentSessions = ClassSession::where(function ($q) use ($instructor) {
            $q->where('primary_instructor_id', $instructor->id)
                ->orWhere('backup_instructor_id', $instructor->id);
        })
            ->where('start_time', '<', now())
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->with(['classPlan', 'location'])
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        // Get class plans this instructor is assigned to (via sessions)
        $classPlans = $instructor->primarySessions()
            ->with('classPlan')
            ->get()
            ->pluck('classPlan')
            ->unique('id')
            ->values();

        // Get this month's stats for billing tab
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyStats = [
            'classes_count' => ClassSession::where('primary_instructor_id', $instructor->id)
                ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->count(),
            'services_count' => $instructor->serviceSlots()
                ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
                ->count(),
        ];

        // Calculate estimated earnings
        $monthlyStats['estimated_earnings'] = $this->calculateEstimatedEarnings($instructor, $monthlyStats);

        // All-time stats
        $allTimeStats = [
            'total_classes' => ClassSession::where('primary_instructor_id', $instructor->id)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '<', now())
                ->count(),
            'total_services' => $instructor->serviceSlots()
                ->where('start_time', '<', now())
                ->count(),
            'total_clients' => 0, // TODO: Implement when class_bookings table is created
            'years_teaching' => $instructor->created_at ? now()->diffInYears($instructor->created_at) : 0,
        ];

        // Upcoming service bookings
        $upcomingServiceBookings = collect(); // TODO: Implement when ServiceBooking model is created

        return view('host.instructors.show', [
            'instructor' => $instructor,
            'tab' => $tab,
            'upcomingSessions' => $upcomingSessions,
            'recentSessions' => $recentSessions,
            'classPlans' => $classPlans,
            'monthlyStats' => $monthlyStats,
            'allTimeStats' => $allTimeStats,
            'upcomingServiceBookings' => $upcomingServiceBookings,
            'noteTypes' => InstructorNote::getNoteTypes(),
        ]);
    }

    /**
     * Toggle instructor active status
     */
    public function toggleStatus(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $oldStatus = $instructor->is_active ? 'active' : 'inactive';
        $newStatus = !$instructor->is_active;

        // Check for future sessions if deactivating
        if (!$newStatus) {
            $futureSessions = ClassSession::where('primary_instructor_id', $instructor->id)
                ->where('start_time', '>', now())
                ->where('status', '!=', ClassSession::STATUS_CANCELLED)
                ->count();

            if ($futureSessions > 0 && !$request->boolean('confirm')) {
                return response()->json([
                    'success' => false,
                    'warning' => true,
                    'message' => "This instructor has {$futureSessions} upcoming session(s). Are you sure you want to make them inactive?",
                    'future_sessions' => $futureSessions,
                ]);
            }
        }

        $instructor->update([
            'is_active' => $newStatus,
            'status' => $newStatus ? Instructor::STATUS_ACTIVE : Instructor::STATUS_INACTIVE,
        ]);

        $instructor->logAction(
            InstructorActionLog::ACTION_STATUS_CHANGE,
            $oldStatus,
            $newStatus ? 'active' : 'inactive',
            $request->get('reason')
        );

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Instructor activated successfully.' : 'Instructor deactivated successfully.',
            'is_active' => $newStatus,
        ]);
    }

    /**
     * Send password reset to instructor
     */
    public function resetPassword(Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        if (!$instructor->hasAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'This instructor does not have a login account.',
            ], 400);
        }

        if (!$instructor->email) {
            return response()->json([
                'success' => false,
                'message' => 'This instructor does not have an email address.',
            ], 400);
        }

        // Send password reset link
        $status = Password::sendResetLink(['email' => $instructor->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $instructor->logAction(InstructorActionLog::ACTION_PASSWORD_RESET);

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to ' . $instructor->email,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send password reset link. Please try again.',
        ], 500);
    }

    /**
     * Store a new note for instructor
     */
    public function storeNote(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'note_type' => 'required|in:note,payroll,availability,incident,system',
            'is_visible_to_instructor' => 'boolean',
        ]);

        $note = $instructor->notes()->create([
            'host_id' => auth()->user()->host_id,
            'user_id' => auth()->id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
            'is_visible_to_instructor' => $validated['is_visible_to_instructor'] ?? false,
        ]);

        $note->load('author');

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
            'note' => $note,
        ]);
    }

    /**
     * Update a note
     */
    public function updateNote(Request $request, InstructorNote $note)
    {
        $this->authorizeNote($note);

        // Only the author or admin can edit
        if ($note->user_id !== auth()->id() && !in_array(auth()->user()->role, ['owner', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own notes.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'note_type' => 'required|in:note,payroll,availability,incident,system',
            'is_visible_to_instructor' => 'boolean',
        ]);

        $note->update([
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
            'is_visible_to_instructor' => $validated['is_visible_to_instructor'] ?? $note->is_visible_to_instructor,
        ]);

        $note->load('author');

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully.',
            'note' => $note,
        ]);
    }

    /**
     * Delete a note
     */
    public function deleteNote(InstructorNote $note)
    {
        $this->authorizeNote($note);

        // Only the author or admin can delete
        if ($note->user_id !== auth()->id() && !in_array(auth()->user()->role, ['owner', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own notes.',
            ], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully.',
        ]);
    }

    /**
     * Calculate estimated earnings based on rate type
     */
    private function calculateEstimatedEarnings(Instructor $instructor, array $stats): ?float
    {
        if (!$instructor->rate_type || !$instructor->rate_amount) {
            return null;
        }

        return match ($instructor->rate_type) {
            'per_class' => $stats['classes_count'] * (float) $instructor->rate_amount,
            'per_hour' => null, // Would need session duration tracking
            'weekly' => (float) $instructor->rate_amount * 4, // Approximate monthly
            'monthly' => (float) $instructor->rate_amount,
            default => null,
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Certifications
    // ─────────────────────────────────────────────────────────────

    /**
     * Store a new certification for an instructor
     */
    public function storeCertification(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);
        $host = auth()->user()->host;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'certification_name' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $certification = new \App\Models\StudioCertification([
            'host_id' => $host->id,
            'instructor_id' => $instructor->id,
            'name' => $validated['name'],
            'certification_name' => $validated['certification_name'] ?? null,
            'expire_date' => $validated['expire_date'] ?? null,
            'reminder_days' => $validated['reminder_days'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storePublicly($host->getStoragePath('certifications'), config('filesystems.uploads'));
            $certification->file_path = $path;
            $certification->file_name = $file->getClientOriginalName();
        }

        $certification->save();

        return response()->json([
            'success' => true,
            'message' => 'Certification added successfully',
            'certification' => $this->formatCertification($certification),
        ]);
    }

    /**
     * Get a certification for editing
     */
    public function getCertification(Instructor $instructor, $certificationId)
    {
        $this->authorizeInstructor($instructor);

        $certification = \App\Models\StudioCertification::where('instructor_id', $instructor->id)
            ->findOrFail($certificationId);

        return response()->json([
            'success' => true,
            'certification' => [
                'id' => $certification->id,
                'name' => $certification->name,
                'certification_name' => $certification->certification_name,
                'expire_date' => $certification->expire_date?->format('Y-m-d'),
                'reminder_days' => $certification->reminder_days,
                'notes' => $certification->notes,
                'file_url' => $certification->file_url,
                'file_name' => $certification->file_name,
            ],
        ]);
    }

    /**
     * Update an instructor certification
     */
    public function updateCertification(Request $request, Instructor $instructor, $certificationId)
    {
        $this->authorizeInstructor($instructor);
        $host = auth()->user()->host;

        $certification = \App\Models\StudioCertification::where('instructor_id', $instructor->id)
            ->findOrFail($certificationId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'certification_name' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'remove_file' => 'nullable|boolean',
        ]);

        $certification->name = $validated['name'];
        $certification->certification_name = $validated['certification_name'] ?? null;
        $certification->expire_date = $validated['expire_date'] ?? null;
        $certification->reminder_days = $validated['reminder_days'] ?? null;
        $certification->notes = $validated['notes'] ?? null;

        if ($certification->isDirty('expire_date')) {
            $certification->reminder_sent = false;
        }

        if ($request->boolean('remove_file') && $certification->file_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            } catch (\Exception $e) {
                // Ignore
            }
            $certification->file_path = null;
            $certification->file_name = null;
        }

        if ($request->hasFile('file')) {
            if ($certification->file_path) {
                try {
                    Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            $file = $request->file('file');
            $path = $file->storePublicly($host->getStoragePath('certifications'), config('filesystems.uploads'));
            $certification->file_path = $path;
            $certification->file_name = $file->getClientOriginalName();
        }

        $certification->save();

        return response()->json([
            'success' => true,
            'message' => 'Certification updated successfully',
            'certification' => $this->formatCertification($certification),
        ]);
    }

    /**
     * Delete an instructor certification
     */
    public function deleteCertification(Instructor $instructor, $certificationId)
    {
        $this->authorizeInstructor($instructor);

        $certification = \App\Models\StudioCertification::where('instructor_id', $instructor->id)
            ->findOrFail($certificationId);

        if ($certification->file_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $certification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certification deleted successfully',
        ]);
    }

    /**
     * Format certification for JSON response
     */
    private function formatCertification(\App\Models\StudioCertification $certification): array
    {
        return [
            'id' => $certification->id,
            'name' => $certification->name,
            'certification_name' => $certification->certification_name,
            'expire_date' => $certification->expire_date?->format('Y-m-d'),
            'expire_date_formatted' => $certification->expire_date?->format('M j, Y'),
            'reminder_days' => $certification->reminder_days,
            'notes' => $certification->notes,
            'file_url' => $certification->file_url,
            'file_name' => $certification->file_name,
            'status_label' => $certification->status_label,
            'status_badge_class' => $certification->status_badge_class,
            'is_expired' => $certification->isExpired(),
        ];
    }

    /**
     * Toggle social links visibility for instructor public profile
     */
    public function toggleSocialVisibility(Request $request, Instructor $instructor)
    {
        $this->authorizeInstructor($instructor);

        $validated = $request->validate([
            'show_social_links' => 'required|boolean',
        ]);

        $instructor->update([
            'show_social_links' => $validated['show_social_links'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['show_social_links']
                ? 'Social links will be shown on public profile'
                : 'Social links hidden from public profile',
        ]);
    }
}
