@extends('layouts.dashboard')

@section('title', 'Add Rental Item')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('rentals.index') }}">Rental Items</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Add Item</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('rentals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('host.rentals._form', ['rental' => null])
</form>
@endsection
