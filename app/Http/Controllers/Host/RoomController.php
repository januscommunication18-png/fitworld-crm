<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Available amenities list
     */
    private function getAmenities(): array
    {
        return [
            'mats' => 'Yoga Mats',
            'blocks' => 'Yoga Blocks',
            'straps' => 'Straps',
            'blankets' => 'Blankets',
            'bolsters' => 'Bolsters',
            'mirrors' => 'Mirrors',
            'sound_system' => 'Sound System',
            'climate_control' => 'Climate Control',
            'changing_rooms' => 'Changing Rooms',
            'showers' => 'Showers',
            'lockers' => 'Lockers',
            'water_fountain' => 'Water Fountain',
            'props' => 'Props Storage',
            'natural_light' => 'Natural Light',
            'private' => 'Private Room',
        ];
    }

    /**
     * Display rooms list page
     */
    public function index()
    {
        $host = auth()->user()->host;
        $locations = $host->locations()->with('rooms')->orderBy('name')->get();
        $rooms = Room::whereIn('location_id', $locations->pluck('id'))
            ->with('location')
            ->orderBy('name')
            ->get();

        return view('host.settings.locations.rooms-index', [
            'host' => $host,
            'locations' => $locations,
            'rooms' => $rooms,
            'amenitiesList' => $this->getAmenities(),
        ]);
    }

    /**
     * Show form to create a new room
     */
    public function create()
    {
        $host = auth()->user()->host;
        $locations = $host->locations()->orderBy('name')->get();

        if ($locations->isEmpty()) {
            return redirect()->route('settings.locations.index')
                ->with('error', 'Please add a location before creating rooms.');
        }

        return view('host.settings.locations.rooms-form', [
            'host' => $host,
            'room' => null,
            'locations' => $locations,
            'amenitiesList' => $this->getAmenities(),
            'isEdit' => false,
        ]);
    }

    /**
     * Show form to edit an existing room
     */
    public function edit(Room $room)
    {
        $host = auth()->user()->host;
        $locations = $host->locations()->orderBy('name')->get();

        // Verify ownership (room's location belongs to this host)
        if (!$locations->pluck('id')->contains($room->location_id)) {
            abort(403);
        }

        return view('host.settings.locations.rooms-form', [
            'host' => $host,
            'room' => $room,
            'locations' => $locations,
            'amenitiesList' => $this->getAmenities(),
            'isEdit' => true,
        ]);
    }

    /**
     * Store a new room
     */
    public function store(Request $request)
    {
        $host = auth()->user()->host;
        $locationIds = $host->locations()->pluck('id')->toArray();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id', function ($attribute, $value, $fail) use ($locationIds) {
                if (!in_array($value, $locationIds)) {
                    $fail('Invalid location selected.');
                }
            }],
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:500',
            'description' => 'nullable|string|max:1000',
            'dimensions' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
        ]);

        Room::create($validated);

        return redirect()->route('settings.locations.rooms')
            ->with('success', 'Room created successfully');
    }

    /**
     * Update a room
     */
    public function update(Request $request, Room $room)
    {
        $host = auth()->user()->host;
        $locationIds = $host->locations()->pluck('id')->toArray();

        // Verify ownership
        if (!in_array($room->location_id, $locationIds)) {
            abort(403);
        }

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id', function ($attribute, $value, $fail) use ($locationIds) {
                if (!in_array($value, $locationIds)) {
                    $fail('Invalid location selected.');
                }
            }],
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:500',
            'description' => 'nullable|string|max:1000',
            'dimensions' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
        ]);

        $room->update($validated);

        return redirect()->route('settings.locations.rooms')
            ->with('success', 'Room updated successfully');
    }

    /**
     * Delete a room
     */
    public function destroy(Room $room)
    {
        $host = auth()->user()->host;
        $locationIds = $host->locations()->pluck('id')->toArray();

        // Verify ownership
        if (!in_array($room->location_id, $locationIds)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // TODO: Check for scheduled classes before deleting

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully',
        ]);
    }

    /**
     * Toggle room active status
     */
    public function toggleStatus(Room $room)
    {
        $host = auth()->user()->host;
        $locationIds = $host->locations()->pluck('id')->toArray();

        // Verify ownership
        if (!in_array($room->location_id, $locationIds)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $room->update(['is_active' => !$room->is_active]);

        return response()->json([
            'success' => true,
            'message' => $room->is_active ? 'Room activated' : 'Room deactivated',
            'is_active' => $room->is_active,
        ]);
    }
}
