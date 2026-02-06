<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display locations list page
     */
    public function index()
    {
        $host = auth()->user()->host;
        $locations = $host->locations()->withCount('rooms')->orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('host.settings.locations.index', compact('host', 'locations'));
    }

    /**
     * Show form to create a new location
     */
    public function create()
    {
        $host = auth()->user()->host;

        // Check location limit (max 3)
        if ($host->locations()->count() >= 3) {
            return redirect()->route('settings.locations.index')
                ->with('error', 'Maximum of 3 locations allowed per studio.');
        }

        $countries = $this->getCountries();

        return view('host.settings.locations.form', [
            'host' => $host,
            'location' => null,
            'countries' => $countries,
            'isEdit' => false,
        ]);
    }

    /**
     * Show form to edit an existing location
     */
    public function edit(Location $location)
    {
        $host = auth()->user()->host;

        // Verify ownership
        if ($location->host_id !== $host->id) {
            abort(403);
        }

        $countries = $this->getCountries();

        return view('host.settings.locations.form', [
            'host' => $host,
            'location' => $location,
            'countries' => $countries,
            'isEdit' => true,
        ]);
    }

    /**
     * Store a new location
     */
    public function store(Request $request)
    {
        $host = auth()->user()->host;

        // Check location limit (max 3)
        if ($host->locations()->count() >= 3) {
            return redirect()->route('settings.locations.index')
                ->with('error', 'Maximum of 3 locations allowed per studio.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['host_id'] = $host->id;

        // If this is the first location, make it default
        if ($host->locations()->count() === 0) {
            $validated['is_default'] = true;
        }

        Location::create($validated);

        return redirect()->route('settings.locations.index')
            ->with('success', 'Location added successfully');
    }

    /**
     * Update a location
     */
    public function update(Request $request, Location $location)
    {
        $host = auth()->user()->host;

        // Verify ownership
        if ($location->host_id !== $host->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $location->update($validated);

        return redirect()->route('settings.locations.index')
            ->with('success', 'Location updated successfully');
    }

    /**
     * Delete a location
     */
    public function destroy(Location $location)
    {
        $host = auth()->user()->host;

        // Verify ownership
        if ($location->host_id !== $host->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Must have at least one location
        if ($host->locations()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only location. A studio must have at least one location.',
            ], 422);
        }

        // Check for assigned rooms
        if ($location->rooms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location with assigned rooms. Please reassign or delete rooms first.',
                'rooms_count' => $location->rooms()->count(),
            ], 422);
        }

        // If deleting default, set another as default
        $wasDefault = $location->is_default;
        $location->delete();

        if ($wasDefault) {
            $newDefault = $host->locations()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully',
        ]);
    }

    /**
     * Set a location as default
     */
    public function setDefault(Location $location)
    {
        $host = auth()->user()->host;

        // Verify ownership
        if ($location->host_id !== $host->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Remove default from all other locations
        $host->locations()->update(['is_default' => false]);

        // Set this one as default
        $location->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default location updated',
        ]);
    }

    /**
     * Get countries list
     */
    private function getCountries(): array
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'IE' => 'Ireland',
        ];
    }
}
