@extends('layouts.dashboard')

@section('title', ($trans['btn.edit'] ?? 'Edit') . ' ' . $instructor->name . ' — ' . ($trans['nav.instructor'] ?? 'Instructor'))

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        @if(request('ref') === 'team')
        <li><a href="{{ route('settings.index') }}">{{ $trans['nav.settings'] ?? 'Settings' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">{{ $trans['nav.team'] ?? 'Users & Roles' }}</a></li>
        @else
        <li><a href="{{ route('instructors.index') }}">{{ $trans['nav.instructors'] ?? 'Instructors' }}</a></li>
        @endif
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('instructors.show', array_filter(['instructor' => $instructor, 'ref' => request('ref')])) }}">{{ $instructor->name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['btn.edit'] ?? 'Edit' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Back Button --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['instructors.edit_instructor'] ?? 'Edit Instructor' }}</h1>
            <p class="text-base-content/60">{{ $trans['instructors.update_profile'] ?? 'Update' }} {{ $instructor->name }}{{ $trans['instructors.profile_suffix'] ?? "'s profile." }}</p>
        </div>
        <a href="{{ route('instructors.show', array_filter(['instructor' => $instructor, 'ref' => request('ref')])) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    {{-- Incomplete Profile Warning (owner / admin only — fields shown here are owner-only) --}}
    @php
        $canEditAdmin = auth()->user()->isOwner() || auth()->user()->hasPermission('team.instructor_admin');
    @endphp
    @if(!empty($missingFields) && $canEditAdmin)
    <div class="alert alert-soft alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <p class="font-medium">{{ $trans['instructors.profile_incomplete'] ?? 'Instructor profile is incomplete' }}</p>
            <p class="text-sm opacity-90">{{ $trans['instructors.cannot_assign_until'] ?? 'This instructor cannot be assigned to classes until the following fields are completed:' }} <strong>{{ implode(', ', $missingFields) }}</strong></p>
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
