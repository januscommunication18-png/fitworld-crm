<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Event;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
     * Show the service request form.
     *
     * The form offers the studio's full catalog (classes, services, class
     * passes, memberships, events) so a visitor can request info on any
     * offering. A specific item can be pre-selected via the legacy
     * /service-request/{servicePlanId} route or ?type=&id= query params used by
     * the public "Request Info" buttons.
     */
    public function create(Request $request, string $subdomain, $servicePlanId = null)
    {
        $host = $this->getHost($request);
        $offeringsByType = $this->buildOfferingsByType($host);

        // Resolve a pre-selected offering.
        $selectedAlias = null;
        $selectedOfferingId = null;

        if ($servicePlanId) {
            $selectedAlias = 'service_plan';
            $selectedOfferingId = (int) $servicePlanId;
        } elseif ($request->filled('type') && $request->filled('id')) {
            $alias = (string) $request->query('type');
            if (array_key_exists($alias, $offeringsByType)) {
                $selectedAlias = $alias;
                $selectedOfferingId = (int) $request->query('id');
            }
        }

        // Only keep the pre-selected id if it actually exists in that type's
        // active list — otherwise just open the picker to that type.
        if ($selectedAlias && !$offeringsByType[$selectedAlias]['items']->firstWhere('id', $selectedOfferingId)) {
            $selectedOfferingId = null;
        }

        $member = Auth::guard('member')->user();

        return view('subdomain.service-request', [
            'host' => $host,
            'offeringsByType' => $offeringsByType,
            'selectedAlias' => $selectedAlias,
            'selectedOfferingId' => $selectedOfferingId,
            'member' => $member,
        ]);
    }

    /**
     * Build the studio's customer-facing offerings grouped by the
     * HelpdeskTicket::REQUESTED_TYPE_MAP alias. Aliases with no active items
     * are omitted so the picker never offers an empty bucket.
     *
     * @return array<string, array{label: string, icon: string, items: \Illuminate\Support\Collection}>
     */
    protected function buildOfferingsByType(Host $host): array
    {
        $sources = [
            'class_plan'   => ['label' => 'Class',      'icon' => 'tabler--yoga',           'query' => fn() => $host->classPlans()->active()->orderBy('name')->get(['id', 'name'])],
            'service_plan' => ['label' => 'Service',    'icon' => 'tabler--sparkles',       'query' => fn() => $host->servicePlans()->active()->orderBy('name')->get(['id', 'name'])],
            'class_pass'   => ['label' => 'Class Pass', 'icon' => 'tabler--ticket',         'query' => fn() => $host->classPasses()->active()->orderBy('name')->get(['id', 'name'])],
            'membership'   => ['label' => 'Membership', 'icon' => 'tabler--id-badge-2',     'query' => fn() => $host->membershipPlans()->active()->orderBy('name')->get(['id', 'name'])],
            'event'        => ['label' => 'Event',      'icon' => 'tabler--calendar-event', 'query' => fn() => Event::forHost($host->id)->published()->where('visibility', 'public')->where('start_datetime', '>=', now())->orderBy('start_datetime')->get(['id', 'title'])->map(fn($e) => (object) ['id' => $e->id, 'name' => $e->title])],
        ];

        $out = [];
        foreach ($sources as $alias => $cfg) {
            $items = $cfg['query']();
            if ($items->isNotEmpty()) {
                $out[$alias] = [
                    'label' => $cfg['label'],
                    'icon'  => $cfg['icon'],
                    'items' => $items,
                ];
            }
        }
        return $out;
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
     * Store a new service request (creates a helpdesk ticket). The chosen
     * offering is resolved polymorphically via REQUESTED_TYPE_MAP and recorded
     * on (requested_type, requested_id); service plans also mirror to the
     * legacy service_plan_id column.
     */
    public function store(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'requested_type_alias' => ['required', 'string', Rule::in(array_keys(HelpdeskTicket::REQUESTED_TYPE_MAP))],
            'requested_offering_id' => 'required|integer',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable',
            'message' => 'nullable|string|max:2000',
        ]);

        // Resolve and host-scope the requested offering.
        $modelClass = HelpdeskTicket::REQUESTED_TYPE_MAP[$validated['requested_type_alias']] ?? null;
        $item = $modelClass
            ? $modelClass::where('host_id', $host->id)->find((int) $validated['requested_offering_id'])
            : null;

        if (!$item) {
            return back()->withInput()
                ->withErrors(['requested_offering_id' => 'That option is no longer available — please pick another.']);
        }

        $itemName = $item->name ?? $item->title ?? 'Offering';
        $typeLabel = HelpdeskTicket::REQUESTED_TYPE_LABELS[$validated['requested_type_alias']] ?? 'Request';
        $servicePlanId = $validated['requested_type_alias'] === 'service_plan' ? $item->getKey() : null;

        // Link to an existing client if the email matches one.
        $client = Client::where('host_id', $host->id)
            ->where('email', $validated['email'])
            ->first();

        // Capture UTM parameters if present
        $utmParams = [];
        foreach (['source', 'medium', 'campaign'] as $utm) {
            if ($request->filled("utm_{$utm}")) {
                $utmParams[$utm] = $request->input("utm_{$utm}");
            }
        }

        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $client?->id,
            'source_type' => HelpdeskTicket::SOURCE_BOOKING_REQUEST,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $typeLabel . ' Request: ' . $itemName,
            'message' => $validated['message'] ?? null,
            'service_plan_id' => $servicePlanId,
            'requested_type' => $modelClass,
            'requested_id' => $item->getKey(),
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
