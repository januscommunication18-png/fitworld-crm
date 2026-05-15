@extends('layouts.settings')

@section('title', 'Edit Instructor — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.instructors') }}">Instructors</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $instructor->name }}</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Edit Instructor</h2>
            <p class="text-sm text-base-content/60">Update instructor profile and settings</p>
        </div>
        <a href="{{ route('settings.team.instructors') }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    {{-- Incomplete Profile Warning --}}
    @if(!empty($missingFields))
    <div class="alert alert-soft alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <p class="font-medium">Instructor profile is incomplete</p>
            <p class="text-sm opacity-90">This instructor cannot be assigned to classes until the following fields are completed: <strong>{{ implode(', ', $missingFields) }}</strong></p>
        </div>
    </div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('settings.team.instructors.update', $instructor) }}" method="POST">
        @csrf
        @method('PUT')
        @include('host.settings.team.instructors._form')
    </form>
</div>
@endsection
