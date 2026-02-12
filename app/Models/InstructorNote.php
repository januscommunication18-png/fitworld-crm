<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InstructorNote extends Model
{
    use HasFactory;

    // Note type constants
    const TYPE_NOTE = 'note';
    const TYPE_PAYROLL = 'payroll';
    const TYPE_AVAILABILITY = 'availability';
    const TYPE_INCIDENT = 'incident';
    const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'instructor_id',
        'host_id',
        'user_id',
        'note_type',
        'content',
        'is_visible_to_instructor',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_instructor' => 'boolean',
        ];
    }

    // Relationships

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
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
        return $query->whereIn('note_type', [
            self::TYPE_NOTE,
            self::TYPE_PAYROLL,
            self::TYPE_AVAILABILITY,
            self::TYPE_INCIDENT,
        ]);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('note_type', self::TYPE_SYSTEM);
    }

    public function scopeVisibleToInstructor(Builder $query): Builder
    {
        return $query->where('is_visible_to_instructor', true);
    }

    public function scopeForHost(Builder $query, int $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    // Static helpers

    public static function getNoteTypes(): array
    {
        return [
            self::TYPE_NOTE => 'General Note',
            self::TYPE_PAYROLL => 'Payroll',
            self::TYPE_AVAILABILITY => 'Availability',
            self::TYPE_INCIDENT => 'Incident',
            self::TYPE_SYSTEM => 'System',
        ];
    }

    public static function getNoteTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_NOTE => 'icon-[tabler--note]',
            self::TYPE_PAYROLL => 'icon-[tabler--wallet]',
            self::TYPE_AVAILABILITY => 'icon-[tabler--calendar-time]',
            self::TYPE_INCIDENT => 'icon-[tabler--alert-triangle]',
            self::TYPE_SYSTEM => 'icon-[tabler--info-circle]',
            default => 'icon-[tabler--note]',
        };
    }

    public static function getNoteTypeBadgeClass(string $type): string
    {
        return match ($type) {
            self::TYPE_NOTE => 'badge-info',
            self::TYPE_PAYROLL => 'badge-success',
            self::TYPE_AVAILABILITY => 'badge-warning',
            self::TYPE_INCIDENT => 'badge-error',
            self::TYPE_SYSTEM => 'badge-neutral',
            default => 'badge-info',
        };
    }
}
