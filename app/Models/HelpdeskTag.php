<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class HelpdeskTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'name',
        'color',
    ];

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(HelpdeskTicket::class, 'helpdesk_ticket_tag', 'tag_id', 'ticket_id');
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    // Static helpers

    public static function getDefaultColors(): array
    {
        return [
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#ef4444', // Red
            '#f97316', // Orange
            '#eab308', // Yellow
            '#22c55e', // Green
            '#14b8a6', // Teal
            '#06b6d4', // Cyan
            '#3b82f6', // Blue
        ];
    }
}
