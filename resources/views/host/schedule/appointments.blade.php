@extends('layouts.dashboard')

@section('title', 'Appointments')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right]"></span>
    </li>
    <li>
        <a href="{{ url('/schedule/classes') }}">
            <span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule
        </a>
    </li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right]"></span>
    </li>
    <li aria-current="page">
        <span class="icon-[tabler--calendar-check] me-1 size-4"></span> Appointments
    </li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Appointments</h1>
        <a href="#" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span> New Appointment
        </a>
    </div>

    {{-- Empty state --}}
    <div class="card">
        <div class="card-body">
            <div class="flex flex-col items-center gap-3 py-8">
                <span class="icon-[tabler--calendar-x] size-12 text-base-content/20"></span>
                <p class="text-base-content/60 text-lg">No appointments scheduled</p>
                <p class="text-base-content/40 text-sm">Appointments will appear here when students book one-on-one sessions.</p>
            </div>
        </div>
    </div>
</div>
@endsection
