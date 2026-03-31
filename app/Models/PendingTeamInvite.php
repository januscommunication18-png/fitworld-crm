<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingTeamInvite extends Model
{
    use HasFactory;

    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_INSTRUCTOR = 'instructor';

    protected $fillable = [
        'host_id',
        'name',
        'email',
        'role',
        'sent',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the host that this pending invite belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Check if the invite has been sent
     */
    public function hasBeenSent(): bool
    {
        return $this->sent;
    }

    /**
     * Mark the invite as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'sent' => true,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get available roles for team invites
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_STAFF => 'Staff',
            self::ROLE_INSTRUCTOR => 'Instructor',
        ];
    }

    /**
     * Scope to get unsent invites
     */
    public function scopeUnsent($query)
    {
        return $query->where('sent', false);
    }

    /**
     * Scope to get sent invites
     */
    public function scopeSent($query)
    {
        return $query->where('sent', true);
    }
}
