<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public ?Booking $booking;
    protected ?string $icsContent;
    protected ?string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Transaction $transaction,
        ?Booking $booking = null,
        ?string $icsContent = null,
        ?string $pdfContent = null
    ) {
        $this->transaction = $transaction;
        $this->booking = $booking;
        $this->icsContent = $icsContent;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $host = $this->transaction->host;
        $metadata = $this->transaction->metadata ?? [];
        $itemName = $metadata['item_name'] ?? 'Booking';

        $subject = $this->transaction->is_paid
            ? "Booking Confirmed: {$itemName} - {$host->studio_name}"
            : "Booking Received: {$itemName} - {$host->studio_name}";

        // Add waitlist indicator
        if (!empty($metadata['is_waitlist'])) {
            $subject = "Waitlist Confirmed: {$itemName} - {$host->studio_name}";
        }

        return new Envelope(
            from: new Address(
                $host->email ?? config('mail.from.address'),
                $host->studio_name
            ),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $metadata = $this->transaction->metadata ?? [];

        return new Content(
            view: 'emails.transaction-confirmation',
            with: [
                'transaction' => $this->transaction,
                'booking' => $this->booking,
                'host' => $this->transaction->host,
                'client' => $this->transaction->client,
                'isPaid' => $this->transaction->is_paid,
                'isManualPayment' => $this->transaction->payment_method === Transaction::METHOD_MANUAL,
                'isWaitlist' => $metadata['is_waitlist'] ?? false,
                'itemName' => $metadata['item_name'] ?? 'Booking',
                'itemDatetime' => $metadata['item_datetime'] ?? null,
                'itemInstructor' => $metadata['item_instructor'] ?? null,
                'itemLocation' => $metadata['item_location'] ?? null,
                'hasCalendarInvite' => !empty($this->icsContent),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        // Calendar invite (.ics)
        if ($this->icsContent) {
            $metadata = $this->transaction->metadata ?? [];
            $itemName = $metadata['item_name'] ?? 'Booking';
            $filename = preg_replace('/[^a-zA-Z0-9\-]/', '-', $itemName) . '.ics';

            $attachments[] = Attachment::fromData(fn () => $this->icsContent, $filename)
                ->withMime('text/calendar');
        }

        // Invoice PDF
        if ($this->pdfContent) {
            $invoice = $this->transaction->invoice;
            $invoiceNumber = $invoice?->invoice_number ?? $this->transaction->transaction_id;

            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, $invoiceNumber . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
