<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProgressMeasurement extends Model
{
    use HasFactory;

    const TYPE_WEIGHT = 'weight';
    const TYPE_BODY_FAT = 'body_fat';
    const TYPE_CHEST = 'chest';
    const TYPE_WAIST = 'waist';
    const TYPE_HIPS = 'hips';
    const TYPE_BICEPS = 'biceps';
    const TYPE_THIGH = 'thigh';
    const TYPE_CALF = 'calf';
    const TYPE_NECK = 'neck';
    const TYPE_SHOULDERS = 'shoulders';

    protected $fillable = [
        'client_progress_report_id',
        'measurement_type',
        'value',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    /**
     * The report this measurement belongs to
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ClientProgressReport::class, 'client_progress_report_id');
    }

    /**
     * Get available measurement types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_WEIGHT => 'Weight',
            self::TYPE_BODY_FAT => 'Body Fat %',
            self::TYPE_CHEST => 'Chest',
            self::TYPE_WAIST => 'Waist',
            self::TYPE_HIPS => 'Hips',
            self::TYPE_BICEPS => 'Biceps',
            self::TYPE_THIGH => 'Thigh',
            self::TYPE_CALF => 'Calf',
            self::TYPE_NECK => 'Neck',
            self::TYPE_SHOULDERS => 'Shoulders',
        ];
    }

    /**
     * Get default units for each measurement type
     */
    public static function getDefaultUnits(): array
    {
        return [
            self::TYPE_WEIGHT => 'kg',
            self::TYPE_BODY_FAT => '%',
            self::TYPE_CHEST => 'cm',
            self::TYPE_WAIST => 'cm',
            self::TYPE_HIPS => 'cm',
            self::TYPE_BICEPS => 'cm',
            self::TYPE_THIGH => 'cm',
            self::TYPE_CALF => 'cm',
            self::TYPE_NECK => 'cm',
            self::TYPE_SHOULDERS => 'cm',
        ];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return static::getTypes()[$this->measurement_type] ?? ucfirst($this->measurement_type);
    }

    /**
     * Get formatted value with unit
     */
    public function getFormattedValueAttribute(): string
    {
        return number_format($this->value, 1) . ' ' . $this->unit;
    }

    /**
     * Scope: By measurement type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('measurement_type', $type);
    }
}
