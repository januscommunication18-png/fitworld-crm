@php
    $user = auth()->user();
    $host = $user->currentHost() ?? $user->host;
    $selectedLang = session("studio_language_{$host->id}", $host->default_language_app ?? 'en');
    $t = \App\Services\TranslationService::make($host, $selectedLang);
    $trans = $t->all();
@endphp

@extends('layouts.dashboard')

@section('title', $trans['nav.catalog'] ?? 'Catalog')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> {{ $trans['nav.catalog'] ?? 'Catalog' }}</li>
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
            <a href="{{ route('catalog.index', ['tab' => 'memberships', 'view' => request('view', 'list')]) }}"
               class="tab {{ $tab === 'memberships' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--id-badge-2] size-4 mr-2"></span>
                {{ $trans['page.memberships'] ?? 'Memberships' }}
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
            @else
            <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                {{ $trans['btn.add'] ?? 'Add' }} {{ $trans['page.memberships'] ?? 'Membership' }}
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

                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} {{ $trans['common.max'] ?? 'max' }}</span>
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

                    <div class="flex items-center gap-6 text-sm mt-2">
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--currency-dollar] size-4 text-base-content/60"></span>
                            <span class="font-medium">{{ $classPlan->formatted_price }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                            <span>{{ $classPlan->default_capacity }} {{ $trans['common.max'] ?? 'max' }}</span>
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
                            <span>{{ $servicePlan->active_instructors_count }} {{ $trans['common.instructor'] ?? 'instructor' }}{{ $servicePlan->active_instructors_count !== 1 ? 's' : '' }}</span>
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
                        <a href="{{ route('service-plans.instructors', $servicePlan) }}" class="btn btn-sm btn-soft btn-info">
                            <span class="icon-[tabler--users] size-4"></span>
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
                            <span>{{ $servicePlan->active_instructors_count }} {{ $trans['common.instructor'] ?? 'instructor' }}{{ $servicePlan->active_instructors_count !== 1 ? 's' : '' }}</span>
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
                        <a href="{{ route('service-plans.instructors', $servicePlan) }}" class="btn btn-sm btn-soft btn-info">
                            <span class="icon-[tabler--users] size-4"></span>
                            {{ $trans['nav.instructors'] ?? 'Instructors' }}
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
    @else
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
