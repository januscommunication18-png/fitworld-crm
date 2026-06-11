<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientQrCode extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'host_id',
        'client_id',
        'qr_token',
        'status',
        'generated_at',
        'last_used_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($qr) {
            if (empty($qr->qr_token)) {
                $qr->qr_token = bin2hex(random_bytes(32));
            }
            if (empty($qr->generated_at)) {
                $qr->generated_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function markUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
