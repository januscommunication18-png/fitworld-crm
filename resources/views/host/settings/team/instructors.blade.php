@extends('layouts.settings')

@section('title', 'Instructors — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Instructors</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Instructors</h2>
                    <p class="text-base-content/60 text-sm">Manage your teaching staff and their profiles</p>
                </div>
                <button class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content w-14 rounded-full">
                                <span class="text-lg">JS</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">Jane Smith</div>
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-xs btn-square">
                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                        <li><a class="dropdown-item"><span class="icon-[tabler--calendar] size-4"></span> View Schedule</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="text-sm text-base-content/60">jane@zenyoga.com</div>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <span class="badge badge-primary badge-soft badge-xs">Yoga</span>
                                <span class="badge badge-primary badge-soft badge-xs">Pilates</span>
                            </div>
                            <div class="text-xs text-base-content/50 mt-2">12 classes this week</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-secondary text-secondary-content w-14 rounded-full">
                                <span class="text-lg">MJ</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">Mike Johnson</div>
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-xs btn-square">
                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                        <li><a class="dropdown-item"><span class="icon-[tabler--calendar] size-4"></span> View Schedule</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="text-sm text-base-content/60">mike@zenyoga.com</div>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <span class="badge badge-primary badge-soft badge-xs">HIIT</span>
                                <span class="badge badge-primary badge-soft badge-xs">Strength</span>
                            </div>
                            <div class="text-xs text-base-content/50 mt-2">8 classes this week</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-dashed border-base-content/20 rounded-lg flex items-center justify-center min-h-[120px]">
                    <button class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Instructor Settings</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Allow instructors to manage their own classes</div>
                        <div class="text-sm text-base-content/60">Instructors can edit class details they teach</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Show instructor bios on booking page</div>
                        <div class="text-sm text-base-content/60">Display instructor profiles publicly</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
