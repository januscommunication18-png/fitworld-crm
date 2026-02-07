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
        'policies',
    ];

    protected function casts(): array
    {
        return [
            'studio_types' => 'array',
            'room_capacities' => 'array',
            'amenities' => 'array',
            'social_links' => 'array',
            'booking_settings' => 'array',
            'policies' => 'array',
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
     * Get a policy setting with default fallback
     */
    public function getPolicy(string $key, $default = null)
    {
        return $this->policies[$key] ?? $default;
    }

    /**
     * Get default policies
     */
    public static function defaultPolicies(): array
    {
        return [
            // Cancellation Policy
            'allow_cancellations' => true,
            'cancellation_window_hours' => 12,
            'cancellation_fee' => null,
            'late_cancellation_handling' => 'mark_late', // mark_late, charge_fee, deduct_credit

            // No-Show Policy
            'no_show_fee' => null,
            'no_show_handling' => 'no_action', // no_action, charge_fee, deduct_credit, strike
            'no_show_grace_period_minutes' => 15,

            // Waitlist Policy
            'enable_waitlist' => true,
            'waitlist_auto_promote' => true,
            'waitlist_promotion_window_minutes' => 120,
            'waitlist_notify_on_promotion' => true,
            'waitlist_hold_spot_minutes' => 15,

            // Booking Limits
            'max_bookings_per_class' => 1,
            'max_active_bookings' => null,
            'allow_booking_without_payment' => false,
            'booking_earliest_days' => 30,
            'booking_latest_minutes' => 30,

            // Studio Rules
            'house_rules' => null,
            'liability_waiver_url' => null,
            'arrival_instructions' => null,
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
