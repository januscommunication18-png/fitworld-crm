@extends('layouts.dashboard')

@section('title', 'Rental Items')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Rental Items</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Rental Items</h1>
            <p class="text-base-content/60 mt-1">Manage equipment and items available for rent.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rentals.fulfillment.index') }}" class="btn btn-ghost">
                <span class="icon-[tabler--clipboard-check] size-5"></span>
                Fulfillment
            </a>
            <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Item
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('rentals.index') }}" class="tab {{ !$status && !$category ? 'tab-active' : '' }}">All</a>
            <a href="{{ route('rentals.index', ['status' => 'active']) }}" class="tab {{ $status === 'active' ? 'tab-active' : '' }}">Active</a>
            <a href="{{ route('rentals.index', ['status' => 'low_stock']) }}" class="tab {{ $status === 'low_stock' ? 'tab-active' : '' }}">Low Stock</a>
            <a href="{{ route('rentals.index', ['status' => 'out_of_stock']) }}" class="tab {{ $status === 'out_of_stock' ? 'tab-active' : '' }}">Out of Stock</a>
        </div>

        @if(count($categories) > 0)
        <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
            <option value="{{ route('rentals.index', ['status' => $status]) }}" {{ !$category ? 'selected' : '' }}>All Categories</option>
            @foreach($categories as $key => $label)
                <option value="{{ route('rentals.index', ['category' => $key, 'status' => $status]) }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @endif
    </div>

    {{-- Content --}}
    @if($rentalItems->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--package] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Rental Items Yet</h3>
                <p class="text-base-content/60 mb-4">Add equipment, mats, towels, and other items for members to rent.</p>
                <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add First Item
                </a>
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Deposit</th>
                                <th>Inventory</th>
                                <th>Status</th>
                                <th class="w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentalItems as $item)
                            <tr class="{{ !$item->is_active ? 'opacity-60' : '' }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($item->primary_image)
                                            <img src="{{ Storage::url($item->primary_image) }}" alt="{{ $item->name }}" class="w-12 h-12 object-cover rounded-lg">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-base-200 flex items-center justify-center">
                                                <span class="icon-[tabler--{{ $item->category_icon }}] size-6 text-base-content/40"></span>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('rentals.show', $item) }}" class="font-medium hover:text-primary">
                                                {{ $item->name }}
                                            </a>
                                            @if($item->sku)
                                                <p class="text-xs text-base-content/60">SKU: {{ $item->sku }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ $item->formatted_category }}</span>
                                </td>
                                <td class="font-medium">{{ $item->getFormattedPriceForCurrency() }}</td>
                                <td>{{ $item->getFormattedDepositForCurrency() }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="@if($item->available_inventory <= 0) text-error @elseif($item->isLowStock()) text-warning @else text-success @endif font-medium">
                                            {{ $item->available_inventory }}
                                        </span>
                                        <span class="text-base-content/40">/ {{ $item->total_inventory }}</span>
                                    </div>
                                    @if($item->available_inventory <= 0)
                                        <span class="badge badge-error badge-xs mt-1">Out of Stock</span>
                                    @elseif($item->isLowStock())
                                        <span class="badge badge-warning badge-xs mt-1">Low Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('rentals.toggle-status', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="badge cursor-pointer {{ $item->is_active ? 'badge-success' : 'badge-neutral' }} badge-soft badge-sm">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('rentals.show', $item) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('rentals.edit', $item) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form action="{{ route('rentals.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
