@extends('layouts.dashboard')

@section('title', 'Edit ' . $instructor->name . ' — Instructor')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('instructors.index') }}">Instructors</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('instructors.show', $instructor) }}">{{ $instructor->name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Back Button --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Instructor</h1>
            <p class="text-base-content/60">Update {{ $instructor->name }}'s profile.</p>
        </div>
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

    <form action="{{ route('instructors.update', $instructor) }}" method="POST">
        @csrf
        @method('PUT')
        @include('host.instructors._form')
    </form>
</div>
@endsection
