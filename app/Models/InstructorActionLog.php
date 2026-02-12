<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorActionLog extends Model
{
    use HasFactory;

    // Disable updated_at since we only use created_at
    public $timestamps = false;

    // Action type constants
    const ACTION_STATUS_CHANGE = 'status_change';
    const ACTION_PASSWORD_RESET = 'password_reset';
    const ACTION_PROFILE_UPDATE = 'profile_update';
    const ACTION_PHOTO_UPLOAD = 'photo_upload';
    const ACTION_PHOTO_REMOVE = 'photo_remove';
    const ACTION_INVITATION_SENT = 'invitation_sent';
    const ACTION_INVITATION_ACCEPTED = 'invitation_accepted';
    const ACTION_ACCOUNT_LINKED = 'account_linked';
    const ACTION_ACCOUNT_UNLINKED = 'account_unlinked';

    protected $fillable = [
        'instructor_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Relationships

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Static helper to log an action

    public static function log(
        int $instructorId,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?int $userId = null,
        ?string $reason = null
    ): self {
        return self::create([
            'instructor_id' => $instructorId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }

    // Static helpers

    public static function getActionTypes(): array
    {
        return [
            self::ACTION_STATUS_CHANGE => 'Status Changed',
            self::ACTION_PASSWORD_RESET => 'Password Reset',
            self::ACTION_PROFILE_UPDATE => 'Profile Updated',
            self::ACTION_PHOTO_UPLOAD => 'Photo Uploaded',
            self::ACTION_PHOTO_REMOVE => 'Photo Removed',
            self::ACTION_INVITATION_SENT => 'Invitation Sent',
            self::ACTION_INVITATION_ACCEPTED => 'Invitation Accepted',
            self::ACTION_ACCOUNT_LINKED => 'Account Linked',
            self::ACTION_ACCOUNT_UNLINKED => 'Account Unlinked',
        ];
    }

    public static function getActionIcon(string $action): string
    {
        return match ($action) {
            self::ACTION_STATUS_CHANGE => 'icon-[tabler--toggle-left]',
            self::ACTION_PASSWORD_RESET => 'icon-[tabler--key]',
            self::ACTION_PROFILE_UPDATE => 'icon-[tabler--edit]',
            self::ACTION_PHOTO_UPLOAD => 'icon-[tabler--photo-up]',
            self::ACTION_PHOTO_REMOVE => 'icon-[tabler--photo-off]',
            self::ACTION_INVITATION_SENT => 'icon-[tabler--mail-forward]',
            self::ACTION_INVITATION_ACCEPTED => 'icon-[tabler--mail-check]',
            self::ACTION_ACCOUNT_LINKED => 'icon-[tabler--link]',
            self::ACTION_ACCOUNT_UNLINKED => 'icon-[tabler--unlink]',
            default => 'icon-[tabler--activity]',
        };
    }

    public function getActionLabel(): string
    {
        return self::getActionTypes()[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
