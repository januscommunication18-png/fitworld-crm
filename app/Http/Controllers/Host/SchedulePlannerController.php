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
        $authUser = auth()->user();
        if (!$authUser->hasPermission('schedule.view') && !$authUser->hasPermission('schedule.view_own')) {
            abort(403, 'You do not have permission to view the schedule planner.');
        }
        $viewOwnOnly = !$authUser->hasPermission('schedule.view') && $authUser->hasPermission('schedule.view_own');
        $host = $authUser->currentHost();
        $type = $request->get('type', 'all');

        $classPlans = $host->classPlans()->where('is_active', true)->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->where('is_active', true)->orderBy('name')->get();
        $membershipPlans = MembershipPlan::where('host_id', $host->id)->active()->orderBy('name')->get();

        $schedules = collect();
        $selectedPlanId = null;

        $openAccessPlans = $this->getOpenAccessSchedules($host);

        if ($type === 'all') {
            // Merge all schedules from all types
            foreach ($classPlans as $plan) {
                $schedules = $schedules->merge($this->getClassSchedules($host, $plan->id));
            }
            foreach ($servicePlans as $plan) {
                $schedules = $schedules->merge($this->getServiceSchedules($host, $plan->id));
            }
            foreach ($membershipPlans as $plan) {
                $schedules = $schedules->merge($this->getMembershipSchedules($host, $plan->id));
            }
            $schedules = $schedules->merge($openAccessPlans);
        } elseif ($type === 'membership') {
            $selectedPlanId = $request->get('membership_plan_id', $membershipPlans->first()?->id);
            $schedules = $this->getMembershipSchedules($host, $selectedPlanId);
            // Always include open access plans in membership tab
            $schedules = $schedules->merge($openAccessPlans);
        } elseif ($type === 'service') {
            $selectedPlanId = $request->get('service_plan_id', $servicePlans->first()?->id);
            $schedules = $this->getServiceSchedules($host, $selectedPlanId);
        } else {
            $selectedPlanId = $request->get('class_plan_id', $classPlans->first()?->id);
            $schedules = $this->getClassSchedules($host, $selectedPlanId);
        }

        // Scope to schedules assigned to this user when only view_own is granted
        if ($viewOwnOnly) {
            $myInstructorIds = \App\Models\Instructor::where('host_id', $host->id)
                ->where('user_id', $authUser->id)
                ->pluck('id')
                ->all();
            $schedules = $schedules->filter(function ($schedule) use ($myInstructorIds) {
                // Each schedule item has a `session` (parent ClassSession) — check primary/backup assignment
                $session = $schedule->session ?? $schedule->classSession ?? null;
                if (!$session) {
                    // Fallback if the structure varies — keep only when we can confirm assignment
                    return false;
                }
                if (in_array($session->primary_instructor_id, $myInstructorIds, true)) return true;
                if (in_array($session->backup_instructor_id, $myInstructorIds, true)) return true;
                if ($session->relationLoaded('backupInstructors')) {
                    foreach ($session->backupInstructors as $bi) {
                        if (in_array($bi->id, $myInstructorIds, true)) return true;
                    }
                }
                return false;
            })->values();
        }

        return view('host.schedule-planner.index', compact(
            'classPlans', 'servicePlans', 'membershipPlans', 'selectedPlanId', 'schedules', 'type'
        ));
    }

    /**
     * Show a planner's details — generic info about the recurring schedule.
     */
    public function show(ClassSession $classSession)
    {
        $host = auth()->user()->currentHost();

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        $classSession->load([
            'classPlan',
            'primaryInstructor',
            'backupInstructors',
            'location',
            'room',
            'membershipPlans',
            'recurrenceChildren' => fn($q) => $q->with('primaryInstructor')->orderBy('start_time'),
        ]);

        $recurrenceService = app(\App\Services\Schedule\RecurrenceService::class);

        // Parse recurrence rule for display
        $recurringDays = [];
        $recurrenceEndType = null;
        $recurrenceEndValue = null;

        if ($classSession->recurrence_rule) {
            $parsed = $recurrenceService->parseRecurrenceRule($classSession->recurrence_rule);
            $dayMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            if (!empty($parsed['days_of_week'])) {
                $recurringDays = array_map(fn($d) => $dayMap[(int) $d] ?? $d, $parsed['days_of_week']);
            }
            $recurrenceEndType = $parsed['end_type'] ?? null;
            $recurrenceEndValue = $parsed['end_value'] ?? null;
        }

        // Determine planner type
        $plannerType = 'class';
        if (!$classSession->class_plan_id && $classSession->membershipPlans->isNotEmpty()) {
            $plannerType = 'membership';
        }

        // Count sessions
        $totalSessions = $classSession->recurrenceChildren->count() + 1;
        $upcomingSessions = $classSession->recurrenceChildren->where('start_time', '>', now())->count();
        if ($classSession->start_time->isFuture()) $upcomingSessions++;
        $completedSessions = $totalSessions - $upcomingSessions;

        // Check instructor availability conflicts for each session
        $instructor = $classSession->primaryInstructor;
        $conflictSessionIds = collect();

        if ($instructor) {
            $hasConfigured = !empty($instructor->working_days) || !empty($instructor->availability_by_day) || !empty($instructor->availability_hours);

            if ($hasConfigured) {
                // Check parent
                if (!$instructor->worksOnDay($classSession->start_time->dayOfWeek)) {
                    $conflictSessionIds->push($classSession->id);
                }
                // Check children
                foreach ($classSession->recurrenceChildren as $child) {
                    if (!$instructor->worksOnDay($child->start_time->dayOfWeek)) {
                        $conflictSessionIds->push($child->id);
                    }
                }
            }
        }

        return view('host.schedule-planner.show', compact(
            'classSession', 'recurringDays', 'recurrenceEndType', 'recurrenceEndValue',
            'plannerType', 'totalSessions', 'upcomingSessions', 'completedSessions',
            'conflictSessionIds'
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

    private function getOpenAccessSchedules($host): \Illuminate\Support\Collection
    {
        $schedules = collect();

        $openAccessPlans = MembershipPlan::where('host_id', $host->id)
            ->where('schedule_type', MembershipPlan::SCHEDULE_TYPE_OPEN_ACCESS)
            ->active()
            ->with('checkins')
            ->orderBy('name')
            ->get();

        foreach ($openAccessPlans as $plan) {
            $todayCheckins = $plan->checkins()->whereDate('checked_in_at', today())->count();
            $activeMembers = $plan->customerMemberships()->where('status', 'active')->count();

            // Resolve location name from location_ids
            $locationName = '—';
            if (!empty($plan->location_ids)) {
                $location = \App\Models\Location::find($plan->location_ids[0]);
                $locationName = $location?->name ?? '—';
            }

            $schedules->push((object) [
                'id' => $plan->id,
                'plan_id' => $plan->id,
                'title' => $plan->name,
                'days' => 'Anytime',
                'time' => 'Open Access',
                'instructor' => '—',
                'location' => $locationName,
                'session_count' => $activeMembers,
                'today_checkins' => $todayCheckins,
                'is_recurring' => false,
                'is_open_access' => true,
                'status' => $plan->status,
                'type' => 'open_access',
            ]);
        }

        return $schedules;
    }
}
