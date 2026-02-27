<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpaceRentalConfig extends Model
{
    use HasFactory, SoftDeletes;

    // Rentable type constants
    const TYPE_LOCATION = 'location';
    const TYPE_ROOM = 'room';

    // Purpose constants
    const PURPOSE_PHOTO_SHOOT = 'photo_shoot';
    const PURPOSE_VIDEO_PRODUCTION = 'video_production';
    const PURPOSE_WORKSHOP = 'workshop';
    const PURPOSE_TRAINING = 'training';
    const PURPOSE_OTHER = 'other';

    protected $fillable = [
        'host_id',
        'rentable_type',
        'location_id',
        'room_id',
        'name',
        'description',
        'hourly_rates',
        'minimum_hours',
        'maximum_hours',
        'deposit_rates',
        'allowed_purposes',
        'amenities_included',
        'rules',
        'setup_time_minutes',
        'cleanup_time_minutes',
        'requires_waiver',
        'waiver_document_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rates' => 'array',
            'deposit_rates' => 'array',
            'allowed_purposes' => 'array',
            'amenities_included' => 'array',
            'is_active' => 'boolean',
            'requires_waiver' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(SpaceRental::class);
    }

    /**
     * Multi-currency price methods
     */
    public function getHourlyRateForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->hourly_rates) && isset($this->hourly_rates[$currency])) {
            return (float) $this->hourly_rates[$currency];
        }

        return null;
    }

    public function getDepositForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->deposit_rates) && isset($this->deposit_rates[$currency])) {
            return (float) $this->deposit_rates[$currency];
        }

        return null;
    }

    public function getFormattedHourlyRateForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $rate = $this->getHourlyRateForCurrency($currency);

        if ($rate === null) {
            return 'Not Set';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($rate, 2) . '/hr';
    }

    public function getFormattedDepositForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $deposit = $this->getDepositForCurrency($currency);

        if ($deposit === null || $deposit == 0) {
            return 'None';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($deposit, 2);
    }

    public function hasHourlyRateForCurrency(string $currency): bool
    {
        return !empty($this->hourly_rates) && isset($this->hourly_rates[$currency]) && $this->hourly_rates[$currency] !== null;
    }

    /**
     * Purpose helpers
     */
    public static function getPurposes(): array
    {
        return [
            self::PURPOSE_PHOTO_SHOOT => 'Photo Shoot',
            self::PURPOSE_VIDEO_PRODUCTION => 'Video Production',
            self::PURPOSE_WORKSHOP => 'Workshop',
            self::PURPOSE_TRAINING => 'Training',
            self::PURPOSE_OTHER => 'Other',
        ];
    }

    public static function getPurposeIcon(string $purpose): string
    {
        return match ($purpose) {
            self::PURPOSE_PHOTO_SHOOT => 'camera',
            self::PURPOSE_VIDEO_PRODUCTION => 'video',
            self::PURPOSE_WORKSHOP => 'users',
            self::PURPOSE_TRAINING => 'school',
            default => 'calendar-event',
        };
    }

    public function allowsPurpose(string $purpose): bool
    {
        if (empty($this->allowed_purposes)) {
            return true; // If no restrictions, allow all
        }

        return in_array($purpose, $this->allowed_purposes);
    }

    public function getAllowedPurposesLabels(): array
    {
        if (empty($this->allowed_purposes)) {
            return ['All purposes allowed'];
        }

        $purposes = self::getPurposes();
        return array_map(fn($p) => $purposes[$p] ?? $p, $this->allowed_purposes);
    }

    /**
     * Type helpers
     */
    public static function getRentableTypes(): array
    {
        return [
            self::TYPE_LOCATION => 'Entire Location',
            self::TYPE_ROOM => 'Specific Room',
        ];
    }

    public function isLocationType(): bool
    {
        return $this->rentable_type === self::TYPE_LOCATION;
    }

    public function isRoomType(): bool
    {
        return $this->rentable_type === self::TYPE_ROOM;
    }

    /**
     * Display helpers
     */
    public function getSpaceNameAttribute(): string
    {
        if ($this->isRoomType() && $this->room) {
            return $this->room->name . ' @ ' . ($this->location?->name ?? 'Unknown');
        }

        return $this->location?->name ?? $this->name;
    }

    public function getTypeIconAttribute(): string
    {
        return $this->isLocationType() ? 'building' : 'door';
    }

    public function getMinMaxHoursDisplayAttribute(): string
    {
        $min = $this->minimum_hours;
        $max = $this->maximum_hours;

        if ($max) {
            return "{$min}-{$max} hours";
        }

        return "{$min}+ hours";
    }

    public function getTotalBufferMinutesAttribute(): int
    {
        return ($this->setup_time_minutes ?? 0) + ($this->cleanup_time_minutes ?? 0);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where(function ($q) use ($locationId) {
            $q->where('location_id', $locationId)
              ->orWhereHas('room', fn($r) => $r->where('location_id', $locationId));
        });
    }

    public function scopeForRoom($query, int $roomId)
    {
        return $query->where('rentable_type', self::TYPE_ROOM)
            ->where('room_id', $roomId);
    }

    public function scopeAllowsPurpose($query, string $purpose)
    {
        return $query->where(function ($q) use ($purpose) {
            $q->whereNull('allowed_purposes')
              ->orWhereJsonContains('allowed_purposes', $purpose);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('rentable_type', $type);
    }
}
