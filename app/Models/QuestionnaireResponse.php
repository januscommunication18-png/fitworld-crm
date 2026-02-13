<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'questionnaire_version_id',
        'host_id',
        'client_id',
        'booking_id',
        'token',
        'status',
        'current_step',
        'started_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($response) {
            if (empty($response->token)) {
                $response->token = Str::random(64);
            }
        });
    }

    // Relationships

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class, 'questionnaire_version_id');
    }

    public function questionnaire()
    {
        return $this->version?->questionnaire();
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class);
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForBooking(Builder $query, $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeByToken(Builder $query, string $token): Builder
    {
        return $query->where('token', $token);
    }

    // Methods

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isIncomplete(): bool
    {
        return !$this->isCompleted();
    }

    public function start(?string $ip = null, ?string $userAgent = null): bool
    {
        if ($this->isPending()) {
            return $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        }

        return false;
    }

    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(int $stepNumber): bool
    {
        return $this->update([
            'current_step' => $stepNumber,
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    public function saveAnswer(int $questionId, ?string $answer): QuestionnaireAnswer
    {
        return $this->answers()->updateOrCreate(
            ['question_id' => $questionId],
            ['answer' => $answer]
        );
    }

    public function getAnswerForQuestion(int $questionId): ?QuestionnaireAnswer
    {
        return $this->answers->firstWhere('question_id', $questionId);
    }

    public function getAnswerValueForQuestion(int $questionId): ?string
    {
        return $this->getAnswerForQuestion($questionId)?->answer;
    }

    public function getCompletionPercentage(): int
    {
        $totalQuestions = $this->version->getTotalQuestionCount();

        if ($totalQuestions === 0) {
            return 100;
        }

        $answeredQuestions = $this->answers->count();

        return (int) round(($answeredQuestions / $totalQuestions) * 100);
    }

    public function getUnansweredRequiredQuestions()
    {
        $answeredQuestionIds = $this->answers->pluck('question_id')->toArray();

        return $this->version->getAllQuestions()
            ->where('is_required', true)
            ->whereNotIn('id', $answeredQuestionIds);
    }

    public function hasAllRequiredAnswers(): bool
    {
        return $this->getUnansweredRequiredQuestions()->isEmpty();
    }

    public function getResponseUrl(): string
    {
        $host = $this->host;
        $subdomain = $host->subdomain ?? 'app';

        return config('app.url') . '/q/' . $this->token;
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_IN_PROGRESS => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            default => 'badge',
        };
    }

    public static function findByToken(string $token): ?self
    {
        return static::byToken($token)->first();
    }
}
