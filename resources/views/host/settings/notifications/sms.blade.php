@extends('layouts.settings')

@section('title', 'SMS Notifications — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">SMS Notifications</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <div class="font-medium">SMS is an add-on feature</div>
            <div class="text-sm">Send text message reminders and notifications to your students.</div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--message] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h2 class="text-xl font-semibold mb-2">Enable SMS Notifications</h2>
            <p class="text-base-content/60 mb-6 max-w-md mx-auto">
                Send text message reminders to reduce no-shows and keep students engaged. SMS messages cost $0.02 per message.
            </p>

            <div class="bg-base-200 rounded-lg p-6 max-w-sm mx-auto mb-6">
                <div class="text-3xl font-bold text-primary mb-1">$0.02</div>
                <div class="text-sm text-base-content/60">per SMS message</div>
                <div class="divider my-4"></div>
                <ul class="text-sm text-left space-y-2">
                    <li class="flex items-center gap-2">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        Class reminders (1hr before)
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        Booking confirmations
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        Waitlist notifications
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        Custom broadcast messages
                    </li>
                </ul>
            </div>

            <button class="btn btn-primary">
                <span class="icon-[tabler--plus] size-4"></span> Enable SMS
            </button>
        </div>
    </div>
</div>
@endsection
