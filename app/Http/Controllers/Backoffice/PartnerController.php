<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    /**
     * Display a listing of partners
     */
    public function index()
    {
        $partners = Partner::orderBy('name')->get();
        $totalPercentage = Partner::active()->sum('percentage');
        $remainingPercentage = 100 - $totalPercentage;

        return view('backoffice.partners.index', [
            'partners' => $partners,
            'totalPercentage' => $totalPercentage,
            'remainingPercentage' => $remainingPercentage,
        ]);
    }

    /**
     * Show the form for creating a new partner
     */
    public function create()
    {
        $remainingPercentage = Partner::getRemainingPercentage();

        return view('backoffice.partners.create', [
            'remainingPercentage' => $remainingPercentage,
        ]);
    }

    /**
     * Store a newly created partner
     */
    public function store(Request $request)
    {
        $remainingPercentage = Partner::getRemainingPercentage();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:partners,email',
            'percentage' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $remainingPercentage,
            ],
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ], [
            'percentage.max' => "The percentage cannot exceed {$remainingPercentage}% (remaining available).",
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Partner::create($validated);

        return redirect()
            ->route('backoffice.partners.index')
            ->with('success', 'Partner created successfully.');
    }

    /**
     * Show the form for editing the specified partner
     */
    public function edit(Partner $partner)
    {
        // Calculate remaining percentage excluding this partner
        $remainingPercentage = Partner::getRemainingPercentage() + $partner->percentage;

        return view('backoffice.partners.edit', [
            'partner' => $partner,
            'remainingPercentage' => $remainingPercentage,
        ]);
    }

    /**
     * Update the specified partner
     */
    public function update(Request $request, Partner $partner)
    {
        // Calculate remaining percentage excluding this partner
        $remainingPercentage = Partner::getRemainingPercentage() + $partner->percentage;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('partners', 'email')->ignore($partner->id),
            ],
            'percentage' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $remainingPercentage,
            ],
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ], [
            'percentage.max' => "The percentage cannot exceed {$remainingPercentage}% (remaining available).",
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $partner->update($validated);

        return redirect()
            ->route('backoffice.partners.index')
            ->with('success', 'Partner updated successfully.');
    }

    /**
     * Remove the specified partner
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        return redirect()
            ->route('backoffice.partners.index')
            ->with('success', 'Partner deleted successfully.');
    }

    /**
     * Toggle partner active status
     */
    public function toggleStatus(Partner $partner)
    {
        $partner->update(['is_active' => !$partner->is_active]);

        $status = $partner->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('backoffice.partners.index')
            ->with('success', "Partner {$status} successfully.");
    }
}
