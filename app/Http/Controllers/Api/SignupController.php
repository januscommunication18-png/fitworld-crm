<?php

namespace App\Http\Controllers\Api;

use App\Events\HostOnboardingCompleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Signup\ClassSetupRequest;
use App\Http\Requests\Signup\InstructorsRequest;
use App\Http\Requests\Signup\LocationRequest;
use App\Http\Requests\Signup\RegisterRequest;
use App\Http\Requests\Signup\StudioBasicsRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\StudioClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SignupController extends Controller
{
    use ApiResponse;

    /**
     * Get current onboarding progress and saved data.
     */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        $host = $user->host;

        // Get instructors for step 6
        $instructors = $host->instructors()
            ->where('user_id', '!=', $user->id)
            ->get(['name', 'email'])
            ->toArray();

        // Get first class for step 7
        $class = $host->classes()->first();

        return $this->success([
            'step' => $host->onboarding_step ?? 4,
            'form_data' => [
                // Step 2: Account (already saved, don't return password)
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'is_studio_owner' => $user->role === 'owner',
                // Step 4: Studio
                'studio_name' => $host->studio_name,
                'studio_types' => $host->studio_types ?? [],
                'city' => $host->city ?? '',
                'timezone' => $host->timezone ?? 'America/New_York',
                'subdomain' => $host->subdomain ?? '',
                // Step 5: Location
                'address' => $host->address ?? '',
                'rooms' => $host->rooms ?? 1,
                'default_capacity' => $host->default_capacity ?? 20,
                'amenities' => $host->amenities ?? [],
                // Step 6: Instructors
                'add_self_as_instructor' => $user->is_instructor,
                'instructors' => $instructors,
                // Step 7: Class
                'skip_class_setup' => !$class,
                'class_name' => $class?->name ?? '',
                'class_type' => $class?->type ?? '',
                'class_duration' => $class?->duration_minutes ?? 60,
                'class_capacity' => $class?->capacity ?? 20,
                'class_price' => $class?->price,
                // Step 8: Payments
                'skip_payments' => !$host->stripe_account_id,
                'stripe_connected' => (bool) $host->stripe_account_id,
            ],
        ]);
    }

    /**
     * Step 2: Create user account + host record.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            // Create the host record
            $host = Host::create([
                'studio_name' => $data['first_name'] . "'s Studio",
            ]);

            // Create the user linked to this host
            $user = $host->users()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'owner',
                'is_instructor' => false,
            ]);

            // Create Sanctum token for subsequent API calls
            $token = $user->createToken('signup')->plainTextToken;

            return compact('user', 'host', 'token');
        });

        // Log the user into the session as well (for web auth)
        Auth::login($result['user']);

        return $this->success([
            'user' => [
                'id' => $result['user']->id,
                'first_name' => $result['user']->first_name,
                'last_name' => $result['user']->last_name,
                'email' => $result['user']->email,
            ],
            'host' => [
                'id' => $result['host']->id,
                'studio_name' => $result['host']->studio_name,
            ],
            'token' => $result['token'],
        ], 'Account created successfully', 201);
    }

    /**
     * Step 3: Resend email verification.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    /**
     * Step 4: Check subdomain availability.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/'],
        ]);

        $subdomain = $request->input('subdomain');
        $exists = Host::where('subdomain', $subdomain)->exists();

        return $this->success([
            'subdomain' => $subdomain,
            'available' => !$exists,
        ]);
    }

    /**
     * Step 4: Save studio basics.
     */
    public function saveStudio(StudioBasicsRequest $request): JsonResponse
    {
        $host = $request->user()->host;
        $data = $request->validated();

        $host->update([
            'studio_name' => $data['studio_name'],
            'studio_types' => $data['studio_types'] ?? [],
            'city' => $data['city'] ?? null,
            'timezone' => $data['timezone'],
            'subdomain' => $data['subdomain'],
            'onboarding_step' => max($host->onboarding_step ?? 4, 5),
        ]);

        return $this->success($host->fresh(), 'Studio basics saved');
    }

    /**
     * Step 5: Save location and space details.
     */
    public function saveLocation(LocationRequest $request): JsonResponse
    {
        $host = $request->user()->host;
        $data = $request->validated();

        $host->update([
            'address' => $data['address'] ?? null,
            'rooms' => $data['rooms'] ?? 1,
            'default_capacity' => $data['default_capacity'] ?? 20,
            'amenities' => $data['amenities'] ?? [],
            'onboarding_step' => max($host->onboarding_step ?? 5, 6),
        ]);

        return $this->success($host->fresh(), 'Location saved');
    }

    /**
     * Step 6: Save instructors.
     */
    public function saveInstructors(InstructorsRequest $request): JsonResponse
    {
        $host = $request->user()->host;
        $user = $request->user();
        $data = $request->validated();

        // Remove existing instructors for this host (re-save pattern)
        $host->instructors()->delete();

        $instructors = [];

        // Add self as instructor if checked
        if (!empty($data['add_self_as_instructor'])) {
            $user->update(['is_instructor' => true]);

            $instructors[] = $host->instructors()->create([
                'user_id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'invite_status' => 'accepted',
            ]);
        } else {
            $user->update(['is_instructor' => false]);
        }

        // Add additional instructors
        if (!empty($data['instructors'])) {
            foreach ($data['instructors'] as $instructorData) {
                if (empty($instructorData['name'])) {
                    continue;
                }
                $instructors[] = $host->instructors()->create([
                    'name' => $instructorData['name'],
                    'email' => $instructorData['email'] ?? null,
                    'invite_status' => 'pending',
                ]);
            }
        }

        // Update onboarding step
        $host->update(['onboarding_step' => max($host->onboarding_step ?? 6, 7)]);

        return $this->success($instructors, 'Instructors saved');
    }

    /**
     * Step 7: Save first class.
     */
    public function saveClass(ClassSetupRequest $request): JsonResponse
    {
        $host = $request->user()->host;
        $data = $request->validated();

        if (!empty($data['skip_class_setup'])) {
            $host->update(['onboarding_step' => max($host->onboarding_step ?? 7, 8)]);
            return $this->success(null, 'Class setup skipped');
        }

        // Remove any existing classes created during signup (re-save pattern)
        $host->classes()->delete();

        $class = $host->classes()->create([
            'name' => $data['class_name'],
            'type' => $data['class_type'] ?? null,
            'duration_minutes' => $data['class_duration'] ?? 60,
            'capacity' => $data['class_capacity'] ?? 20,
            'price' => $data['class_price'] ?? null,
            'is_active' => true,
        ]);

        // Update onboarding step
        $host->update(['onboarding_step' => max($host->onboarding_step ?? 7, 8)]);

        return $this->success($class, 'Class created');
    }

    /**
     * Step 8: Save payment preferences.
     */
    public function savePayments(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $request->validate([
            'skip_payments' => ['boolean'],
            'stripe_connected' => ['boolean'],
        ]);

        if (!$request->boolean('skip_payments') && $request->boolean('stripe_connected')) {
            // Placeholder: actual Stripe Connect OAuth would set this
            $host->update([
                'stripe_account_id' => 'pending_connect',
                'onboarding_step' => max($host->onboarding_step ?? 8, 9),
            ]);
        } else {
            $host->update(['onboarding_step' => max($host->onboarding_step ?? 8, 9)]);
        }

        return $this->success(null, 'Payment preferences saved');
    }

    /**
     * Step 9: Mark onboarding complete and go live.
     */
    public function complete(Request $request): JsonResponse
    {
        $host = $request->user()->host;

        $host->update([
            'is_live' => true,
            'onboarding_completed_at' => now(),
        ]);

        HostOnboardingCompleted::dispatch($host);

        return $this->success([
            'host' => $host->fresh(),
            'dashboard_url' => '/dashboard',
        ], 'Onboarding complete! Your studio is live.');
    }
}
