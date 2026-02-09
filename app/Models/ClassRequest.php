<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClassRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'host_id',
        'class_plan_id',
        'service_plan_id',
        'requester_name',
        'requester_email',
        'preferred_days',
        'preferred_times',
        'notes',
        'status',
        'scheduled_session_id',
    ];

    protected function casts(): array
    {
        return [
            'preferred_days' => 'array',
            'preferred_times' => 'array',
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

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function scheduledSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'scheduled_session_id');
    }

    // Scopes

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeIgnored(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IGNORED);
    }

    public function scopeForClassPlan(Builder $query, $classPlanId): Builder
    {
        return $query->where('class_plan_id', $classPlanId);
    }

    public function scopeForServicePlan(Builder $query, $servicePlanId): Builder
    {
        return $query->where('service_plan_id', $servicePlanId);
    }

    public function scopeForClasses(Builder $query): Builder
    {
        return $query->whereNotNull('class_plan_id');
    }

    public function scopeForServices(Builder $query): Builder
    {
        return $query->whereNotNull('service_plan_id');
    }

    // Status methods

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isIgnored(): bool
    {
        return $this->status === self::STATUS_IGNORED;
    }

    public function markAsScheduled(int $sessionId): bool
    {
        $this->status = self::STATUS_SCHEDULED;
        $this->scheduled_session_id = $sessionId;
        return $this->save();
    }

    public function markAsIgnored(): bool
    {
        $this->status = self::STATUS_IGNORED;
        return $this->save();
    }

    public function markAsPending(): bool
    {
        $this->status = self::STATUS_PENDING;
        $this->scheduled_session_id = null;
        return $this->save();
    }

    // Helper methods

    public function isClassRequest(): bool
    {
        return $this->class_plan_id !== null;
    }

    public function isServiceRequest(): bool
    {
        return $this->service_plan_id !== null;
    }

    public function getPlan(): ClassPlan|ServicePlan|null
    {
        return $this->classPlan ?? $this->servicePlan;
    }

    public function getTypeLabel(): string
    {
        return $this->isClassRequest() ? 'Class' : 'Service';
    }

    public function getFormattedPreferredDaysAttribute(): string
    {
        if (empty($this->preferred_days)) {
            return 'Any day';
        }
        return implode(', ', $this->preferred_days);
    }

    public function getFormattedPreferredTimesAttribute(): string
    {
        if (empty($this->preferred_times)) {
            return 'Any time';
        }
        return implode(', ', $this->preferred_times);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_SCHEDULED => 'badge-success',
            self::STATUS_IGNORED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IGNORED => 'Ignored',
        ];
    }

    public static function getDayOptions(): array
    {
        return [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];
    }

    public static function getTimeOptions(): array
    {
        return [
            'Early Morning (6-9 AM)',
            'Morning (9 AM-12 PM)',
            'Afternoon (12-5 PM)',
            'Evening (5-9 PM)',
        ];
    }
}
