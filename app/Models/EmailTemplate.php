<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_TRANSACTIONAL = 'transactional';
    const CATEGORY_MARKETING = 'marketing';

    protected $fillable = [
        'host_id',
        'category',
        'key',
        'name',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the host this template belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Scope to system templates (no host)
     */
    public function scopeSystem($query)
    {
        return $query->whereNull('host_id');
    }

    /**
     * Scope to templates for a specific host
     */
    public function scopeForHost($query, int $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Scope to active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this is a system template
     */
    public function isSystemTemplate(): bool
    {
        return $this->host_id === null;
    }

    /**
     * Render template with data
     */
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyHtml = str_replace($placeholder, $value, $bodyHtml);
            if ($bodyText) {
                $bodyText = str_replace($placeholder, $value, $bodyText);
            }
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }

    /**
     * Duplicate template, optionally for a different host
     */
    public function duplicate(?int $hostId = null): self
    {
        $newTemplate = $this->replicate();
        $newTemplate->host_id = $hostId;
        $newTemplate->is_default = false;
        $newTemplate->key = $this->key . '_copy_' . time();
        $newTemplate->name = $this->name . ' (Copy)';
        $newTemplate->save();

        return $newTemplate;
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SYSTEM => 'System',
            self::CATEGORY_TRANSACTIONAL => 'Transactional',
            self::CATEGORY_MARKETING => 'Marketing',
        ];
    }

    /**
     * Get common system template keys
     */
    public static function getSystemTemplateKeys(): array
    {
        return [
            'welcome' => 'Welcome Email',
            'email_verification' => 'Email Verification',
            'password_reset' => 'Password Reset',
            'team_invitation' => 'Team Invitation',
            'booking_confirmation' => 'Booking Confirmation',
            'booking_reminder' => 'Booking Reminder',
            'booking_cancellation' => 'Booking Cancellation',
            'payment_receipt' => 'Payment Receipt',
            'membership_welcome' => 'Membership Welcome',
            'membership_expiring' => 'Membership Expiring',
            'trial_ending' => 'Trial Ending',
            'win_back' => 'Win Back Campaign',
        ];
    }
}
