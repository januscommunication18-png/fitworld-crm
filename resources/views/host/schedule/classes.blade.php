@extends('layouts.dashboard')

@section('title', 'Class Schedule')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ url('/schedule/classes') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Schedule</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Class Schedule</h1>
        <a href="#" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span> Add Class
        </a>
    </div>

    {{-- Skeleton loading --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Type</th>
                            <th>Instructor</th>
                            <th>Duration</th>
                            <th>Capacity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Empty state --}}
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="flex flex-col items-center gap-3">
                                    <span class="icon-[tabler--calendar-off] size-12 text-base-content/20"></span>
                                    <p class="text-base-content/60 text-lg">No classes yet</p>
                                    <p class="text-base-content/40 text-sm">Create your first class to start building your schedule.</p>
                                    <a href="#" class="btn btn-primary btn-sm mt-2">
                                        <span class="icon-[tabler--plus] size-4"></span> Create First Class
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
