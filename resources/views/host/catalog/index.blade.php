@extends('layouts.dashboard')

@section('title', 'Catalog')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Classes & Services</h1>
        <p class="text-base-content/60 mt-1">Manage your class templates and service offerings.</p>
    </div>

    {{-- Tabs & Actions --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('catalog.index', ['tab' => 'classes', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'classes' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--users-group] size-4 mr-2"></span>
                Class Plans
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'services', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'services' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--user] size-4 mr-2"></span>
                Service Plans
            </a>
        </div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('catalog.index', ['tab' => $tab, 'view' => 'list']) }}"
                   class="btn btn-sm {{ request('view', 'list') === 'list' ? 'btn-active' : 'btn-ghost' }}" title="List View">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('catalog.index', ['tab' => $tab, 'view' => 'grid']) }}"
                   class="btn btn-sm {{ request('view') === 'grid' ? 'btn-active' : 'btn-ghost' }}" title="Grid View">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
            </div>

            @if($tab === 'classes')
            <a href="{{ route('class-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Class Plan
            </a>
            @else
            <a href="{{ route('service-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Service Plan
            </a>
            @endif
        </div>
    </div>

    {{-- Content --}}
    @if($tab === 'classes')
        @if($classPlans->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--users-group] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Class Plans Yet</h3>
                <p class="text-base-content/60 mb-4">Create your first class plan template to start scheduling classes.</p>
                <a href="{{ route('class-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create First Class Plan
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($classPlans as $classPlan)
            <div class="card bg-base-100 {{ !$classPlan->is_active ? 'opacity-60' : '' }}">
                @if($classPlan->image_path)
                <figure class="h-32">
                    <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="h-32 flex items-center justify-center" style="background-color: {{ $classPlan->color }}20;">
                    <span class="icon-[tabler--yoga] size-12" style="color: {{ $classPlan->color }};"></span>
                </div>
                @endif
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $classPlan->name }}</h3>
                            <p class="text-sm text-base-content/60 capitalize">{{ $classPlan->category }} &bull; {{ $classPlan->formatted_duration }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($classPlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if($classPlan->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $classPlan->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} max</span>
                        </div>
                        <span class="badge {{ $classPlan->getDifficultyBadgeClass() }} badge-sm capitalize">{{ str_replace('_', ' ', $classPlan->difficulty_level) }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('class-plans.edit', $classPlan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <form action="{{ route('class-plans.destroy', $classPlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this class plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft btn-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($classPlans as $classPlan)
            <div class="card bg-base-100 card-side {{ !$classPlan->is_active ? 'opacity-60' : '' }}">
                @if($classPlan->image_path)
                <figure class="w-32 shrink-0">
                    <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="w-32 shrink-0 flex items-center justify-center" style="background-color: {{ $classPlan->color }}20;">
                    <span class="icon-[tabler--yoga] size-10" style="color: {{ $classPlan->color }};"></span>
                </div>
                @endif
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $classPlan->name }}</h3>
                            <p class="text-sm text-base-content/60 capitalize">{{ $classPlan->category }} &bull; {{ $classPlan->formatted_duration }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($classPlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                            <span class="badge {{ $classPlan->getDifficultyBadgeClass() }} badge-soft badge-sm capitalize">{{ str_replace('_', ' ', $classPlan->difficulty_level) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} max</span>
                        </div>
                        @if($classPlan->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $classPlan->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('class-plans.edit', $classPlan) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <form action="{{ route('class-plans.destroy', $classPlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this class plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft btn-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @else
        @if($servicePlans->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--user] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Service Plans Yet</h3>
                <p class="text-base-content/60 mb-4">Create your first service plan for private sessions or consultations.</p>
                <a href="{{ route('service-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create First Service Plan
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($servicePlans as $servicePlan)
            <div class="card bg-base-100 {{ !$servicePlan->is_active ? 'opacity-60' : '' }}">
                @if($servicePlan->image_path)
                <figure class="h-32">
                    <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="h-32 flex items-center justify-center" style="background-color: {{ $servicePlan->color }}20;">
                    <span class="icon-[tabler--user-check] size-12" style="color: {{ $servicePlan->color }};"></span>
                </div>
                @endif
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $servicePlan->name }}</h3>
                            <p class="text-sm text-base-content/60 capitalize">{{ str_replace('_', ' ', $servicePlan->category) }} &bull; {{ $servicePlan->formatted_duration }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($servicePlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if($servicePlan->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $servicePlan->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $servicePlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $servicePlan->active_instructors_count }} instructor{{ $servicePlan->active_instructors_count !== 1 ? 's' : '' }}</span>
                        </div>
                        <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ str_replace('_', ' ', $servicePlan->location_type) }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <a href="{{ route('service-plans.instructors', $servicePlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--users] size-4"></span>
                        </a>
                        <form action="{{ route('service-plans.destroy', $servicePlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft btn-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($servicePlans as $servicePlan)
            <div class="card bg-base-100 card-side {{ !$servicePlan->is_active ? 'opacity-60' : '' }}">
                @if($servicePlan->image_path)
                <figure class="w-32 shrink-0">
                    <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="w-32 shrink-0 flex items-center justify-center" style="background-color: {{ $servicePlan->color }}20;">
                    <span class="icon-[tabler--user-check] size-10" style="color: {{ $servicePlan->color }};"></span>
                </div>
                @endif
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $servicePlan->name }}</h3>
                            <p class="text-sm text-base-content/60 capitalize">{{ str_replace('_', ' ', $servicePlan->category) }} &bull; {{ $servicePlan->formatted_duration }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($servicePlan->is_active)
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                            @endif
                            <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ str_replace('_', ' ', $servicePlan->location_type) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $servicePlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $servicePlan->active_instructors_count }} instructor{{ $servicePlan->active_instructors_count !== 1 ? 's' : '' }}</span>
                        </div>
                        @if($servicePlan->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $servicePlan->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <a href="{{ route('service-plans.instructors', $servicePlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--users] size-4"></span>
                            Instructors
                        </a>
                        <form action="{{ route('service-plans.destroy', $servicePlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft btn-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @endif
</div>
@endsection
