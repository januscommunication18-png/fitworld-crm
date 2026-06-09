<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MembershipPlanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mobile membership-plans listing. Host resolved by `studio.context`.
 */
class MembershipPlanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');

        $query = $host->membershipPlans()
            ->withCount('classPlans')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return MembershipPlanResource::collection(
            $query->paginate(25)->withQueryString()
        );
    }
}
