@extends('layouts.settings')

@section('title', 'Rooms — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Rooms</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Studio Rooms</h2>
                    <p class="text-base-content/60 text-sm">Manage your studio spaces and their capacities</p>
                </div>
                <button class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span> Add Room
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Room Name</th>
                            <th>Capacity</th>
                            <th>Amenities</th>
                            <th>Status</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-medium">Main Studio</td>
                            <td>25</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span class="badge badge-soft badge-xs">Mats</span>
                                    <span class="badge badge-soft badge-xs">Sound System</span>
                                </div>
                            </td>
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
                        <tr>
                            <td class="font-medium">Private Room</td>
                            <td>8</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span class="badge badge-soft badge-xs">Mats</span>
                                    <span class="badge badge-soft badge-xs">Private</span>
                                </div>
                            </td>
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
            <h2 class="text-lg font-semibold mb-4">Studio Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Street Address</label>
                    <p class="font-medium">123 Main Street</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">City, State ZIP</label>
                    <p class="font-medium">Austin, TX 78701</p>
                </div>
            </div>
            <button class="btn btn-soft btn-sm mt-4">
                <span class="icon-[tabler--edit] size-4"></span> Edit Address
            </button>
        </div>
    </div>
</div>
@endsection
