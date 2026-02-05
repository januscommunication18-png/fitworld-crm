@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right]"></span>
    </li>
    <li aria-current="page">
        <span class="icon-[tabler--home] me-1 size-4"></span> Dashboard
    </li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Welcome to your dashboard</h1>

    {{-- Stats row --}}
    <div class="flex flex-wrap gap-4">
        <div class="stats flex-1 min-w-48">
            <div class="stat">
                <div class="stat-title">Students</div>
                <div class="stat-value text-primary">0</div>
                <div class="stat-desc">Active this month</div>
            </div>
        </div>
        <div class="stats flex-1 min-w-48">
            <div class="stat">
                <div class="stat-title">Classes</div>
                <div class="stat-value text-success">0</div>
                <div class="stat-desc">Scheduled this week</div>
            </div>
        </div>
        <div class="stats flex-1 min-w-48">
            <div class="stat">
                <div class="stat-title">Revenue</div>
                <div class="stat-value text-warning">$0</div>
                <div class="stat-desc">This month</div>
            </div>
        </div>
        <div class="stats flex-1 min-w-48">
            <div class="stat">
                <div class="stat-title">Bookings</div>
                <div class="stat-value text-info">0</div>
                <div class="stat-desc">Today</div>
            </div>
        </div>
    </div>

    {{-- Quick actions card --}}
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Get Started</h5>
            <p class="text-base-content/60 mb-4">Complete your studio setup to start accepting bookings.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ url('/schedule/classes') }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--calendar-plus] size-4"></span> Add a Class
                </a>
                <a href="{{ url('/instructors') }}" class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span> Add Instructor
                </a>
                <a href="{{ url('/settings') }}" class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--settings] size-4"></span> Studio Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
