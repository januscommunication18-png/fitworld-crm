@extends('backoffice.layouts.app')

@section('title', 'Features')
@section('page-title', 'Feature Marketplace')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <p class="text-base-content/60">Manage features available in the marketplace for hosts.</p>
    </div>

    {{-- Category Tabs --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('backoffice.features.index', ['category' => 'all']) }}"
               class="tab {{ $category === 'all' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--apps] size-4 mr-2"></span>
                All Features
            </a>
            @foreach($categories as $catKey => $catLabel)
            <a href="{{ route('backoffice.features.index', ['category' => $catKey]) }}"
               class="tab {{ $category === $catKey ? 'tab-active' : '' }}">
                {{ $catLabel }}
            </a>
            @endforeach
        </div>

        <a href="{{ route('backoffice.features.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Feature
        </a>
    </div>

    {{-- Features Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($features as $feature)
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-primary/10">
                            <span class="icon-[tabler--{{ $feature->icon }}] size-6 text-primary"></span>
                        </div>
                        <div>
                            <h3 class="card-title text-base">{{ $feature->name }}</h3>
                            <p class="text-xs text-base-content/50">{{ $feature->slug }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-sm {{ $feature->type === 'premium' ? 'badge-warning' : 'badge-soft badge-neutral' }}">
                            {{ ucfirst($feature->type) }}
                        </span>
                        <span class="badge badge-sm {{ $feature->is_active ? 'badge-soft badge-success' : 'badge-soft badge-error' }}">
                            {{ $feature->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                @if($feature->description)
                <p class="text-sm text-base-content/60 mt-3">{{ Str::limit($feature->description, 100) }}</p>
                @endif

                <div class="mt-3">
                    <span class="badge badge-soft badge-sm">{{ $categories[$feature->category] ?? $feature->category }}</span>
                </div>

                {{-- Usage Stats --}}
                <div class="mt-4 pt-4 border-t border-base-content/10">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">Hosts using this feature</span>
                        <span class="font-medium">{{ $feature->hosts()->wherePivot('is_enabled', true)->count() }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card-actions mt-4">
                    <a href="{{ route('backoffice.features.edit', $feature) }}" class="btn btn-sm btn-soft btn-primary flex-1">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                    <form action="{{ route('backoffice.features.toggle-active', $feature) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-soft {{ $feature->is_active ? 'btn-warning' : 'btn-success' }}">
                            <span class="icon-[tabler--toggle-{{ $feature->is_active ? 'right' : 'left' }}] size-4"></span>
                            {{ $feature->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="card bg-base-100">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--puzzle-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Features Yet</h3>
                    <p class="text-base-content/60 mb-4">Create your first marketplace feature to get started.</p>
                    <a href="{{ route('backoffice.features.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Create First Feature
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
