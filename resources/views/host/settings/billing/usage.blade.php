@extends('layouts.settings')

@section('title', 'Usage — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Usage</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Current Usage</h2>
            <p class="text-base-content/60 text-sm mb-6">Your usage resets on March 1, 2026</p>

            <div class="space-y-6">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="font-medium">Students</div>
                            <div class="text-sm text-base-content/60">Active student accounts</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold">234</span>
                            <span class="text-base-content/60">/ 1,000</span>
                        </div>
                    </div>
                    <progress class="progress progress-primary w-full" value="23" max="100"></progress>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="font-medium">Team Members</div>
                            <div class="text-sm text-base-content/60">Staff with dashboard access</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold">3</span>
                            <span class="text-base-content/60">/ 5</span>
                        </div>
                    </div>
                    <progress class="progress progress-primary w-full" value="60" max="100"></progress>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="font-medium">Classes This Month</div>
                            <div class="text-sm text-base-content/60">Scheduled classes</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold">87</span>
                            <span class="text-base-content/60">/ Unlimited</span>
                        </div>
                    </div>
                    <progress class="progress progress-success w-full" value="100" max="100"></progress>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="font-medium">Email Notifications</div>
                            <div class="text-sm text-base-content/60">Emails sent this month</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold">1,456</span>
                            <span class="text-base-content/60">/ 10,000</span>
                        </div>
                    </div>
                    <progress class="progress progress-primary w-full" value="14" max="100"></progress>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Add-on Usage</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Add-on</th>
                            <th>Usage</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SMS Messages</td>
                            <td>0 messages</td>
                            <td>$0.00</td>
                        </tr>
                        <tr>
                            <td>Extra Storage</td>
                            <td>0 GB</td>
                            <td>$0.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="font-semibold">Total Add-ons</td>
                            <td class="font-semibold">$0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
