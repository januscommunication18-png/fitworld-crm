<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClientNote extends Model
{
    use HasFactory;

    // Note type constants
    const TYPE_NOTE = 'note';
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_BOOKING = 'booking';
    const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'client_id',
        'user_id',
        'note_type',
        'content',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('note_type', $type);
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->whereIn('note_type', [self::TYPE_NOTE, self::TYPE_CALL, self::TYPE_EMAIL]);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('note_type', self::TYPE_SYSTEM);
    }

    // Static helpers

    public static function getNoteTypes(): array
    {
        return [
            self::TYPE_NOTE => 'Note',
            self::TYPE_CALL => 'Call',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_BOOKING => 'Booking',
            self::TYPE_SYSTEM => 'System',
        ];
    }

    public static function getNoteTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_NOTE => 'icon-[tabler--note]',
            self::TYPE_CALL => 'icon-[tabler--phone]',
            self::TYPE_EMAIL => 'icon-[tabler--mail]',
            self::TYPE_BOOKING => 'icon-[tabler--calendar-event]',
            self::TYPE_SYSTEM => 'icon-[tabler--info-circle]',
            default => 'icon-[tabler--note]',
        };
    }
}
