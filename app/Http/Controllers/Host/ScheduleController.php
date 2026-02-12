<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Services\Schedule\ScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Schedule index - redirect to today view
     */
    public function index()
    {
        return redirect()->route('schedule.today');
    }

    /**
     * Today view - day schedule grouped by location
     */
    public function today(Request $request)
    {
        $host = auth()->user()->host;

        // Parse date parameter or default to today
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        // Build filters from request
        $filters = [
            'type' => $request->input('type', 'both'),
            'location_id' => $request->input('location_id'),
            'instructor_id' => $request->input('instructor_id'),
            'status' => $request->input('status', 'active'),
        ];

        // Get schedule grouped by location
        $scheduleByLocation = $this->scheduleService->getScheduleByLocation($host, $date, $filters);

        // Get stats for the day
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        $stats = $this->scheduleService->getStats($host, $startOfDay, $endOfDay);

        // Get filter options
        $locations = $host->locations()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();

        return view('host.schedule.index', [
            'date' => $date,
            'scheduleByLocation' => $scheduleByLocation,
            'stats' => $stats,
            'filters' => $filters,
            'locations' => $locations,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Calendar view - FullCalendar integration
     */
    public function calendar(Request $request)
    {
        $host = auth()->user()->host;

        // Parse initial date and view
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $view = $request->input('view', 'timeGridWeek');

        // Get filter options
        $locations = $host->locations()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();

        return view('host.schedule.calendar', [
            'date' => $date,
            'view' => $view,
            'locations' => $locations,
            'instructors' => $instructors,
        ]);
    }

    /**
     * List view - date range with table
     */
    public function list(Request $request)
    {
        $host = auth()->user()->host;

        // Parse date range parameters
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::today();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::today()->addDays(7);

        // Build filters from request
        $filters = [
            'type' => $request->input('type', 'both'),
            'location_id' => $request->input('location_id'),
            'instructor_id' => $request->input('instructor_id'),
            'status' => $request->input('status', 'active'),
        ];

        // Get schedule grouped by date
        $scheduleByDate = $this->scheduleService->getScheduleByDate($host, $startDate, $endDate, $filters);

        // Get stats for the range
        $stats = $this->scheduleService->getStats($host, $startDate, $endDate);

        // Get filter options
        $locations = $host->locations()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();

        return view('host.schedule.list', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'scheduleByDate' => $scheduleByDate,
            'stats' => $stats,
            'filters' => $filters,
            'locations' => $locations,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Legacy classes route - redirect to schedule index
     */
    public function classes()
    {
        return redirect()->route('class-sessions.index');
    }

    /**
     * Legacy appointments route - redirect to schedule index
     */
    public function appointments()
    {
        return redirect()->route('service-slots.index');
    }
}
