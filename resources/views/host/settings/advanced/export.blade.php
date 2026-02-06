@extends('layouts.settings')

@section('title', 'Data Export — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Data Export</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Export Your Data</h2>
            <p class="text-base-content/60 text-sm mb-6">Download your studio data in CSV format for backup or migration purposes.</p>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--users] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Students</div>
                            <div class="text-sm text-base-content/60">All student profiles and contact info</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--calendar] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Classes & Schedule</div>
                            <div class="text-sm text-base-content/60">All classes and scheduling data</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--receipt] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Transactions</div>
                            <div class="text-sm text-base-content/60">All payment and transaction history</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--ticket] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Bookings</div>
                            <div class="text-sm text-base-content/60">All booking and attendance records</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--database] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Full Export</div>
                            <div class="text-sm text-base-content/60">All data in a single ZIP file</div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Recent Exports</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Feb 5, 2026</td>
                            <td>Students</td>
                            <td>45 KB</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Ready</span></td>
                            <td>
                                <a href="#" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--download] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Jan 15, 2026</td>
                            <td>Full Export</td>
                            <td>2.3 MB</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Ready</span></td>
                            <td>
                                <a href="#" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--download] size-4"></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
