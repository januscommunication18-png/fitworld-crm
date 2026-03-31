<?php

namespace App\Http\Controllers\Api;

use App\Events\HostOnboardingCompleted;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Host;
use App\Models\Location;
use App\Models\PendingTeamInvite;
use App\Models\TechnicalSupportRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OnboardingApiController extends Controller
{
    use ApiResponse;

    protected PhoneVerificationService $phoneService;

    public function __construct(PhoneVerificationService $phoneService)
    {
        $this->phoneService = $phoneService;
    }

    /**
     * Get current onboarding progress and saved data.
     */
    public function getProgress(Request $request): JsonResponse
    {
        $user = $request->user();
        $host = $user->host;

        // Determine current step based on completion status
        $currentStep = $this->calculateCurrentStep($user, $host);

        // Get pending team invites
        $pendingInvites = $host->pendingTeamInvites()->get();

        // Get default location
        $defaultLocation = $host->locations()->where('is_default', true)->first();

        return $this->success([
            'step' => $currentStep,
            'support_requested' => $host->hasSupportRequested(),
            'email_verified' => $user->hasVerifiedEmail(),
            'phone_verified' => $user->hasVerifiedPhone(),
            'form_data' => [
                // Step 1: Verification
                'email' => $user->email,
                'phone' => $user->phone,
                'phone_country_code' => $user->phone_country_code ?? '+1',

                // Step 2: Studio Information
                'studio_name' => $host->studio_name,
                'studio_structure' => $host->studio_structure,
                'subdomain' => $host->subdomain,
                'studio_types' => $host->studio_types ?? [],
                'studio_categories' => $host->studio_categories ?? [],
                'language' => $host->language ?? 'en',
                'default_currency' => $host->default_currency ?? 'USD',
                'cancellation_policy' => $host->cancellation_policy,

                // Step 3: Location
                'location' => $defaultLocation ? [
                    'id' => $defaultLocation->id,
                    'name' => $defaultLocation->name,
                    'address_line_1' => $defaultLocation->address_line_1,
                    'address_line_2' => $defaultLocation->address_line_2,
                    'city' => $defaultLocation->city,
                    'state' => $defaultLocation->state,
                    'postal_code' => $defaultLocation->postal_code,
                    'country' => $defaultLocation->country,
                    'phone' => $defaultLocation->phone,
                ] : null,

                // Step 4: Staff Members
                'staff_members' => $pendingInvites->map(fn($invite) => [
                    'id' => $invite->id,
                    'name' => $invite->name,
                    'email' => $invite->email,
                    'role' => $invite->role,
                ])->toArray(),

                // Step 5: Booking Page
                'is_live' => $host->is_live,
                'logo_url' => $host->logo_url,
            ],
            'options' => [
                'studio_structures' => Host::getStudioStructures(),
                'studio_category_groups' => Host::STUDIO_CATEGORY_GROUPS,
                'country_codes' => PhoneVerificationService::getCountryCodes(),
                'currencies' => $this->getCurrencyOptions(),
                'languages' => $this->getLanguageOptions(),
            ],
        ]);
    }

    /**
     * Calculate current onboarding step.
     */
    protected function calculateCurrentStep($user, $host): int
    {
        // Step 1: Email & Phone verification
        if (!$user->hasVerifiedEmail() || !$user->hasVerifiedPhone()) {
            return 1;
        }

        // Step 2: Studio Information
        if (!$host->studio_name || !$host->subdomain || !$host->studio_structure) {
            return 2;
        }

        // Step 3: Location
        $hasLocation = $host->locations()->where('is_default', true)->exists();
        if (!$hasLocation) {
            return 3;
        }

        // Step 4: Staff Members (optional, can skip)
        // Step 5: Booking Page
        if (!$host->onboarding_completed_at) {
            return $host->onboarding_step ?? 4;
        }

        return 5;
    }

    /**
     * Resend email verification.
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->success(['verified' => true], 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    /**
     * Send phone verification code via SMS.
     */
    public function sendPhoneVerification(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'phone_country_code' => ['required', 'string', 'max:5'],
        ]);

        $user = $request->user();

        // Update user's phone number
        $user->setPhoneNumber($request->phone, $request->phone_country_code);

        // Send verification code
        $result = $this->phoneService->sendCodeForUser($user);

        if ($result['success']) {
            return $this->success([
                'dev_mode' => $result['dev_mode'] ?? false,
            ], $result['message']);
        }

        return $this->error($result['message']);
    }

    /**
     * Verify phone verification code.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedPhone()) {
            return $this->success(['verified' => true], 'Phone already verified');
        }

        $result = $this->phoneService->verifyCodeForUser($user, $request->code);

        if ($result['success']) {
            return $this->success([
                'verified' => true,
            ], $result['message']);
        }

        return $this->error($result['message']);
    }

    /**
     * Save Step 2: Studio Information.
     */
    public function saveStudioInfo(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $data = $request->validate([
            'studio_name' => ['required', 'string', 'max:255'],
            'studio_structure' => ['required', 'string', Rule::in(['solo', 'with_team'])],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('hosts', 'subdomain')->ignore($host->id),
            ],
            'studio_types' => ['nullable', 'array'],
            'studio_categories' => ['nullable', 'array'],
            'language' => ['nullable', 'string', 'max:10'],
            'default_currency' => ['nullable', 'string', 'size:3'],
            'cancellation_policy' => ['nullable', 'string'],
        ]);

        $host->update([
            'studio_name' => $data['studio_name'],
            'studio_structure' => $data['studio_structure'],
            'subdomain' => $data['subdomain'],
            'studio_types' => $data['studio_types'] ?? [],
            'studio_categories' => $data['studio_categories'] ?? [],
            'language' => $data['language'] ?? 'en',
            'default_currency' => $data['default_currency'] ?? 'USD',
            'currencies' => [$data['default_currency'] ?? 'USD'],
            'cancellation_policy' => $data['cancellation_policy'] ?? null,
            'onboarding_step' => max($host->onboarding_step ?? 2, 3),
        ]);

        return $this->success($host->fresh(), 'Studio information saved');
    }

    /**
     * Check subdomain availability.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/'],
        ]);

        $subdomain = strtolower($request->input('subdomain'));
        $currentHostId = $request->user()->host->id;

        $exists = Host::where('subdomain', $subdomain)
            ->where('id', '!=', $currentHostId)
            ->exists();

        return $this->success([
            'subdomain' => $subdomain,
            'available' => !$exists,
        ]);
    }

    /**
     * Save Step 3: Location.
     */
    public function saveLocation(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Create or update the default location
        $location = $host->locations()->where('is_default', true)->first();

        $locationData = [
            'name' => $data['name'] ?? $host->studio_name ?? 'Main Location',
            'location_type' => Location::TYPE_IN_PERSON,
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'],
            'phone' => $data['phone'] ?? null,
            'is_default' => true,
        ];

        if ($location) {
            $location->update($locationData);
        } else {
            $location = $host->locations()->create($locationData);
        }

        // Update host with city/state for display purposes
        $host->update([
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'address' => $data['address_line_1'],
            'onboarding_step' => max($host->onboarding_step ?? 3, 4),
        ]);

        return $this->success([
            'location' => $location->fresh(),
        ], 'Location saved');
    }

    /**
     * Get Step 4: Staff Members (pending invites).
     */
    public function getStaffMembers(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $members = $host->pendingTeamInvites()->get()->map(fn($invite) => [
            'id' => $invite->id,
            'name' => $invite->name,
            'email' => $invite->email,
            'role' => $invite->role,
        ]);

        return $this->success([
            'staff_members' => $members,
        ]);
    }

    /**
     * Add Step 4: Staff Member (pending invite).
     */
    public function addStaffMember(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['staff', 'instructor', 'manager', 'admin'])],
        ]);

        // Check for duplicate email in pending invites
        $exists = $host->pendingTeamInvites()
            ->where('email', $data['email'])
            ->exists();

        if ($exists) {
            return $this->error('A team member with this email has already been added.');
        }

        $invite = $host->pendingTeamInvites()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'sent' => false,
        ]);

        return $this->success([
            'id' => $invite->id,
            'name' => $invite->name,
            'email' => $invite->email,
            'role' => $invite->role,
        ], 'Team member added');
    }

    /**
     * Remove Step 4: Staff Member (pending invite).
     */
    public function removeStaffMember(Request $request, int $id): JsonResponse
    {
        $host = $request->user()->host;

        $invite = $host->pendingTeamInvites()->find($id);

        if (!$invite) {
            return $this->error('Team member not found.', [], 404);
        }

        $invite->delete();

        return $this->success(null, 'Team member removed');
    }

    /**
     * Save Step 5: Booking Page settings.
     */
    public function saveBookingPage(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $data = $request->validate([
            'is_live' => ['required', 'boolean'],
        ]);

        $host->update([
            'is_live' => $data['is_live'],
            'onboarding_step' => max($host->onboarding_step ?? 5, 5),
        ]);

        return $this->success([
            'is_live' => $host->is_live,
        ], 'Booking page settings saved');
    }

    /**
     * Upload logo for Step 5.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $host = $request->user()->host;

        // Delete old logo if exists
        if ($host->logo && Storage::disk('public')->exists($host->logo)) {
            Storage::disk('public')->delete($host->logo);
        }

        // Store new logo
        $path = $request->file('logo')->store("hosts/{$host->id}/logo", 'public');

        $host->update(['logo' => $path]);

        return $this->success([
            'logo_url' => Storage::url($path),
        ], 'Logo uploaded');
    }

    /**
     * Complete onboarding and send pending invites.
     */
    public function complete(Request $request): JsonResponse
    {
        $host = $request->user()->host;
        $user = $request->user();

        // Verify requirements are met
        if (!$user->hasVerifiedEmail()) {
            return $this->error('Please verify your email before completing onboarding.');
        }

        if (!$user->hasVerifiedPhone()) {
            return $this->error('Please verify your phone number before completing onboarding.');
        }

        if (!$host->subdomain) {
            return $this->error('Please complete studio information before finishing.');
        }

        $hasLocation = $host->locations()->where('is_default', true)->exists();
        if (!$hasLocation) {
            return $this->error('Please add at least one location before finishing.');
        }

        DB::transaction(function () use ($host) {
            // Mark onboarding as complete
            $host->update([
                'onboarding_completed_at' => now(),
                'onboarding_step' => 5,
            ]);

            // Dispatch job to send pending team invites (will be created later)
            // SendPendingTeamInvitesJob::dispatch($host);
        });

        HostOnboardingCompleted::dispatch($host);

        return $this->success([
            'host' => $host->fresh(),
            'redirect_url' => '/plans',
        ], 'Onboarding complete! Please select a plan to continue.');
    }

    /**
     * Request technical support.
     */
    public function requestSupport(Request $request): JsonResponse
    {
        $user = $request->user();
        $host = $user->host;

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create support request
        $supportRequest = TechnicalSupportRequest::create([
            'host_id' => $host->id,
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'note' => $data['note'] ?? null,
            'source' => 'onboarding',
            'status' => 'pending',
            'metadata' => [
                'onboarding_step' => $host->onboarding_step,
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // Mark host as having requested support
        $host->markSupportRequested();

        return $this->success([
            'support_request_id' => $supportRequest->id,
            'redirect_url' => '/support-waiting',
        ], 'Support request submitted. Our team will contact you shortly.');
    }

    /**
     * Get currency options.
     */
    protected function getCurrencyOptions(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'CAD' => 'Canadian Dollar (CA$)',
            'AUD' => 'Australian Dollar (A$)',
            'INR' => 'Indian Rupee (₹)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'SGD' => 'Singapore Dollar (S$)',
            'NZD' => 'New Zealand Dollar (NZ$)',
            'AED' => 'UAE Dirham (د.إ)',
            'ZAR' => 'South African Rand (R)',
            'BRL' => 'Brazilian Real (R$)',
            'MXN' => 'Mexican Peso (MX$)',
        ];
    }

    /**
     * Get language options.
     */
    protected function getLanguageOptions(): array
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ja' => 'Japanese',
            'zh' => 'Chinese',
            'ko' => 'Korean',
            'ar' => 'Arabic',
        ];
    }
}
