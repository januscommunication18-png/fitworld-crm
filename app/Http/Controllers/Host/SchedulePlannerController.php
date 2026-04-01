<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class SchedulePlannerController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        $classPlans = $host->classPlans()->where('is_active', true)->orderBy('name')->get();
        $selectedPlanId = $request->get('class_plan_id', $classPlans->first()?->id);

        $schedules = collect();

        if ($selectedPlanId) {
            // Get all recurrence parent sessions for this class plan
            $parents = ClassSession::where('host_id', $host->id)
                ->where('class_plan_id', $selectedPlanId)
                ->where(function ($q) {
                    $q->whereNotNull('recurrence_rule')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('recurrence_parent_id')->whereNull('recurrence_rule');
                      });
                })
                ->with(['primaryInstructor:id,name', 'location:id,name'])
                ->orderBy('start_time')
                ->get();

            $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
            $recurrenceService = app(\App\Services\Schedule\RecurrenceService::class);

            foreach ($parents as $parent) {
                // Count child sessions (future only)
                $childCount = ClassSession::where('recurrence_parent_id', $parent->id)
                    ->where('status', ClassSession::STATUS_PUBLISHED)
                    ->where('start_time', '>=', now())
                    ->count();

                // Include the parent itself if it's in the future
                $totalCount = $childCount + ($parent->start_time->isFuture() && $parent->status === ClassSession::STATUS_PUBLISHED ? 1 : 0);

                // For standalone sessions with no children, count as 1
                if (!$parent->recurrence_rule && $childCount === 0) {
                    $totalCount = $parent->start_time->isFuture() ? 1 : 0;
                }

                // Build day label
                $dayLabel = $parent->start_time->format('l');
                if ($parent->recurrence_rule) {
                    $parsed = $recurrenceService->parseRecurrenceRule($parent->recurrence_rule);
                    if (!empty($parsed['days_of_week'])) {
                        $dayLabel = collect($parsed['days_of_week'])
                            ->map(fn($d) => $dayNames[(int) $d] ?? $d)
                            ->implode(', ');
                    }
                }

                $schedules->push((object) [
                    'id' => $parent->id,
                    'title' => $parent->title,
                    'days' => $dayLabel,
                    'time' => $parent->start_time->format('g:i A') . ' - ' . $parent->end_time->format('g:i A'),
                    'instructor' => $parent->primaryInstructor?->name ?? 'TBD',
                    'location' => $parent->location?->name ?? '—',
                    'session_count' => $totalCount,
                    'is_recurring' => (bool) $parent->recurrence_rule,
                    'status' => $parent->status,
                ]);
            }
        }

        return view('host.schedule-planner.index', compact('classPlans', 'selectedPlanId', 'schedules'));
    }
}
