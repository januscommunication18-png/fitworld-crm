@extends('layouts.settings')

@section('title', 'Cash App — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Cash App</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <div class="p-3 bg-[#00D632]/10 rounded-lg">
                    <span class="icon-[tabler--currency-dollar] size-8 text-[#00D632]"></span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Cash App</h2>
                    <p class="text-base-content/60 text-sm">Accept Cash App payments</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-base-200 rounded-lg mb-6">
                <span class="icon-[tabler--info-circle] size-6 text-info"></span>
                <div>
                    <div class="font-medium">Manual Integration</div>
                    <div class="text-sm text-base-content/60">Display your Cash App tag for students to pay manually</div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="label-text" for="cashtag">Your $Cashtag</label>
                    <div class="input-group w-64">
                        <span class="input-group-text">$</span>
                        <input id="cashtag" type="text" class="input w-full" placeholder="YourStudioName" />
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Show on checkout</div>
                        <div class="text-sm text-base-content/60">Display Cash App as a payment option</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" />
                </div>
            </div>

            <div class="mt-6">
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection
