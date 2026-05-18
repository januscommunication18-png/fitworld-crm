@extends('layouts.dashboard')

@section('title', $rental->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'item-rentals']) }}">Item Rentals</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $rental->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if(!empty($rental->images))
                <img src="{{ Storage::url($rental->primary_image) }}" alt="{{ $rental->name }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--{{ $rental->category_icon }}] size-10 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $rental->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $rental->is_active ? 'badge-success' : 'badge-neutral' }} badge-soft">
                        {{ $rental->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="badge badge-soft badge-primary">{{ $rental->formatted_category }}</span>
                    @if($rental->sku)
                        <span class="badge badge-ghost badge-sm">SKU: {{ $rental->sku }}</span>
                    @endif
                    @if($rental->available_inventory <= 0)
                        <span class="badge badge-soft badge-error">Out of Stock</span>
                    @elseif($rental->isLowStock())
                        <span class="badge badge-soft badge-warning">Low Stock</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('rentals.edit', $rental) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
            <x-actions-dropdown>
                <li><a href="{{ route('rentals.invoice.create') }}">
                    <span class="icon-[tabler--receipt] size-4"></span> New Rental
                </a></li>
                <li>
                    <form action="{{ route('rentals.destroy', $rental) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this rental item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                            <span class="icon-[tabler--trash] size-4"></span> Delete Item
                        </button>
                    </form>
                </li>
            </x-actions-dropdown>
            <a href="{{ route('catalog.index', ['tab' => 'item-rentals']) }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
        </div>
    </div>

    {{-- Description --}}
    @if($rental->description)
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--file-description] size-5"></span>
                    Description
                </h2>
                <p class="mt-2 whitespace-pre-line">{{ $rental->description }}</p>
            </div>
        </div>
    @endif

    {{-- Item Details --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--info-circle] size-5"></span>
                Item Details
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <label class="text-sm text-base-content/60">Category</label>
                    <p class="font-medium">{{ $rental->formatted_category }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">SKU</label>
                    <p class="font-medium">{{ $rental->sku ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Return Required</label>
                    <p class="font-medium">{{ $rental->requires_return ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Max Rental Days</label>
                    <p class="font-medium">{{ $rental->max_rental_days ? $rental->max_rental_days . ' days' : 'No limit' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--box] size-5"></span>
                Inventory
            </h2>
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="text-center p-4 rounded-lg {{ $rental->available_inventory <= 0 ? 'bg-error/10' : ($rental->isLowStock() ? 'bg-warning/10' : 'bg-success/10') }}">
                    <div class="text-2xl font-bold {{ $rental->available_inventory <= 0 ? 'text-error' : ($rental->isLowStock() ? 'text-warning' : 'text-success') }}">
                        {{ $rental->available_inventory }}
                    </div>
                    <div class="text-sm text-base-content/60">Available</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-primary/10">
                    <div class="text-2xl font-bold text-primary">{{ $rental->total_inventory - $rental->available_inventory }}</div>
                    <div class="text-sm text-base-content/60">Rented Out</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-base-200/50">
                    <div class="text-2xl font-bold">{{ $rental->total_inventory }}</div>
                    <div class="text-sm text-base-content/60">Total</div>
                </div>
            </div>

            {{-- Adjust Inventory --}}
            <div class="mt-4 pt-4 border-t border-base-200">
                <h3 class="text-sm font-medium mb-3">Adjust Inventory</h3>
                <form action="{{ route('rentals.adjust-inventory', $rental) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="number" name="adjustment" class="input input-bordered input-sm flex-1" placeholder="Enter adjustment (+/-)" required>
                    <input type="text" name="notes" class="input input-bordered input-sm flex-[2]" placeholder="Reason for adjustment" required>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--check] size-4"></span>
                        Apply
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    @php
        $host = auth()->user()->host;
        $hostCurrencies = $host->currencies ?? ['USD'];
        $currencySymbols = ['USD' => '$', 'CAD' => 'C$', 'GBP' => '£', 'EUR' => '€', 'AUD' => 'A$', 'INR' => '₹'];
    @endphp
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--currency-dollar] size-5"></span>
                Pricing
            </h2>
            <div class="overflow-x-auto mt-4">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Price Type</th>
                            @foreach($hostCurrencies as $currency)
                                <th class="text-center">{{ $currency }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-base-content/70">Rental Price</td>
                            @foreach($hostCurrencies as $currency)
                                <td class="text-center font-medium">
                                    @if(!empty($rental->prices[$currency]))
                                        {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($rental->prices[$currency], 2) }}
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-base-content/70">Security Deposit</td>
                            @foreach($hostCurrencies as $currency)
                                <td class="text-center font-medium">
                                    @if(!empty($rental->deposit_prices[$currency]))
                                        {{ $currencySymbols[$currency] ?? $currency }}{{ number_format($rental->deposit_prices[$currency], 2) }}
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Associated Classes --}}
    @if($rental->classPlans->isNotEmpty())
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--yoga] size-5"></span>
                Associated Classes
            </h2>
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach($rental->classPlans as $classPlan)
                    <span class="badge badge-soft badge-primary">
                        {{ $classPlan->name }}
                        @if($classPlan->pivot->is_required)
                            <span class="badge badge-warning badge-xs ml-1">Required</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Who Can Rent --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--users] size-5"></span>
                Who Can Rent
            </h2>
            <div class="mt-3">
                @if($rental->isAvailableToAll())
                    <div class="flex items-center gap-3">
                        <span class="icon-[tabler--check-circle] size-6 text-success"></span>
                        <div>
                            <p class="font-medium">Everyone</p>
                            <p class="text-sm text-base-content/60">All customers can rent this item</p>
                        </div>
                    </div>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($rental->eligibility as $eligibility)
                            <span class="badge badge-soft badge-primary">
                                @if($eligibility->eligible_type === 'membership')
                                    {{ $eligibility->membershipPlan?->name }}
                                @elseif($eligibility->eligible_type === 'class_pack')
                                    {{ $eligibility->classPack?->name }}
                                @endif
                                @if($eligibility->is_free)
                                    <span class="badge badge-success badge-xs ml-1">Free</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Images --}}
    @if(!empty($rental->images) && count($rental->images) > 1)
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--photo] size-5"></span>
                Images
            </h2>
            <div class="grid grid-cols-4 md:grid-cols-6 gap-2 mt-3">
                @foreach($rental->images as $image)
                    <img src="{{ Storage::url($image) }}" alt="{{ $rental->name }}" class="w-full aspect-square object-cover rounded-lg">
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- File Attachments --}}
    @include('host.partials._file-attachments-show', ['fileAttachments' => $rental->file_attachments])

    {{-- Recent Bookings --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--calendar] size-5"></span>
                Recent Bookings
            </h2>

            @if($recentBookings->isEmpty())
                <p class="text-base-content/60 text-center py-8">No bookings yet.</p>
            @else
                <div class="overflow-x-auto mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                                <tr>
                                    <td>{{ $booking->rental_date->format('M j, Y') }}</td>
                                    <td>{{ $booking->client?->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->quantity }}</td>
                                    <td>
                                        <span class="badge {{ $booking->status_badge_class }} badge-soft badge-sm">
                                            {{ $booking->formatted_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Inventory History --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--history] size-5"></span>
                Inventory History
            </h2>

            @if($inventoryLogs->isEmpty())
                <p class="text-base-content/60 text-center py-8">No inventory changes logged.</p>
            @else
                <div class="overflow-x-auto mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Change</th>
                                <th>After</th>
                                <th>User</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryLogs as $log)
                                <tr>
                                    <td class="text-sm">{{ $log->created_at->format('M j, Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $log->action_badge_class }} badge-soft badge-sm">
                                            {{ $log->formatted_action }}
                                        </span>
                                    </td>
                                    <td class="font-medium {{ $log->quantity_change > 0 ? 'text-success' : 'text-error' }}">
                                        {{ $log->formatted_quantity_change }}
                                    </td>
                                    <td>{{ $log->inventory_after }}</td>
                                    <td class="text-sm">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="text-sm text-base-content/60 max-w-xs truncate">{{ $log->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
