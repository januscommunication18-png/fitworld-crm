<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\BookingPageRequest;
use App\Http\Requests\Onboarding\OnboardingLocationRequest;
use App\Http\Requests\Onboarding\StaffMemberRequest;
use App\Http\Requests\Onboarding\StudioInfoRequest;
use App\Http\Requests\Onboarding\TechSupportRequest;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\Location;
use App\Models\PhoneVerificationCode;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Get the current onboarding progress.
     */
    public function progress(): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        if (!$host) {
            return response()->json([
                'error' => 'No host found for user',
            ], 404);
        }

        // Check if tech support is pending
        if ($host->hasTechSupportPending()) {
            return response()->json([
                'data' => [
                    'tech_support_pending' => true,
                    'tech_support_ticket_id' => $host->tech_support_ticket_id,
                    'tech_support_requested_at' => $host->tech_support_requested_at,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'current_step' => $host->post_signup_step,
                'email_verified' => $user->hasVerifiedEmail(),
                'phone_verified' => $host->hasOwnerPhoneVerified(),
                'tech_support_pending' => false,
                'form_data' => $this->getFormData($host, $user),
            ],
        ]);
    }

    /**
     * Get pre-filled form data from the host.
     */
    protected function getFormData(Host $host, User $user): array
    {
        $location = $host->locations()->first();

        return [
            // Step 2: Studio Info
            'studio_name' => $host->studio_name,
            'studio_structure' => $host->studio_structure ?? 'solo',
            'subdomain' => $host->subdomain,
            'studio_types' => $host->studio_types ?? [],
            'studio_categories' => $host->studio_categories ?? [],
            'default_language_app' => $host->default_language_app ?? 'en',
            'default_currency' => $host->default_currency ?? 'USD',
            'cancellation_window_hours' => $host->getPolicy('cancellation_window_hours', 12),
            // Step 3: Location
            'location' => $location ? [
                'id' => $location->id,
                'name' => $location->name,
                'location_type' => $location->location_type,
                'address_line_1' => $location->address_line_1,
                'address_line_2' => $location->address_line_2,
                'city' => $location->city,
                'state' => $location->state,
                'postal_code' => $location->postal_code,
                'country' => $location->country,
                'phone' => $location->phone,
                'email' => $location->email,
                'virtual_platform' => $location->virtual_platform,
                'virtual_meeting_link' => $location->virtual_meeting_link,
            ] : null,
            // Step 5: Booking Page
            'booking_page_status' => $host->booking_page_status ?? Host::BOOKING_PAGE_DRAFT,
            'logo_url' => $host->logo_url,
        ];
    }

    /**
     * Resend email verification.
     */
    public function resendEmailVerification(): JsonResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
            ]);
        }

        // Rate limiting: 60-second cooldown
        $cacheKey = 'email-verify-cooldown:' . $user->id;
        if (Cache::has($cacheKey)) {
            $remainingSeconds = Cache::get($cacheKey) - time();
            return response()->json([
                'error' => 'Please wait before requesting another email',
                'retry_after' => max(0, $remainingSeconds),
            ], 429);
        }

        // Rate limiting: Max 5 per hour
        $hourlyKey = 'email-verify-hourly:' . $user->id;
        $hourlyCount = Cache::get($hourlyKey, 0);
        if ($hourlyCount >= 5) {
            return response()->json([
                'error' => 'Too many email verification requests. Please try again later.',
            ], 429);
        }

        $user->sendEmailVerificationNotification();

        // Set cooldown and increment hourly count
        Cache::put($cacheKey, time() + 60, 60);
        Cache::put($hourlyKey, $hourlyCount + 1, 3600);

        return response()->json([
            'message' => 'Verification email sent',
        ]);
    }

    /**
     * Send phone verification code via SMS.
     */
    public function sendPhoneCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'max:5'],
        ]);

        $user = auth()->user();
        $host = $user->host;

        // Validate phone number format
        $fullPhoneNumber = $this->twilioService->formatPhoneNumber(
            $request->phone_number,
            $request->country_code
        );

        if (!$this->twilioService->isValidPhoneNumber($fullPhoneNumber)) {
            return response()->json([
                'error' => 'Invalid phone number format',
            ], 422);
        }

        // Rate limiting: Max 3 codes per hour
        $recentCount = PhoneVerificationCode::countRecentCodesForHost($host);
        if ($recentCount >= 3) {
            return response()->json([
                'error' => 'Too many verification code requests. Please try again later.',
            ], 429);
        }

        // Create verification code
        $verification = PhoneVerificationCode::createForHost($host, $fullPhoneNumber);

        // Send via Twilio
        $sent = $this->twilioService->sendVerificationCode($fullPhoneNumber, $verification->code);

        if (!$sent && !app()->environment('local')) {
            return response()->json([
                'error' => 'Failed to send verification code. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Verification code sent',
            'phone_number' => $fullPhoneNumber,
        ]);
    }

    /**
     * Verify phone code.
     */
    public function verifyPhoneCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'phone_number' => ['required', 'string'],
            'country_code' => ['required', 'string'],
        ]);

        $user = auth()->user();
        $host = $user->host;

        $fullPhoneNumber = $this->twilioService->formatPhoneNumber(
            $request->phone_number,
            $request->country_code
        );

        // Find the verification code
        $verification = PhoneVerificationCode::where('host_id', $host->id)
            ->where('phone_number', $fullPhoneNumber)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'error' => 'No verification code found. Please request a new code.',
            ], 404);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'error' => 'Verification code has expired. Please request a new code.',
            ], 422);
        }

        if ($verification->hasMaxAttempts()) {
            return response()->json([
                'error' => 'Too many failed attempts. Please request a new code.',
            ], 429);
        }

        // Check the code
        if ($verification->code !== $request->code) {
            $verification->incrementAttempts();
            return response()->json([
                'error' => 'Invalid verification code',
                'attempts_remaining' => max(0, 5 - $verification->attempts),
            ], 422);
        }

        // Mark as verified
        $verification->markAsVerified();
        $host->markOwnerPhoneVerified($fullPhoneNumber, $request->country_code);

        // Advance to step 2 if on step 1
        if ($host->post_signup_step === 1 && $user->hasVerifiedEmail()) {
            $host->update(['post_signup_step' => 2]);
        }

        return response()->json([
            'message' => 'Phone verified successfully',
        ]);
    }

    /**
     * Save studio information (Step 2).
     */
    public function saveStudioInfo(StudioInfoRequest $request): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        $data = $request->validated();

        // Update policies if cancellation window is provided
        if (isset($data['cancellation_window_hours'])) {
            $policies = $host->policies ?? Host::defaultPolicies();
            $policies['cancellation_window_hours'] = $data['cancellation_window_hours'];
            $data['policies'] = $policies;
            unset($data['cancellation_window_hours']);
        }

        $host->update($data);

        // Advance step if appropriate
        if ($host->post_signup_step < 3) {
            $host->update(['post_signup_step' => 3]);
        }

        return response()->json([
            'message' => 'Studio information saved',
            'data' => [
                'current_step' => $host->post_signup_step,
            ],
        ]);
    }

    /**
     * Save location information (Step 3).
     */
    public function saveLocation(OnboardingLocationRequest $request): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        $data = $request->validated();

        // Create or update the first location
        $location = $host->locations()->first();

        if ($location) {
            $location->update($data);
        } else {
            $data['host_id'] = $host->id;
            $data['is_default'] = true;
            $location = Location::create($data);
        }

        // Advance step if appropriate
        if ($host->post_signup_step < 4) {
            $host->update(['post_signup_step' => 4]);
        }

        return response()->json([
            'message' => 'Location saved',
            'data' => [
                'current_step' => $host->post_signup_step,
                'location_id' => $location->id,
            ],
        ]);
    }

    /**
     * Save staff members (Step 4).
     * Note: Invites are stored but not sent until onboarding completion.
     */
    public function saveStaffMember(StaffMemberRequest $request): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        $staffMembers = $request->validated('staff_members', []);

        // Store pending invitations in session (sent on completion)
        session(['onboarding_pending_invites' => $staffMembers]);

        // Advance step if appropriate
        if ($host->post_signup_step < 5) {
            $host->update(['post_signup_step' => 5]);
        }

        return response()->json([
            'message' => 'Staff members saved',
            'data' => [
                'current_step' => $host->post_signup_step,
                'staff_count' => count($staffMembers),
            ],
        ]);
    }

    /**
     * Save booking page settings (Step 5).
     */
    public function saveBookingPage(BookingPageRequest $request): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        $host->update([
            'booking_page_status' => $request->validated('booking_page_status'),
        ]);

        return response()->json([
            'message' => 'Booking page settings saved',
            'data' => [
                'current_step' => $host->post_signup_step,
            ],
        ]);
    }

    /**
     * Upload logo for booking page.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,gif,svg,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        // Delete old logo if exists
        if ($host->logo_path) {
            Storage::disk(config('filesystems.uploads'))->delete($host->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store(
            $host->getStoragePath('branding'),
            config('filesystems.uploads')
        );

        $host->update(['logo_path' => $path]);

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'data' => [
                'logo_url' => $host->logo_url,
            ],
        ]);
    }

    /**
     * Complete the onboarding process.
     */
    public function completeOnboarding(): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        $this->checkTechSupportStatus($host);

        // Validate minimum requirements
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'Email verification is required',
            ], 422);
        }

        // Phone verification is optional - no check needed

        if (!$host->subdomain) {
            return response()->json([
                'error' => 'Studio subdomain is required',
            ], 422);
        }

        if (!$host->locations()->exists()) {
            return response()->json([
                'error' => 'At least one location is required',
            ], 422);
        }

        DB::transaction(function () use ($host, $user) {
            // Send pending staff invitations
            $pendingInvites = session('onboarding_pending_invites', []);
            foreach ($pendingInvites as $inviteData) {
                $this->sendStaffInvitation($host, $user, $inviteData);
            }
            session()->forget('onboarding_pending_invites');

            // Mark onboarding as complete
            $host->markPostSignupOnboardingComplete();
        });

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'data' => [
                'redirect_url' => '/dashboard',
            ],
        ]);
    }

    /**
     * Request technical support.
     */
    public function requestTechSupport(TechSupportRequest $request): JsonResponse
    {
        $user = auth()->user();
        $host = $user->host;

        if ($host->hasTechSupportPending()) {
            return response()->json([
                'error' => 'Tech support request already submitted',
            ], 422);
        }

        $data = $request->validated();

        // Create helpdesk ticket
        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'source_type' => HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT,
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => 'Onboarding Support Request - ' . $host->studio_name,
            'message' => $data['note'] ?? 'User requested technical assistance for onboarding setup.',
            'status' => HelpdeskTicket::STATUS_OPEN,
        ]);

        // Mark tech support as requested
        $host->markTechSupportRequested($ticket->id);

        Log::info('Onboarding tech support requested', [
            'host_id' => $host->id,
            'ticket_id' => $ticket->id,
        ]);

        return response()->json([
            'message' => 'Tech support request submitted',
            'data' => [
                'ticket_id' => $ticket->id,
            ],
        ]);
    }

    /**
     * Check if tech support is pending and block actions.
     */
    protected function checkTechSupportStatus(Host $host): void
    {
        if ($host->hasTechSupportPending()) {
            abort(403, 'Your account setup is being handled by our support team.');
        }
    }

    /**
     * Send a staff invitation.
     */
    protected function sendStaffInvitation(Host $host, User $inviter, array $data): void
    {
        // Check if user already exists
        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser) {
            // Check if already a member of this host
            if ($host->teamMembers()->where('user_id', $existingUser->id)->exists()) {
                return;
            }

            // Add to host
            $host->teamMembers()->attach($existingUser->id, [
                'role' => $data['role'],
                'permissions' => json_encode([]),
                'is_primary' => false,
                'joined_at' => now(),
            ]);
        } else {
            // Create invited user
            $newUser = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'],
                'status' => User::STATUS_INVITED,
                'host_id' => $host->id,
                'role' => $data['role'],
            ]);

            // Attach to host via pivot
            $host->teamMembers()->attach($newUser->id, [
                'role' => $data['role'],
                'permissions' => json_encode([]),
                'is_primary' => false,
                'joined_at' => now(),
            ]);

            // Create invitation
            TeamInvitation::create([
                'host_id' => $host->id,
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'role' => $data['role'],
                'permissions' => [],
                'token' => TeamInvitation::generateToken(),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
                'invited_by' => $inviter->id,
            ]);

            // Note: Email will be sent by a listener or job
        }
    }
}
