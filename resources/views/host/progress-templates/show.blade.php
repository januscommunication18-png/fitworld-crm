@extends('layouts.dashboard')

@section('title', $progressTemplate->name . ' - Progress Templates')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('marketplace.index') }}"><span class="icon-[tabler--apps] me-1 size-4"></span> Marketplace</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('progress-templates.index') }}"><span class="icon-[tabler--chart-line] me-1 size-4"></span> Progress Templates</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $progressTemplate->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Alert Container --}}
    <div id="template-alert" class="alert hidden">
        <span id="alert-icon" class="size-5"></span>
        <span id="alert-message"></span>
    </div>

    {{-- Hero Header --}}
    <div class="card bg-gradient-to-br from-primary/10 via-primary/5 to-transparent border-0 overflow-hidden">
        <div class="card-body p-8">
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                {{-- Icon & Info --}}
                <div class="flex items-start gap-5 flex-1">
                    <div class="p-5 rounded-2xl bg-primary/20 shadow-lg shadow-primary/10" id="icon-container">
                        <span class="icon-[tabler--{{ $progressTemplate->icon ?? 'chart-line' }}] size-14 text-primary" id="template-icon"></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 flex-wrap mb-2">
                            @if($isRecommended)
                                <span class="badge badge-warning gap-1">
                                    <span class="icon-[tabler--star-filled] size-3"></span>
                                    Recommended for You
                                </span>
                            @endif
                            <span class="badge {{ $isEnabled ? 'badge-success' : 'badge-neutral' }}" id="status-badge">
                                <span class="icon-[tabler--{{ $isEnabled ? 'check' : 'x' }}] size-3 mr-1"></span>
                                {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-bold text-base-content">{{ $progressTemplate->name }}</h1>
                        <p class="text-base-content/60 mt-2 text-lg">{{ $progressTemplate->description }}</p>
                    </div>
                </div>

                {{-- Toggle Card --}}
                <div class="lg:ml-auto">
                    <div class="bg-base-100 rounded-2xl p-6 shadow-lg min-w-[220px]">
                        <div class="flex flex-col items-center gap-3">
                            <span class="text-sm font-medium text-base-content/70" id="status-text">
                                {{ $isEnabled ? 'Template Active' : 'Template Disabled' }}
                            </span>
                            <label class="switch switch-success switch-lg">
                                <input type="checkbox"
                                    id="template-toggle"
                                    data-template-id="{{ $progressTemplate->id }}"
                                    {{ $isEnabled ? 'checked' : '' }}
                                >
                                <span class="switch-indicator"></span>
                            </label>
                            <span class="text-xs text-base-content/40">
                                Toggle to {{ $isEnabled ? 'disable' : 'enable' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4 flex flex-row items-center gap-3">
                <div class="p-3 rounded-xl bg-info/10">
                    <span class="icon-[tabler--layout-grid] size-6 text-info"></span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ $progressTemplate->sections->count() }}</div>
                    <div class="text-xs text-base-content/50">Sections</div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 flex flex-row items-center gap-3">
                <div class="p-3 rounded-xl bg-success/10">
                    <span class="icon-[tabler--chart-dots] size-6 text-success"></span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ $progressTemplate->sections->sum(fn($s) => $s->metrics->count()) }}</div>
                    <div class="text-xs text-base-content/50">Metrics</div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 flex flex-row items-center gap-3">
                <div class="p-3 rounded-xl bg-warning/10">
                    <span class="icon-[tabler--star] size-6 text-warning"></span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ $progressTemplate->sections->sum(fn($s) => $s->metrics->where('is_required', true)->count()) }}</div>
                    <div class="text-xs text-base-content/50">Required</div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 flex flex-row items-center gap-3">
                <div class="p-3 rounded-xl bg-primary/10">
                    <span class="icon-[tabler--building] size-6 text-primary"></span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ count($progressTemplate->studio_types ?? []) }}</div>
                    <div class="text-xs text-base-content/50">Studio Types</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sections Grid --}}
    <div class="space-y-4">
        <h2 class="text-xl font-bold flex items-center gap-2">
            <span class="icon-[tabler--list-details] size-6 text-primary"></span>
            Template Sections
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($progressTemplate->sections as $section)
            <div class="card bg-base-100 hover:shadow-lg transition-shadow">
                <div class="card-body">
                    {{-- Section Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5">
                            <span class="icon-[tabler--{{ $section->icon ?? 'folder' }}] size-6 text-primary"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg">{{ $section->name }}</h3>
                            @if($section->description)
                                <p class="text-sm text-base-content/50">{{ $section->description }}</p>
                            @endif
                        </div>
                        <div class="badge badge-primary badge-outline">{{ $section->metrics->count() }} metrics</div>
                    </div>

                    {{-- Metrics List --}}
                    <div class="space-y-2">
                        @foreach($section->metrics as $metric)
                        @php
                            $typeConfig = [
                                'slider' => ['icon' => 'adjustments-horizontal', 'color' => 'info', 'bg' => 'bg-info/10'],
                                'number' => ['icon' => 'numbers', 'color' => 'success', 'bg' => 'bg-success/10'],
                                'select' => ['icon' => 'list-check', 'color' => 'warning', 'bg' => 'bg-warning/10'],
                                'checkbox_list' => ['icon' => 'checkbox', 'color' => 'primary', 'bg' => 'bg-primary/10'],
                                'rating' => ['icon' => 'star', 'color' => 'warning', 'bg' => 'bg-warning/10'],
                                'text' => ['icon' => 'text-caption', 'color' => 'neutral', 'bg' => 'bg-base-content/5'],
                            ];
                            $config = $typeConfig[$metric->metric_type] ?? ['icon' => 'question-mark', 'color' => 'neutral', 'bg' => 'bg-base-content/5'];
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $config['bg'] }} hover:scale-[1.01] transition-transform">
                            <span class="icon-[tabler--{{ $config['icon'] }}] size-5 text-{{ $config['color'] }}"></span>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $metric->name }}</div>
                                @if($metric->unit)
                                    <div class="text-xs text-base-content/50">Unit: {{ $metric->unit }}</div>
                                @elseif($metric->metric_type === 'slider' || $metric->metric_type === 'rating')
                                    <div class="text-xs text-base-content/50">Range: {{ $metric->min_value ?? 1 }} - {{ $metric->max_value ?? 10 }}</div>
                                @endif
                            </div>
                            @if($metric->is_required)
                                <span class="badge badge-error badge-xs">Required</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Studio Types & Features --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Studio Types --}}
        @if($progressTemplate->studio_types && count($progressTemplate->studio_types) > 0)
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="font-bold flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--building] size-5 text-primary"></span>
                    Designed For
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($progressTemplate->studio_types as $studioType)
                        <span class="badge badge-lg badge-soft badge-primary gap-1">
                            <span class="icon-[tabler--check] size-4"></span>
                            {{ ucfirst(str_replace('_', ' ', $studioType)) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Features --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="font-bold flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--sparkles] size-5 text-warning"></span>
                    Features Included
                </h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                        <span>Progress tracking over time</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                        <span>Visual charts & graphs</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                        <span>Before/after photo comparison</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                        <span>PDF report export</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                        <span>Client portal access</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('progress-templates.index') }}" class="btn btn-ghost gap-2">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            Back to Templates
        </a>
        @if($isEnabled)
        <div class="flex gap-2">
            <button class="btn btn-outline btn-primary gap-2" disabled>
                <span class="icon-[tabler--file-plus] size-5"></span>
                Create Report
                <span class="badge badge-xs">Coming Soon</span>
            </button>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateToggle = document.getElementById('template-toggle');

    if (templateToggle) {
        templateToggle.addEventListener('change', function() {
            const enable = this.checked;

            templateToggle.disabled = true;

            fetch(`{{ route('progress-templates.toggle', $progressTemplate) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ enable: enable })
            })
            .then(response => response.json())
            .then(data => {
                templateToggle.disabled = false;

                if (data.success) {
                    const statusBadge = document.getElementById('status-badge');
                    const statusText = document.getElementById('status-text');
                    const iconContainer = document.getElementById('icon-container');

                    statusBadge.innerHTML = '<span class="icon-[tabler--' + (enable ? 'check' : 'x') + '] size-3 mr-1"></span>' + (enable ? 'Enabled' : 'Disabled');
                    statusBadge.className = 'badge ' + (enable ? 'badge-success' : 'badge-neutral');

                    statusText.textContent = enable ? 'Template Active' : 'Enable Template';

                    showAlert('success', data.message);

                    // Reload to show/hide action buttons
                    setTimeout(() => location.reload(), 1000);
                } else {
                    templateToggle.checked = !enable;
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                templateToggle.disabled = false;
                templateToggle.checked = !enable;
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    }

    function showAlert(type, message) {
        const alert = document.getElementById('template-alert');
        const icon = document.getElementById('alert-icon');
        const msg = document.getElementById('alert-message');

        alert.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
        icon.className = 'size-5 icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + ']';
        msg.textContent = message;

        alert.classList.remove('hidden');

        setTimeout(function() {
            alert.classList.add('hidden');
        }, 5000);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>
@endpush
@endsection
