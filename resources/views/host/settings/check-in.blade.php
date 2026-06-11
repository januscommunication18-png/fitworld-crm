@extends('layouts.settings')

@section('title', 'Check-In — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Check-In</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div>
        <h1 class="text-xl font-semibold">Check-In</h1>
        <p class="text-base-content/60 text-sm">Control digital QR check-in and the time window around sessions.</p>
    </div>

    <form method="POST" action="{{ route('settings.check-in.update') }}">
        @csrf
        @method('PUT')

        <div class="card bg-base-100">
            <div class="card-body space-y-5">

                {{-- Toggles --}}
                @php
                    $toggles = [
                        ['enable_digital_checkin', 'Enable digital check-in', 'Show the Digital Check-In page in the dashboard.'],
                        ['enable_qr_checkin', 'Enable QR scanning', 'Allow staff to scan a client QR code. When off, only manual lookup is available.'],
                        ['allow_self_checkin', 'Allow self check-in', 'Members can check themselves in (member portal / their own device).'],
                        ['allow_staff_override', 'Allow staff override', 'Staff can override the window or an unpaid booking to force a check-in.'],
                        ['block_unpaid_checkin', 'Block unpaid check-ins', 'Prevent check-in when a booking is not paid (unless overridden).'],
                    ];
                @endphp

                @foreach($toggles as [$name, $label, $help])
                <label class="flex items-start gap-3 cursor-pointer" for="{{ $name }}">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
                           class="checkbox checkbox-primary mt-1" {{ ($policies[$name] ?? false) ? 'checked' : '' }}>
                    <span>
                        <span class="font-medium block">{{ $label }}</span>
                        <span class="text-base-content/60 text-sm">{{ $help }}</span>
                    </span>
                </label>
                @endforeach

                <div class="divider my-1"></div>

                {{-- Window --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="self_checkin_window_minutes">Open check-in before class (minutes)</label>
                        <input type="number" id="self_checkin_window_minutes" name="self_checkin_window_minutes" min="0" max="240"
                               class="input w-full mt-1" value="{{ $policies['self_checkin_window_minutes'] ?? 30 }}">
                    </div>
                    <div>
                        <label class="label-text" for="self_checkin_late_minutes">Allow late check-in after start (minutes)</label>
                        <input type="number" id="self_checkin_late_minutes" name="self_checkin_late_minutes" min="0" max="240"
                               class="input w-full mt-1" value="{{ $policies['self_checkin_late_minutes'] ?? 30 }}">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--device-floppy] size-4"></span> Save Check-In Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
