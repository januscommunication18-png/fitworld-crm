<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class HelpdeskTicket extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CUSTOMER_REPLY = 'customer_reply';
    const STATUS_RESOLVED = 'resolved';

    // Source type constants
    const SOURCE_BOOKING_REQUEST = 'booking_request';
    const SOURCE_GENERAL_INQUIRY = 'general_inquiry';
    const SOURCE_LEAD_MAGNET = 'lead_magnet';
    const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'host_id',
        'client_id',
        'source_type',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'service_plan_id',
        'preferred_date',
        'preferred_time',
        'status',
        'assigned_user_id',
        'source_url',
        'utm_params',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'preferred_time' => 'datetime',
            'utm_params' => 'array',
        ];
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HelpdeskMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(HelpdeskTag::class, 'helpdesk_ticket_tag', 'ticket_id', 'tag_id');
    }

    // Accessors

    public function getIsOpenAttribute(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::getSourceTypes()[$this->source_type] ?? $this->source_type;
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCustomerReply(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CUSTOMER_REPLY);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_RESOLVED);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBySource(Builder $query, string $sourceType): Builder
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_user_id');
    }

    public function scopeWithTag(Builder $query, $tagId): Builder
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('helpdesk_tags.id', $tagId);
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('subject', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
        });
    }

    // Methods

    public function markAsOpen(): void
    {
        $this->update(['status' => self::STATUS_OPEN]);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    public function markAsCustomerReply(): void
    {
        $this->update(['status' => self::STATUS_CUSTOMER_REPLY]);
    }

    public function markAsResolved(): void
    {
        $this->update(['status' => self::STATUS_RESOLVED]);
    }

    public function assignTo(?int $userId): void
    {
        $this->update([
            'assigned_user_id' => $userId,
            'status' => $userId ? self::STATUS_IN_PROGRESS : $this->status,
        ]);
    }

    public function addMessage(string $message, ?int $userId = null, string $senderType = 'staff'): HelpdeskMessage
    {
        $helpdeskMessage = $this->messages()->create([
            'user_id' => $userId,
            'sender_type' => $senderType,
            'message' => $message,
        ]);

        // Update ticket status based on sender
        if ($senderType === 'customer') {
            $this->markAsCustomerReply();
        } elseif ($senderType === 'staff' && $this->status === self::STATUS_CUSTOMER_REPLY) {
            $this->markAsInProgress();
        }

        return $helpdeskMessage;
    }

    public function convertToClient(): ?Client
    {
        // Check if client already exists
        if ($this->client_id) {
            return $this->client;
        }

        // Check if client exists with same email
        $existingClient = Client::where('host_id', $this->host_id)
            ->where('email', $this->email)
            ->first();

        if ($existingClient) {
            $this->update(['client_id' => $existingClient->id]);
            return $existingClient;
        }

        // Parse name into first/last
        $nameParts = explode(' ', $this->name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Create new client
        $client = Client::create([
            'host_id' => $this->host_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => Client::STATUS_LEAD,
            'lead_source' => $this->source_type === self::SOURCE_BOOKING_REQUEST
                ? Client::SOURCE_WEBSITE
                : Client::SOURCE_WEBSITE,
            'source_url' => $this->source_url,
            'utm_source' => $this->utm_params['source'] ?? null,
            'utm_medium' => $this->utm_params['medium'] ?? null,
            'utm_campaign' => $this->utm_params['campaign'] ?? null,
        ]);

        $this->update(['client_id' => $client->id]);

        return $client;
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_CUSTOMER_REPLY => 'Customer Reply',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    public static function getSourceTypes(): array
    {
        return [
            self::SOURCE_BOOKING_REQUEST => 'Booking Request',
            self::SOURCE_GENERAL_INQUIRY => 'General Inquiry',
            self::SOURCE_LEAD_MAGNET => 'Lead Magnet',
            self::SOURCE_MANUAL => 'Manual',
        ];
    }
}
