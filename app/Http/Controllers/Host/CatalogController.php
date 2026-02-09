<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\ServicePlan;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $tab = $request->get('tab', 'classes');

        $classPlans = collect();
        $servicePlans = collect();

        if ($tab === 'classes') {
            $classPlans = $host->classPlans()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } else {
            $servicePlans = $host->servicePlans()
                ->withCount('activeInstructors')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('host.catalog.index', compact('tab', 'classPlans', 'servicePlans'));
    }
}
