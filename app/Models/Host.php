<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Host extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING_VERIFY = 'pending_verify';
    const STATUS_SUSPENDED = 'suspended';

    const SUBSCRIPTION_TRIALING = 'trialing';
    const SUBSCRIPTION_ACTIVE = 'active';
    const SUBSCRIPTION_PAST_DUE = 'past_due';
    const SUBSCRIPTION_CANCELED = 'canceled';

    // Booking page status constants
    const BOOKING_PAGE_DRAFT = 'draft';
    const BOOKING_PAGE_PUBLISHED = 'published';

    protected $fillable = [
        'studio_name',
        'short_description',
        'subdomain',
        'studio_types',
        'city',
        'country',
        'operating_countries',
        'default_language_app',
        'default_language_booking',
        'studio_languages',
        'currencies',
        'default_currency',
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
        'payment_settings',
        'tax_settings',
        'client_settings',
        'member_portal_settings',
        'policies',
        'status',
        'verified_at',
        'plan_id',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'booking_page_status',
        'show_address',
        'show_social_links',
    ];

    protected function casts(): array
    {
        return [
            'studio_types' => 'array',
            'room_capacities' => 'array',
            'amenities' => 'array',
            'operating_countries' => 'array',
            'studio_languages' => 'array',
            'currencies' => 'array',
            'social_links' => 'array',
            'booking_settings' => 'array',
            'payment_settings' => 'array',
            'tax_settings' => 'array',
            'client_settings' => 'array',
            'member_portal_settings' => 'array',
            'policies' => 'array',
            'is_live' => 'boolean',
            'show_address' => 'boolean',
            'show_social_links' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * Get the storage folder path for this host
     * Format: studioname_hostid (sanitized, lowercase, no special chars)
     */
    public function getStoragePath(string $subfolder = ''): string
    {
        // Sanitize studio name: lowercase, replace spaces with hyphens, remove special chars
        $sanitizedName = strtolower($this->studio_name ?? 'studio');
        $sanitizedName = preg_replace('/[^a-z0-9]+/', '-', $sanitizedName);
        $sanitizedName = trim($sanitizedName, '-');

        // Build folder path: studioname_hostid
        $basePath = $sanitizedName . '_' . $this->id;

        // Append subfolder if provided
        if ($subfolder) {
            return $basePath . '/' . trim($subfolder, '/');
        }

        return $basePath;
    }

    /**
     * Get the logo URL (works with both local and cloud storage)
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        return \Storage::disk(config('filesystems.uploads'))->url($this->logo_path);
    }

    /**
     * Get the cover image URL (works with both local and cloud storage)
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image_path) {
            return null;
        }
        return \Storage::disk(config('filesystems.uploads'))->url($this->cover_image_path);
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
     * Get a member portal setting with default fallback
     */
    public function getMemberPortalSetting(string $key, $default = null)
    {
        return $this->member_portal_settings[$key] ?? $default;
    }

    /**
     * Get default member portal settings
     */
    public static function defaultMemberPortalSettings(): array
    {
        return [
            'enabled' => false,
            'allow_self_registration' => true,
            'login_method' => 'otp', // 'otp' or 'password'
            'session_timeout_days' => 30,
            'require_email_verification' => false,
            'activation_code_expiry_minutes' => 10,
            'max_otp_resend_per_hour' => 3,
            'max_login_attempts' => 10,
            'lockout_duration_minutes' => 30,
            'allowed_features' => ['schedule', 'bookings', 'payments', 'invoices', 'profile'],
        ];
    }

    /**
     * Check if member portal is enabled
     */
    public function isMemberPortalEnabled(): bool
    {
        return (bool) $this->getMemberPortalSetting('enabled', false);
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

    /**
     * Legacy single-host relationship (for backwards compatibility)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Multi-studio relationship through pivot table
     * Use this for team management to include users from multiple studios
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'host_user')
            ->withPivot(['role', 'permissions', 'instructor_id', 'is_primary', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get all assignable team members (from pivot table OR direct host_id)
     * This ensures we get users regardless of how they're linked to the host
     */
    public function getAllTeamMembers()
    {
        // Get users from pivot table
        $pivotUserIds = $this->teamMembers()->pluck('users.id')->toArray();

        // Get users with direct host_id
        $directUserIds = $this->users()->pluck('id')->toArray();

        // Combine and get unique
        $allUserIds = array_unique(array_merge($pivotUserIds, $directUserIds));

        return User::whereIn('id', $allUserIds)->orderBy('first_name')->get();
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(StudioClass::class);
    }

    public function classPlans(): HasMany
    {
        return $this->hasMany(ClassPlan::class);
    }

    public function servicePlans(): HasMany
    {
        return $this->hasMany(ServicePlan::class);
    }

    public function serviceSlots(): HasMany
    {
        return $this->hasMany(ServiceSlot::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function rentalBookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function classRequests(): HasMany
    {
        return $this->hasMany(ClassRequest::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    public function helpdeskTickets(): HasMany
    {
        return $this->hasMany(HelpdeskTicket::class);
    }

    public function helpdeskTags(): HasMany
    {
        return $this->hasMany(HelpdeskTag::class);
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }

    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function customerMemberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class);
    }

    public function classPacks(): HasMany
    {
        return $this->hasMany(ClassPack::class);
    }

    public function classPackPurchases(): HasMany
    {
        return $this->hasMany(ClassPackPurchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(StudioGalleryImage::class)->ordered();
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(StudioCertification::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public function defaultLocation()
    {
        return $this->locations()->where('is_default', true)->first();
    }

    /**
     * Get the plan for this host
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get status history
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(HostStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the owner user (relationship for eager loading)
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', User::ROLE_OWNER);
    }

    /**
     * Get the owner user (direct query)
     */
    public function getOwner(): ?User
    {
        return $this->users()->where('role', User::ROLE_OWNER)->first();
    }

    /**
     * Check if host is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if host is pending verification
     */
    public function isPendingVerify(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFY;
    }

    /**
     * Check if host is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if booking page is published
     */
    public function isBookingPagePublished(): bool
    {
        return $this->booking_page_status === self::BOOKING_PAGE_PUBLISHED;
    }

    /**
     * Check if email is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark host as verified
     */
    public function markVerified(?int $adminUserId = null): void
    {
        $oldStatus = $this->status;
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'verified_at' => now(),
        ]);

        HostStatusHistory::log(
            $this->id,
            self::STATUS_ACTIVE,
            $oldStatus,
            $adminUserId,
            'Email verified'
        );
    }

    /**
     * Change host status
     */
    public function changeStatus(string $status, ?int $adminUserId = null, ?string $reason = null): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $status]);

        HostStatusHistory::log(
            $this->id,
            $status,
            $oldStatus,
            $adminUserId,
            $reason
        );
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING_VERIFY => 'Pending Verification',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Get available subscription statuses
     */
    public static function getSubscriptionStatuses(): array
    {
        return [
            self::SUBSCRIPTION_TRIALING => 'Trialing',
            self::SUBSCRIPTION_ACTIVE => 'Active',
            self::SUBSCRIPTION_PAST_DUE => 'Past Due',
            self::SUBSCRIPTION_CANCELED => 'Canceled',
        ];
    }

    /**
     * Get available booking page statuses
     */
    public static function getBookingPageStatuses(): array
    {
        return [
            self::BOOKING_PAGE_DRAFT => 'Draft',
            self::BOOKING_PAGE_PUBLISHED => 'Published',
        ];
    }
}
