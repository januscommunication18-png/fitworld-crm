<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'default');

        $plans = Plan::when($tab === 'default', fn($q) => $q->where('is_default', true))
            ->when($tab === 'custom', fn($q) => $q->where('is_default', false))
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('backoffice.plans.index', compact('plans', 'tab'));
    }

    public function create()
    {
        return view('backoffice.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'paddle_product_id' => 'nullable|string|max:255',
            'paddle_monthly_price_id' => 'nullable|string|max:255',
            'paddle_yearly_price_id' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Process features
        $validated['features'] = $this->processFeatures($request);

        $plan = Plan::create($validated);

        $tab = $plan->is_default ? 'default' : 'custom';

        return redirect()->route('backoffice.plans.index', ['tab' => $tab])
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('backoffice.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'paddle_product_id' => 'nullable|string|max:255',
            'paddle_monthly_price_id' => 'nullable|string|max:255',
            'paddle_yearly_price_id' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Process features
        $validated['features'] = $this->processFeatures($request);

        $plan->update($validated);

        return redirect()->route('backoffice.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        // Check if any hosts are using this plan
        if ($plan->hosts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan. It is currently assigned to ' . $plan->hosts()->count() . ' client(s).');
        }

        $plan->delete();

        return redirect()->route('backoffice.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Plan {$status} successfully.");
    }

    protected function processFeatures(Request $request): array
    {
        return [
            // Limits (0 = unlimited)
            'locations' => (int) $request->input('feature_locations', 1),
            'rooms' => (int) $request->input('feature_rooms', 3),
            'classes' => (int) $request->input('feature_classes', 10),
            'students' => (int) $request->input('feature_students', 100),

            // Boolean features
            'crm' => $request->boolean('feature_crm'),
            'stripe_payments' => $request->boolean('feature_stripe_payments'),
            'memberships' => $request->boolean('feature_memberships'),
            'intro_offers' => $request->boolean('feature_intro_offers'),
            'automated_emails' => $request->boolean('feature_automated_emails'),
            'attendance_insights' => $request->boolean('feature_attendance_insights'),
            'revenue_insights' => $request->boolean('feature_revenue_insights'),
            'manual_payments' => $request->boolean('feature_manual_payments'),
            'online_payments' => $request->boolean('feature_online_payments'),
            'ics_sync' => $request->boolean('feature_ics_sync'),
            'fitnearyou_attribution' => $request->boolean('feature_fitnearyou_attribution'),
            'priority_support' => $request->boolean('feature_priority_support'),
        ];
    }
}
