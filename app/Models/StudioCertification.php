<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudioCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'instructor_id',
        'user_id',
        'name',
        'certification_name',
        'expire_date',
        'file_path',
        'file_name',
        'reminder_days',
        'reminder_sent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expire_date' => 'date',
            'reminder_days' => 'integer',
            'reminder_sent' => 'boolean',
        ];
    }

    /**
     * Get the host that owns the certification
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Get the instructor this certification belongs to (if any)
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the user this certification belongs to (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is an instructor certification
     */
    public function isInstructorCertification(): bool
    {
        return $this->instructor_id !== null;
    }

    /**
     * Check if this is a user/team member certification
     */
    public function isUserCertification(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Check if this is a studio certification
     */
    public function isStudioCertification(): bool
    {
        return $this->instructor_id === null && $this->user_id === null;
    }

    /**
     * Check if the certification is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expire_date) {
            return false;
        }

        return $this->expire_date->isPast();
    }

    /**
     * Check if the certification is expiring soon
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expire_date || !$this->reminder_days) {
            return false;
        }

        return $this->expire_date->isBetween(now(), now()->addDays($this->reminder_days));
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expire_date) {
            return null;
        }

        return now()->diffInDays($this->expire_date, false);
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isExpired()) {
            return 'badge-error';
        }

        if ($this->isExpiringSoon()) {
            return 'badge-warning';
        }

        return 'badge-success';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isExpiringSoon()) {
            return 'Expiring Soon';
        }

        if (!$this->expire_date) {
            return 'No Expiry';
        }

        return 'Valid';
    }

    /**
     * Scope to get certifications for a specific host
     */
    public function scopeForHost($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Scope to get certifications for a specific instructor
     */
    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Scope to get studio-level certifications (not instructor or user specific)
     */
    public function scopeStudioLevel($query)
    {
        return $query->whereNull('instructor_id')->whereNull('user_id');
    }

    /**
     * Scope to get instructor certifications only
     */
    public function scopeInstructorLevel($query)
    {
        return $query->whereNotNull('instructor_id');
    }

    /**
     * Scope to get user/team member certifications only
     */
    public function scopeUserLevel($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope to get certifications for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get expired certifications
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expire_date')->where('expire_date', '<', now());
    }

    /**
     * Scope to get expiring soon certifications
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expire_date')
            ->where('expire_date', '>=', now())
            ->where('expire_date', '<=', now()->addDays($days));
    }
}
