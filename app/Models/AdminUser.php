<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    const ROLE_ADMINISTRATOR = 'administrator';
    const ROLE_TEAM_MEMBER = 'team_member';

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_DEACTIVATED = 'deactivated';

    const PERMISSIONS = [
        'dashboard' => 'View Dashboard',
        'clients' => 'Manage Clients',
        'plans' => 'Manage Plans',
        'email_templates' => 'Manage Email Templates',
        'email_logs' => 'View Email Logs',
        'admin_members' => 'Manage Admin Members',
        'settings' => 'Manage Settings',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'permissions',
        'status',
        'must_change_password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is administrator
     */
    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    /**
     * Check if user is team member
     */
    public function isTeamMember(): bool
    {
        return $this->role === self::ROLE_TEAM_MEMBER;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user has permission for a section
     */
    public function hasPermission(string $section): bool
    {
        // Administrators have all permissions
        if ($this->isAdministrator()) {
            return true;
        }

        // Check if permission exists in user's permissions array
        $permissions = $this->permissions ?? [];
        return in_array($section, $permissions) || ($permissions[$section] ?? false);
    }

    /**
     * Get all permissions for this user
     */
    public function getAllPermissions(): array
    {
        if ($this->isAdministrator()) {
            return array_keys(self::PERMISSIONS);
        }

        return $this->permissions ?? [];
    }

    /**
     * Get default permissions for team members
     */
    public static function getDefaultTeamMemberPermissions(): array
    {
        return [
            'dashboard',
            'clients',
            'email_templates',
            'email_logs',
        ];
    }

    /**
     * Get available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMINISTRATOR => 'Administrator',
            self::ROLE_TEAM_MEMBER => 'Team Member',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_DEACTIVATED => 'Deactivated',
        ];
    }
}
