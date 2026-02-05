@extends('layouts.dashboard')

@section('title', 'Offers')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--gift] me-1 size-4"></span> Offers</li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Offers</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-base-100 hover:shadow-md transition-shadow cursor-pointer"><div class="card-body items-center text-center p-6"><span class="icon-[tabler--star] size-10 text-warning mb-2"></span><h3 class="font-semibold">Intro Offers</h3><p class="text-xs text-base-content/60">First-visit deals</p><span class="badge badge-sm mt-2">3 active</span></div></div>
        <div class="card bg-base-100 hover:shadow-md transition-shadow cursor-pointer"><div class="card-body items-center text-center p-6"><span class="icon-[tabler--package] size-10 text-primary mb-2"></span><h3 class="font-semibold">Class Packs</h3><p class="text-xs text-base-content/60">Bundled class credits</p><span class="badge badge-sm mt-2">5 active</span></div></div>
        <div class="card bg-base-100 hover:shadow-md transition-shadow cursor-pointer"><div class="card-body items-center text-center p-6"><span class="icon-[tabler--id-badge] size-10 text-success mb-2"></span><h3 class="font-semibold">Memberships</h3><p class="text-xs text-base-content/60">Recurring plans</p><span class="badge badge-sm mt-2">4 active</span></div></div>
        <div class="card bg-base-100 hover:shadow-md transition-shadow cursor-pointer"><div class="card-body items-center text-center p-6"><span class="icon-[tabler--percentage] size-10 text-error mb-2"></span><h3 class="font-semibold">Promo Codes</h3><p class="text-xs text-base-content/60">Discount codes</p><span class="badge badge-sm mt-2">2 active</span></div></div>
    </div>
</div>
@endsection
