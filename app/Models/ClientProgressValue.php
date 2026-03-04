<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProgressValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'client_progress_report_id',
        'progress_template_metric_id',
        'value_numeric',
        'value_text',
        'value_json',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'value_numeric' => 'decimal:2',
            'value_json' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->recorded_at) {
                $model->recorded_at = now();
            }
        });
    }

    /**
     * The report this value belongs to
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ClientProgressReport::class, 'client_progress_report_id');
    }

    /**
     * The metric this value is for
     */
    public function metric(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplateMetric::class, 'progress_template_metric_id');
    }

    /**
     * Get the appropriate value based on metric type
     */
    public function getValue()
    {
        if ($this->value_numeric !== null) {
            return $this->value_numeric;
        }

        if ($this->value_json !== null) {
            return $this->value_json;
        }

        return $this->value_text;
    }

    /**
     * Get formatted display value
     */
    public function getFormattedValueAttribute(): string
    {
        $metric = $this->metric;
        $value = $this->getValue();

        if ($value === null) {
            return '-';
        }

        switch ($metric?->metric_type) {
            case ProgressTemplateMetric::TYPE_SLIDER:
            case ProgressTemplateMetric::TYPE_NUMBER:
                $formatted = number_format($value, $metric->step < 1 ? 1 : 0);
                return $metric->unit ? "{$formatted} {$metric->unit}" : $formatted;

            case ProgressTemplateMetric::TYPE_RATING:
                $labels = $metric->rating_labels ?? ProgressTemplateMetric::getDefaultRatingLabels();
                $index = min(max(0, (int) $value - 1), count($labels) - 1);
                return $labels[$index] ?? $value;

            case ProgressTemplateMetric::TYPE_SELECT:
                $options = $metric->options ?? [];
                return in_array($value, $options) ? $value : '-';

            case ProgressTemplateMetric::TYPE_CHECKBOX_LIST:
                if (is_array($value)) {
                    return implode(', ', $value);
                }
                return $value;

            case ProgressTemplateMetric::TYPE_TEXT:
            default:
                return (string) $value;
        }
    }

    /**
     * Set value based on metric type
     */
    public function setValueByType($value, string $metricType): void
    {
        switch ($metricType) {
            case ProgressTemplateMetric::TYPE_SLIDER:
            case ProgressTemplateMetric::TYPE_NUMBER:
            case ProgressTemplateMetric::TYPE_RATING:
                $this->value_numeric = $value;
                $this->value_text = null;
                $this->value_json = null;
                break;

            case ProgressTemplateMetric::TYPE_CHECKBOX_LIST:
                $this->value_json = is_array($value) ? $value : [$value];
                $this->value_numeric = null;
                $this->value_text = null;
                break;

            case ProgressTemplateMetric::TYPE_SELECT:
            case ProgressTemplateMetric::TYPE_TEXT:
            default:
                $this->value_text = $value;
                $this->value_numeric = null;
                $this->value_json = null;
                break;
        }
    }
}
