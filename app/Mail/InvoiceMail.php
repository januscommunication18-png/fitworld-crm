<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    protected ?string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, ?string $pdfContent = null)
    {
        $this->invoice = $invoice;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $host = $this->invoice->host;
        $subject = $this->invoice->is_paid
            ? "Receipt from {$host->studio_name} - {$this->invoice->invoice_number}"
            : "Invoice from {$host->studio_name} - {$this->invoice->invoice_number}";

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
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'host' => $this->invoice->host,
                'client' => $this->invoice->client,
                'items' => $this->invoice->items,
                'isPaid' => $this->invoice->is_paid,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        if (!$this->pdfContent) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
