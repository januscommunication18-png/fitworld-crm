<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Segment extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_STATIC = 'static';
    const TYPE_DYNAMIC = 'dynamic';
    const TYPE_SMART = 'smart';

    // Tier constants for smart segments
    const TIER_BRONZE = 'bronze';
    const TIER_SILVER = 'silver';
    const TIER_GOLD = 'gold';
    const TIER_VIP = 'vip';

    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'description',
        'color',
        'type',
        'tier',
        'min_score',
        'max_score',
        'is_active',
        'is_system',
        'member_count',
        'member_count_updated_at',
        'total_revenue',
        'avg_visit_frequency',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'member_count' => 'integer',
            'member_count_updated_at' => 'datetime',
            'total_revenue' => 'decimal:2',
            'avg_visit_frequency' => 'decimal:2',
            'min_score' => 'integer',
            'max_score' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($segment) {
            if (empty($segment->slug)) {
                $segment->slug = Str::slug($segment->name);
            }
        });

        static::updating(function ($segment) {
            if ($segment->isDirty('name') && !$segment->isDirty('slug')) {
                $segment->slug = Str::slug($segment->name);
            }
        });
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(SegmentRule::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_segment')
            ->withPivot(['added_by', 'matched_at'])
            ->withTimestamps();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
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

    public function scopeStatic(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_STATIC);
    }

    public function scopeDynamic(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DYNAMIC);
    }

    public function scopeSmart(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SMART);
    }

    // Helpers
    public static function getTypes(): array
    {
        return [
            self::TYPE_STATIC => 'Static (Manual)',
            self::TYPE_DYNAMIC => 'Dynamic (Rule-based)',
            self::TYPE_SMART => 'Smart (Score-based)',
        ];
    }

    public static function getTiers(): array
    {
        return [
            self::TIER_BRONZE => 'Bronze',
            self::TIER_SILVER => 'Silver',
            self::TIER_GOLD => 'Gold',
            self::TIER_VIP => 'VIP',
        ];
    }

    public function isStatic(): bool
    {
        return $this->type === self::TYPE_STATIC;
    }

    public function isDynamic(): bool
    {
        return $this->type === self::TYPE_DYNAMIC;
    }

    public function isSmart(): bool
    {
        return $this->type === self::TYPE_SMART;
    }

    /**
     * Update the member count cache
     */
    public function updateMemberCount(): void
    {
        $this->update([
            'member_count' => $this->clients()->count(),
            'member_count_updated_at' => now(),
        ]);
    }

    /**
     * Get clients matching this segment's rules (for dynamic segments)
     */
    public function getMatchingClientsQuery(): Builder
    {
        if ($this->type !== self::TYPE_DYNAMIC || $this->rules->isEmpty()) {
            return Client::where('id', 0); // Empty query
        }

        $query = Client::forHost($this->host_id)->active();

        // Group rules by group_index
        $ruleGroups = $this->rules->groupBy('group_index');

        // Apply OR between groups, AND within groups
        $query->where(function ($q) use ($ruleGroups) {
            foreach ($ruleGroups as $groupIndex => $rules) {
                $q->orWhere(function ($groupQuery) use ($rules) {
                    foreach ($rules as $rule) {
                        $rule->applyToQuery($groupQuery);
                    }
                });
            }
        });

        return $query;
    }

    /**
     * Sync dynamic segment membership
     */
    public function syncDynamicMembership(): int
    {
        if ($this->type !== self::TYPE_DYNAMIC) {
            return 0;
        }

        $matchingClientIds = $this->getMatchingClientsQuery()->pluck('id');

        // Sync the pivot table
        $this->clients()->sync(
            $matchingClientIds->mapWithKeys(fn ($id) => [$id => ['matched_at' => now()]])->toArray()
        );

        $this->updateMemberCount();

        return $matchingClientIds->count();
    }
}
