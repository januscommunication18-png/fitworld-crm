<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class QuestionnaireBlock extends Model
{
    use HasFactory;

    // Display style constants
    const STYLE_PLAIN = 'plain';
    const STYLE_CARD = 'card';

    // Visibility constants
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_INTERNAL = 'internal';

    protected $fillable = [
        'questionnaire_version_id',
        'step_id',
        'title',
        'description',
        'display_style',
        'visibility',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class, 'questionnaire_version_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireStep::class, 'step_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireQuestion::class)->orderBy('sort_order');
    }

    // Scopes

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_INTERNAL);
    }

    public function scopeForStep(Builder $query, $stepId): Builder
    {
        return $query->where('step_id', $stepId);
    }

    public function scopeWithoutStep(Builder $query): Builder
    {
        return $query->whereNull('step_id');
    }

    // Methods

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isInternal(): bool
    {
        return $this->visibility === self::VISIBILITY_INTERNAL;
    }

    public function isCardStyle(): bool
    {
        return $this->display_style === self::STYLE_CARD;
    }

    public function getQuestionCount(): int
    {
        return $this->questions->count();
    }

    public function getRequiredQuestionCount(): int
    {
        return $this->questions->where('is_required', true)->count();
    }

    // Static helpers

    public static function getDisplayStyles(): array
    {
        return [
            self::STYLE_PLAIN => 'Plain',
            self::STYLE_CARD => 'Card',
        ];
    }

    public static function getVisibilities(): array
    {
        return [
            self::VISIBILITY_PUBLIC => 'Client Visible',
            self::VISIBILITY_INTERNAL => 'Internal Only',
        ];
    }
}
