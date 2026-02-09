@extends('backoffice.layouts.app')

@section('title', 'Plans')
@section('page-title', 'Plans')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <p class="text-base-content/60">Manage subscription plans and their features.</p>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('backoffice.plans.index', ['tab' => 'default']) }}"
               class="tab {{ $tab === 'default' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--settings] size-4 mr-2"></span>
                Default Plans
            </a>
            <a href="{{ route('backoffice.plans.index', ['tab' => 'custom']) }}"
               class="tab {{ $tab === 'custom' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--license] size-4 mr-2"></span>
                Custom Plans
            </a>
        </div>

        <a href="{{ route('backoffice.plans.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add New Plan
        </a>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="card bg-base-100 {{ $plan->is_featured ? 'ring-2 ring-primary' : '' }}">
            @if($plan->is_featured)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="badge badge-primary">Featured</span>
            </div>
            @endif
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="card-title">{{ $plan->name }}</h3>
                        <p class="text-sm text-base-content/60">{{ $plan->slug }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($plan->is_active)
                            <span class="badge badge-soft badge-success badge-sm">Active</span>
                        @else
                            <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                        @endif
                    </div>
                </div>

                {{-- Price --}}
                <div class="mt-4">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold">${{ number_format($plan->price_monthly, 2) }}</span>
                        <span class="text-base-content/60">/month</span>
                    </div>
                    @if($plan->price_yearly)
                    <div class="text-sm text-base-content/60">
                        ${{ number_format($plan->price_yearly, 2) }}/year
                        @php
                            $monthlyCost = $plan->price_monthly * 12;
                            $savings = $monthlyCost - $plan->price_yearly;
                            $savingsPercent = $monthlyCost > 0 ? round(($savings / $monthlyCost) * 100) : 0;
                        @endphp
                        @if($savingsPercent > 0)
                            <span class="text-success">(Save {{ $savingsPercent }}%)</span>
                        @endif
                    </div>
                    @endif
                </div>

                @if($plan->description)
                <p class="text-sm text-base-content/60 mt-3">{{ $plan->description }}</p>
                @endif

                {{-- Features Summary --}}
                <div class="mt-4 space-y-2">
                    <p class="text-sm font-medium">Features:</p>
                    <ul class="text-sm space-y-1">
                        @php $features = $plan->features ?? []; @endphp
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            {{ ($features['locations'] ?? 1) == 0 ? 'Unlimited' : ($features['locations'] ?? 1) }} Location(s)
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            {{ ($features['rooms'] ?? 3) == 0 ? 'Unlimited' : ($features['rooms'] ?? 3) }} Room(s)
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            {{ ($features['classes'] ?? 10) == 0 ? 'Unlimited' : ($features['classes'] ?? 10) }} Classes
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            {{ ($features['students'] ?? 100) == 0 ? 'Unlimited' : ($features['students'] ?? 100) }} Students
                        </li>
                    </ul>
                </div>

                {{-- Clients using this plan --}}
                <div class="mt-4 pt-4 border-t border-base-content/10">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">Clients using this plan</span>
                        <span class="font-medium">{{ $plan->hosts()->count() }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card-actions mt-4">
                    <a href="{{ route('backoffice.plans.edit', $plan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                    <form action="{{ route('backoffice.plans.toggle-active', $plan) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-soft {{ $plan->is_active ? 'btn-warning' : 'btn-success' }}">
                            <span class="icon-[tabler--toggle-{{ $plan->is_active ? 'right' : 'left' }}] size-4"></span>
                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="card bg-base-100">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--license-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Plans Yet</h3>
                    <p class="text-base-content/60 mb-4">Create your first subscription plan to get started.</p>
                    <a href="{{ route('backoffice.plans.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Create First Plan
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
