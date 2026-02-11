<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskMessage extends Model
{
    use HasFactory;

    // Sender type constants
    const SENDER_STAFF = 'staff';
    const SENDER_CUSTOMER = 'customer';
    const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'sender_type',
        'message',
    ];

    // Relationships

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors

    public function getIsStaffMessageAttribute(): bool
    {
        return $this->sender_type === self::SENDER_STAFF;
    }

    public function getIsCustomerMessageAttribute(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    public function getIsSystemMessageAttribute(): bool
    {
        return $this->sender_type === self::SENDER_SYSTEM;
    }

    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === self::SENDER_CUSTOMER) {
            return $this->ticket->name ?? 'Customer';
        }

        if ($this->sender_type === self::SENDER_SYSTEM) {
            return 'System';
        }

        return $this->user?->name ?? 'Staff';
    }

    // Static helpers

    public static function getSenderTypes(): array
    {
        return [
            self::SENDER_STAFF => 'Staff',
            self::SENDER_CUSTOMER => 'Customer',
            self::SENDER_SYSTEM => 'System',
        ];
    }
}
