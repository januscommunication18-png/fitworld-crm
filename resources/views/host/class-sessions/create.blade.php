@extends('layouts.dashboard')

@section('title', 'Schedule Class Session')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Schedule Class</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Schedule Class Session</h1>
        <p class="text-base-content/60 mt-1">Create a new class session from a class plan.</p>
    </div>

    <form action="{{ route('class-sessions.store') }}" method="POST">
        @csrf
        @include('host.class-sessions._form')
    </form>
</div>
@endsection
