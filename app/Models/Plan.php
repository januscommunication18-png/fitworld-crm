<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'paddle_product_id',
        'paddle_monthly_price_id',
        'paddle_yearly_price_id',
        'features',
        'is_active',
        'is_featured',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get hosts on this plan
     */
    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class);
    }

    /**
     * Scope to active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get a feature limit value (0 = unlimited)
     */
    public function getFeatureLimit(string $key): int
    {
        return $this->features[$key] ?? 0;
    }

    /**
     * Check if plan has a boolean feature
     */
    public function hasFeature(string $key): bool
    {
        return (bool) ($this->features[$key] ?? false);
    }

    /**
     * Get default features structure
     */
    public static function getDefaultFeatures(): array
    {
        return [
            'locations' => 1,
            'rooms' => 3,
            'classes' => 10,
            'students' => 100,
            'crm' => true,
            'stripe_payments' => false,
            'memberships' => false,
            'intro_offers' => false,
            'automated_emails' => false,
            'attendance_insights' => true,
            'revenue_insights' => false,
            'manual_payments' => true,
            'online_payments' => false,
            'ics_sync' => false,
            'fitnearyou_attribution' => false,
            'priority_support' => false,
        ];
    }

    /**
     * Get all feature definitions with labels
     */
    public static function getFeatureDefinitions(): array
    {
        return [
            'locations' => ['label' => 'Locations', 'type' => 'number'],
            'rooms' => ['label' => 'Rooms', 'type' => 'number'],
            'classes' => ['label' => 'Classes', 'type' => 'number'],
            'students' => ['label' => 'Students', 'type' => 'number'],
            'crm' => ['label' => 'CRM', 'type' => 'boolean'],
            'stripe_payments' => ['label' => 'Stripe Online Payments', 'type' => 'boolean'],
            'memberships' => ['label' => 'Memberships & Class Packs', 'type' => 'boolean'],
            'intro_offers' => ['label' => 'Intro Offers & Promo Codes', 'type' => 'boolean'],
            'automated_emails' => ['label' => 'Automated Emails', 'type' => 'boolean'],
            'attendance_insights' => ['label' => 'Attendance Insights', 'type' => 'boolean'],
            'revenue_insights' => ['label' => 'Revenue Insights', 'type' => 'boolean'],
            'manual_payments' => ['label' => 'Manual Payments', 'type' => 'boolean'],
            'online_payments' => ['label' => 'Online Payments', 'type' => 'boolean'],
            'ics_sync' => ['label' => 'ICS Calendar Sync', 'type' => 'boolean'],
            'fitnearyou_attribution' => ['label' => 'FitNearYou Booking Attribution', 'type' => 'boolean'],
            'priority_support' => ['label' => 'Priority Support', 'type' => 'boolean'],
        ];
    }
}
