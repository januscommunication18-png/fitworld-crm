<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassRequest;
use Illuminate\Http\Request;

class ClassRequestController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        $query = ClassRequest::where('host_id', $host->id)
            ->with(['classPlan', 'servicePlan', 'scheduledSession']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            if ($request->type === 'class') {
                $query->forClasses();
            } else {
                $query->forServices();
            }
        }

        if ($request->filled('class_plan_id')) {
            $query->forClassPlan($request->class_plan_id);
        }

        if ($request->filled('service_plan_id')) {
            $query->forServicePlan($request->service_plan_id);
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        // Get counts by status
        $statusCounts = [
            'pending' => ClassRequest::where('host_id', $host->id)->pending()->count(),
            'scheduled' => ClassRequest::where('host_id', $host->id)->scheduled()->count(),
            'ignored' => ClassRequest::where('host_id', $host->id)->ignored()->count(),
        ];

        return view('host.class-requests.index', [
            'requests' => $requests,
            'statusCounts' => $statusCounts,
            'classPlans' => $host->classPlans()->orderBy('name')->get(),
            'servicePlans' => $host->servicePlans()->orderBy('name')->get(),
            'statuses' => ClassRequest::getStatuses(),
            'currentStatus' => $request->status,
            'currentType' => $request->type,
        ]);
    }

    public function show(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $classRequest->load(['classPlan', 'servicePlan', 'scheduledSession']);

        return view('host.class-requests.show', [
            'classRequest' => $classRequest,
        ]);
    }

    public function scheduleFromRequest(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        if (!$classRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        // Redirect to create session form with pre-filled data
        if ($classRequest->isClassRequest()) {
            return redirect()->route('class-sessions.create', [
                'class_plan_id' => $classRequest->class_plan_id,
                'from_request' => $classRequest->id,
            ]);
        }

        // For service requests, redirect to service slots create
        return redirect()->route('service-slots.create', [
            'service_plan_id' => $classRequest->service_plan_id,
            'from_request' => $classRequest->id,
        ]);
    }

    public function ignore(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        if (!$classRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $classRequest->markAsIgnored();

        return back()->with('success', 'Request marked as ignored.');
    }

    public function destroy(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $classRequest->delete();

        return redirect()
            ->route('class-requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    protected function authorizeRequest(ClassRequest $classRequest): void
    {
        if ($classRequest->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
