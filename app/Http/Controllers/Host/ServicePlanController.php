<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\ServicePlanRequest;
use App\Models\ServicePlan;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServicePlanController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $category = $request->get('category');

        $servicePlans = $host->servicePlans()
            ->withCount('activeInstructors')
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = ServicePlan::getCategories();

        return view('host.service-plans.index', compact('servicePlans', 'category', 'categories'));
    }

    public function create()
    {
        $host = auth()->user()->host;
        $categories = ServicePlan::getCategories();
        $locationTypes = ServicePlan::getLocationTypes();
        $instructors = $host->instructors()->active()->get();

        return view('host.service-plans.create', compact('categories', 'locationTypes', 'instructors'));
    }

    public function store(ServicePlanRequest $request)
    {
        $host = auth()->user()->host;
        $data = $request->validated();

        // Generate unique slug
        $data['slug'] = Str::slug($data['name']);
        $counter = 1;
        while ($host->servicePlans()->where('slug', $data['slug'])->exists()) {
            $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('service-plans', 'public');
        }

        // Set default sort order
        $data['sort_order'] = $host->servicePlans()->max('sort_order') + 1;

        $servicePlan = $host->servicePlans()->create($data);

        // Attach instructors if provided
        if ($request->has('instructor_ids')) {
            $servicePlan->instructors()->attach($request->input('instructor_ids'));
        }

        return redirect()->route('catalog.index', ['tab' => 'services'])
            ->with('success', 'Service plan created successfully.');
    }

    public function show(ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $servicePlan->load('instructors');

        return view('host.service-plans.show', compact('servicePlan'));
    }

    public function edit(ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $host = auth()->user()->host;
        $categories = ServicePlan::getCategories();
        $locationTypes = ServicePlan::getLocationTypes();
        $instructors = $host->instructors()->active()->get();
        $assignedInstructorIds = $servicePlan->instructors->pluck('id')->toArray();

        return view('host.service-plans.edit', compact('servicePlan', 'categories', 'locationTypes', 'instructors', 'assignedInstructorIds'));
    }

    public function update(ServicePlanRequest $request, ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $data = $request->validated();

        // Update slug if name changed
        if ($data['name'] !== $servicePlan->name) {
            $host = auth()->user()->host;
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            while ($host->servicePlans()->where('slug', $data['slug'])->where('id', '!=', $servicePlan->id)->exists()) {
                $data['slug'] = Str::slug($data['name']) . '-' . $counter++;
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($servicePlan->image_path) {
                Storage::disk('public')->delete($servicePlan->image_path);
            }
            $data['image_path'] = $request->file('image')->store('service-plans', 'public');
        }

        // Handle checkboxes
        $data['is_active'] = $request->boolean('is_active');
        $data['is_visible_on_booking_page'] = $request->boolean('is_visible_on_booking_page');

        $servicePlan->update($data);

        // Sync instructors
        if ($request->has('instructor_ids')) {
            $servicePlan->instructors()->sync($request->input('instructor_ids'));
        } else {
            $servicePlan->instructors()->detach();
        }

        return redirect()->route('catalog.index', ['tab' => 'services'])
            ->with('success', 'Service plan updated successfully.');
    }

    public function destroy(ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        // Check if any slots use this plan
        if ($servicePlan->slots()->exists()) {
            return back()->with('error', 'Cannot delete this service plan. It has slots associated with it.');
        }

        // Delete image
        if ($servicePlan->image_path) {
            Storage::disk('public')->delete($servicePlan->image_path);
        }

        $servicePlan->delete();

        return redirect()->route('catalog.index', ['tab' => 'services'])
            ->with('success', 'Service plan deleted successfully.');
    }

    public function toggleActive(ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $servicePlan->update(['is_active' => !$servicePlan->is_active]);

        return back()->with('success', 'Service plan ' . ($servicePlan->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }

    public function manageInstructors(ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $host = auth()->user()->host;
        $allInstructors = $host->instructors()->active()->get();
        $servicePlan->load('instructors');

        return view('host.service-plans.instructors', compact('servicePlan', 'allInstructors'));
    }

    public function updateInstructors(Request $request, ServicePlan $servicePlan)
    {
        $this->authorizeHost($servicePlan);

        $request->validate([
            'instructors' => 'array',
            'instructors.*.id' => 'required|exists:instructors,id',
            'instructors.*.custom_price' => 'nullable|numeric|min:0',
            'instructors.*.is_active' => 'boolean',
        ]);

        $syncData = [];
        foreach ($request->input('instructors', []) as $instructorData) {
            $syncData[$instructorData['id']] = [
                'custom_price' => $instructorData['custom_price'] ?? null,
                'is_active' => $instructorData['is_active'] ?? true,
            ];
        }

        $servicePlan->instructors()->sync($syncData);

        return back()->with('success', 'Instructors updated successfully.');
    }

    private function authorizeHost(ServicePlan $servicePlan): void
    {
        if ($servicePlan->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
