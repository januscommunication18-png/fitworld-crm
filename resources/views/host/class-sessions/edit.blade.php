@extends('layouts.dashboard')

@section('title', 'Edit Class Session')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit Session</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Class Session</h1>
            <p class="text-base-content/60 mt-1">Update the class session details.</p>
        </div>
    </div>

    <form action="{{ route('class-sessions.update', $classSession) }}" method="POST">
        @csrf
        @method('PUT')
        @include('host.class-sessions._form')
    </form>
</div>
@endsection
