@extends('layouts.settings')

@section('title', 'Automation Rules — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Automation Rules</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Automation Rules</h2>
                    <p class="text-base-content/60 text-sm">Set up automatic actions based on triggers</p>
                </div>
                <button class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span> New Rule
                </button>
            </div>

            <div class="space-y-4">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <span class="icon-[tabler--clock] size-5 text-primary"></span>
                            </div>
                            <div>
                                <div class="font-medium">Class Reminder - 24 hours</div>
                                <div class="text-sm text-base-content/60">Send email reminder 24 hours before class</div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="badge badge-soft badge-xs">Email</span>
                                    <span class="badge badge-soft badge-xs">All Classes</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                            <div class="dropdown dropdown-end">
                                <button class="btn btn-ghost btn-xs btn-square">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                    <li><a class="dropdown-item text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-success/10 rounded-lg">
                                <span class="icon-[tabler--user-plus] size-5 text-success"></span>
                            </div>
                            <div>
                                <div class="font-medium">Welcome Email</div>
                                <div class="text-sm text-base-content/60">Send welcome email when new student signs up</div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="badge badge-soft badge-xs">Email</span>
                                    <span class="badge badge-soft badge-xs">New Students</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                            <div class="dropdown dropdown-end">
                                <button class="btn btn-ghost btn-xs btn-square">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                    <li><a class="dropdown-item text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-warning/10 rounded-lg">
                                <span class="icon-[tabler--calendar-x] size-5 text-warning"></span>
                            </div>
                            <div>
                                <div class="font-medium">Win-back Campaign</div>
                                <div class="text-sm text-base-content/60">Email students who haven't visited in 30 days</div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="badge badge-soft badge-xs">Email</span>
                                    <span class="badge badge-soft badge-xs">Inactive Students</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" />
                            <div class="dropdown dropdown-end">
                                <button class="btn btn-ghost btn-xs btn-square">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                    <li><a class="dropdown-item text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
