@extends('layouts.settings')

@section('title', 'Permissions — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Permissions</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Role Permissions</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th class="text-center">Owner</th>
                            <th class="text-center">Manager</th>
                            <th class="text-center">Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="bg-base-200 font-semibold text-sm">Schedule</td>
                        </tr>
                        <tr>
                            <td>View schedule</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                        </tr>
                        <tr>
                            <td>Create/edit classes</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td>Delete classes</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="bg-base-200 font-semibold text-sm">Students</td>
                        </tr>
                        <tr>
                            <td>View students</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                        </tr>
                        <tr>
                            <td>Check-in students</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                        </tr>
                        <tr>
                            <td>Edit student profiles</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="bg-base-200 font-semibold text-sm">Payments</td>
                        </tr>
                        <tr>
                            <td>View transactions</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td>Process refunds</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="bg-base-200 font-semibold text-sm">Settings</td>
                        </tr>
                        <tr>
                            <td>Edit studio settings</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td>Manage billing</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                        <tr>
                            <td>Access danger zone</td>
                            <td class="text-center"><span class="icon-[tabler--check] size-5 text-success"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                            <td class="text-center"><span class="icon-[tabler--x] size-5 text-base-content/30"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <span>Custom role permissions are available on the Pro plan. <a href="{{ route('settings.billing.plan') }}" class="link">Upgrade now</a></span>
    </div>
</div>
@endsection
