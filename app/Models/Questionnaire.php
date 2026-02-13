<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Questionnaire extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_SINGLE = 'single';
    const TYPE_WIZARD = 'wizard';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'host_id',
        'name',
        'description',
        'type',
        'status',
        'estimated_minutes',
        'intro_text',
        'thank_you_message',
        'allow_save_resume',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'allow_save_resume' => 'boolean',
            'estimated_minutes' => 'integer',
        ];
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuestionnaireVersion::class);
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(QuestionnaireVersion::class)
            ->where('status', QuestionnaireVersion::STATUS_ACTIVE);
    }

    public function draftVersion(): HasOne
    {
        return $this->hasOne(QuestionnaireVersion::class)
            ->where('status', QuestionnaireVersion::STATUS_DRAFT);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(QuestionnaireVersion::class)
            ->latestOfMany('version_number');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(QuestionnaireAttachment::class);
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

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

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeSinglePage(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SINGLE);
    }

    public function scopeWizard(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_WIZARD);
    }

    // Methods

    public function isWizard(): bool
    {
        return $this->type === self::TYPE_WIZARD;
    }

    public function isSinglePage(): bool
    {
        return $this->type === self::TYPE_SINGLE;
    }

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

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function archive(): bool
    {
        return $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function createNewVersion(?int $userId = null): QuestionnaireVersion
    {
        $latestVersionNumber = $this->versions()->max('version_number') ?? 0;

        return $this->versions()->create([
            'version_number' => $latestVersionNumber + 1,
            'status' => QuestionnaireVersion::STATUS_DRAFT,
            'created_by' => $userId,
        ]);
    }

    public function getOrCreateDraftVersion(?int $userId = null): QuestionnaireVersion
    {
        $draft = $this->draftVersion;

        if ($draft) {
            return $draft;
        }

        // Create new draft version
        $newVersion = $this->createNewVersion($userId);

        // If there's an active version, copy its content to the new draft
        $sourceVersion = $this->activeVersion ?? $this->latestVersion;
        if ($sourceVersion && $sourceVersion->id !== $newVersion->id) {
            $sourceVersion->copyContentTo($newVersion);
        }

        return $newVersion;
    }

    public function duplicate(?int $userId = null): self
    {
        $newQuestionnaire = $this->replicate(['status']);
        $newQuestionnaire->name = $this->name . ' (Copy)';
        $newQuestionnaire->status = self::STATUS_DRAFT;
        $newQuestionnaire->created_by = $userId;
        $newQuestionnaire->save();

        // Duplicate the active or latest version
        $sourceVersion = $this->activeVersion ?? $this->latestVersion;

        if ($sourceVersion) {
            $sourceVersion->duplicateTo($newQuestionnaire, $userId);
        }

        return $newQuestionnaire;
    }

    // Static helpers

    public static function getTypes(): array
    {
        return [
            self::TYPE_SINGLE => 'Single Page',
            self::TYPE_WIZARD => 'Multi-Step Wizard',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_SINGLE => 'icon-[tabler--file-text]',
            self::TYPE_WIZARD => 'icon-[tabler--list-numbers]',
            default => 'icon-[tabler--forms]',
        };
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_ARCHIVED => 'badge-neutral',
            default => 'badge',
        };
    }
}
