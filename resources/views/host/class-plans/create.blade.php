@extends('layouts.dashboard')

@section('title', 'Create Class Plan')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'classes']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Class Plan</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('catalog.index', ['tab' => 'classes']) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Class Plan</h1>
            <p class="text-base-content/60 mt-1">Define a template for group classes.</p>
        </div>
    </div>

    <form action="{{ route('class-plans.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('host.class-plans._form')
    </form>
</div>
@endsection
