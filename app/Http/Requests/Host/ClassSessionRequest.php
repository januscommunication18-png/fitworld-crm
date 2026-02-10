<?php

namespace App\Http\Requests\Host;

use App\Models\ClassSession;
use App\Services\Schedule\ConflictChecker;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hostId = auth()->user()->host_id;
        $sessionId = $this->route('class_session')?->id;

        $rules = [
            'class_plan_id' => [
                'required',
                Rule::exists('class_plans', 'id')->where('host_id', $hostId),
            ],
            'primary_instructor_id' => [
                'required',
                Rule::exists('instructors', 'id')->where('host_id', $hostId),
            ],
            'backup_instructor_ids' => ['nullable', 'array'],
            'backup_instructor_ids.*' => [
                'nullable',
                'distinct',
                Rule::exists('instructors', 'id')->where('host_id', $hostId),
            ],
            'location_id' => [
                'nullable',
                Rule::exists('locations', 'id')->where('host_id', $hostId),
            ],
            'room_id' => [
                'nullable',
                'exists:rooms,id',
            ],
            'room_ids' => ['nullable', 'array'],
            'room_ids.*' => [
                'nullable',
                'exists:rooms,id',
            ],
            'location_notes' => 'nullable|string|max:2000',
            'title' => 'nullable|string|max:255',
            'session_date' => 'required|date',
            'session_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'capacity' => 'required|integer|min:1|max:500',
            'price' => 'nullable|numeric|min:0|max:9999.99',
            'notes' => 'nullable|string|max:1000',

            // Recurrence fields
            'is_recurring' => 'boolean',
            'recurrence_days' => 'nullable|required_if:is_recurring,true|array',
            'recurrence_days.*' => 'integer|between:0,6',
            'recurrence_end_type' => 'nullable|required_if:is_recurring,true|in:never,after,on',
            'recurrence_count' => 'nullable|required_if:recurrence_end_type,after|integer|min:2|max:52',
            'recurrence_end_date' => 'nullable|required_if:recurrence_end_type,on|date|after:session_date',
        ];

        // For updates, add status validation
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['nullable', Rule::in(array_keys(ClassSession::getStatuses()))];
        }

        return $rules;
    }

    /**
     * Availability warnings (soft validation - stored for controller to handle)
     */
    public array $availabilityWarnings = [];

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $this->validateRoomOwnership($validator);
            $this->validateBackupInstructors($validator);
            $this->validateConflicts($validator);
            $this->validateRoomCapacity($validator);
            $this->collectAvailabilityWarnings();
        });
    }

    protected function validateRoomOwnership($validator): void
    {
        $hostId = auth()->user()->host_id;
        $roomId = $this->getFirstRoomId();

        if (!$roomId) {
            return;
        }

        // Check that the room belongs to a location owned by this host
        $room = \App\Models\Room::with('location')->find($roomId);
        if (!$room || !$room->location || $room->location->host_id !== $hostId) {
            $validator->errors()->add('room_ids', 'The selected room is invalid.');
        }
    }

    protected function validateBackupInstructors($validator): void
    {
        $backupIds = array_filter($this->backup_instructor_ids ?? []);

        if (empty($backupIds)) {
            return;
        }

        // Ensure no backup instructor is the same as the primary instructor
        if (in_array($this->primary_instructor_id, $backupIds)) {
            $validator->errors()->add(
                'backup_instructor_ids',
                'Backup instructors cannot include the primary instructor.'
            );
        }
    }

    protected function validateConflicts($validator): void
    {
        $hostId = auth()->user()->host_id;
        $sessionId = $this->route('class_session')?->id;

        $startTime = Carbon::parse($this->session_date . ' ' . $this->session_time);
        $endTime = $startTime->copy()->addMinutes((int) $this->duration_minutes);

        $conflictChecker = app(ConflictChecker::class);

        // Check primary instructor conflict
        $instructorConflicts = $conflictChecker->hasInstructorConflict(
            $this->primary_instructor_id,
            $startTime,
            $endTime,
            $hostId,
            $sessionId
        );

        if (!empty($instructorConflicts)) {
            $message = $conflictChecker->formatConflictMessage($instructorConflicts, 'instructor');
            $validator->errors()->add('primary_instructor_id', $message);
        }

        // Check room conflict if room is specified
        $roomId = $this->getFirstRoomId();
        if ($roomId) {
            $roomConflicts = $conflictChecker->hasRoomConflict(
                $roomId,
                $startTime,
                $endTime,
                $hostId,
                $sessionId
            );

            if (!empty($roomConflicts)) {
                $message = $conflictChecker->formatConflictMessage($roomConflicts, 'room');
                $validator->errors()->add('room_ids', $message);
            }
        }
    }

    protected function validateRoomCapacity($validator): void
    {
        $roomId = $this->getFirstRoomId();
        if (!$roomId) {
            return;
        }

        $conflictChecker = app(ConflictChecker::class);

        if (!$conflictChecker->validateCapacity($roomId, $this->capacity)) {
            $roomCapacity = $conflictChecker->getRoomCapacity($roomId);
            $validator->errors()->add(
                'capacity',
                "Capacity exceeds room capacity ({$roomCapacity})."
            );
        }
    }

    /**
     * Collect availability warnings (soft validation)
     * These are stored for the controller to handle, not added as errors
     */
    protected function collectAvailabilityWarnings(): void
    {
        // Skip if user has already acknowledged the warnings
        if ($this->boolean('override_availability_warnings')) {
            return;
        }

        $hostId = auth()->user()->host_id;
        $sessionId = $this->route('class_session')?->id;

        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();

        $conflictChecker = app(ConflictChecker::class);

        $this->availabilityWarnings = $conflictChecker->checkInstructorAvailability(
            $this->primary_instructor_id,
            $startTime,
            $endTime,
            $hostId,
            $sessionId
        );
    }

    /**
     * Check if there are availability warnings that need user acknowledgment
     */
    public function hasAvailabilityWarnings(): bool
    {
        return !empty($this->availabilityWarnings) && !$this->boolean('override_availability_warnings');
    }

    /**
     * Get availability warnings
     */
    public function getAvailabilityWarnings(): array
    {
        return $this->availabilityWarnings;
    }

    public function messages(): array
    {
        return [
            'class_plan_id.required' => 'Please select a class.',
            'class_plan_id.exists' => 'The selected class is invalid.',
            'primary_instructor_id.required' => 'Please select a primary instructor.',
            'primary_instructor_id.exists' => 'The selected instructor is invalid.',
            'backup_instructor_ids.*.distinct' => 'You cannot select the same backup instructor twice.',
            'backup_instructor_ids.*.exists' => 'One of the selected backup instructors is invalid.',
            'room_id.required_with' => 'Please select a room when a location is selected.',
            'session_date.required' => 'Please select a date.',
            'session_time.required' => 'Please select a start time.',
            'duration_minutes.required' => 'Please enter the duration.',
            'duration_minutes.min' => 'Duration must be at least 5 minutes.',
            'capacity.required' => 'Please enter the capacity.',
            'capacity.min' => 'Capacity must be at least 1.',
            'recurrence_days.required_if' => 'Please select at least one day for recurring sessions.',
            'recurrence_count.required_if' => 'Please enter the number of occurrences.',
            'recurrence_end_date.required_if' => 'Please select an end date for the recurrence.',
            'recurrence_end_date.after' => 'End date must be after the session date.',
        ];
    }

    /**
     * Get the validated start time
     */
    public function getStartTime(): Carbon
    {
        return Carbon::parse($this->session_date . ' ' . $this->session_time);
    }

    /**
     * Get the validated end time
     */
    public function getEndTime(): Carbon
    {
        return $this->getStartTime()->addMinutes((int) $this->duration_minutes);
    }

    /**
     * Get the first room ID (from room_ids array or room_id)
     */
    public function getFirstRoomId(): ?int
    {
        // Check room_ids array first (from multi-select)
        $roomIds = array_filter($this->room_ids ?? []);
        if (!empty($roomIds)) {
            return (int) reset($roomIds);
        }

        // Fall back to single room_id
        return $this->room_id ? (int) $this->room_id : null;
    }
}
