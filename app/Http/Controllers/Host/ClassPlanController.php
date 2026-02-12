<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\ClassPlanRequest;
use App\Models\ClassPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClassPlanController extends Controller
{
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
        $categories = ClassPlan::getCategories();
        $types = ClassPlan::getTypes();
        $difficultyLevels = ClassPlan::getDifficultyLevels();

        return view('host.class-plans.create', compact('categories', 'types', 'difficultyLevels'));
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

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly($host->getStoragePath('class-plans'), config('filesystems.uploads'));
        }

        // Set default sort order
        $data['sort_order'] = $host->classPlans()->max('sort_order') + 1;

        $classPlan = $host->classPlans()->create($data);

        return redirect()->route('catalog.index', ['tab' => 'classes'])
            ->with('success', 'Class plan created successfully.');
    }

    public function show(ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        return view('host.class-plans.show', compact('classPlan'));
    }

    public function edit(ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        $categories = ClassPlan::getCategories();
        $types = ClassPlan::getTypes();
        $difficultyLevels = ClassPlan::getDifficultyLevels();

        return view('host.class-plans.edit', compact('classPlan', 'categories', 'types', 'difficultyLevels'));
    }

    public function update(ClassPlanRequest $request, ClassPlan $classPlan)
    {
        $this->authorizeHost($classPlan);

        $data = $request->validated();

        // Update slug if name changed
        if ($data['name'] !== $classPlan->name) {
            $host = auth()->user()->host;
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
