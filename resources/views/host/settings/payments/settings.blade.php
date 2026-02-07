@extends('layouts.settings')

@section('title', 'Payment Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Payment Settings</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Payment Methods</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--credit-card] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Credit/Debit Cards</div>
                            <div class="text-sm text-base-content/60">Accept Visa, Mastercard, Amex via Stripe</div>
                        </div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--wallet] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Apple Pay / Google Pay</div>
                            <div class="text-sm text-base-content/60">Digital wallet payments</div>
                        </div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--cash] size-8 text-success"></span>
                        <div>
                            <div class="font-medium">Cash Payments</div>
                            <div class="text-sm text-base-content/60">Accept cash at studio (manual entry)</div>
                        </div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" />
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Currency & Locale</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text" for="currency">Currency</label>
                    <select id="currency" class="select w-full">
                        <option selected>USD ($) - US Dollar</option>
                        <option>EUR (€) - Euro</option>
                        <option>GBP (£) - British Pound</option>
                        <option>CAD ($) - Canadian Dollar</option>
                        <option>AUD ($) - Australian Dollar</option>
                    </select>
                </div>
                <div>
                    <label class="label-text" for="locale">Number Format</label>
                    <select id="locale" class="select w-full">
                        <option selected>1,234.56 (US)</option>
                        <option>1.234,56 (EU)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Receipt Settings</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Send email receipts</div>
                        <div class="text-sm text-base-content/60">Automatically email receipts after purchase</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>
                <div>
                    <label class="label-text" for="receipt_footer">Receipt Footer Text</label>
                    <textarea id="receipt_footer" class="textarea w-full" rows="2" placeholder="Thank you for your purchase!"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="btn btn-primary">Save Changes</button>
    </div>
</div>
@endsection
