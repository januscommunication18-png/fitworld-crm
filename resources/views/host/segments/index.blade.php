@extends('layouts.dashboard')

@section('title', 'Segments')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><span class="icon-[tabler--speakerphone] me-1 size-4"></span> Marketing</li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Segments</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Client Segments</h1>
            <p class="text-base-content/60 mt-1">Create targeted groups of clients for personalized offers and campaigns.</p>
        </div>
        <a href="{{ route('segments.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Create Segment
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--users-group] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($totalClients) }}</p>
                        <p class="text-sm text-base-content/60">Total Clients</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--filter] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $segments->where('type', 'dynamic')->count() }}</p>
                        <p class="text-sm text-base-content/60">Dynamic Segments</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--crown] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $segments->where('type', 'smart')->count() }}</p>
                        <p class="text-sm text-base-content/60">Smart Segments</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-4">
        <div class="tabs tabs-boxed">
            <a href="{{ route('segments.index') }}" class="tab {{ !$type ? 'tab-active' : '' }}">All</a>
            @foreach($types as $key => $label)
                <a href="{{ route('segments.index', ['type' => $key]) }}"
                   class="tab {{ $type === $key ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Content --}}
    @if($segments->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--users-group] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Segments Yet</h3>
                <p class="text-base-content/60 mb-4">Create segments to target specific groups of clients with personalized offers.</p>
                <a href="{{ route('segments.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create First Segment
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($segments as $segment)
                <div class="card bg-base-100 hover:shadow-md transition-shadow">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $segment->color }}20;">
                                    @if($segment->type === 'static')
                                        <span class="icon-[tabler--users] size-5" style="color: {{ $segment->color }};"></span>
                                    @elseif($segment->type === 'dynamic')
                                        <span class="icon-[tabler--filter] size-5" style="color: {{ $segment->color }};"></span>
                                    @else
                                        <span class="icon-[tabler--crown] size-5" style="color: {{ $segment->color }};"></span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('segments.show', $segment) }}" class="font-semibold hover:text-primary">
                                        {{ $segment->name }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="badge badge-soft badge-sm {{ $segment->type === 'dynamic' ? 'badge-primary' : ($segment->type === 'smart' ? 'badge-warning' : 'badge-neutral') }}">
                                            {{ ucfirst($segment->type) }}
                                        </span>
                                        @if(!$segment->is_active)
                                            <span class="badge badge-soft badge-error badge-sm">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <details class="dropdown dropdown-end">
                                <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999;">
                                    <li><a href="{{ route('segments.show', $segment) }}"><span class="icon-[tabler--eye] size-4"></span> View</a></li>
                                    @if(!$segment->is_system)
                                        <li><a href="{{ route('segments.edit', $segment) }}"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                        @if($segment->type === 'dynamic')
                                            <li>
                                                <form action="{{ route('segments.refresh', $segment) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left flex items-center gap-2">
                                                        <span class="icon-[tabler--refresh] size-4"></span> Refresh
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('segments.destroy', $segment) }}" method="POST" onsubmit="return confirm('Delete this segment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        </div>

                        @if($segment->description)
                            <p class="text-sm text-base-content/60 mt-3 line-clamp-2">{{ $segment->description }}</p>
                        @endif

                        <div class="mt-4 pt-4 border-t border-base-300">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                    <span class="font-medium">{{ number_format($segment->member_count) }}</span>
                                    <span class="text-sm text-base-content/60">members</span>
                                </div>
                                @if($segment->total_revenue > 0)
                                    <span class="text-sm text-success font-medium">${{ number_format($segment->total_revenue, 0) }} revenue</span>
                                @endif
                            </div>
                            @if($segment->tier)
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="icon-[tabler--diamond] size-4 text-warning"></span>
                                    <span class="text-sm text-base-content/60">{{ ucfirst($segment->tier) }} tier ({{ $segment->min_score }}-{{ $segment->max_score ?? '1000' }} pts)</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
