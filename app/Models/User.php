<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    // Role constants
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_INSTRUCTOR = 'instructor';

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INVITED = 'invited';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_DEACTIVATED = 'deactivated';

    protected $fillable = [
        'host_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_instructor',
        'status',
        'last_login_at',
        'permissions',
        'instructor_id',
        'profile_photo',
        'phone',
        'phone_type',
        'phone_verified_at',
        'bio',
        'social_links',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_instructor' => 'boolean',
            'last_login_at' => 'datetime',
            'permissions' => 'array',
            'social_links' => 'array',
        ];
    }

    /**
     * Legacy single-host relationship (for backwards compatibility)
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Multi-studio relationship through pivot table
     */
    public function hosts(): BelongsToMany
    {
        return $this->belongsToMany(Host::class, 'host_user')
            ->withPivot(['role', 'permissions', 'instructor_id', 'is_primary', 'joined_at', 'personal_override_code'])
            ->withTimestamps();
    }

    /**
     * Get the current host from session or return primary host
     */
    public function currentHost(): ?Host
    {
        $currentHostId = Session::get('current_host_id');

        if ($currentHostId) {
            $host = $this->hosts()->where('hosts.id', $currentHostId)->first();
            if ($host) {
                return $host;
            }
        }

        return $this->getPrimaryHost();
    }

    /**
     * Get the user's primary host
     */
    public function getPrimaryHost(): ?Host
    {
        // Try pivot table first
        $host = $this->hosts()->wherePivot('is_primary', true)->first()
            ?? $this->hosts()->first();

        // Fallback to legacy host_id relationship
        if (!$host && $this->host_id) {
            $host = $this->host;
        }

        return $host;
    }

    /**
     * Check if user belongs to multiple studios
     */
    public function hasMultipleHosts(): bool
    {
        return $this->hosts()->count() > 1;
    }

    /**
     * Get user's role for a specific host
     */
    public function getRoleForHost(Host $host): ?string
    {
        $membership = $this->hosts()->where('hosts.id', $host->id)->first();
        return $membership?->pivot?->role;
    }

    /**
     * Get user's permissions for a specific host
     */
    public function getPermissionsForHost(Host $host): ?array
    {
        $membership = $this->hosts()->where('hosts.id', $host->id)->first();
        $permissions = $membership?->pivot?->permissions;
        return is_string($permissions) ? json_decode($permissions, true) : $permissions;
    }

    /**
     * Get user's personal override code for a specific host
     */
    public function getPersonalOverrideCode(Host $host): ?string
    {
        $membership = $this->hosts()->where('hosts.id', $host->id)->first();
        return $membership?->pivot?->personal_override_code;
    }

    /**
     * Check if user can approve price overrides (has pricing.override permission or is owner/admin)
     */
    public function canApprovePriceOverride(?Host $host = null): bool
    {
        $host = $host ?? $this->currentHost() ?? $this->host;
        if (!$host) return false;

        return $this->isOwner($host) || $this->isAdmin($host) || $this->hasPermission('pricing.override', $host);
    }

    /**
     * Set the current host in session
     */
    public function setCurrentHost(Host $host): void
    {
        if ($this->hosts()->where('hosts.id', $host->id)->exists()) {
            Session::put('current_host_id', $host->id);
        }
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Notes about this user (as subject)
     */
    public function notes()
    {
        return $this->hasMany(UserNote::class, 'subject_user_id')->orderBy('created_at', 'desc');
    }

    /**
     * Certifications for this user
     */
    public function certifications()
    {
        return $this->hasMany(StudioCertification::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Alias for full_name for compatibility
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get profile photo URL (works with both local and cloud storage)
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) {
            return null;
        }
        return \Storage::disk(config('filesystems.uploads'))->url($this->profile_photo);
    }

    /**
     * Get the current role (context-aware for multi-studio)
     */
    public function getCurrentRole(): string
    {
        $currentHost = $this->currentHost();
        if ($currentHost) {
            return $this->getRoleForHost($currentHost) ?? $this->role;
        }
        return $this->role;
    }

    /**
     * Check if user is the owner (context-aware)
     */
    public function isOwner(?Host $host = null): bool
    {
        if ($host) {
            $role = $this->getRoleForHost($host);
            // Fallback: if user owns this host directly but not in pivot
            if ($role === null && $this->host_id === $host->id) {
                $role = $this->role;
            }
            return $role === self::ROLE_OWNER;
        }
        return $this->getCurrentRole() === self::ROLE_OWNER;
    }

    /**
     * Check if user is an admin (context-aware)
     */
    public function isAdmin(?Host $host = null): bool
    {
        if ($host) {
            $role = $this->getRoleForHost($host);
            // Fallback: if user belongs to this host directly but not in pivot
            if ($role === null && $this->host_id === $host->id) {
                $role = $this->role;
            }
            return $role === self::ROLE_ADMIN;
        }
        return $this->getCurrentRole() === self::ROLE_ADMIN;
    }

    /**
     * Check if user is manager (context-aware)
     */
    public function isManager(?Host $host = null): bool
    {
        if ($host) {
            return $this->getRoleForHost($host) === self::ROLE_MANAGER;
        }
        return $this->getCurrentRole() === self::ROLE_MANAGER;
    }

    /**
     * Check if user is staff (context-aware)
     */
    public function isStaff(?Host $host = null): bool
    {
        if ($host) {
            return $this->getRoleForHost($host) === self::ROLE_STAFF;
        }
        return $this->getCurrentRole() === self::ROLE_STAFF;
    }

    /**
     * Check if user has instructor role (context-aware)
     */
    public function hasInstructorRole(?Host $host = null): bool
    {
        if ($host) {
            return $this->getRoleForHost($host) === self::ROLE_INSTRUCTOR;
        }
        return $this->getCurrentRole() === self::ROLE_INSTRUCTOR;
    }

    /**
     * Check if user can manage team (context-aware)
     */
    public function canManageTeam(?Host $host = null): bool
    {
        return $this->isOwner($host) || ($this->isAdmin($host) && $this->hasPermission('team.manage', $host));
    }

    /**
     * Check if user has a specific permission (context-aware)
     */
    public function hasPermission(string $permission, ?Host $host = null): bool
    {
        $currentHost = $host ?? $this->currentHost();

        // Get role from pivot table, fall back to user's role if not in pivot
        $role = $currentHost ? $this->getRoleForHost($currentHost) : $this->role;

        // If no role in pivot, check if user owns this host (backwards compatibility)
        if ($role === null && $currentHost && $this->host_id === $currentHost->id) {
            $role = $this->role;
        }

        // If still no role found, user has no permissions for this context
        if ($role === null) {
            return false;
        }

        // Owner has all permissions
        if ($role === self::ROLE_OWNER) {
            return true;
        }

        // Check custom permissions override.
        // The form saves a flat array of granted permission keys (e.g. ["schedule.view"]).
        // Older records may use an associative map (e.g. {"schedule.view": true}).
        $permissions = $currentHost ? $this->getPermissionsForHost($currentHost) : $this->permissions;
        if (is_array($permissions) && !empty($permissions)) {
            // Associative map (key => bool)
            if (array_key_exists($permission, $permissions)) {
                return (bool) $permissions[$permission];
            }
            // Flat list of granted keys
            if (in_array($permission, $permissions, true)) {
                return true;
            }
            // Custom permission set exists but this permission isn't granted — explicit deny.
            return false;
        }

        // No custom override — fall back to role-based default permissions
        return $this->hasDefaultPermissionForRole($permission, $role);
    }

    /**
     * Check default permission for a specific role
     */
    protected function hasDefaultPermissionForRole(string $permission, string $role): bool
    {
        $rolePermissions = self::getDefaultPermissionsForRole($role);
        return in_array($permission, $rolePermissions);
    }

    /**
     * Get default permissions for a role
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        $permissions = [
            self::ROLE_OWNER => ['*'], // All permissions
            self::ROLE_ADMIN => [
                'nav.dashboard', 'nav.schedule', 'nav.bookings', 'nav.clients', 'nav.helpdesk',
                'nav.instructors', 'nav.classes_services', 'nav.marketing', 'nav.insights', 'nav.payments', 'nav.settings',
                'schedule.view', 'schedule.create', 'schedule.edit', 'schedule.publish', 'schedule.cancel',
                'bookings.view', 'bookings.create', 'bookings.cancel', 'bookings.waitlist', 'bookings.attendance', 'bookings.comp',
                'students.view', 'students.view_all', 'students.create', 'students.edit', 'students.notes', 'students.export',
                'helpdesk.view', 'helpdesk.view_assigned', 'helpdesk.create', 'helpdesk.edit', 'helpdesk.delete', 'helpdesk.assign', 'helpdesk.reply', 'helpdesk.view_client',
                'offers.intro', 'offers.packs', 'offers.memberships', 'offers.promos',
                'insights.attendance', 'insights.revenue',
                'studio.profile', 'studio.locations', 'studio.rooms', 'studio.class_types', 'studio.booking_page', 'studio.policies', 'studio.client_settings',
                'team.view', 'team.manage', 'team.instructors', 'team.instructor_admin', 'team.permissions',
                'communication.manage', 'integrations.manage',
                'pricing.override',
            ],
            self::ROLE_MANAGER => [
                'nav.dashboard', 'nav.schedule', 'nav.bookings', 'nav.clients', 'nav.helpdesk',
                'nav.instructors', 'nav.classes_services', 'nav.insights', 'nav.settings',
                // Manager: team viewing & communication tools (Settings → My Profile is always available)
                'schedule.view', 'schedule.create', 'schedule.edit', 'schedule.publish', 'schedule.cancel',
                'bookings.view', 'bookings.create', 'bookings.cancel', 'bookings.waitlist', 'bookings.attendance', 'bookings.comp',
                'students.view', 'students.create', 'students.edit', 'students.notes',
                // Manager: full helpdesk except destructive delete.
                'helpdesk.view', 'helpdesk.view_assigned', 'helpdesk.create', 'helpdesk.edit', 'helpdesk.assign', 'helpdesk.reply', 'helpdesk.view_client',
                'insights.attendance', 'insights.revenue',
                'team.view', 'team.instructors',
                'communication.manage',
                'pricing.override',
            ],
            self::ROLE_STAFF => [
                'nav.dashboard', 'nav.schedule', 'nav.bookings', 'nav.clients', 'nav.settings',
                // Staff: sees Settings → My Profile only (always available)
                'schedule.view',
                'bookings.view', 'bookings.create', 'bookings.cancel', 'bookings.attendance',
                'students.view', 'students.edit', 'students.notes',
                // Staff: assigned-only by default — can see and reply to tickets assigned to them.
                'helpdesk.view_assigned', 'helpdesk.reply', 'helpdesk.edit',
            ],
            self::ROLE_INSTRUCTOR => [
                'nav.dashboard', 'nav.schedule', 'nav.bookings', 'nav.settings',
                // Instructor: sees Settings → My Profile only (always available)
                'schedule.view_own',
                'bookings.view_own', 'bookings.attendance_own',
            ],
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Get all available permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            'nav' => [
                'nav.dashboard' => 'Show Dashboard in sidebar',
                'nav.schedule' => 'Show Schedule in sidebar',
                'nav.bookings' => 'Show Bookings in sidebar',
                'nav.clients' => 'Show Clients in sidebar',
                'nav.helpdesk' => 'Show Help Desk in sidebar',
                'nav.instructors' => 'Show Instructors in sidebar',
                'nav.classes_services' => 'Show Classes & Services in sidebar',
                'nav.marketing' => 'Show Marketing in sidebar',
                'nav.insights' => 'Show Insights in sidebar',
                'nav.payments' => 'Show Payments in sidebar',
                'nav.settings' => 'Show Settings in sidebar',
            ],
            'schedule' => [
                'schedule.view' => 'View all schedule',
                'schedule.view_own' => 'View own schedule only',
                'schedule.create' => 'Add schedules (classes, services, memberships, rentals, events)',
                'schedule.edit' => 'Edit existing schedules',
                'schedule.publish' => 'Publish and unpublish schedules',
                'schedule.cancel' => 'Cancel classes',
            ],
            'bookings' => [
                'bookings.view' => 'View all bookings',
                'bookings.view_own' => 'View own class bookings',
                'bookings.create' => 'Create bookings',
                'bookings.cancel' => 'Cancel bookings',
                'bookings.waitlist' => 'Manage waitlist',
                'bookings.attendance' => 'Mark attendance',
                'bookings.attendance_own' => 'Mark attendance for own classes',
                'bookings.comp' => 'Comp bookings (waive payment)',
            ],
            'students' => [
                'students.view' => 'View clients (search by full name required)',
                'students.view_all' => 'View full client directory',
                'students.create' => 'Add clients',
                'students.edit' => 'Edit client profiles',
                'students.notes' => 'Add client notes and tags',
                'students.export' => 'Export clients',
            ],
            'helpdesk' => [
                'helpdesk.view' => 'View all helpdesk tickets',
                'helpdesk.view_assigned' => 'View only tickets assigned to me',
                'helpdesk.create' => 'Create helpdesk tickets',
                'helpdesk.edit' => 'Edit ticket status, tags, and details',
                'helpdesk.delete' => 'Delete helpdesk tickets',
                'helpdesk.assign' => 'Assign tickets to other team members',
                'helpdesk.reply' => 'Reply to helpdesk tickets',
                'helpdesk.view_client' => 'Open the linked client profile from a ticket',
            ],
            'offers' => [
                'offers.intro' => 'Manage intro offers',
                'offers.packs' => 'Manage class packs',
                'offers.memberships' => 'Manage memberships',
                'offers.promos' => 'Manage promo codes',
            ],
            'insights' => [
                'insights.attendance' => 'View attendance insights',
                'insights.revenue' => 'View revenue insights',
                'insights.export' => 'Export reports',
            ],
            'payments' => [
                'payments.view' => 'View transactions',
                'payments.refunds' => 'Issue refunds',
                'payments.payouts' => 'View payouts',
                'payments.stripe' => 'Manage Stripe connection',
            ],
            'studio' => [
                'studio.profile' => 'Edit studio profile',
                'studio.locations' => 'Manage locations',
                'studio.rooms' => 'Manage rooms',
                'studio.class_types' => 'Manage class types',
                'studio.booking_page' => 'Manage booking page',
                'studio.policies' => 'Manage policies',
                'studio.client_settings' => 'Manage Client & Portal settings',
            ],
            'team' => [
                'team.view' => 'View team members',
                'team.manage' => 'Manage team (invite and deactivate)',
                'team.instructors' => 'Manage instructor profiles',
                'team.instructor_admin' => 'Manage instructor employment, workload, availability & visibility',
                'team.permissions' => 'Manage user permissions',
            ],
            'billing' => [
                'billing.plan' => 'Manage subscription plan',
                'billing.invoices' => 'View invoices',
                'billing.payment' => 'Update payment method',
            ],
            'communication' => [
                'communication.manage' => 'Manage email templates, notifications, and automation',
            ],
            'integrations' => [
                'integrations.manage' => 'Manage integrations (Stripe, FitNearYou, Calendar, PayPal, Cash App, Venmo)',
            ],
            'pricing' => [
                'pricing.override' => 'Override prices at checkout',
            ],
        ];
    }

    /**
     * Get available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_STAFF => 'Staff',
            self::ROLE_INSTRUCTOR => 'Instructor',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INVITED => 'Invited',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_DEACTIVATED => 'Deactivated',
        ];
    }
}
