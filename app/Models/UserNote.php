<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserNote extends Model
{
    use HasFactory;

    // Note type constants
    const TYPE_NOTE = 'note';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_HR = 'hr';
    const TYPE_INCIDENT = 'incident';
    const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'subject_user_id',
        'host_id',
        'author_id',
        'note_type',
        'content',
        'is_visible_to_user',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_user' => 'boolean',
        ];
    }

    // Relationships

    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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
            self::TYPE_PERFORMANCE,
            self::TYPE_HR,
            self::TYPE_INCIDENT,
        ]);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('note_type', self::TYPE_SYSTEM);
    }

    public function scopeVisibleToUser(Builder $query): Builder
    {
        return $query->where('is_visible_to_user', true);
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
            self::TYPE_PERFORMANCE => 'Performance',
            self::TYPE_HR => 'HR',
            self::TYPE_INCIDENT => 'Incident',
            self::TYPE_SYSTEM => 'System',
        ];
    }

    public static function getNoteTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_NOTE => 'icon-[tabler--note]',
            self::TYPE_PERFORMANCE => 'icon-[tabler--chart-line]',
            self::TYPE_HR => 'icon-[tabler--briefcase]',
            self::TYPE_INCIDENT => 'icon-[tabler--alert-triangle]',
            self::TYPE_SYSTEM => 'icon-[tabler--info-circle]',
            default => 'icon-[tabler--note]',
        };
    }

    public static function getNoteTypeBadgeClass(string $type): string
    {
        return match ($type) {
            self::TYPE_NOTE => 'badge-info',
            self::TYPE_PERFORMANCE => 'badge-success',
            self::TYPE_HR => 'badge-secondary',
            self::TYPE_INCIDENT => 'badge-error',
            self::TYPE_SYSTEM => 'badge-neutral',
            default => 'badge-info',
        };
    }
}
