@extends('layouts.dashboard')

@section('title', 'Edit ' . $rental->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.index') }}">Rental Items</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $rental->name }}</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('rentals.update', $rental) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('host.rentals._form')
</form>
@endsection
