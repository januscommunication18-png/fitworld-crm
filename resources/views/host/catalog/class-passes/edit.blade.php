@extends('layouts.dashboard')

@section('title', 'Edit Class Pass')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}">Class Passes</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $classPass->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Class Pass</h1>
            <p class="text-base-content/60 mt-1">Update the details for this class pass.</p>
        </div>
    </div>

    <form action="{{ route('class-passes.update', $classPass) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('host.catalog.class-passes._form')
    </form>
</div>
@endsection
