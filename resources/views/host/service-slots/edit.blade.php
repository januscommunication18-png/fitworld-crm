@extends('layouts.dashboard')

@section('title', 'Edit Service Slot')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('service-slots.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Service Slots</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit Slot</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Edit Service Slot</h1>
        <p class="text-base-content/60 mt-1">Update the service slot details.</p>
    </div>

    <form action="{{ route('service-slots.update', $serviceSlot) }}" method="POST">
        @csrf
        @method('PUT')
        @include('host.service-slots._form')
    </form>
</div>
@endsection
