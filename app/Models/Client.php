<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Client extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable, Notifiable;

    // Status constants
    const STATUS_LEAD = 'lead';
    const STATUS_CLIENT = 'client';
    const STATUS_MEMBER = 'member';
    const STATUS_AT_RISK = 'at_risk';

    // Membership status constants
    const MEMBERSHIP_NONE = 'none';
    const MEMBERSHIP_ACTIVE = 'active';
    const MEMBERSHIP_PAUSED = 'paused';
    const MEMBERSHIP_CANCELLED = 'cancelled';

    // Lead source constants
    const SOURCE_MANUAL = 'manual';
    const SOURCE_MARKETING = 'marketing';
    const SOURCE_WEBSITE = 'website';
    const SOURCE_LEAD_MAGNET = 'lead_magnet';
    const SOURCE_FITNEARYOU = 'fitnearyou';
    const SOURCE_REFERRAL = 'referral';

    protected $fillable = [
        'host_id',
        // Basic Information
        'first_name',
        'last_name',
        'email',
        'phone',
        'stripe_customer_id',
        'secondary_phone',
        'date_of_birth',
        'gender',
        'profile_photo',
        // Contact Details
        'address',
        'address_line_1',
        'address_line_2',
        'city',
        'state_province',
        'postal_code',
        'country',
        // Status
        'status',
        'membership_status',
        'membership_plan_id',
        'membership_start_date',
        'membership_end_date',
        'membership_renewal_date',
        // Source & Marketing
        'lead_source',
        'source_url',
        'referral_source',
        'referral_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        // Engagement & Activity
        'first_visit_date',
        'last_visit_at',
        'next_booking_at',
        'total_classes_attended',
        'total_services_booked',
        'lifetime_value',
        'total_spent',
        // Communication Preferences
        'email_opt_in',
        'sms_opt_in',
        'marketing_opt_in',
        'preferred_contact_method',
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_email',
        // Health & Fitness
        'medical_conditions',
        'injuries',
        'limitations',
        'fitness_goals',
        'experience_level',
        'pregnancy_status',
        // Internal
        'assigned_staff_id',
        'assigned_instructor_id',
        'notes',
        // System
        'converted_at',
        'membership_id',
        'membership_expires_at',
        'created_by_user_id',
        'updated_by_user_id',
        'archived_at',
        // Member Portal Fields
        'password',
        'activation_code',
        'activation_code_expires_at',
        'portal_last_login_at',
        'portal_login_count',
        'portal_email_verified_at',
        'remember_token',
        'password_reset_token',
        'password_reset_expires_at',
        'otp_attempts',
        'otp_locked_until',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'date_of_birth' => 'date',
            'membership_start_date' => 'date',
            'membership_end_date' => 'date',
            'membership_renewal_date' => 'date',
            'first_visit_date' => 'date',
            'last_visit_at' => 'datetime',
            'next_booking_at' => 'datetime',
            'converted_at' => 'datetime',
            'membership_expires_at' => 'datetime',
            'archived_at' => 'datetime',
            'email_opt_in' => 'boolean',
            'sms_opt_in' => 'boolean',
            'marketing_opt_in' => 'boolean',
            'pregnancy_status' => 'boolean',
            'total_classes_attended' => 'integer',
            'total_services_booked' => 'integer',
            'lifetime_value' => 'decimal:2',
            'total_spent' => 'decimal:2',
            // Member Portal
            'password' => 'hashed',
            'activation_code_expires_at' => 'datetime',
            'portal_last_login_at' => 'datetime',
            'portal_login_count' => 'integer',
            'portal_email_verified_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'otp_attempts' => 'integer',
            'otp_locked_until' => 'datetime',
        ];
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'client_tag')
            ->withTimestamps();
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class);
    }

    public function clientNotes(): HasMany
    {
        return $this->hasMany(ClientNote::class)->orderByDesc('created_at');
    }

    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function customerMemberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class);
    }

    public function activeCustomerMembership()
    {
        return $this->customerMemberships()
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->notExpired()
            ->latest()
            ->first();
    }

    public function classPackPurchases(): HasMany
    {
        return $this->hasMany(ClassPackPurchase::class);
    }

    public function usableClassPackPurchases()
    {
        return $this->classPackPurchases()->usable();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'client_segment')
            ->withPivot(['added_by', 'matched_at'])
            ->withTimestamps();
    }

    public function score(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientScore::class);
    }

    public function scoreEvents(): HasMany
    {
        return $this->hasMany(ScoreEvent::class);
    }

    public function offerRedemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class);
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getIsLeadAttribute(): bool
    {
        return $this->status === self::STATUS_LEAD;
    }

    public function getIsMemberAttribute(): bool
    {
        return $this->status === self::STATUS_MEMBER || $this->membership_status === self::MEMBERSHIP_ACTIVE;
    }

    public function getIsAtRiskAttribute(): bool
    {
        return $this->status === self::STATUS_AT_RISK;
    }

    public function getIsArchivedAttribute(): bool
    {
        return !is_null($this->archived_at);
    }

    public function getHasAvatarAttribute(): bool
    {
        return !empty($this->profile_photo);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->has_avatar) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (str_starts_with($this->profile_photo, 'http')) {
            return $this->profile_photo;
        }

        // Otherwise, assume it's a storage path
        return asset('storage/' . $this->profile_photo);
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeLeads(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LEAD);
    }

    public function scopeMembers(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_MEMBER)
              ->orWhere('membership_status', self::MEMBERSHIP_ACTIVE);
        });
    }

    public function scopeAtRisk(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AT_RISK);
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeWithSource(Builder $query, string $source): Builder
    {
        return $query->where('lead_source', $source);
    }

    public function scopeWithTag(Builder $query, $tagId): Builder
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Methods

    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    public function restore(): void
    {
        $this->update(['archived_at' => null]);
    }

    public function convertToClient(): void
    {
        $this->update([
            'status' => self::STATUS_CLIENT,
            'converted_at' => now(),
        ]);
    }

    public function convertToMember(): void
    {
        $this->update([
            'status' => self::STATUS_MEMBER,
            'membership_status' => self::MEMBERSHIP_ACTIVE,
            'converted_at' => $this->converted_at ?? now(),
        ]);
    }

    public function markAsAtRisk(): void
    {
        $this->update(['status' => self::STATUS_AT_RISK]);
    }

    public function clearAtRisk(): void
    {
        $this->update([
            'status' => $this->membership_status === self::MEMBERSHIP_ACTIVE
                ? self::STATUS_MEMBER
                : self::STATUS_CLIENT,
        ]);
    }

    public function recordVisit(): void
    {
        $this->update(['last_visit_at' => now()]);
    }

    public function getCustomFieldValue(string $fieldKey): ?string
    {
        $value = $this->fieldValues()
            ->whereHas('fieldDefinition', function ($q) use ($fieldKey) {
                $q->where('field_key', $fieldKey);
            })
            ->first();

        return $value?->value;
    }

    public function setCustomFieldValue(string $fieldKey, ?string $value): void
    {
        $definition = ClientFieldDefinition::where('host_id', $this->host_id)
            ->where('field_key', $fieldKey)
            ->first();

        if (!$definition) {
            return;
        }

        $this->fieldValues()->updateOrCreate(
            ['field_definition_id' => $definition->id],
            ['value' => $value]
        );
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_LEAD => 'Lead',
            self::STATUS_CLIENT => 'Client',
            self::STATUS_MEMBER => 'Member',
            self::STATUS_AT_RISK => 'At Risk',
        ];
    }

    public static function getMembershipStatuses(): array
    {
        return [
            self::MEMBERSHIP_NONE => 'None',
            self::MEMBERSHIP_ACTIVE => 'Active',
            self::MEMBERSHIP_PAUSED => 'Paused',
            self::MEMBERSHIP_CANCELLED => 'Cancelled',
        ];
    }

    public static function getLeadSources(): array
    {
        return [
            self::SOURCE_MANUAL => 'Manual Entry',
            self::SOURCE_MARKETING => 'Marketing Campaign',
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_LEAD_MAGNET => 'Lead Magnet',
            self::SOURCE_FITNEARYOU => 'FitNearYou',
            self::SOURCE_REFERRAL => 'Referral',
        ];
    }

    public static function getGenders(): array
    {
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
        ];
    }

    public static function getExperienceLevels(): array
    {
        return [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ];
    }

    public static function getContactMethods(): array
    {
        return [
            'email' => 'Email',
            'phone' => 'Phone',
            'sms' => 'SMS',
        ];
    }

    // ===== MEMBER PORTAL METHODS =====

    /**
     * Get the transactions for this client
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the invoices for this client
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if client has portal access (password or verified email for OTP)
     */
    public function hasPortalAccess(): bool
    {
        return !empty($this->password) || !empty($this->portal_email_verified_at);
    }

    /**
     * Check if client can login (not locked out)
     */
    public function canAttemptOtp(): bool
    {
        if ($this->otp_locked_until && $this->otp_locked_until->isFuture()) {
            return false;
        }
        return true;
    }

    /**
     * Get remaining lockout time in minutes
     */
    public function getOtpLockoutMinutesRemaining(): ?int
    {
        if (!$this->otp_locked_until || $this->otp_locked_until->isPast()) {
            return null;
        }
        return now()->diffInMinutes($this->otp_locked_until);
    }

    /**
     * Generate and set OTP activation code
     */
    public function generateActivationCode(int $expiryMinutes = 10): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'activation_code' => $code,
            'activation_code_expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return $code;
    }

    /**
     * Verify activation code
     */
    public function verifyActivationCode(string $code): bool
    {
        if (!$this->activation_code || !$this->activation_code_expires_at) {
            return false;
        }

        if ($this->activation_code_expires_at->isPast()) {
            return false;
        }

        if ($this->activation_code !== $code) {
            $this->incrementOtpAttempts();
            return false;
        }

        // Clear the code and reset attempts on success
        $this->update([
            'activation_code' => null,
            'activation_code_expires_at' => null,
            'otp_attempts' => 0,
            'otp_locked_until' => null,
        ]);

        return true;
    }

    /**
     * Increment OTP attempts and lock if needed
     */
    public function incrementOtpAttempts(int $maxAttempts = 5, int $lockoutMinutes = 30): void
    {
        $attempts = ($this->otp_attempts ?? 0) + 1;

        $updates = ['otp_attempts' => $attempts];

        if ($attempts >= $maxAttempts) {
            $updates['otp_locked_until'] = now()->addMinutes($lockoutMinutes);
        }

        $this->update($updates);
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken(int $expiryMinutes = 60): string
    {
        $token = Str::random(64);

        $this->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return $token;
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken(string $token): bool
    {
        if (!$this->password_reset_token || !$this->password_reset_expires_at) {
            return false;
        }

        if ($this->password_reset_expires_at->isPast()) {
            return false;
        }

        return hash_equals($this->password_reset_token, $token);
    }

    /**
     * Reset password using token
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        if (!$this->verifyPasswordResetToken($token)) {
            return false;
        }

        $this->update([
            'password' => Hash::make($newPassword),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Set password for portal access
     */
    public function setPortalPassword(string $password): void
    {
        $this->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Record portal login
     */
    public function recordPortalLogin(): void
    {
        $this->update([
            'portal_last_login_at' => now(),
            'portal_login_count' => ($this->portal_login_count ?? 0) + 1,
        ]);
    }

    /**
     * Mark email as verified for portal
     */
    public function markPortalEmailAsVerified(): void
    {
        $this->update([
            'portal_email_verified_at' => now(),
        ]);
    }

    /**
     * Check if portal email is verified
     */
    public function hasVerifiedPortalEmail(): bool
    {
        return !is_null($this->portal_email_verified_at);
    }

    /**
     * Get upcoming bookings
     */
    public function upcomingBookings()
    {
        return $this->bookings()
            ->whereHas('bookable', function ($query) {
                $query->where('start_time', '>=', now());
            })
            ->whereIn('status', [Booking::STATUS_CONFIRMED])
            ->with(['bookable'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get past bookings
     */
    public function pastBookings()
    {
        return $this->bookings()
            ->whereHas('bookable', function ($query) {
                $query->where('start_time', '<', now());
            })
            ->with(['bookable'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Scope: Clients with portal access
     */
    public function scopeWithPortalAccess(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotNull('password')
              ->orWhereNotNull('portal_email_verified_at');
        });
    }
}
