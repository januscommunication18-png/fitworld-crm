@extends('layouts.dashboard')

@section('title', 'Create Service Slot')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('service-slots.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Service Slots</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Slot</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('service-slots.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Service Slot</h1>
            <p class="text-base-content/60 mt-1">Add a new available time slot for a service.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-error mb-4">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <div>
            <div class="font-medium">Please fix the following errors:</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-error mb-4">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('service-slots.store') }}" method="POST">
        @csrf
        @include('host.service-slots._form')
    </form>
</div>
@endsection
