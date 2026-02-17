<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    // Location Types
    public const TYPE_IN_PERSON = 'in_person';
    public const TYPE_PUBLIC = 'public';
    public const TYPE_VIRTUAL = 'virtual';

    // Virtual Platforms
    public const PLATFORM_ZOOM = 'zoom';
    public const PLATFORM_GOOGLE_MEET = 'google_meet';
    public const PLATFORM_TEAMS = 'teams';
    public const PLATFORM_OTHER = 'other';

    protected $fillable = [
        'host_id',
        'name',
        'location_types',
        'location_type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'notes',
        'latitude',
        'longitude',
        'is_default',
        // Public location fields
        'public_location_notes',
        // Virtual location fields
        'virtual_platform',
        'virtual_meeting_link',
        'virtual_access_notes',
        'hide_link_until_booking',
        // Mobile/Travel fields
        'mobile_service_area',
        'mobile_travel_notes',
    ];

    protected function casts(): array
    {
        return [
            'location_types' => 'array',
            'is_default' => 'boolean',
            'hide_link_until_booking' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // ========== Relationships ==========

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    // ========== Scopes ==========

    public function scopeInPerson($query)
    {
        return $query->where('location_type', self::TYPE_IN_PERSON);
    }

    public function scopePublic($query)
    {
        return $query->where('location_type', self::TYPE_PUBLIC);
    }

    public function scopeVirtual($query)
    {
        return $query->where('location_type', self::TYPE_VIRTUAL);
    }

    public function scopePhysical($query)
    {
        return $query->whereIn('location_type', [self::TYPE_IN_PERSON, self::TYPE_PUBLIC]);
    }

    public function scopeActive($query)
    {
        return $query; // All locations are considered active (no is_active column)
    }

    // ========== Type Checkers ==========

    public function isInPerson(): bool
    {
        return $this->location_type === self::TYPE_IN_PERSON;
    }

    public function isPublic(): bool
    {
        return $this->location_type === self::TYPE_PUBLIC;
    }

    public function isVirtual(): bool
    {
        return $this->location_type === self::TYPE_VIRTUAL;
    }

    public function isPhysical(): bool
    {
        return $this->isInPerson() || $this->isPublic();
    }

    public function requiresRoom(): bool
    {
        return $this->isInPerson();
    }

    // ========== Accessors ==========

    /**
     * Get full address as a single string
     */
    public function getFullAddressAttribute(): string
    {
        if ($this->isVirtual()) {
            return 'Online (' . $this->getVirtualPlatformLabelAttribute() . ')';
        }

        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get short address (city, state only)
     */
    public function getShortAddressAttribute(): string
    {
        if ($this->isVirtual()) {
            return 'Online';
        }

        $parts = array_filter([$this->city, $this->state]);
        return implode(', ', $parts);
    }

    /**
     * Get location type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypeLabels()[$this->location_type] ?? $this->location_type;
    }

    /**
     * Get virtual platform label
     */
    public function getVirtualPlatformLabelAttribute(): string
    {
        if (!$this->virtual_platform) {
            return '';
        }

        return self::getPlatformLabels()[$this->virtual_platform] ?? $this->virtual_platform;
    }

    /**
     * Get location type badge class
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->location_type) {
            self::TYPE_IN_PERSON => 'badge-primary',
            self::TYPE_PUBLIC => 'badge-success',
            self::TYPE_VIRTUAL => 'badge-info',
            default => 'badge-neutral',
        };
    }

    /**
     * Get location type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->location_type) {
            self::TYPE_IN_PERSON => 'icon-[tabler--building]',
            self::TYPE_PUBLIC => 'icon-[tabler--trees]',
            self::TYPE_VIRTUAL => 'icon-[tabler--video]',
            default => 'icon-[tabler--map-pin]',
        };
    }

    // ========== Static Helpers ==========

    /**
     * Get all location types with labels
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_IN_PERSON => 'In-Person Studio',
            self::TYPE_PUBLIC => 'Public Location',
            self::TYPE_VIRTUAL => 'Virtual',
        ];
    }

    /**
     * Get all virtual platforms with labels
     */
    public static function getPlatformLabels(): array
    {
        return [
            self::PLATFORM_ZOOM => 'Zoom',
            self::PLATFORM_GOOGLE_MEET => 'Google Meet',
            self::PLATFORM_TEAMS => 'Microsoft Teams',
            self::PLATFORM_OTHER => 'Other',
        ];
    }

    // Mobile Location Type
    public const TYPE_MOBILE = 'mobile';

    /**
     * Get all available location types/categories for multiselect
     */
    public static function getLocationTypeOptions(): array
    {
        return [
            self::TYPE_IN_PERSON => 'In-Person Studio',
            self::TYPE_PUBLIC => 'Public Location',
            self::TYPE_VIRTUAL => 'Virtual',
            self::TYPE_MOBILE => 'Mobile/Travel Studio',
        ];
    }

    // ========== Business Logic ==========

    /**
     * Check if location can be deleted (no rooms or future classes)
     */
    public function canBeDeleted(): bool
    {
        // Virtual locations don't have rooms
        if ($this->isVirtual()) {
            return $this->classSessions()->upcoming()->count() === 0;
        }

        return $this->rooms()->count() === 0
            && $this->classSessions()->upcoming()->count() === 0;
    }

    /**
     * Check if location type can be changed
     */
    public function canChangeType(): bool
    {
        return $this->classSessions()->upcoming()->count() === 0;
    }

    /**
     * Get count of classes scheduled in next 30 days
     */
    public function getUpcomingClassesCountAttribute(): int
    {
        return $this->classSessions()->upcoming()->count();
    }

    /**
     * Get display info for booking pages
     */
    public function getBookingDisplayAttribute(): array
    {
        $display = [
            'name' => $this->name,
            'type' => $this->location_type,
            'type_label' => $this->type_label,
            'icon' => $this->type_icon,
        ];

        if ($this->isInPerson()) {
            $display['address'] = $this->full_address;
            $display['notes'] = $this->notes;
        } elseif ($this->isPublic()) {
            $display['address'] = $this->short_address;
            $display['instructions'] = $this->public_location_notes;
        } else {
            $display['platform'] = $this->virtual_platform_label;
            $display['access_notes'] = $this->virtual_access_notes;
            $display['hide_link'] = $this->hide_link_until_booking;
            if (!$this->hide_link_until_booking) {
                $display['meeting_link'] = $this->virtual_meeting_link;
            }
        }

        return $display;
    }
}
