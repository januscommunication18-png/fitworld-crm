@extends('layouts.settings')

@section('title', 'Payout Preferences — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Payout Preferences</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Payout Account</h2>
            <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
                <span class="icon-[tabler--building-bank] size-10 text-primary"></span>
                <div class="flex-1">
                    <div class="font-medium">Chase Bank ****4567</div>
                    <div class="text-sm text-base-content/60">Checking account</div>
                </div>
                <button class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span> Change
                </button>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Payout Schedule</h2>
            <div class="space-y-4">
                <div>
                    <label class="label-text">Payout Frequency</label>
                    <div class="space-y-2 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="payout_freq" class="radio radio-primary radio-sm" />
                            <span class="text-sm">Daily (available funds paid out each day)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="payout_freq" class="radio radio-primary radio-sm" checked />
                            <span class="text-sm">Weekly (every Monday)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="payout_freq" class="radio radio-primary radio-sm" />
                            <span class="text-sm">Monthly (1st of each month)</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="label-text" for="min_payout">Minimum Payout Amount</label>
                    <div class="input-group w-48">
                        <span class="input-group-text">$</span>
                        <input id="min_payout" type="number" class="input w-full" value="100" />
                    </div>
                    <p class="text-xs text-base-content/50 mt-1">Payouts under this amount will be held until threshold is met</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Recent Payouts</h2>
            <div class="text-center py-8">
                <span class="icon-[tabler--building-bank] size-12 text-base-content/20 mx-auto block"></span>
                <p class="text-base-content/50 mt-2">No payouts yet</p>
                <p class="text-xs text-base-content/40 mt-1">Payouts will appear here once transactions are processed</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="btn btn-primary">Save Changes</button>
    </div>
</div>
@endsection
