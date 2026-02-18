<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClassRequest extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_IN_DISCUSSION = 'in_discussion';
    const STATUS_NEED_TO_CONVERT = 'need_to_convert';
    const STATUS_BOOKED = 'booked';

    protected $fillable = [
        'host_id',
        'class_plan_id',
        'service_plan_id',
        'class_session_id',
        'client_id',
        'helpdesk_ticket_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
        'waitlist_requested',
        'source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waitlist_requested' => 'boolean',
        ];
    }

    /**
     * Get the requester's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
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

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function helpdeskTicket(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicket::class);
    }

    public function waitlistEntry(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WaitlistEntry::class);
    }

    public function waitlistEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    // Scopes

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInDiscussion(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_DISCUSSION);
    }

    public function scopeNeedToConvert(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEED_TO_CONVERT);
    }

    public function scopeBooked(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BOOKED);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_BOOKED);
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

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isInDiscussion(): bool
    {
        return $this->status === self::STATUS_IN_DISCUSSION;
    }

    public function isNeedToConvert(): bool
    {
        return $this->status === self::STATUS_NEED_TO_CONVERT;
    }

    public function isBooked(): bool
    {
        return $this->status === self::STATUS_BOOKED;
    }

    public function markAsOpen(): bool
    {
        $this->status = self::STATUS_OPEN;
        return $this->save();
    }

    public function markAsInDiscussion(): bool
    {
        $this->status = self::STATUS_IN_DISCUSSION;
        return $this->save();
    }

    public function markAsNeedToConvert(): bool
    {
        $this->status = self::STATUS_NEED_TO_CONVERT;
        return $this->save();
    }

    public function markAsBooked(int $sessionId = null): bool
    {
        $this->status = self::STATUS_BOOKED;
        if ($sessionId) {
            $this->class_session_id = $sessionId;
        }
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

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'badge-info',
            self::STATUS_IN_DISCUSSION => 'badge-warning',
            self::STATUS_NEED_TO_CONVERT => 'badge-primary',
            self::STATUS_BOOKED => 'badge-success',
            default => 'badge-neutral',
        };
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_DISCUSSION => 'In Discussion',
            self::STATUS_NEED_TO_CONVERT => 'Need to Convert',
            self::STATUS_BOOKED => 'Booked',
        ];
    }

}
