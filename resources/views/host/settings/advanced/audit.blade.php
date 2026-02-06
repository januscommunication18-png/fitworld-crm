@extends('layouts.settings')

@section('title', 'Audit Logs — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Audit Logs</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Audit Logs</h2>
                    <p class="text-base-content/60 text-sm">Track all changes made to your studio settings</p>
                </div>
                <div class="flex gap-2">
                    <select class="select select-sm">
                        <option>All Actions</option>
                        <option>Settings Changes</option>
                        <option>User Actions</option>
                        <option>Payment Events</option>
                    </select>
                    <select class="select select-sm">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 90 days</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-sm">Feb 7, 2026 2:34 PM</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-6 rounded-full text-xs">
                                            <span>JS</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">Jane Smith</span>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Settings Update</span></td>
                            <td class="text-sm text-base-content/60">Changed cancellation window to 12 hours</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Feb 7, 2026 11:20 AM</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-secondary text-secondary-content w-6 rounded-full text-xs">
                                            <span>MJ</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">Mike Johnson</span>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Class Created</span></td>
                            <td class="text-sm text-base-content/60">Created "Morning Flow Yoga"</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Feb 6, 2026 4:15 PM</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-6 rounded-full text-xs">
                                            <span>JS</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">Jane Smith</span>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">User Invited</span></td>
                            <td class="text-sm text-base-content/60">Invited sarah@example.com as Staff</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Feb 5, 2026 9:00 AM</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-6 rounded-full text-xs">
                                            <span>JS</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">Jane Smith</span>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Payment Settings</span></td>
                            <td class="text-sm text-base-content/60">Updated tax rate to 8.25%</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Feb 4, 2026 3:45 PM</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-secondary text-secondary-content w-6 rounded-full text-xs">
                                            <span>MJ</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">Mike Johnson</span>
                                </div>
                            </td>
                            <td><span class="badge badge-soft badge-sm">Instructor Added</span></td>
                            <td class="text-sm text-base-content/60">Added instructor "Sarah Lee"</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center mt-4">
                <div class="join">
                    <button class="join-item btn btn-sm">«</button>
                    <button class="join-item btn btn-sm btn-active">1</button>
                    <button class="join-item btn btn-sm">2</button>
                    <button class="join-item btn btn-sm">3</button>
                    <button class="join-item btn btn-sm">»</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
