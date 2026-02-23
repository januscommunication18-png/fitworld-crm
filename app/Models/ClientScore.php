<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClientScore extends Model
{
    use HasFactory;

    // Tier constants
    const TIER_BRONZE = 'bronze';
    const TIER_SILVER = 'silver';
    const TIER_GOLD = 'gold';
    const TIER_VIP = 'vip';

    // Default tier thresholds
    const DEFAULT_THRESHOLDS = [
        self::TIER_BRONZE => ['min' => 0, 'max' => 249],
        self::TIER_SILVER => ['min' => 250, 'max' => 499],
        self::TIER_GOLD => ['min' => 500, 'max' => 749],
        self::TIER_VIP => ['min' => 750, 'max' => 1000],
    ];

    protected $fillable = [
        'host_id',
        'client_id',
        'engagement_score',
        'loyalty_tier',
        'attendance_score',
        'spending_score',
        'engagement_score_component',
        'loyalty_score',
        'total_classes_30d',
        'total_no_shows_30d',
        'total_late_cancels_30d',
        'total_referrals',
        'membership_renewals',
        'days_since_last_visit',
        'previous_score',
        'score_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'engagement_score' => 'integer',
            'attendance_score' => 'integer',
            'spending_score' => 'integer',
            'engagement_score_component' => 'integer',
            'loyalty_score' => 'integer',
            'total_classes_30d' => 'integer',
            'total_no_shows_30d' => 'integer',
            'total_late_cancels_30d' => 'integer',
            'total_referrals' => 'integer',
            'membership_renewals' => 'integer',
            'days_since_last_visit' => 'integer',
            'previous_score' => 'integer',
            'score_calculated_at' => 'datetime',
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

    // Scopes
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeTier(Builder $query, string $tier): Builder
    {
        return $query->where('loyalty_tier', $tier);
    }

    public function scopeHighEngagement(Builder $query, int $minScore = 500): Builder
    {
        return $query->where('engagement_score', '>=', $minScore);
    }

    public function scopeLowEngagement(Builder $query, int $maxScore = 250): Builder
    {
        return $query->where('engagement_score', '<', $maxScore);
    }

    // Helpers
    public static function getTiers(): array
    {
        return [
            self::TIER_BRONZE => 'Bronze',
            self::TIER_SILVER => 'Silver',
            self::TIER_GOLD => 'Gold',
            self::TIER_VIP => 'VIP',
        ];
    }

    public static function getTierColor(string $tier): string
    {
        return match ($tier) {
            self::TIER_BRONZE => '#CD7F32',
            self::TIER_SILVER => '#C0C0C0',
            self::TIER_GOLD => '#FFD700',
            self::TIER_VIP => '#8B5CF6',
            default => '#6B7280',
        };
    }

    public static function getTierIcon(string $tier): string
    {
        return match ($tier) {
            self::TIER_BRONZE => 'icon-[tabler--award]',
            self::TIER_SILVER => 'icon-[tabler--award-filled]',
            self::TIER_GOLD => 'icon-[tabler--crown]',
            self::TIER_VIP => 'icon-[tabler--diamond]',
            default => 'icon-[tabler--star]',
        };
    }

    /**
     * Calculate tier from score using host's custom thresholds or defaults
     */
    public static function calculateTier(int $score, ?array $customThresholds = null): string
    {
        $thresholds = $customThresholds ?? self::DEFAULT_THRESHOLDS;

        foreach ([self::TIER_VIP, self::TIER_GOLD, self::TIER_SILVER, self::TIER_BRONZE] as $tier) {
            if (isset($thresholds[$tier]) && $score >= $thresholds[$tier]['min']) {
                return $tier;
            }
        }

        return self::TIER_BRONZE;
    }

    /**
     * Get score change indicator
     */
    public function getScoreChangeAttribute(): int
    {
        return $this->engagement_score - $this->previous_score;
    }

    /**
     * Get score trend (up, down, stable)
     */
    public function getScoreTrendAttribute(): string
    {
        $change = $this->score_change;
        if ($change > 10) return 'up';
        if ($change < -10) return 'down';
        return 'stable';
    }
}
