@extends('layouts.dashboard')

@section('title', 'Progress Report - ' . $client->full_name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}"><span class="icon-[tabler--users] me-1 size-4"></span> Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.show', $client) }}">{{ $client->full_name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.progress.index', $client) }}">Progress</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $report->report_date->format('M d, Y') }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.progress.index', $client) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--{{ $report->template->icon ?? 'chart-line' }}] size-6 text-primary"></span>
                    <h1 class="text-2xl font-bold">{{ $report->template->name }}</h1>
                </div>
                <p class="text-base-content/60 mt-1">
                    {{ $report->report_date->format('F d, Y') }}
                    @if($report->classSession)
                        &bull; {{ $report->classSession->classPlan?->name ?? 'Class Session' }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
            @if($previousReport)
                <a href="{{ route('clients.progress.show', [$client, $previousReport]) }}"
                   class="btn btn-ghost btn-sm gap-1"
                   title="Previous: {{ $previousReport->report_date->format('M d, Y') }}">
                    <span class="icon-[tabler--chevron-left] size-4"></span>
                    Previous
                </a>
            @endif
            @if($nextReport)
                <a href="{{ route('clients.progress.show', [$client, $nextReport]) }}"
                   class="btn btn-ghost btn-sm gap-1"
                   title="Next: {{ $nextReport->report_date->format('M d, Y') }}">
                    Next
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Overall Score Card --}}
            @if($report->overall_score)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm text-base-content/60 uppercase tracking-wide">Overall Score</h3>
                                <div class="text-4xl font-bold mt-1 {{ $report->overall_score >= 70 ? 'text-success' : ($report->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                                    {{ number_format($report->overall_score, 1) }}%
                                </div>
                            </div>
                            <div class="radial-progress text-primary" style="--value:{{ min(100, $report->overall_score) }}; --size:5rem;" role="progressbar">
                                <span class="text-sm font-semibold">{{ round($report->overall_score) }}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-3 mt-4">
                            <div class="h-3 rounded-full {{ $report->overall_score >= 70 ? 'bg-success' : ($report->overall_score >= 40 ? 'bg-warning' : 'bg-error') }}"
                                 style="width: {{ min(100, $report->overall_score) }}%"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Metrics by Section --}}
            @foreach($valuesBySection as $sectionData)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--{{ $sectionData['section']->icon ?? 'folder' }}] size-5 me-2 text-primary"></span>
                            {{ $sectionData['section']->name }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($sectionData['metrics'] as $metricData)
                                @php
                                    $metric = $metricData['metric'];
                                    $value = $metricData['value'];
                                @endphp
                                <div class="p-4 bg-base-200/30 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-base-content/70">{{ $metric->name }}</span>
                                        @if($metric->unit)
                                            <span class="text-xs text-base-content/50">{{ $metric->unit }}</span>
                                        @endif
                                    </div>

                                    @if($value)
                                        @switch($metric->metric_type)
                                            @case('slider')
                                            @case('number')
                                                <div class="text-2xl font-bold text-primary">
                                                    {{ number_format($value->value_numeric, $metric->step < 1 ? 1 : 0) }}
                                                    @if($metric->unit)
                                                        <span class="text-sm font-normal text-base-content/60">{{ $metric->unit }}</span>
                                                    @endif
                                                </div>
                                                @if($metric->metric_type === 'slider' && $metric->min_value !== null && $metric->max_value !== null)
                                                    @php
                                                        $percentage = (($value->value_numeric - $metric->min_value) / ($metric->max_value - $metric->min_value)) * 100;
                                                    @endphp
                                                    <div class="w-full bg-base-300 rounded-full h-2 mt-2">
                                                        <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <div class="flex justify-between text-xs text-base-content/50 mt-1">
                                                        <span>{{ $metric->min_value }}</span>
                                                        <span>{{ $metric->max_value }}</span>
                                                    </div>
                                                @endif
                                                @break

                                            @case('rating')
                                                <div class="rating rating-lg rating-half">
                                                    @for($i = 1; $i <= ($metric->max_value ?? 5); $i++)
                                                        <span class="mask mask-star-2 {{ $i <= $value->value_numeric ? 'bg-warning' : 'bg-base-300' }}"></span>
                                                    @endfor
                                                </div>
                                                <div class="text-sm text-base-content/60 mt-1">
                                                    {{ $value->value_numeric }} / {{ $metric->max_value ?? 5 }}
                                                </div>
                                                @break

                                            @case('select')
                                                <div class="text-lg font-semibold">{{ $value->value_text }}</div>
                                                @break

                                            @case('checkbox_list')
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($value->value_json ?? [] as $item)
                                                        <span class="badge badge-primary badge-soft">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('text')
                                            @default
                                                <div class="text-base-content">{{ $value->value_text ?: '-' }}</div>
                                                @break
                                        @endswitch
                                    @else
                                        <div class="text-base-content/40 italic">Not recorded</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Trainer Notes --}}
            @if($report->trainer_notes)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--notes] size-5 me-2 text-primary"></span>
                            Trainer Notes
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="whitespace-pre-wrap">{{ $report->trainer_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Report Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon-[tabler--info-circle] size-5 me-2"></span>
                        Report Details
                    </h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <span class="text-sm text-base-content/60">Client</span>
                        <div class="flex items-center gap-2 mt-1">
                            @if($client->avatar_url)
                                <div class="avatar">
                                    <div class="w-8 rounded-full">
                                        <img src="{{ $client->avatar_url }}" alt="{{ $client->full_name }}">
                                    </div>
                                </div>
                            @else
                                <div class="avatar avatar-ring avatar-sm">
                                    <div class="bg-primary text-primary-content rounded-full">
                                        <span class="text-xs">{{ $client->initials }}</span>
                                    </div>
                                </div>
                            @endif
                            <a href="{{ route('clients.show', $client) }}" class="font-medium hover:text-primary">
                                {{ $client->full_name }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm text-base-content/60">Report Date</span>
                        <div class="font-medium">{{ $report->report_date->format('F d, Y') }}</div>
                    </div>

                    @if($report->classSession)
                        <div>
                            <span class="text-sm text-base-content/60">Class Session</span>
                            <div class="font-medium">
                                <a href="{{ route('class-sessions.show', $report->classSession) }}" class="hover:text-primary">
                                    {{ $report->classSession->classPlan?->name ?? 'Class Session' }}
                                </a>
                            </div>
                            <div class="text-sm text-base-content/60">
                                {{ $report->classSession->start_time->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    @endif

                    @if($report->recordedBy)
                        <div>
                            <span class="text-sm text-base-content/60">Recorded By</span>
                            <div class="font-medium">{{ $report->recordedBy->name }}</div>
                            <div class="text-sm text-base-content/60">
                                {{ $report->completed_at?->format('M d, Y g:i A') ?? $report->created_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <span class="text-sm text-base-content/60">Status</span>
                        <div class="mt-1">
                            @if($report->status === 'completed')
                                <span class="badge badge-success badge-soft gap-1">
                                    <span class="icon-[tabler--check] size-3"></span>
                                    Completed
                                </span>
                            @else
                                <span class="badge badge-warning badge-soft gap-1">
                                    <span class="icon-[tabler--edit] size-3"></span>
                                    Draft
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress History Mini Chart --}}
            @if($templateReports->count() > 1)
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon-[tabler--chart-line] size-5 me-2"></span>
                            Score History
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-2">
                            @foreach($templateReports->take(5) as $historyReport)
                                <a href="{{ route('clients.progress.show', [$client, $historyReport]) }}"
                                   class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200/50 transition-colors {{ $historyReport->id === $report->id ? 'bg-primary/10 border border-primary/20' : '' }}">
                                    <div class="flex items-center gap-2">
                                        @if($historyReport->id === $report->id)
                                            <span class="icon-[tabler--point-filled] size-4 text-primary"></span>
                                        @else
                                            <span class="icon-[tabler--point] size-4 text-base-content/30"></span>
                                        @endif
                                        <span class="text-sm {{ $historyReport->id === $report->id ? 'font-semibold' : '' }}">
                                            {{ $historyReport->report_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @if($historyReport->overall_score)
                                        <span class="text-sm font-medium {{ $historyReport->overall_score >= 70 ? 'text-success' : ($historyReport->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                                            {{ number_format($historyReport->overall_score, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-base-content/40">-</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        @if($templateReports->count() > 5)
                            <a href="{{ route('clients.progress.index', $client) }}" class="btn btn-ghost btn-sm btn-block mt-3">
                                View All ({{ $templateReports->count() }})
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
