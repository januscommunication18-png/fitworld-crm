<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use App\Models\SegmentRule;
use App\Models\Client;
use App\Models\MembershipPlan;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SegmentController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $type = $request->get('type');

        $segments = Segment::forHost($host->id)
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('name')
            ->get();

        $types = Segment::getTypes();

        // Summary stats
        $totalClients = Client::forHost($host->id)->active()->count();

        return view('host.segments.index', compact('segments', 'type', 'types', 'totalClients'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $types = Segment::getTypes();
        $tiers = Segment::getTiers();
        $availableFields = SegmentRule::getAvailableFields();
        $operators = SegmentRule::getOperators();

        // For dropdowns in rules
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();

        return view('host.segments.create', compact(
            'types',
            'tiers',
            'availableFields',
            'operators',
            'membershipPlans',
            'tags'
        ));
    }

    public function store(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:static,dynamic,smart',
            'color' => 'nullable|string|max:7',
            'tier' => 'nullable|in:bronze,silver,gold,vip',
            'min_score' => 'nullable|integer|min:0|max:1000',
            'max_score' => 'nullable|integer|min:0|max:1000',
            'rules' => 'nullable|array',
            'rules.*.field' => 'required_with:rules|string',
            'rules.*.operator' => 'required_with:rules|string',
            'rules.*.value' => 'nullable',
            'rules.*.group_index' => 'nullable|integer',
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $counter = 1;
        while (Segment::forHost($host->id)->where('slug', $slug)->exists()) {
            $slug = Str::slug($validated['name']) . '-' . $counter++;
        }

        $segment = Segment::create([
            'host_id' => $host->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'color' => $validated['color'] ?? '#6366f1',
            'tier' => $validated['tier'] ?? null,
            'min_score' => $validated['min_score'] ?? null,
            'max_score' => $validated['max_score'] ?? null,
            'is_active' => true,
        ]);

        // Create rules for dynamic segments
        if ($validated['type'] === Segment::TYPE_DYNAMIC && !empty($validated['rules'])) {
            foreach ($validated['rules'] as $ruleData) {
                if (!empty($ruleData['field']) && !empty($ruleData['operator'])) {
                    $segment->rules()->create([
                        'group_index' => $ruleData['group_index'] ?? 0,
                        'field' => $ruleData['field'],
                        'operator' => $ruleData['operator'],
                        'value' => is_array($ruleData['value'] ?? null)
                            ? json_encode($ruleData['value'])
                            : ($ruleData['value'] ?? null),
                    ]);
                }
            }

            // Sync initial membership
            $segment->syncDynamicMembership();
        }

        return redirect()->route('segments.show', $segment)
            ->with('success', 'Segment created successfully.');
    }

    public function show(Segment $segment)
    {
        $this->authorizeHost($segment);

        $segment->load('rules');

        // Get paginated clients in this segment
        $clients = $segment->clients()
            ->with('tags')
            ->orderBy('last_name')
            ->paginate(25);

        // Calculate analytics
        $analytics = [
            'total_members' => $segment->member_count,
            'total_revenue' => $segment->clients()->sum('total_spent'),
            'avg_visits' => $segment->clients()->avg('total_classes_attended') ?? 0,
        ];

        return view('host.segments.show', compact('segment', 'clients', 'analytics'));
    }

    public function edit(Segment $segment)
    {
        $this->authorizeHost($segment);

        if ($segment->is_system) {
            return redirect()->route('segments.show', $segment)
                ->with('error', 'System segments cannot be edited.');
        }

        $host = auth()->user()->host;
        $segment->load('rules');

        $types = Segment::getTypes();
        $tiers = Segment::getTiers();
        $availableFields = SegmentRule::getAvailableFields();
        $operators = SegmentRule::getOperators();
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();

        return view('host.segments.edit', compact(
            'segment',
            'types',
            'tiers',
            'availableFields',
            'operators',
            'membershipPlans',
            'tags'
        ));
    }

    public function update(Request $request, Segment $segment)
    {
        $this->authorizeHost($segment);

        if ($segment->is_system) {
            return redirect()->route('segments.show', $segment)
                ->with('error', 'System segments cannot be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:static,dynamic,smart',
            'color' => 'nullable|string|max:7',
            'tier' => 'nullable|in:bronze,silver,gold,vip',
            'min_score' => 'nullable|integer|min:0|max:1000',
            'max_score' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'boolean',
            'rules' => 'nullable|array',
            'rules.*.field' => 'required_with:rules|string',
            'rules.*.operator' => 'required_with:rules|string',
            'rules.*.value' => 'nullable',
            'rules.*.group_index' => 'nullable|integer',
        ]);

        $segment->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'color' => $validated['color'] ?? '#6366f1',
            'tier' => $validated['tier'] ?? null,
            'min_score' => $validated['min_score'] ?? null,
            'max_score' => $validated['max_score'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Update rules for dynamic segments
        if ($validated['type'] === Segment::TYPE_DYNAMIC) {
            // Delete existing rules
            $segment->rules()->delete();

            // Create new rules
            if (!empty($validated['rules'])) {
                foreach ($validated['rules'] as $ruleData) {
                    if (!empty($ruleData['field']) && !empty($ruleData['operator'])) {
                        $segment->rules()->create([
                            'group_index' => $ruleData['group_index'] ?? 0,
                            'field' => $ruleData['field'],
                            'operator' => $ruleData['operator'],
                            'value' => is_array($ruleData['value'] ?? null)
                                ? json_encode($ruleData['value'])
                                : ($ruleData['value'] ?? null),
                        ]);
                    }
                }
            }

            // Re-sync membership
            $segment->syncDynamicMembership();
        }

        return redirect()->route('segments.show', $segment)
            ->with('success', 'Segment updated successfully.');
    }

    public function destroy(Segment $segment)
    {
        $this->authorizeHost($segment);

        if ($segment->is_system) {
            return redirect()->route('segments.index')
                ->with('error', 'System segments cannot be deleted.');
        }

        // Check if segment is used by any offers
        if ($segment->offers()->exists()) {
            return redirect()->route('segments.show', $segment)
                ->with('error', 'This segment is used by one or more offers. Remove the segment from offers first.');
        }

        $segment->delete();

        return redirect()->route('segments.index')
            ->with('success', 'Segment deleted successfully.');
    }

    /**
     * Add a client to a static segment
     */
    public function addClient(Request $request, Segment $segment)
    {
        $this->authorizeHost($segment);

        if ($segment->type !== Segment::TYPE_STATIC) {
            return back()->with('error', 'Only static segments support manual client management.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $client = Client::find($validated['client_id']);
        if ($client->host_id !== $segment->host_id) {
            abort(403);
        }

        // Add client to segment
        if (!$segment->clients()->where('client_id', $client->id)->exists()) {
            $segment->clients()->attach($client->id, [
                'added_by' => auth()->id(),
            ]);
            $segment->updateMemberCount();
        }

        return back()->with('success', 'Client added to segment.');
    }

    /**
     * Remove a client from a static segment
     */
    public function removeClient(Segment $segment, Client $client)
    {
        $this->authorizeHost($segment);

        if ($segment->type !== Segment::TYPE_STATIC) {
            return back()->with('error', 'Only static segments support manual client management.');
        }

        $segment->clients()->detach($client->id);
        $segment->updateMemberCount();

        return back()->with('success', 'Client removed from segment.');
    }

    /**
     * Refresh dynamic segment membership
     */
    public function refresh(Segment $segment)
    {
        $this->authorizeHost($segment);

        if ($segment->type !== Segment::TYPE_DYNAMIC) {
            return back()->with('error', 'Only dynamic segments can be refreshed.');
        }

        $count = $segment->syncDynamicMembership();

        return back()->with('success', "Segment refreshed. {$count} clients now match.");
    }

    /**
     * Preview clients matching segment rules (before saving)
     */
    public function preview(Request $request)
    {
        $host = auth()->user()->host;

        $rules = $request->input('rules', []);

        if (empty($rules)) {
            return response()->json(['count' => 0, 'sample' => []]);
        }

        // Create a temporary segment to use the query builder
        $tempSegment = new Segment([
            'host_id' => $host->id,
            'type' => Segment::TYPE_DYNAMIC,
        ]);

        // Build query based on rules
        $query = Client::forHost($host->id)->active();

        // Group rules by group_index
        $ruleGroups = collect($rules)->groupBy(fn ($r) => $r['group_index'] ?? 0);

        $query->where(function ($q) use ($ruleGroups) {
            foreach ($ruleGroups as $groupIndex => $groupRules) {
                $q->orWhere(function ($groupQuery) use ($groupRules) {
                    foreach ($groupRules as $ruleData) {
                        if (!empty($ruleData['field']) && !empty($ruleData['operator'])) {
                            $rule = new SegmentRule([
                                'field' => $ruleData['field'],
                                'operator' => $ruleData['operator'],
                                'value' => $ruleData['value'] ?? null,
                            ]);
                            $rule->applyToQuery($groupQuery);
                        }
                    }
                });
            }
        });

        $count = $query->count();
        $sample = $query->limit(5)->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'count' => $count,
            'sample' => $sample,
        ]);
    }

    protected function authorizeHost(Segment $segment): void
    {
        if ($segment->host_id !== auth()->user()->host_id) {
            abort(403, 'Unauthorized');
        }
    }
}
