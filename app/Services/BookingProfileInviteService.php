<?php

namespace App\Services;

use App\Models\BookingProfile;
use App\Models\Instructor;
use App\Models\Host;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Mail\OneOnOneAccessGrantedMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class BookingProfileInviteService
{
    /**
     * Grant 1:1 booking access to an instructor
     */
    public function grantAccess(Instructor $instructor, ?array $defaultConfig = null): BookingProfile
    {
        // Check if profile already exists
        $existingProfile = BookingProfile::where('host_id', $instructor->host_id)
            ->where('instructor_id', $instructor->id)
            ->first();

        if ($existingProfile) {
            // Re-enable if it was disabled
            if (!$existingProfile->is_enabled) {
                $existingProfile->update([
                    'is_enabled' => true,
                    'invited_at' => now(),
                ]);

                // Send re-invitation email
                $this->sendAccessGrantedEmail($instructor, $existingProfile);
            }

            return $existingProfile;
        }

        // Get feature config for defaults
        $host = $instructor->host;
        $featureConfig = $this->getFeatureConfig($host);

        // Create new booking profile with defaults
        $profile = BookingProfile::create([
            'host_id' => $instructor->host_id,
            'instructor_id' => $instructor->id,
            'is_enabled' => true,
            'is_setup_complete' => false,
            'display_name' => $instructor->name,
            'title' => $instructor->specialties[0] ?? null,
            'bio' => $instructor->bio,
            'meeting_types' => ['in_person'],
            'allowed_durations' => $featureConfig['default_durations'] ?? [30, 60],
            'default_duration' => $featureConfig['default_durations'][0] ?? 30,
            'buffer_before' => $featureConfig['default_buffer'] ?? 10,
            'buffer_after' => $featureConfig['default_buffer'] ?? 10,
            'min_notice_hours' => 24,
            'max_advance_days' => 60,
            'working_days' => $instructor->working_days ?? [1, 2, 3, 4, 5],
            'availability_by_day' => $instructor->availability_by_day,
            'default_start_time' => $instructor->availability_default_from ?? '09:00:00',
            'default_end_time' => $instructor->availability_default_to ?? '17:00:00',
            'allow_reschedule' => true,
            'reschedule_cutoff_hours' => 24,
            'allow_cancel' => true,
            'cancel_cutoff_hours' => 24,
            'invited_at' => now(),
        ]);

        // Send access granted email
        $this->sendAccessGrantedEmail($instructor, $profile);

        return $profile;
    }

    /**
     * Revoke 1:1 booking access
     */
    public function revokeAccess(BookingProfile $profile): void
    {
        // Just disable the profile, don't delete (preserve history)
        $profile->update([
            'is_enabled' => false,
        ]);
    }

    /**
     * Permanently delete a booking profile
     */
    public function deleteProfile(BookingProfile $profile): void
    {
        // Check for existing bookings
        $hasUpcomingBookings = $profile->bookings()
            ->where('status', 'confirmed')
            ->where('start_time', '>', now())
            ->exists();

        if ($hasUpcomingBookings) {
            throw new Exception('Cannot delete profile with upcoming bookings. Cancel the bookings first.');
        }

        $profile->delete();
    }

    /**
     * Resend access invitation email
     */
    public function resendInvitation(BookingProfile $profile): void
    {
        $instructor = $profile->instructor;

        if (!$instructor) {
            throw new Exception('Instructor not found for this profile.');
        }

        $profile->update(['invited_at' => now()]);

        $this->sendAccessGrantedEmail($instructor, $profile);
    }

    /**
     * Send access granted email
     */
    private function sendAccessGrantedEmail(Instructor $instructor, BookingProfile $profile): void
    {
        if (!$instructor->email) {
            return;
        }

        try {
            // Check if instructor has a user account
            $hasUserAccount = $instructor->user_id !== null;
            $invitation = null;

            // If no user account, check for existing user by email or create invitation
            if (!$hasUserAccount) {
                // Check if there's an existing user with this email
                $existingUser = User::where('email', $instructor->email)->first();

                if ($existingUser) {
                    // Link the user to the instructor
                    $instructor->update(['user_id' => $existingUser->id]);
                    $hasUserAccount = true;
                } else {
                    // Create or get a pending team invitation
                    $invitation = $this->getOrCreateInvitation($instructor);
                }
            }

            if (class_exists(OneOnOneAccessGrantedMail::class)) {
                Mail::to($instructor->email)
                    ->send(new OneOnOneAccessGrantedMail($instructor, $profile, $hasUserAccount, $invitation));
            }
        } catch (Exception $e) {
            \Log::error('Failed to send 1:1 access granted email: ' . $e->getMessage());
        }
    }

    /**
     * Get or create a team invitation for an instructor
     */
    private function getOrCreateInvitation(Instructor $instructor): TeamInvitation
    {
        // Check for existing pending invitation
        $existingInvitation = TeamInvitation::where('host_id', $instructor->host_id)
            ->where('email', $instructor->email)
            ->where('status', TeamInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return $existingInvitation;
        }

        // Parse instructor name into first/last name
        $nameParts = explode(' ', $instructor->name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Create new invitation
        return TeamInvitation::create([
            'host_id' => $instructor->host_id,
            'email' => $instructor->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'instructor',
            'permissions' => ['schedule.view_own', 'bookings.view_own'],
            'instructor_id' => $instructor->id,
            'token' => TeamInvitation::generateToken(),
            'status' => TeamInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
            'invited_by' => auth()->id(),
        ]);
    }

    /**
     * Get feature configuration for a host
     */
    private function getFeatureConfig(Host $host): array
    {
        try {
            $hostFeature = $host->features()
                ->where('slug', 'online-1on1-meeting')
                ->first();

            if ($hostFeature && $hostFeature->pivot->config) {
                $config = $hostFeature->pivot->config;
                // Handle JSON string or array
                if (is_string($config)) {
                    $config = json_decode($config, true) ?? [];
                }
                return is_array($config) ? $config : [];
            }

            // Return feature default config
            $feature = \App\Models\Feature::where('slug', 'online-1on1-meeting')->first();
            $defaultConfig = $feature?->default_config;
            if (is_string($defaultConfig)) {
                $defaultConfig = json_decode($defaultConfig, true) ?? [];
            }
            return is_array($defaultConfig) ? $defaultConfig : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all instructors with booking profile status for a host
     */
    public function getInstructorsWithStatus(Host $host): array
    {
        $instructors = $host->instructors()->with('bookingProfile')->get();

        return $instructors->map(function ($instructor) {
            $profile = $instructor->bookingProfile;

            return [
                'instructor' => $instructor,
                'has_access' => $profile && $profile->is_enabled,
                'is_setup_complete' => $profile && $profile->is_setup_complete,
                'profile' => $profile,
            ];
        })->toArray();
    }
}
