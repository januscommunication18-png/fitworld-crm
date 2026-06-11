<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\DigitalCheckinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigitalCheckinController extends Controller
{
    public function __construct(
        protected DigitalCheckinService $checkinService,
    ) {}

    /**
     * The staff "Digital Check-In" scanner page.
     */
    public function index(): View
    {
        $host = $this->host();

        if (! $host->getPolicy('enable_digital_checkin', true)) {
            abort(404);
        }

        return view('host.digital-checkin.index', [
            'host' => $host,
        ]);
    }

    /**
     * Resolve a scanned token (or a manually-picked client) to the client and
     * their eligible options. Auto-checks-in when exactly one option is ready.
     */
    public function resolve(Request $request): JsonResponse
    {
        $host = $this->host();
        $validated = $request->validate([
            'token' => 'nullable|string',
            'client_id' => 'nullable|integer',
        ]);

        $client = $this->resolveClient($host, $validated);
        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not recognized. Try a manual lookup.',
            ], 404);
        }

        $options = $this->checkinService->resolveEligibleOptions($host, $client, now());
        $eligible = array_values(array_filter($options, fn ($o) => $o['eligible']));

        $payload = [
            'success' => true,
            'client' => $this->clientPayload($client),
            'auto' => false,
            'options' => $options,
        ];

        // Auto check-in when exactly one option is eligible and in-window.
        if (count($eligible) === 1) {
            $result = $this->checkinService->checkIn($host, $client, $eligible[0], $request->user(), false);
            $payload['auto'] = $result['success'];
            $payload['checked_in'] = $result;
            // Re-resolve so the UI shows the now-checked-in state.
            $payload['options'] = $this->checkinService->resolveEligibleOptions($host, $client, now());
        }

        return response()->json($payload);
    }

    /**
     * Commit a check-in for a chosen option (chooser path / override).
     */
    public function confirm(Request $request): JsonResponse
    {
        $host = $this->host();
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'type' => 'required|string|in:class_booking,service_booking,membership',
            'ref_id' => 'required|integer',
            'override' => 'nullable|boolean',
        ]);

        $client = Client::forHost($host->id)->find($validated['client_id']);
        if (! $client) {
            return response()->json(['success' => false, 'message' => 'Client not found.'], 404);
        }

        // Honor override only when the studio policy allows it.
        $override = (bool) ($validated['override'] ?? false)
            && (bool) $host->getPolicy('allow_staff_override', true);

        $result = $this->checkinService->checkIn($host, $client, [
            'type' => $validated['type'],
            'ref_id' => $validated['ref_id'],
        ], $request->user(), $override);

        $result['options'] = $this->checkinService->resolveEligibleOptions($host, $client, now());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Manual client lookup (camera fallback).
     */
    public function searchClients(Request $request): JsonResponse
    {
        $host = $this->host();
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Client::forHost($host->id)
            ->active()
            ->search($q)
            ->limit(10)
            ->get()
            ->map(fn (Client $c) => $this->clientPayload($c));

        return response()->json(['results' => $results]);
    }

    private function resolveClient($host, array $validated): ?Client
    {
        if (! empty($validated['token'])) {
            return $this->checkinService->resolveByToken($host, $validated['token']);
        }

        if (! empty($validated['client_id'])) {
            return Client::forHost($host->id)->find($validated['client_id']);
        }

        return null;
    }

    private function clientPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'full_name' => $client->full_name,
            'email' => $client->email,
            'initials' => $client->initials,
            'avatar_url' => $client->avatar_url ?? null,
        ];
    }

    private function host()
    {
        return auth()->user()->currentHost() ?? auth()->user()->host;
    }
}
