<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'client_id',
        'recorded_by_user_id',
        'measured_at',
        'weight',
        'weight_unit',
        'body_fat',
        'chest',
        'waist',
        'hips',
        'biceps_left',
        'biceps_right',
        'thigh_left',
        'thigh_right',
        'calf_left',
        'calf_right',
        'neck',
        'shoulders',
        'measurement_unit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'date',
            'weight' => 'decimal:2',
            'body_fat' => 'decimal:2',
            'chest' => 'decimal:2',
            'waist' => 'decimal:2',
            'hips' => 'decimal:2',
            'biceps_left' => 'decimal:2',
            'biceps_right' => 'decimal:2',
            'thigh_left' => 'decimal:2',
            'thigh_right' => 'decimal:2',
            'calf_left' => 'decimal:2',
            'calf_right' => 'decimal:2',
            'neck' => 'decimal:2',
            'shoulders' => 'decimal:2',
        ];
    }

    /**
     * The host this measurement belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * The client this measurement is for
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user who recorded this measurement
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * Get all measurement fields
     */
    public static function getMeasurementFields(): array
    {
        return [
            'weight' => ['label' => 'Weight', 'icon' => 'scale', 'unit_field' => 'weight_unit', 'category' => 'body'],
            'body_fat' => ['label' => 'Body Fat %', 'icon' => 'percentage', 'unit' => '%', 'category' => 'body'],
            'chest' => ['label' => 'Chest', 'icon' => 'ruler-measure', 'category' => 'upper'],
            'waist' => ['label' => 'Waist', 'icon' => 'ruler-measure', 'category' => 'core'],
            'hips' => ['label' => 'Hips', 'icon' => 'ruler-measure', 'category' => 'lower'],
            'shoulders' => ['label' => 'Shoulders', 'icon' => 'ruler-measure', 'category' => 'upper'],
            'neck' => ['label' => 'Neck', 'icon' => 'ruler-measure', 'category' => 'upper'],
            'biceps_left' => ['label' => 'Left Bicep', 'icon' => 'barbell', 'category' => 'arms'],
            'biceps_right' => ['label' => 'Right Bicep', 'icon' => 'barbell', 'category' => 'arms'],
            'thigh_left' => ['label' => 'Left Thigh', 'icon' => 'ruler-measure', 'category' => 'legs'],
            'thigh_right' => ['label' => 'Right Thigh', 'icon' => 'ruler-measure', 'category' => 'legs'],
            'calf_left' => ['label' => 'Left Calf', 'icon' => 'ruler-measure', 'category' => 'legs'],
            'calf_right' => ['label' => 'Right Calf', 'icon' => 'ruler-measure', 'category' => 'legs'],
        ];
    }

    /**
     * Get measurements grouped by category
     */
    public static function getMeasurementsByCategory(): array
    {
        $fields = self::getMeasurementFields();
        $grouped = [];

        foreach ($fields as $key => $field) {
            $category = $field['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = $field;
        }

        return $grouped;
    }

    /**
     * Get the change compared to previous measurement
     */
    public function getChangeFromPrevious(string $field): ?array
    {
        $previous = static::where('client_id', $this->client_id)
            ->where('measured_at', '<', $this->measured_at)
            ->orderBy('measured_at', 'desc')
            ->first();

        if (!$previous || $previous->$field === null || $this->$field === null) {
            return null;
        }

        $change = $this->$field - $previous->$field;
        $percentage = $previous->$field != 0
            ? round(($change / $previous->$field) * 100, 1)
            : 0;

        return [
            'change' => $change,
            'percentage' => $percentage,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
            'previous_value' => $previous->$field,
            'previous_date' => $previous->measured_at,
        ];
    }

    /**
     * Get filled measurement count
     */
    public function getFilledMeasurementsCount(): int
    {
        $fields = array_keys(self::getMeasurementFields());
        $count = 0;

        foreach ($fields as $field) {
            if ($this->$field !== null) {
                $count++;
            }
        }

        return $count;
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
     * Scope: Order by date (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('measured_at', 'desc');
    }

    /**
     * Scope: Order by date (oldest first)
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('measured_at', 'asc');
    }
}