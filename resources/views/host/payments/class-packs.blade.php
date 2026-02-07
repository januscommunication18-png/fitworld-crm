@extends('layouts.dashboard')

@section('title', 'Class Packs')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ url('/payments/transactions') }}"><span class="icon-[tabler--credit-card] me-1 size-4"></span> Payments</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--package] me-1 size-4"></span> Class Packs</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Class Packs</h1>
        <a href="#" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span> Create Pack
        </a>
    </div>

    {{-- Class packs grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Empty state --}}
        <div class="col-span-full">
            <div class="card">
                <div class="card-body">
                    <div class="flex flex-col items-center gap-3 py-8">
                        <span class="icon-[tabler--packages] size-12 text-base-content/20"></span>
                        <p class="text-base-content/60 text-lg">No class packs created yet</p>
                        <p class="text-base-content/40 text-sm">Create class packs to sell bundles of classes to your students.</p>
                        <a href="#" class="btn btn-primary btn-sm mt-2">
                            <span class="icon-[tabler--plus] size-4"></span> Create First Pack
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
