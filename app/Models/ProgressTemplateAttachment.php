<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressTemplateAttachment extends Model
{
    use HasFactory;

    public const TRIGGER_BEFORE_CLASS = 'before_class';
    public const TRIGGER_AFTER_CLASS = 'after_class';
    public const TRIGGER_ANY = 'any';

    public const FREQUENCY_EVERY_CLASS = 'every_class';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_BIWEEKLY = 'biweekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_CUSTOM = 'custom';

    protected $fillable = [
        'host_id',
        'class_plan_id',
        'progress_template_id',
        'is_required',
        'trigger_point',
        'tracking_frequency',
        'tracking_interval_days',
        'notify_instructor',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'notify_instructor' => 'boolean',
            'tracking_interval_days' => 'integer',
        ];
    }

    /**
     * The host this belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * The class plan this is attached to
     */
    public function classPlan(): BelongsTo
    {
        return $this->belongsTo(ClassPlan::class);
    }

    /**
     * The progress template that's attached
     */
    public function progressTemplate(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplate::class);
    }

    /**
     * Get trigger point options for forms
     */
    public static function getTriggerPointOptions(): array
    {
        return [
            self::TRIGGER_AFTER_CLASS => 'After Class',
            self::TRIGGER_BEFORE_CLASS => 'Before Class',
            self::TRIGGER_ANY => 'Any Time',
        ];
    }

    /**
     * Get tracking frequency options for forms
     */
    public static function getTrackingFrequencyOptions(): array
    {
        return [
            self::FREQUENCY_EVERY_CLASS => 'Every Class',
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_BIWEEKLY => 'Every 2 Weeks',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_CUSTOM => 'Custom Interval',
        ];
    }

    /**
     * Get the number of days between tracking based on frequency
     */
    public function getTrackingIntervalInDays(): ?int
    {
        return match ($this->tracking_frequency) {
            self::FREQUENCY_EVERY_CLASS => null, // Track every class, no day interval
            self::FREQUENCY_DAILY => 1,
            self::FREQUENCY_WEEKLY => 7,
            self::FREQUENCY_BIWEEKLY => 14,
            self::FREQUENCY_MONTHLY => 30,
            self::FREQUENCY_CUSTOM => $this->tracking_interval_days,
            default => null,
        };
    }

    /**
     * Get attachments for a class plan
     */
    public static function getForClassPlan(int $classPlanId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('class_plan_id', $classPlanId)
            ->with('progressTemplate')
            ->get();
    }

    /**
     * Sync attachments for a class plan
     */
    public static function syncForClassPlan(
        int $hostId,
        int $classPlanId,
        array $templateIds,
        array $triggerPoints = [],
        array $isRequired = []
    ): void {
        // Delete removed attachments
        static::where('class_plan_id', $classPlanId)
            ->whereNotIn('progress_template_id', $templateIds)
            ->delete();

        // Create or update attachments
        foreach ($templateIds as $templateId) {
            static::updateOrCreate(
                [
                    'host_id' => $hostId,
                    'class_plan_id' => $classPlanId,
                    'progress_template_id' => $templateId,
                ],
                [
                    'trigger_point' => $triggerPoints[$templateId] ?? self::TRIGGER_AFTER_CLASS,
                    'is_required' => $isRequired[$templateId] ?? false,
                    'notify_instructor' => true,
                ]
            );
        }
    }
}
