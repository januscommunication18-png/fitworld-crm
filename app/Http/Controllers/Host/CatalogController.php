<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\MembershipPlan;
use App\Models\RentalItem;
use App\Models\ServicePlan;
use App\Models\SpaceRentalConfig;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $tab = $request->get('tab', 'classes');

        $classPlans = collect();
        $servicePlans = collect();
        $membershipPlans = collect();
        $spaceRentalConfigs = collect();
        $rentalItems = collect();

        if ($tab === 'classes') {
            $classPlans = $host->classPlans()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } elseif ($tab === 'services') {
            $servicePlans = $host->servicePlans()
                ->withCount('activeInstructors')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } elseif ($tab === 'memberships') {
            $membershipPlans = $host->membershipPlans()
                ->withCount('classPlans')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } elseif ($tab === 'rental-spaces') {
            $spaceRentalConfigs = $host->spaceRentalConfigs()
                ->with(['location', 'room'])
                ->withCount('rentals')
                ->orderBy('name')
                ->get();
        } elseif ($tab === 'item-rentals') {
            $rentalItems = $host->rentalItems()
                ->withCount('bookings')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('host.catalog.index', compact('tab', 'classPlans', 'servicePlans', 'membershipPlans', 'spaceRentalConfigs', 'rentalItems'));
    }
}
