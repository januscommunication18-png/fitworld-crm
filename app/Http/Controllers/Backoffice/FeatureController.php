<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');

        $features = Feature::when($category !== 'all', fn($q) => $q->where('category', $category))
            ->ordered()
            ->get();

        $categories = Feature::getCategories();

        return view('backoffice.features.index', compact('features', 'category', 'categories'));
    }

    public function create()
    {
        $types = Feature::getTypes();
        $categories = Feature::getCategories();

        return view('backoffice.features.create', compact('types', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:features,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'type' => 'required|in:free,premium',
            'category' => 'required|string|max:50',
            'is_active' => 'boolean',
            'config_schema' => 'nullable|string',
            'default_config' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Parse JSON fields
        $validated['config_schema'] = $this->parseJson($validated['config_schema'] ?? null);
        $validated['default_config'] = $this->parseJson($validated['default_config'] ?? null);

        Feature::create($validated);

        return redirect()->route('backoffice.features.index')
            ->with('success', 'Feature created successfully.');
    }

    public function edit(Feature $feature)
    {
        $types = Feature::getTypes();
        $categories = Feature::getCategories();

        return view('backoffice.features.edit', compact('feature', 'types', 'categories'));
    }

    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:features,slug,' . $feature->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'type' => 'required|in:free,premium',
            'category' => 'required|string|max:50',
            'is_active' => 'boolean',
            'config_schema' => 'nullable|string',
            'default_config' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Parse JSON fields
        $validated['config_schema'] = $this->parseJson($validated['config_schema'] ?? null);
        $validated['default_config'] = $this->parseJson($validated['default_config'] ?? null);

        $feature->update($validated);

        return redirect()->route('backoffice.features.index')
            ->with('success', 'Feature updated successfully.');
    }

    public function destroy(Feature $feature)
    {
        // Check if any hosts are using this feature
        $usageCount = $feature->hosts()->wherePivot('is_enabled', true)->count();

        if ($usageCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete feature. It is currently enabled by {$usageCount} client(s).");
        }

        $feature->delete();

        return redirect()->route('backoffice.features.index')
            ->with('success', 'Feature deleted successfully.');
    }

    public function toggleActive(Feature $feature)
    {
        $feature->update(['is_active' => !$feature->is_active]);

        $status = $feature->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Feature {$status} successfully.");
    }

    protected function parseJson(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
