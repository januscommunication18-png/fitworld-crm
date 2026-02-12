<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Schedule\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ScheduleApiController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Get events for FullCalendar
     *
     * Query params: start, end, type, location_id, instructor_id
     */
    public function events(Request $request): JsonResponse
    {
        $host = auth()->user()?->host;

        if (!$host) {
            return response()->json([], 200);
        }

        // Validate and parse date range
        $start = $request->input('start')
            ? Carbon::parse($request->input('start'))
            : Carbon::today()->startOfMonth();

        $end = $request->input('end')
            ? Carbon::parse($request->input('end'))
            : Carbon::today()->endOfMonth();

        // Build filters from request
        $filters = [
            'type' => $request->input('type', 'both'),
            'location_id' => $request->input('location_id'),
            'instructor_id' => $request->input('instructor_id'),
            'status' => $request->input('status', 'active'),
        ];

        // Get schedule items
        $items = $this->scheduleService->getScheduleItems($host, $start, $end, $filters);

        // Format for FullCalendar
        $events = $this->scheduleService->formatForCalendar($items);

        return response()->json($events);
    }

    /**
     * Get stats for a date range
     */
    public function stats(Request $request): JsonResponse
    {
        $host = auth()->user()?->host;

        if (!$host) {
            return response()->json(['total' => 0, 'classes' => 0, 'services' => 0], 200);
        }

        $start = $request->input('start')
            ? Carbon::parse($request->input('start'))
            : Carbon::today();

        $end = $request->input('end')
            ? Carbon::parse($request->input('end'))
            : Carbon::today();

        $stats = $this->scheduleService->getStats($host, $start, $end);

        return response()->json($stats);
    }

    /**
     * Get upcoming events
     */
    public function upcoming(Request $request): JsonResponse
    {
        $host = auth()->user()?->host;

        if (!$host) {
            return response()->json([], 200);
        }

        $limit = min($request->input('limit', 5), 20);

        $filters = [
            'type' => $request->input('type', 'both'),
        ];

        $items = $this->scheduleService->getUpcoming($host, $limit, $filters);
        $events = $this->scheduleService->formatForCalendar($items);

        return response()->json($events);
    }
}
