<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Host\Traits\SyncsQuestionnaireAttachments;
use App\Http\Requests\Host\ClassPlanRequest;
use App\Models\ClassPlan;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClassPlanController extends Controller
{
    use SyncsQuestionnaireAttachments;

    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $category = $request->get('category');

        $classPlans = $host->classPlans()
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = ClassPlan::getCategories();

        return view('host.class-plans.index', compact('classPlans', 'category', 'categories'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $categories = ClassPlan::getCategories();
        $types = ClassPlan::getTypes();
        $difficultyLevels = ClassPlan::getDifficultyLevels();
        $questionnaires = $this->getPublishedQuestionnaires();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.class-plans.create', compact(
            'categories',
            'types',
            'difficultyLevels',
            'questionnaires',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(ClassPlanRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();

        // Generate unique slug
        $data['slug'] = Str::slug($data['name']);
        $counter = 1;
        while ($host->classPlans()->where('slug', $data['slug'])->exists()) {
            $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
        }

        // Handle equipment_needed as array
        if (isset($data['equipment_needed']) && is_string($data['equipment_needed'])) {
            $data['equipment_needed'] = array_filter(array_map('trim', explode(',', $data['equipment_needed'])));
        }

        // Handle multi-currency prices
        $defaultCurrency = $host->default_currency ?? 'USD';
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            $data['default_price'] = $data['prices'][$defaultCurrency] ?? null;
        }

        if (isset($data['drop_in_prices'])) {
            $data['drop_in_prices'] = array_filter($data['drop_in_prices'], fn($price) => $price !== null && $price !== '');
            $data['drop_in_price'] = $data['drop_in_prices'][$defaultCurrency] ?? null;
        }

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        if (isset($data['new_member_drop_in_prices'])) {
            $data['new_member_drop_in_prices'] = array_filter($data['new_member_drop_in_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly($host->getStoragePath('class-plans'), config('filesystems.uploads'));
        }

        // Set default sort order
        $data['sort_order'] = $host->classPlans()->max('sort_order') + 1;

        $classPlan = $host->classPlans()->create($data);

        // Sync questionnaire attachments
        $this->syncQuestionnaireAttachments($classPlan, $request);

        return redirect()->route('catalog.index', ['tab' => 'classes'])
            ->with('success', 'Class plan created successfully.');
    }

    public function show(ClassPlan $classPlan, Request $request)
    {
        $this->authorizeHost($classPlan);

        $host = auth()->user()->host;
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        // Tab support
        $tab = $request->get('tab', 'overview');

        // Load locations that have sessions for this class plan (scoped to host)
        $locations = $host->locations()
            ->whereHas('classSessions', function ($q) use ($classPlan, $host) {
                $q->where('class_plan_id', $classPlan->id)
                    ->where('host_id', $host->id);
            })
            ->orderBy('name')
            ->get();

        // Load upcoming sessions grouped by location (scoped to host)
        $sessionsByLocation = $classPlan->sessions()
            ->where('host_id', $host->id)
            ->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled')
            ->with(['primaryInstructor', 'location', 'room'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('location_id');

        return view('host.class-plans.show', compact(
            'classPlan',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols',
            'tab',
            'locations',
            'sessionsByLocation'
        ));
    }

    public function edit(ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        $host = auth()->user()->host;
        $categories = ClassPlan::getCategories();
        $types = ClassPlan::getTypes();
        $difficultyLevels = ClassPlan::getDifficultyLevels();
        $questionnaires = $this->getPublishedQuestionnaires();
        $classPlan->load('questionnaireAttachments');

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.class-plans.edit', compact(
            'classPlan',
            'categories',
            'types',
            'difficultyLevels',
            'questionnaires',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function update(ClassPlanRequest $request, ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        $host = auth()->user()->host;
        $data = $request->validated();

        // Update slug if name changed
        if ($data['name'] !== $classPlan->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            while ($host->classPlans()->where('slug', $data['slug'])->where('id', '!=', $classPlan->id)->exists()) {
                $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
            }
        }

        // Handle equipment_needed as array
        if (isset($data['equipment_needed']) && is_string($data['equipment_needed'])) {
            $data['equipment_needed'] = array_filter(array_map('trim', explode(',', $data['equipment_needed'])));
        }

        // Handle multi-currency prices
        $defaultCurrency = $host->default_currency ?? 'USD';
        if (isset($data['prices'])) {
            $data['prices'] = array_filter($data['prices'], fn($price) => $price !== null && $price !== '');
            $data['default_price'] = $data['prices'][$defaultCurrency] ?? null;
        }

        if (isset($data['drop_in_prices'])) {
            $data['drop_in_prices'] = array_filter($data['drop_in_prices'], fn($price) => $price !== null && $price !== '');
            $data['drop_in_price'] = $data['drop_in_prices'][$defaultCurrency] ?? null;
        }

        // Handle new member prices
        if (isset($data['new_member_prices'])) {
            $data['new_member_prices'] = array_filter($data['new_member_prices'], fn($price) => $price !== null && $price !== '');
        }

        if (isset($data['new_member_drop_in_prices'])) {
            $data['new_member_drop_in_prices'] = array_filter($data['new_member_drop_in_prices'], fn($price) => $price !== null && $price !== '');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image (try-catch for cloud storage compatibility)
            if ($classPlan->image_path) {
                try {
                    Storage::disk(config('filesystems.uploads'))->delete($classPlan->image_path);
                } catch (\Exception $e) {
                    // Ignore deletion errors
                }
            }
            $data['image_path'] = $request->file('image')->storePublicly($host->getStoragePath('class-plans'), config('filesystems.uploads'));
        }

        // Handle checkbox for is_active and is_visible
        $data['is_active'] = $request->boolean('is_active');
        $data['is_visible_on_booking_page'] = $request->boolean('is_visible_on_booking_page');

        $classPlan->update($data);

        // Sync questionnaire attachments
        $this->syncQuestionnaireAttachments($classPlan, $request);

        return redirect()->route('catalog.index', ['tab' => 'classes'])
            ->with('success', 'Class plan updated successfully.');
    }

    public function destroy(ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        // Check if any scheduled classes use this plan
        if ($classPlan->scheduledClasses()->exists()) {
            return back()->with('error', 'Cannot delete this class plan. It has scheduled classes associated with it.');
        }

        // Delete image
        if ($classPlan->image_path) {
            Storage::disk(config('filesystems.uploads'))->delete($classPlan->image_path);
        }

        $classPlan->delete();

        return redirect()->route('catalog.index', ['tab' => 'classes'])
            ->with('success', 'Class plan deleted successfully.');
    }

    public function toggleActive(ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        $classPlan->update(['is_active' => !$classPlan->is_active]);

        return back()->with('success', 'Class plan ' . ($classPlan->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }

    public function reorder(Request $request)
    {
        $host = auth()->user()->host;
        $order = $request->input('order', []);

        foreach ($order as $position => $id) {
            $host->classPlans()->where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true]);
    }

    private function authorizeHost(ClassPlan $classPlan): void
    {
        if ($classPlan->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
