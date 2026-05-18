@extends('layouts.dashboard')

@section('title', $trans['space_rentals.edit_space'] ?? 'Edit Rentable Space')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}">Rental Spaces</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $config->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['space_rentals.edit_space'] ?? 'Edit Rentable Space' }}</h1>
            <p class="text-base-content/60 mt-1">Update the configuration for this rentable space.</p>
        </div>
        <a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    <x-form-validate action="{{ route('space-rentals.config.update', $config) }}" method="PUT" :has-files="true">
        @include('host.space-rentals.config._form', ['config' => $config])
    </x-form-validate>
</div>
@endsection
