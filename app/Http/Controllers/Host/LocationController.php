<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        // Check location limit (max 5 - increased for different types)
        if ($host->locations()->count() >= 5) {
            return redirect()->route('settings.locations.index')
                ->with('error', 'Maximum of 5 locations allowed per studio.');
        }

        $countries = $this->getCountries();

        return view('host.settings.locations.form', [
            'host' => $host,
            'location' => new Location(['location_type' => Location::TYPE_IN_PERSON]),
            'countries' => $countries,
            'locationTypeOptions' => Location::getLocationTypeOptions(),
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
            'locationTypeOptions' => Location::getLocationTypeOptions(),
            'isEdit' => true,
        ]);
    }

    /**
     * Store a new location
     */
    public function store(Request $request)
    {
        $host = auth()->user()->host;

        // Check location limit
        if ($host->locations()->count() >= 5) {
            return redirect()->route('settings.locations.index')
                ->with('error', 'Maximum of 5 locations allowed per studio.');
        }

        $validated = $this->validateLocation($request);
        $validated['host_id'] = $host->id;

        // Set location_type from the first selected location_types
        $locationTypes = $validated['location_types'] ?? [];
        $validated['location_type'] = $locationTypes[0] ?? Location::TYPE_IN_PERSON;

        // Handle checkbox for virtual locations
        if (in_array(Location::TYPE_VIRTUAL, $locationTypes)) {
            $validated['hide_link_until_booking'] = $request->has('hide_link_until_booking');
        }

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

        $validated = $this->validateLocation($request, $location);

        // Set location_type from the first selected location_types
        $locationTypes = $validated['location_types'] ?? [];
        $validated['location_type'] = $locationTypes[0] ?? $location->location_type ?? Location::TYPE_IN_PERSON;

        // Handle checkbox for virtual locations
        if (in_array(Location::TYPE_VIRTUAL, $locationTypes)) {
            $validated['hide_link_until_booking'] = $request->has('hide_link_until_booking');
        }

        // Clear irrelevant fields based on selected types
        $hasInPerson = in_array(Location::TYPE_IN_PERSON, $locationTypes);
        $hasPublic = in_array(Location::TYPE_PUBLIC, $locationTypes);
        $hasVirtual = in_array(Location::TYPE_VIRTUAL, $locationTypes);
        $hasPhysical = $hasInPerson || $hasPublic;

        if (!$hasPhysical) {
            $validated['address_line_1'] = null;
            $validated['address_line_2'] = null;
            $validated['city'] = null;
            $validated['state'] = null;
            $validated['postal_code'] = null;
            $validated['country'] = null;
        }

        if (!$hasPublic) {
            $validated['public_location_notes'] = null;
        }

        if (!$hasVirtual) {
            $validated['virtual_platform'] = null;
            $validated['virtual_meeting_link'] = null;
            $validated['virtual_access_notes'] = null;
            $validated['hide_link_until_booking'] = true;
        }

        // Clear mobile fields if mobile type not selected
        $hasMobile = in_array(Location::TYPE_MOBILE, $locationTypes);
        if (!$hasMobile) {
            $validated['mobile_service_area'] = null;
            $validated['mobile_travel_notes'] = null;
        }

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

        // Check for assigned rooms (only for in-person locations)
        if ($location->isInPerson() && $location->rooms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location with assigned rooms. Please reassign or delete rooms first.',
                'rooms_count' => $location->rooms()->count(),
            ], 422);
        }

        // Check for scheduled classes
        if (!$location->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location with scheduled classes.',
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
     * Validate location based on selected types
     */
    private function validateLocation(Request $request, ?Location $location = null): array
    {
        $locationTypes = $request->input('location_types', []);
        $hasInPerson = in_array(Location::TYPE_IN_PERSON, $locationTypes);
        $hasPublic = in_array(Location::TYPE_PUBLIC, $locationTypes);
        $hasVirtual = in_array(Location::TYPE_VIRTUAL, $locationTypes);
        $hasPhysical = $hasInPerson || $hasPublic;

        // Base rules for all types
        $rules = [
            'name' => 'required|string|max:255',
            'location_types' => ['required', 'array', 'min:1'],
            'location_types.*' => ['string', Rule::in(array_keys(Location::getLocationTypeOptions()))],
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Physical location rules (in_person or public)
        if ($hasPhysical) {
            $rules += [
                'address_line_1' => $hasInPerson ? 'required|string|max:255' : 'nullable|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'postal_code' => $hasInPerson ? 'required|string|max:20' : 'nullable|string|max:20',
                'country' => 'required|string|size:2',
            ];
        } else {
            $rules += [
                'address_line_1' => 'nullable|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|size:2',
            ];
        }

        // Public location rules
        if ($hasPublic) {
            $rules['public_location_notes'] = 'required|string|max:2000';
        } else {
            $rules['public_location_notes'] = 'nullable|string|max:2000';
        }

        // Virtual location rules
        if ($hasVirtual) {
            $rules += [
                'virtual_platform' => ['required', Rule::in(array_keys(Location::getPlatformLabels()))],
                'virtual_meeting_link' => 'required|url|max:500',
                'virtual_access_notes' => 'nullable|string|max:1000',
            ];
        } else {
            $rules += [
                'virtual_platform' => ['nullable', Rule::in(array_keys(Location::getPlatformLabels()))],
                'virtual_meeting_link' => 'nullable|url|max:500',
                'virtual_access_notes' => 'nullable|string|max:1000',
            ];
        }

        // Mobile/Travel location rules
        $rules += [
            'mobile_service_area' => 'nullable|string|max:255',
            'mobile_travel_notes' => 'nullable|string|max:1000',
        ];

        return $request->validate($rules);
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
