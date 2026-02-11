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
        // Employment Details
        'employment_type',
        'rate_type',
        'rate_amount',
        'compensation_notes',
        // Workload & Allocation
        'hours_per_week',
        'max_classes_per_week',
        // Working Days
        'working_days',
        // Default Daily Availability
        'availability_default_from',
        'availability_default_to',
        'availability_by_day',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'social_links' => 'array',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'rate_amount' => 'decimal:2',
            'hours_per_week' => 'decimal:2',
            'working_days' => 'array',
            'availability_by_day' => 'array',
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
            return '/storage/' . $this->photo_path;
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

    /**
     * Get employment type options
     */
    public static function getEmploymentTypes(): array
    {
        return [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
        ];
    }

    /**
     * Get rate type options
     */
    public static function getRateTypes(): array
    {
        return [
            'per_hour' => 'Per Hour',
            'per_class' => 'Per Class',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    /**
     * Get day of week options (0=Sunday, 6=Saturday)
     */
    public static function getDayOptions(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * Check if instructor works on a given day (0=Sunday, 6=Saturday)
     */
    public function worksOnDay(int $dayOfWeek): bool
    {
        if (empty($this->working_days)) {
            return true; // No restrictions means all days
        }
        return in_array($dayOfWeek, $this->working_days);
    }

    /**
     * Get availability window for a specific day
     * Returns ['from' => 'HH:MM', 'to' => 'HH:MM'] or null if no restrictions
     */
    public function getAvailabilityForDay(int $dayOfWeek): ?array
    {
        // Check day-specific override first
        if (!empty($this->availability_by_day) && isset($this->availability_by_day[$dayOfWeek])) {
            return $this->availability_by_day[$dayOfWeek];
        }

        // Fall back to default availability
        if ($this->availability_default_from && $this->availability_default_to) {
            return [
                'from' => $this->availability_default_from,
                'to' => $this->availability_default_to,
            ];
        }

        return null; // No availability restrictions
    }

    /**
     * Check if a time range is within instructor's availability for a specific day
     */
    public function isWithinAvailability(int $dayOfWeek, string $startTime, string $endTime): bool
    {
        $availability = $this->getAvailabilityForDay($dayOfWeek);

        if ($availability === null) {
            return true; // No restrictions
        }

        return $startTime >= $availability['from'] && $endTime <= $availability['to'];
    }

    /**
     * Get formatted working days as comma-separated string
     */
    public function getFormattedWorkingDays(): string
    {
        if (empty($this->working_days)) {
            return 'All days';
        }

        $dayNames = self::getDayOptions();
        $days = array_map(fn($d) => $dayNames[$d] ?? '', $this->working_days);
        return implode(', ', array_filter($days));
    }

    /**
     * Get formatted rate display (e.g., "$45.00 / hour")
     */
    public function getFormattedRate(): ?string
    {
        if (!$this->rate_type || !$this->rate_amount) {
            return null;
        }

        $rateTypes = self::getRateTypes();
        $rateLabel = $rateTypes[$this->rate_type] ?? $this->rate_type;

        return '$' . number_format($this->rate_amount, 2) . ' / ' . strtolower(str_replace('Per ', '', $rateLabel));
    }

    /**
     * Get formatted employment type display
     */
    public function getFormattedEmploymentType(): ?string
    {
        if (!$this->employment_type) {
            return null;
        }

        $types = self::getEmploymentTypes();
        return $types[$this->employment_type] ?? $this->employment_type;
    }
}
