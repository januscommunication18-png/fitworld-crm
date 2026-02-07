@extends('layouts.dashboard')

@section('title', 'Students')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--users] me-1 size-4"></span> Students</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Students</h1>
        <button class="btn btn-primary btn-sm"><span class="icon-[tabler--plus] size-4 mr-1"></span>Add Student</button>
    </div>

    <div class="flex flex-wrap gap-2">
        <button class="btn btn-primary btn-sm">All Students <span class="badge badge-sm ml-1">248</span></button>
        <button class="btn btn-soft btn-sm">Leads <span class="badge badge-sm ml-1">42</span></button>
        <button class="btn btn-soft btn-sm">Active <span class="badge badge-sm ml-1">186</span></button>
        <button class="btn btn-soft btn-sm">At-Risk <span class="badge badge-sm ml-1">20</span></button>
        <button class="btn btn-soft btn-sm">Tags</button>
    </div>

    <div class="card bg-base-100 mb-4">
        <div class="card-body p-3 flex flex-wrap gap-3 items-center">
            <div class="input-group flex-1 min-w-48">
                <span class="input-group-text"><span class="icon-[tabler--search] size-4"></span></span>
                <input type="text" class="input grow" placeholder="Search students..." />
            </div>
            <select class="select select-sm w-auto"><option>All Memberships</option><option>Monthly Unlimited</option><option>10-Class Pack</option><option>Drop-in</option></select>
            <select class="select select-sm w-auto"><option>Sort: Recent</option><option>Sort: Name A-Z</option><option>Sort: Last Visit</option></select>
            <button class="btn btn-soft btn-sm"><span class="icon-[tabler--download] size-4 mr-1"></span>Export</button>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th><input type="checkbox" class="checkbox checkbox-sm" /></th><th>Name</th><th>Email</th><th>Phone</th><th>Membership</th><th>Last Visit</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox" class="checkbox checkbox-sm" /></td>
                            <td><div class="flex items-center gap-3"><div class="avatar avatar-placeholder"><div class="bg-primary text-primary-content size-8 rounded-full text-xs font-bold">AL</div></div><div><div class="font-medium">Amy Lopez</div><div class="text-xs text-base-content/50">Joined Jan 15</div></div></div></td>
                            <td>amy@email.com</td><td>(512) 555-1234</td>
                            <td><span class="badge badge-primary badge-soft badge-sm">Monthly Unlimited</span></td>
                            <td>Today</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Active</span></td>
                            <td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Student</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--message-circle] size-4 me-2"></span>Send Message</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="checkbox checkbox-sm" /></td>
                            <td><div class="flex items-center gap-3"><div class="avatar avatar-placeholder"><div class="bg-secondary text-secondary-content size-8 rounded-full text-xs font-bold">BK</div></div><div><div class="font-medium">Brian Kim</div><div class="text-xs text-base-content/50">Joined Dec 3</div></div></div></td>
                            <td>brian@email.com</td><td>(512) 555-5678</td>
                            <td><span class="badge badge-info badge-soft badge-sm">10-Class Pack</span></td>
                            <td>2 days ago</td>
                            <td><span class="badge badge-success badge-soft badge-sm">Active</span></td>
                            <td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Student</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--message-circle] size-4 me-2"></span>Send Message</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="checkbox checkbox-sm" /></td>
                            <td><div class="flex items-center gap-3"><div class="avatar avatar-placeholder"><div class="bg-warning text-warning-content size-8 rounded-full text-xs font-bold">CD</div></div><div><div class="font-medium">Carol Davis</div><div class="text-xs text-base-content/50">Joined Nov 20</div></div></div></td>
                            <td>carol@email.com</td><td>(512) 555-9012</td>
                            <td><span class="badge badge-warning badge-soft badge-sm">Drop-in</span></td>
                            <td>14 days ago</td>
                            <td><span class="badge badge-warning badge-soft badge-sm">At-Risk</span></td>
                            <td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Student</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--message-circle] size-4 me-2"></span>Send Message</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="checkbox checkbox-sm" /></td>
                            <td><div class="flex items-center gap-3"><div class="avatar avatar-placeholder"><div class="bg-accent text-accent-content size-8 rounded-full text-xs font-bold">DM</div></div><div><div class="font-medium">Derek Martinez</div><div class="text-xs text-base-content/50">Joined Feb 1</div></div></div></td>
                            <td>derek@email.com</td><td>(512) 555-3456</td>
                            <td><span class="badge badge-neutral badge-soft badge-sm">Lead</span></td>
                            <td>-</td>
                            <td><span class="badge badge-neutral badge-soft badge-sm">Lead</span></td>
                            <td><div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]"><button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions"><span class="icon-[tabler--dots] size-4"></span></button><ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu"><li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View Profile</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit Student</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--message-circle] size-4 me-2"></span>Send Message</a></li><li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Remove</span></a></li></ul></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between p-4 border-t border-base-content/10">
                <div class="text-sm text-base-content/60">Showing 1-4 of 248 students</div>
                <div class="pagination">
                    <button class="btn btn-soft btn-xs" disabled>Prev</button>
                    <button class="btn btn-primary btn-xs">1</button>
                    <button class="btn btn-soft btn-xs">2</button>
                    <button class="btn btn-soft btn-xs">3</button>
                    <span class="text-base-content/40 px-1">...</span>
                    <button class="btn btn-soft btn-xs">25</button>
                    <button class="btn btn-soft btn-xs">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
