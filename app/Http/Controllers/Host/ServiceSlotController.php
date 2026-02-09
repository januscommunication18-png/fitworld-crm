<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\ServiceSlotRequest;
use App\Models\ServiceSlot;
use App\Models\ServicePlan;
use App\Models\Instructor;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ServiceSlotController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        // Get filter parameters
        $servicePlanId = $request->get('service_plan_id');
        $instructorId = $request->get('instructor_id');
        $status = $request->get('status');
        $date = $request->get('date', now()->format('Y-m-d'));

        // Date range for calendar view (default to current week)
        $startDate = Carbon::parse($date)->startOfWeek();
        $endDate = Carbon::parse($date)->endOfWeek();

        $slots = $host->serviceSlots()
            ->with(['servicePlan', 'instructor', 'location', 'room'])
            ->when($servicePlanId, fn($q) => $q->where('service_plan_id', $servicePlanId))
            ->when($instructorId, fn($q) => $q->where('instructor_id', $instructorId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->forDateRange($startDate, $endDate)
            ->orderBy('start_time')
            ->get();

        // Get filter options
        $servicePlans = $host->servicePlans()->active()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();
        $statuses = ServiceSlot::getStatuses();

        return view('host.service-slots.index', compact(
            'slots', 'servicePlans', 'instructors', 'statuses',
            'servicePlanId', 'instructorId', 'status', 'date',
            'startDate', 'endDate'
        ));
    }

    public function create(Request $request)
    {
        $host = auth()->user()->host;

        $servicePlans = $host->servicePlans()->active()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();
        $locations = $host->locations()->active()->orderBy('name')->get();

        // Pre-select values from query parameters
        $selectedServicePlanId = $request->get('service_plan_id');
        $selectedInstructorId = $request->get('instructor_id');
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        return view('host.service-slots.create', compact(
            'servicePlans', 'instructors', 'locations',
            'selectedServicePlanId', 'selectedInstructorId', 'selectedDate'
        ));
    }

    public function store(ServiceSlotRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();
        $data['host_id'] = $host->id;

        // Calculate end time based on service plan duration
        $servicePlan = ServicePlan::find($data['service_plan_id']);
        $startTime = Carbon::parse($data['start_time']);
        $data['end_time'] = $startTime->copy()->addMinutes($servicePlan->duration_minutes);

        // Check for overlapping slots
        if ($this->hasOverlappingSlot($host->id, $data['instructor_id'], $startTime, $data['end_time'])) {
            return back()->withInput()->with('error', 'This slot overlaps with an existing slot for this instructor.');
        }

        ServiceSlot::create($data);

        return redirect()->route('service-slots.index')
            ->with('success', 'Service slot created successfully.');
    }

    public function show(ServiceSlot $serviceSlot)
    {
        $this->authorizeHost($serviceSlot);

        $serviceSlot->load(['servicePlan', 'instructor', 'location', 'room']);

        return view('host.service-slots.show', compact('serviceSlot'));
    }

    public function edit(ServiceSlot $serviceSlot)
    {
        $this->authorizeHost($serviceSlot);

        $host = auth()->user()->host;
        $servicePlans = $host->servicePlans()->active()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();
        $locations = $host->locations()->active()->orderBy('name')->get();
        $statuses = ServiceSlot::getStatuses();

        return view('host.service-slots.edit', compact('serviceSlot', 'servicePlans', 'instructors', 'locations', 'statuses'));
    }

    public function update(ServiceSlotRequest $request, ServiceSlot $serviceSlot)
    {
        $this->authorizeHost($serviceSlot);

        $data = $request->validated();

        // Calculate end time based on service plan duration
        $servicePlan = ServicePlan::find($data['service_plan_id']);
        $startTime = Carbon::parse($data['start_time']);
        $data['end_time'] = $startTime->copy()->addMinutes($servicePlan->duration_minutes);

        // Check for overlapping slots (exclude current slot)
        if ($this->hasOverlappingSlot($serviceSlot->host_id, $data['instructor_id'], $startTime, $data['end_time'], $serviceSlot->id)) {
            return back()->withInput()->with('error', 'This slot overlaps with an existing slot for this instructor.');
        }

        $serviceSlot->update($data);

        return redirect()->route('service-slots.index')
            ->with('success', 'Service slot updated successfully.');
    }

    public function destroy(ServiceSlot $serviceSlot)
    {
        $this->authorizeHost($serviceSlot);

        // Cannot delete booked slots
        if ($serviceSlot->status === ServiceSlot::STATUS_BOOKED) {
            return back()->with('error', 'Cannot delete a booked slot. Please cancel the booking first.');
        }

        $serviceSlot->delete();

        return redirect()->route('service-slots.index')
            ->with('success', 'Service slot deleted successfully.');
    }

    public function bulkCreate(Request $request)
    {
        $host = auth()->user()->host;

        $request->validate([
            'service_plan_id' => 'required|exists:service_plans,id',
            'instructor_id' => 'required|exists:instructors,id',
            'location_id' => 'nullable|exists:locations,id',
            'room_id' => 'nullable|exists:rooms,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'times' => 'required|array|min:1',
            'times.*' => 'date_format:H:i',
        ]);

        $servicePlan = ServicePlan::find($request->service_plan_id);
        $period = CarbonPeriod::create($request->start_date, $request->end_date);
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($period as $date) {
            if (!in_array($date->dayOfWeek, $request->days_of_week)) {
                continue;
            }

            foreach ($request->times as $time) {
                $startTime = $date->copy()->setTimeFromTimeString($time);
                $endTime = $startTime->copy()->addMinutes($servicePlan->duration_minutes);

                // Skip if in the past
                if ($startTime->isPast()) {
                    $skippedCount++;
                    continue;
                }

                // Check for overlapping slots
                if ($this->hasOverlappingSlot($host->id, $request->instructor_id, $startTime, $endTime)) {
                    $skippedCount++;
                    continue;
                }

                ServiceSlot::create([
                    'host_id' => $host->id,
                    'service_plan_id' => $request->service_plan_id,
                    'instructor_id' => $request->instructor_id,
                    'location_id' => $request->location_id,
                    'room_id' => $request->room_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => ServiceSlot::STATUS_AVAILABLE,
                ]);
                $createdCount++;
            }
        }

        $message = "Created {$createdCount} slots.";
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} slots due to conflicts or past dates.";
        }

        return redirect()->route('service-slots.index')->with('success', $message);
    }

    /**
     * Check if a slot overlaps with existing slots
     */
    private function hasOverlappingSlot(int $hostId, int $instructorId, Carbon $startTime, Carbon $endTime, ?int $excludeId = null): bool
    {
        return ServiceSlot::where('host_id', $hostId)
            ->where('instructor_id', $instructorId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }

    private function authorizeHost(ServiceSlot $serviceSlot): void
    {
        if ($serviceSlot->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
