<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoringTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'tier',
        'display_name',
        'min_score',
        'max_score',
        'color',
        'icon',
        'benefits',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_score' => 'integer',
            'max_score' => 'integer',
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('min_score', 'asc');
    }

    /**
     * Get tier for a given score
     */
    public static function getTierForScore(int $hostId, int $score): ?self
    {
        return self::forHost($hostId)
            ->where('min_score', '<=', $score)
            ->where(function ($query) use ($score) {
                $query->whereNull('max_score')
                    ->orWhere('max_score', '>=', $score);
            })
            ->orderBy('min_score', 'desc')
            ->first();
    }

    /**
     * Create default tiers for a host
     */
    public static function createDefaultTiers(int $hostId): void
    {
        $defaults = [
            [
                'tier' => 'bronze',
                'display_name' => 'Bronze',
                'min_score' => 0,
                'max_score' => 249,
                'color' => '#CD7F32',
                'icon' => 'icon-[tabler--award]',
                'benefits' => 'Welcome to our community! Keep attending classes to level up.',
            ],
            [
                'tier' => 'silver',
                'display_name' => 'Silver',
                'min_score' => 250,
                'max_score' => 499,
                'color' => '#C0C0C0',
                'icon' => 'icon-[tabler--award-filled]',
                'benefits' => 'You\'re making progress! Enjoy priority booking for popular classes.',
            ],
            [
                'tier' => 'gold',
                'display_name' => 'Gold',
                'min_score' => 500,
                'max_score' => 749,
                'color' => '#FFD700',
                'icon' => 'icon-[tabler--crown]',
                'benefits' => 'You\'re a valued member! Get 10% off retail and early access to workshops.',
            ],
            [
                'tier' => 'vip',
                'display_name' => 'VIP',
                'min_score' => 750,
                'max_score' => null,
                'color' => '#8B5CF6',
                'icon' => 'icon-[tabler--diamond]',
                'benefits' => 'You\'re a star! Enjoy exclusive perks, free guest passes, and VIP events.',
            ],
        ];

        foreach ($defaults as $index => $tier) {
            self::firstOrCreate(
                ['host_id' => $hostId, 'tier' => $tier['tier']],
                array_merge($tier, ['host_id' => $hostId, 'sort_order' => $index])
            );
        }
    }
}
