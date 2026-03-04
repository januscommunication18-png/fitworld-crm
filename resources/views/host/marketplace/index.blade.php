@extends('layouts.dashboard')

@section('title', 'Feature Marketplace')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--apps] me-1 size-4"></span> Feature Marketplace</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <span class="icon-[tabler--apps] size-7"></span>
                Feature Marketplace
            </h1>
            <p class="text-base-content/60 mt-1">Discover and enable features to enhance your studio management.</p>
        </div>
    </div>

    {{-- Features by Category --}}
    @foreach($categories as $categoryKey => $categoryLabel)
        @if(isset($featuresGrouped[$categoryKey]) && $featuresGrouped[$categoryKey]->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                @if($categoryKey === 'tools')
                    <span class="icon-[tabler--tool] size-5 text-primary"></span>
                @elseif($categoryKey === 'calendar')
                    <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                @elseif($categoryKey === 'payments')
                    <span class="icon-[tabler--credit-card] size-5 text-primary"></span>
                @elseif($categoryKey === 'integrations')
                    <span class="icon-[tabler--plug-connected] size-5 text-primary"></span>
                @else
                    <span class="icon-[tabler--category] size-5 text-primary"></span>
                @endif
                {{ $categoryLabel }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($featuresGrouped[$categoryKey] as $item)
                @php
                    $feature = $item->feature;
                    // Features with dedicated pages link directly when enabled
                    $featureUrl = match($feature->slug) {
                        'progress-templates' => route('progress-templates.index'),
                        default => route('marketplace.show', $feature->slug),
                    };
                @endphp
                <a href="{{ $featureUrl }}" class="card bg-base-100 hover:shadow-lg hover:border-primary/30 border border-transparent transition-all duration-200 cursor-pointer group">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="p-3 rounded-lg {{ $item->is_enabled ? 'bg-primary/10' : 'bg-base-content/5' }} group-hover:bg-primary/10 transition-colors">
                                <span class="icon-[tabler--{{ $feature->icon }}] size-6 {{ $item->is_enabled ? 'text-primary' : 'text-base-content/40' }} group-hover:text-primary transition-colors"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold group-hover:text-primary transition-colors">{{ $feature->name }}</h3>
                                    @if($feature->isPremium())
                                        <span class="badge badge-warning badge-xs">
                                            <span class="icon-[tabler--crown] size-3 mr-0.5"></span>
                                            Premium
                                        </span>
                                    @else
                                        <span class="badge badge-soft badge-success badge-xs">Free</span>
                                    @endif
                                </div>
                                <p class="text-sm text-base-content/60 mt-1 line-clamp-2">{{ $feature->description }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-content/10">
                            <span class="badge badge-sm {{ $item->is_enabled ? 'badge-success' : 'badge-neutral' }}">
                                {{ $item->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>

                            @if($item->requires_upgrade)
                                <span class="text-warning text-sm flex items-center gap-1">
                                    <span class="icon-[tabler--crown] size-4"></span>
                                    Upgrade Required
                                </span>
                            @else
                                <span class="text-sm text-base-content/50 flex items-center gap-1 group-hover:text-primary transition-colors">
                                    {{ $feature->slug === 'progress-templates' ? 'Open' : 'Configure' }}
                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach

    {{-- Empty State --}}
    @if($featuresGrouped->flatten()->count() === 0)
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--puzzle-off] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Features Available</h3>
            <p class="text-base-content/60">There are no features available in the marketplace yet.</p>
        </div>
    </div>
    @endif

    {{-- Help Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold flex items-center gap-2">
                <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                About the Feature Marketplace
            </h3>
            <div class="mt-3 space-y-3 text-sm text-base-content/70">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--sparkles] size-5 text-success shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Free Features:</strong> Available to all studios. Enable them anytime to enhance your workflow.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--crown] size-5 text-warning shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Premium Features:</strong> Require a compatible plan or special access granted by FitCRM support.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--mouse] size-5 text-primary shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Click to Configure:</strong> Click on any feature card to view details, enable/disable, and customize settings.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
