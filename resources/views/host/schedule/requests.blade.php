@extends('layouts.dashboard')

@section('title', 'Schedule Requests')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Requests</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Schedule Requests</h1>
        <p class="text-base-content/60 mt-1">Manage class and appointment requests from clients.</p>
    </div>

    {{-- Sub Navigation --}}
    @include('host.schedule.partials.sub-nav')

    {{-- Coming Soon Card --}}
    <div class="card bg-base-100">
        <div class="card-body text-center py-16">
            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
                <span class="icon-[tabler--message-question] size-10 text-primary"></span>
            </div>
            <h2 class="text-2xl font-bold mb-2">Coming Soon</h2>
            <p class="text-base-content/60 max-w-md mx-auto mb-6">
                The Requests feature is currently in development. Soon you'll be able to receive and manage scheduling requests from your clients.
            </p>
            <div class="flex flex-col items-center gap-4">
                <div class="bg-base-200/50 rounded-xl p-4 max-w-sm">
                    <h3 class="font-semibold mb-2">What's coming:</h3>
                    <ul class="text-sm text-base-content/70 space-y-2 text-left">
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Clients can request specific class times
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Approve or decline requests with one click
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Automatic notifications to clients
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                            Convert requests to scheduled sessions
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
