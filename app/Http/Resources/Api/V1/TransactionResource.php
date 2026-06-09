<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $types = Transaction::getTypes();
        $statuses = Transaction::getStatuses();
        $methods = Transaction::getPaymentMethods();

        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'type_label' => $types[$this->type] ?? $this->type,
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'payment_method' => $this->payment_method,
            'payment_label' => $methods[$this->payment_method] ?? $this->payment_method,
            'total_amount' => $this->total_amount !== null ? (float) $this->total_amount : null,
            'refunded_amount' => $this->refunded_amount !== null ? (float) $this->refunded_amount : null,
            'currency' => $this->currency,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'client' => $this->whenLoaded('client', fn () => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ] : null),
        ];
    }
}
