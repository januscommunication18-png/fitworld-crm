<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\ClassPassRequest;
use App\Models\ClassPass;
use App\Models\ClassPassPurchase;
use App\Models\ClassPlan;
use App\Models\MembershipPlan;
use App\Services\ClassPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClassPassController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $status = $request->get('status');

        $classPasses = $host->classPasses()
            ->withCount('purchases')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $statuses = ClassPass::getStatuses();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';

        return redirect()->route('catalog.index', ['tab' => 'class-passes']);
    }

    public function create()
    {
        $host = auth()->user()->host;

        // Get all the data needed for the form
        $statuses = ClassPass::getStatuses();
        $activationTypes = ClassPass::getActivationTypes();
        $eligibilityTypes = ClassPass::getEligibilityTypes();
        $validityTypes = ClassPass::getValidityTypes();
        $validityPresets = ClassPass::getValidityPresets();
        $renewalIntervals = ClassPass::getRenewalIntervals();
        $classTypes = ClassPass::getClassTypes();

        // Related data for eligibility selections
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->where('is_active', true)->orderBy('name')->get();
        $instructors = $host->instructors()->where('status', 'active')->orderBy('name')->get();
        $locations = $host->locations()->orderBy('name')->get();

        // Class categories from ClassPlan
        $classCategories = ClassPlan::getCategories();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.catalog.class-passes.create', compact(
            'host',
            'statuses',
            'activationTypes',
            'eligibilityTypes',
            'validityTypes',
            'validityPresets',
            'renewalIntervals',
            'classTypes',
            'classPlans',
            'servicePlans',
            'instructors',
            'locations',
            'classCategories',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(ClassPassRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();

        // Set default sort order
        $data['sort_order'] = $host->classPasses()->max('sort_order') + 1;

        // Handle visibility checkbox
        $data['visibility_public'] = $request->boolean('visibility_public');

        // Handle boolean fields
        $data['allow_admin_extension'] = $request->boolean('allow_admin_extension', true);
        $data['allow_freeze'] = $request->boolean('allow_freeze');
        $data['allow_transfer'] = $request->boolean('allow_transfer');
        $data['allow_family_sharing'] = $request->boolean('allow_family_sharing');
        $data['allow_gifting'] = $request->boolean('allow_gifting');
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['rollover_enabled'] = $request->boolean('rollover_enabled');

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['price'] = $data['prices'][$defaultCurrency] ?? 0;
        }

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle reactivation fee prices
        if (isset($data['reactivation_fee_prices'])) {
            $data['reactivation_fee_prices'] = array_filter($data['reactivation_fee_prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['reactivation_fee'] = $data['reactivation_fee_prices'][$defaultCurrency] ?? 0;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store(
                $host->getStoragePath('class-passes'),
                config('filesystems.uploads')
            );
            $data['image_path'] = $path;
        }

        // Set expires_after_days for backward compatibility
        if ($data['validity_type'] === ClassPass::VALIDITY_DAYS) {
            $data['expires_after_days'] = $data['validity_value'];
        } elseif ($data['validity_type'] === ClassPass::VALIDITY_MONTHS) {
            $data['expires_after_days'] = $data['validity_value'] * 30; // Approximate
        } else {
            $data['expires_after_days'] = null;
        }

        // Clear eligibility fields based on type
        $this->clearIrrelevantEligibilityFields($data);

        // Clear recurring fields if not recurring
        if (!$data['is_recurring']) {
            $data['renewal_interval'] = null;
            $data['rollover_enabled'] = false;
            $data['max_rollover_credits'] = 0;
            $data['max_rollover_periods'] = 0;
        }

        $classPass = $host->classPasses()->create($data);

        return redirect()->route('catalog.index', ['tab' => 'class-passes'])
            ->with('success', 'Class pass created successfully.');
    }

    public function show(ClassPass $classPass, Request $request)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->host;
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        // Tab support
        $tab = $request->get('tab', 'overview');

        // Load purchases for purchases tab
        $purchases = $classPass->purchases()
            ->with(['client', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Stats
        $stats = [
            'total_purchases' => $classPass->purchases()->count(),
            'active_purchases' => $classPass->activePurchases()->count(),
            'total_credits_remaining' => $classPass->activePurchases()->sum('classes_remaining'),
            'total_revenue' => $classPass->purchases()->count() * ($classPass->price ?? 0),
        ];

        return view('host.catalog.class-passes.show', compact(
            'classPass',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols',
            'tab',
            'purchases',
            'stats'
        ));
    }

    public function edit(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->host;

        // Get all the data needed for the form
        $statuses = ClassPass::getStatuses();
        $activationTypes = ClassPass::getActivationTypes();
        $eligibilityTypes = ClassPass::getEligibilityTypes();
        $validityTypes = ClassPass::getValidityTypes();
        $validityPresets = ClassPass::getValidityPresets();
        $renewalIntervals = ClassPass::getRenewalIntervals();
        $classTypes = ClassPass::getClassTypes();

        // Related data for eligibility selections
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->where('is_active', true)->orderBy('name')->get();
        $instructors = $host->instructors()->where('status', 'active')->orderBy('name')->get();
        $locations = $host->locations()->orderBy('name')->get();

        // Class categories from ClassPlan
        $classCategories = ClassPlan::getCategories();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.catalog.class-passes.edit', compact(
            'host',
            'classPass',
            'statuses',
            'activationTypes',
            'eligibilityTypes',
            'validityTypes',
            'validityPresets',
            'renewalIntervals',
            'classTypes',
            'classPlans',
            'servicePlans',
            'instructors',
            'locations',
            'classCategories',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function update(ClassPassRequest $request, ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->host;
        $data = $request->validated();

        // Handle visibility checkbox
        $data['visibility_public'] = $request->boolean('visibility_public');

        // Handle boolean fields
        $data['allow_admin_extension'] = $request->boolean('allow_admin_extension', true);
        $data['allow_freeze'] = $request->boolean('allow_freeze');
        $data['allow_transfer'] = $request->boolean('allow_transfer');
        $data['allow_family_sharing'] = $request->boolean('allow_family_sharing');
        $data['allow_gifting'] = $request->boolean('allow_gifting');
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['rollover_enabled'] = $request->boolean('rollover_enabled');

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['price'] = $data['prices'][$defaultCurrency] ?? 0;
        }

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle reactivation fee prices
        if (isset($data['reactivation_fee_prices'])) {
            $data['reactivation_fee_prices'] = array_filter($data['reactivation_fee_prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['reactivation_fee'] = $data['reactivation_fee_prices'][$defaultCurrency] ?? 0;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($classPass->image_path) {
                Storage::disk(config('filesystems.uploads'))->delete($classPass->image_path);
            }

            $path = $request->file('image')->store(
                $host->getStoragePath('class-passes'),
                config('filesystems.uploads')
            );
            $data['image_path'] = $path;
        }

        // Handle image removal
        if ($request->boolean('remove_image') && $classPass->image_path) {
            Storage::disk(config('filesystems.uploads'))->delete($classPass->image_path);
            $data['image_path'] = null;
        }

        // Set expires_after_days for backward compatibility
        if ($data['validity_type'] === ClassPass::VALIDITY_DAYS) {
            $data['expires_after_days'] = $data['validity_value'];
        } elseif ($data['validity_type'] === ClassPass::VALIDITY_MONTHS) {
            $data['expires_after_days'] = $data['validity_value'] * 30;
        } else {
            $data['expires_after_days'] = null;
        }

        // Clear eligibility fields based on type
        $this->clearIrrelevantEligibilityFields($data);

        // Clear recurring fields if not recurring
        if (!$data['is_recurring']) {
            $data['renewal_interval'] = null;
            $data['rollover_enabled'] = false;
            $data['max_rollover_credits'] = 0;
            $data['max_rollover_periods'] = 0;
        }

        $classPass->update($data);

        return redirect()->route('catalog.index', ['tab' => 'class-passes'])
            ->with('success', 'Class pass updated successfully.');
    }

    public function destroy(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        // Check if any active purchases exist
        if ($classPass->activePurchases()->exists()) {
            return back()->with('error', 'Cannot delete. Active pass purchases exist. Archive instead.');
        }

        // Delete image
        if ($classPass->image_path) {
            Storage::disk(config('filesystems.uploads'))->delete($classPass->image_path);
        }

        $classPass->delete();

        return redirect()->route('catalog.index', ['tab' => 'class-passes'])
            ->with('success', 'Class pass deleted successfully.');
    }

    public function toggleStatus(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $newStatus = $classPass->status === ClassPass::STATUS_ACTIVE
            ? ClassPass::STATUS_ARCHIVED
            : ClassPass::STATUS_ACTIVE;

        $classPass->update(['status' => $newStatus]);

        return back()->with('success', 'Class pass status updated.');
    }

    public function archive(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);
        $classPass->archive();

        return back()->with('success', 'Class pass archived.');
    }

    public function reorder(Request $request)
    {
        $host = auth()->user()->host;

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:class_passes,id',
        ]);

        foreach ($request->order as $index => $id) {
            $host->classPasses()->where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function duplicate(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->host;

        // Clone the pass
        $newPass = $classPass->replicate();
        $newPass->name = $classPass->name . ' (Copy)';
        $newPass->status = ClassPass::STATUS_ARCHIVED; // Start as draft/archived
        $newPass->sort_order = $host->classPasses()->max('sort_order') + 1;
        $newPass->stripe_product_id = null;
        $newPass->stripe_price_id = null;
        $newPass->save();

        return redirect()->route('class-passes.edit', $newPass)
            ->with('success', 'Class pass duplicated. Make your changes and save.');
    }

    public function purchases(ClassPass $classPass, Request $request)
    {
        $this->authorizeHost($classPass);

        $status = $request->get('status');

        $purchases = $classPass->purchases()
            ->with(['client', 'createdBy'])
            ->when($status === 'active', fn($q) => $q->usable())
            ->when($status === 'expired', fn($q) => $q->expired())
            ->when($status === 'exhausted', fn($q) => $q->exhausted())
            ->when($status === 'frozen', fn($q) => $q->frozen())
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('host.catalog.class-passes.purchases', compact('classPass', 'purchases', 'status'));
    }

    /**
     * Clear eligibility fields that don't apply based on eligibility_type
     */
    private function clearIrrelevantEligibilityFields(array &$data): void
    {
        switch ($data['eligibility_type']) {
            case ClassPass::ELIGIBILITY_ALL:
                $data['eligible_class_plan_ids'] = null;
                $data['eligible_categories'] = null;
                $data['eligible_instructor_ids'] = null;
                $data['eligible_location_ids'] = null;
                break;
            case ClassPass::ELIGIBILITY_CLASS_PLANS:
                $data['eligible_categories'] = null;
                $data['eligible_instructor_ids'] = null;
                $data['eligible_location_ids'] = null;
                break;
            case ClassPass::ELIGIBILITY_CATEGORIES:
                $data['eligible_class_plan_ids'] = null;
                $data['eligible_instructor_ids'] = null;
                $data['eligible_location_ids'] = null;
                break;
            case ClassPass::ELIGIBILITY_INSTRUCTORS:
                $data['eligible_class_plan_ids'] = null;
                $data['eligible_categories'] = null;
                $data['eligible_location_ids'] = null;
                break;
            case ClassPass::ELIGIBILITY_LOCATIONS:
                $data['eligible_class_plan_ids'] = null;
                $data['eligible_categories'] = null;
                $data['eligible_instructor_ids'] = null;
                break;
        }
    }

    /**
     * Show form to sell a class pass to a client
     */
    public function sellForm(ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->currentHost();
        $clients = $host->clients()->orderBy('first_name')->orderBy('last_name')->get();
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.catalog.class-passes.sell', compact(
            'classPass',
            'clients',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    /**
     * Sell a class pass to a client
     */
    public function sell(Request $request, ClassPass $classPass)
    {
        $this->authorizeHost($classPass);

        $host = auth()->user()->host;

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_method' => 'required|in:cash,card,check,other,comp',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $client = \App\Models\Client::where('id', $validated['client_id'])
            ->where('host_id', $host->id)
            ->firstOrFail();

        // Create the purchase using ClassPassService
        $classPassService = app(\App\Services\ClassPassService::class);

        $purchase = $classPassService->purchasePass($host, $client, $classPass, [
            'payment_method' => $validated['payment_method'],
            'amount_paid' => $validated['amount_paid'] ?? $classPass->price,
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('class-passes.show', $classPass)
            ->with('success', "Class pass sold to {$client->name}. They now have {$purchase->classes_remaining} credits.");
    }

    /**
     * Manually activate a class pass purchase
     */
    public function activatePurchase(ClassPassPurchase $purchase, ClassPassService $classPassService)
    {
        $this->authorizePurchaseHost($purchase);

        if ($purchase->is_activated) {
            return back()->with('error', 'This pass is already activated.');
        }

        try {
            $classPassService->manuallyActivatePass($purchase);

            return back()->with('success', "Pass activated successfully. Expires on {$purchase->fresh()->expires_at->format('M d, Y')}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function authorizePurchaseHost(ClassPassPurchase $purchase): void
    {
        if ($purchase->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }

    private function authorizeHost(ClassPass $classPass): void
    {
        if ($classPass->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
