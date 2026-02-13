<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class QuestionnaireStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_version_id',
        'title',
        'description',
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

    public function blocks(): HasMany
    {
        return $this->hasMany(QuestionnaireBlock::class, 'step_id')->orderBy('sort_order');
    }

    // Scopes

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // Methods

    public function getQuestionCount(): int
    {
        return $this->blocks->sum(function ($block) {
            return $block->questions->count();
        });
    }

    public function getRequiredQuestionCount(): int
    {
        return $this->blocks->sum(function ($block) {
            return $block->questions->where('is_required', true)->count();
        });
    }

    public function getAllQuestions()
    {
        return $this->blocks->flatMap(function ($block) {
            return $block->questions;
        });
    }

    public function getStepNumber(): int
    {
        return $this->version->steps()
            ->where('sort_order', '<=', $this->sort_order)
            ->count();
    }
}
