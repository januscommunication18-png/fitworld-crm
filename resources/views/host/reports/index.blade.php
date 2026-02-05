@extends('layouts.dashboard')

@section('title', 'Insights')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--chart-bar] me-1 size-4"></span> Insights</li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Insights</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card bg-base-100"><div class="card-body"><h3 class="font-semibold mb-3"><span class="icon-[tabler--checks] size-5 mr-2 text-primary"></span>Attendance</h3><div class="bg-base-200/50 rounded-lg h-48 flex items-center justify-center text-base-content/40">Chart placeholder</div></div></div>
        <div class="card bg-base-100"><div class="card-body"><h3 class="font-semibold mb-3"><span class="icon-[tabler--coin] size-5 mr-2 text-success"></span>Revenue</h3><div class="bg-base-200/50 rounded-lg h-48 flex items-center justify-center text-base-content/40">Chart placeholder</div></div></div>
        <div class="card bg-base-100"><div class="card-body"><h3 class="font-semibold mb-3"><span class="icon-[tabler--trophy] size-5 mr-2 text-warning"></span>Class Performance</h3><div class="bg-base-200/50 rounded-lg h-48 flex items-center justify-center text-base-content/40">Chart placeholder</div></div></div>
        <div class="card bg-base-100"><div class="card-body"><h3 class="font-semibold mb-3"><span class="icon-[tabler--trending-up] size-5 mr-2 text-info"></span>Retention</h3><div class="bg-base-200/50 rounded-lg h-48 flex items-center justify-center text-base-content/40">Chart placeholder</div></div></div>
    </div>
</div>
@endsection
