<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ScoreEvent extends Model
{
    use HasFactory;

    // Event type constants
    const EVENT_CLASS_ATTENDED = 'class_attended';
    const EVENT_NO_SHOW = 'no_show';
    const EVENT_LATE_CANCEL = 'late_cancel';
    const EVENT_REFERRAL = 'referral';
    const EVENT_MEMBERSHIP_RENEWAL = 'membership_renewal';
    const EVENT_PURCHASE = 'purchase';
    const EVENT_INACTIVITY_PENALTY = 'inactivity_penalty';
    const EVENT_FIRST_PURCHASE = 'first_purchase';
    const EVENT_REVIEW_POSTED = 'review_posted';

    // Default points for each event type
    const DEFAULT_POINTS = [
        self::EVENT_CLASS_ATTENDED => 10,
        self::EVENT_NO_SHOW => -15,
        self::EVENT_LATE_CANCEL => -10,
        self::EVENT_REFERRAL => 50,
        self::EVENT_MEMBERSHIP_RENEWAL => 100,
        self::EVENT_PURCHASE => 5,
        self::EVENT_INACTIVITY_PENALTY => -20,
        self::EVENT_FIRST_PURCHASE => 25,
        self::EVENT_REVIEW_POSTED => 15,
    ];

    public $timestamps = false;

    protected $fillable = [
        'host_id',
        'client_id',
        'event_type',
        'points',
        'score_before',
        'score_after',
        'source_type',
        'source_id',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'score_before' => 'integer',
            'score_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeOfType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    public function scopePositive(Builder $query): Builder
    {
        return $query->where('points', '>', 0);
    }

    public function scopeNegative(Builder $query): Builder
    {
        return $query->where('points', '<', 0);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helpers
    public static function getEventTypes(): array
    {
        return [
            self::EVENT_CLASS_ATTENDED => 'Class Attended',
            self::EVENT_NO_SHOW => 'No Show',
            self::EVENT_LATE_CANCEL => 'Late Cancellation',
            self::EVENT_REFERRAL => 'Referral',
            self::EVENT_MEMBERSHIP_RENEWAL => 'Membership Renewal',
            self::EVENT_PURCHASE => 'Purchase',
            self::EVENT_INACTIVITY_PENALTY => 'Inactivity Penalty',
            self::EVENT_FIRST_PURCHASE => 'First Purchase',
            self::EVENT_REVIEW_POSTED => 'Review Posted',
        ];
    }

    public static function getEventIcon(string $eventType): string
    {
        return match ($eventType) {
            self::EVENT_CLASS_ATTENDED => 'icon-[tabler--check]',
            self::EVENT_NO_SHOW => 'icon-[tabler--user-x]',
            self::EVENT_LATE_CANCEL => 'icon-[tabler--clock-x]',
            self::EVENT_REFERRAL => 'icon-[tabler--users-plus]',
            self::EVENT_MEMBERSHIP_RENEWAL => 'icon-[tabler--refresh]',
            self::EVENT_PURCHASE => 'icon-[tabler--shopping-cart]',
            self::EVENT_INACTIVITY_PENALTY => 'icon-[tabler--zzz]',
            self::EVENT_FIRST_PURCHASE => 'icon-[tabler--gift]',
            self::EVENT_REVIEW_POSTED => 'icon-[tabler--star]',
            default => 'icon-[tabler--circle]',
        };
    }

    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    public function isNegative(): bool
    {
        return $this->points < 0;
    }
}
