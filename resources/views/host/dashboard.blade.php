@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <ol>
        <li>
            <a href="{{ url('/dashboard') }}">
                <span class="icon-[tabler--home] size-4"></span> Dashboard
            </a>
        </li>
        <li class="breadcrumbs-separator rtl:rotate-180">
            <span class="icon-[tabler--chevron-right]"></span>
        </li>
        <li aria-current="page">
            <span class="icon-[tabler--home] me-1 size-4"></span> Dashboard
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <div class="flex gap-2">
            <button class="btn btn-soft btn-sm"><span class="icon-[tabler--download] size-4 mr-1"></span>Export</button>
            <button class="btn btn-primary btn-sm"><span class="icon-[tabler--plus] size-4 mr-1"></span>Quick Add</button>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2.5"><span class="icon-[tabler--users] size-6 text-primary"></span></div>
                    <div><div class="text-2xl font-bold">248</div><div class="text-xs text-base-content/60">Total Students</div></div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2.5"><span class="icon-[tabler--calendar-event] size-6 text-success"></span></div>
                    <div><div class="text-2xl font-bold">12</div><div class="text-xs text-base-content/60">Classes Today</div></div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2.5"><span class="icon-[tabler--book] size-6 text-warning"></span></div>
                    <div><div class="text-2xl font-bold">36</div><div class="text-xs text-base-content/60">Upcoming Bookings</div></div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2.5"><span class="icon-[tabler--coin] size-6 text-info"></span></div>
                    <div><div class="text-2xl font-bold">$4,280</div><div class="text-xs text-base-content/60">Revenue (MTD)</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Classes --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Today's Classes</h2>
                <button class="btn btn-soft btn-xs"><span class="icon-[tabler--filter] size-3 mr-1"></span>Filter</button>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Time</th><th>Class</th><th>Instructor</th><th>Room</th><th>Booked</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="font-medium">6:00 AM</td><td>Morning Vinyasa</td><td>Jane Smith</td><td>Studio A</td><td>18/20</td><td><span class="badge badge-success badge-soft badge-sm">Active</span></td><td>
                            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Class</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--users] size-4 me-2"></span>Attendance</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Class</span></a></li>
                                </ul>
                            </div>
                        </td></tr>
                        <tr><td class="font-medium">8:30 AM</td><td>Power Yoga</td><td>Mike Johnson</td><td>Studio B</td><td>12/15</td><td><span class="badge badge-success badge-soft badge-sm">Active</span></td><td>
                            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Class</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--users] size-4 me-2"></span>Attendance</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Class</span></a></li>
                                </ul>
                            </div>
                        </td></tr>
                        <tr><td class="font-medium">12:00 PM</td><td>Lunch Pilates</td><td>Sarah Lee</td><td>Studio A</td><td>8/12</td><td><span class="badge badge-warning badge-soft badge-sm">Upcoming</span></td><td>
                            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Class</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--users] size-4 me-2"></span>Attendance</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Class</span></a></li>
                                </ul>
                            </div>
                        </td></tr>
                        <tr><td class="font-medium">5:30 PM</td><td>Evening Flow</td><td>Jane Smith</td><td>Studio A</td><td>20/20</td><td><span class="badge badge-error badge-soft badge-sm">Full</span></td><td>
                            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Class</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--users] size-4 me-2"></span>Attendance</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Class</span></a></li>
                                </ul>
                            </div>
                        </td></tr>
                        <tr><td class="font-medium">7:00 PM</td><td>Restorative</td><td>Sarah Lee</td><td>Studio B</td><td>6/10</td><td><span class="badge badge-warning badge-soft badge-sm">Upcoming</span></td><td>
                            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Class</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--users] size-4 me-2"></span>Attendance</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Class</span></a></li>
                                </ul>
                            </div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Alerts & Reminders --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Alerts &amp; Reminders</h2>
            <div class="space-y-3">
                <div class="alert alert-soft alert-warning"><span class="icon-[tabler--alert-triangle] size-5"></span><div><strong>3 students</strong> have expiring memberships this week.</div></div>
                <div class="alert alert-soft alert-info"><span class="icon-[tabler--bell] size-5"></span><div>Instructor <strong>Mike Johnson</strong> requested time off for next Monday.</div></div>
                <div class="alert alert-soft alert-success"><span class="icon-[tabler--circle-check] size-5"></span><div><strong>5 new students</strong> signed up this week via your booking page.</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
