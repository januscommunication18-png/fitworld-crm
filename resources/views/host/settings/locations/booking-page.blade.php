@extends('layouts.settings')

@section('title', 'Booking Page — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Booking Page</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Booking Page Settings</h2>
                    <p class="text-base-content/60 text-sm">Customize how your public booking page looks and behaves</p>
                </div>
                <a href="#" class="btn btn-soft btn-sm" target="_blank">
                    <span class="icon-[tabler--external-link] size-4"></span> Preview
                </a>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium">Show class descriptions</div>
                        <div class="text-sm text-base-content/60">Display full class descriptions on the schedule</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium">Show instructor photos</div>
                        <div class="text-sm text-base-content/60">Display instructor profile photos next to classes</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium">Allow waitlist</div>
                        <div class="text-sm text-base-content/60">Let students join a waitlist when classes are full</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium">Require account to book</div>
                        <div class="text-sm text-base-content/60">Guests must create an account before booking</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" />
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Theme & Colors</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label class="text-sm text-base-content/60">Primary Color</label>
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-primary"></div>
                        <input type="text" class="input input-sm w-28" value="#6366f1" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm text-base-content/60">Background</label>
                    <select class="select select-sm w-full">
                        <option selected>Light</option>
                        <option>Dark</option>
                        <option>Auto (System)</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm text-base-content/60">Font</label>
                    <select class="select select-sm w-full">
                        <option selected>Inter</option>
                        <option>Roboto</option>
                        <option>Open Sans</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
