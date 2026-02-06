@extends('layouts.settings')

@section('title', 'Stripe — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Stripe</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <span class="icon-[tabler--brand-stripe] size-12 text-[#635BFF]"></span>
                <div>
                    <h2 class="text-lg font-semibold">Stripe</h2>
                    <p class="text-base-content/60 text-sm">Accept credit card payments securely</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-success/10 rounded-lg mb-6">
                <span class="icon-[tabler--circle-check] size-6 text-success"></span>
                <div>
                    <div class="font-medium text-success">Connected</div>
                    <div class="text-sm text-base-content/60">Your Stripe account is connected and ready to accept payments</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Account</label>
                    <p class="font-medium">Zen Yoga Studio LLC</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Account ID</label>
                    <p class="font-medium font-mono text-sm">acct_1234567890</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Connected</label>
                    <p class="font-medium">January 15, 2026</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Payout Status</label>
                    <p class="font-medium"><span class="badge badge-success badge-soft badge-sm">Active</span></p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="flex items-center justify-between">
                <div>
                    <a href="https://dashboard.stripe.com" target="_blank" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--external-link] size-4"></span> Open Stripe Dashboard
                    </a>
                </div>
                <button class="btn btn-ghost btn-sm text-error">
                    <span class="icon-[tabler--unlink] size-4"></span> Disconnect
                </button>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">This Month</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-2xl font-bold">$4,567</div>
                    <div class="text-sm text-base-content/60">Processed</div>
                </div>
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-2xl font-bold">156</div>
                    <div class="text-sm text-base-content/60">Transactions</div>
                </div>
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-2xl font-bold">$89</div>
                    <div class="text-sm text-base-content/60">Fees</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
