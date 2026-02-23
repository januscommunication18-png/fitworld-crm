<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoringRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'event_type',
        'points',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // Scopes
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get points for an event type, using host's custom rules or defaults
     */
    public static function getPointsForEvent(int $hostId, string $eventType): int
    {
        $rule = self::forHost($hostId)
            ->active()
            ->where('event_type', $eventType)
            ->first();

        if ($rule) {
            return $rule->points;
        }

        // Fall back to default points
        return ScoreEvent::DEFAULT_POINTS[$eventType] ?? 0;
    }

    /**
     * Create default scoring rules for a host
     */
    public static function createDefaultRules(int $hostId): void
    {
        $defaults = [
            [
                'event_type' => ScoreEvent::EVENT_CLASS_ATTENDED,
                'points' => 10,
                'description' => 'Points for each class attended',
            ],
            [
                'event_type' => ScoreEvent::EVENT_NO_SHOW,
                'points' => -15,
                'description' => 'Penalty for no-show',
            ],
            [
                'event_type' => ScoreEvent::EVENT_LATE_CANCEL,
                'points' => -10,
                'description' => 'Penalty for late cancellation',
            ],
            [
                'event_type' => ScoreEvent::EVENT_REFERRAL,
                'points' => 50,
                'description' => 'Bonus for referring a new client',
            ],
            [
                'event_type' => ScoreEvent::EVENT_MEMBERSHIP_RENEWAL,
                'points' => 100,
                'description' => 'Bonus for membership renewal',
            ],
            [
                'event_type' => ScoreEvent::EVENT_INACTIVITY_PENALTY,
                'points' => -20,
                'description' => 'Penalty for 30 days of inactivity',
            ],
            [
                'event_type' => ScoreEvent::EVENT_FIRST_PURCHASE,
                'points' => 25,
                'description' => 'Bonus for first purchase',
            ],
        ];

        foreach ($defaults as $index => $rule) {
            self::firstOrCreate(
                ['host_id' => $hostId, 'event_type' => $rule['event_type']],
                array_merge($rule, ['host_id' => $hostId, 'sort_order' => $index, 'is_active' => true])
            );
        }
    }
}
