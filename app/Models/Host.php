<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'studio_name',
        'subdomain',
        'studio_types',
        'city',
        'timezone',
        'address',
        'rooms',
        'default_capacity',
        'room_capacities',
        'amenities',
        'stripe_account_id',
        'is_live',
        'onboarding_step',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'studio_types' => 'array',
            'room_capacities' => 'array',
            'amenities' => 'array',
            'is_live' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(StudioClass::class);
    }
}
