@extends('layouts.settings')

@section('title', 'Policies — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Policies</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Cancellation Policy</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="cancel_window">Cancellation Window</label>
                        <select id="cancel_window" class="select w-full">
                            <option>No cancellations allowed</option>
                            <option>1 hour before class</option>
                            <option>2 hours before class</option>
                            <option>4 hours before class</option>
                            <option selected>12 hours before class</option>
                            <option>24 hours before class</option>
                            <option>48 hours before class</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-text" for="late_cancel_fee">Late Cancellation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="late_cancel_fee" type="number" class="input w-full" value="0" />
                        </div>
                    </div>
                </div>
                <div>
                    <label class="label-text">Late Cancel Behavior</label>
                    <div class="space-y-2 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="late_cancel" class="radio radio-primary radio-sm" checked />
                            <span class="text-sm">Deduct from class pack / membership</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="late_cancel" class="radio radio-primary radio-sm" />
                            <span class="text-sm">Charge late cancellation fee</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="late_cancel" class="radio radio-primary radio-sm" />
                            <span class="text-sm">No penalty</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">No-Show Policy</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Track no-shows</div>
                        <div class="text-sm text-base-content/60">Mark students who don't attend as no-show</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
                <div>
                    <label class="label-text" for="noshow_fee">No-Show Fee</label>
                    <div class="input-group w-48">
                        <span class="input-group-text">$</span>
                        <input id="noshow_fee" type="number" class="input w-full" value="15" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Terms & Conditions</h2>
                    <p class="text-base-content/60 text-sm">Displayed when students sign up</p>
                </div>
                <button class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>
            <div class="bg-base-200 rounded-lg p-4 text-sm text-base-content/70 max-h-40 overflow-y-auto">
                <p>By creating an account and booking classes at our studio, you agree to our cancellation policy and payment terms...</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="btn btn-primary">Save Changes</button>
    </div>
</div>
@endsection
