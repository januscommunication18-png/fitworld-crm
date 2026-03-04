<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressTemplateSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_template_id',
        'name',
        'description',
        'icon',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    /**
     * The template this section belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplate::class, 'progress_template_id');
    }

    /**
     * Metrics within this section
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ProgressTemplateMetric::class)->orderBy('sort_order');
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
