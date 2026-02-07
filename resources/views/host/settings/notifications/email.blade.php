@extends('layouts.settings')

@section('title', 'Email Notifications — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Email Notifications</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Notifications to You</h2>
            <p class="text-sm text-base-content/60 mb-4">Control which emails you receive as a studio owner</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">New Booking</div>
                        <div class="text-xs text-base-content/50">When a student books a class</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Cancellation</div>
                        <div class="text-xs text-base-content/50">When a student cancels a booking</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Payment Received</div>
                        <div class="text-xs text-base-content/50">When a payment is processed</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">New Student Signup</div>
                        <div class="text-xs text-base-content/50">When a new student creates an account</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Daily Summary</div>
                        <div class="text-xs text-base-content/50">Recap of the day's activity at 9pm</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Weekly Report</div>
                        <div class="text-xs text-base-content/50">Weekly business metrics every Monday</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Notifications to Students</h2>
            <p class="text-sm text-base-content/60 mb-4">Control which emails your students receive</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Booking Confirmation</div>
                        <div class="text-xs text-base-content/50">Sent when a booking is made</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Class Reminder</div>
                        <div class="text-xs text-base-content/50">Sent 24 hours before class</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Cancellation Confirmation</div>
                        <div class="text-xs text-base-content/50">Sent when they cancel a booking</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Waitlist Notification</div>
                        <div class="text-xs text-base-content/50">Sent when a spot opens up</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
                <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                    <div>
                        <div class="font-medium text-sm">Payment Receipt</div>
                        <div class="text-xs text-base-content/50">Sent after successful payment</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="btn btn-primary">Save Changes</button>
    </div>
</div>
@endsection
