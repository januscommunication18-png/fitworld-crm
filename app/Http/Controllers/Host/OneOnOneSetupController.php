<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\BookingProfile;
use App\Models\Feature;
use App\Models\Instructor;
use Illuminate\Http\Request;

class OneOnOneSetupController extends Controller
{
    /**
     * Display the 1:1 booking setup page.
     */
    public function index()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Find the instructor associated with this user
        $instructor = Instructor::where('host_id', $host->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$instructor) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not associated with an instructor profile.');
        }

        // Get or check for booking profile
        $profile = BookingProfile::where('host_id', $host->id)
            ->where('instructor_id', $instructor->id)
            ->first();

        if (!$profile || !$profile->is_enabled) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have 1:1 booking access. Please contact your studio owner.');
        }

        // Get studio-level settings from feature config
        $studioSettings = $this->getStudioSettings($host);

        return view('host.one-on-one-setup.index', [
            'profile' => $profile,
            'instructor' => $instructor,
            'host' => $host,
            'meetingTypes' => BookingProfile::getMeetingTypes(),
            'durationOptions' => BookingProfile::getDurationOptions(),
            'dayOptions' => BookingProfile::getDayOptions(),
            'studioSettings' => $studioSettings,
        ]);
    }

    /**
     * Get studio-level settings for 1:1 bookings from feature config.
     */
    protected function getStudioSettings($host): array
    {
        $defaults = [
            'buffer_before' => 10,
            'buffer_after' => 10,
            'min_notice_hours' => 24,
            'max_advance_days' => 60,
            'allow_reschedule' => true,
            'reschedule_cutoff_hours' => 24,
            'allow_cancel' => true,
            'cancel_cutoff_hours' => 24,
        ];

        try {
            $hostFeature = $host->features()
                ->where('slug', 'online-1on1-meeting')
                ->first();

            if ($hostFeature && $hostFeature->pivot->config) {
                $config = $hostFeature->pivot->config;
                if (is_string($config)) {
                    $config = json_decode($config, true) ?? [];
                }
                if (is_array($config)) {
                    return array_merge($defaults, $config);
                }
            }

            // Fall back to feature default config
            $feature = Feature::where('slug', 'online-1on1-meeting')->first();
            if ($feature && $feature->default_config) {
                $defaultConfig = $feature->default_config;
                if (is_string($defaultConfig)) {
                    $defaultConfig = json_decode($defaultConfig, true) ?? [];
                }
                if (is_array($defaultConfig)) {
                    return array_merge($defaults, $defaultConfig);
                }
            }
        } catch (\Exception $e) {
            // Log but continue with defaults
        }

        return $defaults;
    }

    /**
     * Store the booking profile settings.
     */
    public function store(Request $request)
    {
        return $this->update($request);
    }

    /**
     * Update the booking profile settings.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $instructor = Instructor::where('host_id', $host->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$instructor) {
            return response()->json([
                'success' => false,
                'message' => 'Instructor profile not found.',
            ], 404);
        }

        $profile = BookingProfile::where('host_id', $host->id)
            ->where('instructor_id', $instructor->id)
            ->first();

        if (!$profile || !$profile->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have 1:1 booking access.',
            ], 403);
        }

        // Validate only instructor-configurable fields
        // Buffer & Limits and Reschedule & Cancellation come from studio settings
        $validated = $request->validate([
            'display_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'meeting_types' => 'required|array|min:1',
            'meeting_types.*' => 'in:in_person,phone,video',
            'video_link' => 'nullable|url|max:500',
            'phone_number' => 'nullable|string|max:50',
            'in_person_location' => 'nullable|string|max:1000',
            'allowed_durations' => 'required|array|min:1',
            'allowed_durations.*' => 'in:15,30,45,60',
            'default_duration' => 'required|in:15,30,45,60',
            'daily_max_meetings' => 'nullable|integer|min:1|max:20',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|between:0,6',
            'default_start_time' => 'required|date_format:H:i',
            'default_end_time' => 'required|date_format:H:i|after:default_start_time',
            'availability_by_day' => 'nullable|array',
        ]);

        // Convert durations to integers
        $validated['allowed_durations'] = array_map('intval', $validated['allowed_durations']);
        $validated['default_duration'] = intval($validated['default_duration']);
        $validated['working_days'] = array_map('intval', $validated['working_days']);

        // Ensure default duration is in allowed durations
        if (!in_array($validated['default_duration'], $validated['allowed_durations'])) {
            $validated['default_duration'] = $validated['allowed_durations'][0];
        }

        // Format times
        $validated['default_start_time'] = $validated['default_start_time'] . ':00';
        $validated['default_end_time'] = $validated['default_end_time'] . ':00';

        // Apply studio-level settings for buffer/limits/reschedule/cancel
        $studioSettings = $this->getStudioSettings($host);
        $validated['buffer_before'] = $studioSettings['buffer_before'];
        $validated['buffer_after'] = $studioSettings['buffer_after'];
        $validated['min_notice_hours'] = $studioSettings['min_notice_hours'];
        $validated['max_advance_days'] = $studioSettings['max_advance_days'];
        $validated['allow_reschedule'] = $studioSettings['allow_reschedule'];
        $validated['reschedule_cutoff_hours'] = $studioSettings['reschedule_cutoff_hours'];
        $validated['allow_cancel'] = $studioSettings['allow_cancel'];
        $validated['cancel_cutoff_hours'] = $studioSettings['cancel_cutoff_hours'];

        // Mark setup as complete
        $validated['is_setup_complete'] = true;
        $validated['setup_completed_at'] = now();

        $profile->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your 1:1 booking profile has been saved.',
                'public_url' => $profile->getPublicUrl(),
            ]);
        }

        return redirect()->route('one-on-one.index')
            ->with('success', 'Your 1:1 booking profile has been saved. You can now accept bookings!');
    }
}
