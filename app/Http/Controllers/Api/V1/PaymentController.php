<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mobile payments/transactions listing. Host resolved by `studio.context`.
 */
class PaymentController extends Controller
{
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');

        $query = Transaction::where('host_id', $host->id)
            ->with(['client'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return TransactionResource::collection(
            $query->paginate(25)->withQueryString()
        );
    }
}
