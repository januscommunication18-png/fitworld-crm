<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressTemplateMetric extends Model
{
    use HasFactory;

    const TYPE_SLIDER = 'slider';
    const TYPE_NUMBER = 'number';
    const TYPE_SELECT = 'select';
    const TYPE_CHECKBOX_LIST = 'checkbox_list';
    const TYPE_RATING = 'rating';
    const TYPE_TEXT = 'text';

    protected $fillable = [
        'progress_template_section_id',
        'name',
        'metric_key',
        'description',
        'metric_type',
        'unit',
        'min_value',
        'max_value',
        'step',
        'options',
        'rating_labels',
        'is_required',
        'weight',
        'chart_color',
        'show_on_summary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'rating_labels' => 'array',
            'is_required' => 'boolean',
            'show_on_summary' => 'boolean',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'step' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    /**
     * The section this metric belongs to
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplateSection::class, 'progress_template_section_id');
    }

    /**
     * Values recorded for this metric
     */
    public function values(): HasMany
    {
        return $this->hasMany(ClientProgressValue::class);
    }

    /**
     * Get available metric types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SLIDER => 'Slider (1-10)',
            self::TYPE_NUMBER => 'Number Input',
            self::TYPE_SELECT => 'Dropdown Select',
            self::TYPE_CHECKBOX_LIST => 'Checkbox List',
            self::TYPE_RATING => 'Rating Scale',
            self::TYPE_TEXT => 'Text Area',
        ];
    }

    /**
     * Check if metric is numeric (for charts)
     */
    public function isNumeric(): bool
    {
        return in_array($this->metric_type, [
            self::TYPE_SLIDER,
            self::TYPE_NUMBER,
            self::TYPE_RATING,
        ]);
    }

    /**
     * Get default rating labels
     */
    public static function getDefaultRatingLabels(): array
    {
        return ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope: Summary metrics only
     */
    public function scopeSummary($query)
    {
        return $query->where('show_on_summary', true);
    }

    /**
     * Scope: Numeric metrics for charting
     */
    public function scopeNumeric($query)
    {
        return $query->whereIn('metric_type', [
            self::TYPE_SLIDER,
            self::TYPE_NUMBER,
            self::TYPE_RATING,
        ]);
    }
}
