@extends('layouts.settings')

@section('title', 'Users & Roles — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Users & Roles</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Team Members</h2>
                    <p class="text-base-content/60 text-sm">Manage who has access to your studio dashboard</p>
                </div>
                <button class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span> Invite User
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-10 rounded-full">
                                            <span>JS</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">Jane Smith</div>
                                        <div class="text-sm text-base-content/60">jane@zenyoga.com</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-primary badge-soft badge-sm">Owner</span></td>
                            <td><span class="badge badge-success badge-soft badge-sm">Active</span></td>
                            <td class="text-sm text-base-content/60">Just now</td>
                            <td>
                                <span class="text-base-content/40 text-sm">You</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-secondary text-secondary-content w-10 rounded-full">
                                            <span>MJ</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">Mike Johnson</div>
                                        <div class="text-sm text-base-content/60">mike@zenyoga.com</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Manager</span></td>
                            <td><span class="badge badge-success badge-soft badge-sm">Active</span></td>
                            <td class="text-sm text-base-content/60">2 hours ago</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-xs btn-square">
                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"><span class="icon-[tabler--edit] size-4"></span> Edit Role</a></li>
                                        <li><a class="dropdown-item text-error"><span class="icon-[tabler--trash] size-4"></span> Remove</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-base-300 text-base-content w-10 rounded-full">
                                            <span>SL</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">Sarah Lee</div>
                                        <div class="text-sm text-base-content/60">sarah@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Staff</span></td>
                            <td><span class="badge badge-warning badge-soft badge-sm">Pending</span></td>
                            <td class="text-sm text-base-content/60">Invited 2 days ago</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-xs btn-square">
                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"><span class="icon-[tabler--send] size-4"></span> Resend Invite</a></li>
                                        <li><a class="dropdown-item text-error"><span class="icon-[tabler--x] size-4"></span> Cancel Invite</a></li>
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
            <h2 class="text-lg font-semibold mb-4">Available Roles</h2>
            <div class="space-y-3">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium">Owner</div>
                            <div class="text-sm text-base-content/60">Full access to all features including billing and danger zone</div>
                        </div>
                        <span class="badge badge-primary badge-soft badge-sm">1 user</span>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium">Manager</div>
                            <div class="text-sm text-base-content/60">Can manage classes, students, and view reports</div>
                        </div>
                        <span class="badge badge-soft badge-sm">1 user</span>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium">Staff</div>
                            <div class="text-sm text-base-content/60">Can check-in students and view schedule</div>
                        </div>
                        <span class="badge badge-soft badge-sm">1 user</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
