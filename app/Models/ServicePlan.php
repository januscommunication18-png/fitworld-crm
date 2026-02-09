<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServicePlan extends Model
{
    use HasFactory;

    const CATEGORY_PRIVATE_TRAINING = 'private_training';
    const CATEGORY_CONSULTATION = 'consultation';
    const CATEGORY_THERAPY = 'therapy';
    const CATEGORY_OTHER = 'other';

    const LOCATION_IN_STUDIO = 'in_studio';
    const LOCATION_ONLINE = 'online';
    const LOCATION_CLIENT_LOCATION = 'client_location';

    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'description',
        'category',
        'duration_minutes',
        'buffer_minutes',
        'price',
        'deposit_amount',
        'location_type',
        'max_participants',
        'image_path',
        'color',
        'booking_notice_hours',
        'cancellation_hours',
        'is_active',
        'is_visible_on_booking_page',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'is_visible_on_booking_page' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($servicePlan) {
            if (empty($servicePlan->slug)) {
                $servicePlan->slug = Str::slug($servicePlan->name);
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'service_plan_instructors')
            ->withPivot(['custom_price', 'is_active'])
            ->withTimestamps();
    }

    public function activeInstructors(): BelongsToMany
    {
        return $this->instructors()->wherePivot('is_active', true);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(ServiceSlot::class);
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        return null;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return 'Free';
        }
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes} min";
    }

    /**
     * Get price for a specific instructor (custom or default)
     */
    public function getPriceForInstructor(Instructor $instructor): ?float
    {
        $pivot = $this->instructors()->where('instructor_id', $instructor->id)->first()?->pivot;

        if ($pivot && $pivot->custom_price !== null) {
            return (float) $pivot->custom_price;
        }

        return $this->price !== null ? (float) $this->price : null;
    }

    /**
     * Scope active service plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope visible service plans
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->where('is_visible_on_booking_page', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_PRIVATE_TRAINING => 'Private Training',
            self::CATEGORY_CONSULTATION => 'Consultation',
            self::CATEGORY_THERAPY => 'Therapy',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    /**
     * Get available location types
     */
    public static function getLocationTypes(): array
    {
        return [
            self::LOCATION_IN_STUDIO => 'In Studio',
            self::LOCATION_ONLINE => 'Online',
            self::LOCATION_CLIENT_LOCATION => 'Client Location',
        ];
    }
}
