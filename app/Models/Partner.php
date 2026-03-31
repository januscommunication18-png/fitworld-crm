<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'percentage',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active partners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total percentage allocated to all active partners
     */
    public static function getTotalAllocatedPercentage(): float
    {
        return static::active()->sum('percentage');
    }

    /**
     * Get remaining percentage available
     */
    public static function getRemainingPercentage(): float
    {
        return 100 - static::getTotalAllocatedPercentage();
    }

    /**
     * Check if a percentage value is valid (won't exceed 100% total)
     */
    public static function isValidPercentage(float $percentage, ?int $excludeId = null): bool
    {
        $query = static::active();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $currentTotal = $query->sum('percentage');

        return ($currentTotal + $percentage) <= 100;
    }
}
