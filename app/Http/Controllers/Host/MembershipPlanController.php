<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Host\Traits\SyncsQuestionnaireAttachments;
use App\Http\Requests\Host\MembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Clear credits_per_cycle if not a credits-based plan
        if ($data['type'] !== MembershipPlan::TYPE_CREDITS) {
            $data['credits_per_cycle'] = null;
        }

        $defaultCurrency = $host->default_currency ?? 'USD';

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle billing discounts from multi-currency inputs
        // The 1-month row is the base price — derive prices from it
        $billingDiscounts = [];
        foreach (['1' => 'billing_discounts_1mo', '3' => 'billing_discounts_3mo', '6' => 'billing_discounts_6mo', '9' => 'billing_discounts_9mo', '12' => 'billing_discounts_12mo'] as $months => $field) {
            if (isset($data[$field])) {
                $billingDiscounts[$months] = array_filter($data[$field], fn($v) => $v !== null && $v !== '');
                unset($data[$field]);
            }
        }
        if (!empty($billingDiscounts)) {
            $data['billing_discounts'] = $billingDiscounts;
        }

        // Derive prices from the 1-month billing discount (base price)
        if (!empty($billingDiscounts['1'])) {
            $data['prices'] = $billingDiscounts['1'];
            $data['price'] = $billingDiscounts['1'][$defaultCurrency] ?? 0;
        }

        // Handle multi-currency registration/cancellation fees
        if (isset($data['registration_fees'])) {
            $data['registration_fees'] = array_filter($data['registration_fees'], fn($v) => $v !== null && $v !== '');
            $data['registration_fee'] = $data['registration_fees'][$defaultCurrency] ?? null;
        }
        if (isset($data['cancellation_fees'])) {
            $data['cancellation_fees'] = array_filter($data['cancellation_fees'], fn($v) => $v !== null && $v !== '');
            $data['cancellation_fee'] = $data['cancellation_fees'][$defaultCurrency] ?? null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly($host->getStoragePath('membership-plans'), config('filesystems.uploads'));
        }

        // Handle file attachments
        if ($request->hasFile('file_attachments')) {
            $attachments = [];
            foreach ($request->file('file_attachments') as $file) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->storePublicly($host->getStoragePath('membership-plans/files'), config('filesystems.uploads')),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
            $data['file_attachments'] = $attachments;
        }

        $membershipPlan = $host->membershipPlans()->create($data);

        // Attach class plans based on eligibility scope
        if ($data['eligibility_scope'] === MembershipPlan::ELIGIBILITY_SELECTED && $request->has('class_plan_ids')) {
            $membershipPlan->classPlans()->attach($request->input('class_plan_ids', []));
        }

        // Sync questionnaire attachments
        $this->syncQuestionnaireAttachments($membershipPlan, $request);

        return redirect()->route('catalog.index', ['tab' => 'memberships'])
            ->with('success', 'Membership plan created successfully.');
    }

    public function show(MembershipPlan $membershipPlan, Request $request)
    {
        $this->authorizeHost($membershipPlan);
        $membershipPlan->load('classPlans');

        $host = auth()->user()->host;
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        // Tab support
        $tab = $request->get('tab', 'overview');

        // Load upcoming sessions specifically linked to this membership plan (via pivot table)
        $upcomingSessions = $membershipPlan->classSessions()
            ->where('host_id', $host->id)
            ->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled')
            ->with(['classPlan', 'primaryInstructor', 'location', 'room'])
            ->orderBy('start_time')
            ->get();

        // Get unique locations from the sessions
        $locations = $upcomingSessions->pluck('location')->filter()->unique('id')->sortBy('name')->values();

        // Group sessions by location
        $sessionsByLocation = $upcomingSessions->groupBy('location_id');

        return view('host.membership-plans.show', compact(
            'membershipPlan',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols',
            'tab',
            'locations',
            'sessionsByLocation'
        ));
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

        // Clear credits_per_cycle if not a credits-based plan
        if ($data['type'] !== MembershipPlan::TYPE_CREDITS) {
            $data['credits_per_cycle'] = null;
        }

        $defaultCurrency = $host->default_currency ?? 'USD';

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle billing discounts from multi-currency inputs
        // The 1-month row is the base price — derive prices from it
        $billingDiscounts = [];
        foreach (['1' => 'billing_discounts_1mo', '3' => 'billing_discounts_3mo', '6' => 'billing_discounts_6mo', '9' => 'billing_discounts_9mo', '12' => 'billing_discounts_12mo'] as $months => $field) {
            if (isset($data[$field])) {
                $billingDiscounts[$months] = array_filter($data[$field], fn($v) => $v !== null && $v !== '');
                unset($data[$field]);
            }
        }
        if (!empty($billingDiscounts)) {
            $data['billing_discounts'] = $billingDiscounts;
        }

        // Derive prices from the 1-month billing discount (base price)
        if (!empty($billingDiscounts['1'])) {
            $data['prices'] = $billingDiscounts['1'];
            $data['price'] = $billingDiscounts['1'][$defaultCurrency] ?? 0;
        }

        // Handle multi-currency registration/cancellation fees
        if (isset($data['registration_fees'])) {
            $data['registration_fees'] = array_filter($data['registration_fees'], fn($v) => $v !== null && $v !== '');
            $data['registration_fee'] = $data['registration_fees'][$defaultCurrency] ?? null;
        }
        if (isset($data['cancellation_fees'])) {
            $data['cancellation_fees'] = array_filter($data['cancellation_fees'], fn($v) => $v !== null && $v !== '');
            $data['cancellation_fee'] = $data['cancellation_fees'][$defaultCurrency] ?? null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($membershipPlan->image_path) {
                Storage::disk(config('filesystems.uploads'))->delete($membershipPlan->image_path);
            }
            $data['image_path'] = $request->file('image')->storePublicly($host->getStoragePath('membership-plans'), config('filesystems.uploads'));
        }

        // Handle file attachments (append to existing)
        if ($request->hasFile('file_attachments')) {
            $existing = $membershipPlan->file_attachments ?? [];
            foreach ($request->file('file_attachments') as $file) {
                $existing[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->storePublicly($host->getStoragePath('membership-plans/files'), config('filesystems.uploads')),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
            $data['file_attachments'] = $existing;
        }

        $membershipPlan->update($data);

        // Sync class plans based on eligibility scope
        if ($data['eligibility_scope'] === MembershipPlan::ELIGIBILITY_SELECTED) {
            $membershipPlan->classPlans()->sync($request->input('class_plan_ids', []));
        } else {
            $membershipPlan->classPlans()->sync([]);
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
