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
}
