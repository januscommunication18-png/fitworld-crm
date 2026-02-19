<?php

namespace App\Mail;

use App\Models\QuestionnaireResponse;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntakeFormRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public array $responses;

    /**
     * Create a new message instance.
     *
     * @param Transaction $transaction
     * @param QuestionnaireResponse[] $responses
     */
    public function __construct(Transaction $transaction, array $responses)
    {
        $this->transaction = $transaction;
        $this->responses = $responses;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $host = $this->transaction->host;

        return new Envelope(
            from: new Address(
                $host->email ?? config('mail.from.address'),
                $host->studio_name
            ),
            subject: "Please Complete Your Intake Form - {$host->studio_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $metadata = $this->transaction->metadata ?? [];

        // Build form data for the view
        $forms = [];
        foreach ($this->responses as $response) {
            $questionnaire = $response->version?->questionnaire;
            if ($questionnaire) {
                $forms[] = [
                    'name' => $questionnaire->title ?? 'Intake Form',
                    'url' => $response->getResponseUrl(),
                    'required' => true,
                ];
            }
        }

        return new Content(
            view: 'emails.intake-form-request',
            with: [
                'transaction' => $this->transaction,
                'host' => $this->transaction->host,
                'client' => $this->transaction->client,
                'forms' => $forms,
                'itemName' => $metadata['item_name'] ?? 'your booking',
                'itemDatetime' => $metadata['item_datetime'] ?? null,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
