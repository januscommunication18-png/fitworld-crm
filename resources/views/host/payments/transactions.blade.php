@extends('layouts.dashboard')

@section('title', 'Payments')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--credit-card] me-1 size-4"></span> Payments</li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Payments</h1>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold">$12,480</div><div class="text-xs text-base-content/60">Total Revenue</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-success">84</div><div class="text-xs text-base-content/60">Subscriptions</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-info">$3,200</div><div class="text-xs text-base-content/60">Pending Payouts</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-error">4</div><div class="text-xs text-base-content/60">Refunds</div></div></div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Date</th><th>Student</th><th>Description</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>Feb 5</td><td>Amy Lopez</td><td>Monthly Unlimited - Feb</td><td>$120.00</td><td>Visa *4242</td><td><span class="badge badge-success badge-soft badge-sm">Paid</span></td></tr>
                        <tr><td>Feb 4</td><td>Brian Kim</td><td>10-Class Pack</td><td>$150.00</td><td>Visa *1234</td><td><span class="badge badge-success badge-soft badge-sm">Paid</span></td></tr>
                        <tr><td>Feb 3</td><td>Carol Davis</td><td>Drop-in class</td><td>$25.00</td><td>Apple Pay</td><td><span class="badge badge-warning badge-soft badge-sm">Refunded</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
