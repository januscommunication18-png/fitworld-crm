@extends('layouts.dashboard')

@section('title', $trans['space_rentals.edit_space'] ?? 'Edit Rentable Space')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('space-rentals.config.index') }}">{{ $trans['nav.space_rentals_config'] ?? 'Rentable Spaces' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $config->name }}</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('space-rentals.config.update', $config) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('host.space-rentals.config._form', ['config' => $config])
</form>
@endsection
