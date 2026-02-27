@extends('layouts.dashboard')

@section('title', $trans['space_rentals.add_space'] ?? 'Add Rentable Space')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('space-rentals.config.index') }}">{{ $trans['nav.space_rentals_config'] ?? 'Rentable Spaces' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['space_rentals.add_space'] ?? 'Add Space' }}</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('space-rentals.config.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('host.space-rentals.config._form', ['config' => null])
</form>
@endsection
