@extends('layouts.dashboard')

@section('title', 'Progress Templates')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('marketplace.index') }}"><span class="icon-[tabler--apps] me-1 size-4"></span> Marketplace</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--chart-line] me-1 size-4"></span> Progress Templates</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <span class="icon-[tabler--chart-line] size-7"></span>
                Progress Templates
            </h1>
            <p class="text-base-content/60 mt-1">Track client fitness progress with pre-built assessment templates.</p>
        </div>
    </div>

    {{-- Recommended Templates --}}
    @if($recommendedTemplates->count() > 0)
    <div class="space-y-4">
        <h2 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--star] size-5 text-warning"></span>
            Recommended for Your Studio
        </h2>
        <p class="text-sm text-base-content/60 -mt-2">These templates are tailored for your studio type.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($recommendedTemplates as $template)
            <a href="{{ route('progress-templates.show', $template) }}" class="card bg-base-100 hover:shadow-lg hover:border-primary/30 border-2 {{ $template->is_enabled ? 'border-success/30' : 'border-warning/20' }} transition-all duration-200 cursor-pointer group">
                <div class="card-body">
                    <div class="flex items-start gap-3">
                        <div class="p-3 rounded-lg {{ $template->is_enabled ? 'bg-success/10' : 'bg-warning/10' }} group-hover:bg-primary/10 transition-colors">
                            <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-6 {{ $template->is_enabled ? 'text-success' : 'text-warning' }} group-hover:text-primary transition-colors"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold group-hover:text-primary transition-colors">{{ $template->name }}</h3>
                                <span class="badge badge-warning badge-xs">
                                    <span class="icon-[tabler--star] size-3 mr-0.5"></span>
                                    Recommended
                                </span>
                            </div>
                            <p class="text-sm text-base-content/60 mt-1 line-clamp-2">{{ $template->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-3 text-xs text-base-content/50">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--layout-grid] size-3.5"></span>
                            {{ $template->sections_count ?? $template->sections->count() }} sections
                        </span>
                        <span class="text-base-content/20">|</span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--chart-dots] size-3.5"></span>
                            {{ $template->metrics_count }} metrics
                        </span>
                    </div>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-content/10">
                        <span class="badge badge-sm {{ $template->is_enabled ? 'badge-success' : 'badge-neutral' }}">
                            {{ $template->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                        <span class="text-sm text-base-content/50 flex items-center gap-1 group-hover:text-primary transition-colors">
                            View Details
                            <span class="icon-[tabler--chevron-right] size-4"></span>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- All Other Templates --}}
    @if($otherTemplates->count() > 0)
    <div class="space-y-4">
        <h2 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--list] size-5 text-primary"></span>
            All Templates
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($otherTemplates as $template)
            <a href="{{ route('progress-templates.show', $template) }}" class="card bg-base-100 hover:shadow-lg hover:border-primary/30 border border-transparent transition-all duration-200 cursor-pointer group">
                <div class="card-body">
                    <div class="flex items-start gap-3">
                        <div class="p-3 rounded-lg {{ $template->is_enabled ? 'bg-primary/10' : 'bg-base-content/5' }} group-hover:bg-primary/10 transition-colors">
                            <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-6 {{ $template->is_enabled ? 'text-primary' : 'text-base-content/40' }} group-hover:text-primary transition-colors"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold group-hover:text-primary transition-colors">{{ $template->name }}</h3>
                            <p class="text-sm text-base-content/60 mt-1 line-clamp-2">{{ $template->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-3 text-xs text-base-content/50">
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--layout-grid] size-3.5"></span>
                            {{ $template->sections_count ?? $template->sections->count() }} sections
                        </span>
                        <span class="text-base-content/20">|</span>
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--chart-dots] size-3.5"></span>
                            {{ $template->metrics_count }} metrics
                        </span>
                    </div>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-content/10">
                        <span class="badge badge-sm {{ $template->is_enabled ? 'badge-success' : 'badge-neutral' }}">
                            {{ $template->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                        <span class="text-sm text-base-content/50 flex items-center gap-1 group-hover:text-primary transition-colors">
                            View Details
                            <span class="icon-[tabler--chevron-right] size-4"></span>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($templates->count() === 0)
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--chart-line-off] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Templates Available</h3>
            <p class="text-base-content/60">There are no progress templates available yet.</p>
        </div>
    </div>
    @endif

    {{-- Help Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold flex items-center gap-2">
                <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                About Progress Templates
            </h3>
            <div class="mt-3 space-y-3 text-sm text-base-content/70">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--chart-line] size-5 text-primary shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Track Progress:</strong> Record client metrics over time to visualize their fitness journey with graphs and charts.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--star] size-5 text-warning shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Recommended Templates:</strong> Based on your studio type, we highlight templates that are most relevant for your clients.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--camera] size-5 text-success shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Progress Photos:</strong> Capture before/after photos alongside measurements for visual progress tracking.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--users] size-5 text-info shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Client Portal:</strong> Clients can view their progress in the member portal with interactive charts.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
