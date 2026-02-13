<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class QuestionnaireAttachment extends Model
{
    use HasFactory;

    // Collection timing constants
    const TIMING_BEFORE_BOOKING = 'before_booking';
    const TIMING_AFTER_BOOKING = 'after_booking';
    const TIMING_BEFORE_FIRST_SESSION = 'before_first_session';

    // Applies to constants
    const APPLIES_FIRST_TIME_ONLY = 'first_time_only';
    const APPLIES_EVERY_BOOKING = 'every_booking';

    protected $fillable = [
        'questionnaire_id',
        'attachable_type',
        'attachable_id',
        'is_required',
        'collection_timing',
        'applies_to',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    // Relationships

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('is_required', false);
    }

    public function scopeBeforeBooking(Builder $query): Builder
    {
        return $query->where('collection_timing', self::TIMING_BEFORE_BOOKING);
    }

    public function scopeAfterBooking(Builder $query): Builder
    {
        return $query->where('collection_timing', self::TIMING_AFTER_BOOKING);
    }

    public function scopeBeforeFirstSession(Builder $query): Builder
    {
        return $query->where('collection_timing', self::TIMING_BEFORE_FIRST_SESSION);
    }

    public function scopeFirstTimeOnly(Builder $query): Builder
    {
        return $query->where('applies_to', self::APPLIES_FIRST_TIME_ONLY);
    }

    public function scopeEveryBooking(Builder $query): Builder
    {
        return $query->where('applies_to', self::APPLIES_EVERY_BOOKING);
    }

    public function scopeForClassPlan(Builder $query): Builder
    {
        return $query->where('attachable_type', 'App\\Models\\ClassPlan');
    }

    public function scopeForServicePlan(Builder $query): Builder
    {
        return $query->where('attachable_type', 'App\\Models\\ServicePlan');
    }

    public function scopeForMembershipPlan(Builder $query): Builder
    {
        return $query->where('attachable_type', 'App\\Models\\MembershipPlan');
    }

    // Methods

    public function isBlocking(): bool
    {
        return $this->is_required && $this->collection_timing === self::TIMING_BEFORE_BOOKING;
    }

    public function isFirstTimeOnly(): bool
    {
        return $this->applies_to === self::APPLIES_FIRST_TIME_ONLY;
    }

    public function requiresCompletionBeforeBooking(): bool
    {
        return $this->collection_timing === self::TIMING_BEFORE_BOOKING;
    }

    public function getAttachableTypeName(): string
    {
        return match ($this->attachable_type) {
            'App\\Models\\ClassPlan' => 'Class Plan',
            'App\\Models\\ServicePlan' => 'Service Plan',
            'App\\Models\\MembershipPlan' => 'Membership Plan',
            default => 'Unknown',
        };
    }

    // Static helpers

    public static function getCollectionTimings(): array
    {
        return [
            self::TIMING_BEFORE_BOOKING => 'Before booking completes (blocking)',
            self::TIMING_AFTER_BOOKING => 'After booking (intake pending)',
            self::TIMING_BEFORE_FIRST_SESSION => 'Before first session',
        ];
    }

    public static function getAppliesTo(): array
    {
        return [
            self::APPLIES_FIRST_TIME_ONLY => 'First-time client only',
            self::APPLIES_EVERY_BOOKING => 'Every booking',
        ];
    }
}
