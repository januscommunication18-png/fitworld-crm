<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\ServicePlan;
use Illuminate\Http\Request;

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
     * Store a new service request (creates a helpdesk ticket)
     */
    public function store(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'service_plan_id' => 'required|exists:service_plans,id',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable',
            'message' => 'nullable|string|max:2000',
        ]);

        // Verify service plan belongs to this host
        $servicePlan = ServicePlan::where('id', $validated['service_plan_id'])
            ->where('host_id', $host->id)
            ->firstOrFail();

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
        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $client?->id,
            'source_type' => HelpdeskTicket::SOURCE_BOOKING_REQUEST,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => 'Service Request: ' . $servicePlan->name,
            'message' => $validated['message'] ?? null,
            'service_plan_id' => $servicePlan->id,
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

        return redirect()->back()->with('success', 'Thank you! Your service request has been submitted. We\'ll be in touch soon.');
    }
}
