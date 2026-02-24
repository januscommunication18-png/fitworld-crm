<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ClassSession extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'host_id',
        'class_plan_id',
        'primary_instructor_id',
        'backup_instructor_id',
        'location_id',
        'room_id',
        'location_notes',
        'title',
        'start_time',
        'end_time',
        'duration_minutes',
        'capacity',
        'price',
        'status',
        'has_scheduling_conflict',
        'conflict_notes',
        'conflict_resolved_at',
        'conflict_resolved_by',
        'recurrence_rule',
        'recurrence_parent_id',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'cancelled_at' => 'datetime',
            'conflict_resolved_at' => 'datetime',
            'has_scheduling_conflict' => 'boolean',
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'capacity' => 'integer',
        ];
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function classPlan(): BelongsTo
    {
        return $this->belongsTo(ClassPlan::class);
    }

    public function primaryInstructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'primary_instructor_id');
    }

    public function backupInstructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'backup_instructor_id');
    }

    /**
     * Many-to-many relationship for multiple backup instructors
     * Ordered by priority (lower number = higher priority)
     */
    public function backupInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'class_session_backup_instructors')
            ->withPivot('priority')
            ->withTimestamps()
            ->orderBy('priority');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'recurrence_parent_id');
    }

    public function recurrenceChildren(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'recurrence_parent_id');
    }

    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookable');
    }

    public function confirmedBookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookable')
            ->where('status', Booking::STATUS_CONFIRMED);
    }

    /**
     * Membership plans linked to this session for scheduled auto-enrollment
     */
    public function membershipPlans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'class_session_membership_plan')
            ->withTimestamps();
    }

    // Scopes

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>=', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('start_time', '<', now());
    }

    public function scopeForInstructor(Builder $query, $instructorId): Builder
    {
        return $query->where(function ($q) use ($instructorId) {
            $q->where('primary_instructor_id', $instructorId)
              ->orWhereHas('backupInstructors', function ($q2) use ($instructorId) {
                  $q2->where('instructors.id', $instructorId);
              });
        });
    }

    public function scopeForPrimaryInstructor(Builder $query, $instructorId): Builder
    {
        return $query->where('primary_instructor_id', $instructorId);
    }

    public function scopeForRoom(Builder $query, $roomId): Builder
    {
        return $query->where('room_id', $roomId);
    }

    public function scopeForLocation(Builder $query, $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    public function scopeWithConflicts(Builder $query): Builder
    {
        return $query->where('has_scheduling_conflict', true)
            ->whereNull('conflict_resolved_at');
    }

    public function scopeConflictsResolved(Builder $query): Builder
    {
        return $query->where('has_scheduling_conflict', true)
            ->whereNotNull('conflict_resolved_at');
    }

    public function scopeForWeek(Builder $query, $date = null): Builder
    {
        $date = $date ? Carbon::parse($date) : now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();
        return $query->forDateRange($startOfWeek, $endOfWeek);
    }

    // Status methods

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPast(): bool
    {
        return $this->start_time->isPast();
    }

    public function isFuture(): bool
    {
        return $this->start_time->isFuture();
    }

    public function publish(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        $this->status = self::STATUS_PUBLISHED;
        return $this->save();
    }

    public function unpublish(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        $this->status = self::STATUS_DRAFT;
        return $this->save();
    }

    public function cancel(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        return $this->save();
    }

    public function markComplete(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    // Instructor methods

    /**
     * Promote the first backup instructor to primary
     * The current primary becomes the last backup instructor
     */
    public function promoteBackupToPrimary(): bool
    {
        $firstBackup = $this->backupInstructors()->first();

        if (!$firstBackup) {
            return false;
        }

        $currentPrimary = $this->primary_instructor_id;

        // Remove the first backup from the backup list
        $this->backupInstructors()->detach($firstBackup->id);

        // Set the first backup as the new primary
        $this->primary_instructor_id = $firstBackup->id;
        $this->save();

        // Add the old primary as the last backup
        $maxPriority = $this->backupInstructors()->max('priority') ?? 0;
        $this->backupInstructors()->attach($currentPrimary, ['priority' => $maxPriority + 1]);

        return true;
    }

    public function assignSubstitute(int $instructorId): bool
    {
        $this->primary_instructor_id = $instructorId;
        return $this->save();
    }

    /**
     * Check if session has any backup instructors
     */
    public function hasBackupInstructor(): bool
    {
        return $this->backupInstructors()->exists();
    }

    /**
     * Get the first (highest priority) backup instructor
     */
    public function getFirstBackupInstructor(): ?Instructor
    {
        return $this->backupInstructors()->first();
    }

    /**
     * Sync backup instructors with their priorities
     * @param array $instructorIds Array of instructor IDs in priority order
     */
    public function syncBackupInstructors(array $instructorIds): void
    {
        $syncData = [];
        foreach ($instructorIds as $priority => $instructorId) {
            if ($instructorId && $instructorId != $this->primary_instructor_id) {
                $syncData[$instructorId] = ['priority' => $priority + 1];
            }
        }
        $this->backupInstructors()->sync($syncData);
    }

    // Price & Capacity methods

    public function getEffectivePrice(): ?float
    {
        if ($this->price !== null) {
            return (float) $this->price;
        }
        return $this->classPlan?->default_price ? (float) $this->classPlan->default_price : null;
    }

    public function getEffectiveCapacity(): int
    {
        return $this->capacity ?? $this->classPlan?->default_capacity ?? 0;
    }

    public function getAvailableSpots(): int
    {
        // Future: subtract booked attendees
        return $this->getEffectiveCapacity();
    }

    public function isFull(): bool
    {
        return $this->getAvailableSpots() <= 0;
    }

    // Display helpers

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?? $this->classPlan?->name ?? 'Untitled Session';
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->start_time->format('l, M j, Y');
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = $this->getEffectivePrice();
        if ($price === null || $price == 0) {
            return 'Free';
        }
        return '$' . number_format($price, 2);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes} min";
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_PUBLISHED => 'badge-success',
            self::STATUS_COMPLETED => 'badge-info',
            self::STATUS_CANCELLED => 'badge-error',
            default => 'badge-neutral',
        };
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Recurrence helpers

    public function isRecurring(): bool
    {
        return $this->recurrence_rule !== null || $this->recurrence_parent_id !== null;
    }

    public function isRecurrenceParent(): bool
    {
        return $this->recurrence_rule !== null && $this->recurrence_parent_id === null;
    }

    public function isRecurrenceChild(): bool
    {
        return $this->recurrence_parent_id !== null;
    }

    // Conflict management methods

    /**
     * Mark session as having a scheduling conflict
     */
    public function markAsConflict(?string $notes = null): bool
    {
        $this->has_scheduling_conflict = true;
        $this->conflict_notes = $notes;
        $this->conflict_resolved_at = null;
        $this->conflict_resolved_by = null;
        return $this->save();
    }

    /**
     * Resolve the scheduling conflict
     */
    public function resolveConflict(?int $resolvedBy = null): bool
    {
        $this->conflict_resolved_at = now();
        $this->conflict_resolved_by = $resolvedBy ?? auth()->id();
        return $this->save();
    }

    /**
     * Check if session has an unresolved conflict
     */
    public function hasUnresolvedConflict(): bool
    {
        return $this->has_scheduling_conflict && $this->conflict_resolved_at === null;
    }

    /**
     * Check if conflict was resolved
     */
    public function isConflictResolved(): bool
    {
        return $this->has_scheduling_conflict && $this->conflict_resolved_at !== null;
    }

    /**
     * Relationship to user who resolved the conflict
     */
    public function conflictResolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conflict_resolved_by');
    }
}
