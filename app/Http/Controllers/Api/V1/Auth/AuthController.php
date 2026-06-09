<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StudioResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Staff/owner authentication for the FitCRM mobile app (Sanctum bearer tokens).
 *
 * Studio context for subsequent requests is carried by the `X-Studio-Id`
 * header and validated by the `studio.context` middleware — these endpoints
 * only deal with the user and the studios they belong to.
 */
class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => ['This account is not active. Please contact your studio owner.'],
            ]);
        }

        $hosts = $user->hosts()->get();

        if ($hosts->isEmpty() && ! $user->host) {
            throw ValidationException::withMessages([
                'email' => ['This account is not linked to any studio.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        // Make $request->user() resolve to this user so StudioResource can
        // compute per-studio permissions on this (unauthenticated) route.
        $request->setUserResolver(fn () => $user);

        $device = $data['device_name'] ?? 'mobile';
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
                'studios' => StudioResource::collection($hosts),
                'primary_studio_id' => $user->getPrimaryHost()?->id,
            ],
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'studios' => StudioResource::collection($user->hosts()->get()),
                'primary_studio_id' => $user->getPrimaryHost()?->id,
            ],
        ]);
    }

    /**
     * GET /api/v1/auth/studios
     */
    public function studios(Request $request): JsonResponse
    {
        return response()->json([
            'data' => StudioResource::collection($request->user()->hosts()->get()),
        ]);
    }

    /**
     * POST /api/v1/auth/switch-studio/{host}
     *
     * Validates the user belongs to the studio and returns it. The app then
     * sends the studio id via the `X-Studio-Id` header on future requests.
     */
    public function switchStudio(Request $request, int $host): JsonResponse
    {
        $studio = $request->user()->hosts()->where('hosts.id', $host)->first();

        if (! $studio) {
            return response()->json([
                'message' => 'You do not have access to this studio.',
            ], 403);
        }

        return response()->json([
            'message' => "Switched to {$studio->studio_name}.",
            'data' => [
                'studio' => new StudioResource($studio),
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout — revoke the current device's token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}
