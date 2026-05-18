@extends('layouts.dashboard')

@section('title', 'Edit Service Plan')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'services']) }}">Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $servicePlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Edit Service Plan</h1>
            <p class="text-base-content/60 mt-1">Update the service plan template.</p>
        </div>
        <a href="{{ route('catalog.index', ['tab' => 'services']) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    <x-form-validate action="{{ route('service-plans.update', $servicePlan) }}" method="PUT" :has-files="true">
        @include('host.service-plans._form')
    </x-form-validate>
</div>
@endsection
