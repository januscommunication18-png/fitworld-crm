<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProgressReport extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'host_id',
        'client_id',
        'progress_template_id',
        'booking_id',
        'class_session_id',
        'report_date',
        'overall_score',
        'status',
        'trainer_notes',
        'goals_notes',
        'recorded_by_user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'overall_score' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * The host this report belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * The client this report is for
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The template used for this report
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplate::class, 'progress_template_id');
    }

    /**
     * The user who recorded this report
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * The booking this report is linked to (if created from a class)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The class session this report is linked to (if created from a class)
     */
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    /**
     * Metric values recorded in this report
     */
    public function values(): HasMany
    {
        return $this->hasMany(ClientProgressValue::class);
    }

    /**
     * Photos attached to this report
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ClientProgressPhoto::class)->orderBy('sort_order');
    }

    /**
     * Body measurements in this report
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(ClientProgressMeasurement::class);
    }

    /**
     * Get before photos
     */
    public function beforePhotos(): HasMany
    {
        return $this->photos()->where('photo_type', 'before');
    }

    /**
     * Get after photos
     */
    public function afterPhotos(): HasMany
    {
        return $this->photos()->where('photo_type', 'after');
    }

    /**
     * Mark report as completed and calculate score
     */
    public function complete(): void
    {
        $this->calculateOverallScore();
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate and save overall score based on metric weights
     */
    public function calculateOverallScore(): void
    {
        $totalWeight = 0;
        $weightedSum = 0;

        $values = $this->values()->with('metric')->get();

        foreach ($values as $value) {
            $metric = $value->metric;
            if (!$metric || !$metric->isNumeric()) {
                continue;
            }

            $weight = $metric->weight ?? 1;
            $normalizedValue = $this->normalizeValue($value->value_numeric, $metric);

            $weightedSum += $normalizedValue * $weight;
            $totalWeight += $weight;
        }

        $this->overall_score = $totalWeight > 0 ? ($weightedSum / $totalWeight) : null;
        $this->save();
    }

    /**
     * Normalize a value to 0-100 scale
     */
    protected function normalizeValue($value, ProgressTemplateMetric $metric): float
    {
        if ($value === null) {
            return 0;
        }

        $min = $metric->min_value ?? 0;
        $max = $metric->max_value ?? 10;

        if ($max == $min) {
            return 50; // Can't normalize if range is zero
        }

        return (($value - $min) / ($max - $min)) * 100;
    }

    /**
     * Get value for a specific metric
     */
    public function getValueForMetric(int $metricId)
    {
        return $this->values()->where('progress_template_metric_id', $metricId)->first();
    }

    /**
     * Set value for a metric
     */
    public function setValueForMetric(int $metricId, $value, string $type = 'numeric'): ClientProgressValue
    {
        $data = ['progress_template_metric_id' => $metricId];

        switch ($type) {
            case 'numeric':
                $data['value_numeric'] = $value;
                break;
            case 'text':
                $data['value_text'] = $value;
                break;
            case 'json':
                $data['value_json'] = $value;
                break;
        }

        return $this->values()->updateOrCreate(
            ['progress_template_metric_id' => $metricId],
            $data
        );
    }

    /**
     * Check if report is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if report is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Scope: For a specific host
     */
    public function scopeForHost($query, int $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Scope: For a specific client
     */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope: Completed reports only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Draft reports only
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope: Order by report date (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('report_date', 'desc');
    }

    /**
     * Get comparison data between two reports
     */
    public static function getComparisonData(int $reportId1, int $reportId2): array
    {
        $report1 = static::with(['values.metric', 'measurements'])->find($reportId1);
        $report2 = static::with(['values.metric', 'measurements'])->find($reportId2);

        if (!$report1 || !$report2) {
            return [];
        }

        $comparison = [
            'report1' => $report1,
            'report2' => $report2,
            'metrics' => [],
            'measurements' => [],
        ];

        // Compare metric values
        foreach ($report1->values as $value1) {
            $metric = $value1->metric;
            if (!$metric) continue;

            $value2 = $report2->values->where('progress_template_metric_id', $metric->id)->first();

            $comparison['metrics'][$metric->id] = [
                'metric' => $metric,
                'value1' => $value1->value_numeric,
                'value2' => $value2?->value_numeric,
                'change' => $value2 ? ($value2->value_numeric - $value1->value_numeric) : null,
            ];
        }

        // Compare measurements
        foreach ($report1->measurements as $m1) {
            $m2 = $report2->measurements->where('measurement_type', $m1->measurement_type)->first();

            $comparison['measurements'][$m1->measurement_type] = [
                'type' => $m1->measurement_type,
                'value1' => $m1->value,
                'value2' => $m2?->value,
                'change' => $m2 ? ($m2->value - $m1->value) : null,
                'unit' => $m1->unit,
            ];
        }

        return $comparison;
    }
}
