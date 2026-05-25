<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'color',
        'usage_count',
        'is_active',
        'is_preset',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_preset' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_tag')
            ->withTimestamps();
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // Methods

    public function updateUsageCount(): void
    {
        $this->update(['usage_count' => $this->clients()->count()]);
    }

    // Static helpers

    public static function getDefaultColors(): array
    {
        return [
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#ef4444', // Red
            '#f97316', // Orange
            '#eab308', // Yellow
            '#22c55e', // Green
            '#14b8a6', // Teal
            '#06b6d4', // Cyan
            '#3b82f6', // Blue
        ];
    }

    /**
     * The seed list of tag presets every host gets out of the box.
     * Hosts can edit/delete/add tags after seeding — these are just the starting point.
     */
    public static function getDefaultPresets(): array
    {
        return [
            ['name' => 'Lead',                     'color' => '#f97316'],
            ['name' => 'Trial Client',             'color' => '#14b8a6'],
            ['name' => 'Member',                   'color' => '#22c55e'],
            ['name' => 'Class Pass Holder',        'color' => '#3b82f6'],
            ['name' => 'Drop-In Client',           'color' => '#06b6d4'],
            ['name' => 'Personal Training Client', 'color' => '#6366f1'],
            ['name' => 'Yoga Client',              'color' => '#8b5cf6'],
            ['name' => 'Pilates Client',           'color' => '#ec4899'],
            ['name' => 'Gym Member',               'color' => '#22c55e'],
            ['name' => 'Online Member',            'color' => '#3b82f6'],
            ['name' => 'Family Member',            'color' => '#14b8a6'],
            ['name' => 'Corporate Client',         'color' => '#6366f1'],
            ['name' => 'VIP Client',               'color' => '#eab308'],
            ['name' => 'New Client',               'color' => '#3b82f6'],
            ['name' => 'Returning Client',         'color' => '#22c55e'],
            ['name' => 'High Attendance',          'color' => '#22c55e'],
            ['name' => 'Low Attendance',           'color' => '#eab308'],
            ['name' => 'Payment Issue',            'color' => '#ef4444'],
            ['name' => 'Renewal Due',              'color' => '#f97316'],
            ['name' => 'Cancelled',                'color' => '#ef4444'],
            ['name' => 'Do Not Market',            'color' => '#ef4444'],
        ];
    }

    /**
     * Create any preset tags that don't already exist for the given host.
     * Idempotent: matches by slug, so re-runs do nothing.
     */
    public static function ensureDefaultsForHost($hostId): void
    {
        $presetSlugs = array_map(
            fn ($preset) => Str::slug($preset['name']),
            static::getDefaultPresets()
        );

        // Backfill: flag any pre-existing tag whose slug matches a known preset as is_preset = true.
        // Catches rows that existed before the is_preset column was added, where the column default is false.
        static::forHost($hostId)
            ->whereIn('slug', $presetSlugs)
            ->where('is_preset', false)
            ->update(['is_preset' => true]);

        $existingSlugs = static::forHost($hostId)->pluck('slug')->all();

        foreach (static::getDefaultPresets() as $preset) {
            $slug = Str::slug($preset['name']);
            if (in_array($slug, $existingSlugs, true)) {
                continue;
            }
            static::create([
                'host_id' => $hostId,
                'name' => $preset['name'],
                'slug' => $slug,
                'color' => $preset['color'],
                'is_preset' => true,
                'is_active' => true,
            ]);
        }
    }
}
