<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_VOID = 'void';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'host_id',
        'client_id',
        'transaction_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'issue_date',
        'due_date',
        'paid_at',
        'sent_at',
        'voided_at',
        'pdf_path',
        'billing_info',
        'notes',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
            'voided_at' => 'datetime',
            'billing_info' => 'array',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->host_id);
            }
            if (empty($invoice->issue_date)) {
                $invoice->issue_date = now()->toDateString();
            }
        });
    }

    /**
     * Generate a unique invoice number
     * Format: INV-{STUDIO_CODE}-{YYYYMM}-{SEQ}
     */
    public static function generateInvoiceNumber(int $hostId): string
    {
        $host = Host::find($hostId);
        $studioCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $host->subdomain ?? 'STUDIO'), 0, 7));
        $yearMonth = now()->format('Ym');

        // Get the next sequence number for this studio and month
        $lastInvoice = self::where('host_id', $hostId)
            ->where('invoice_number', 'like', "INV-{$studioCode}-{$yearMonth}-%")
            ->orderByDesc('invoice_number')
            ->first();

        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('INV-%s-%s-%05d', $studioCode, $yearMonth, $nextSeq);
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Accessors
     */
    public function getFormattedTotalAttribute(): string
    {
        $symbol = match ($this->currency) {
            'USD', 'CAD', 'AUD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
            default => '$',
        };
        return $symbol . number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getIsVoidAttribute(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'badge-success',
            self::STATUS_SENT => 'badge-warning',
            self::STATUS_DRAFT => 'badge-neutral',
            self::STATUS_VOID => 'badge-error',
            self::STATUS_REFUNDED => 'badge-info',
            default => 'badge-neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Mark invoice as sent
     */
    public function markSent(): self
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return $this->fresh();
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid(): self
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);

        return $this->fresh();
    }

    /**
     * Void the invoice
     */
    public function markVoid(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_VOID,
            'voided_at' => now(),
            'internal_notes' => $reason
                ? ($this->internal_notes ? $this->internal_notes . "\n" : '') . "Voided: {$reason}"
                : $this->internal_notes,
        ]);

        return $this->fresh();
    }

    /**
     * Set PDF path after generation
     */
    public function setPdfPath(string $path): self
    {
        $this->update(['pdf_path' => $path]);
        return $this->fresh();
    }

    /**
     * Calculate totals from items
     */
    public function recalculateTotals(): self
    {
        $subtotal = $this->items->sum('total_price');
        $tax = $this->items->sum('tax');
        $discount = $this->discount_amount ?? 0;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax - $discount,
        ]);

        return $this->fresh();
    }

    /**
     * Scopes
     */
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_VOID => 'Void',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }
}
