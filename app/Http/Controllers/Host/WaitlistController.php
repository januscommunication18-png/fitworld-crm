<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\WaitlistEntry;
use App\Models\ClassPlan;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        $query = WaitlistEntry::where('host_id', $host->id)
            ->with(['classPlan', 'classRequest', 'client']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_plan_id')) {
            $query->where('class_plan_id', $request->class_plan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $entries = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Get counts by status
        $statusCounts = [
            'waiting' => WaitlistEntry::where('host_id', $host->id)->waiting()->count(),
            'offered' => WaitlistEntry::where('host_id', $host->id)->offered()->count(),
            'claimed' => WaitlistEntry::where('host_id', $host->id)->claimed()->count(),
            'expired' => WaitlistEntry::where('host_id', $host->id)->expired()->count(),
            'cancelled' => WaitlistEntry::where('host_id', $host->id)->cancelled()->count(),
        ];

        return view('host.waitlist.index', [
            'entries' => $entries,
            'statusCounts' => $statusCounts,
            'classPlans' => $host->classPlans()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => WaitlistEntry::getStatuses(),
            'currentStatus' => $request->status,
            'currentClassPlan' => $request->class_plan_id,
            'search' => $request->search,
        ]);
    }

    public function show(WaitlistEntry $waitlistEntry)
    {
        $this->authorizeEntry($waitlistEntry);

        $waitlistEntry->load(['classPlan', 'classRequest.helpdeskTicket', 'client']);

        return view('host.waitlist.show', [
            'entry' => $waitlistEntry,
        ]);
    }

    public function updateStatus(Request $request, WaitlistEntry $waitlistEntry)
    {
        $this->authorizeEntry($waitlistEntry);

        $validated = $request->validate([
            'status' => 'required|in:waiting,offered,claimed,expired,cancelled',
        ]);

        $waitlistEntry->status = $validated['status'];

        // Set timestamps based on status
        if ($validated['status'] === 'offered' && !$waitlistEntry->offered_at) {
            $waitlistEntry->offered_at = now();
            $waitlistEntry->expires_at = now()->addHours(24);
        } elseif ($validated['status'] === 'claimed' && !$waitlistEntry->claimed_at) {
            $waitlistEntry->claimed_at = now();
        }

        $waitlistEntry->save();

        return back()->with('success', 'Status updated successfully.');
    }

    public function offer(WaitlistEntry $waitlistEntry)
    {
        $this->authorizeEntry($waitlistEntry);

        if (!$waitlistEntry->isWaiting()) {
            return back()->with('error', 'This entry is not in waiting status.');
        }

        $waitlistEntry->markAsOffered(24); // 24 hours to claim

        return back()->with('success', 'Spot offered to ' . $waitlistEntry->full_name . '. They have 24 hours to claim it.');
    }

    public function cancel(WaitlistEntry $waitlistEntry)
    {
        $this->authorizeEntry($waitlistEntry);

        $waitlistEntry->markAsCancelled();

        return back()->with('success', 'Waitlist entry cancelled.');
    }

    public function destroy(WaitlistEntry $waitlistEntry)
    {
        $this->authorizeEntry($waitlistEntry);

        $waitlistEntry->delete();

        return redirect()
            ->route('waitlist.index')
            ->with('success', 'Waitlist entry deleted successfully.');
    }

    protected function authorizeEntry(WaitlistEntry $waitlistEntry): void
    {
        if ($waitlistEntry->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
