<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_response_id',
        'question_id',
        'answer',
    ];

    // Relationships

    public function response(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireResponse::class, 'questionnaire_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'question_id');
    }

    // Methods

    /**
     * Get the formatted answer value based on question type
     */
    public function getFormattedValueAttribute(): ?string
    {
        if ($this->answer === null) {
            return null;
        }

        $question = $this->question;

        if (!$question) {
            return $this->answer;
        }

        return match ($question->question_type) {
            QuestionnaireQuestion::TYPE_YES_NO => $this->formatYesNo(),
            QuestionnaireQuestion::TYPE_ACKNOWLEDGEMENT => $this->formatAcknowledgement(),
            QuestionnaireQuestion::TYPE_SINGLE_SELECT,
            QuestionnaireQuestion::TYPE_DROPDOWN => $this->formatSingleSelect(),
            QuestionnaireQuestion::TYPE_MULTI_SELECT => $this->formatMultiSelect(),
            QuestionnaireQuestion::TYPE_DATE => $this->formatDate(),
            default => $this->answer,
        };
    }

    protected function formatYesNo(): string
    {
        return $this->answer === '1' || $this->answer === 'true' || $this->answer === 'yes'
            ? 'Yes'
            : 'No';
    }

    protected function formatAcknowledgement(): string
    {
        return $this->answer === '1' || $this->answer === 'true'
            ? 'Acknowledged'
            : 'Not acknowledged';
    }

    protected function formatSingleSelect(): string
    {
        $options = $this->question->options ?? [];

        foreach ($options as $option) {
            if (isset($option['key']) && $option['key'] === $this->answer) {
                return $option['label'] ?? $this->answer;
            }
        }

        return $this->answer;
    }

    protected function formatMultiSelect(): string
    {
        $selectedKeys = json_decode($this->answer, true);

        if (!is_array($selectedKeys)) {
            return $this->answer;
        }

        $options = $this->question->options ?? [];
        $labels = [];

        foreach ($options as $option) {
            if (isset($option['key']) && in_array($option['key'], $selectedKeys)) {
                $labels[] = $option['label'] ?? $option['key'];
            }
        }

        return implode(', ', $labels);
    }

    protected function formatDate(): string
    {
        try {
            return \Carbon\Carbon::parse($this->answer)->format('M j, Y');
        } catch (\Exception $e) {
            return $this->answer;
        }
    }

    /**
     * Get the raw answer value (useful for multi-select as array)
     */
    public function getRawValueAttribute()
    {
        $question = $this->question;

        if (!$question) {
            return $this->answer;
        }

        if ($question->question_type === QuestionnaireQuestion::TYPE_MULTI_SELECT) {
            return json_decode($this->answer, true) ?? [];
        }

        if ($question->isBooleanType()) {
            return $this->answer === '1' || $this->answer === 'true' || $this->answer === 'yes';
        }

        if ($question->question_type === QuestionnaireQuestion::TYPE_NUMBER) {
            return is_numeric($this->answer) ? (float) $this->answer : null;
        }

        return $this->answer;
    }

    /**
     * Check if this answer is for a sensitive question
     */
    public function isSensitive(): bool
    {
        return $this->question?->is_sensitive ?? false;
    }

    /**
     * Check if this answer is for an instructor-only question
     */
    public function isInstructorOnly(): bool
    {
        return $this->question?->visibility === QuestionnaireQuestion::VISIBILITY_INSTRUCTOR_ONLY;
    }
}
