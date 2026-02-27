@extends('layouts.dashboard')

@section('title', $trans['instructors.add_instructor'] ?? 'Add Instructor')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('instructors.index') }}">{{ $trans['nav.instructors'] ?? 'Instructors' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['instructors.add_instructor'] ?? 'Add Instructor' }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('instructors.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold mb-2">{{ $trans['instructors.add_new_instructor'] ?? 'Add New Instructor' }}</h1>
            <p class="text-base-content/60">{{ $trans['instructors.create_description'] ?? 'Create a new instructor profile or link an existing team member.' }}</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
    <div class="alert alert-soft alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('instructors.store') }}" method="POST" id="instructor-form">
        @csrf
        @include('host.instructors._form')
    </form>
</div>
@endsection
