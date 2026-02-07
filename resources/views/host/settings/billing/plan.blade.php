@extends('layouts.settings')

@section('title', 'Current Plan — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Current Plan</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold">Pro Plan</h2>
                        <span class="badge badge-primary badge-soft badge-sm">Current</span>
                    </div>
                    <p class="text-base-content/60 text-sm">Your subscription renews on March 1, 2026</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold">$49<span class="text-base font-normal text-base-content/60">/mo</span></div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-xl font-bold">Unlimited</div>
                    <div class="text-sm text-base-content/60">Classes</div>
                </div>
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-xl font-bold">5</div>
                    <div class="text-sm text-base-content/60">Team Members</div>
                </div>
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-xl font-bold">1,000</div>
                    <div class="text-sm text-base-content/60">Students</div>
                </div>
                <div class="text-center p-4 bg-base-200 rounded-lg">
                    <div class="text-xl font-bold">0%</div>
                    <div class="text-sm text-base-content/60">Platform Fee</div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="btn btn-soft">Change Plan</button>
                <button class="btn btn-ghost text-error">Cancel Subscription</button>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Compare Plans</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-base-content/10 rounded-lg p-6">
                    <div class="text-lg font-semibold mb-1">Starter</div>
                    <div class="text-2xl font-bold mb-4">$19<span class="text-base font-normal text-base-content/60">/mo</span></div>
                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 50 classes/month</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 2 team members</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 200 students</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 2% platform fee</li>
                    </ul>
                    <button class="btn btn-soft btn-sm w-full">Downgrade</button>
                </div>

                <div class="border-2 border-primary rounded-lg p-6 relative">
                    <span class="badge badge-primary absolute -top-3 left-1/2 -translate-x-1/2">Current Plan</span>
                    <div class="text-lg font-semibold mb-1">Pro</div>
                    <div class="text-2xl font-bold mb-4">$49<span class="text-base font-normal text-base-content/60">/mo</span></div>
                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> Unlimited classes</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 5 team members</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 1,000 students</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> 0% platform fee</li>
                    </ul>
                    <button class="btn btn-primary btn-sm w-full" disabled>Current Plan</button>
                </div>

                <div class="border border-base-content/10 rounded-lg p-6">
                    <div class="text-lg font-semibold mb-1">Enterprise</div>
                    <div class="text-2xl font-bold mb-4">$149<span class="text-base font-normal text-base-content/60">/mo</span></div>
                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> Unlimited everything</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> Unlimited team</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> Priority support</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--check] size-4 text-success"></span> Custom branding</li>
                    </ul>
                    <button class="btn btn-soft btn-sm w-full">Upgrade</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
