@extends('layouts.dashboard')

@section('title', 'Membership Plans')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'memberships']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Membership Plans</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.index', ['tab' => 'memberships']) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Membership Plans</h1>
                <p class="text-base-content/60 mt-1">Manage recurring subscription plans for your members.</p>
            </div>
        </div>
        <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Membership
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('membership-plans.index') }}" class="tab {{ !$status ? 'tab-active' : '' }}">All</a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('membership-plans.index', ['status' => $key]) }}"
                   class="tab {{ $status === $key ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Content --}}
    @if($membershipPlans->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--id-badge-2] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Membership Plans Yet</h3>
                <p class="text-base-content/60 mb-4">Create membership plans to offer recurring access to your classes.</p>
                <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create First Membership
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
                                <th>Plan</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Interval</th>
                                <th>Eligibility</th>
                                <th>Status</th>
                                <th>Visibility</th>
                                <th class="w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($membershipPlans as $membershipPlan)
                            <tr class="{{ $membershipPlan->status !== 'active' ? 'opacity-60' : '' }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $membershipPlan->color }}20;">
                                            <span class="icon-[tabler--id-badge-2] size-5" style="color: {{ $membershipPlan->color }};"></span>
                                        </div>
                                        <div>
                                            <a href="{{ route('membership-plans.show', $membershipPlan) }}" class="font-medium hover:text-primary">
                                                {{ $membershipPlan->name }}
                                            </a>
                                            @if($membershipPlan->description)
                                                <p class="text-xs text-base-content/60 line-clamp-1">{{ $membershipPlan->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $membershipPlan->type_badge_class }} badge-soft badge-sm">
                                        {{ $membershipPlan->formatted_type }}
                                    </span>
                                    @if($membershipPlan->isCredits())
                                        <span class="text-xs text-base-content/60 ml-1">({{ $membershipPlan->credits_per_cycle }}/cycle)</span>
                                    @endif
                                </td>
                                <td class="font-medium">{{ $membershipPlan->formatted_price }}</td>
                                <td>{{ ucfirst($membershipPlan->interval) }}</td>
                                <td>
                                    @if($membershipPlan->coversAllClasses())
                                        <span class="text-success text-sm">All Classes</span>
                                    @else
                                        <span class="text-sm">{{ $membershipPlan->class_plans_count }} class plan(s)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft {{ $membershipPlan->status_badge_class }} badge-sm">
                                        {{ ucfirst($membershipPlan->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($membershipPlan->visibility_public)
                                        <span class="icon-[tabler--eye] size-4 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--eye-off] size-4 text-base-content/40"></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('membership-plans.show', $membershipPlan) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('membership-plans.edit', $membershipPlan) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form action="{{ route('membership-plans.destroy', $membershipPlan) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
