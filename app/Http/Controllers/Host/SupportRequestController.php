<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportRequestController extends Controller
{
    /**
     * Display a listing of support requests for the current host.
     */
    public function index()
    {
        $host = Auth::user()->currentHost();

        $supportRequests = SupportRequest::forHost($host->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('host.support.index', [
            'supportRequests' => $supportRequests,
        ]);
    }

    /**
     * Store a new support request.
     */
    public function store(Request $request)
    {
        $host = Auth::user()->currentHost();

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'note' => 'required|string|max:2000',
        ]);

        $supportRequest = SupportRequest::create([
            'host_id' => $host->id,
            'user_id' => Auth::id(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'note' => $validated['note'],
            'status' => SupportRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support request submitted successfully! We will get back to you within 24 hours.',
            'data' => [
                'id' => $supportRequest->id,
            ],
        ]);
    }

    /**
     * Display a specific support request.
     */
    public function show(SupportRequest $supportRequest)
    {
        $host = Auth::user()->currentHost();

        // Ensure the request belongs to the current host
        if ($supportRequest->host_id !== $host->id) {
            abort(403);
        }

        return view('host.support.show', [
            'supportRequest' => $supportRequest,
        ]);
    }
}
