@extends('layouts.settings')

@section('title', 'Danger Zone — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="text-error">Danger Zone</span></li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="alert alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <div class="font-medium">Proceed with caution</div>
            <div class="text-sm">Actions on this page can have permanent consequences and cannot be undone.</div>
        </div>
    </div>

    <div class="card border-2 border-error/30 bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold text-error mb-6">Danger Zone</h2>

            <div class="space-y-6">
                <div class="flex items-start justify-between p-4 border border-error/20 rounded-lg bg-error/5">
                    <div>
                        <div class="font-medium">Reset All Settings</div>
                        <div class="text-sm text-base-content/60">Reset all studio settings to their default values. This will not delete any data.</div>
                    </div>
                    <button class="btn btn-outline btn-error btn-sm">Reset Settings</button>
                </div>

                <div class="flex items-start justify-between p-4 border border-error/20 rounded-lg bg-error/5">
                    <div>
                        <div class="font-medium">Delete All Students</div>
                        <div class="text-sm text-base-content/60">Permanently delete all student accounts and their data. This action cannot be undone.</div>
                    </div>
                    <button class="btn btn-outline btn-error btn-sm">Delete Students</button>
                </div>

                <div class="flex items-start justify-between p-4 border border-error/20 rounded-lg bg-error/5">
                    <div>
                        <div class="font-medium">Clear All Schedule</div>
                        <div class="text-sm text-base-content/60">Delete all classes and schedules. Historical booking data will be preserved.</div>
                    </div>
                    <button class="btn btn-outline btn-error btn-sm">Clear Schedule</button>
                </div>

                <div class="divider"></div>

                <div class="flex items-start justify-between p-4 border-2 border-error rounded-lg bg-error/10">
                    <div>
                        <div class="font-semibold text-error">Delete Studio Account</div>
                        <div class="text-sm text-base-content/60">Permanently delete your entire studio account and all associated data. This includes all students, classes, transactions, and settings. This action is irreversible.</div>
                    </div>
                    <button class="btn btn-error btn-sm">Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Transfer Ownership</h2>
            <p class="text-base-content/60 text-sm mb-4">Transfer your studio to another team member. They will become the new owner with full access to all settings and billing.</p>
            <div class="flex gap-2">
                <select class="select flex-1">
                    <option disabled selected>Select a team member</option>
                    <option>Mike Johnson (mike@zenyoga.com)</option>
                </select>
                <button class="btn btn-outline">Transfer</button>
            </div>
        </div>
    </div>
</div>
@endsection
