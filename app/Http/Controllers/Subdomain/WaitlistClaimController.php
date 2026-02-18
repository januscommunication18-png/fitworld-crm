<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;

class WaitlistClaimController extends Controller
{
    /**
     * Get the host from the request attributes (set by ResolveSubdomainHost middleware)
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Show the claim page for a waitlist offer
     */
    public function show(Request $request, string $subdomain, string $token)
    {
        $host = $this->getHost($request);

        $entry = WaitlistEntry::where('host_id', $host->id)
            ->where('claim_token', $token)
            ->where('status', WaitlistEntry::STATUS_OFFERED)
            ->first();

        if (!$entry) {
            return view('subdomain.waitlist-claim-invalid', [
                'host' => $host,
                'reason' => 'expired_or_invalid',
            ]);
        }

        // Check if offer has expired
        if ($entry->expires_at && $entry->expires_at->isPast()) {
            $entry->markAsExpired();
            return view('subdomain.waitlist-claim-invalid', [
                'host' => $host,
                'reason' => 'expired',
            ]);
        }

        $entry->load(['classPlan', 'classSession', 'classSession.primaryInstructor', 'classSession.location']);

        return view('subdomain.waitlist-claim', [
            'host' => $host,
            'entry' => $entry,
        ]);
    }

    /**
     * Process the waitlist claim
     */
    public function claim(Request $request, string $subdomain, string $token)
    {
        $host = $this->getHost($request);

        $entry = WaitlistEntry::where('host_id', $host->id)
            ->where('claim_token', $token)
            ->where('status', WaitlistEntry::STATUS_OFFERED)
            ->first();

        if (!$entry) {
            return redirect()->route('subdomain.waitlist-claim', [
                'subdomain' => $host->subdomain,
                'token' => $token,
            ])->with('error', 'This offer is no longer valid.');
        }

        // Check if offer has expired
        if ($entry->expires_at && $entry->expires_at->isPast()) {
            $entry->markAsExpired();
            return redirect()->route('subdomain.waitlist-claim', [
                'subdomain' => $host->subdomain,
                'token' => $token,
            ])->with('error', 'This offer has expired.');
        }

        // Mark as claimed
        $entry->markAsClaimed();

        return redirect()->route('subdomain.waitlist-claim.success', [
            'subdomain' => $host->subdomain,
            'token' => $token,
        ]);
    }

    /**
     * Show the success page after claiming
     */
    public function success(Request $request, string $subdomain, string $token)
    {
        $host = $this->getHost($request);

        $entry = WaitlistEntry::where('host_id', $host->id)
            ->where('claim_token', $token)
            ->where('status', WaitlistEntry::STATUS_CLAIMED)
            ->with(['classPlan', 'classSession'])
            ->first();

        return view('subdomain.waitlist-claim-success', [
            'host' => $host,
            'entry' => $entry,
        ]);
    }
}
