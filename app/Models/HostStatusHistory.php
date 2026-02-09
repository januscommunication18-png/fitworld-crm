<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'host_status_history';

    protected $fillable = [
        'host_id',
        'admin_user_id',
        'old_status',
        'new_status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the host
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Get the admin user who made the change
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    /**
     * Log a status change
     */
    public static function log(
        int $hostId,
        string $newStatus,
        ?string $oldStatus = null,
        ?int $adminUserId = null,
        ?string $reason = null
    ): self {
        return self::create([
            'host_id' => $hostId,
            'admin_user_id' => $adminUserId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);
    }
}
