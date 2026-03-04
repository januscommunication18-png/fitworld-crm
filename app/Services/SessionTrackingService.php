<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SessionTrackingService
{
    /**
     * Record a new login session
     */
    public function recordLogin(User $user, Request $request): UserSession
    {
        $sessionId = session()->getId();
        $location = $this->getLocationFromIp($request->ip());

        // Use updateOrCreate to handle existing sessions (e.g., re-login with same session)
        return UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'host_id' => $user->host_id,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => $location,
                'logged_in_at' => now(),
                'last_activity_at' => now(),
                'is_active' => true,
                'logged_out_at' => null,
            ]
        );
    }

    /**
     * Record a logout
     */
    public function recordLogout(User $user): void
    {
        $sessionId = session()->getId();

        UserSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'logged_out_at' => now(),
                'is_active' => false,
            ]);
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(string $sessionId): void
    {
        UserSession::where('session_id', $sessionId)
            ->where('is_active', true)
            ->update(['last_activity_at' => now()]);
    }

    /**
     * End inactive sessions (sessions with no activity for X hours)
     */
    public function endInactiveSessions(int $inactiveHours = 24): int
    {
        $cutoff = now()->subHours($inactiveHours);

        return UserSession::where('is_active', true)
            ->where('last_activity_at', '<', $cutoff)
            ->update([
                'logged_out_at' => now(),
                'is_active' => false,
            ]);
    }

    /**
     * Get location from IP address using ip-api.com (free service)
     */
    protected function getLocationFromIp(?string $ip): ?string
    {
        if (!$ip || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return 'Local';
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,city,country',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success') {
                    $city = $data['city'] ?? '';
                    $country = $data['country'] ?? '';
                    return trim("{$city}, {$country}", ', ') ?: null;
                }
            }
        } catch (\Exception $e) {
            // Silently fail - location is not critical
        }

        return null;
    }

    /**
     * Get all sessions for a user
     */
    public function getUserSessions(int $userId, bool $activeOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = UserSession::where('user_id', $userId)
            ->orderBy('logged_in_at', 'desc');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Get all sessions for a host
     */
    public function getHostSessions(int $hostId, int $days = 90): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::where('host_id', $hostId)
            ->where('created_at', '>=', now()->subDays($days))
            ->with('user')
            ->orderBy('logged_in_at', 'desc')
            ->get();
    }

    /**
     * Get concurrent sessions count for a user
     */
    public function getConcurrentSessionsCount(int $userId): int
    {
        return UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Force end all sessions for a user (useful for security)
     */
    public function endAllUserSessions(int $userId, ?string $exceptSessionId = null): int
    {
        $query = UserSession::where('user_id', $userId)
            ->where('is_active', true);

        if ($exceptSessionId) {
            $query->where('session_id', '!=', $exceptSessionId);
        }

        return $query->update([
            'logged_out_at' => now(),
            'is_active' => false,
        ]);
    }
}
