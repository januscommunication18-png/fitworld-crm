<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Services\SpaceRentalService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SpaceRentalController extends Controller
{
    public function __construct(
        protected SpaceRentalService $spaceRentalService
    ) {}

    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $status = $request->get('status');
        $configId = $request->get('config_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $rentals = $host->spaceRentals()
            ->with(['config.location', 'config.room', 'client'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($configId, fn($q) => $q->where('space_rental_config_id', $configId))
            ->when($dateFrom, fn($q) => $q->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay()))
            ->when($dateTo, fn($q) => $q->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay()))
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        $configs = $host->spaceRentalConfigs()->active()->orderBy('name')->get();
        $statuses = SpaceRental::getStatuses();

        return view('host.space-rentals.index', compact('rentals', 'configs', 'statuses', 'status', 'configId'));
    }

    public function create(Request $request)
    {
        $host = auth()->user()->host;
        $configs = $host->spaceRentalConfigs()->active()->with(['location', 'room', 'host'])->orderBy('name')->get();
        $clients = $host->clients()->orderBy('updated_at', 'desc')->take(10)->get();
        $purposes = SpaceRentalConfig::getPurposes();

        // Pre-select config if provided
        $selectedConfigId = $request->get('config_id');
        $selectedConfig = $selectedConfigId ? $configs->find($selectedConfigId) : null;

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.space-rentals.create', compact(
            'configs',
            'clients',
            'purposes',
            'selectedConfigId',
            'selectedConfig',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(Request $request)
    {
        $host = auth()->user()->host;

        $data = $request->validate([
            'space_rental_config_id' => 'required|exists:space_rental_configs,id',
            'client_type' => 'required|in:existing,external',
            'client_id' => 'required_if:client_type,existing|nullable|exists:clients,id',
            'external_client_name' => 'required_if:client_type,external|nullable|string|max:255',
            'external_client_email' => 'nullable|email|max:255',
            'external_client_phone' => 'nullable|string|max:50',
            'external_client_company' => 'nullable|string|max:255',
            'purpose' => 'required|in:photo_shoot,video_production,workshop,training,other',
            'purpose_notes' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'internal_notes' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,confirmed',
        ]);

        // Verify config belongs to host
        $config = SpaceRentalConfig::where('host_id', $host->id)->findOrFail($data['space_rental_config_id']);

        // Build datetime from date and time
        $date = Carbon::parse($data['date']);
        $startTime = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $endTime = Carbon::parse($data['date'] . ' ' . $data['end_time']);

        // Calculate hours
        $hours = $startTime->diffInMinutes($endTime) / 60;

        // Validate minimum hours
        if ($hours < $config->minimum_hours) {
            return back()->withErrors(['end_time' => "Minimum booking is {$config->minimum_hours} hours."])->withInput();
        }

        // Validate maximum hours if set
        if ($config->maximum_hours && $hours > $config->maximum_hours) {
            return back()->withErrors(['end_time' => "Maximum booking is {$config->maximum_hours} hours."])->withInput();
        }

        // Check for conflicts
        $conflicts = $this->spaceRentalService->checkConflicts($config, $startTime, $endTime);
        if (!empty($conflicts)) {
            $conflictChecker = app(\App\Services\Schedule\SpaceRentalConflictChecker::class);
            $message = $conflictChecker->formatConflictMessage($conflicts);
            return back()->withErrors(['date' => $message])->withInput();
        }

        // Prepare rental data
        $rentalData = [
            'space_rental_config_id' => $config->id,
            'purpose' => $data['purpose'],
            'purpose_notes' => $data['purpose_notes'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours_booked' => $hours,
            'internal_notes' => $data['internal_notes'],
            'status' => $data['status'] ?? SpaceRental::STATUS_DRAFT,
        ];

        if ($data['client_type'] === 'existing') {
            $rentalData['client_id'] = $data['client_id'];
        } else {
            $rentalData['external_client_name'] = $data['external_client_name'];
            $rentalData['external_client_email'] = $data['external_client_email'];
            $rentalData['external_client_phone'] = $data['external_client_phone'];
            $rentalData['external_client_company'] = $data['external_client_company'];
        }

        $rental = $this->spaceRentalService->createRental($rentalData, auth()->user());

        return redirect()->route('space-rentals.show', $rental)
            ->with('success', 'Space rental created successfully.');
    }

    public function show(SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        $rental->load(['config.location', 'config.room', 'client', 'createdBy', 'confirmedBy', 'completedBy', 'cancelledBy', 'statusLogs.updatedByUser']);

        return view('host.space-rentals.show', compact('rental'));
    }

    public function edit(SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        // Can only edit draft or pending rentals
        if (!in_array($rental->status, [SpaceRental::STATUS_DRAFT, SpaceRental::STATUS_PENDING])) {
            return back()->with('error', 'Cannot edit rental in current status.');
        }

        $host = auth()->user()->host;
        $configs = $host->spaceRentalConfigs()->active()->with(['location', 'room'])->orderBy('name')->get();
        $clients = $host->clients()->orderBy('first_name')->get();
        $purposes = SpaceRentalConfig::getPurposes();

        return view('host.space-rentals.edit', compact('rental', 'configs', 'clients', 'purposes'));
    }

    public function update(Request $request, SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        if (!in_array($rental->status, [SpaceRental::STATUS_DRAFT, SpaceRental::STATUS_PENDING])) {
            return back()->with('error', 'Cannot edit rental in current status.');
        }

        $host = auth()->user()->host;

        $data = $request->validate([
            'client_type' => 'required|in:existing,external',
            'client_id' => 'required_if:client_type,existing|nullable|exists:clients,id',
            'external_client_name' => 'required_if:client_type,external|nullable|string|max:255',
            'external_client_email' => 'nullable|email|max:255',
            'external_client_phone' => 'nullable|string|max:50',
            'external_client_company' => 'nullable|string|max:255',
            'purpose' => 'required|in:photo_shoot,video_production,workshop,training,other',
            'purpose_notes' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'internal_notes' => 'nullable|string',
        ]);

        $config = $rental->config;

        // Build datetime from date and time
        $startTime = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $endTime = Carbon::parse($data['date'] . ' ' . $data['end_time']);

        // Calculate hours
        $hours = $startTime->diffInMinutes($endTime) / 60;

        // Validate minimum/maximum hours
        if ($hours < $config->minimum_hours) {
            return back()->withErrors(['end_time' => "Minimum booking is {$config->minimum_hours} hours."])->withInput();
        }
        if ($config->maximum_hours && $hours > $config->maximum_hours) {
            return back()->withErrors(['end_time' => "Maximum booking is {$config->maximum_hours} hours."])->withInput();
        }

        // Check for conflicts (excluding this rental)
        $conflicts = $this->spaceRentalService->checkConflicts($config, $startTime, $endTime, $rental->id);
        if (!empty($conflicts)) {
            $conflictChecker = app(\App\Services\Schedule\SpaceRentalConflictChecker::class);
            $message = $conflictChecker->formatConflictMessage($conflicts);
            return back()->withErrors(['date' => $message])->withInput();
        }

        // Prepare update data
        $updateData = [
            'purpose' => $data['purpose'],
            'purpose_notes' => $data['purpose_notes'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours_booked' => $hours,
            'internal_notes' => $data['internal_notes'],
        ];

        if ($data['client_type'] === 'existing') {
            $updateData['client_id'] = $data['client_id'];
            $updateData['external_client_name'] = null;
            $updateData['external_client_email'] = null;
            $updateData['external_client_phone'] = null;
            $updateData['external_client_company'] = null;
        } else {
            $updateData['client_id'] = null;
            $updateData['external_client_name'] = $data['external_client_name'];
            $updateData['external_client_email'] = $data['external_client_email'];
            $updateData['external_client_phone'] = $data['external_client_phone'];
            $updateData['external_client_company'] = $data['external_client_company'];
        }

        $this->spaceRentalService->updateRental($rental, $updateData, auth()->user());

        return redirect()->route('space-rentals.show', $rental)
            ->with('success', 'Space rental updated successfully.');
    }

    public function confirm(SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        try {
            $this->spaceRentalService->confirmRental($rental, auth()->user());
            return back()->with('success', 'Rental confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function start(SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        try {
            $this->spaceRentalService->startRental($rental, auth()->user());
            return back()->with('success', 'Rental started successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(Request $request, SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        $data = $request->validate([
            'has_damage' => 'boolean',
            'damage_notes' => 'nullable|string',
            'damage_charge' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->spaceRentalService->completeRental(
                $rental,
                auth()->user(),
                $request->boolean('has_damage'),
                $data['damage_notes'] ?? null,
                (float) ($data['damage_charge'] ?? 0)
            );
            return back()->with('success', 'Rental completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        $data = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->spaceRentalService->cancelRental($rental, auth()->user(), $data['cancellation_reason'] ?? null);
            return back()->with('success', 'Rental cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function signWaiver(Request $request, SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        $data = $request->validate([
            'signer_name' => 'required|string|max:255',
        ]);

        try {
            $this->spaceRentalService->signWaiver($rental, $data['signer_name'], $request->ip());
            return back()->with('success', 'Waiver signed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordDeposit(SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        try {
            $this->spaceRentalService->recordDepositPayment($rental);
            return back()->with('success', 'Deposit payment recorded.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function refundDeposit(Request $request, SpaceRental $rental)
    {
        $this->authorizeHost($rental);

        $data = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $rental->deposit_amount,
            'refund_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->spaceRentalService->processDepositRefund(
                $rental,
                (float) $data['refund_amount'],
                $data['refund_reason'] ?? null
            );
            return back()->with('success', 'Deposit refund processed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX: Get available time slots for a config on a date
     */
    public function getAvailableTimes(Request $request, SpaceRentalConfig $config)
    {
        $this->authorizeConfigHost($config);

        $date = Carbon::parse($request->get('date', today()));
        $slots = $this->spaceRentalService->getAvailableSlots($config, $date);

        return response()->json([
            'slots' => array_map(function ($slot) {
                return [
                    'start' => $slot['start']->format('H:i'),
                    'end' => $slot['end']->format('H:i'),
                    'hours' => $slot['hours'],
                    'start_formatted' => $slot['start']->format('g:i A'),
                    'end_formatted' => $slot['end']->format('g:i A'),
                ];
            }, $slots),
            'minimum_hours' => $config->minimum_hours,
            'maximum_hours' => $config->maximum_hours,
        ]);
    }

    /**
     * AJAX: Calculate price for given config and hours
     */
    public function calculatePrice(Request $request)
    {
        $host = auth()->user()->host;

        $data = $request->validate([
            'config_id' => 'required|exists:space_rental_configs,id',
            'hours' => 'required|numeric|min:0.5',
        ]);

        $config = SpaceRentalConfig::where('host_id', $host->id)->findOrFail($data['config_id']);

        $pricing = $this->spaceRentalService->calculatePricing($config, (float) $data['hours']);

        return response()->json($pricing);
    }

    /**
     * AJAX: Check for conflicts
     */
    public function checkConflicts(Request $request)
    {
        $host = auth()->user()->host;

        $data = $request->validate([
            'config_id' => 'required|exists:space_rental_configs,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'exclude_rental_id' => 'nullable|integer',
        ]);

        $config = SpaceRentalConfig::where('host_id', $host->id)->findOrFail($data['config_id']);

        $conflicts = $this->spaceRentalService->checkConflicts(
            $config,
            Carbon::parse($data['start_time']),
            Carbon::parse($data['end_time']),
            $data['exclude_rental_id'] ?? null
        );

        $hasConflict = !empty($conflicts);
        $message = '';

        if ($hasConflict) {
            $conflictChecker = app(\App\Services\Schedule\SpaceRentalConflictChecker::class);
            $message = $conflictChecker->formatConflictMessage($conflicts);
        }

        return response()->json([
            'has_conflict' => $hasConflict,
            'message' => $message,
            'conflicts' => $conflicts,
        ]);
    }

    private function authorizeHost(SpaceRental $rental): void
    {
        if ($rental->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }

    private function authorizeConfigHost(SpaceRentalConfig $config): void
    {
        if ($config->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
