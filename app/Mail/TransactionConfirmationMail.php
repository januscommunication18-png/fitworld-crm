<?php

namespace App\Mail;

use App\Http\Controllers\Host\EmailTemplateController;
use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class TransactionConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesCustomTemplate;

    public Transaction $transaction;
    public ?Booking $booking;
    protected ?string $icsContent;
    protected ?string $pdfContent;
    public ?string $scheduleSelectionUrl;

    public function __construct(
        Transaction $transaction,
        ?Booking $booking = null,
        ?string $icsContent = null,
        ?string $pdfContent = null,
        ?string $scheduleSelectionUrl = null
    ) {
        $this->transaction = $transaction;
        $this->booking = $booking;
        $this->icsContent = $icsContent;
        $this->pdfContent = $pdfContent;
        $this->scheduleSelectionUrl = $scheduleSelectionUrl;
    }

    public function build()
    {
        $host = $this->transaction->host;
        $metadata = $this->transaction->metadata ?? [];
        $itemName = $metadata['item_name'] ?? 'Booking';
        $studioName = $host?->studio_name ?? config('app.name');

        $fromAddress = $host?->email ?: config('mail.from.address');
        if ($fromAddress) {
            $this->from($fromAddress, $studioName);
        }

        // Try a host-customized template first — picked by the booking's state.
        $templateKey = $this->resolveTemplateKey($metadata);
        $variables = $this->buildTemplateVariables();

        if ($host && $this->buildFromCustomTemplate($templateKey, $host, $variables)) {
            return $this;
        }

        // No customization saved: render the default subject + default body
        // through the same wrapInLayout helper so the email visually matches
        // what the host sees in the templates editor preview.
        $defaultBody = EmailTemplateController::getDefaultTemplateHtml($templateKey);
        if ($host && $defaultBody !== '') {
            $defaultSubjectTemplate = EmailTemplateController::getDefaultSubject($templateKey);
            return $this->renderTemplateStrings($defaultSubjectTemplate, $defaultBody, $host, $variables);
        }

        // Last-resort fallback to the legacy rich blade view (only reached if
        // the resolved template key has no default body registered).
        $subject = $this->defaultSubject($itemName, $studioName, $metadata);

        return $this->subject($subject)->view('emails.transaction-confirmation', [
            'transaction' => $this->transaction,
            'booking' => $this->booking,
            'host' => $host,
            'client' => $this->transaction->client,
            'isPaid' => $this->transaction->is_paid,
            'isManualPayment' => $this->transaction->payment_method === Transaction::METHOD_MANUAL,
            'isWaitlist' => $metadata['is_waitlist'] ?? false,
            'itemName' => $itemName,
            'itemDatetime' => $metadata['item_datetime'] ?? null,
            'itemInstructor' => $metadata['item_instructor'] ?? null,
            'itemLocation' => $metadata['item_location'] ?? null,
            'hasCalendarInvite' => !empty($this->icsContent),
            'scheduleSelectionUrl' => $this->scheduleSelectionUrl,
        ]);
    }

    /**
     * Pick which EmailTemplate key applies to this transaction's state.
     * Waitlist > pending payment > paid.
     */
    protected function resolveTemplateKey(array $metadata): string
    {
        if (!empty($metadata['is_waitlist'])) {
            return 'waitlist_confirmation';
        }
        if (!$this->transaction->is_paid) {
            return 'booking_received';
        }
        return 'booking_confirmation';
    }

    protected function defaultSubject(string $itemName, string $studioName, array $metadata): string
    {
        if (!empty($metadata['is_waitlist'])) {
            return "Waitlist Confirmed: {$itemName} - {$studioName}";
        }
        return $this->transaction->is_paid
            ? "Booking Confirmed: {$itemName} - {$studioName}"
            : "Booking Received: {$itemName} - {$studioName}";
    }

    /**
     * Build the variable map used to render custom EmailTemplate content.
     * Variables match the keys advertised in EmailTemplateController::getEditableTemplateKeys().
     */
    protected function buildTemplateVariables(): array
    {
        $tx = $this->transaction;
        $host = $tx->host;
        $client = $tx->client;
        $metadata = $tx->metadata ?? [];

        // Split datetime into date / time when possible. The stored
        // item_datetime is a free-form display string (e.g. "Mon, Jun 2 · 9:00 AM"),
        // so we just split on the bullet separator when present.
        $datetime = $metadata['item_datetime'] ?? '';
        $date = $datetime;
        $time = '';
        if ($datetime && str_contains($datetime, '·')) {
            [$date, $time] = array_map('trim', explode('·', $datetime, 2));
        }

        // For series bookings there isn't a single session — surface the date
        // range and session count instead so Date / Time aren't blank.
        $seriesInfo = $this->resolveSeriesInfo($metadata);
        if ($seriesInfo) {
            if (!$date) {
                $date = ($seriesInfo['start_date'] && $seriesInfo['end_date'])
                    ? $seriesInfo['start_date'] . ' – ' . $seriesInfo['end_date']
                    : ($seriesInfo['start_date'] ?? '');
            }
            if (!$time && !empty($seriesInfo['session_count'])) {
                $time = $seriesInfo['session_count'] . ' sessions';
            }
        }

        // Fall back the instructor / location to whatever we can derive when
        // nothing was captured at booking time (typically series bookings,
        // where they vary per session). For series we surface the instructor
        // and room of the first upcoming session of the plan; otherwise we
        // fall back to a plan-level instructor or the host's default location.
        $instructorName = $metadata['item_instructor'] ?? '';
        $locationName = $metadata['item_location'] ?? '';
        if (!$instructorName || !$locationName) {
            $plan = $this->resolveClassPlan($metadata);
            $firstSession = $plan ? $this->resolveFirstUpcomingSession($plan) : null;

            if (!$instructorName) {
                $instructorName = $firstSession?->primaryInstructor?->name
                    ?? ($plan?->instructors()->first()?->name ?? '');
            }
            if (!$locationName) {
                $locationName = $firstSession?->room?->location?->name
                    ?? ($host && method_exists($host, 'defaultLocation')
                        ? ($host->defaultLocation()?->name ?? '')
                        : '');
            }
        }

        // Service-plan purchases (a service "type" rather than a specific slot)
        // arrive without a date / time / instructor — the customer picks a
        // slot later, or the studio reaches out to schedule. Replace blank
        // values with a clear "To be scheduled" placeholder so the email
        // doesn't render half-empty rows.
        $isServicePlanWithoutSlot = ($tx->purchasable instanceof \App\Models\ServicePlan)
            && empty($metadata['service_slot_id']);
        if ($isServicePlanWithoutSlot) {
            if (!$date) {
                $date = 'To be scheduled';
            }
            if (!$time) {
                $time = 'Studio will reach out to confirm';
            }
            if (!$instructorName) {
                $instructorName = 'To be assigned';
            }
        }

        $bookingId = $this->booking?->id
            ? 'BK-' . $this->booking->id
            : ($tx->transaction_id ?: ('TX-' . $tx->id));

        $transactionIdDisplay = $tx->transaction_id ?: ('TX-' . $tx->id);

        return [
            'customer_name' => trim(($client?->first_name ?? '') . ' ' . ($client?->last_name ?? '')) ?: ($client?->email ?? ''),
            'customer_email' => $client?->email ?? '',
            'class_name' => $metadata['item_name'] ?? 'Booking',
            'class_date' => $date,
            'class_time' => $time,
            'instructor_name' => $instructorName,
            'location' => $locationName,
            'booking_id' => $bookingId,
            'transaction_id' => $transactionIdDisplay,
            'payment_method' => $tx->payment_method_label ?? '',
            'total_amount' => $tx->formatted_total ?? '',
            'payment_instructions' => $this->resolvePaymentInstructions(),
            'position_number' => $metadata['waitlist_position'] ?? '',
            'studio_name' => $host?->studio_name ?? '',
            'studio_phone' => $host?->phone ?? '',
            'studio_email' => $host?->email ?? '',
            'cancellation_policy' => ($host && method_exists($host, 'getPolicy'))
                ? ($host->getPolicy('house_rules') ?? '')
                : '',
        ];
    }

    /**
     * Resolve the ClassPlan tied to this transaction — checks the polymorphic
     * purchasable first, then the explicit class_plan_id captured in metadata.
     */
    protected function resolveClassPlan(array $metadata): ?\App\Models\ClassPlan
    {
        $plan = $this->transaction->purchasable;
        if ($plan instanceof \App\Models\ClassPlan) {
            return $plan;
        }
        if (!empty($metadata['class_plan_id'])) {
            return \App\Models\ClassPlan::find($metadata['class_plan_id']);
        }
        return null;
    }

    /**
     * Return the first upcoming published session of a class plan, scoped to
     * this transaction's host. The lookup window starts at the transaction's
     * created_at so old transactions still resolve a meaningful "first session"
     * relative to when the booking was made.
     */
    protected function resolveFirstUpcomingSession(\App\Models\ClassPlan $plan): ?\App\Models\ClassSession
    {
        $rangeStart = $this->transaction->created_at ?? now();
        return \App\Models\ClassSession::with(['primaryInstructor', 'room.location'])
            ->where('host_id', $this->transaction->host_id)
            ->where('class_plan_id', $plan->id)
            ->where('status', \App\Models\ClassSession::STATUS_PUBLISHED)
            ->where('start_time', '>=', $rangeStart)
            ->orderBy('start_time')
            ->first();
    }

    /**
     * Return series summary info if this transaction is a class_plan series
     * booking. Reads metadata first; if missing (older transactions) and the
     * purchasable is still a ClassPlan, computes the summary on the fly from
     * the plan's upcoming sessions so the email isn't blank.
     *
     * @return array{start_date: ?string, end_date: ?string, session_count: ?int}|null
     */
    protected function resolveSeriesInfo(array $metadata): ?array
    {
        $isSeries = ($metadata['class_booking_type'] ?? null) === 'series'
            || (is_string($metadata['item_name'] ?? null) && str_contains(strtolower($metadata['item_name']), 'series'));

        if (!$isSeries) {
            return null;
        }

        $summary = $metadata['series_summary'] ?? null;
        if (is_array($summary) && ($summary['start_date'] || $summary['session_count'])) {
            return [
                'start_date' => $summary['start_date'] ?? null,
                'end_date' => $summary['end_date'] ?? null,
                'session_count' => isset($summary['session_count']) ? (int) $summary['session_count'] : null,
            ];
        }

        // Backfill for transactions saved before series_summary was persisted
        // to metadata. We derive the range from the billing_period (e.g. "6 months")
        // and count the plan's upcoming published sessions in that window.
        $months = null;
        if (!empty($metadata['billing_period']) && preg_match('/(\d+)/', $metadata['billing_period'], $m)) {
            $months = (int) $m[1];
        } elseif (is_string($metadata['item_name'] ?? null) && preg_match('/(\d+)\s*Month/i', $metadata['item_name'], $m)) {
            $months = (int) $m[1];
        }

        $plan = $this->resolveClassPlan($metadata);
        if (!$months || !$plan) {
            return null;
        }

        $rangeStart = $this->transaction->created_at ?? now();
        $rangeEnd = $rangeStart->copy()->addMonths($months);
        $sessions = \App\Models\ClassSession::where('host_id', $this->transaction->host_id)
            ->where('class_plan_id', $plan->id)
            ->where('status', \App\Models\ClassSession::STATUS_PUBLISHED)
            ->whereBetween('start_time', [$rangeStart, $rangeEnd])
            ->orderBy('start_time')
            ->get(['start_time']);

        if ($sessions->isEmpty()) {
            return [
                'start_date' => $rangeStart->format('M j, Y'),
                'end_date' => $rangeEnd->format('M j, Y'),
                'session_count' => 0,
            ];
        }

        return [
            'start_date' => $sessions->first()->start_time?->format('M j, Y'),
            'end_date' => $sessions->last()->start_time?->format('M j, Y'),
            'session_count' => $sessions->count(),
        ];
    }

    protected function resolvePaymentInstructions(): string
    {
        $tx = $this->transaction;
        if ($tx->payment_method !== Transaction::METHOD_MANUAL || !$tx->manual_method || !$tx->host) {
            return '';
        }

        $instructions = app(\App\Services\TransactionService::class)
            ->getManualPaymentInstructions($tx->host, $tx->manual_method);

        return (string) ($instructions ?? '');
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->icsContent) {
            $metadata = $this->transaction->metadata ?? [];
            $itemName = $metadata['item_name'] ?? 'Booking';
            $filename = preg_replace('/[^a-zA-Z0-9\-]/', '-', $itemName) . '.ics';

            $attachments[] = Attachment::fromData(fn () => $this->icsContent, $filename)
                ->withMime('text/calendar');
        }

        if ($this->pdfContent) {
            $invoice = $this->transaction->invoice;
            $invoiceNumber = $invoice?->invoice_number ?? $this->transaction->transaction_id;

            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, $invoiceNumber . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
