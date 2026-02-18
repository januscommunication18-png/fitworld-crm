<?php

namespace App\Observers;

use App\Models\WaitlistEntry;
use App\Models\Client;
use App\Models\ClassRequest;

class WaitlistEntryObserver
{
    /**
     * Handle the WaitlistEntry "updated" event.
     */
    public function updated(WaitlistEntry $entry): void
    {
        // Only process if status changed to 'claimed'
        if (!$entry->wasChanged('status')) {
            return;
        }

        if ($entry->status !== WaitlistEntry::STATUS_CLAIMED) {
            return;
        }

        // Auto-convert linked Lead to Client
        $this->convertLeadToClient($entry);

        // Also mark the linked ClassRequest as booked if exists
        $this->markClassRequestAsBooked($entry);
    }

    /**
     * Convert the linked Lead to Client when waitlist entry is claimed
     */
    protected function convertLeadToClient(WaitlistEntry $entry): void
    {
        if (!$entry->client_id) {
            return;
        }

        $client = Client::find($entry->client_id);

        if (!$client) {
            return;
        }

        // Only convert if currently a lead
        if ($client->status === Client::STATUS_LEAD) {
            $client->update([
                'status' => Client::STATUS_CLIENT,
                'converted_at' => now(),
            ]);
        }
    }

    /**
     * Mark the linked ClassRequest as booked when waitlist entry is claimed
     */
    protected function markClassRequestAsBooked(WaitlistEntry $entry): void
    {
        if (!$entry->class_request_id) {
            return;
        }

        $classRequest = ClassRequest::find($entry->class_request_id);

        if (!$classRequest) {
            return;
        }

        // Only update if not already booked
        if ($classRequest->status !== ClassRequest::STATUS_BOOKED) {
            $classRequest->update([
                'status' => ClassRequest::STATUS_BOOKED,
            ]);
        }
    }
}
