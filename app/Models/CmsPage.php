<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    use HasFactory;

    const TYPE_TERMS_CONDITIONS = 'terms_conditions';
    const TYPE_PRIVACY_POLICY = 'privacy_policy';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'content',
        'status',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title) . '-' . Str::random(6);
            }
        });

        static::saving(function ($page) {
            // If setting to active, deactivate other pages of the same type
            if ($page->isDirty('status') && $page->status === self::STATUS_ACTIVE) {
                static::where('type', $page->type)
                    ->where('id', '!=', $page->id ?? 0)
                    ->where('status', self::STATUS_ACTIVE)
                    ->update(['status' => self::STATUS_INACTIVE]);

                // Set published_at if not already set
                if (!$page->published_at) {
                    $page->published_at = now();
                }
            }
        });
    }

    // Relationships

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTermsConditions($query)
    {
        return $query->where('type', self::TYPE_TERMS_CONDITIONS);
    }

    public function scopePrivacyPolicy($query)
    {
        return $query->where('type', self::TYPE_PRIVACY_POLICY);
    }

    // Helpers

    public static function getTypes(): array
    {
        return [
            self::TYPE_TERMS_CONDITIONS => 'Terms & Conditions',
            self::TYPE_PRIVACY_POLICY => 'Privacy Policy',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_INACTIVE => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get the active page for a specific type
     */
    public static function getActivePage(string $type): ?self
    {
        return static::where('type', $type)
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Get active Terms & Conditions page
     */
    public static function getActiveTermsConditions(): ?self
    {
        return static::getActivePage(self::TYPE_TERMS_CONDITIONS);
    }

    /**
     * Get active Privacy Policy page
     */
    public static function getActivePrivacyPolicy(): ?self
    {
        return static::getActivePage(self::TYPE_PRIVACY_POLICY);
    }
}
