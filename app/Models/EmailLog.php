<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public $timestamps = false;

    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';

    protected $fillable = [
        'host_id',
        'template_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'body_preview',
        'status',
        'provider',
        'provider_message_id',
        'error_message',
        'sent_at',
        'opened_at',
        'clicked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'created_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the host
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Get the template used
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Scope to logs for a specific host
     */
    public function scopeForHost($query, ?int $hostId)
    {
        if ($hostId) {
            return $query->where('host_id', $hostId);
        }
        return $query;
    }

    /**
     * Scope to logs by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to logs within date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(?string $providerId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'provider_message_id' => $providerId,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_BOUNCED => 'Bounced',
        ];
    }

    /**
     * Create a log entry for an email
     */
    public static function logEmail(
        string $recipientEmail,
        string $subject,
        string $bodyPreview,
        ?int $hostId = null,
        ?int $templateId = null,
        ?string $recipientName = null,
        ?string $provider = null
    ): self {
        return self::create([
            'host_id' => $hostId,
            'template_id' => $templateId,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'body_preview' => substr($bodyPreview, 0, 500),
            'status' => self::STATUS_QUEUED,
            'provider' => $provider,
        ]);
    }
}
