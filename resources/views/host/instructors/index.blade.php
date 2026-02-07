@extends('layouts.dashboard')

@section('title', 'Instructors')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--user-star] me-1 size-4"></span> Instructors</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Instructors</h1>
        <button class="btn btn-primary btn-sm"><span class="icon-[tabler--plus] size-4 mr-1"></span>Add Instructor</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card bg-base-100"><div class="card-body p-4 flex flex-row items-center gap-4"><div class="avatar avatar-placeholder"><div class="bg-primary text-primary-content size-12 rounded-full font-bold">JS</div></div><div class="flex-1"><div class="font-semibold">Jane Smith</div><div class="text-xs text-base-content/60">Yoga, Pilates</div><span class="badge badge-success badge-soft badge-xs mt-1">Owner</span></div>
            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Schedule</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--wallet] size-4 me-2"></span>Manage Pay</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div>
        </div></div>
        <div class="card bg-base-100"><div class="card-body p-4 flex flex-row items-center gap-4"><div class="avatar avatar-placeholder"><div class="bg-secondary text-secondary-content size-12 rounded-full font-bold">MJ</div></div><div class="flex-1"><div class="font-semibold">Mike Johnson</div><div class="text-xs text-base-content/60">Power Yoga, HIIT</div><span class="badge badge-info badge-soft badge-xs mt-1">Active</span></div>
            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Schedule</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--wallet] size-4 me-2"></span>Manage Pay</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div>
        </div></div>
        <div class="card bg-base-100"><div class="card-body p-4 flex flex-row items-center gap-4"><div class="avatar avatar-placeholder"><div class="bg-accent text-accent-content size-12 rounded-full font-bold">SL</div></div><div class="flex-1"><div class="font-semibold">Sarah Lee</div><div class="text-xs text-base-content/60">Pilates, Barre</div><span class="badge badge-info badge-soft badge-xs mt-1">Active</span></div>
            <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Schedule</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--wallet] size-4 me-2"></span>Manage Pay</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div>
        </div></div>
    </div>
</div>
@endsection
