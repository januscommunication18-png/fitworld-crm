<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\ClassPack;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\Host;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MembershipPlan;
use App\Models\ServiceSlot;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Create an invoice from a transaction
     */
    public function createFromTransaction(Transaction $transaction): Invoice
    {
        $invoice = Invoice::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'transaction_id' => $transaction->id,
            'status' => $transaction->is_paid ? Invoice::STATUS_PAID : Invoice::STATUS_DRAFT,
            'subtotal' => $transaction->subtotal,
            'tax_amount' => $transaction->tax_amount,
            'discount_amount' => $transaction->discount_amount,
            'total' => $transaction->total_amount,
            'currency' => $transaction->currency,
            'issue_date' => now(),
            'due_date' => $transaction->is_paid ? now() : now()->addDays(7),
            'paid_at' => $transaction->paid_at,
            'billing_info' => $this->getBillingInfo($transaction->client),
            'notes' => $this->getInvoiceNotes($transaction),
        ]);

        // Create invoice item from the purchasable
        $this->createInvoiceItem($invoice, $transaction);

        return $invoice->fresh(['items', 'client', 'host']);
    }

    /**
     * Create invoice item from transaction's purchasable
     */
    protected function createInvoiceItem(Invoice $invoice, Transaction $transaction): void
    {
        $purchasable = $transaction->purchasable;
        $metadata = $transaction->metadata ?? [];

        $itemData = match (true) {
            $purchasable instanceof ClassSession => InvoiceItem::fromClassSession($purchasable, $transaction->subtotal),
            $purchasable instanceof ServiceSlot => InvoiceItem::fromServiceSlot($purchasable, $transaction->subtotal),
            $purchasable instanceof MembershipPlan => InvoiceItem::fromMembershipPlan($purchasable, $transaction->subtotal),
            $purchasable instanceof ClassPack => InvoiceItem::fromClassPack($purchasable, $transaction->subtotal),
            default => [
                'description' => $metadata['item_name'] ?? 'Booking',
                'quantity' => 1,
                'unit_price' => $transaction->subtotal,
                'discount' => 0,
                'tax' => 0,
            ],
        };

        $itemData['invoice_id'] = $invoice->id;
        $itemData['metadata'] = [
            'datetime' => $metadata['item_datetime'] ?? null,
            'instructor' => $metadata['item_instructor'] ?? null,
            'location' => $metadata['item_location'] ?? null,
        ];

        InvoiceItem::create($itemData);
    }

    /**
     * Get billing info from client
     */
    protected function getBillingInfo(Client $client): array
    {
        return [
            'name' => $client->full_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'city' => $client->city,
            'state' => $client->state,
            'postal_code' => $client->postal_code,
            'country' => $client->country,
        ];
    }

    /**
     * Get invoice notes based on transaction type
     */
    protected function getInvoiceNotes(Transaction $transaction): ?string
    {
        $metadata = $transaction->metadata ?? [];

        if (!empty($metadata['is_waitlist'])) {
            return 'Note: This booking is on the waitlist. You will be notified when a spot becomes available.';
        }

        return null;
    }

    /**
     * Generate PDF for an invoice
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['items', 'client', 'host', 'transaction']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'host' => $invoice->host,
            'client' => $invoice->client,
            'items' => $invoice->items,
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = sprintf(
            'invoices/%d/%s.pdf',
            $invoice->host_id,
            $invoice->invoice_number
        );

        // Store the PDF
        Storage::disk('local')->put($filename, $pdf->output());

        // Update invoice with PDF path
        $invoice->setPdfPath($filename);

        return $filename;
    }

    /**
     * Get PDF content for download/display
     */
    public function getPdfContent(Invoice $invoice): string
    {
        // If PDF exists on disk, return it
        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            return Storage::disk('local')->get($invoice->pdf_path);
        }

        // Otherwise generate on the fly
        $invoice->load(['items', 'client', 'host', 'transaction']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'host' => $invoice->host,
            'client' => $invoice->client,
            'items' => $invoice->items,
        ]);

        return $pdf->output();
    }

    /**
     * Stream PDF for browser display
     */
    public function streamPdf(Invoice $invoice)
    {
        $invoice->load(['items', 'client', 'host', 'transaction']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'host' => $invoice->host,
            'client' => $invoice->client,
            'items' => $invoice->items,
        ]);

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }

    /**
     * Download PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['items', 'client', 'host', 'transaction']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'host' => $invoice->host,
            'client' => $invoice->client,
            'items' => $invoice->items,
        ]);

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    /**
     * Send invoice email to client
     */
    public function sendInvoiceEmail(Invoice $invoice, bool $attachPdf = true): void
    {
        $invoice->load(['items', 'client', 'host', 'transaction']);

        if (!$invoice->client?->email) {
            return;
        }

        // Generate PDF if needed
        $pdfContent = null;
        if ($attachPdf) {
            $pdfContent = $this->getPdfContent($invoice);
        }

        Mail::to($invoice->client->email)
            ->send(new InvoiceMail($invoice, $pdfContent));

        // Mark as sent if it was draft
        if ($invoice->status === Invoice::STATUS_DRAFT) {
            $invoice->markSent();
        }
    }

    /**
     * Mark invoice as paid (and update transaction if linked)
     */
    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->markPaid();

        // Also update linked transaction if exists
        if ($invoice->transaction && !$invoice->transaction->is_paid) {
            $invoice->transaction->markPaid();
        }

        return $invoice->fresh();
    }

    /**
     * Void an invoice
     */
    public function voidInvoice(Invoice $invoice, ?string $reason = null): Invoice
    {
        $invoice->markVoid($reason);

        // Delete stored PDF if exists
        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            Storage::disk('local')->delete($invoice->pdf_path);
            $invoice->update(['pdf_path' => null]);
        }

        return $invoice->fresh();
    }

    /**
     * Get invoices for a client
     */
    public function getClientInvoices(Client $client, int $limit = 20)
    {
        return Invoice::forClient($client->id)
            ->with(['items', 'transaction'])
            ->orderByDesc('issue_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unpaid invoices for a host
     */
    public function getUnpaidInvoices(Host $host, int $limit = 50)
    {
        return Invoice::forHost($host->id)
            ->unpaid()
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get overdue invoices for a host
     */
    public function getOverdueInvoices(Host $host)
    {
        return Invoice::forHost($host->id)
            ->overdue()
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->get();
    }
}
