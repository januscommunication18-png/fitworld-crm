@extends('layouts.settings')

@section('title', 'FitNearYou — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">FitNearYou</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <div class="p-3 bg-primary/10 rounded-lg">
                    <span class="icon-[tabler--map-search] size-8 text-primary"></span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">FitNearYou</h2>
                    <p class="text-base-content/60 text-sm">Get discovered by fitness seekers in your area</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-base-200 rounded-lg mb-6">
                <span class="icon-[tabler--info-circle] size-6 text-info"></span>
                <div>
                    <div class="font-medium">Not Connected</div>
                    <div class="text-sm text-base-content/60">Connect to list your studio on the FitNearYou marketplace</div>
                </div>
            </div>

            <div class="bg-base-200 rounded-lg p-6 mb-6">
                <h3 class="font-semibold mb-4">Why connect to FitNearYou?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success mt-0.5"></span>
                        <div>
                            <div class="font-medium">Get discovered</div>
                            <div class="text-sm text-base-content/60">Appear in local fitness searches</div>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success mt-0.5"></span>
                        <div>
                            <div class="font-medium">Direct bookings</div>
                            <div class="text-sm text-base-content/60">Students book directly through your calendar</div>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success mt-0.5"></span>
                        <div>
                            <div class="font-medium">No commission</div>
                            <div class="text-sm text-base-content/60">Keep 100% of your booking revenue</div>
                        </div>
                    </li>
                </ul>
            </div>

            <button class="btn btn-primary">
                <span class="icon-[tabler--plug] size-4"></span> Connect to FitNearYou
            </button>
        </div>
    </div>
</div>
@endsection
