<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientMeasurementController extends Controller
{
    protected function getHost()
    {
        $host = Auth::user()->currentHost() ?? Auth::user()->host;

        if (!$host) {
            abort(403, 'No studio access. Please select a studio.');
        }

        return $host;
    }

    /**
     * Store a new measurement for a client.
     */
    public function store(Request $request, int $clientId)
    {
        $client = Client::findOrFail($clientId);
        $this->authorizeClient($client);

        $host = $this->getHost();

        $validated = $request->validate([
            'measured_at' => ['required', 'date'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'weight_unit' => ['nullable', 'in:kg,lbs'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'chest' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'shoulders' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'neck' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'biceps_left' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'biceps_right' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'thigh_left' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'thigh_right' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'calf_left' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'calf_right' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'measurement_unit' => ['nullable', 'in:cm,in'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $measurement = ClientMeasurement::create([
            'host_id' => $host->id,
            'client_id' => $client->id,
            'recorded_by_user_id' => Auth::id(),
            'measured_at' => $validated['measured_at'],
            'weight' => $validated['weight'] ?? null,
            'weight_unit' => $validated['weight_unit'] ?? 'kg',
            'body_fat' => $validated['body_fat'] ?? null,
            'chest' => $validated['chest'] ?? null,
            'waist' => $validated['waist'] ?? null,
            'hips' => $validated['hips'] ?? null,
            'shoulders' => $validated['shoulders'] ?? null,
            'neck' => $validated['neck'] ?? null,
            'biceps_left' => $validated['biceps_left'] ?? null,
            'biceps_right' => $validated['biceps_right'] ?? null,
            'thigh_left' => $validated['thigh_left'] ?? null,
            'thigh_right' => $validated['thigh_right'] ?? null,
            'calf_left' => $validated['calf_left'] ?? null,
            'calf_right' => $validated['calf_right'] ?? null,
            'measurement_unit' => $validated['measurement_unit'] ?? 'cm',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Measurement recorded successfully.',
                'measurement' => $measurement->load('recordedBy'),
            ]);
        }

        return redirect()->route('clients.show', ['id' => $client->id, 'tab' => 'progress'])
            ->with('success', 'Measurement recorded successfully.');
    }

    /**
     * Get measurement details as JSON.
     */
    public function show(int $clientId, int $measurementId)
    {
        $client = Client::findOrFail($clientId);
        $this->authorizeClient($client);

        $measurement = ClientMeasurement::where('client_id', $client->id)
            ->findOrFail($measurementId);

        $measurement->load('recordedBy');

        // Get changes from previous measurement
        $changes = [];
        $fields = ClientMeasurement::getMeasurementFields();
        foreach (array_keys($fields) as $field) {
            $change = $measurement->getChangeFromPrevious($field);
            if ($change !== null) {
                $changes[$field] = $change;
            }
        }

        return response()->json([
            'measurement' => $measurement,
            'changes' => $changes,
        ]);
    }

    /**
     * Update a measurement.
     */
    public function update(Request $request, int $clientId, int $measurementId)
    {
        $client = Client::findOrFail($clientId);
        $this->authorizeClient($client);

        $measurement = ClientMeasurement::where('client_id', $client->id)
            ->findOrFail($measurementId);

        $validated = $request->validate([
            'measured_at' => ['required', 'date'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'weight_unit' => ['nullable', 'in:kg,lbs'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'chest' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'shoulders' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'neck' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'biceps_left' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'biceps_right' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'thigh_left' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'thigh_right' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'calf_left' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'calf_right' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'measurement_unit' => ['nullable', 'in:cm,in'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $measurement->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Measurement updated successfully.',
                'measurement' => $measurement->fresh()->load('recordedBy'),
            ]);
        }

        return redirect()->route('clients.show', ['id' => $client->id, 'tab' => 'progress'])
            ->with('success', 'Measurement updated successfully.');
    }

    /**
     * Delete a measurement.
     */
    public function destroy(Request $request, int $clientId, int $measurementId)
    {
        $client = Client::findOrFail($clientId);
        $this->authorizeClient($client);

        $measurement = ClientMeasurement::where('client_id', $client->id)
            ->findOrFail($measurementId);

        $measurement->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Measurement deleted successfully.',
            ]);
        }

        return redirect()->route('clients.show', ['id' => $client->id, 'tab' => 'progress'])
            ->with('success', 'Measurement deleted successfully.');
    }

    /**
     * Get chart data for measurements.
     */
    public function chartData(int $clientId, Request $request)
    {
        $client = Client::findOrFail($clientId);
        $this->authorizeClient($client);

        $field = $request->get('field', 'weight');
        $limit = $request->get('limit', 12);

        $measurements = $client->measurements()
            ->whereNotNull($field)
            ->orderBy('measured_at', 'asc')
            ->take($limit)
            ->get(['measured_at', $field]);

        return response()->json([
            'labels' => $measurements->map(fn($m) => $m->measured_at->format('M j'))->values(),
            'data' => $measurements->map(fn($m) => $m->$field)->values(),
        ]);
    }

    /**
     * Authorize that the client belongs to the current host.
     */
    protected function authorizeClient(Client $client): void
    {
        $host = $this->getHost();

        if ($client->host_id !== $host->id) {
            abort(403, 'This client belongs to a different studio.');
        }
    }
}
