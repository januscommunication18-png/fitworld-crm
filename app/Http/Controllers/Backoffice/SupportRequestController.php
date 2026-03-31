<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\TechnicalSupportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportRequestController extends Controller
{
    /**
     * Display listing of support requests.
     */
    public function index(Request $request): View
    {
        $query = TechnicalSupportRequest::with(['host', 'user', 'resolvedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $supportRequests = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => TechnicalSupportRequest::count(),
            'pending' => TechnicalSupportRequest::where('status', 'pending')->count(),
            'in_progress' => TechnicalSupportRequest::where('status', 'in_progress')->count(),
            'resolved' => TechnicalSupportRequest::where('status', 'resolved')->count(),
            'this_week' => TechnicalSupportRequest::where('created_at', '>=', now()->subWeek())->count(),
        ];

        return view('backoffice.support-requests.index', compact('supportRequests', 'stats'));
    }

    /**
     * Display a support request detail.
     */
    public function show(TechnicalSupportRequest $supportRequest): View
    {
        $supportRequest->load(['host', 'user', 'resolvedBy']);

        return view('backoffice.support-requests.show', compact('supportRequest'));
    }

    /**
     * Update support request status.
     */
    public function updateStatus(Request $request, TechnicalSupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,resolved'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $supportRequest->status = $validated['status'];

        if ($validated['status'] === 'resolved') {
            $supportRequest->resolved_at = now();
            $supportRequest->resolved_by = auth('admin')->id();
            $supportRequest->resolution_notes = $validated['resolution_notes'] ?? null;
        } elseif ($validated['status'] === 'in_progress') {
            // Clear resolution if moving back to in_progress
            $supportRequest->resolved_at = null;
            $supportRequest->resolved_by = null;
        }

        $supportRequest->save();

        return back()->with('success', 'Support request status updated.');
    }

    /**
     * Add a note to a support request.
     */
    public function addNote(Request $request, TechnicalSupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        // Append to existing metadata or create new
        $metadata = $supportRequest->metadata ?? [];
        $metadata['admin_notes'] = $metadata['admin_notes'] ?? [];
        $metadata['admin_notes'][] = [
            'note' => $validated['note'],
            'admin_id' => auth('admin')->id(),
            'admin_name' => auth('admin')->user()->full_name ?? 'Admin',
            'created_at' => now()->toIso8601String(),
        ];

        $supportRequest->metadata = $metadata;
        $supportRequest->save();

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * Delete a support request.
     */
    public function destroy(TechnicalSupportRequest $supportRequest): RedirectResponse
    {
        $supportRequest->delete();

        return redirect()
            ->route('backoffice.support-requests.index')
            ->with('success', 'Support request deleted.');
    }
}
