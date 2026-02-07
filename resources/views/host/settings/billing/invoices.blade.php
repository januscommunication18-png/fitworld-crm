@extends('layouts.settings')

@section('title', 'Invoices — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Invoices</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Billing History</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Feb 1, 2026</td>
                            <td>Pro Plan - Monthly</td>
                            <td class="font-medium">$49.00</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Paid</span></td>
                            <td>
                                <a href="#" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--download] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Jan 1, 2026</td>
                            <td>Pro Plan - Monthly</td>
                            <td class="font-medium">$49.00</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Paid</span></td>
                            <td>
                                <a href="#" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--download] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Dec 1, 2025</td>
                            <td>Pro Plan - Monthly</td>
                            <td class="font-medium">$49.00</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Paid</span></td>
                            <td>
                                <a href="#" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--download] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Nov 1, 2025</td>
                            <td>Pro Plan - Monthly</td>
                            <td class="font-medium">$49.00</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Paid</span></td>
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

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
            <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
                <span class="icon-[tabler--credit-card] size-10 text-primary"></span>
                <div class="flex-1">
                    <div class="font-medium">Visa ending in 4242</div>
                    <div class="text-sm text-base-content/60">Expires 12/2027</div>
                </div>
                <button class="btn btn-soft btn-sm">Update</button>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Billing Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-base-content/60">Company</span>
                    <p class="font-medium">Zen Yoga Studio LLC</p>
                </div>
                <div>
                    <span class="text-base-content/60">Address</span>
                    <p class="font-medium">123 Main Street, Austin, TX 78701</p>
                </div>
            </div>
            <button class="btn btn-soft btn-sm mt-4">Update Address</button>
        </div>
    </div>
</div>
@endsection
