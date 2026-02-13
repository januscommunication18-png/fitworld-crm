<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    // Question type constants
    const TYPE_SHORT_TEXT = 'short_text';
    const TYPE_LONG_TEXT = 'long_text';
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_YES_NO = 'yes_no';
    const TYPE_SINGLE_SELECT = 'single_select';
    const TYPE_MULTI_SELECT = 'multi_select';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_DATE = 'date';
    const TYPE_NUMBER = 'number';
    const TYPE_ACKNOWLEDGEMENT = 'acknowledgement';

    // Visibility constants
    const VISIBILITY_CLIENT = 'client';
    const VISIBILITY_INSTRUCTOR_ONLY = 'instructor_only';

    protected $fillable = [
        'questionnaire_block_id',
        'question_key',
        'question_label',
        'question_type',
        'options',
        'is_required',
        'help_text',
        'placeholder',
        'default_value',
        'validation_rules',
        'visibility',
        'is_sensitive',
        'tags',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'validation_rules' => 'array',
            'tags' => 'array',
            'is_required' => 'boolean',
            'is_sensitive' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->question_key)) {
                $question->question_key = Str::slug($question->question_label, '_') . '_' . Str::random(4);
            }
        });
    }

    // Relationships

    public function block(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireBlock::class, 'questionnaire_block_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'question_id');
    }

    // Scopes

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('is_required', false);
    }

    public function scopeClientVisible(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_CLIENT);
    }

    public function scopeInstructorOnly(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_INSTRUCTOR_ONLY);
    }

    public function scopeSensitive(Builder $query): Builder
    {
        return $query->where('is_sensitive', true);
    }

    public function scopeNotSensitive(Builder $query): Builder
    {
        return $query->where('is_sensitive', false);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // Methods

    public function isClientVisible(): bool
    {
        return $this->visibility === self::VISIBILITY_CLIENT;
    }

    public function isInstructorOnly(): bool
    {
        return $this->visibility === self::VISIBILITY_INSTRUCTOR_ONLY;
    }

    public function hasOptions(): bool
    {
        return in_array($this->question_type, [
            self::TYPE_SINGLE_SELECT,
            self::TYPE_MULTI_SELECT,
            self::TYPE_DROPDOWN,
        ]);
    }

    public function isTextType(): bool
    {
        return in_array($this->question_type, [
            self::TYPE_SHORT_TEXT,
            self::TYPE_LONG_TEXT,
            self::TYPE_EMAIL,
            self::TYPE_PHONE,
        ]);
    }

    public function isBooleanType(): bool
    {
        return in_array($this->question_type, [
            self::TYPE_YES_NO,
            self::TYPE_ACKNOWLEDGEMENT,
        ]);
    }

    public function getInputType(): string
    {
        return match ($this->question_type) {
            self::TYPE_SHORT_TEXT => 'text',
            self::TYPE_LONG_TEXT => 'textarea',
            self::TYPE_EMAIL => 'email',
            self::TYPE_PHONE => 'tel',
            self::TYPE_NUMBER => 'number',
            self::TYPE_DATE => 'date',
            self::TYPE_YES_NO => 'toggle',
            self::TYPE_SINGLE_SELECT => 'radio',
            self::TYPE_MULTI_SELECT => 'checkbox',
            self::TYPE_DROPDOWN => 'select',
            self::TYPE_ACKNOWLEDGEMENT => 'checkbox',
            default => 'text',
        };
    }

    // Static helpers

    public static function getQuestionTypes(): array
    {
        return [
            self::TYPE_SHORT_TEXT => 'Short Text',
            self::TYPE_LONG_TEXT => 'Long Text',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_PHONE => 'Phone',
            self::TYPE_YES_NO => 'Yes/No',
            self::TYPE_SINGLE_SELECT => 'Single Select (Radio)',
            self::TYPE_MULTI_SELECT => 'Multi Select (Checkboxes)',
            self::TYPE_DROPDOWN => 'Dropdown',
            self::TYPE_DATE => 'Date',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_ACKNOWLEDGEMENT => 'Acknowledgement',
        ];
    }

    public static function getQuestionTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_SHORT_TEXT => 'icon-[tabler--text-size]',
            self::TYPE_LONG_TEXT => 'icon-[tabler--align-left]',
            self::TYPE_EMAIL => 'icon-[tabler--mail]',
            self::TYPE_PHONE => 'icon-[tabler--phone]',
            self::TYPE_YES_NO => 'icon-[tabler--toggle-left]',
            self::TYPE_SINGLE_SELECT => 'icon-[tabler--circle-dot]',
            self::TYPE_MULTI_SELECT => 'icon-[tabler--checkbox]',
            self::TYPE_DROPDOWN => 'icon-[tabler--selector]',
            self::TYPE_DATE => 'icon-[tabler--calendar]',
            self::TYPE_NUMBER => 'icon-[tabler--123]',
            self::TYPE_ACKNOWLEDGEMENT => 'icon-[tabler--square-check]',
            default => 'icon-[tabler--forms]',
        };
    }

    public static function getVisibilities(): array
    {
        return [
            self::VISIBILITY_CLIENT => 'Client Visible',
            self::VISIBILITY_INSTRUCTOR_ONLY => 'Instructor Only',
        ];
    }

    public static function getCommonTags(): array
    {
        return [
            'injury' => 'Injury/Health',
            'goals' => 'Goals',
            'experience' => 'Experience Level',
            'preferences' => 'Preferences',
            'emergency' => 'Emergency Contact',
            'medical' => 'Medical Information',
        ];
    }
}
