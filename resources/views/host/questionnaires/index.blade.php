@extends('layouts.settings')

@section('title', 'Questionnaires — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Questionnaires</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border-0">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex w-14 h-14 rounded-2xl bg-primary/20 items-center justify-center flex-shrink-0">
                        <span class="icon-[tabler--forms] size-7 text-primary"></span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Questionnaires</h1>
                        <p class="text-base-content/60 mt-1">Build intake forms to collect client information before bookings.</p>
                    </div>
                </div>
                <a href="{{ route('questionnaires.create') }}" class="btn btn-primary shadow-lg shadow-primary/25">
                    <span class="icon-[tabler--plus] size-5"></span>
                    New Questionnaire
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('questionnaires.index') }}" class="card bg-base-100 hover:shadow-md transition-all {{ !$status ? 'ring-2 ring-primary ring-offset-2' : '' }}">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['all'] }}</p>
                        <p class="text-sm text-base-content/60">Total</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-base-200 flex items-center justify-center">
                        <span class="icon-[tabler--files] size-5 text-base-content/60"></span>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('questionnaires.index', ['status' => 'active']) }}" class="card bg-base-100 hover:shadow-md transition-all {{ $status === 'active' ? 'ring-2 ring-success ring-offset-2' : '' }}">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-success">{{ $counts['active'] }}</p>
                        <p class="text-sm text-base-content/60">Active</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('questionnaires.index', ['status' => 'draft']) }}" class="card bg-base-100 hover:shadow-md transition-all {{ $status === 'draft' ? 'ring-2 ring-warning ring-offset-2' : '' }}">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-warning">{{ $counts['draft'] }}</p>
                        <p class="text-sm text-base-content/60">Drafts</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--pencil] size-5 text-warning"></span>
                    </div>
                </div>
            </div>
        </a>
        <a href="{{ route('questionnaires.index', ['status' => 'archived']) }}" class="card bg-base-100 hover:shadow-md transition-all {{ $status === 'archived' ? 'ring-2 ring-neutral ring-offset-2' : '' }}">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-base-content/50">{{ $counts['archived'] }}</p>
                        <p class="text-sm text-base-content/60">Archived</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-base-200 flex items-center justify-center">
                        <span class="icon-[tabler--archive] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Filters Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            @if($status)
                <span class="badge badge-lg gap-2">
                    <span class="icon-[tabler--filter] size-4"></span>
                    {{ ucfirst($status) }}
                    <a href="{{ route('questionnaires.index') }}" class="hover:text-error">
                        <span class="icon-[tabler--x] size-4"></span>
                    </a>
                </span>
            @endif
            @if($type)
                <span class="badge badge-lg gap-2">
                    {{ $types[$type] ?? $type }}
                    <a href="{{ route('questionnaires.index', array_filter(['status' => $status])) }}" class="hover:text-error">
                        <span class="icon-[tabler--x] size-4"></span>
                    </a>
                </span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            {{-- Type Filter --}}
            <select class="select select-bordered select-sm" onchange="window.location.href=this.value">
                <option value="{{ route('questionnaires.index', array_filter(['status' => $status])) }}" {{ !$type ? 'selected' : '' }}>All Types</option>
                @foreach($types as $key => $label)
                    <option value="{{ route('questionnaires.index', array_filter(['status' => $status, 'type' => $key])) }}" {{ $type === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Content --}}
    @if($questionnaires->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-20">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 mx-auto mb-6">
                    <span class="icon-[tabler--clipboard-list] size-12 text-primary"></span>
                </div>
                <h3 class="text-xl font-bold mb-2">No Questionnaires Yet</h3>
                <p class="text-base-content/60 mb-8 max-w-md mx-auto">
                    Create intake forms to gather important information from clients before their first visit.
                    Learn about injuries, goals, and preferences.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('questionnaires.create') }}" class="btn btn-primary shadow-lg shadow-primary/25">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Create Your First Questionnaire
                    </a>
                </div>

                {{-- Features Preview --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-12 max-w-2xl mx-auto">
                    <div class="text-center p-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-3">
                            <span class="icon-[tabler--list-numbers] size-6 text-primary"></span>
                        </div>
                        <h4 class="font-semibold text-sm">Multi-Step Wizards</h4>
                        <p class="text-xs text-base-content/50 mt-1">Guide clients through step-by-step forms</p>
                    </div>
                    <div class="text-center p-4">
                        <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center mx-auto mb-3">
                            <span class="icon-[tabler--device-mobile] size-6 text-success"></span>
                        </div>
                        <h4 class="font-semibold text-sm">Mobile Friendly</h4>
                        <p class="text-xs text-base-content/50 mt-1">Works perfectly on any device</p>
                    </div>
                    <div class="text-center p-4">
                        <div class="w-12 h-12 rounded-xl bg-info/10 flex items-center justify-center mx-auto mb-3">
                            <span class="icon-[tabler--chart-dots] size-6 text-info"></span>
                        </div>
                        <h4 class="font-semibold text-sm">Track Responses</h4>
                        <p class="text-xs text-base-content/50 mt-1">View and manage all submissions</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($questionnaires as $questionnaire)
                @php
                    $version = $questionnaire->activeVersion ?? $questionnaire->latestVersion;
                    $questionCount = $version ? $version->getTotalQuestionCount() : 0;
                    $isWizard = $questionnaire->isWizard();
                    $stepCount = $isWizard && $version ? $version->steps->count() : 0;
                @endphp
                <div class="card bg-base-100 group hover:shadow-xl hover:shadow-base-content/5 transition-all duration-300 border border-base-200 flex flex-col h-full">
                    {{-- Status Indicator Bar --}}
                    <div class="h-1 rounded-t-2xl {{ $questionnaire->isActive() ? 'bg-success' : ($questionnaire->isDraft() ? 'bg-warning' : 'bg-base-300') }}"></div>

                    <div class="card-body p-5 flex-1">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isWizard ? 'bg-gradient-to-br from-violet-500/20 to-purple-500/10' : 'bg-gradient-to-br from-blue-500/20 to-cyan-500/10' }}">
                                    <span class="{{ $isWizard ? 'icon-[tabler--list-numbers] text-violet-600' : 'icon-[tabler--file-text] text-blue-600' }} size-6"></span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-base truncate">{{ $questionnaire->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="badge badge-sm {{ $isWizard ? 'badge-ghost' : 'badge-ghost' }}">
                                            {{ $isWizard ? 'Wizard' : 'Single Page' }}
                                        </span>
                                        <span class="badge badge-sm {{ \App\Models\Questionnaire::getStatusBadgeClass($questionnaire->status) }}">
                                            {{ ucfirst($questionnaire->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Dropdown Menu --}}
                            <div class="relative">
                                <details class="dropdown dropdown-bottom dropdown-end">
                                    <summary class="btn btn-ghost btn-sm btn-square list-none cursor-pointer">
                                        <span class="icon-[tabler--dots-vertical] size-5"></span>
                                    </summary>
                                    <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                        <li><a href="{{ route('questionnaires.builder', $questionnaire) }}">
                                            <span class="icon-[tabler--edit] size-4"></span> Edit Builder
                                        </a></li>
                                        <li><a href="{{ route('questionnaires.preview', $questionnaire) }}">
                                            <span class="icon-[tabler--eye] size-4"></span> Preview
                                        </a></li>
                                        <li><a href="{{ route('questionnaires.show', $questionnaire) }}">
                                            <span class="icon-[tabler--chart-bar] size-4"></span> View Responses
                                        </a></li>
                                        <li><a href="{{ route('questionnaires.edit', $questionnaire) }}">
                                            <span class="icon-[tabler--settings] size-4"></span> Settings
                                        </a></li>
                                        <li>
                                            <form action="{{ route('questionnaires.duplicate', $questionnaire) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="w-full text-left flex items-center gap-2">
                                                    <span class="icon-[tabler--copy] size-4"></span> Duplicate
                                                </button>
                                            </form>
                                        </li>
                                        @if($questionnaire->isDraft())
                                            <li>
                                                <form action="{{ route('questionnaires.publish', $questionnaire) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left flex items-center gap-2 text-success">
                                                        <span class="icon-[tabler--rocket] size-4"></span> Publish
                                                    </button>
                                                </form>
                                            </li>
                                        @elseif($questionnaire->isActive())
                                            <li>
                                                <form action="{{ route('questionnaires.unpublish', $questionnaire) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left flex items-center gap-2 text-warning">
                                                        <span class="icon-[tabler--archive] size-4"></span> Archive
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('questionnaires.destroy', $questionnaire) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this questionnaire?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </details>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($questionnaire->description)
                            <p class="text-sm text-base-content/60 line-clamp-2 mt-3">{{ $questionnaire->description }}</p>
                        @endif

                        {{-- Stats --}}
                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <div class="flex items-center gap-1.5 text-sm text-base-content/70 bg-base-200/50 px-2.5 py-1 rounded-lg">
                                <span class="icon-[tabler--help-circle] size-4 text-primary"></span>
                                <span class="font-medium">{{ $questionCount }}</span>
                                <span class="text-base-content/50">{{ Str::plural('question', $questionCount) }}</span>
                            </div>
                            @if($isWizard && $stepCount > 0)
                                <div class="flex items-center gap-1.5 text-sm text-base-content/70 bg-base-200/50 px-2.5 py-1 rounded-lg">
                                    <span class="icon-[tabler--list-numbers] size-4 text-violet-500"></span>
                                    <span class="font-medium">{{ $stepCount }}</span>
                                    <span class="text-base-content/50">{{ Str::plural('step', $stepCount) }}</span>
                                </div>
                            @endif
                            @if($questionnaire->estimated_minutes)
                                <div class="flex items-center gap-1.5 text-sm text-base-content/70 bg-base-200/50 px-2.5 py-1 rounded-lg">
                                    <span class="icon-[tabler--clock] size-4 text-amber-500"></span>
                                    <span>~{{ $questionnaire->estimated_minutes }}m</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Fixed Footer --}}
                    <div class="card-footer bg-base-200/30 px-5 py-3 border-t border-base-200 mt-auto">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-sm btn-primary flex-1 gap-2">
                                <span class="icon-[tabler--edit] size-4"></span>
                                Edit
                            </a>
                            <a href="{{ route('questionnaires.preview', $questionnaire) }}" class="btn btn-sm btn-ghost flex-1 gap-2">
                                <span class="icon-[tabler--eye] size-4"></span>
                                Preview
                            </a>
                            @if($questionnaire->isDraft())
                                <form action="{{ route('questionnaires.publish', $questionnaire) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success gap-2" title="Publish">
                                        <span class="icon-[tabler--rocket] size-4"></span>
                                        Publish
                                    </button>
                                </form>
                            @elseif($questionnaire->isActive())
                                <form action="{{ route('questionnaires.unpublish', $questionnaire) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning gap-2" title="Unpublish / Archive">
                                        <span class="icon-[tabler--archive] size-4"></span>
                                        Archive
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('questionnaires.show', $questionnaire) }}" class="btn btn-sm btn-ghost gap-2" title="View Responses">
                                    <span class="icon-[tabler--chart-bar] size-4"></span>
                                    Responses
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($questionnaires->hasPages())
            <div class="flex justify-center mt-8">
                {{ $questionnaires->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
