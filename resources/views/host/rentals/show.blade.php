@extends('layouts.dashboard')

@section('title', $rental->name)

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
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $rental->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge {{ $rental->is_active ? 'badge-success' : 'badge-neutral' }} badge-sm">
                        {{ $rental->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="badge badge-ghost badge-sm">{{ $rental->formatted_category }}</span>
                    @if($rental->sku)
                        <span class="text-sm text-base-content/60">SKU: {{ $rental->sku }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rentals.edit', $rental) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit Item
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Images & Description --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex gap-6">
                        @if(!empty($rental->images))
                            <div class="w-32 h-32 flex-shrink-0">
                                <img src="{{ Storage::url($rental->primary_image) }}" alt="{{ $rental->name }}"
                                     class="w-full h-full object-cover rounded-lg">
                            </div>
                        @else
                            <div class="w-32 h-32 flex-shrink-0 bg-base-200 rounded-lg flex items-center justify-center">
                                <span class="icon-[tabler--{{ $rental->category_icon }}] size-12 text-base-content/40"></span>
                            </div>
                        @endif
                        <div class="flex-1">
                            @if($rental->description)
                                <p class="text-base-content/80">{{ $rental->description }}</p>
                            @else
                                <p class="text-base-content/40 italic">No description provided.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Inventory Adjustment --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--adjustments] size-5"></span>
                        Adjust Inventory
                    </h2>

                    <form action="{{ route('rentals.adjust-inventory', $rental) }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                        @csrf
                        <div class="form-control flex-1">
                            <input type="number" name="adjustment" class="input input-bordered" placeholder="Enter adjustment (+/-)" required>
                        </div>
                        <div class="form-control flex-[2]">
                            <input type="text" name="notes" class="input input-bordered" placeholder="Reason for adjustment" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Apply
                        </button>
                    </form>
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        Recent Bookings
                    </h2>

                    @if($recentBookings->isEmpty())
                        <p class="text-base-content/60 text-center py-8">No bookings yet.</p>
                    @else
                        <div class="overflow-x-auto">
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
                                                <span class="badge {{ $booking->status_badge_class }} badge-sm">
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

            {{-- Inventory Log --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--history] size-5"></span>
                        Inventory History
                    </h2>

                    @if($inventoryLogs->isEmpty())
                        <p class="text-base-content/60 text-center py-8">No inventory changes logged.</p>
                    @else
                        <div class="overflow-x-auto">
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
                                                <span class="badge {{ $log->action_badge_class }} badge-sm">
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

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Stats --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Inventory Status</h2>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">Available</span>
                            <span class="text-2xl font-bold @if($rental->available_inventory <= 0) text-error @elseif($rental->isLowStock()) text-warning @else text-success @endif">
                                {{ $rental->available_inventory }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">Total</span>
                            <span class="text-lg font-medium">{{ $rental->total_inventory }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">Rented Out</span>
                            <span class="text-lg font-medium text-primary">{{ $rental->total_inventory - $rental->available_inventory }}</span>
                        </div>

                        <progress class="progress progress-primary w-full"
                                  value="{{ $rental->available_inventory }}"
                                  max="{{ $rental->total_inventory }}"></progress>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Pricing</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">Rental Price</span>
                            <span class="font-bold text-primary">{{ $rental->getFormattedPriceForCurrency() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-base-content/60">Security Deposit</span>
                            <span class="font-medium">{{ $rental->getFormattedDepositForCurrency() }}</span>
                        </div>
                        @if($rental->requires_return)
                            <div class="text-sm text-base-content/60 flex items-center gap-1">
                                <span class="icon-[tabler--refresh] size-4"></span>
                                Item must be returned
                            </div>
                        @endif
                        @if($rental->max_rental_days)
                            <div class="text-sm text-base-content/60 flex items-center gap-1">
                                <span class="icon-[tabler--clock] size-4"></span>
                                Max {{ $rental->max_rental_days }} days
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Associated Classes --}}
            @if($rental->classPlans->isNotEmpty())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Associated Classes</h2>

                    <div class="space-y-2">
                        @foreach($rental->classPlans as $classPlan)
                            <div class="flex items-center justify-between">
                                <span>{{ $classPlan->name }}</span>
                                @if($classPlan->pivot->is_required)
                                    <span class="badge badge-warning badge-xs">Required</span>
                                @else
                                    <span class="badge badge-ghost badge-xs">Suggested</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Eligibility --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Who Can Rent</h2>

                    @if($rental->isAvailableToAll())
                        <div class="flex items-center gap-2 text-success">
                            <span class="icon-[tabler--users] size-5"></span>
                            <span>Everyone</span>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($rental->eligibility as $eligibility)
                                <div class="flex items-center justify-between">
                                    @if($eligibility->eligible_type === 'membership')
                                        <span>{{ $eligibility->membershipPlan?->name }}</span>
                                    @elseif($eligibility->eligible_type === 'class_pack')
                                        <span>{{ $eligibility->classPack?->name }}</span>
                                    @endif
                                    @if($eligibility->is_free)
                                        <span class="badge badge-success badge-xs">Free</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
