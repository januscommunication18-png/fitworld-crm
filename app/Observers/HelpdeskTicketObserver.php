<?php

namespace App\Observers;

use App\Models\ClassRequest;
use App\Models\HelpdeskTicket;

class HelpdeskTicketObserver
{
    /**
     * Handle the HelpdeskTicket "updated" event.
     * Syncs status changes to linked ClassRequest.
     */
    public function updated(HelpdeskTicket $ticket): void
    {
        // Only process if status changed
        if (!$ticket->wasChanged('status')) {
            return;
        }

        // Find linked ClassRequest
        $classRequest = ClassRequest::where('helpdesk_ticket_id', $ticket->id)->first();

        if (!$classRequest) {
            return;
        }

        // Sync status based on helpdesk ticket status
        match ($ticket->status) {
            HelpdeskTicket::STATUS_IN_PROGRESS,
            HelpdeskTicket::STATUS_CUSTOMER_REPLY => $classRequest->markAsInDiscussion(),
            HelpdeskTicket::STATUS_RESOLVED => $classRequest->markAsNeedToConvert(),
            default => null,
        };
    }
}
