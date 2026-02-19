<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\Client;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    /**
     * Get the host from the request attributes (set by ResolveSubdomainHost middleware)
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Show the service request form
     */
    public function create(Request $request, $servicePlanId = null)
    {
        $servicePlanId = $servicePlanId ? (int) $servicePlanId : null;
        $host = $this->getHost($request);

        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classPlans = ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedServicePlan = null;
        if ($servicePlanId) {
            $selectedServicePlan = $servicePlans->firstWhere('id', $servicePlanId);
        }

        // Get logged-in member if authenticated
        $member = Auth::guard('member')->user();

        return view('subdomain.service-request', [
            'host' => $host,
            'servicePlans' => $servicePlans,
            'classPlans' => $classPlans,
            'selectedServicePlan' => $selectedServicePlan,
            'member' => $member,
        ]);
    }

    /**
     * Show success page after submitting request
     */
    public function success(Request $request)
    {
        $host = $this->getHost($request);

        return view('subdomain.service-request-success', [
            'host' => $host,
        ]);
    }

    /**
     * Store a new service request (creates a helpdesk ticket)
     */
    public function store(Request $request)
    {
        $host = $this->getHost($request);
        $bookingType = $request->input('booking_type', 'service');

        // Validate based on booking type
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'booking_type' => 'required|in:service,class',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable',
            'message' => 'nullable|string|max:2000',
        ];

        if ($bookingType === 'service') {
            $rules['service_plan_id'] = 'required|exists:service_plans,id';
        } else {
            $rules['class_plan_id'] = 'required|exists:class_plans,id';
        }

        $validated = $request->validate($rules);

        // Get the plan name based on booking type
        $planName = '';
        $servicePlanId = null;
        $classPlanId = null;

        if ($bookingType === 'service') {
            $servicePlan = ServicePlan::where('id', $validated['service_plan_id'])
                ->where('host_id', $host->id)
                ->firstOrFail();
            $planName = $servicePlan->name;
            $servicePlanId = $servicePlan->id;
        } else {
            $classPlan = ClassPlan::where('id', $validated['class_plan_id'])
                ->where('host_id', $host->id)
                ->firstOrFail();
            $planName = $classPlan->name;
            $classPlanId = $classPlan->id;
        }

        // Check if client exists with this email
        $client = Client::where('host_id', $host->id)
            ->where('email', $validated['email'])
            ->first();

        // Capture UTM parameters if present
        $utmParams = [];
        if ($request->has('utm_source')) {
            $utmParams['source'] = $request->input('utm_source');
        }
        if ($request->has('utm_medium')) {
            $utmParams['medium'] = $request->input('utm_medium');
        }
        if ($request->has('utm_campaign')) {
            $utmParams['campaign'] = $request->input('utm_campaign');
        }

        // Create helpdesk ticket
        $subjectPrefix = $bookingType === 'service' ? 'Service Request' : 'Class Request';
        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $client?->id,
            'source_type' => HelpdeskTicket::SOURCE_BOOKING_REQUEST,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $subjectPrefix . ': ' . $planName,
            'message' => $validated['message'] ?? null,
            'service_plan_id' => $servicePlanId,
            'class_plan_id' => $classPlanId,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'status' => HelpdeskTicket::STATUS_OPEN,
            'source_url' => $request->headers->get('referer'),
            'utm_params' => !empty($utmParams) ? $utmParams : null,
        ]);

        // Add initial message if provided
        if (!empty($validated['message'])) {
            $ticket->addMessage($validated['message'], null, 'customer');
        }

        return redirect()->route('subdomain.service-request.success', ['subdomain' => $host->subdomain])
            ->with('success', 'Thank you! Your request has been submitted. We\'ll be in touch soon.');
    }
}
