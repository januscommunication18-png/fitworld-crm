@extends('layouts.dashboard')

@section('title', 'Schedule')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Schedule</h1>
        <button class="btn btn-primary btn-sm"><span class="icon-[tabler--plus] size-4 mr-1"></span>Add Class</button>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-2 mb-4">
                <button class="btn btn-primary btn-sm">Calendar View</button>
                <button class="btn btn-soft btn-sm">List View</button>
            </div>
            <div class="bg-base-200/50 rounded-lg h-96 flex items-center justify-center text-base-content/40">
                <div class="text-center"><span class="icon-[tabler--calendar-month] size-16 mb-2"></span><p>FullCalendar component loads here</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
