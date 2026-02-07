@extends('layouts.settings')

@section('title', 'PayPal — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">PayPal</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <span class="icon-[tabler--brand-paypal] size-12 text-[#003087]"></span>
                <div>
                    <h2 class="text-lg font-semibold">PayPal</h2>
                    <p class="text-base-content/60 text-sm">Accept PayPal payments from students</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-base-200 rounded-lg mb-6">
                <span class="icon-[tabler--info-circle] size-6 text-info"></span>
                <div>
                    <div class="font-medium">Not Connected</div>
                    <div class="text-sm text-base-content/60">Connect your PayPal business account to accept payments</div>
                </div>
            </div>

            <button class="btn btn-primary">
                <span class="icon-[tabler--brand-paypal] size-4"></span> Connect PayPal
            </button>
        </div>
    </div>
</div>
@endsection
