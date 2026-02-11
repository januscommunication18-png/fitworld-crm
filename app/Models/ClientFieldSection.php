<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ClientFieldSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(ClientFieldDefinition::class, 'section_id')
            ->orderBy('sort_order');
    }

    public function activeFieldDefinitions(): HasMany
    {
        return $this->hasMany(ClientFieldDefinition::class, 'section_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
