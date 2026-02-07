<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'studio_name',
        'subdomain',
        'studio_types',
        'city',
        'country',
        'currency',
        'phone',
        'studio_email',
        'timezone',
        'address',
        'rooms',
        'default_capacity',
        'room_capacities',
        'amenities',
        'about',
        'social_links',
        'contact_name',
        'support_email',
        'logo_path',
        'cover_image_path',
        'stripe_account_id',
        'is_live',
        'onboarding_step',
        'onboarding_completed_at',
        'booking_settings',
    ];

    protected function casts(): array
    {
        return [
            'studio_types' => 'array',
            'room_capacities' => 'array',
            'amenities' => 'array',
            'social_links' => 'array',
            'booking_settings' => 'array',
            'is_live' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    /**
     * Get a booking setting with default fallback
     */
    public function getBookingSetting(string $key, $default = null)
    {
        return $this->booking_settings[$key] ?? $default;
    }

    /**
     * Get default booking settings
     */
    public static function defaultBookingSettings(): array
    {
        return [
            // Branding
            'display_name' => null,
            'primary_color' => '#6366f1',
            'theme' => 'light', // light, dark, auto
            'font' => 'inter',

            // Public Content
            'about_text' => null,
            'show_instructors' => true,
            'show_amenities' => true,
            'location_display' => 'auto', // auto, single, multi

            // Booking UX
            'default_view' => 'calendar', // calendar, list
            'show_class_descriptions' => true,
            'show_instructor_photos' => true,
            'allow_waitlist' => true,
            'require_account' => false,

            // Filters
            'filter_class_type' => true,
            'filter_instructor' => true,
            'filter_location' => true,
        ];
    }

    /**
     * Check if studio has financial data (bookings, payments, memberships)
     */
    public function hasFinancialData(): bool
    {
        // TODO: Implement when booking/payment models exist
        return false;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(StudioClass::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function defaultLocation()
    {
        return $this->locations()->where('is_default', true)->first();
    }
}
