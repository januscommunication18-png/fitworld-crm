<?php

namespace App\Observers;

use App\Models\ClassRequest;
use App\Models\Client;

class ClassRequestObserver
{
    /**
     * Handle the ClassRequest "updated" event.
     */
    public function updated(ClassRequest $classRequest): void
    {
        // Only process if status changed to 'booked'
        if (!$classRequest->wasChanged('status')) {
            return;
        }

        if ($classRequest->status !== ClassRequest::STATUS_BOOKED) {
            return;
        }

        // Auto-convert linked Lead to Client
        $this->convertLeadToClient($classRequest);
    }

    /**
     * Convert the linked Lead to Client when request is booked
     */
    protected function convertLeadToClient(ClassRequest $classRequest): void
    {
        if (!$classRequest->client_id) {
            return;
        }

        $client = Client::find($classRequest->client_id);

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
}
