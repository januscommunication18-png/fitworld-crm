<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Instructor extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'host_id',
        'user_id',
        'name',
        'email',
        'phone',
        'photo_path',
        'bio',
        'specialties',
        'certifications',
        'social_links',
        'is_visible',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'social_links' => 'array',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(StudioClass::class);
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(TeamInvitation::class);
    }

    public function servicePlans(): BelongsToMany
    {
        return $this->belongsToMany(ServicePlan::class, 'service_plan_instructors')
            ->withPivot(['custom_price', 'is_active'])
            ->withTimestamps();
    }

    public function activeServicePlans(): BelongsToMany
    {
        return $this->servicePlans()->wherePivot('is_active', true);
    }

    public function serviceSlots(): HasMany
    {
        return $this->hasMany(ServiceSlot::class);
    }

    public function primarySessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'primary_instructor_id');
    }

    public function backupSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'backup_instructor_id');
    }

    public function allSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'primary_instructor_id')
            ->orWhere('backup_instructor_id', $this->id);
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return Storage::url($this->photo_path);
        }
        return null;
    }

    /**
     * Check if instructor has a linked user account
     */
    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Check if instructor has a pending invitation
     */
    public function hasPendingInvitation(): bool
    {
        return $this->invitation()
            ->where('status', TeamInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Check if instructor can be deactivated (no future classes)
     */
    public function canDeactivate(): bool
    {
        // TODO: Check for future scheduled classes when scheduling is implemented
        return true;
    }

    /**
     * Scope visible instructors
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)->where('is_active', true);
    }

    /**
     * Scope active instructors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
        ];
    }

    /**
     * Get common specialties
     */
    public static function getCommonSpecialties(): array
    {
        return [
            'Yoga',
            'Pilates',
            'HIIT',
            'Strength Training',
            'Cycling',
            'Meditation',
            'Barre',
            'Dance',
            'Boxing',
            'CrossFit',
            'Stretching',
            'Cardio',
        ];
    }
}
