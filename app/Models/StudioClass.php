<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'host_id',
        'class_plan_id',
        'instructor_id',
        'name',
        'type',
        'duration_minutes',
        'capacity',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function classPlan(): BelongsTo
    {
        return $this->belongsTo(ClassPlan::class);
    }

    /**
     * Inherit properties from the associated class plan
     */
    public function inheritFromPlan(): void
    {
        if ($this->classPlan) {
            $this->name = $this->classPlan->name;
            $this->type = $this->classPlan->category;
            $this->duration_minutes = $this->classPlan->default_duration_minutes;
            $this->capacity = $this->classPlan->default_capacity;
            $this->price = $this->classPlan->default_price;
        }
    }
}
