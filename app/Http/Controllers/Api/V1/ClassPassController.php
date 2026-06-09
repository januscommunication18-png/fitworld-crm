<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ClassPassResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mobile class-passes listing. Host resolved by `studio.context`.
 */
class ClassPassController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');

        $query = $host->classPasses()
            ->withCount('purchases')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return ClassPassResource::collection(
            $query->paginate(25)->withQueryString()
        );
    }
}
