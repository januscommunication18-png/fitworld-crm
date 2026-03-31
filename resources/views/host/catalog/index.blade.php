@extends('layouts.dashboard')

@section('title', $trans['nav.classes_services'] ?? 'Classes & Services')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> {{ $trans['nav.classes_services'] ?? 'Classes & Services' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">{{ $trans['page.classes'] ?? 'Classes' }} & {{ $trans['page.services'] ?? 'Services' }}</h1>
        <p class="text-base-content/60 mt-1">{{ $trans['catalog.description'] ?? 'Manage your class templates and service offerings.' }}</p>
    </div>

    {{-- Tabs & Actions --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('catalog.index', ['tab' => 'classes', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'classes' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--users-group] size-4 mr-2"></span>
                {{ $trans['nav.catalog.class_plans'] ?? 'Class Plans' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'services', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'services' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--user] size-4 mr-2"></span>
                {{ $trans['nav.catalog.service_plans'] ?? 'Service Plans' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'class-passes', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'class-passes' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--ticket] size-4 mr-2"></span>
                {{ $trans['nav.catalog.class_passes'] ?? 'Class Passes' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'memberships', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'memberships' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--id-badge-2] size-4 mr-2"></span>
                {{ $trans['page.memberships'] ?? 'Memberships' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'rental-spaces', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'rental-spaces' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--building] size-4 mr-2"></span>
                {{ $trans['nav.rental_spaces'] ?? 'Rental Spaces' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'item-rentals', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'item-rentals' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--package] size-4 mr-2"></span>
                {{ $trans['nav.item_rentals'] ?? 'Item Rentals' }}
            </a>
            <a href="{{ route('catalog.index', ['tab' => 'events', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'events' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>
                {{ $trans['nav.events'] ?? 'Events' }}
            </a>
        </div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('catalog.index', ['tab' => $tab, 'view' => 'list']) }}"
                   class="btn btn-sm {{ request('view', 'list') === 'list' ? 'btn-active' : 'btn-ghost' }}" title="{{ $trans['common.list'] ?? 'List View' }}">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('catalog.index', ['tab' => $tab, 'view' => 'grid']) }}"
                   class="btn btn-sm {{ request('view') === 'grid' ? 'btn-active' : 'btn-ghost' }}" title="{{ $trans['common.grid'] ?? 'Grid View' }}">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
            </div>

            @if($tab === 'classes')
            <a href="{{ route('class-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.catalog.class_plans'] ?? 'Class Plan' }}
            </a>
            @elseif($tab === 'services')
            <a href="{{ route('service-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.catalog.service_plans'] ?? 'Service Plan' }}
            </a>
            @elseif($tab === 'class-passes')
            <a href="{{ route('class-passes.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.catalog.class_passes'] ?? 'Class Pass' }}
            </a>
            @elseif($tab === 'memberships')
            <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['page.memberships'] ?? 'Membership' }}
            </a>
            @elseif($tab === 'rental-spaces')
            <a href="{{ route('space-rentals.config.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.rental_spaces'] ?? 'Rental Space' }}
            </a>
            @elseif($tab === 'item-rentals')
            <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.item_rentals'] ?? 'Item' }}
            </a>
            @elseif($tab === 'events')
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['nav.events'] ?? 'Event' }}
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
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_class_plans'] ?? 'No Class Plans Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_class_plans_desc'] ?? 'Create your first class plan template to start scheduling classes.' }}</p>
                <a href="{{ route('class-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_class'] ?? 'Create First Class Plan' }}
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
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($classPlan->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $classPlan->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm flex-wrap">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} {{ $trans['common.max'] ?? 'max' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->active_staff_members_count }} staff</span>
                        </div>
                        <span class="badge {{ $classPlan->getDifficultyBadgeClass() }} badge-sm capitalize">{{ str_replace('_', ' ', $classPlan->difficulty_level) }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('class-plans.show', $classPlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('class-plans.edit', $classPlan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('class-plans.destroy', $classPlan) }}', '{{ $classPlan->name }}', '{{ $trans['catalog.class_plan'] ?? 'class plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
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
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                            <span class="badge {{ $classPlan->getDifficultyBadgeClass() }} badge-soft badge-sm capitalize">{{ str_replace('_', ' ', $classPlan->difficulty_level) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2 flex-wrap">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} {{ $trans['common.max'] ?? 'max' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->active_staff_members_count }} staff</span>
                        </div>
                        @if($classPlan->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $classPlan->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('class-plans.show', $classPlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('class-plans.edit', $classPlan) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('class-plans.destroy', $classPlan) }}', '{{ $classPlan->name }}', '{{ $trans['catalog.class_plan'] ?? 'class plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'services')
        @if($servicePlans->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--user] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_service_plans'] ?? 'No Service Plans Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_service_plans_desc'] ?? 'Create your first service plan for private sessions or consultations.' }}</p>
                <a href="{{ route('service-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_service'] ?? 'Create First Service Plan' }}
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
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
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
                            <span>{{ $servicePlan->active_staff_members_count }} {{ $trans['common.staff_member'] ?? 'staff' }}</span>
                        </div>
                        <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ str_replace('_', ' ', $servicePlan->location_type) }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('service-plans.show', $servicePlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('service-plans.destroy', $servicePlan) }}', '{{ $servicePlan->name }}', '{{ $trans['catalog.service_plan'] ?? 'service plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
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
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
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
                            <span>{{ $servicePlan->active_staff_members_count }} {{ $trans['common.staff_member'] ?? 'staff' }}</span>
                        </div>
                        @if($servicePlan->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $servicePlan->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('service-plans.show', $servicePlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('service-plans.edit', $servicePlan) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('service-plans.destroy', $servicePlan) }}', '{{ $servicePlan->name }}', '{{ $trans['catalog.service_plan'] ?? 'service plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'memberships')
        {{-- Memberships Tab --}}
        @if($membershipPlans->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--id-badge-2] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_membership_plans'] ?? 'No Membership Plans Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_membership_plans_desc'] ?? 'Create membership plans to offer recurring access to your classes.' }}</p>
                <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_membership'] ?? 'Create First Membership' }}
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($membershipPlans as $membershipPlan)
            <div class="card bg-base-100 {{ $membershipPlan->status !== 'active' ? 'opacity-60' : '' }}">
                <div class="h-32 flex items-center justify-center" style="background-color: {{ $membershipPlan->color }}20;">
                    <span class="icon-[tabler--id-badge-2] size-12" style="color: {{ $membershipPlan->color }};"></span>
                </div>
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $membershipPlan->name }}</h3>
                            <p class="text-sm text-base-content/60">{{ $membershipPlan->formatted_type }} &bull; {{ ucfirst($membershipPlan->interval) }}</p>
                        </div>
                        <span class="badge badge-soft {{ $membershipPlan->status_badge_class }} badge-sm">{{ ucfirst($membershipPlan->status) }}</span>
                    </div>

                    @if($membershipPlan->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $membershipPlan->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $membershipPlan->formatted_price_with_interval }}</span>
                        </div>
                        @if($membershipPlan->isCredits())
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--ticket] size-4 text-base-content/60"></span>
                            <span>{{ $membershipPlan->credits_per_cycle }} {{ $trans['common.credits'] ?? 'credits' }}</span>
                        </div>
                        @endif
                        <span class="badge {{ $membershipPlan->type_badge_class }} badge-soft badge-sm">{{ $membershipPlan->formatted_type }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('membership-plans.show', $membershipPlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('membership-plans.edit', $membershipPlan) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('membership-plans.destroy', $membershipPlan) }}', '{{ $membershipPlan->name }}', '{{ $trans['catalog.membership_plan'] ?? 'membership plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($membershipPlans as $membershipPlan)
            <div class="card bg-base-100 card-side {{ $membershipPlan->status !== 'active' ? 'opacity-60' : '' }}">
                <div class="w-32 shrink-0 flex items-center justify-center" style="background-color: {{ $membershipPlan->color }}20;">
                    <span class="icon-[tabler--id-badge-2] size-10" style="color: {{ $membershipPlan->color }};"></span>
                </div>
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $membershipPlan->name }}</h3>
                            <p class="text-sm text-base-content/60">{{ $membershipPlan->formatted_type }} &bull; {{ ucfirst($membershipPlan->interval) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-soft {{ $membershipPlan->status_badge_class }} badge-sm">{{ ucfirst($membershipPlan->status) }}</span>
                            <span class="badge {{ $membershipPlan->type_badge_class }} badge-soft badge-sm">{{ $membershipPlan->formatted_type }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $membershipPlan->formatted_price_with_interval }}</span>
                        </div>
                        @if($membershipPlan->isCredits())
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--ticket] size-4 text-base-content/60"></span>
                            <span>{{ $membershipPlan->credits_per_cycle }} {{ $trans['common.credits_per_cycle'] ?? 'credits/cycle' }}</span>
                        </div>
                        @endif
                        @if($membershipPlan->coversAllClasses())
                        <span class="text-base-content/60">{{ $trans['common.all_classes'] ?? 'All Classes' }}</span>
                        @else
                        <span class="text-base-content/60">{{ $membershipPlan->class_plans_count }} {{ $trans['common.class_plans'] ?? 'class plan(s)' }}</span>
                        @endif
                        @if($membershipPlan->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $membershipPlan->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('membership-plans.show', $membershipPlan) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('membership-plans.edit', $membershipPlan) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('membership-plans.destroy', $membershipPlan) }}', '{{ $membershipPlan->name }}', '{{ $trans['catalog.membership_plan'] ?? 'membership plan' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'class-passes')
        {{-- Class Passes Tab --}}
        @if($classPasses->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--ticket] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_class_passes'] ?? 'No Class Passes Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_class_passes_desc'] ?? 'Create class passes to offer credit bundles for attending classes.' }}</p>
                <a href="{{ route('class-passes.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_class_pass'] ?? 'Create First Class Pass' }}
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($classPasses as $classPass)
            <div class="card bg-base-100 {{ $classPass->status !== 'active' ? 'opacity-60' : '' }}">
                @if($classPass->image_path)
                <figure class="h-32">
                    <img src="{{ $classPass->image_url }}" alt="{{ $classPass->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="h-32 flex items-center justify-center" style="background-color: {{ $classPass->color ?? '#6366f1' }}20;">
                    <span class="icon-[tabler--ticket] size-12" style="color: {{ $classPass->color ?? '#6366f1' }};"></span>
                </div>
                @endif
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $classPass->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $classPass->class_count }} {{ $trans['common.credits'] ?? 'credits' }}
                                &bull; {{ $classPass->formatted_validity }}
                            </p>
                        </div>
                        <span class="badge badge-soft {{ $classPass->status === 'active' ? 'badge-success' : ($classPass->status === 'draft' ? 'badge-warning' : 'badge-neutral') }} badge-sm">
                            {{ ucfirst($classPass->status) }}
                        </span>
                    </div>

                    @if($classPass->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $classPass->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPass->getFormattedPriceForCurrency($defaultCurrency) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPass->purchases_count }} {{ $trans['common.sold'] ?? 'sold' }}</span>
                        </div>
                        @if($classPass->is_recurring)
                        <span class="badge badge-soft badge-info badge-sm">{{ $trans['common.recurring'] ?? 'Recurring' }}</span>
                        @endif
                    </div>

                    {{-- Eligibility Badge --}}
                    <div class="mt-2">
                        @if($classPass->eligibility_type === 'all')
                            <span class="badge badge-soft badge-success badge-sm">{{ $trans['class_passes.all_classes'] ?? 'All Classes' }}</span>
                        @elseif($classPass->eligibility_type === 'categories')
                            <span class="badge badge-soft badge-secondary badge-sm">{{ count($classPass->eligible_categories ?? []) }} {{ $trans['common.categories'] ?? 'categories' }}</span>
                        @elseif($classPass->eligibility_type === 'class_plans')
                            <span class="badge badge-soft badge-secondary badge-sm">{{ count($classPass->eligible_class_plan_ids ?? []) }} {{ $trans['common.classes'] ?? 'classes' }}</span>
                        @elseif($classPass->eligibility_type === 'instructors')
                            <span class="badge badge-soft badge-secondary badge-sm">{{ count($classPass->eligible_instructor_ids ?? []) }} {{ $trans['common.instructors'] ?? 'instructors' }}</span>
                        @elseif($classPass->eligibility_type === 'locations')
                            <span class="badge badge-soft badge-secondary badge-sm">{{ count($classPass->eligible_location_ids ?? []) }} {{ $trans['common.locations'] ?? 'locations' }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('class-passes.show', $classPass) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('class-passes.edit', $classPass) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('class-passes.destroy', $classPass) }}', '{{ $classPass->name }}', '{{ $trans['catalog.class_pass'] ?? 'class pass' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($classPasses as $classPass)
            <div class="card bg-base-100 card-side {{ $classPass->status !== 'active' ? 'opacity-60' : '' }}">
                @if($classPass->image_path)
                <figure class="w-32 shrink-0">
                    <img src="{{ $classPass->image_url }}" alt="{{ $classPass->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="w-32 shrink-0 flex items-center justify-center" style="background-color: {{ $classPass->color ?? '#6366f1' }}20;">
                    <span class="icon-[tabler--ticket] size-10" style="color: {{ $classPass->color ?? '#6366f1' }};"></span>
                </div>
                @endif
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $classPass->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $classPass->class_count }} {{ $trans['common.credits'] ?? 'credits' }}
                                &bull; {{ $classPass->formatted_validity }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-soft {{ $classPass->status === 'active' ? 'badge-success' : ($classPass->status === 'draft' ? 'badge-warning' : 'badge-neutral') }} badge-sm">
                                {{ ucfirst($classPass->status) }}
                            </span>
                            @if($classPass->eligibility_type === 'all')
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['class_passes.all_classes'] ?? 'All Classes' }}</span>
                            @else
                                <span class="badge badge-soft badge-secondary badge-sm capitalize">{{ str_replace('_', ' ', $classPass->eligibility_type) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPass->getFormattedPriceForCurrency($defaultCurrency) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPass->purchases_count }} {{ $trans['common.sold'] ?? 'sold' }}</span>
                        </div>
                        @if($classPass->is_recurring)
                        <span class="badge badge-soft badge-info badge-sm">{{ ucfirst($classPass->renewal_interval) }}</span>
                        @endif
                        @if($classPass->default_credits_per_class > 1)
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--ticket] size-4 text-base-content/60"></span>
                            <span>{{ $classPass->default_credits_per_class }} {{ $trans['class_passes.credits_per_class'] ?? 'credits/class' }}</span>
                        </div>
                        @endif
                        @if($classPass->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $classPass->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('class-passes.show', $classPass) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('class-passes.edit', $classPass) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <form action="{{ route('class-passes.duplicate', $classPass) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-soft btn-info" title="{{ $trans['btn.duplicate'] ?? 'Duplicate' }}">
                                <span class="icon-[tabler--copy] size-4"></span>
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('class-passes.destroy', $classPass) }}', '{{ $classPass->name }}', '{{ $trans['catalog.class_pass'] ?? 'class pass' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'rental-spaces')
        {{-- Rental Spaces Tab --}}
        @if($spaceRentalConfigs->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--building] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_rental_spaces'] ?? 'No Rental Spaces Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_rental_spaces_desc'] ?? 'Configure spaces that can be rented out for professional use or workshops.' }}</p>
                <a href="{{ route('space-rentals.config.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_rental_space'] ?? 'Create First Rental Space' }}
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($spaceRentalConfigs as $config)
            <div class="card bg-base-100 {{ !$config->is_active ? 'opacity-60' : '' }}">
                <div class="h-32 flex items-center justify-center bg-secondary/10">
                    <span class="icon-[tabler--{{ $config->type_icon }}] size-12 text-secondary"></span>
                </div>
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $config->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $config->location?->name ?? 'No location' }}
                                @if($config->room)
                                    &bull; {{ $config->room->name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($config->is_active)
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($config->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $config->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $config->getFormattedHourlyRateForCurrency() }}/hr</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                            <span>{{ $config->minimum_hours }}h {{ $trans['common.min'] ?? 'min' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar-event] size-4 text-base-content/60"></span>
                            <span>{{ $config->rentals_count }} {{ $trans['common.bookings'] ?? 'bookings' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('space-rentals.config.show', $config) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('space-rentals.config.edit', $config) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('space-rentals.config.destroy', $config) }}', '{{ $config->name }}', '{{ $trans['catalog.rental_space'] ?? 'rental space' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($spaceRentalConfigs as $config)
            <div class="card bg-base-100 card-side {{ !$config->is_active ? 'opacity-60' : '' }}">
                <div class="w-32 shrink-0 flex items-center justify-center bg-secondary/10">
                    <span class="icon-[tabler--{{ $config->type_icon }}] size-10 text-secondary"></span>
                </div>
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $config->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $config->location?->name ?? 'No location' }}
                                @if($config->room)
                                    &bull; {{ $config->room->name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($config->is_active)
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                            <span class="badge badge-soft badge-secondary badge-sm capitalize">{{ $config->rentable_type }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $config->getFormattedHourlyRateForCurrency() }}/hr</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                            <span>{{ $config->minimum_hours }}h {{ $trans['common.min'] ?? 'min' }}</span>
                        </div>
                        @if($config->getDepositForCurrency() > 0)
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--cash] size-4 text-base-content/60"></span>
                            <span>{{ $config->getFormattedDepositForCurrency() }} {{ $trans['space_rentals.deposit'] ?? 'deposit' }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar-event] size-4 text-base-content/60"></span>
                            <span>{{ $config->rentals_count }} {{ $trans['common.bookings'] ?? 'bookings' }}</span>
                        </div>
                        @if($config->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $config->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('space-rentals.config.show', $config) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('space-rentals.config.edit', $config) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <a href="{{ route('space-rentals.create', ['config_id' => $config->id]) }}" class="btn btn-sm btn-soft btn-info">
                            <span class="icon-[tabler--calendar-plus] size-4"></span>
                            {{ $trans['space_rentals.new_booking'] ?? 'New Booking' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('space-rentals.config.destroy', $config) }}', '{{ $config->name }}', '{{ $trans['catalog.rental_space'] ?? 'rental space' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'item-rentals')
        {{-- Item Rentals Tab --}}
        @if($rentalItems->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--package] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_rental_items'] ?? 'No Rental Items Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_rental_items_desc'] ?? 'Add equipment, mats, towels, and other items for members to rent.' }}</p>
                <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_rental_item'] ?? 'Create First Rental Item' }}
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($rentalItems as $item)
            <div class="card bg-base-100 {{ !$item->is_active ? 'opacity-60' : '' }}">
                @if($item->primary_image)
                <figure class="h-32">
                    <img src="{{ Storage::url($item->primary_image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="h-32 flex items-center justify-center bg-primary/10">
                    <span class="icon-[tabler--{{ $item->category_icon }}] size-12 text-primary"></span>
                </div>
                @endif
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $item->name }}</h3>
                            <p class="text-sm text-base-content/60">{{ $item->formatted_category }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($item->is_active)
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($item->description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $item->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $item->getFormattedPriceForCurrency() }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--box] size-4 text-base-content/60"></span>
                            <span class="{{ $item->available_inventory <= 0 ? 'text-error' : ($item->isLowStock() ? 'text-warning' : 'text-success') }}">
                                {{ $item->available_inventory }}/{{ $item->total_inventory }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar-event] size-4 text-base-content/60"></span>
                            <span>{{ $item->bookings_count }} {{ $trans['common.rentals'] ?? 'rentals' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('rentals.show', $item) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('rentals.edit', $item) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('rentals.destroy', $item) }}', '{{ $item->name }}', '{{ $trans['catalog.rental_item'] ?? 'rental item' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($rentalItems as $item)
            <div class="card bg-base-100 card-side {{ !$item->is_active ? 'opacity-60' : '' }}">
                @if($item->primary_image)
                <figure class="w-32 shrink-0">
                    <img src="{{ Storage::url($item->primary_image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="w-32 shrink-0 flex items-center justify-center bg-primary/10">
                    <span class="icon-[tabler--{{ $item->category_icon }}] size-10 text-primary"></span>
                </div>
                @endif
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $item->name }}</h3>
                            <p class="text-sm text-base-content/60">{{ $item->formatted_category }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($item->is_active)
                                <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                            @else
                                <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                            @endif
                            @if($item->available_inventory <= 0)
                                <span class="badge badge-soft badge-error badge-sm">{{ $trans['rentals.out_of_stock'] ?? 'Out of Stock' }}</span>
                            @elseif($item->isLowStock())
                                <span class="badge badge-soft badge-warning badge-sm">{{ $trans['rentals.low_stock'] ?? 'Low Stock' }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $item->getFormattedPriceForCurrency() }}</span>
                        </div>
                        @if($item->getDepositForCurrency() > 0)
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--cash] size-4 text-base-content/60"></span>
                            <span>{{ $item->getFormattedDepositForCurrency() }} {{ $trans['rentals.deposit'] ?? 'deposit' }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--box] size-4 text-base-content/60"></span>
                            <span>{{ $item->available_inventory }}/{{ $item->total_inventory }} {{ $trans['rentals.available'] ?? 'available' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--calendar-event] size-4 text-base-content/60"></span>
                            <span>{{ $item->bookings_count }} {{ $trans['common.rentals'] ?? 'rentals' }}</span>
                        </div>
                        @if($item->description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $item->description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('rentals.show', $item) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('rentals.edit', $item) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <a href="{{ route('rentals.invoice.create') }}" class="btn btn-sm btn-soft btn-info">
                            <span class="icon-[tabler--receipt] size-4"></span>
                            {{ $trans['rentals.new_rental'] ?? 'New Rental' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('rentals.destroy', $item) }}', '{{ $item->name }}', '{{ $trans['catalog.rental_item'] ?? 'rental item' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @elseif($tab === 'events')
        {{-- Events Tab --}}
        @if($events->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-event] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['catalog.no_events'] ?? 'No Events Yet' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['catalog.no_events_desc'] ?? 'Create events like workshops, seminars, or special classes for your clients.' }}</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['catalog.create_first_event'] ?? 'Create First Event' }}
                </a>
            </div>
        </div>
        @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($events as $event)
            <div class="card bg-base-100 {{ $event->status === 'cancelled' ? 'opacity-60' : '' }}">
                @if($event->cover_image)
                <figure class="h-32 relative">
                    <img src="{{ $event->cover_image }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    {{-- Date badge --}}
                    <div class="absolute top-3 left-3 bg-base-100/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-center shadow">
                        <div class="text-xs font-medium text-primary uppercase">{{ $event->start_datetime->format('M') }}</div>
                        <div class="text-lg font-bold leading-none">{{ $event->start_datetime->format('d') }}</div>
                    </div>
                </figure>
                @else
                <div class="h-32 flex items-center justify-center bg-primary/10 relative">
                    <span class="icon-[tabler--calendar-event] size-12 text-primary"></span>
                    {{-- Date badge --}}
                    <div class="absolute top-3 left-3 bg-base-100/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-center shadow">
                        <div class="text-xs font-medium text-primary uppercase">{{ $event->start_datetime->format('M') }}</div>
                        <div class="text-lg font-bold leading-none">{{ $event->start_datetime->format('d') }}</div>
                    </div>
                </div>
                @endif
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title">{{ $event->title }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $event->start_datetime->format('g:i A') }}
                                @if($event->venue_name)
                                    &bull; {{ $event->venue_name }}
                                @endif
                            </p>
                        </div>
                        <span class="badge badge-soft {{ $event->status === 'published' ? 'badge-success' : ($event->status === 'draft' ? 'badge-warning' : ($event->status === 'cancelled' ? 'badge-error' : 'badge-neutral')) }} badge-sm">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>

                    @if($event->short_description)
                    <p class="text-sm text-base-content/60 line-clamp-2 mt-2">{{ $event->short_description }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $event->registered_attendees_count }}@if($event->capacity)/{{ $event->capacity }}@endif</span>
                        </div>
                        <span class="badge badge-soft badge-sm capitalize">
                            @if($event->event_type === 'in_person')
                                <span class="icon-[tabler--map-pin] size-3 mr-1"></span> In-Person
                            @elseif($event->event_type === 'online')
                                <span class="icon-[tabler--device-laptop] size-3 mr-1"></span> Online
                            @else
                                <span class="icon-[tabler--arrows-exchange] size-3 mr-1"></span> Hybrid
                            @endif
                        </span>
                        <span class="badge badge-soft badge-sm capitalize">
                            @if($event->visibility === 'public')
                                <span class="icon-[tabler--world] size-3 mr-1"></span>
                            @elseif($event->visibility === 'private')
                                <span class="icon-[tabler--lock] size-3 mr-1"></span>
                            @else
                                <span class="icon-[tabler--link] size-3 mr-1"></span>
                            @endif
                            {{ ucfirst($event->visibility) }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions mt-4 pt-4 border-t border-base-content/10">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                        </a>
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('events.destroy', $event) }}', '{{ $event->title }}', '{{ $trans['catalog.event'] ?? 'event' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="space-y-4">
            @foreach($events as $event)
            <div class="card bg-base-100 card-side {{ $event->status === 'cancelled' ? 'opacity-60' : '' }}">
                @if($event->cover_image)
                <figure class="w-32 shrink-0 relative">
                    <img src="{{ $event->cover_image }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                </figure>
                @else
                <div class="w-32 shrink-0 flex items-center justify-center bg-primary/10">
                    <div class="text-center">
                        <div class="text-xs font-medium text-primary uppercase">{{ $event->start_datetime->format('M') }}</div>
                        <div class="text-2xl font-bold">{{ $event->start_datetime->format('d') }}</div>
                    </div>
                </div>
                @endif
                <div class="card-body py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-base">{{ $event->title }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $event->start_datetime->format('D, M j, Y \a\t g:i A') }}
                                @if($event->venue_name)
                                    &bull; {{ $event->venue_name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-soft {{ $event->status === 'published' ? 'badge-success' : ($event->status === 'draft' ? 'badge-warning' : ($event->status === 'cancelled' ? 'badge-error' : 'badge-neutral')) }} badge-sm">
                                {{ ucfirst($event->status) }}
                            </span>
                            <span class="badge badge-soft badge-sm capitalize">
                                {{ str_replace('_', '-', $event->event_type) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $event->registered_attendees_count }}@if($event->capacity)/{{ $event->capacity }}@endif {{ $trans['common.attendees'] ?? 'attendees' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                            <span>{{ $event->start_datetime->diffForHumans() }}</span>
                        </div>
                        @if($event->short_description)
                        <p class="text-base-content/60 line-clamp-1 flex-1">{{ $event->short_description }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-2">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-soft btn-secondary">
                            <span class="icon-[tabler--eye] size-4"></span>
                            {{ $trans['btn.view'] ?? 'View' }}
                        </a>
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-soft btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            {{ $trans['btn.edit'] ?? 'Edit' }}
                        </a>
                        @if($event->status === 'draft')
                        <form action="{{ route('events.publish', $event) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-soft btn-success">
                                <span class="icon-[tabler--send] size-4"></span>
                                {{ $trans['btn.publish'] ?? 'Publish' }}
                            </button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal('{{ route('events.destroy', $event) }}', '{{ $event->title }}', '{{ $trans['catalog.event'] ?? 'event' }}')">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-error/10 flex items-center justify-center">
                <span class="icon-[tabler--trash] size-6 text-error"></span>
            </div>
            <div>
                <h3 class="font-bold text-lg">{{ $trans['btn.delete'] ?? 'Delete' }} <span id="deleteItemType"></span></h3>
                <p class="text-base-content/60 text-sm">{{ $trans['common.action_cannot_undone'] ?? 'This action cannot be undone.' }}</p>
            </div>
        </div>
        <p class="py-2">{{ $trans['common.confirm_delete'] ?? 'Are you sure you want to delete' }} <strong id="deleteItemName"></strong>?</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">{{ $trans['btn.cancel'] ?? 'Cancel' }}</button>
            </form>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    {{ $trans['btn.delete'] ?? 'Delete' }}
                </button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
    function openDeleteModal(action, name, type) {
        document.getElementById('deleteForm').action = action;
        document.getElementById('deleteItemName').textContent = name;
        document.getElementById('deleteItemType').textContent = type;
        document.getElementById('deleteModal').showModal();
    }
</script>
@endpush
