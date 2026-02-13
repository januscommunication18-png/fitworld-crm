<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class QuestionnaireVersion extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'questionnaire_id',
        'version_number',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    // Relationships

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(QuestionnaireStep::class)->orderBy('sort_order');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(QuestionnaireBlock::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    // Scopes

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    // Methods

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function publish(): bool
    {
        // Archive any currently active version
        $this->questionnaire->versions()
            ->where('status', self::STATUS_ACTIVE)
            ->update(['status' => self::STATUS_ARCHIVED]);

        // Activate this version
        $result = $this->update([
            'status' => self::STATUS_ACTIVE,
            'published_at' => now(),
        ]);

        // Also activate the parent questionnaire if it's draft
        if ($this->questionnaire->isDraft()) {
            $this->questionnaire->activate();
        }

        return $result;
    }

    public function archive(): bool
    {
        return $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function duplicateTo(Questionnaire $targetQuestionnaire, ?int $userId = null): self
    {
        // Create new version on target questionnaire
        $newVersion = $targetQuestionnaire->versions()->create([
            'version_number' => 1,
            'status' => self::STATUS_DRAFT,
            'created_by' => $userId,
        ]);

        // Duplicate steps
        $stepMapping = [];
        foreach ($this->steps as $step) {
            $newStep = $newVersion->steps()->create([
                'title' => $step->title,
                'description' => $step->description,
                'sort_order' => $step->sort_order,
            ]);
            $stepMapping[$step->id] = $newStep->id;
        }

        // Duplicate blocks and questions
        foreach ($this->blocks as $block) {
            $newStepId = $block->step_id ? ($stepMapping[$block->step_id] ?? null) : null;

            $newBlock = $newVersion->blocks()->create([
                'step_id' => $newStepId,
                'title' => $block->title,
                'description' => $block->description,
                'display_style' => $block->display_style,
                'visibility' => $block->visibility,
                'sort_order' => $block->sort_order,
            ]);

            // Duplicate questions
            foreach ($block->questions as $question) {
                $newBlock->questions()->create([
                    'question_key' => $question->question_key,
                    'question_label' => $question->question_label,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'is_required' => $question->is_required,
                    'help_text' => $question->help_text,
                    'placeholder' => $question->placeholder,
                    'default_value' => $question->default_value,
                    'validation_rules' => $question->validation_rules,
                    'visibility' => $question->visibility,
                    'is_sensitive' => $question->is_sensitive,
                    'tags' => $question->tags,
                    'sort_order' => $question->sort_order,
                ]);
            }
        }

        return $newVersion;
    }

    /**
     * Copy steps, blocks, and questions to another version (same questionnaire)
     */
    public function copyContentTo(QuestionnaireVersion $targetVersion): void
    {
        // Load all related data
        $this->load(['steps', 'blocks.questions']);

        // Copy steps
        $stepMapping = [];
        foreach ($this->steps as $step) {
            $newStep = $targetVersion->steps()->create([
                'title' => $step->title,
                'description' => $step->description,
                'sort_order' => $step->sort_order,
            ]);
            $stepMapping[$step->id] = $newStep->id;
        }

        // Copy blocks and questions
        foreach ($this->blocks as $block) {
            $newStepId = $block->step_id
                ? ($stepMapping[$block->step_id] ?? null)
                : null;

            $newBlock = $targetVersion->blocks()->create([
                'step_id' => $newStepId,
                'title' => $block->title,
                'description' => $block->description,
                'display_style' => $block->display_style,
                'visibility' => $block->visibility,
                'sort_order' => $block->sort_order,
            ]);

            // Copy questions
            foreach ($block->questions as $question) {
                $newBlock->questions()->create([
                    'question_key' => $question->question_key,
                    'question_label' => $question->question_label,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'is_required' => $question->is_required,
                    'help_text' => $question->help_text,
                    'placeholder' => $question->placeholder,
                    'default_value' => $question->default_value,
                    'validation_rules' => $question->validation_rules,
                    'visibility' => $question->visibility,
                    'is_sensitive' => $question->is_sensitive,
                    'tags' => $question->tags,
                    'sort_order' => $question->sort_order,
                ]);
            }
        }
    }

    public function getTotalQuestionCount(): int
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
}
