@extends('layouts.dashboard')

@section('title', 'Edit Service Plan')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'services']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $servicePlan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Edit Service Plan</h1>
        <p class="text-base-content/60 mt-1">Update the service plan template.</p>
    </div>

    <form action="{{ route('service-plans.update', $servicePlan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('host.service-plans._form')
    </form>
</div>
@endsection
