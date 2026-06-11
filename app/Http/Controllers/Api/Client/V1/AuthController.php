<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Host;
use App\Rules\ValidName;
use App\Services\Member\MemberAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Branded client app authentication. The login identifier is the client's
 * email — the app build is already scoped to one studio via the app token,
 * so email is unambiguous within it. The second factor is an emailed OTP or
 * a password, per the studio's `member_portal_settings.login_method`. The
 * Client Token ID remains the check-in / reference code (returned in the
 * client payload), not a login credential.
 */
class AuthController extends Controller
{
    public function __construct(protected MemberAuthService $memberAuth) {}

    /**
     * Resolve an email to the login method the app should present next.
     */
    public function lookup(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $validated = $request->validate(['email' => 'required|email|max:255']);

        $client = $this->findByEmail($host, $validated['email']);
        if (! $client) {
            return response()->json([
                'message' => 'We could not find an account with that email at this studio.',
            ], 404);
        }

        return response()->json(['data' => [
            'first_name' => $client->first_name,
            'masked_email' => $this->maskEmail($client->email),
            'login_method' => $host->getMemberPortalSetting('login_method', 'otp'),
        ]]);
    }

    /**
     * Email a one-time code to the client.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $validated = $request->validate(['email' => 'required|email|max:255']);

        $client = $this->findByEmail($host, $validated['email']);
        if (! $client) {
            return response()->json([
                'message' => 'We could not find an account with that email at this studio.',
            ], 404);
        }

        if ($retryAfter = $this->memberAuth->otpRetryAfter($host, $client->email)) {
            return response()->json([
                'message' => "Too many code requests. Try again in {$retryAfter} seconds.",
            ], 429);
        }

        if (! $client->canAttemptOtp()) {
            $minutes = $client->getOtpLockoutMinutesRemaining();

            return response()->json([
                'message' => "Account temporarily locked. Try again in {$minutes} minutes.",
            ], 423);
        }

        $this->memberAuth->sendOtp($host, $client);

        return response()->json([
            'message' => 'We sent a verification code to your email.',
            'data' => ['masked_email' => $this->maskEmail($client->email)],
        ]);
    }

    /**
     * Verify the emailed code and issue a Sanctum token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
            'device_name' => 'required|string|max:100',
        ]);

        $client = $this->findByEmail($host, $validated['email']);
        if (! $client) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        if (! $client->canAttemptOtp()) {
            $minutes = $client->getOtpLockoutMinutesRemaining();

            return response()->json([
                'message' => "Account temporarily locked. Try again in {$minutes} minutes.",
            ], 423);
        }

        if (! $this->memberAuth->verifyOtp($client, $validated['code'])) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        return $this->tokenResponse($client, $validated['device_name']);
    }

    /**
     * Password login (studios with `login_method: password`).
     */
    public function login(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $key = 'client-app-login:'.$host->id.':'.strtolower(trim($validated['email']));
        $maxAttempts = (int) $host->getMemberPortalSetting('max_login_attempts', 10);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Too many login attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $client = $this->findByEmail($host, $validated['email']);

        if (! $client || ! $this->memberAuth->checkPassword($client, $validated['password'])) {
            RateLimiter::hit($key);

            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        RateLimiter::clear($key);

        return $this->tokenResponse($client, $validated['device_name']);
    }

    /**
     * Self-registration (when the studio allows it). The account starts
     * unverified — the client confirms via the OTP that is sent here, then
     * logs in with the returned Client Token ID.
     */
    public function register(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');

        if (! $host->getMemberPortalSetting('allow_self_registration', true)) {
            return response()->json([
                'message' => 'Self-registration is not available for this studio.',
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $existing = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'An account with this email already exists. Sign in instead.',
            ], 422);
        }

        $client = Client::create([
            'host_id' => $host->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'status' => Client::STATUS_INACTIVE,
            'source' => 'member_portal_signup',
            'created_via' => 'mobile',
        ]);

        $clientCode = $client->getOrCreateClientCode();
        $this->memberAuth->sendOtp($host, $client);

        return response()->json([
            'message' => 'Account created. We sent a verification code to your email.',
            'data' => [
                'client_code' => $clientCode,
                'masked_email' => $this->maskEmail($client->email),
            ],
        ], 201);
    }

    /**
     * The signed-in client's profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        return response()->json(['data' => $this->clientPayload($client)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function findByEmail(Host $host, string $email): ?Client
    {
        return Client::where('host_id', $host->id)
            ->where('email', strtolower(trim($email)))
            ->first();
    }

    private function tokenResponse(Client $client, string $deviceName): JsonResponse
    {
        $token = $client->createToken($deviceName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'client' => $this->clientPayload($client),
            ],
        ]);
    }

    private function clientPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'full_name' => $client->full_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'initials' => $client->initials,
            'client_code' => $client->getOrCreateClientCode(),
            'photo_url' => ProfileController::photoUrl($client),
        ];
    }

    private function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('*', max(1, mb_strlen($local) - mb_strlen($visible))).'@'.$domain;
    }
}
