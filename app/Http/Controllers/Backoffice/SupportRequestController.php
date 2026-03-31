<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\Request;

class SupportRequestController extends Controller
{
    /**
     * Display a listing of all support requests.
     */
    public function index(Request $request)
    {
        $query = SupportRequest::with(['host', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $supportRequests = $query->paginate(20);

        // Stats
        $stats = [
            'total' => SupportRequest::count(),
            'pending' => SupportRequest::status(SupportRequest::STATUS_PENDING)->count(),
            'in_progress' => SupportRequest::status(SupportRequest::STATUS_IN_PROGRESS)->count(),
            'resolved' => SupportRequest::status(SupportRequest::STATUS_RESOLVED)->count(),
        ];

        return view('backoffice.support.index', [
            'supportRequests' => $supportRequests,
            'stats' => $stats,
            'currentStatus' => $request->status,
            'search' => $request->search,
        ]);
    }

    /**
     * Display a specific support request.
     */
    public function show(SupportRequest $supportRequest)
    {
        $supportRequest->load(['host', 'user']);

        return view('backoffice.support.show', [
            'supportRequest' => $supportRequest,
        ]);
    }

    /**
     * Update the status of a support request.
     */
    public function updateStatus(Request $request, SupportRequest $supportRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $supportRequest->status = $validated['status'];

        if (isset($validated['admin_notes'])) {
            $supportRequest->admin_notes = $validated['admin_notes'];
        }

        if ($validated['status'] === SupportRequest::STATUS_RESOLVED) {
            $supportRequest->resolved_at = now();
        }

        $supportRequest->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support request updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Support request updated successfully.');
    }

    /**
     * Delete a support request.
     */
    public function destroy(SupportRequest $supportRequest)
    {
        $supportRequest->delete();

        return redirect()->route('backoffice.support.index')
            ->with('success', 'Support request deleted successfully.');
    }
}
