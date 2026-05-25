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

        // Promote inactive clients to active when they engage by booking a class.
        if ($client->status === Client::STATUS_INACTIVE) {
            $client->update([
                'status' => Client::STATUS_ACTIVE,
                'converted_at' => now(),
            ]);
        }
    }
}
