<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    // Role constants
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_instructor' => 'boolean',
            'last_login_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is the owner
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    /**
     * Check if user has instructor role
     */
    public function hasInstructorRole(): bool
    {
        return $this->role === self::ROLE_INSTRUCTOR;
    }

    /**
     * Check if user can manage team
     */
    public function canManageTeam(): bool
    {
        return $this->isOwner() || ($this->isAdmin() && $this->hasPermission('team.manage'));
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Owner has all permissions
        if ($this->isOwner()) {
            return true;
        }

        // Check custom permissions override
        if (is_array($this->permissions) && isset($this->permissions[$permission])) {
            return (bool) $this->permissions[$permission];
        }

        // Fall back to role-based default permissions
        return $this->hasDefaultPermission($permission);
    }

    /**
     * Get default permission based on role
     */
    protected function hasDefaultPermission(string $permission): bool
    {
        $rolePermissions = self::getDefaultPermissionsForRole($this->role);
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
                'schedule.view', 'schedule.create', 'schedule.edit', 'schedule.cancel', 'schedule.rooms',
                'bookings.view', 'bookings.create', 'bookings.cancel', 'bookings.waitlist', 'bookings.attendance',
                'students.view', 'students.create', 'students.edit', 'students.notes', 'students.export',
                'offers.intro', 'offers.packs', 'offers.memberships', 'offers.promos',
                'insights.attendance', 'insights.revenue',
                'studio.profile', 'studio.locations', 'studio.booking_page', 'studio.policies',
                'team.view', 'team.manage', 'team.instructors',
            ],
            self::ROLE_STAFF => [
                'schedule.view',
                'bookings.view', 'bookings.create', 'bookings.cancel', 'bookings.attendance',
                'students.view', 'students.edit', 'students.notes',
            ],
            self::ROLE_INSTRUCTOR => [
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
            'schedule' => [
                'schedule.view' => 'View schedule',
                'schedule.view_own' => 'View own schedule only',
                'schedule.create' => 'Create/edit classes',
                'schedule.cancel' => 'Cancel classes',
                'schedule.rooms' => 'Manage rooms/class types',
            ],
            'bookings' => [
                'bookings.view' => 'View all bookings',
                'bookings.view_own' => 'View own class bookings',
                'bookings.create' => 'Create bookings',
                'bookings.cancel' => 'Cancel bookings',
                'bookings.waitlist' => 'Manage waitlist',
                'bookings.attendance' => 'Mark attendance',
                'bookings.attendance_own' => 'Mark attendance for own classes',
            ],
            'students' => [
                'students.view' => 'View students',
                'students.create' => 'Add students',
                'students.edit' => 'Edit student profiles',
                'students.notes' => 'Add notes/tags',
                'students.export' => 'Export students',
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
                'studio.locations' => 'Manage locations/rooms',
                'studio.booking_page' => 'Manage booking page',
                'studio.policies' => 'Manage policies',
            ],
            'team' => [
                'team.view' => 'View team members',
                'team.manage' => 'Manage team (invite/deactivate)',
                'team.instructors' => 'Manage instructor profiles',
                'team.permissions' => 'Change permissions',
            ],
            'billing' => [
                'billing.plan' => 'Manage plan',
                'billing.invoices' => 'View invoices',
                'billing.payment' => 'Update payment method',
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
