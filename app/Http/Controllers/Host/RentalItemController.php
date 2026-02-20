<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\RentalItemRequest;
use App\Models\MembershipPlan;
use App\Models\RentalItem;
use App\Models\RentalInventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RentalItemController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $category = $request->get('category');
        $status = $request->get('status');

        $rentalItems = $host->rentalItems()
            ->withCount('bookings')
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($status === 'low_stock', fn($q) => $q->where('available_inventory', '<=', 5)->where('available_inventory', '>', 0))
            ->when($status === 'out_of_stock', fn($q) => $q->where('available_inventory', '<=', 0))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = RentalItem::getCategories();

        return view('host.rentals.index', compact('rentalItems', 'category', 'status', 'categories'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $categories = RentalItem::getCategories();
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $classPacks = $host->classPacks()->where('status', 'active')->orderBy('name')->get();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.rentals.create', compact(
            'categories',
            'classPlans',
            'membershipPlans',
            'classPacks',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(RentalItemRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();

        // Set default sort order
        $data['sort_order'] = $host->rentalItems()->max('sort_order') + 1;

        // Handle boolean
        $data['is_active'] = $request->boolean('is_active', true);
        $data['requires_return'] = $request->boolean('requires_return', true);

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
        }

        if (isset($data['deposit_prices'])) {
            $data['deposit_prices'] = array_filter($data['deposit_prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['deposit_amount'] = $data['deposit_prices'][$defaultCurrency] ?? 0;
        }

        // Set available inventory equal to total on creation
        $data['available_inventory'] = $data['total_inventory'] ?? 0;

        // Handle image uploads
        $data['images'] = $this->handleImageUploads($request, $host);

        $rentalItem = $host->rentalItems()->create($data);

        // Sync class plans if provided
        if ($request->has('class_plan_ids')) {
            $classPlansData = [];
            foreach ($request->input('class_plan_ids', []) as $classPlanId) {
                $classPlansData[$classPlanId] = [
                    'is_required' => in_array($classPlanId, $request->input('required_class_plan_ids', [])),
                ];
            }
            $rentalItem->classPlans()->sync($classPlansData);
        }

        // Handle eligibility
        $this->syncEligibility($rentalItem, $request);

        // Log initial inventory
        if ($rentalItem->total_inventory > 0) {
            RentalInventoryLog::create([
                'rental_item_id' => $rentalItem->id,
                'action' => 'restock',
                'quantity_change' => $rentalItem->total_inventory,
                'inventory_after' => $rentalItem->total_inventory,
                'notes' => 'Initial inventory',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('rentals.index')
            ->with('success', 'Rental item created successfully.');
    }

    public function show(RentalItem $rental)
    {
        $this->authorizeHost($rental);
        $rental->load(['classPlans', 'eligibility.membershipPlan', 'eligibility.classPack']);

        $recentBookings = $rental->bookings()
            ->with(['client', 'transaction'])
            ->latest()
            ->take(10)
            ->get();

        $inventoryLogs = $rental->inventoryLogs()
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('host.rentals.show', compact('rental', 'recentBookings', 'inventoryLogs'));
    }

    public function edit(RentalItem $rental)
    {
        $this->authorizeHost($rental);

        $host = auth()->user()->host;
        $categories = RentalItem::getCategories();
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $classPacks = $host->classPacks()->where('status', 'active')->orderBy('name')->get();

        $selectedClassPlanIds = $rental->classPlans->pluck('id')->toArray();
        $requiredClassPlanIds = $rental->classPlans->where('pivot.is_required', true)->pluck('id')->toArray();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        $rental->load(['eligibility.membershipPlan', 'eligibility.classPack']);

        return view('host.rentals.edit', compact(
            'rental',
            'categories',
            'classPlans',
            'membershipPlans',
            'classPacks',
            'selectedClassPlanIds',
            'requiredClassPlanIds',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function update(RentalItemRequest $request, RentalItem $rental)
    {
        $this->authorizeHost($rental);

        $host = auth()->user()->host;
        $data = $request->validated();

        // Handle boolean
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_return'] = $request->boolean('requires_return');

        // Handle multi-currency prices
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
        }

        if (isset($data['deposit_prices'])) {
            $data['deposit_prices'] = array_filter($data['deposit_prices'], fn($price) => $price !== null && $price !== '');
            $defaultCurrency = $host->default_currency ?? 'USD';
            $data['deposit_amount'] = $data['deposit_prices'][$defaultCurrency] ?? 0;
        }

        // Handle inventory change
        $inventoryChange = ($data['total_inventory'] ?? $rental->total_inventory) - $rental->total_inventory;
        if ($inventoryChange != 0) {
            $data['available_inventory'] = $rental->available_inventory + $inventoryChange;

            RentalInventoryLog::create([
                'rental_item_id' => $rental->id,
                'action' => $inventoryChange > 0 ? 'restock' : 'adjustment',
                'quantity_change' => $inventoryChange,
                'inventory_after' => $data['available_inventory'],
                'notes' => $inventoryChange > 0 ? 'Inventory restocked' : 'Inventory adjusted',
                'user_id' => auth()->id(),
            ]);
        }

        // Handle image uploads
        $newImages = $this->handleImageUploads($request, $host);
        if (!empty($newImages)) {
            $existingImages = $rental->images ?? [];
            $data['images'] = array_merge($existingImages, $newImages);
        }

        // Handle image deletions
        if ($request->has('delete_images')) {
            $imagesToDelete = $request->input('delete_images', []);
            $currentImages = $rental->images ?? [];
            $data['images'] = array_values(array_diff($currentImages, $imagesToDelete));

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $rental->update($data);

        // Sync class plans
        if ($request->has('class_plan_ids')) {
            $classPlansData = [];
            foreach ($request->input('class_plan_ids', []) as $classPlanId) {
                $classPlansData[$classPlanId] = [
                    'is_required' => in_array($classPlanId, $request->input('required_class_plan_ids', [])),
                ];
            }
            $rental->classPlans()->sync($classPlansData);
        } else {
            $rental->classPlans()->detach();
        }

        // Handle eligibility
        $this->syncEligibility($rental, $request);

        return redirect()->route('rentals.index')
            ->with('success', 'Rental item updated successfully.');
    }

    public function destroy(RentalItem $rental)
    {
        $this->authorizeHost($rental);

        // Check for active bookings
        if ($rental->bookings()->active()->exists()) {
            return back()->with('error', 'Cannot delete. Active rentals exist for this item.');
        }

        // Delete images
        if (!empty($rental->images)) {
            foreach ($rental->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $rental->delete();

        return redirect()->route('rentals.index')
            ->with('success', 'Rental item deleted successfully.');
    }

    public function toggleStatus(RentalItem $rental)
    {
        $this->authorizeHost($rental);

        $rental->update(['is_active' => !$rental->is_active]);

        return back()->with('success', 'Rental item status updated.');
    }

    public function adjustInventory(Request $request, RentalItem $rental)
    {
        $this->authorizeHost($rental);

        $request->validate([
            'adjustment' => 'required|integer',
            'notes' => 'required|string|max:500',
        ]);

        $adjustment = (int) $request->input('adjustment');
        $newAvailable = $rental->available_inventory + $adjustment;
        $newTotal = $rental->total_inventory + $adjustment;

        if ($newAvailable < 0 || $newTotal < 0) {
            return back()->with('error', 'Adjustment would result in negative inventory.');
        }

        RentalInventoryLog::create([
            'rental_item_id' => $rental->id,
            'action' => 'adjustment',
            'quantity_change' => $adjustment,
            'inventory_after' => $newAvailable,
            'notes' => $request->input('notes'),
            'user_id' => auth()->id(),
        ]);

        $rental->update([
            'available_inventory' => $newAvailable,
            'total_inventory' => $newTotal,
        ]);

        return back()->with('success', 'Inventory adjusted successfully.');
    }

    private function authorizeHost(RentalItem $rental): void
    {
        if ($rental->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }

    private function handleImageUploads(Request $request, $host): array
    {
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store("hosts/{$host->id}/rentals", 'public');
                $images[] = $path;
            }
        }

        return $images;
    }

    private function syncEligibility(RentalItem $rental, Request $request): void
    {
        // Delete existing eligibility
        $rental->eligibility()->delete();

        $eligibilityType = $request->input('eligibility_type', 'all');

        if ($eligibilityType === 'all') {
            $rental->eligibility()->create([
                'eligible_type' => 'all',
            ]);
        } elseif ($eligibilityType === 'membership') {
            foreach ($request->input('eligible_membership_ids', []) as $membershipId) {
                $rental->eligibility()->create([
                    'eligible_type' => 'membership',
                    'membership_plan_id' => $membershipId,
                    'is_free' => in_array($membershipId, $request->input('free_membership_ids', [])),
                ]);
            }
        } elseif ($eligibilityType === 'class_pack') {
            foreach ($request->input('eligible_class_pack_ids', []) as $packId) {
                $rental->eligibility()->create([
                    'eligible_type' => 'class_pack',
                    'class_pack_id' => $packId,
                    'is_free' => in_array($packId, $request->input('free_class_pack_ids', [])),
                ]);
            }
        }
    }
}
