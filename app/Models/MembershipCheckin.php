<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipCheckin extends Model
{
    protected $fillable = [
        'host_id',
        'client_id',
        'customer_membership_id',
        'membership_plan_id',
        'checked_in_at',
        'checked_out_at',
        'checked_in_by',
        'location_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function customerMembership(): BelongsTo
    {
        return $this->belongsTo(CustomerMembership::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForHost(Builder $query, int $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForPlan(Builder $query, int $planId): Builder
    {
        return $query->where('membership_plan_id', $planId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('checked_in_at', today());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsCheckedOutAttribute(): bool
    {
        return $this->checked_out_at !== null;
    }
}
