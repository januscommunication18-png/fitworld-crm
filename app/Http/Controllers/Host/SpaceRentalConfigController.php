<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\SpaceRentalConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpaceRentalConfigController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $type = $request->get('type');
        $status = $request->get('status');

        $configs = $host->spaceRentalConfigs()
            ->withCount('rentals')
            ->with(['location', 'room'])
            ->when($type, fn($q) => $q->where('rentable_type', $type))
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        $types = SpaceRentalConfig::getRentableTypes();

        return view('host.space-rentals.config.index', compact('configs', 'type', 'status', 'types'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $types = SpaceRentalConfig::getRentableTypes();
        $purposes = SpaceRentalConfig::getPurposes();
        $locations = $host->locations()->with('rooms')->get();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.space-rentals.config.create', compact(
            'types',
            'purposes',
            'locations',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(Request $request)
    {
        $host = auth()->user()->host;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rentable_type' => 'required|in:location,room',
            'location_id' => 'required_if:rentable_type,location|nullable|exists:locations,id',
            'room_id' => 'required_if:rentable_type,room|nullable|exists:rooms,id',
            'hourly_rates' => 'nullable|array',
            'hourly_rates.*' => 'nullable|numeric|min:0',
            'deposit_rates' => 'nullable|array',
            'deposit_rates.*' => 'nullable|numeric|min:0',
            'minimum_hours' => 'required|integer|min:1',
            'maximum_hours' => 'nullable|integer|min:1',
            'allowed_purposes' => 'nullable|array',
            'amenities_included' => 'nullable|array',
            'rules' => 'nullable|string',
            'setup_time_minutes' => 'nullable|integer|min:0',
            'cleanup_time_minutes' => 'nullable|integer|min:0',
            'requires_waiver' => 'boolean',
            'waiver_document' => 'nullable|file|mimes:pdf|max:5120',
            'is_active' => 'boolean',
        ]);

        // Handle boolean
        $data['is_active'] = $request->boolean('is_active', true);
        $data['requires_waiver'] = $request->boolean('requires_waiver', true);

        // Handle multi-currency rates
        if (isset($data['hourly_rates'])) {
            $data['hourly_rates'] = array_filter($data['hourly_rates'], fn($rate) => $rate !== null && $rate !== '');
        }

        if (isset($data['deposit_rates'])) {
            $data['deposit_rates'] = array_filter($data['deposit_rates'], fn($rate) => $rate !== null && $rate !== '');
        }

        // Set location_id from room if room type
        if ($data['rentable_type'] === 'room' && !empty($data['room_id'])) {
            $room = \App\Models\Room::find($data['room_id']);
            $data['location_id'] = $room?->location_id;
        }

        // Handle waiver document upload
        if ($request->hasFile('waiver_document')) {
            $path = $request->file('waiver_document')->store(
                $host->getStoragePath('waivers'),
                config('filesystems.uploads')
            );
            $data['waiver_document_path'] = $path;
        }

        $config = $host->spaceRentalConfigs()->create($data);

        return redirect()->route('space-rentals.config.index')
            ->with('success', 'Rentable space created successfully.');
    }

    public function show(Request $request, SpaceRentalConfig $config)
    {
        $this->authorizeHost($config);
        $config->load(['location', 'room', 'host']);

        $tab = $request->get('tab', 'overview');

        $recentRentals = $config->rentals()
            ->with(['client'])
            ->latest()
            ->take(10)
            ->get();

        $upcomingRentals = $config->rentals()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->with(['client'])
            ->take(5)
            ->get();

        // For schedule tab - get all rentals
        $allRentals = collect();
        $statuses = \App\Models\SpaceRental::getStatuses();

        if ($tab === 'schedule') {
            $allRentals = $config->rentals()
                ->with(['client'])
                ->orderBy('start_time', 'desc')
                ->get();
        }

        return view('host.space-rentals.config.show', compact(
            'config',
            'recentRentals',
            'upcomingRentals',
            'tab',
            'allRentals',
            'statuses'
        ));
    }

    public function edit(SpaceRentalConfig $config)
    {
        $this->authorizeHost($config);

        $host = auth()->user()->host;
        $types = SpaceRentalConfig::getRentableTypes();
        $purposes = SpaceRentalConfig::getPurposes();
        $locations = $host->locations()->with('rooms')->get();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.space-rentals.config.edit', compact(
            'config',
            'types',
            'purposes',
            'locations',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function update(Request $request, SpaceRentalConfig $config)
    {
        $this->authorizeHost($config);

        $host = auth()->user()->host;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rentable_type' => 'required|in:location,room',
            'location_id' => 'required_if:rentable_type,location|nullable|exists:locations,id',
            'room_id' => 'required_if:rentable_type,room|nullable|exists:rooms,id',
            'hourly_rates' => 'nullable|array',
            'hourly_rates.*' => 'nullable|numeric|min:0',
            'deposit_rates' => 'nullable|array',
            'deposit_rates.*' => 'nullable|numeric|min:0',
            'minimum_hours' => 'required|integer|min:1',
            'maximum_hours' => 'nullable|integer|min:1',
            'allowed_purposes' => 'nullable|array',
            'amenities_included' => 'nullable|array',
            'rules' => 'nullable|string',
            'setup_time_minutes' => 'nullable|integer|min:0',
            'cleanup_time_minutes' => 'nullable|integer|min:0',
            'requires_waiver' => 'boolean',
            'waiver_document' => 'nullable|file|mimes:pdf|max:5120',
            'is_active' => 'boolean',
        ]);

        // Handle boolean
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_waiver'] = $request->boolean('requires_waiver');

        // Handle multi-currency rates
        if (isset($data['hourly_rates'])) {
            $data['hourly_rates'] = array_filter($data['hourly_rates'], fn($rate) => $rate !== null && $rate !== '');
        }

        if (isset($data['deposit_rates'])) {
            $data['deposit_rates'] = array_filter($data['deposit_rates'], fn($rate) => $rate !== null && $rate !== '');
        }

        // Set location_id from room if room type
        if ($data['rentable_type'] === 'room' && !empty($data['room_id'])) {
            $room = \App\Models\Room::find($data['room_id']);
            $data['location_id'] = $room?->location_id;
        }

        // Handle waiver document upload
        if ($request->hasFile('waiver_document')) {
            // Delete old waiver
            if ($config->waiver_document_path) {
                Storage::disk(config('filesystems.uploads'))->delete($config->waiver_document_path);
            }

            $path = $request->file('waiver_document')->store(
                $host->getStoragePath('waivers'),
                config('filesystems.uploads')
            );
            $data['waiver_document_path'] = $path;
        }

        $config->update($data);

        return redirect()->route('space-rentals.config.index')
            ->with('success', 'Rentable space updated successfully.');
    }

    public function destroy(SpaceRentalConfig $config)
    {
        $this->authorizeHost($config);

        // Check for active rentals
        if ($config->rentals()->active()->exists()) {
            return back()->with('error', 'Cannot delete. Active rentals exist for this space.');
        }

        // Delete waiver document
        if ($config->waiver_document_path) {
            Storage::disk(config('filesystems.uploads'))->delete($config->waiver_document_path);
        }

        $config->delete();

        return redirect()->route('space-rentals.config.index')
            ->with('success', 'Rentable space deleted successfully.');
    }

    public function toggleStatus(SpaceRentalConfig $config)
    {
        $this->authorizeHost($config);

        $config->update(['is_active' => !$config->is_active]);

        return back()->with('success', 'Space rental status updated.');
    }

    private function authorizeHost(SpaceRentalConfig $config): void
    {
        if ($config->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
