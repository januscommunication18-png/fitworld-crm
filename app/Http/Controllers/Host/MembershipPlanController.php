<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Host\Traits\SyncsQuestionnaireAttachments;
use App\Http\Requests\Host\MembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipPlanController extends Controller
{
    use SyncsQuestionnaireAttachments;

    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $status = $request->get('status');

        $membershipPlans = $host->membershipPlans()
            ->withCount('classPlans')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $statuses = MembershipPlan::getStatuses();

        return view('host.membership-plans.index', compact('membershipPlans', 'status', 'statuses'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $types = MembershipPlan::getTypes();
        $intervals = MembershipPlan::getIntervals();
        $statuses = MembershipPlan::getStatuses();
        $eligibilityScopes = MembershipPlan::getEligibilityScopes();
        $locationScopes = MembershipPlan::getLocationScopes();
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $locations = $host->locations()->orderBy('name')->get();
        $rentalItems = $host->rentalItems()->where('is_active', true)->orderBy('name')->get();
        $questionnaires = $this->getPublishedQuestionnaires();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.membership-plans.create', compact(
            'host',
            'types',
            'intervals',
            'statuses',
            'eligibilityScopes',
            'locationScopes',
            'classPlans',
            'locations',
            'rentalItems',
            'questionnaires',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(MembershipPlanRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();

        // Generate unique slug
        $data['slug'] = Str::slug($data['name']);
        $counter = 1;
        while ($host->membershipPlans()->where('slug', $data['slug'])->exists()) {
            $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
        }

        // Set default sort order
        $data['sort_order'] = $host->membershipPlans()->max('sort_order') + 1;

        // Handle visibility checkbox
        $data['visibility_public'] = $request->boolean('visibility_public');

        // Handle scheduled class checkbox
        $data['has_scheduled_class'] = $request->boolean('has_scheduled_class');

        // Clear credits_per_cycle if not a credits-based plan
        if ($data['type'] !== MembershipPlan::TYPE_CREDITS) {
            $data['credits_per_cycle'] = null;
        }

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            // Filter out null/empty prices and keep only numeric values
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            // Set legacy price field to default currency price
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['price'] = $data['prices'][$defaultCurrency] ?? 0;
        }

        $membershipPlan = $host->membershipPlans()->create($data);

        // Attach class plans if eligibility is selected
        if ($data['eligibility_scope'] === MembershipPlan::ELIGIBILITY_SELECTED && $request->has('class_plan_ids')) {
            $membershipPlan->classPlans()->attach($request->input('class_plan_ids'));
        }

        // Sync questionnaire attachments
        $this->syncQuestionnaireAttachments($membershipPlan, $request);

        return redirect()->route('catalog.index', ['tab' => 'memberships'])
            ->with('success', 'Membership plan created successfully.');
    }

    public function show(MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);
        $membershipPlan->load('classPlans');

        return view('host.membership-plans.show', compact('membershipPlan'));
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);

        $host = auth()->user()->host;
        $types = MembershipPlan::getTypes();
        $intervals = MembershipPlan::getIntervals();
        $statuses = MembershipPlan::getStatuses();
        $eligibilityScopes = MembershipPlan::getEligibilityScopes();
        $locationScopes = MembershipPlan::getLocationScopes();
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $locations = $host->locations()->orderBy('name')->get();
        $rentalItems = $host->rentalItems()->where('is_active', true)->orderBy('name')->get();
        $selectedClassPlanIds = $membershipPlan->classPlans->pluck('id')->toArray();
        $selectedLocationIds = $membershipPlan->location_ids ?? [];
        $questionnaires = $this->getPublishedQuestionnaires();
        $membershipPlan->load('questionnaireAttachments');

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.membership-plans.edit', compact(
            'host',
            'membershipPlan',
            'types',
            'intervals',
            'statuses',
            'eligibilityScopes',
            'locationScopes',
            'classPlans',
            'locations',
            'rentalItems',
            'selectedClassPlanIds',
            'selectedLocationIds',
            'questionnaires',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function update(MembershipPlanRequest $request, MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);

        $host = auth()->user()->host;
        $data = $request->validated();

        // Update slug if name changed
        if ($data['name'] !== $membershipPlan->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            while ($host->membershipPlans()->where('slug', $data['slug'])->where('id', '!=', $membershipPlan->id)->exists()) {
                $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
            }
        }

        // Handle visibility checkbox
        $data['visibility_public'] = $request->boolean('visibility_public');

        // Handle scheduled class checkbox
        $data['has_scheduled_class'] = $request->boolean('has_scheduled_class');

        // Clear credits_per_cycle if not a credits-based plan
        if ($data['type'] !== MembershipPlan::TYPE_CREDITS) {
            $data['credits_per_cycle'] = null;
        }

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            // Filter out null/empty prices and keep only numeric values
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            // Set legacy price field to default currency price
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['price'] = $data['prices'][$defaultCurrency] ?? 0;
        }

        $membershipPlan->update($data);

        // Sync class plans
        if ($data['eligibility_scope'] === MembershipPlan::ELIGIBILITY_SELECTED) {
            $membershipPlan->classPlans()->sync($request->input('class_plan_ids', []));
        } else {
            $membershipPlan->classPlans()->detach();
        }

        // Sync questionnaire attachments
        $this->syncQuestionnaireAttachments($membershipPlan, $request);

        return redirect()->route('catalog.index', ['tab' => 'memberships'])
            ->with('success', 'Membership plan updated successfully.');
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);

        // TODO: Check if any active subscriptions use this plan
        // if ($membershipPlan->customerMemberships()->active()->exists()) {
        //     return back()->with('error', 'Cannot delete. Active subscriptions exist.');
        // }

        $membershipPlan->delete();

        return redirect()->route('catalog.index', ['tab' => 'memberships'])
            ->with('success', 'Membership plan deleted successfully.');
    }

    public function toggleStatus(MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);

        $newStatus = $membershipPlan->status === MembershipPlan::STATUS_ACTIVE
            ? MembershipPlan::STATUS_DRAFT
            : MembershipPlan::STATUS_ACTIVE;

        $membershipPlan->update(['status' => $newStatus]);

        return back()->with('success', 'Membership plan status updated.');
    }

    public function archive(MembershipPlan $membershipPlan)
    {
        $this->authorizeHost($membershipPlan);
        $membershipPlan->update(['status' => MembershipPlan::STATUS_ARCHIVED]);

        return back()->with('success', 'Membership plan archived.');
    }

    private function authorizeHost(MembershipPlan $membershipPlan): void
    {
        if ($membershipPlan->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
