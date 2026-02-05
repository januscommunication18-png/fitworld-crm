@extends('layouts.dashboard')

@section('title', 'Bookings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Bookings</h1>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold">142</div><div class="text-xs text-base-content/60">All Bookings</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-success">36</div><div class="text-xs text-base-content/60">Upcoming</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-warning">8</div><div class="text-xs text-base-content/60">Cancellations</div></div></div>
        <div class="card bg-base-100"><div class="card-body p-4 text-center"><div class="text-2xl font-bold text-error">3</div><div class="text-xs text-base-content/60">No-Shows</div></div></div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Date</th><th>Class</th><th>Student</th><th>Type</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <tr><td>Feb 5</td><td>Morning Vinyasa</td><td>Amy Lopez</td><td>Monthly</td><td><span class="badge badge-success badge-soft badge-sm">Confirmed</span></td><td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Booking</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--user-x] size-4 me-2"></span>Mark No-Show</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Booking</span></a></li></ul></div></td></tr>
                        <tr><td>Feb 5</td><td>Power Yoga</td><td>Brian Kim</td><td>Class Pack</td><td><span class="badge badge-success badge-soft badge-sm">Confirmed</span></td><td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Booking</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--user-x] size-4 me-2"></span>Mark No-Show</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Booking</span></a></li></ul></div></td></tr>
                        <tr><td>Feb 4</td><td>Evening Flow</td><td>Carol Davis</td><td>Drop-in</td><td><span class="badge badge-error badge-soft badge-sm">Cancelled</span></td><td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Details</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Booking</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--user-x] size-4 me-2"></span>Mark No-Show</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--circle-x] size-4 me-2 text-error"></span><span class="text-error">Cancel Booking</span></a></li></ul></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
