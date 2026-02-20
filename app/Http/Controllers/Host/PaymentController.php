<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\CustomerMembership;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get the current host
     */
    protected function getHost()
    {
        return Auth::user()->host;
    }

    /**
     * Show all transactions
     */
    public function transactions(Request $request)
    {
        $host = $this->getHost();
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        $query = Transaction::where('host_id', $host->id)
            ->with(['client', 'booking', 'purchasable', 'invoice'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $transactions = $query->paginate(20);

        // Get counts for tabs
        $counts = [
            'all' => Transaction::where('host_id', $host->id)->count(),
            'pending' => Transaction::where('host_id', $host->id)->pending()->count(),
            'paid' => Transaction::where('host_id', $host->id)->paid()->count(),
        ];

        return view('host.payments.transactions', [
            'transactions' => $transactions,
            'status' => $status,
            'type' => $type,
            'counts' => $counts,
        ]);
    }

    /**
     * Confirm a manual payment
     */
    public function confirmPayment(Request $request, Transaction $transaction)
    {
        $host = $this->getHost();
        $user = Auth::user();

        // Ensure transaction belongs to this host
        if ($transaction->host_id !== $host->id) {
            abort(403);
        }

        // Ensure transaction is pending
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'This transaction is not pending.');
        }

        // Store who confirmed the payment and notes
        $metadata = $transaction->metadata ?? [];
        $metadata['confirmed_by'] = $user->id;
        $metadata['confirmed_by_name'] = $user->full_name;
        $metadata['confirmed_at'] = now()->toISOString();
        $transaction->metadata = $metadata;

        // Add confirmation notes if provided
        $notes = $request->input('notes');
        if ($notes) {
            $existingNotes = $transaction->notes ?? '';
            $timestamp = now()->format('M j, Y g:i A');
            $newNote = "[{$timestamp}] Confirmed by {$user->full_name}: {$notes}";
            $transaction->notes = $existingNotes ? $existingNotes . "\n" . $newNote : $newNote;
        }

        $transaction->save();

        // Process the successful payment (this will create booking/membership/etc)
        $this->transactionService->processSuccessfulPayment($transaction);

        return back()->with('success', 'Payment confirmed successfully. ' . $this->getConfirmationMessage($transaction));
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(Request $request, Transaction $transaction)
    {
        $host = $this->getHost();
        $user = Auth::user();

        // Ensure transaction belongs to this host
        if ($transaction->host_id !== $host->id) {
            abort(403);
        }

        // Ensure transaction is pending
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'Only pending transactions can be cancelled.');
        }

        // Validate reason is provided
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $reason = $request->input('reason');

        // Store who cancelled the transaction in metadata
        $metadata = $transaction->metadata ?? [];
        $metadata['cancelled_by'] = $user->id;
        $metadata['cancelled_by_name'] = $user->full_name;
        $metadata['cancelled_at'] = now()->toISOString();
        $metadata['cancellation_reason'] = $reason;
        $transaction->metadata = $metadata;
        $transaction->save();

        // Mark as cancelled with formatted reason
        $formattedReason = "Cancelled by {$user->full_name}: {$reason}";
        $transaction->markCancelled($formattedReason);

        // Cancel associated booking if exists
        if ($transaction->booking) {
            $transaction->booking->cancel('Transaction cancelled', $reason, $user->id);
        }

        return back()->with('success', 'Transaction cancelled.');
    }

    /**
     * View transaction details
     */
    public function showTransaction(Transaction $transaction)
    {
        $host = $this->getHost();

        if ($transaction->host_id !== $host->id) {
            abort(403);
        }

        $transaction->load(['client', 'booking.bookable', 'purchasable', 'invoice']);

        return view('host.payments.transaction-detail', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Get confirmation message based on transaction type
     */
    protected function getConfirmationMessage(Transaction $transaction): string
    {
        return match ($transaction->type) {
            Transaction::TYPE_CLASS_BOOKING => 'Class booking confirmed.',
            Transaction::TYPE_SERVICE_BOOKING => 'Service booking confirmed.',
            Transaction::TYPE_MEMBERSHIP_PURCHASE => 'Membership activated.',
            Transaction::TYPE_CLASS_PACK_PURCHASE => 'Class pack activated.',
            default => '',
        };
    }

    /**
     * Toggle hide from books status for cash transactions
     */
    public function toggleHideFromBooks(Transaction $transaction)
    {
        $host = $this->getHost();
        $user = Auth::user();

        // Ensure transaction belongs to this host
        if ($transaction->host_id !== $host->id) {
            abort(403);
        }

        // Only allow hiding cash transactions
        if (!$transaction->canHideFromBooks()) {
            return back()->with('error', 'Only cash transactions can be hidden from books.');
        }

        // Toggle the hide status
        $transaction->toggleHideFromBooks($user->id);

        $message = $transaction->hide_from_books
            ? 'Transaction hidden from books.'
            : 'Transaction restored to books.';

        return back()->with('success', $message);
    }

    public function memberships()
    {
        $host = $this->getHost();

        $memberships = CustomerMembership::where('host_id', $host->id)
            ->with(['client', 'membershipPlan'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('host.payments.memberships', [
            'memberships' => $memberships,
        ]);
    }

    public function classPacks()
    {
        return view('host.payments.class-packs');
    }
}
