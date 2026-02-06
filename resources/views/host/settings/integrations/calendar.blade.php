@extends('layouts.settings')

@section('title', 'Calendar Sync — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Calendar Sync</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Calendar Integrations</h2>
            <p class="text-base-content/60 text-sm mb-6">Sync your class schedule with external calendars</p>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--brand-google] size-10 text-[#4285F4]"></span>
                        <div>
                            <div class="font-medium">Google Calendar</div>
                            <div class="text-sm text-base-content/60">Sync with your Google Calendar</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">Connect</button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--brand-apple] size-10"></span>
                        <div>
                            <div class="font-medium">Apple Calendar</div>
                            <div class="text-sm text-base-content/60">Sync with iCloud Calendar</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">Connect</button>
                </div>

                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--brand-windows] size-10 text-[#00A4EF]"></span>
                        <div>
                            <div class="font-medium">Outlook Calendar</div>
                            <div class="text-sm text-base-content/60">Sync with Microsoft Outlook</div>
                        </div>
                    </div>
                    <button class="btn btn-soft btn-sm">Connect</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">iCal Feed</h2>
            <p class="text-base-content/60 text-sm mb-4">Subscribe to your schedule in any calendar app that supports iCal</p>

            <div class="flex gap-2">
                <input type="text" class="input flex-1 font-mono text-sm" value="https://app.fitcrm.com/ical/abc123xyz" readonly />
                <button class="btn btn-soft btn-square" title="Copy URL">
                    <span class="icon-[tabler--copy] size-4"></span>
                </button>
            </div>
            <p class="text-xs text-base-content/50 mt-2">This is a read-only feed. Changes sync one-way from FitCRM to your calendar.</p>
        </div>
    </div>
</div>
@endsection
