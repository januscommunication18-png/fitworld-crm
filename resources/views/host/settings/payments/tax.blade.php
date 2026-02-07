@extends('layouts.settings')

@section('title', 'Tax Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Tax Settings</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Tax Rates</h2>
                    <p class="text-base-content/60 text-sm">Configure sales tax for your location</p>
                </div>
                <button class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span> Add Tax Rate
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rate</th>
                            <th>Applies To</th>
                            <th>Status</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-medium">Texas Sales Tax</td>
                            <td>8.25%</td>
                            <td>All products & services</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Active</span></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-xs btn-square">
                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                        <li><a class="dropdown-item text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Tax Display</h2>
            <div class="space-y-4">
                <div>
                    <label class="label-text">Price Display</label>
                    <div class="space-y-2 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="tax_display" class="radio radio-primary radio-sm" checked />
                            <span class="text-sm">Show prices excluding tax (add tax at checkout)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="tax_display" class="radio radio-primary radio-sm" />
                            <span class="text-sm">Show prices including tax</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Show tax breakdown on receipts</div>
                        <div class="text-sm text-base-content/60">Display itemized tax amounts</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Tax ID</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text" for="tax_id">Business Tax ID / EIN</label>
                    <input id="tax_id" type="text" class="input w-full" placeholder="XX-XXXXXXX" />
                </div>
                <div>
                    <label class="label-text" for="tax_name">Business Legal Name</label>
                    <input id="tax_name" type="text" class="input w-full" placeholder="Your Business LLC" />
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="btn btn-primary">Save Changes</button>
    </div>
</div>
@endsection
