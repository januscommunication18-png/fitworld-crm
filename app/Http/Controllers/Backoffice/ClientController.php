<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\HostStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $search = $request->get('search');

        $query = Host::with(['owner', 'plan']);

        // Apply tab filters
        switch ($tab) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'active':
                $query->where('status', Host::STATUS_ACTIVE);
                break;
            case 'inactive':
                $query->where('status', Host::STATUS_INACTIVE);
                break;
            case 'pending':
                $query->where('status', Host::STATUS_PENDING_VERIFY);
                break;
            case 'suspended':
                $query->where('status', Host::STATUS_SUSPENDED);
                break;
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('studio_name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20);

        // Tab counts
        $counts = [
            'all' => Host::count(),
            'today' => Host::whereDate('created_at', Carbon::today())->count(),
            'active' => Host::where('status', Host::STATUS_ACTIVE)->count(),
            'inactive' => Host::where('status', Host::STATUS_INACTIVE)->count(),
            'pending' => Host::where('status', Host::STATUS_PENDING_VERIFY)->count(),
            'suspended' => Host::where('status', Host::STATUS_SUSPENDED)->count(),
        ];

        return view('backoffice.clients.index', compact('clients', 'tab', 'search', 'counts'));
    }

    public function show(Host $client)
    {
        $client->load(['owner', 'plan', 'users', 'statusHistory.adminUser']);

        // Get counts for tabs
        $counts = [
            'users' => $client->users()->count(),
            'instructors' => 0, // Will be implemented later
            'classes' => 0, // Will be implemented later
        ];

        return view('backoffice.clients.show', compact('client', 'counts'));
    }

    public function edit(Host $client)
    {
        $client->load(['owner', 'plan']);

        return view('backoffice.clients.edit', compact('client'));
    }

    public function update(Request $request, Host $client)
    {
        $validated = $request->validate([
            'studio_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:hosts,subdomain,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
        ]);

        $client->update($validated);

        return redirect()->route('backoffice.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function changeStatus(Request $request, Host $client)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Host::STATUS_ACTIVE,
                Host::STATUS_INACTIVE,
                Host::STATUS_PENDING_VERIFY,
                Host::STATUS_SUSPENDED,
            ]),
            'reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $client->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            $client->changeStatus(
                $newStatus,
                Auth::guard('admin')->id(),
                $validated['reason'] ?? null
            );
        }

        return redirect()->back()
            ->with('success', 'Client status changed to ' . str_replace('_', ' ', $newStatus) . '.');
    }

    public function resendVerification(Host $client)
    {
        // Resend verification email to the owner
        if ($client->owner && $client->owner->email) {
            $client->owner->sendEmailVerificationNotification();

            return redirect()->back()
                ->with('success', 'Verification email sent to ' . $client->owner->email);
        }

        return redirect()->back()
            ->with('error', 'No owner email found for this client.');
    }
}
