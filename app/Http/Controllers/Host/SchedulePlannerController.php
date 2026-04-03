<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\MembershipPlan;
use App\Models\ServiceSlot;
use Illuminate\Http\Request;

class SchedulePlannerController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->currentHost();
        $type = $request->get('type', 'class');

        $classPlans = $host->classPlans()->where('is_active', true)->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->where('is_active', true)->orderBy('name')->get();
        $membershipPlans = MembershipPlan::where('host_id', $host->id)->active()->orderBy('name')->get();

        $schedules = collect();

        if ($type === 'membership') {
            $selectedPlanId = $request->get('membership_plan_id', $membershipPlans->first()?->id);
            $schedules = $this->getMembershipSchedules($host, $selectedPlanId);
        } elseif ($type === 'service') {
            $selectedPlanId = $request->get('service_plan_id', $servicePlans->first()?->id);
            $schedules = $this->getServiceSchedules($host, $selectedPlanId);
        } else {
            $selectedPlanId = $request->get('class_plan_id', $classPlans->first()?->id);
            $schedules = $this->getClassSchedules($host, $selectedPlanId);
        }

        return view('host.schedule-planner.index', compact(
            'classPlans', 'servicePlans', 'membershipPlans', 'selectedPlanId', 'schedules', 'type'
        ));
    }

    private function getClassSchedules($host, $selectedPlanId): \Illuminate\Support\Collection
    {
        $schedules = collect();

        if (!$selectedPlanId) return $schedules;

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
            $childCount = ClassSession::where('recurrence_parent_id', $parent->id)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>=', now())
                ->count();

            $totalCount = $childCount + ($parent->start_time->isFuture() && $parent->status === ClassSession::STATUS_PUBLISHED ? 1 : 0);

            if (!$parent->recurrence_rule && $childCount === 0) {
                $totalCount = $parent->start_time->isFuture() ? 1 : 0;
            }

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
                'type' => 'class',
            ]);
        }

        return $schedules;
    }

    private function getMembershipSchedules($host, $selectedPlanId): \Illuminate\Support\Collection
    {
        $schedules = collect();

        if (!$selectedPlanId) return $schedules;

        // Membership sessions have class_plan_id = null and are linked via pivot
        $parents = ClassSession::where('host_id', $host->id)
            ->whereNull('class_plan_id')
            ->whereHas('membershipPlans', function ($q) use ($selectedPlanId) {
                $q->where('membership_plans.id', $selectedPlanId);
            })
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
            $childCount = ClassSession::where('recurrence_parent_id', $parent->id)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>=', now())
                ->count();

            $totalCount = $childCount + ($parent->start_time->isFuture() && $parent->status === ClassSession::STATUS_PUBLISHED ? 1 : 0);

            if (!$parent->recurrence_rule && $childCount === 0) {
                $totalCount = $parent->start_time->isFuture() ? 1 : 0;
            }

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
                'type' => 'membership',
            ]);
        }

        return $schedules;
    }

    private function getServiceSchedules($host, $selectedPlanId): \Illuminate\Support\Collection
    {
        $schedules = collect();

        if (!$selectedPlanId) return $schedules;

        $parents = ServiceSlot::where('host_id', $host->id)
            ->where('service_plan_id', $selectedPlanId)
            ->where(function ($q) {
                $q->whereNotNull('recurrence_rule')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('recurrence_parent_id')->whereNull('recurrence_rule');
                  });
            })
            ->with(['instructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $recurrenceService = app(\App\Services\Schedule\RecurrenceService::class);

        foreach ($parents as $parent) {
            $childCount = ServiceSlot::where('recurrence_parent_id', $parent->id)
                ->where('status', ServiceSlot::STATUS_AVAILABLE)
                ->where('start_time', '>=', now())
                ->count();

            $totalCount = $childCount + ($parent->start_time->isFuture() && $parent->status === ServiceSlot::STATUS_AVAILABLE ? 1 : 0);

            if (!$parent->recurrence_rule && $childCount === 0) {
                $totalCount = $parent->start_time->isFuture() ? 1 : 0;
            }

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
                'instructor' => $parent->instructor?->name ?? 'TBD',
                'location' => $parent->location?->name ?? '—',
                'session_count' => $totalCount,
                'is_recurring' => (bool) $parent->recurrence_rule,
                'status' => $parent->status,
                'type' => 'service',
            ]);
        }

        return $schedules;
    }
}
